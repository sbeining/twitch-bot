<?php

namespace TwitchBot\Listener;

use TwitchBot\Util\Message;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class PokemonListener extends BaseMessageListener
{
    /** @var Client */
    private $client;

    /**
     * @param string $user
     * @param string $channel
     * @param string $text
     * @param Message $message
     *
     * @return void
     */
    public function handleMessage($user, $channel, $text, Message $message)
    {
        $this->client = new Client(['base_uri' => 'https://pokeapi.co/api/v2/']);

        if (preg_match('/!pokemon (.*) (.*)?/', $text, $matches)) {
            $name = strtolower($matches[1]);
            $form = strtolower($matches[2]);
            try {
                $species = $this->fetchSpecies($name);
                $pokemon = $this->getPokemon($species, "{$name}-{$form}");
                $this->printPokemon($channel, $species, $pokemon);
            } catch (RequestException $e) {
                $this->chat->sendMessage($channel, sprintf("Sorry, I don't know about %s", ucfirst($name)));
            }
        } elseif (preg_match('/!pokemon (.*)?/', $text, $matches)) {
            $name = strtolower($matches[1]);
            try {
                $species = $this->fetchSpecies($name);
                $pokemon = $this->getPokemon($species);
                $this->printPokemon($channel, $species, $pokemon);
            } catch (RequestException $e) {
                $this->chat->sendMessage($channel, sprintf("Sorry, I don't know about %s", ucfirst($name)));
            }
        }
    }

    /**
     * @param string $name
     *
     * @throws RequestException
     *
     * @return array
     */
    private function fetchPokemon($name) {
        $name = urlencode($name);
        $res = $this->client->get("pokemon/{$name}");

        return json_decode($res->getBody(), true);
    }

    /**
     * @param string $name
     *
     * @throws RequestException
     *
     * @return array
     */
    private function fetchSpecies($name) {
        $res = $this->client->get("pokemon-species/{$name}");

        return json_decode($res->getBody(), true);
    }

    /**
     * @param string $name
     *
     * @throws RequestException
     *
     * @return array
     */
    private function fetchForm($name) {
        $res = $this->client->get("pokemon-form/{$name}");

        return json_decode($res->getBody(), true);
    }

    /**
     * @param array $species
     * @param string|null $form
     *
     * @throws RequestException
     *
     * @return array
     */
    private function getPokemon($species, $form = null) {
        if ($form) {
            return $this->fetchPokemon($form);
        }

        foreach ($species['varieties'] as $variety) {
            if ($variety['is_default']) {
                return $this->fetchPokemon($variety['pokemon']['name']);
            }
        }
    }

    /**
     * @param string $channel
     * @param array $species
     * @param array $pokemon
     *
     * @return void
     */
    private function printPokemon($channel, $species, $pokemon) {
        $forms = [];

        foreach ($species['varieties'] as $variety) {
            if ($variety['pokemon']['name'] !== $pokemon['name']) {
                $shortForm = str_replace("{$species['name']}-", '', $variety['pokemon']['name']);
                $forms[] = ucfirst($shortForm);
            }
        }

        $nameEn = $this->getName($species, 'en');

        if (!$pokemon['is_default']) {
            $form = $this->fetchForm($pokemon['name']);
            $name = $this->getName($form, 'en');
        } else {
            $name = $nameEn;
        }

        $text = sprintf("%s, the %s. It is %s type.",
            $name,
            $this->getGenus($species, 'en'),
            $this->enumerate($this->getType($pokemon))
        );

        $best = $this->getBestStats($pokemon);
        $worst = $this->getWorstStats($pokemon);

        if (count($best) < 6) {
            $bestRating = current($best);
            $worstRating = current($worst);
            $text .= sprintf(' It has %s %s but %s %s.',
                $bestRating,
                $this->enumerate(array_keys($best)),
                $worstRating,
                $this->enumerate(array_keys($worst))
            );
        } else {
            $text .= ' It is perfectly balanced.';
        }

        if (!empty($forms)) {
            $text .= sprintf(' It has different forms: %s.', $this->enumerate($forms));
        }

        $nameDe = $this->getName($species, 'de');

        if ($nameEn != $nameDe) {
            $text .= sprintf(' In germany it is more commonly known as %s.', $nameDe);
        }

        $this->chat->sendMessage($channel, $text);
    }

    /**
     * @param array $stats
     *
     * @return array
     */
    private function getStatsInWords($stats) {
        return array_map([$this, 'rateStat'], $stats);
    }

    /**
     * @param array $pokemon
     *
     * @return array
     */
    private function getStats($pokemon) {
        $result = [];

        foreach (array_reverse($pokemon['stats']) as $stat) {
            $name = ucfirst($stat['stat']['name']);
            $value = $stat['base_stat'];
            $result[$name] = $value;
        }

        return $result;
    }

    /**
     * @param array $pokemon
     *
     * @return array
     */
    private function getBestStats($pokemon) {
        $result = [];
        $stats = $this->getStats($pokemon);
        arsort($stats);
        $stats = $this->getStatsInWords($stats);

        $prev = null;
        foreach ($stats as $name => $value) {
            if ($prev !== null && $value !== $prev) {
                break;
            }

            $result[$name] = $value;
            $prev = $value;
        }

        return $result;
    }

    /**
     * @param array $pokemon
     *
     * @return array
     */
    private function getWorstStats($pokemon) {
        $result = [];
        $stats = $this->getStats($pokemon);
        asort($stats);
        $stats = $this->getStatsInWords($stats);

        $prev = null;
        foreach ($stats as $name => $value) {
            if ($prev !== null && $value !== $prev) {
                break;
            }

            $result[$name] = $value;
            $prev = $value;
        }

        return $result;
    }

    /**
     * @param int $value
     *
     * @return string
     */
    private function rateStat($value) {
        if ($value < 25) {
            return 'horrible';
        } else if ($value < 50) {
            return 'bad';
        } else if ($value < 75) {
            return 'poor';
        } else if ($value < 100) {
            return 'below average';
        } else if ($value < 125) {
            return 'above average';
        } else if ($value < 150) {
            return 'good';
        } else if ($value < 175) {
            return 'very good';
        } else if ($value < 200) {
            return 'excellent';
        } else if ($value < 225) {
            return 'fantastic';
        } else {
            return 'amazing';
        }
    }

    /**
     * @param array $pokemon
     *
     * @return int
     */
    private function getStatTotal($pokemon) {
        $result = 0;

        foreach ($pokemon['stats'] as $stat) {
            $result += $stat['base_stat'];
        }

        return $result;
    }

    /**
     * @param array $pokemon
     *
     * @return array
     */
    private function getType($pokemon) {
        $result = [];

        foreach ($pokemon['types'] as $type) {
            $result[] = ucfirst($type['type']['name']);
        }

        return $result;
    }

    /**
     * @param array $species
     * @param string $lang
     *
     * @return string|null
     */
    private function getGenus($species, $lang) {
        foreach ($species['genera'] as $genus) {
            if ($genus['language']['name'] === $lang) {
                return $genus['genus'];
            }
        }
    }

    /**
     * @param array $species
     * @param string $lang
     *
     * @return string|null
     */
    private function getName($species, $lang) {
        foreach ($species['names'] as $name) {
            if ($name['language']['name'] === $lang) {
                return $name['name'];
            }
        }
    }
}
