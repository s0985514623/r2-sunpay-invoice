<?php
/**
 * Singleton trait
 *
 * @package J7\WpUtils
 */

namespace R2SunpayInvoice\vendor\J7\WpUtils\Traits;

if ( trait_exists( 'SingletonTrait' ) ) {
	return;
}

trait SingletonTrait {

	/**
	 * Singleton instance
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Get the singleton instance
	 *
	 * @param mixed ...$args Arguments
	 *
	 * @return self
	 */
	public static function instance(...$args) { // phpcs:ignore
		// TEST 記得移除
		\J7\WpUtils\Classes\ErrorLog::info('Invoice');
		if ( null === self::$instance ) {
			self::$instance = new self(...$args);
		}

		return self::$instance;
	}
}
