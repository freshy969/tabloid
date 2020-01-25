<?php

/*
	Interface for drivers of caching system.
*/

/**
 * Interface for caching drivers.
 */
interface Q2A_Storage_CacheDriver
{
	/**
	 * Get the cached data for the supplied key. Data can be any format but is usually an array.
	 * @param string $key The unique cache identifier.
	 *
	 * @return mixed The cached data, or null otherwise.
	 */
	public function get($key);

	/**
	 * Store something in the cache along with the key and expiry time. Data gets 'serialized' to a string before storing.
	 * @param string $key The unique cache identifier.
	 * @param mixed $data The data to cache (in core Q2A this is usually an array).
	 * @param int $ttl Number of minutes for which to cache the data.
	 *
	 * @return bool Whether the file was successfully cached.
	 */
	public function set($key, $data, $ttl);

	/**
	 * Delete an item from the cache.
	 * @param string $key The unique cache identifier.
	 *
	 * @return bool Whether the operation succeeded.
	 */
	public function delete($key);

	/**
	 * Delete multiple items from the cache.
	 * @param int $limit Maximum number of items to process. 0 = unlimited
	 * @param int $start Offset from which to start (used for 'batching' deletes).
	 * @param bool $expiredOnly Delete cache only if it has expired.
	 *
	 * @return int Number of files deleted.
	 */
	public function clear($limit = 0, $start = 0, $expiredOnly = false);

	/**
	 * Whether caching is available.
	 *
	 * @return bool
	 */
	public function isEnabled();

	/**
	 * Get the last error.
	 *
	 * @return string
	 */
	public function getError();

	/**
	 * Get the prefix used for all cache keys.
	 *
	 * @return string
	 */
	public function getKeyPrefix();

	/**
	 * Get current statistics for the cache.
	 *
	 * @return array Array of stats: 'files' => number of files, 'size' => total file size in bytes.
	 */
	public function getStats();
}
