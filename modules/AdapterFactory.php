<?php

namespace ITrifonov\PageViews\Modules;

class AdapterFactory
{
    protected $adapters = [
        "memcached" => "MemcachedAdapter",
        "redis" => "RedisAdapter"
    ];

    public function get($config, $server)
    {
        if (!$config || !isset($config["default"])) {
            return null;
        }

        $name = $config["default"];

        if (!isset($this->adapters[$name])) {
            return null;
        }

        $class = "\\ITrifonov\\PageViews\\Modules\\Adapters\\" . $this->adapters[$name];

        if (!class_exists($class)) {
            return null;
        }

        $adapter = new $class();

        if ($config) {
            $adapter->setConfig($config["servers"][$name]);
        }

        if ($server) {
            $adapter->setServer($server);
        } else {
            $adapter->init();
        }

        return $adapter;
    }
}
