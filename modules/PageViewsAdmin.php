<?php

namespace ITrifonov\PageViews\modules;

class PageViewsAdmin extends PageViews
{
    protected $domains = null;
    protected $ajax = false;

    public function __construct($config = [], $ajax = false)
    {
        parent::__construct($config);
        $this->ajax = $ajax;
        $this->domains = $this->getPageViews($ajax);
    }

    public function getPageViews($json = false)
    {
        $result = parent::getPageViews();
        if ($json) {
            $result = json_encode($result);
        }

        return $result;
    }

    public function output()
    {
        if ($this->ajax) {
            return $this->domains;
        } else {
            ob_start();
            $domains = &$this->domains;
            require_once __DIR__.'/../templates/index.phtml';

            return ob_get_clean();
        }
    }
}
