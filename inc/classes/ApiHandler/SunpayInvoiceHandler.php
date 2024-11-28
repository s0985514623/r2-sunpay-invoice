<?php
/**
 * Sunpay Invoice Handler
 */

// 禁用該文件的SnakeCase檢查
// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
declare(strict_types=1);

namespace J7\R2SunpayInvoice\ApiHandler;

if (class_exists('J7\R2SunpayInvoice\ApiHandler\SunpayInvoiceHandler')) {
	return;
}

/**
 * Class SunpayInvoiceHandler
 */
final class SunpayInvoiceHandler {
	use \J7\WpUtils\Traits\SingletonTrait;

	/**
	 * 開立發票
	 *
	 * @param int $order_id 訂單ID
	 *
	 * return string
	 */
	public function generate_invoice( $order_id ) {
		$order       = wc_get_order( $order_id );
		$order_total = $order->get_total();

		if ( '0' === $order_total ) {
			return;
		}

		$order_info   = $order->get_address();
		$invoice_data = $order->get_meta( '_sunpay_invoice_data' );
		$is_testmode  = get_option( 'wc_woomp_sunpay_invoice_testmode_enabled' )==='yes';
		$is_b2b       = $invoice_data['_invoice_type'] === 'company';
		$b2c_api_url  = $is_testmode?'https://testinv.sunpay.com.tw/api/v1/SunPay/CreateInvoiceb2c':'https://inv.sunpay.com.tw/api/v1/SunPay/CreateInvoiceb2c';
		$b2b_api_url  = $is_testmode?'https://testinv.sunpay.com.tw/api/v1/SunPay/CreateInvoiceb2b':'https://inv.sunpay.com.tw/api/v1/SunPay/CreateInvoiceb2b';

		// 訂購人資料
		$buyer_identifier = $is_b2b?$invoice_data['_invoice_tax_id']:'';
		$buyer_name       = $is_b2b?$invoice_data['_invoice_company_name']:$order_info['first_name'] . $order_info['last_name'];
		$buyer_email      = $order->get_billing_email();
		$buyer_phone      = $order->get_billing_phone();
		$buyer_address    = $order_info['address_1'] . $order_info['address_2'];

		// 發票資料
		$donate_mark   = $invoice_data['_invoice_type'] === 'donate'?'1':'0';// 捐贈
		$poban         = $donate_mark ==='1'?$invoice_data['_invoice_donate']:'';// 捐贈碼
		$carrier_type  = 0;// 目前設定為0，皆不提供使用載具
		$is_send_paper = 0;// 目前設定為0，皆不提供紅陽代印發票

		// 課稅別及銷售額
		$tax_type               = 1;// 預設為應稅，若有免稅商品，則更改為9混稅
		$tax_rate               = 0.05;// 預設稅率為5% 若有免稅商品，則更改為undefined
		$tax_amount             = $order->get_total_tax();// 稅額,直接取order中的稅額
		$sales_amount           = 0; // 應稅銷售額，從商品迴圈中累加(含運費)
		$zero_tax_sales_amount  = 0; // 零稅率銷售額，目前不開放設定為0
		$free_tax_sales_amount  = 0; // 免稅銷售額，從商品迴圈中累加
		$total_amount           = $order_total;// 總金額
		$customs_clearance_mark = 0; // 通關方式，目前設定為0，皆不提供通關方式
		$isprint                = 0;// 紙本發票是否列印，目前設定為0，皆不列印
		$shipping_excl_tax      = $order->get_shipping_total();// 運費不含稅
		$shipping_tax           = $order->get_shipping_tax(); // 運費稅額

		// 商品資料及稅率資料
		$product_items =[];
		$items         = $order->get_items();
		/** @var WC_Order_Item_Product $item */
		foreach ($items as $item) {
			$product_items[] = [
				'description' => $item->get_name(),
				'quantity'    => $item->get_quantity(),
				'unitPrice'   => $order->get_item_subtotal( $item, false ), // 取得不含稅價格
				'amount'      => round( (float) $item->get_subtotal(), 0),
				'taxType'     => $item->get_tax_status() ==='none' ? 3 : 1,
			];
			// 若有免稅商品，則更改為混稅，並加總免稅銷售額
			if ($item->get_tax_status()==='none') {
				$tax_type               = 9;
				$free_tax_sales_amount += round( (float) $item->get_subtotal(), 0);
			} else {
				// 否則為應稅商品，加總應稅銷售額
				$sales_amount += round( (float) $item->get_subtotal(), 0);
			}
		}
		// 將運費項目加入商品資料
		if ($shipping_excl_tax > 0) {
			$product_items[] = [
				'description' => '運費',
				'quantity'    => 1,
				'unitPrice'   => $shipping_excl_tax,
				'amount'      => round( (float) $shipping_excl_tax, 0),
				'taxType'     => 1,
			];
			$sales_amount   += round( (float) $shipping_excl_tax, 0);
		}
		try {
			// 1.載入SDK程式
			$sunpay_invoice = new SunpayInvoiceSDK();

			// 2.寫入基本介接參數
			$sunpay_invoice->CompanyID  = get_option('wc_woomp_sunpay_invoice_company_id');
			$sunpay_invoice->merchantID = $is_testmode?'14F8CK87XB':get_option('wc_woomp_sunpay_invoice_merchant_id');
			$sunpay_invoice->HashKey    = $is_testmode?'WF09QRGVZX6R20HS':get_option('wc_woomp_sunpay_invoice_hashkey');
			$sunpay_invoice->HashIV     = $is_testmode?'UBAMHYLNSYY7P0U4':get_option('wc_woomp_sunpay_invoice_hashiv');
			$sunpay_invoice->api_url    = $invoice_data['_invoice_type'] === 'company'?$b2b_api_url:$b2c_api_url;

			// 3.寫入發票資訊
			$sunpay_invoice->send['orderNo']              = (string) $order_id;
			$sunpay_invoice->send['buyerIdentifier']      = $buyer_identifier;
			$sunpay_invoice->send['buyerName']            = $buyer_name;
			$sunpay_invoice->send['buyerEmailAddress']    = $buyer_email;
			$sunpay_invoice->send['taxType']              = $tax_type;
			$sunpay_invoice->send['taxRate']              = $tax_rate;
			$sunpay_invoice->send['taxAmount']            = $tax_amount;
			$sunpay_invoice->send['salesAmount']          = $sales_amount;
			$sunpay_invoice->send['zeroTaxSalesAmount']   = $zero_tax_sales_amount;
			$sunpay_invoice->send['freeTaxSalesAmount']   = $free_tax_sales_amount;
			$sunpay_invoice->send['totalAmount']          = $total_amount;
			$sunpay_invoice->send['customsClearanceMark'] = $customs_clearance_mark;
			$sunpay_invoice->send['isPrint']              = $isprint;
			$sunpay_invoice->send['donateMark']           = $donate_mark;
			$sunpay_invoice->send['poban']                = $poban;
			$sunpay_invoice->send['carrierType']          = $carrier_type;
			$sunpay_invoice->send['carrierId1']           = '';
			$sunpay_invoice->send['IsSendPaper']          = $is_send_paper;
			$sunpay_invoice->send['buyerTelephoneNumber'] = $buyer_phone;
			$sunpay_invoice->send['buyerAddress']         = $buyer_address;
			$sunpay_invoice->send['productItems']         = $product_items;

			// 4.送出
			$return_info = $sunpay_invoice->invoice_send();

			// 於備註區寫入發票資訊
			$invoice_date    =$return_info['result']['crT_DAT'];
			$invoice_number  =$return_info['result']['invoiceNumber'];
			$invoice_message =$return_info['status'];
			$invocie_result  = ( $invoice_date ) ? __( '<b>Invoice issue result</b>', 'r2-sunpay-invoice' ) : __( '<b>Invoice issue faild</b>', 'r2-sunpay-invoice' );
			$invocie_time    = ( $invoice_date ) ? __( '<br>Generate Time: ', 'r2-sunpay-invoice' ) . $invoice_date : '';
			$invocie_number  = ( $invoice_date ) ? __( '<br>Invoice Number: ', 'r2-sunpay-invoice' ) . $invoice_number : '';
			if (isset( $return_info['status'] ) && $return_info['status'] === 'SUCCESS' ) {
				$invoice_msg = __( '<br>Invoice Message: ', 'r2-sunpay-invoice' ) . $invoice_message;
			} elseif (isset( $return_info['status'] ) && $return_info['status'] === 'ERROR' ) {
				$invoice_msg = __( '<br>Invoice Message: ', 'r2-sunpay-invoice' ) . $return_info['message'];
			} else {
				$invoice_msg = '';
			}

			$order->add_order_note( $invocie_result . $invocie_time . $invocie_number . $invoice_msg );

			// 寫入發票回傳資訊
			if ( isset( $return_info['status'] ) && $return_info['status'] === 'SUCCESS' ) {
				// 異動已經開立發票的狀態 1.已經開立 0.尚未開立
				$order->update_meta_data( '_sunpay_invoice_status', 1 );
				// 寫入發票號碼
				$order->update_meta_data( '_sunpay_invoice_number', $invoice_number);
				$order->save();
			}
			return $invoice_message;
		} catch (\Exception $e) {
			// 例外錯誤處理.
			return new \WP_Error( 'error', $e->getMessage() );
		}
	}
	/**
	 * 作廢發票
	 *
	 * @param int    $order_id 訂單ID
	 * @param string $content 作廢原因
	 *
	 * return string
	 */
	public function invalid_invoice( $order_id, $content ) {
		$order       = wc_get_order( $order_id );
		$order_total = $order->get_total();

		if ( '0' === $order_total ) {
			return;
		}
		$is_testmode    = get_option( 'wc_woomp_sunpay_invoice_testmode_enabled' )==='yes';
		$api_url        = $is_testmode?'https://testinv.sunpay.com.tw/api/v1/SunPay/CreateInvoiceInvalid':'https://inv.sunpay.com.tw/api/v1/SunPay/CreateInvoiceInvalid';
		$invoice_number = $order->get_meta( '_sunpay_invoice_number' );
		try {
			// 1.載入SDK程式
			$sunpay_invoice = new SunpayInvoiceSDK();

			// 2.寫入基本介接參數
			$sunpay_invoice->CompanyID  = get_option('wc_woomp_sunpay_invoice_company_id');
			$sunpay_invoice->merchantID = $is_testmode?'14F8CK87XB':get_option('wc_woomp_sunpay_invoice_merchant_id');
			$sunpay_invoice->HashKey    = $is_testmode?'WF09QRGVZX6R20HS':get_option('wc_woomp_sunpay_invoice_hashkey');
			$sunpay_invoice->HashIV     = $is_testmode?'UBAMHYLNSYY7P0U4':get_option('wc_woomp_sunpay_invoice_hashiv');
			$sunpay_invoice->api_url    = $api_url;

			// 3.寫入發票資訊
			$sunpay_invoice->send                  =[];// 清空send中的資料
			$sunpay_invoice->send['invoiceNumber'] = $invoice_number;
			$sunpay_invoice->send['cancelReason']  = $content;

			// 4.送出
			$return_info = $sunpay_invoice->invoice_invalid();

			// 於備註區寫入發票資訊
			$invoice_date    =$return_info['result']['cancelDateTime'];
			$invoice_number  =$return_info['result']['invoiceNumber'];
			$invoice_message =$return_info['status'];
			$invocie_result  = ( $invoice_date ) ? __( '<b>Invalid invoice result</b>', 'r2-sunpay-invoice' ) : __( '<b>Invalid issue faild</b>', 'r2-sunpay-invoice' );
			$invocie_time    = ( $invoice_date ) ? __( '<br>Invalid Time: ', 'r2-sunpay-invoice' ) . $invoice_date : '';
			$invocie_number  = ( $invoice_date ) ? __( '<br>Invoice Number: ', 'r2-sunpay-invoice' ) . $invoice_number : '';
			$cancel_reason   = ( $invoice_date ) ? __( '<br>Cancel Reason: ', 'r2-sunpay-invoice' ) . $content : '';
			if (isset( $return_info['status'] ) && $return_info['status'] === 'SUCCESS' ) {
				$invoice_msg = __( '<br>Invoice Message: ', 'r2-sunpay-invoice' ) . $invoice_message;
			} elseif (isset( $return_info['status'] ) && $return_info['status'] === 'ERROR' ) {
				$invoice_msg = __( '<br>Invoice Message: ', 'r2-sunpay-invoice' ) . $return_info['message'];
			} else {
				$invoice_msg = '';
			}
			$order->add_order_note( $invocie_result . $invocie_time . $invocie_number . $invoice_msg . $cancel_reason );

			// 寫入發票回傳資訊
			if ( isset( $return_info['status'] ) && $return_info['status'] === 'SUCCESS' ) {
				// 異動已經開立發票的狀態 1.已經開立 0.尚未開立
				$order->update_meta_data( '_sunpay_invoice_status', 0 );
				// 清除發票號碼
				$order->update_meta_data( '_sunpay_invoice_number', '');
				$order->save();
			}
			return $invoice_message;
		} catch (\Exception $e) {
			// 例外錯誤處理.
			return new \WP_Error( 'error', $e->getMessage() );
		}
	}
	/**
	 * 查詢發票
	 *
	 * @param string $invoice_numbers 發票號碼多筆陣列
	 * @param string $order_no 自訂訂單編號
	 *
	 * return JSON
	 */
	public function get_invoice_list( $invoice_numbers = '', $order_no = '' ) {
		$is_testmode = get_option( 'wc_woomp_sunpay_invoice_testmode_enabled' )==='yes';
		$api_url     = $is_testmode?'https://testinv.sunpay.com.tw/api/v1/SunPay/GetInvoiceList':'https://inv.sunpay.com.tw/api/v1/SunPay/GetInvoiceList';
		try {
			// 1.載入SDK程式
			$sunpay_invoice = new SunpayInvoiceSDK();

			// 2.寫入基本介接參數
			$sunpay_invoice->CompanyID  = get_option('wc_woomp_sunpay_invoice_company_id');
			$sunpay_invoice->merchantID = $is_testmode?'14F8CK87XB':get_option('wc_woomp_sunpay_invoice_merchant_id');
			$sunpay_invoice->HashKey    = $is_testmode?'WF09QRGVZX6R20HS':get_option('wc_woomp_sunpay_invoice_hashkey');
			$sunpay_invoice->HashIV     = $is_testmode?'UBAMHYLNSYY7P0U4':get_option('wc_woomp_sunpay_invoice_hashiv');
			$sunpay_invoice->api_url    = $api_url;

			// 3.寫入發票資訊
			$sunpay_invoice->send =[];// 清空send中的資料
			if (isset($invoice_numbers) && $invoice_numbers !==[ '' ]) {
				$sunpay_invoice->send['invoiceNumbers'] = $invoice_numbers;
			}
			if (isset($order_no) && $order_no !=='') {
				$sunpay_invoice->send['orderNo'] = $order_no;
			}

			// 4.送出
			$return_info = $sunpay_invoice->invoice_list();

			return $return_info;
		} catch (\Exception $e) {
			// 例外錯誤處理.
			return new \WP_Error( 'error', $e->getMessage() );
		}
	}
}
