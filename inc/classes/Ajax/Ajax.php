<?php
/**
 * Ajax php
 */

declare(strict_types=1);

namespace J7\R2SunpayInvoice\Ajax;

use J7\R2SunpayInvoice\Plugin;
use J7\R2SunpayInvoice\ApiHandler\SunpayInvoiceHandler;

if (class_exists('J7\R2SunpayInvoice\Ajax\Ajax')) {
	return;
}

/**
 * Class Ajax
 */
final class Ajax {
	use \J7\WpUtils\Traits\SingletonTrait;

	/**
	 * Invoice Handler
	 *
	 * @var InvoiceHandler
	 */
	private $invoice_handler;
	/**
	 * Construct
	 */
	public function __construct() {
		add_action( 'wp_ajax_gen_invoice', [ $this, 'generate_invoice' ] );
		add_action( 'wp_ajax_invalid_invoice', [ $this, 'invalid_invoice' ] );
		$this->invoice_handler = SunpayInvoiceHandler::instance();
	}

	/**
	 * Generate Invoice
	 *
	 * @return void
	 */
	public function generate_invoice() {

		// Security check
		\check_ajax_referer(Plugin::$kebab, 'nonce');

		$order_id = isset($_POST['orderId'])?intval( sanitize_text_field( wp_unslash($_POST['orderId']) ) ):'';

		$invoice_data = [
			'_invoice_type'         => isset($_POST['_invoice_type'])?sanitize_text_field(wp_unslash($_POST['_invoice_type'])  ):'',
			'_invoice_individual'   => isset($_POST['_invoice_individual'])?sanitize_text_field(wp_unslash($_POST['_invoice_individual'])  ):'',
			'_invoice_carrier'      => isset($_POST['_invoice_carrier'])?sanitize_text_field(wp_unslash($_POST['_invoice_carrier'])  ):'',
			'_invoice_company_name' => isset($_POST['_invoice_company_name'])?sanitize_text_field(wp_unslash($_POST['_invoice_company_name'])  ):'',
			'_invoice_tax_id'       => isset($_POST['_invoice_tax_id']) ?sanitize_text_field(wp_unslash($_POST['_invoice_tax_id']) ):'',
			'_invoice_donate'       => isset($_POST['_invoice_donate'])?sanitize_text_field(wp_unslash($_POST['_invoice_donate'])  ):'',
		];
		$order = wc_get_order( $order_id );
		$order->update_meta_data( '_sunpay_invoice_data', $invoice_data );
		$order->save();

		if ( ! empty( $order_id ) ) {
			// $msg = 'test';
			$msg = $this->invoice_handler->generate_invoice( $order_id );
			echo $msg;
		}
		wp_die();
	}
}
