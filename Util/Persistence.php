<?php

namespace TwitchBot\Util;

class Persistence
{
    /** @var array */
    private $data;
    /** @var string */
    private $path;

    /**
     * @param string $path
     */
    public function __construct($path)
    {
      $this->path = $path;
      $this->data = $this->load();
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     *
     * @return void
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return array|null
     */
    private function load()
    {
        $json = file_get_contents($this->path);

        return json_decode($json, true);
    }

    /**
     * @return void
     */
    public function save()
    {
        $json = json_encode($this->data, JSON_PRETTY_PRINT);

        file_put_contents($this->path, $json);
    }
}
