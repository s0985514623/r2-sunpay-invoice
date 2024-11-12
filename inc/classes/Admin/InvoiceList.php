<?php
/**
 * Get Invoice List
 * 查詢發票列表 Class
 */

declare(strict_types=1);

namespace J7\R2SunpayInvoice\Admin;

use J7\R2SunpayInvoice\ApiHandler\SunpayInvoiceHandler;
use J7\R2SunpayInvoice\Plugin;
use J7\R2SunpayInvoice\Bootstrap;

if (class_exists('J7\R2SunpayInvoice\FrontEnd\InvoiceList')) {
	return;
}
/**
 * Class Entry
 */
final class InvoiceList {
	use \J7\WpUtils\Traits\SingletonTrait;

	/**
	 * Constructor
	 */
	public function __construct() {
		\add_action('admin_menu', [ $this, 'add_invoice_list_menu' ]);
	}
	/**
	 * Add Invoice List Menu
	 */
	public function add_invoice_list_menu() {
		\add_menu_page(
		__('查詢發票', 'r2-sunpay-invoice'),
		__('查詢發票', 'r2-sunpay-invoice'),
		'manage_options',
		'r2-sunpay-invoice-list',
		[ $this, 'invoice_list_page' ],
		'dashicons-media-spreadsheet',
		6
		);
	}
	/**
	 * Invoice List Page
	 */
	public function invoice_list_page() {
		Bootstrap::enqueue_script();
		echo '<div id="' . Plugin::$snake . '" class="my-app"></div>';
	}
}
