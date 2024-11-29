<?php
/**
 * Admin
 * 管理自動開立發票or手動開立 Class
 */

declare(strict_types=1);

namespace J7\R2SunpayInvoice\Admin;

use J7\R2SunpayInvoice\ApiHandler\SunpayInvoiceHandler;
use J7\R2SunpayInvoice\ApiHandler\SuntechBuysafeHandler;

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
		// 增加退費時信用卡退刷API
		add_action( 'woocommerce_order_refunded', [ $this, 'refund_invoice' ], 10, 2 );
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
	/**
	 * 退費時退刷信用卡
	 *
	 * @param int $order_id 訂單ID
	 * @param int $refund_id 退費訂單ID
	 * @return void
	 */
	public function refund_invoice( $order_id, $refund_id ) {
		if (!\wc_get_order( $order_id ) || !\wc_get_order( $refund_id )) {
			return;
		}
		/** @var \WC_Order $order */
		$order = \wc_get_order( $order_id );
		/** @var \WC_Order_Refund $refund_order */
		$refund_order = \wc_get_order( $refund_id );
		// 取得付款方式
		$payment_method = $order->get_payment_method();
		// 如果非紅陽信用卡付款方式則不處理
		if ( 'suntech_buysafe' !== $payment_method ) {
			return;
		}
		$suntech_buysafe = SuntechBuysafeHandler::instance();
		$response        = $suntech_buysafe->refund( $order, $refund_order );
		// 成功退刷會返回E0
		if ( $response === 'E0' ) {
			$order->add_order_note(
				'信用卡退刷申請成功'
			);
		} elseif ( is_wp_error( $response )) {
			// 取得錯誤訊息
			$error_message = $response->get_error_message();
			$order->add_order_note(
				"退款失敗：錯誤訊息 - {$error_message}"
				);
		} elseif (empty($response)) {
			$order->add_order_note(
				'退款失敗：無紅陽信用卡付款方式'
			);
		} else {
			$order->add_order_note(
				"退款失敗：錯誤訊息 - {$response}"
			);
		}
	}
}
