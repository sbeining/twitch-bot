<?php declare(strict_types=1);

namespace TwitchBot\Processor\Module;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class PokemonModule extends Module
{
    /** @var Client */
    private $client;

    /**
     * @return void
     */
    public function __construct() {
        $this->client = new Client(['base_uri' => 'https://pokeapi.co/api/v2/']);
    }

    /**
     * @param string $in
     *
     * @return string|null
     */
    public function handle(string $in): ?string {
        $data = json_decode($in, true);
        $text = $data['content'];
        $result = null;

        if (preg_match('/!pokemon (.*) (.*)?/', $text, $matches)) {
            $name = strtolower($matches[1]);
            $formName = strtolower($matches[2]);
            try {
                $species = $this->fetchSpecies($name);
                $form = $this->fetchForm("{$name}-{$formName}");
                $pokemon = $this->getPokemon($species, "{$name}-{$formName}");
                $result = [
                    'species' => $species,
                    'form' => $form,
                    'pokemon' => $pokemon,
                ];
            } catch (RequestException $e) {
                $result = ['content' => sprintf("Sorry, I don't know about %s", ucfirst($name))];
            }
        } elseif (preg_match('/!pokemon (.*)?/', $text, $matches)) {
            $name = strtolower($matches[1]);
            try {
                $species = $this->fetchSpecies($name);
                $form = $this->fetchForm($name);
                $pokemon = $this->getPokemon($species);
                $result = [
                    'species' => $species,
                    'form' => $form,
                    'pokemon' => $pokemon,
                ];
            } catch (RequestException $e) {
                $result = ['content' => sprintf("Sorry, I don't know about %s", ucfirst($name))];
            }
        }

        if ($result) {
            return json_encode($result) ?: null;
        }

        return null;
    }

    /**
     * @param string $name
     *
     * @throws RequestException
     *
     * @return array
     */
    private function fetchPokemon(string $name): array {
        $name = urlencode($name);
        $res = $this->client->get("pokemon/{$name}");

        return json_decode($res->getBody()->getContents(), true);
    }

    /**
     * @param string $name
     *
     * @throws RequestException
     *
     * @return array
     */
    private function fetchSpecies(string $name): array {
        $res = $this->client->get("pokemon-species/{$name}");

        return json_decode($res->getBody()->getContents(), true);
    }

    /**
     * @param string $name
     *
     * @throws RequestException
     *
     * @return array
     */
    private function fetchForm(string $name): array {
        $res = $this->client->get("pokemon-form/{$name}");

        return json_decode($res->getBody()->getContents(), true);
    }

    /**
     * @param array $species
     * @param string|null $form
     *
     * @throws RequestException
     *
     * @return array
     */
    private function getPokemon(array $species, string $form = null): array {
        if ($form) {
            return $this->fetchPokemon($form);
        }

        foreach ($species['varieties'] as $variety) {
            if ($variety['is_default']) {
                return $this->fetchPokemon($variety['pokemon']['name']);
            }
        }
    }
}
