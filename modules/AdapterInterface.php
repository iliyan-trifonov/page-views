<?php

namespace ITrifonov\PageViews\Modules;

interface AdapterInterface
{
    public function setServer($server);
    public function setConfig($config);
    public function init();
    public function get();
    public function incr($domain);
}
