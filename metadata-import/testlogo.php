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
$entry = array(
  'url' => 'https://static.surfconext.nl/logos/idp/kempel.png',
  'height' => 79,
  'width' => 108,
);
// $entry = array(
//   'url' => 'https://www.nmc.teicrete.gr/sites/default/files/images/logo-teicrete-350x76.png',
//   'height' => 350,
//   'width' => 76,
// );

// $entry = array(
//   'url' => 'https://www.imtlucca.it/_img/logo/new/logo_imt_80x80_square_blueback.png',
//   'height' => 60,
//   'width' => 80,
//   'lang' => 'en',
// ); // With redirects

$logoProcessor = new LogoProcessor($entry);

$logo = $logoProcessor->getLogo();

echo "Logo " . base64_encode($logo) . "\n";
echo sha1($logo) . "\n";
