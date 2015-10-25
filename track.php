<?php

require_once __DIR__.'/config/config.php';
require_once __DIR__.'/autoload.php';

$pageViews = new \ITrifonov\PageViews\Modules\PageViews($pageViewsConfig);
$pageViews->addPageView();
