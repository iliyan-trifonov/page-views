<?php

namespace ITrifonov\PageViews\modules\adapters;

use ITrifonov\PageViews\Modules\AdapterInterface;

class MemcachedAdapter extends BaseAdapter implements AdapterInterface
{
    protected $extensionName = 'memcached';
    protected $extensionClass = '\\Memcached';
    protected $host = '127.0.0.1';
    protected $port = '11211';

    public function init()
    {
        if (extension_loaded($this->extensionName)) {
            $this->server = new $this->extensionClass();
            $result = $this->server->addServer(
                $this->host,
                $this->port
            );
            $statuses = $this->server->getStats();
            if (!$result
                || !isset(
                    $statuses[
                    $this->host.':'.$this->port
                    ]
                )
            ) {
                $this->server = null;

                return false;
            }

            return $result;
        } else {
            return false;
        }
    }

    public function incr($domain = '')
    {
        if (!$domain) {
            return false;
        }
        if ($this->server) {
            $cas = null;
            $counter = 0;

            do {
                $value = $this->server->get(
                    $this->key,
                    null,
                    $cas
                );
                if ($this->server->getResultCode() === \Memcached::RES_NOTFOUND) {
                    $value = [$domain => 1];
                    $result = $this->server->add(
                        $this->key,
                        $value,
                        $this->time
                    );
                    break;
                } else {
                    if (!isset($value[$domain])) {
                        $value[$domain] = 1;
                    } else {
                        $value[$domain]++;
                    }
                    $this->server->cas(
                        $cas,
                        $this->key,
                        $value,
                        $this->time
                    );
                }
                $counter++;
                if ($counter > 1000) {
                    break;
                }
            } while ($this->server->getResultCode() !== \Memcached::RES_SUCCESS);

            $result = (isset($result) && !!$result)
                    || ($this->server->getResultCode() === \Memcached::RES_SUCCESS);

            return $result;
        } else {
            return false;
        }
    }
}
