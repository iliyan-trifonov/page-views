<?php

ini_set("display_errors", "1");
error_reporting(E_ALL);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../autoload.php';

echo "(" . date("Y-m-d H:i:s") . ") Starting..\n";

$pageViews = new \ITrifonov\PageViews\Modules\PageViews($pageViewsConfig);

$pageViews->setDate(date("Ymd", strtotime("-1 DAY")));

$domains = $pageViews->getPageViews();

arsort($domains);

$domains = array_map(
    function ($a) {
        return number_format($a);
    },
    $domains
);

ob_start();
echo "<div style=\"font-size: 14px;\">\n<pre>"
        . print_r($domains, true)
        . "</pre>\n</div>\n";
$buffer = ob_get_clean();
echo "email to be sent\n" . $buffer;

echo "Sending admin email..\n";
$emailRes = mail(
    "your.email@host.com",
    "Page Views Stats for " . date("Y-m-d"),
    "All domains:\n$buffer"
);
echo "Email " . ($emailRes ? " " : "\033[1;31mNOT\033[0m ") . "sent successfully!\n";

echo "(" . date("Y-m-d H:i:s") . ") Finished!\n";
