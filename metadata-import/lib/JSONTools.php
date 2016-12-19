<?php
/**
 * Tool for doing diffs of nested arrays
 *
 * @author wrey75@gmail.com
 *
 */
class JSONTools {

	const OLD_ITEM = 'old';
	const NEW_ITEM = 'new';

	/**
	 * Compute the difference between $arr1 and $arr2.
	 * The result is an array (can be empty) giving the
	 * differences between the 2 arrays.
	 *
	 * Note the differences are expressed in a form you
	 * can use the resulting array to "patch" $arr1 to
	 * obtain $arr2.
	 *
	 * This is basically the same stuff than for "diff"
	 * command (from UNIX).
	 *
	 * @param array $arr1 the first array
	 * @param array $arr2 the second array
	 */
	public static function diff($arr1, $arr2) {
		$diff = array();

		// Check the similarities
		foreach( $arr1 as $k1=>$v1 ){
			if( isset( $arr2[$k1]) ){
				$v2 = $arr2[$k1];
				if( is_array($v1) && is_array($v2) ){
					// 2 arrays: just go further...
					// .. and explain it's an update!
					$changes = self::diff($v1, $v2);
					if( count($changes) > 0 ){
						// If we have no change, simply ignore
						$diff[$k1] = array('upd' => $changes);
					}
					unset($arr2[$k1]); // don't forget
				}
				else if( $v2 === $v1 ){
					// unset the value on the second array
					// for the "surplus"
					unset( $arr2[$k1] );
				}
				else {
					// Don't mind if arrays or not.
					$diff[$k1] = array( 'old' => $v1, 'new'=>$v2 );
					unset( $arr2[$k1] );
				}
			}
			else {
				// remove information
				$diff[$k1] = array( 'old' => $v1 );
			}
		}

		// Now, check for new stuff in $arr2
		reset( $arr2 ); // Don't argue it's unnecessary (even I believe you)
		foreach( $arr2 as $k=>$v ){
			// OK, it is quite stupid my friend
			$diff[$k] = array( 'new' => $v );
		}
		return $diff;
	}


	/**
	 * Patching is so simple...
	 *
	 * @param unknown $arr
	 * @param unknown $patch
	 */
	public static function patch($arr, $patch) {
		$dest = $arr;
		foreach ($patch as $k=>$v){
			// $k is the key to change
			// $v contains 'old' and 'new'.

			if( !is_array($v) ){
				error_log('$patch is a bad argument.');
			}
			else if( isset( $v['upd'] )){
				// Update...!
				$dest[$k] = self::patch($arr[$k], $v['upd']);
			}
			else if( !isset( $v['new'] )){
				// Remove the entry
				unset( $dest[$k] );
			}
			else {
				// A new value (can be array by the way).
				$dest[$k] = $v['new'];
			}
		}
		return $dest;
	}

}
