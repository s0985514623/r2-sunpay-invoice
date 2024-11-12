<?php
/**
 * Admin
 * 管理自動開立發票or手動開立 Class
 */

declare(strict_types=1);

namespace J7\R2SunpayInvoice\Admin;

use J7\R2SunpayInvoice\ApiHandler\SunpayInvoiceHandler;

if (class_exists('J7\R2SunpayInvoice\Admin\Admin')) {
	return;
}
/**
 * Class Entry
 */
final class Admin {
	use \J7\WpUtils\Traits\SingletonTrait;

	/**
	 * Constructor
	 */
	public function __construct() {
		if ( 'auto' === get_option( 'wc_woomp_sunpay_invoice_issue_mode' ) ) {
			$invoice_issue_at = str_replace( 'wc-', '', get_option( 'wc_woomp_sunpay_invoice_issue_at' ) );
			add_action( 'woocommerce_order_status_' . $invoice_issue_at, [ $this, 'issue_invoice' ], 10, 1 );
		}
	}

	/**
	 * 開立發票
	 *
	 * @param int $order_id 訂單ID
	 * @return void
	 */
	public function issue_invoice( $order_id ) {
		$order = \wc_get_order( $order_id );

		if ( $order->get_meta( '_sunpay_invoice_status' ) === '0' || $order->get_meta( '_ecpay_invoice_number' ) === '' ) {
			$invoice = SunpayInvoiceHandler::instance();
			$invoice->generate_invoice( $order_id );
		}
	}
}
