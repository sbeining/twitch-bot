<?php

namespace TwitchBot\Listener;

require_once __DIR__.'/../Util/Message.php';
require_once __DIR__.'/BaseMessageListener.php';

use TwitchBot\Util\Message;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class PokemonTypeListener extends BaseMessageListener
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

        if (preg_match('/!poketype (.*) (against|vs) (.*)/', $text, $matches)) {
            $offType = strtolower($matches[1]);
            $defType = strtolower($matches[3]);

            try {
                $this->printMatchup(
                    $channel,
                    $this->fetchType($offType),
                    $this->fetchType($defType)
                );
            } catch (RequestException $e) {
                $this->chat->sendMessage($channel, sprintf("Sorry, I don't know about %s or %s", ucfirst($offType), ucfirst($defType)));
            }
        } else if (preg_match('/!poketype (against|vs) (.*)/', $text, $matches)) {
            $type = strtolower($matches[2]);
            try {
                $this->printDefenses($channel, $this->fetchType($type));
            } catch (RequestException $e) {
                $this->chat->sendMessage($channel, sprintf("Sorry, I don't know about %s", ucfirst($type)));
            }
        } else if (preg_match('/!poketype (.*) (against|vs)/', $text, $matches)) {
            $type = strtolower($matches[1]);
            try {
                $this->printOffenses($channel, $this->fetchType($type));
            } catch (RequestException $e) {
                $this->chat->sendMessage($channel, sprintf("Sorry, I don't know about %s", ucfirst($type)));
            }
        } else if (preg_match('/!poketype (.*)/', $text, $matches)) {
            $type = strtolower($matches[1]);
            try {
                $this->printFull($channel, $this->fetchType($type));
            } catch (RequestException $e) {
                $this->chat->sendMessage($channel, sprintf("Sorry, I don't know about %s", ucfirst($type)));
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
    private function fetchType(string $name): array {
        $res = $this->client->get("type/{$name}");

        return json_decode($res->getBody(), true);
    }

    /**
     * @param string $channel
     * @param array $offType
     * @param array $defType
     *
     * @return void
     */
    private function printMatchup(string $channel, array $offType, array $defType) {
        $offTypeName = $this->getName($offType, 'en');
        $defTypeName = $this->getName($defType, 'en');

        $relations = $this->getDamageRelations($offType, $defType);

        $text = '';

        foreach ($relations as $relation) {
            switch($relation) {
                case 'no_damage_to':
                    $text = sprintf("%s has no effect against %s", $offTypeName, $defTypeName);
                    break;
                case 'half_damage_to':
                    $text = sprintf("%s is not very effective against %s", $offTypeName, $defTypeName);
                    break;
                case 'double_damage_to':
                    $text = sprintf("%s is super-effective against %s", $offTypeName, $defTypeName);
                    break;
            }
        }

        if ($text === '') {
            $text = sprintf("%s is normal against %s", $offTypeName, $defTypeName);
        }

        $this->chat->sendMessage($channel, $text);
    }

    /**
     * @param string $channel
     * @param array $type
     *
     * @return void
     */
    private function printDefenses(string $channel, array $type) {
        $name = $this->getName($type, 'en');

        $relations = $this->getDamageRelations($type);
        $relations = array_filter($relations, function($key) {
            return in_array($key, ['no_damage_from', 'half_damage_from', 'double_damage_from']);
        }, ARRAY_FILTER_USE_KEY);

        $this->chat->sendMessage($channel, $this->buildDamageRelationString($name, $relations));
    }

    /**
     * @param string $channel
     * @param array $type
     *
     * @return void
     */
    private function printOffenses(string $channel, array $type) {
        $name = $this->getName($type, 'en');

        $relations = $this->getDamageRelations($type);
        $relations = array_filter($relations, function($key) {
            return in_array($key, ['no_damage_to', 'half_damage_to', 'double_damage_to']);
        }, ARRAY_FILTER_USE_KEY);

        $this->chat->sendMessage($channel, $this->buildDamageRelationString($name, $relations));
    }

    /**
     * @param string $channel
     * @param array $type
     *
     * @return void
     */
    private function printFull(string $channel, array $type) {
        $name = $this->getName($type, 'en');

        $relations = $this->getDamageRelations($type);

        $this->chat->sendMessage($channel, $this->buildDamageRelationString($name, $relations));
    }

    /**
     * @param string $type
     * @param array $relations
     *
     * @return string
     */
    private function buildDamageRelationString(string $type, array $relations): string {
        $result = '';

        $cnt = 0;

        foreach ($relations as $relation => $types) {
            $result .= ($cnt === 0 ? $type : ' It');
            $typeText = $this->enumerate(array_map('ucfirst', $types));

            switch($relation) {
                case 'no_damage_to':
                    $result .= sprintf(" has no effect against %s.", $typeText);
                    break;
                case 'half_damage_to':
                    $result .= sprintf(" is not very effective against %s.", $typeText);
                    break;
                case 'double_damage_to':
                    $result .= sprintf(" is super-effective against %s.", $typeText);
                    break;
                case 'no_damage_from':
                    $result .= sprintf(" is immune to %s.", $typeText);
                    break;
                case 'half_damage_from':
                    $result .= sprintf(" is resistant to %s.", $typeText);
                    break;
                case 'double_damage_from':
                    $result .= sprintf(" is weak to %s.", $typeText);
                    break;
            }

            $cnt++;
        }

        return $result;
    }

    /**
     * @param array $offType
     * @param array|null $defType
     *
     * @return array
     */
    private function getDamageRelations(array $offType, array $defType = null): array {
        $result = array();

        foreach ($offType['damage_relations'] as $relation => $types) {
            foreach ($types as $type) {
                if ($defType === null) {
                    $result[$relation][] = $type['name'];
                } else if ($type['name'] === $defType['name']) {
                    $result[] = $relation;
                }
            }
        }

        return $result;
    }

    /**
     * @param array $type
     * @param string $lang
     *
     * @return string|null
     */
    private function getName(array $type, string $lang): ?string {
        foreach ($type['names'] as $name) {
            if ($name['language']['name'] === $lang) {
                return $name['name'];
            }
        }
    }
}
