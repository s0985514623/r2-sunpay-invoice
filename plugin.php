<?php
/**
 * Plugin Name:       R2 Sunpay Invoice
 * Plugin URI:        https://github.com/s0985514623/r2-sunpay-invoice
 * Description:       紅陽科技發票串接
 * Version:           1.0.7
 * Requires at least: 5.7
 * Requires PHP:      8.0
 * Author:            Ren
 * Author URI:        https://github.com/s0985514623
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       r2_sunpay_invoice
 * Domain Path:       /languages
 * Tags: your tags
 */

declare(strict_types=1);

namespace J7\R2SunpayInvoice;

if (\class_exists('J7\R2SunpayInvoice\Plugin')) {
	return;
}

require_once __DIR__ . '/vendor/autoload.php';

/**
 * Class Plugin
 */
final class Plugin {

	use \J7\WpUtils\Traits\PluginTrait;
	use \J7\WpUtils\Traits\SingletonTrait;

	/**
	 * Constructor
	 */
	public function __construct() {
		// if your plugin depends on other plugins, you can add them here
		$this->required_plugins = [
			[
				'name'     => 'WooCommerce',
				'slug'     => 'woocommerce',
				'required' => true,
				'version'  => '7.6.0',
			],
		];

		$this->init(
			[
				'app_name'    => 'R2 Sunpay Invoice',
				'github_repo' => 'https://github.com/s0985514623/r2-sunpay-invoice',
				'callback'    => [ Bootstrap::class, 'instance' ],
			]
		);
	}
}

Plugin::instance();
