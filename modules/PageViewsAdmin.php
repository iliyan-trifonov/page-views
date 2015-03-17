<?php

namespace ITrifonov;

require_once __DIR__.'/PageViews.php';

class PageViewsAdmin extends PageViews
{
    protected $sites = null;
    protected $ajax = false;

    public function __construct($config = [], $ajax = false)
    {
        parent::__construct($config);
        $this->ajax = $ajax;
        $this->sites = $this->getPageViews($ajax);
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
            return $this->sites;
        } else {
            ob_start();
            $sites = &$this->sites;
            require_once __DIR__.'/../templates/index.phtml';

            return ob_get_clean();
        }
    }
}
