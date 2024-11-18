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
		add_action( 'wp_ajax_get_invoice_list', [ $this, 'get_invoice_list' ] );
		add_action( 'wp_ajax_get_orders_list', [ $this, 'get_orders_list' ] );
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
		$order        = wc_get_order( $order_id );
		$order->update_meta_data( '_sunpay_invoice_data', $invoice_data );
		$order->save();

		if ( ! empty( $order_id ) ) {
			// $msg = 'test';
			$msg = $this->invoice_handler->generate_invoice( $order_id );
			echo $msg;
		}
		wp_die();
	}
	/**
	 * Invalid Invoice
	 *
	 * @return void
	 */
	public function invalid_invoice() {
		// Security check
		\check_ajax_referer(Plugin::$kebab, 'nonce');
		$order_id = isset($_POST['orderId'])?intval( sanitize_text_field( wp_unslash($_POST['orderId']) ) ):'';
		$content  = isset($_POST['content'])?sanitize_text_field( wp_unslash($_POST['content']) ):'';
		if ( ! empty( $order_id ) ) {
			$msg = $this->invoice_handler->invalid_invoice( $order_id, $content );
			// $msg = $content;
			echo $msg;
		}
		// $order = wc_get_order( $order_id );
		// $order->update_meta_data( '_sunpay_invoice_status', '0' );
		// $order->save();
		wp_die();
	}
	/**
	 * Get Invoice List
	 */
	public function get_invoice_list() {
		// Security check
		\check_ajax_referer(Plugin::$kebab, 'nonce');
		$invoice_numbers = isset($_POST['invoiceNumbers'])? sanitize_text_field(wp_unslash($_POST['invoiceNumbers'])):'';
		$order_no        = isset($_POST['orderNos'])? sanitize_text_field(wp_unslash( $_POST['orderNos'] )):'';
		// 紅陽API 的order_no自訂編號只能單筆獲取，若要多筆的話則多次發送單筆API
		$order_no_array        =explode(',', $order_no);
		$invoice_numbers_array =explode(',', $invoice_numbers);
		$invoice_list_data     = [];
		// 以order_no_array為基準，逐筆取得invoice_list
		foreach ($order_no_array as $order_no) {
			$invoice_list = $this->invoice_handler->get_invoice_list( $invoice_numbers_array, $order_no );
			// 回傳值都是成功,失敗筆數紀錄在data中
			if ('SUCCESS'===$invoice_list['status']) {
				if (empty($invoice_list['result'])) {
					$invoice_list_data['result'][] =[
						'orderNo'       =>$order_no,
						'invoiceNumber' =>'尚未開立',
					];
				} else {
					foreach ($invoice_list['result'] as $result) {
						$invoice_list_data['result'][] =$result;
					}
				}
			} elseif (400 === $invoice_list['status']) {
				$invoice_list_data['result'][] =[
					'orderNo'       =>$order_no,
					'invoiceNumber' =>'尚未開立',
				];
			}
		}
		$return = [
			'message' => 'success',
			'data'    => $invoice_list_data,
		];
		\wp_send_json($return);
		\wp_die();

		// $invoice_list = $this->invoice_handler->get_invoice_list( $invoice_numbers, '3670' );

		// if (400 === $invoice_list['status']) {
		// $return = [
		// 'message' => 'error',
		// 'data'    => $invoice_list,
		// ];
		// \wp_send_json($return);
		// \wp_die();
		// } else {
		// $return = [
		// 'message' => 'success',
		// 'data'    => $invoice_list,
		// ];
		// \wp_send_json($return);
		// \wp_die();
		// }
	}
	/**
	 * Get OrderNo And InvoiceNo From DateRange
	 */
	public function get_orders_list() {
		// Security check
		\check_ajax_referer(Plugin::$kebab, 'nonce');
		$start_date = isset($_POST['startDate'])?sanitize_text_field( wp_unslash($_POST['startDate']) ):'';
		$end_date   = isset($_POST['endDate'])?sanitize_text_field( wp_unslash($_POST['endDate']) ):'';
		// 使用 wc_get_orders 取得所有商品
		$args = [
			'limit'        => -1, // -1 表示取得所有商品
			'paged'        => 1,
			'status'       => [ 'completed', 'processing' ], // 取得已完成及處理中的訂單
			'date_created' => $start_date . '...' . $end_date, // 前端送來格式為YYYY-MM-DD
		];
		// 取得訂單
		$orders     =wc_get_orders($args);
		$order_no   =[];
		$invoice_no =[];
		foreach ($orders as $order) {
			$order_no[] = $order->get_order_number();
			if (!empty($order->get_meta('_sunpay_invoice_number'))) {
				$invoice_no[] = $order->get_meta('_sunpay_invoice_number');
			}
		}

		$return = [
			'message' => 'success',
			'data'    => [
				'order_no'   => $order_no,
				'invoice_no' => $invoice_no,
			],
		];
		\wp_send_json($return);
		\wp_die();
	}
}
