<?php

namespace Dataporten\MetadataImport;

class MetaEngine {

    protected $store = null;
    protected $feeds = null;
    protected $log = null;

    function __construct() {
        $this->log = Logger::getInstance();
        $this->store = new Store();
        $this->feeds = $this->getFeeds();
        $this->process();
    }

    protected function getFeeds() {
        $feed = array();
        include(dirname(dirname(__FILE__)) . '/etc/config.php');
        return $feed;
    }

    function process() {


        $failed = false;
        foreach ($this->feeds as $feed => $feedconfig) {
            try {
                // $this->log->info('About to process logs for ' . $feed, $feedconfig);
                $fp = new MetaFeedProcessor($this->store, $feed, $feedconfig);
                $fp->process();

                // $processors = array();
                // if (array_key_exists('processors', $config)) {
                //     $processors = $config['processors'];
                // }
                // processFeed($name, $config['url'], $config['certs'], $processors);
            } catch (Exception $e) {
                error_log('Error processing feed ' . $name . ':');
                error_log(get_class($e) . ': ' . $e->getMessage());
                error_log($e->getTraceAsString());
                $failed = true;
                // We dont abort on a failure of a single feed, but continue with the next.
            }
        }
        if ($failed) {
            exit(1); // One or more feeds had errors.
        }
        exit(0);
    }

}
