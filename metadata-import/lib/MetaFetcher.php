<?php

class MetaFetcher {

    static function fetch($url) {
        $options = array(
            'http' => array(
                'header' => "User-Agent: dataporten-metadata-import <kontakt@uninett.no>\r\n",
            ),
        );
        $xml = file_get_contents($url, false, stream_context_create($options));
        if ($xml === false) {
            $error = error_get_last();
            throw new Exception('Error fetching ' . $url . ': ' . $error['message']);
        }
        return $xml;
    }

    static function parse($xml) {
        // create DOM
        $doc = new DOMDocument();
        if (@$doc->loadXML($xml) === false) {
            $error = error_get_last();
            throw new Exception('Error parsing XML: ' . $error['message']);
        }
        $root = $doc->documentElement;
        if ($root->namespaceURI !== SAML2_Const::NS_MD || $root->localName !== 'EntitiesDescriptor') {
            throw new Exception('Got unknown root element in metadata: {' . $root->namespaceURI . '}' . $root->localName);
        }
        return new SAML2_XML_md_EntitiesDescriptor($root);
    }

    static function validate_signature($metadata, $certs) {
        foreach ($certs as $cert) {
            $key = new XMLSecurityKey(XMLSecurityKey::RSA_SHA1, array('type' => 'public'));
            $valid = false;
            $key->loadKey($cert, false, true);
            try {
                $valid = $metadata->validate($key);
            } catch(Exception $e) {}
                if ($valid) {
                    return;
                }
            }
            // We have tried all certificates without success.
            throw new Exception('Unable to validate metadata signature');
        }

        static function validate_expiration($metadata) {
            if ($metadata->validUntil === NULL) {
                return; /* No expiration time in metadata -- nothing to validate. */
            }
            if ($metadata->validUntil <= time()) {
                throw new Exception('Metadata expired at: ' . strftime('%Y-%m-%d %H:%M:%S', $metadata->validUntil));
            }
        }

        static function findEntitiesRecursive(SAML2_XML_md_EntitiesDescriptor $entitiesDescriptor) {
            $entities = array();
            foreach ($entitiesDescriptor->children as $child) {
                if ($child instanceof SAML2_XML_md_EntitiesDescriptor) {
                    $entities = array_merge($entities, findEntitiesRecursive($child));
                } elseif ($child instanceof SAML2_XML_md_EntityDescriptor) {
                    $entities[] = $child;
                }
            }
            return $entities;
        }

        static function cleanEntities(array $entities) {
            foreach ($entities as $entity) {
                $entity->validUntil = null;
            }
        }



    }
