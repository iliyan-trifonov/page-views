<?php

namespace ITrifonov;

class PageViews
{
    protected $memcached = null;
    protected $memcachedHost = '127.0.0.1';
    protected $memcachedPort = '11211';
    protected $pageviewsMemCKey = 'pageviews_stats';
    protected $pageviewsCacheTime = 86400;
    protected $error = "";

    public function __construct($config = [], $memcached = null)
    {
        $this->setConfig($config);
        if (!is_null($memcached)) {
            $this->memcached = $memcached;
        } else {
            $result = $this->createMemcached();
            if (!$result) {
                $this->setError("createMemcached() error!");
                return false;
            }
        }
        $this->pageviewsMemCKey .= '::'.date('Ymd');
        //$this->memcached->delete($this->pageviewsMemCKey); //cache cleanup
        return true;
    }

    protected function createMemcached()
    {
        if (extension_loaded('memcached')) {
            $this->memcached = new \Memcached();
            $result = $this->memcached->addServer(
                $this->memcachedHost,
                $this->memcachedPort
            );
            $statuses = $this->memcached->getStats();
            if (!$result
                || !isset(
                        $statuses[
                            $this->memcachedHost.":".$this->memcachedPort
                        ]
                    )
            ) {
                $this->memcached = null;
                $this->setError("memcached server connect error!");
            }

            return $result;
        } else {
            $this->setError("No memcached extension found!");

            return false;
        }
    }

    protected function getCurrentHostName()
    {
        return $_SERVER['HTTP_HOST'];
    }

    public function addPageView($site = "")
    {
        if (!$site) {
            $site = $this->getCurrentHostName();
        }
        if ($this->memcached) {
            $cas = null;
            $counter = 0;
            do {
                $value = $this->memcached->get(
                    $this->pageviewsMemCKey,
                    null,
                    $cas
                );
                if ($this->memcached->getResultCode() === \Memcached::RES_NOTFOUND) {
                    $value = [$site => 1];
                    $result = $this->memcached->add(
                        $this->pageviewsMemCKey,
                        $value,
                        $this->pageviewsCacheTime
                    );
                    break;
                } else {
                    if (!isset($value[$site])) {
                        $value[$site] = 1;
                    } else {
                        $value[$site]++;
                    }
                    $this->memcached->cas(
                        $cas,
                        $this->pageviewsMemCKey,
                        $value,
                        $this->pageviewsCacheTime
                    );
                }
                $counter++;
                if ($counter > 1000) {
                    break;
                }
            } while ($this->memcached->getResultCode() !== \Memcached::RES_SUCCESS);
            $result = (isset($result) && !!$result)
                || ($this->memcached->getResultCode() === \Memcached::RES_SUCCESS);
            if (!$result) {
                $this->setError(
                    "addPageView(): result error! result code: "
                    .$this->memcached->getResultCode()
                    ." counter: ".$counter
                );
            }

            return $result;
        } else {
            $this->setError("addPageView($site): No memcached instance!");

            return false;
        }
    }

    public function getPageViews($site = "")
    {
        $result = $this->memcached->get($this->pageviewsMemCKey);
        if (!$result) {
            $result = [];
        }
        if ($site) {
            if (isset($result[$site])) {
                return $result[$site];
            } else {
                return 0;
            }
        }
        return $result;
    }

    protected function setError($errorTxt = "")
    {
        $this->error = $errorTxt;
    }

    public function getError()
    {
        if (!$this->error) {
            return false;
        } else {
            return $this->error;
        }
    }

    protected function setConfig($config = [])
    {
        if (isset($config) && !empty($config)) {
            foreach ([
                'memcachedHost',
                'memcachedPort',
                'pageviewsMemCKey',
                'pageviewsCacheTime',
                ] as $prop
            ) {
                if (isset($config[$prop])) {
                    $this->$prop = $config[$prop];
                }                
            }
        }
    }
}
