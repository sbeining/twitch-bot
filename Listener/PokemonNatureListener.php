<?php

namespace TwitchBot\Listener;

use TwitchBot\Util\Message;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class PokemonNatureListener extends BaseMessageListener
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

        if (preg_match('/!pokenature (.*)/', $text, $matches)) {
            $nature = strtolower($matches[1]);
            try {
                $nature = $this->fetchNature($nature);
                $this->printNature($channel, $nature);
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
    private function fetchNature($name) {
        $res = $this->client->get("nature/{$name}");

        return json_decode($res->getBody(), true);
    }


    /**
     * @param string $channel
     * @param array $nature
     *
     * @return void
     */
    private function printNature($channel, $nature) {
        $name = $nature['name'];

        $increased = $nature['increased_stat'];
        $decreased = $nature['decreased_stat'];

        if ($increased === null || $decreased === null) {
            $this->chat->sendMessage($channel, sprintf('%s is neutral', ucfirst($name)));
            return;
        }

        $text = sprintf('%s is plus %s minus %s', ucfirst($name), $increased['name'], $decreased['name']);

        $this->chat->sendMessage($channel, $text);
    }
}
