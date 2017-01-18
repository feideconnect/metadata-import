#! /usr/local/bin/php
<?php

require_once(__DIR__ . '/../vendor/autoload.php');

use Dataporten\MetadataImport\LogoProcessor;



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

$entry = [
    "url" => "https://www.fhs.se/files/sidhuvud/logotyp-sv.jpg",
    "height" => 118,
    "width" => 106,
    "lang" => "sv",
];
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
