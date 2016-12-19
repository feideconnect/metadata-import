<?php



class MetaFeedProcessor {
    protected $log, $store, $key, $config;
    function __construct($store, $key, $config) {
        $this->log = Logger::getInstance();
        $this->store = $store;
        $this->key = $key;
        $this->config = $config;
    }

    function process() {
        echo "\n";
        echo "Processing feed: $name\n";
        echo "Fetching metadata from: $url\n";

        $this->log->info("Processing feed " . $this->key, [
            "feed" => $this->key,
            "url" => $this->config['url'],
            ] );

            $xml = MetaFetcher::fetch($this->config['url']);
            $this->log->info("Parsing XML");
            $metadata = MetaFetcher::parse($xml);
            $this->log->info("Validating metadata", [
                "feed" => $this->key,
                "certs" => $this->config['certs']
            ]);
            MetaFetcher::validate_signature($metadata, $this->config['certs']);
            MetaFetcher::validate_expiration($metadata);
            $entities = MetaFetcher::findEntitiesRecursive($metadata);
            MetaFetcher::cleanEntities($entities);

            // $targetDir = dirname(__FILE__) . '/output/' . $name;
            // if (!is_dir($targetDir)) {
            //     echo "Creating output directory: $targetDir\n";
            //     createDir($targetDir);
            // }


            // TODO: Fetch entities
            // $entities = $this->store->getFeed($this->key);
            // echo "We Got entities";
            // print_r($entities);
            // exit;



            $total = count($entities);
            $added = 0;
            $updated = 0;
            $deleted = 0;

            echo "Processing $total entities.\n";

            $this->log->info("Processing entities", [
                "feed" => $this->key,
                "count" => $total
            ]);

            $seen = array();
            foreach ($entities as $entity) {

                $this->log->info("About to process entity", [
                    "entityID" => $entity["entityID"],
                    "entity" => $entity
                ]);

                // $filename = getEntityFilename($entity);
                // $seen[$filename] = true; // To clean out deleted files later

                $seen[$entity['entityID']] = true;

                // $filePath = $targetDir . '/' . $filename;
                // if (file_exists($filePath)) {
                //     $old = file_get_contents($filePath);
                // } else {
                //     $old = false;
                // }

                // $new = processEntity($entity, $processors);
                if ($old === $new) {
                    continue; // Unchanged
                }

                // file_put_contents($filePath, $new);
                // if ($old === false) {
                //     echo 'Added: ' . $entity->entityID . "\n";
                //     $added += 1;
                // } else {
                //     echo 'Updated: ' . $entity->entityID . "\n";
                //     $updated += 1;
                // }
            }
            // foreach (scandir($targetDir) as $entry) {
            //     if ($entry[0] === '.') {
            //         continue; // Skip hidden files / directories
            //     }
            //     if (substr($entry, -4) !== '.xml') {
            //         continue; // Ignore non-XML files.
            //     }
            //     if (isset($seen[$entry])) {
            //         continue; // Existing metadata file.
            //     }
            //
            //     // This is an unknown XML file. Delete it.
            //     echo 'Deleted: ' . $entry . "\n";
            //     unlink($targetDir . '/' . $entry);
            //     $deleted += 1;
            // }

            // echo "Processed $total entities. $added added, $updated updated, $deleted deleted.\n";

        }

    }
