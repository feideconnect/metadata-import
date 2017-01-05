#! /usr/local/bin/php
<?php


require_once('./lib/ext/SimpleImage.php');
require_once('./lib/Logger.php');
require_once('./lib/Store.php');
require_once('./lib/MetaFetcher.php');
require_once('./lib/LogoProcessor.php');
require_once('./lib/MetaFeedProcessor.php');
require_once('./lib/MetaEngine.php');
require_once('vendor/autoload.php');



$entry = [
    'url' => 'https://idp.howcollege.ac.uk/logo.png',
    'height' => 60,
    'width' => 60,
];

$logoProcessor = new LogoProcessor($entry);

$logo = $logoProcessor->getLogo();

echo "Logo " . base64_encode($logo) . "\n";
echo sha1($logo) . "\n";
