<?php



function getconfig($name, $default = null) {
	$val = getenv($name);
	if ($val === false) {
		return $default;
	}
	return $val;
}

/**
 * A Cassandra (database) datastore for metadata
 */
class Store {
	/**
	 * The Database object.
     *
	 * @var DB
	 */
    public $db;
	/**
	 * Initialize the SQL datastore.
	 */
	public function __construct() {
		$config = [];
		$keyspace 	= getconfig('CASSANDRA_KEYSPACE', 'metadata');
		$nodes 		= explode(' ', getconfig('CASSANDRA_NODES'));
		$use_ssl    = boolval( getconfig('CASSANDRA_USESSL', false) );
		$ssl_ca     = getconfig('CASSANDRA_CA');
		$username   = getconfig('CASSANDRA_USERNAME');
		$password   = getconfig('CASSANDRA_PASSWD');

		$cluster = \Cassandra::cluster()
				 ->withContactPoints(implode(',', $nodes))
				 ->withDefaultConsistency(\Cassandra::CONSISTENCY_LOCAL_QUORUM);
		if (isset($username) && isset($password)) {
			$cluster = $cluster->withCredentials($username, $password);
		}
		if ($use_ssl) {
			$ssl = \Cassandra::ssl()
				 ->withVerifyFlags(\Cassandra::VERIFY_PEER_CERT);
			if ($ssl_ca) {
				$ssl = $ssl->withTrustedCerts($ssl_ca);
			}
			$ssl = $ssl->build();
			$cluster = $cluster->withSSL($ssl);
		}
		$cluster = $cluster->build();
		$this->db = $cluster->connect($keyspace);
	}
	/**
	 * Convert long keys to something we can fit in the database table
	 */
	// private function dbKey($key) {
	// 	if (strlen($key) > 50) {
	// 		$key = sha1($key);
	// 	}
	// 	return $key;
	// }
	/**
	 * Retrieve a value from the datastore.
	 *
	 * @param string $type  The datatype.
	 * @param string $key  The key.
	 * @return mixed|NULL  The value.
	 */
	// public function get($type, $key) {
	// 	assert('is_string($type)');
	// 	assert('is_string($key)');
	// 	$key = $this->dbKey($key);
	// 	$query = ' SELECT value FROM "session" WHERE type = :type AND key = :key';
	// 	$params = array('type' => $type, 'key' => $key);
	// 	// echo "<pre>About to perform a query \n"; print_r($query); echo "\n"; print_r($params);
	// 	// echo "\n\n";
	// 	// debug_print_backtrace();
	// 	// echo "\n------\n\n";
	// 	// exit;
	// 	// $result = $this->db->query($query, $params);
	// 	$statement = new \Cassandra\SimpleStatement($query);
	// 	$options = new \Cassandra\ExecutionOptions([
	// 		'arguments' => $params,
	// 		'consistency' => \Cassandra::CONSISTENCY_QUORUM,
	// 	]);
	// 	try {
	// 		$response = $this->db->execute($statement, $options);
	// 	} catch (\Cassandra\Exception $e) {
	// 		error_log("Received cassandra exception in get: " . $e);
	// 		throw $e;
	// 	}
	// 	if (count($response) < 1) return null;
	// 	$data = $response[0];
	// 	$value = $data["value"];
	// 	$value = urldecode($value);
	// 	$value = unserialize($value);
	// 	if ($value === FALSE) {
	// 		return NULL;
	// 	}
	// 	return $value;
	// }
	/**
	 * Save a value to the datastore.
	 *
	 * @param string $type  The datatype.
	 * @param string $key  The key.
	 * @param mixed $value  The value.
	 * @param int|NULL $expire  The expiration time (unix timestamp), or NULL if it never expires.
	 */
     public function insert($feed, $entityId, $metadata, $uimeta, $reg, $opUpdate = false) {

         assert('is_string($feed)');
         assert('is_string($entityId)');
         assert('is_array($metadata)');
         // $key = $this->dbKey($key);
         $metadataJSON = json_encode($metadata, true);
         $uimetaJSON = json_encode($uimeta, true);
         $query = 'INSERT INTO "entities" (feed, entityid, metadata, uimeta, reg, enabled, ' . ($opUpdate ? 'updated' : 'created') . ') VALUES (:feed, :entityid, :metadata, :uimeta, :reg, :enabled, :ts)';
         // echo "About to insert \n"; print_r($query); print_r($params); echo "\n\n";
         // $result = $this->db->query($query, $params);
         $statement = new \Cassandra\SimpleStatement($query);
         $params = [
			 'feed' => $feed,
			 'entityid' => $entityId,
			 'metadata' => $metadataJSON,
             'uimeta' => $uimetaJSON,
			 'reg' => $reg,
			 'enabled' => true,
			 'ts' => new \Cassandra\Timestamp(),
		 ];
         $options = new \Cassandra\ExecutionOptions([
             'arguments' => $params,
             'consistency' => \Cassandra::CONSISTENCY_QUORUM,
         ]);
         try {
             $this->db->execute($statement, $options);
         } catch (\Cassandra\Exception $e) {
             error_log("Received cassandra exception in set: " . $e);
             throw $e;
         }
     }


     public function getFeed($feed) {
         assert('is_string($feed)');
         // $key = $this->dbKey($key);

         $query = 'SELECT entityid, feed, enabled, verification, metadata, uimeta, reg, created, updated FROM "entities" WHERE feed = :feed ALLOW FILTERING';
         $params = array('feed' => $feed);

         // echo "<pre>About to perform a query \n"; print_r($query); echo "\n"; print_r($params);
         // echo "\n\n";
         // debug_print_backtrace();
         // echo "\n------\n\n";
         // exit;
         // $result = $this->db->query($query, $params);

         $statement = new \Cassandra\SimpleStatement($query);
         $options = new \Cassandra\ExecutionOptions([
             'arguments' => $params,
             'consistency' => \Cassandra::CONSISTENCY_QUORUM,
         ]);
         try {
             $response = $this->db->execute($statement, $options);
         } catch (\Cassandra\Exception $e) {
             error_log("Received cassandra exception in get: " . $e);
             throw $e;
         }
         // if (count($response) < 1) return [];
         $res = [];
         foreach($response AS $row) {
             $row['metadata'] = json_decode($row['metadata'], true);
             $row['uimeta'] = json_decode($row['uimeta'], true);
             $row['verification'] = json_decode($row['verification'], true);
             $row['created'] = (isset($row['created']) ? $row['created']->time() : null);
             $row['updated'] = (isset($row['updated']) ? $row['updated']->time() : null);
             $res[$row['entityid']] = $row;
         }
         //  print_r($res);
         return $res;

     }

	/**
	 * Delete a value from the datastore.
	 *
	 * @param string $feed  Feed.
	 * @param string $entityId  Entityid
	 */
	public function delete($feed, $entityId) {
		assert('is_string($feed)');
		assert('is_string($entityId)');


		$params = [
			"feed" 	=> $feed,
			"entityid"	=> $entityId
		];
		$query = 'DELETE FROM "entities" WHERE feed = :feed AND entityid = :entityid';
		// echo "About to delete \n"; print_r($query); print_r($params); echo "\n\n";
		// $result = $this->db->query($query, $params);
		$statement = new \Cassandra\SimpleStatement($query);
		$options = new \Cassandra\ExecutionOptions([
			'arguments' => $params,
			'consistency' => \Cassandra::CONSISTENCY_QUORUM,
		]);
		try {
			$this->db->execute($statement, $options);
		} catch (\Cassandra\Exception $e) {
			error_log("Received cassandra exception in delete: " . $e);
			throw $e;
		}
	}



	/**
	 * Delete a value from the datastore.
	 *
	 * @param string $feed  Feed.
	 * @param string $entityId  Entityid
	 */
	public function softDelete($feed, $entityId) {
		assert('is_string($feed)');
		assert('is_string($entityId)');

		$query = 'INSERT INTO "entities" (feed, entityid, enabled, updated) VALUES (:feed, :entityid, :enabled, :ts)';
		$statement = new \Cassandra\SimpleStatement($query);
		$params = [
			'feed' => $feed,
			'entityid' => $entityId,
			'enabled' => false,
			'ts' => new \Cassandra\Timestamp(),
		];
		$options = new \Cassandra\ExecutionOptions([
			'arguments' => $params,
			'consistency' => \Cassandra::CONSISTENCY_QUORUM,
		]);
		try {
			$this->db->execute($statement, $options);
		} catch (\Cassandra\Exception $e) {
			error_log("Received cassandra exception in set: " . $e);
			throw $e;
		}
	}

}
