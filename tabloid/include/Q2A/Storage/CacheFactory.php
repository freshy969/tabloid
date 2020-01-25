<?php

/*
	Handler for caching system.
*/

/**
 * Caches data (typically from database queries) to the filesystem.
 */
class Q2A_Storage_CacheFactory
{
	private static $cacheDriver = null;

	/**
	 * Get the appropriate cache handler.
	 * @return Q2A_Storage_CacheDriver The cache handler.
	 */
	public static function getCacheDriver()
	{
		if (self::$cacheDriver === null) {
			$config = array(
				'enabled' => (int) qa_opt('caching_enabled') === 1,
				'keyprefix' => DB_NAME . '.' . TABLE_PREFIX . '.',
				'dir' => defined('CACHE_DIRECTORY') ? CACHE_DIRECTORY : null,
			);

			$driver = qa_opt('caching_driver');

			switch($driver)
			{
				case 'memcached':
					self::$cacheDriver = new Q2A_Storage_MemcachedDriver($config);
					break;

				case 'filesystem':
				default:
					self::$cacheDriver = new Q2A_Storage_FileCacheDriver($config);
					break;
			}

		}

		return self::$cacheDriver;
	}
}
