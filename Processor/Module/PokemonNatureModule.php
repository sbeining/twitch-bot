<?php declare(strict_types=1);

namespace TwitchBot\Processor\Module;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class PokemonNatureModule extends Module
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

        if (preg_match('/!pokenature (.*)/', $text, $matches)) {
            $name = strtolower($matches[1]);
            try {
                $nature = $this->fetchNature($name);
                $result = $this->printNature($nature);
            } catch (RequestException $e) {
                $result = sprintf("Sorry, I don't know about %s", ucfirst($name));
            }
        }

        if ($result) {
            return json_encode([
                'content' => $result,
            ]) ?: null;
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
    private function fetchNature(string $name): array {
        $res = $this->client->get("nature/{$name}");

        return json_decode($res->getBody()->getContents(), true);
    }

    /**
     * @param array $nature
     *
     * @return string
     */
    private function printNature(array $nature): string {
        $name = $nature['name'];

        $increased = $nature['increased_stat'];
        $decreased = $nature['decreased_stat'];

        if ($increased === null || $decreased === null) {
            return sprintf('%s is neutral', ucfirst($name));
        }

        return sprintf('%s is plus %s minus %s', ucfirst($name), $increased['name'], $decreased['name']);
    }
}
