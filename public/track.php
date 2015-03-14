<?php

require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../modules/PageViews.php';

$pageViews = new \ITrifonov\PageViews($pageViewsConfig);
$pageViews->addPageView();
