<?php

namespace ITrifonov\PageViews\Modules\Adapters;

use ITrifonov\PageViews\Modules\AdapterInterface;

class RedisAdapter extends BaseAdapter implements AdapterInterface
{
    protected $extensionName = "redis";
    protected $extensionClass = "\\Redis";
    protected $host = "127.0.0.1";
    protected $port = "6379";

    public function init()
    {
        if (extension_loaded($this->extensionName)) {
            $this->server = new $this->extensionClass();
            $result = $this->server->connect(
                $this->host,
                $this->port,
                0.1 //sec
            );
            if (!$result) {
                $this->server = null;

                return false;
            }

            return $result;
        } else {
            return false;
        }
    }

    public function get()
    {
        if (!$this->server) {
            return false;
        }

        //TODO: add try/catch
        $result = $this->server->hGetAll($this->key);

        if (!$result) {
            return [];
        }

        $domains = [];
        foreach ($result as $domain => $pageViews) {
            $domains[$domain] = (int) $pageViews;
        }

        return $domains;
    }

    public function incr($domain)
    {
        if (!$this->server) {
            return false;
        }

        //@todo: replace get() with key check
        if (!$this->get()) {
            return $this->server->multi()
                ->hSet($this->key, $domain, 1)
                //+3600: add more time for the cron to get the previous day data
                ->expire($this->key, $this->time + 3600)
                ->exec();
        } else {
            return $this->server->hIncrBy($this->key, $domain, 1);
        }
    }
}
