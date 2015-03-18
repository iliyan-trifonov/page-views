<?php

ini_set("display_errors", "1");
error_reporting(E_ALL);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../autoload.php';

echo "(" . date("Y-m-d H:i:s") . ") Starting..\n";

$pageViews = new \ITrifonov\PageViews\Modules\PageViews($pageViewsConfig);

$yesterday = date("Ymd", strtotime("-1 DAY"));
echo "yesterday is: $yesterday\n";

$pageViews->setDate($yesterday);

$domains = $pageViews->getPageViews();

arsort($domains);

$body = "All domains:<br/><div style=\"font-size: 14px;\">";
if (!$domains || empty($domains)) {
    $body .= "None";
} else {
    foreach ($domains as $domain => $pViews) {
        $body .= "<span>$domain -> ".number_format($pViews)."</span><br/>\n";
    }
}
$body .= "</div>";

echo "email to be sent:\n\033[1;34m$body\033[0m\n";

echo "Sending email..\n";
$emailRes = mail(
    "your.email@example.com",
    "Page Views Stats for $yesterday",
    "$body"
);
echo "Email " . ($emailRes ? " " : "\033[1;31mNOT\033[0m ") . "sent successfully!\n";

echo "(" . date("Y-m-d H:i:s") . ") Finished!\n";
