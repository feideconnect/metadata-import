<?php

require_once(dirname(__FILE__) . '/JSONTools.php');

class MetaFeedProcessor {
    protected $log, $store, $key, $config;
    function __construct($store, $key, $config) {
        $this->log = Logger::getInstance();
        $this->store = $store;
        $this->key = $key;
        $this->config = $config;
    }

    static function normalizeEntity($x) {
        unset($x['expire']);
        unset($x['entityDescriptor']);
        return $x;
    }

    static function compareEntities($a, $b) {

        // $xa = self::normalizeEntity($a);
        // $xb = self::normalizeEntity($b);

        return ($a == $b);

    }


    static function getUIInfo($x) {
        $properties = [
            'description', 'OrganizationName', 'name', 'OrganizationDisplayName', 'url', 'OrganizationURL',
            'scope',
            'RegistrationInfo', 'UIInfo', 'DiscoHints'
        ];
        $nx = [];
        foreach($properties AS $p) {
            if (isset($x[$p])) {
                $nx[$p] = $x[$p];
            }
        }
        return $nx;
    }

    static function getReg($x) {
        if (isset($x['RegistrationInfo']) && is_string($x['RegistrationInfo']['registrationAuthority'])) {
            return $x['RegistrationInfo']['registrationAuthority'];
        }
        return null;
    }



    function process() {

        $url = $this->config['url'];
        $doValidate = true;
        if (isset($_ENV['DEBUG_FAST_UNSECURE']) && $_ENV['DEBUG_FAST_UNSECURE'] === "true") {
            // Override with local cached file for development.
            $file = dirname(dirname(__FILE__)) . '/temp.xml';
            if (file_exists($file)) {
                $url = $file;
            }
            $doValidate = false;
        }

        $timeStart = time();

        $this->log->info("Processing feed " . $this->key, [
            "feed" => $this->key,
            "url" => $url,
            "validate" => $doValidate,
        ] );
        $xml = MetaFetcher::fetch($url);

        if ($doValidate) {
            $this->log->info("Parsing XML", [
                "feed" => $this->key,
            ]);

            $metadata = MetaFetcher::parse($xml);
            $this->log->info("Validating metadata", [
                "feed" => $this->key,
                // "certs" => $this->config['certs']
            ]);
            MetaFetcher::validate_signature($metadata, $this->config['certs']);
            MetaFetcher::validate_expiration($metadata);
        }

        unset($metadata);

        $entities = SimpleSAML_Metadata_SAMLParser::parseDescriptorsString($xml);
        $existingFeed = $this->store->getFeed($this->key);

        $total = count($entities);
        $added = 0;
        $updated = 0;
        $deleted = 0;

        $this->log->info("Processing entities", [
            "feed" => $this->key,
            "feedCount" => $total,
            "existingCount" => count($existingFeed),
        ]);

        $seen = array();
        foreach ($entities as $entity) {

            $rawSAMLmeta = $entity->getMetadata20IdP();

            // echo "----\n\n"; print_r($rawSAMLmeta); exit;

            $saml2idp = self::normalizeEntity($rawSAMLmeta);
            $entityid = $saml2idp['entityid'];
            if (!isset($entityid)) {
                continue;
            }

            if (isset($_ENV['DEBUG_RANDOMIZE']) && $_ENV['DEBUG_RANDOMIZE'] === 'true') {
                if (mt_rand(0, 100) >= 99) {
                    continue;
                }
                if (mt_rand(0, 100) >= 100) {
                    $saml2idp["name"] = [
                        "en" => "New random name",
                    ];
                }
            }

            // if ($existingFeed[$entityid]) {
            //     print_r($existingFeed[$entityid]); exit;
            // }

            $seen[$entityid] = true;
            if ($existingFeed[$entityid] && $existingFeed[$entityid]['enabled']) {
                // There already exist an entry.

                if (!self::compareEntities($existingFeed[$entityid]['metadata'], $saml2idp)) {


                    $diff = JSONTools::diff($existingFeed[$entityid]['metadata'], $saml2idp);
                    $this->log->info("UPDATING entity", [
                        "entityID" => $entityid,
                        "diff" => $diff,
                    ]);
                    $this->store->insert($this->key, $entityid, $saml2idp, self::getUIInfo($saml2idp), self::getReg($saml2idp), TRUE); // UPDATE
                    $updated++;


                } else  {
                    // $this->log->info("NOOP entity", [
                    //     "entityID" => $entityid,
                    // ]);
                    // New entry is identical with the old one.
                    continue;
                }

            } else {

                if ($existingFeed[$entityid] ) {
                    $this->log->info("INSERTING (RE-ENABLING) entity", [
                        "entityID" => $entityid,
                    ]);
                } else {
                    $this->log->info("INSERTING entity", [
                        "entityID" => $entityid,
                    ]);
                }

                $this->store->insert($this->key, $entityid, $saml2idp, self::getUIInfo($saml2idp), self::getReg($saml2idp), FALSE); // New entry
                $added++;
            }

            if (isset($_ENV['DEBUG_DUMP']) && $_ENV['DEBUG_DUMP'] === 'true') {
                $outfilename = '/metadata-import/var/' . sha1($entityid) . '.json';
                echo "Writing to " . $outfilename . "\n";
                file_put_contents($outfilename, json_encode($saml2idp, JSON_PRETTY_PRINT));
            }

        }

        foreach($existingFeed AS $entityid => $oldEntity) {

            if ($oldEntity['enabled'] === false) {

                continue;
            }

            if (!$seen[$entityid]) {
                $this->log->info("DELETING entity", [
                    "entityID" => $entityid,
                ]);
                $this->store->softDelete($this->key, $entityid);
                $deleted++;
            }

        }


        $this->log->info("Completed processing entities", [
            "feed" => $this->key,
            "feedCount" => $total,
            "existingCount" => count($existingFeed),
            "added" => $added,
            "updated" => $updated,
            "deleted" => $deleted,
            "untouched" => (count($existingFeed) - $updated - $deleted),
            "timerSeconds" => time() - $timeStart,
        ]);
    }

}
