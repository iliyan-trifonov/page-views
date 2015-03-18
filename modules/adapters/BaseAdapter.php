<?php

namespace ITrifonov\PageViews\Modules\Adapters;

class BaseAdapter
{
    protected $server = null;
    protected $host = "";
    protected $port = "";
    protected $keyprefix = "";
    protected $key = "";
    protected $time = 86400; //24h

    protected $extensionName = "";
    protected $extensionClass = "";
    protected $propNamesAllowed = [
        'host',
        'port',
        'keyprefix',
        'time',
    ];

    public function setServer($server)
    {
        if ($server && $server instanceof $this->extenstionClass) {
            $this->server = $server;
            return true;
        }
        return false;
    }

    public function setDate($date)
    {
        $this->key = $this->keyprefix."--".$date;
    }

    public function setConfig($config = [])
    {
        if (isset($config) && !empty($config)) {
            foreach ($this->propNamesAllowed as $prop) {
                if (isset($config[$prop])) {
                    $this->$prop = $config[$prop];
                }
            }
        }
        $this->key = $this->keyprefix."--".date("Ymd");
    }

    public function get()
    {
        if (!$this->server) {
            return false;
        }

        //TODO: add try/catch
        return $this->server->get($this->key);
    }
}
