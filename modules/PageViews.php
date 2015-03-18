<?php

namespace ITrifonov\PageViews\Modules;

class PageViews
{
    protected $adapter = null;
    protected $adapters = [
        "memcached" => "MemcachedAdapter",
        "redis" => "RedisAdapter"
    ];
    protected $error = "";

    public function __construct($config = [], $server = null)
    {
        $factory = new AdapterFactory();
        if (!$this->adapter = $factory->get($config, $server)) {
            $this->setError("Could not create adapter!");
            return false;
        }
        return true;
    }

    protected function getCurrentHostName()
    {
        return $_SERVER['HTTP_HOST'];
    }

    public function addPageView($domain = "")
    {
        if ($this->error) {
            return false;
        }
        if (!$domain) {
            $domain = $this->getCurrentHostName();
        }
        try {
            $result = $this->adapter->incr($domain);
        } catch (\Exception $exc) {
            $this->setError("incr() exception: " . $exc->getMessage());
            return false;
        }
        if (!$result) {
            $this->setError("incr() returned false! Check your config!");
        }
        return $result;
    }

    public function getPageViews($domain = "")
    {
        $result = $this->adapter->get();
        if (!$result) {
            $result = [];
        }
        if ($domain) {
            if (isset($result[$domain])) {
                return $result[$domain];
            } else {
                return 0;
            }
        }

        return $result;
    }

    public function setDate($date)
    {
        //TODO: check for valid date: YYYYMMDD
        if ($date) {
            $this->adapter->setDate($date);
        }
    }

    protected function setError($errorTxt = "")
    {
        $this->error = $errorTxt;
    }

    public function getError()
    {
        return $this->error;
    }
}
