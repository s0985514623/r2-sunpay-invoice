<?php
/**
 * Suntech Buysafe Handler
 * 紅陽信用卡付款處理(退刷) Class
 */

// 禁用該文件的SnakeCase檢查
// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
// phpcs:disable WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase
declare(strict_types=1);

namespace J7\R2SunpayInvoice\ApiHandler;

if (class_exists('J7\R2SunpayInvoice\ApiHandler\SuntechBuysafeHandler')) {
	return;
}

/**
 * Class SuntechBuysafeHandler
 */
final class SuntechBuysafeHandler {
	use \R2SunpayInvoice\vendor\J7\WpUtils\Traits\SingletonTrait;

	/**
	 * Refund class
	 * 退刷信用卡
	 *
	 * @param \WC_Order        $order 訂單Object
	 * @param \WC_Order_Refund $refund_order 退費訂單Object
	 * @return \WP_Error | string | void
	 */
	public function refund( $order, $refund_order ) {
		$is_testmode = get_option( 'wc_woomp_sunpay_invoice_testmode_enabled' )==='yes';
		$api_url     = $is_testmode ? 'https://test.esafe.com.tw/Service/Hx_CardRefund.ashx' : 'https://www.esafe.com.tw/Service/Hx_CardRefund.ashx';
		// 取得紅陽信用卡付款方式物件
		$payment_gateways = \WC()->payment_gateways()->get_available_payment_gateways();
		if (!isset( $payment_gateways['suntech_buysafe'] )) {
			return;
		}
		$suntech_buysafe_gateway = $payment_gateways['suntech_buysafe'];
		// 訂單編號
		$order_id =strval( $order->get_id());
		// 信用卡商店代號
		$web =strval($suntech_buysafe_gateway->get_option( 'web_value' ));
		// 交易密碼
		$password = strval($suntech_buysafe_gateway->get_option( 'web_password_value' ));
		// 交易金額(輸出為負數字串,先轉為float再取絕對值)
		$MN = abs(floatval($refund_order->get_total()));
		// 紅陽交易編號
		/** @var string|null $metaValue */
		$metaValue = \get_post_meta( $order->get_id(), '_transaction_id', true );
		$buysafeno = strval($metaValue);
		// 退貨原因
		$RefundMemo =empty($refund_order->get_reason())?'退貨':$refund_order->get_reason();
		// 交易檢查碼
		$ChkValue  = $this->get_chk_value( $web, $password, $buysafeno, strval($MN), $order_id );
		$post_data =[
			'web'        => $web,
			'MN'         => $MN,
			'buysafeno'  => $buysafeno,
			'Td'         => $order_id,
			'RefundMemo' => $RefundMemo,
			'ChkValue'   => $ChkValue,
		];
		try {
			// code...
			$response = wp_remote_post(
			$api_url,
			[
				'body'    => $post_data,
				'headers' => [
					'Content-Type' => 'application/x-www-form-urlencoded',
				],
			]
				);
			if ( is_wp_error( $response ) ) {
				$error_message = $response->get_error_message();
				return "Something went wrong: $error_message";
			} else {
				$body = wp_remote_retrieve_body( $response );
				// 處理 $data 變量
				return $body;
			}
		} catch (\Exception $e) {
			// throw $th;
			// 例外錯誤處理.
			return new \WP_Error( 'error', $e->getMessage() );
		}
	}
	/**
	 * 取得交易檢查碼
	 * 交易檢查碼使用SHA 256雜湊函數產生。
	 *
	 * @param string $web 信用卡商店代號
	 * @param string $password 交易密碼
	 * @param string $buysafeno 紅陽交易編號
	 * @param string $MN 交易金額
	 * @param string $order_id 訂單編號
	 * @return string
	 */
	private function get_chk_value( $web, $password, $buysafeno, $MN, $order_id ) {
		$value = $web . $password . $buysafeno . $MN . $order_id;
		return hash( 'sha256', $value );
	}
}
