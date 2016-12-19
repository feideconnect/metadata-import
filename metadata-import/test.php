#! /usr/local/bin/php
<?php

require_once('./lib/Store.php');

putenv('CASSANDRA_NODES=' . $_ENV['CASSANDRA_PORT_9042_TCP_ADDR']);
$store = new Store();

$feed = 'edugain';
$metadata = [
  'name' => ['no' => 'Test data'],
  'descr' => ['no' => 'Yay'],
  'entityId' => 'https://blah'
];

#$store->insertOrUpdate($feed, $metadata['entityId'], $metadata);

$fdata = $store->getFeed($feed);
