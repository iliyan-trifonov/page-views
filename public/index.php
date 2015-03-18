<?php

ini_set("default_charset", 'UTF-8');
ob_start("ob_gzhandler");
header('Content-Type: text/html;charset=utf-8');

require_once __DIR__.'/../config/config.php';
require_once __DIR__ . '/../autoload.php';

$pViews = new \ITrifonov\PageViews\Modules\PageViewsAdmin(
    $pageViewsConfig,
    isset($_GET['ajax']) && "1" === $_GET['ajax']
);

echo $pViews->output();
