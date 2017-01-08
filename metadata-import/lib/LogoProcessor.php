<?php


class LogoProcessor {

    private $entries;
    private $picked;

    public function __construct($entries) {
        $this->entries = $entries;
        $this->pick();
    }

    /**
     * Pick the logo entry with the largest size (area)
     */
    private function pick() {
        $max = 0;
        $this->picked = null;
        if (is_array($this->entries) && self::isSequential($this->entries)) {
            $this->picked = $this->entries[0];
            foreach($this->entries AS $entry) {
                if (isset($entry['height']) && isset($entry['width'])) {
                    $area = intval($entry['height']) * intval($entry['width']);
                    if ($area > $max) {
                        $this->picked = $entry;
                        $max = $area;
                    }
                }
            }
        } else if (is_array($this->entries)) {
            $this->picked = $this->entries;
        }
    }

    public function debug() {
        echo "\n\n\n -------- debug \n";
        print_r(count($this->entries));
        echo "\n --- picked --- \n";
        print_r($this->picked);
    }


    public function getLogo() {
        $logo = null;
        if ($this->picked === null) {
            return $logo;
        }
        try {
            $logo = self::getLogoContentResized($this->picked);
        } catch (Exception $e) {
            error_log("Error loading logo from " . var_export($this->picked, true));
            echo $e->getMessage() . "\n";
            // print_r($e);
        }
        return $logo;
    }


    protected static function isSequential(array $arr) {
        if (array() === $arr) return true;
        return array_keys($arr) === range(0, count($arr) - 1);
    }

    protected static function getLogoContentResized($entry) {

        $rawimg = self::getLogoContent($entry);

        if ($rawimg === null) {
            // echo "Got null as image\n";
            // die("a");
            return null;
        }

        // echo "logo content i s" . $rawimg . "\n\n";

        $logoDir = '/tmp';
        $cleanUp = true;
        if (getenv('LOGODIR')) {
            $logoDir = getenv('LOGODIR');
            $cleanUp = false;
        }

        $imgOrgFile = tempnam($logoDir, 'logo-org-');
        $imgNewFile = tempnam($logoDir, 'logo-rsz-');
        file_put_contents($imgOrgFile, $rawimg);

        // echo "about to load image " . $imgOrgFile . "\n";
        // echo "rawimage : " . var_export($rawimg, true) . "\n";
        $image = new SimpleImage();
        $image->load($imgOrgFile);
        $image->fillSquare();
        $image->resize(64, 64);
        $image->save($imgNewFile);

        $res = file_get_contents($imgNewFile);

        unlink($imgOrgFile);
        if ($cleanUp) {
            unlink($imgNewFile);
        }

        return $res;
    }


    protected static function getLogoContent($entry) {
        // echo "about to get content from ";
        // print_r($entry);
        if (!isset($entry['url'])) {
            throw new \Exception('Missing logo URL. URL parameter not present in logo metadata entry.');
        }
        $embedded = self::isValidEmbedded($entry['url']);

        // echo "result from embedded is " . $embedded;

        if ($embedded !== null) {
            return $embedded;
        }
        if (!self::isValidURL($entry['url'])) {
            return new \Exception('Logo URL was not valid: ' . $entry['url']);
        }
        $rawimg = self::url_get_contents($entry['url']);
        return $rawimg;
    }

    /*
     * Check whether a src string is a valid embedded image or not.
     * Returns null if not.
     */
    protected static function isValidEmbedded($src) {
        if (strpos($src, 'data:image') === 0) {
            $splitted = explode(',', $src);
            $check = base64_decode($splitted[1]);
            if ($check === false) {
            	// DiscoUtils::debug('Skipping logo containing a misformatted embedded logo');
            	return null;
            }
            return $check;
        } else {
            return null;
        }
    }

    protected static function isValidURL($src) {
		if (filter_var($src, FILTER_VALIDATE_URL) === false) return false;

		// A valid URL
		$p = parse_url($src);
		if (!in_array(strtolower($p['scheme']), array('http', 'https'))) {
			// DiscoUtils::debug('Skipping URL to logo because it is not a valid scheme. Only http and https is valid.');
			return false;
		}

		return true;
	}

    protected static function url_get_contents ($Url) {
        if (!function_exists('curl_init')){
            throw new \Exception('CURL is not installed! Will not download logo.');
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $Url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    	curl_setopt($ch, CURLOPT_TIMEOUT, 10); //timeout in seconds
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
    	// curl_setopt($ch, CURLOPT_SSLVERSION, 3);
        $output   = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($output === false) {
        	$err = curl_error($ch);
            curl_close($ch);
            throw new \Exception('Error downloading data from ' . $Url . ": " . $err);
        }
        curl_close($ch);

        if ($httpcode !== 200) {
            throw new \Exception('Error downloading data from ' . $Url . ": HTTP Status code " . $httpcode);
        }
        return $output;
    }

}
