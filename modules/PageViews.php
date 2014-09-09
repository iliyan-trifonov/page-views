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
    protected $allowDebug = false;
    protected $debugMessages = array();

    public function __construct($config = array(), $memcached = null, $debug = null)
    {
        $this->setConfig($config);
        if (!is_null($debug) && is_bool($debug)) {
            $this->setDebug($debug);
        }
        if (!is_null($memcached)) {
            $this->addDebug("used given memcached server");
            $this->memcached = $memcached;
        } else {
            $this->addDebug("creating a memcached server connection");
            $result = $this->createMemcached();
            if (!$result) {
                $msg = "createMemcached() error!";
                $this->addDebug($msg);
                $this->setError($msg);
            }
        }
        $this->pageviewsMemCKey .= '::' . date('Ymd');
        //$this->memcached->delete($this->pageviewsMemCKey); //cache cleanup
    }

    protected function createMemcached()
    {
        if (extension_loaded('memcached')) {
            $this->addDebug("memcached extension found!");
            $this->memcached = new \Memcached();
            $result = $this->memcached->addServer($this->memcachedHost, $this->memcachedPort);
            $statuses = $this->memcached->getStats();
            if ($result && isset($statuses[$this->memcachedHost.":".$this->memcachedPort])) {
                $this->addDebug("memcached server added successfully");
            } else {
                $this->memcached = null;
                $msg = "memcached server connect error!";
                $this->addDebug($msg);
                $this->setError($msg);
            }
            return $result;
        } else {
            $msg = "No memcached extension found!";
            $this->addDebug($msg);
            $this->setError($msg);
            return false;
        }
    }

    protected function getCache($key = "")
    {
        if ($this->memcached) {
            return $this->memcached->get($key);
        } else {
            $this->addDebug("getCache(): memcached not initialized!");
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
                $value = $this->memcached->get($this->pageviewsMemCKey, null, $cas);
                if ($this->memcached->getResultCode() == \Memcached::RES_NOTFOUND) {
                    $value = array($site => 1);
                    $result = $this->memcached->add($this->pageviewsMemCKey, $value, $this->pageviewsCacheTime);
                    break;
                } else {
                    if (!isset($value[$site])) {
                        $value[$site] = 1;
                    } else {
                        $value[$site]++;
                    }
                    $this->memcached->cas($cas, $this->pageviewsMemCKey, $value, $this->pageviewsCacheTime);
                }
                $counter++;
                if ($counter > 1000) {
                    break;
                }
            } while ($this->memcached->getResultCode() != \Memcached::RES_SUCCESS);
            $result = (isset($result) && !!$result) || ($this->memcached->getResultCode() == \Memcached::RES_SUCCESS);
            if (!$result) {
                $this->setError(
                    "addPageView(): false result error! result code: "
                    . $this->memcached->getResultCode()
                    . " counter: " . $counter
                );
            }
            $this->addDebug("addPageView() stats: result = $result, counter = $counter, last cas: $cas");
            return $result;
        } else {
            $this->setError("addPageView(): No memcached instance!");
            return false;
        }
    }

    public function getPageViews($site = "")
    {
        $result = $this->getCache($this->pageviewsMemCKey);
        if (!$result) {
            $result = array();
            if ($site) {
                return 0;
            }
        } elseif ($site) {
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
        $this->addDebug("Error: $errorTxt");
    }

    public function getError()
    {
        if (!$this->error) {
            return false;
        } else {
            return $this->error;
        }
    }

    protected function addDebug($message)
    {
        if ($this->allowDebug) {
            $this->debugMessages[] = $message;
            return true;
        }
        return false;
    }

    public function setDebug($allow = false)
    {
        $this->allowDebug = $allow;
    }

    public function getDebugMessages()
    {
        $result = empty($this->debugMessages) ? "none" : implode("<br/>\n", $this->debugMessages);
        return $result;
    }

    protected function setConfig($config = array())
    {
        if (isset($config) && !empty($config)) {
            if (isset($config['memcachedHost'])) {
                $this->memcachedHost = $config['memcachedHost'];
            }
            if (isset($config['memcachedPort'])) {
                $this->memcachedPort = $config['memcachedPort'];
            }
            if (isset($config['pageviewsMemCKey'])) {
                $this->pageviewsMemCKey = $config['pageviewsMemCKey'];
            }
            if (isset($config['pageviewsCacheTime'])) {
                $this->pageviewsCacheTime = $config['pageviewsCacheTime'];
            }
            if (isset($config['allowDebug'])) {
                $this->allowDebug = $config['allowDebug'];
            }
        }
    }
}
