<?php
/**
 * WC Order Button
 * 開立、作廢、折讓、作廢折讓發票按鈕
 */

declare(strict_types=1);

namespace J7\R2SunpayInvoice\Admin;

if (class_exists('J7\R2SunpayInvoice\Admin\OrderButton')) {
	return;
}
/**
 * Class Entry
 */
final class OrderButton {
	use \J7\WpUtils\Traits\SingletonTrait;

	/**
	 * Constructor
	 */
	public function __construct() {
		add_filter( 'manage_shop_order_posts_columns', [ $this, 'shop_order_columns' ], 11, 1 );
		add_action( 'manage_shop_order_posts_custom_column', [ $this, 'shop_order_column' ], 11, 2 );
		add_action( 'add_meta_boxes', [ $this, 'add_metabox' ] );
	}

	/**
	 * 後台訂單列表增加單號欄位
	 *
	 * @param array<string> $columns 欄位
	 * @return array<string>
	 */
	public function shop_order_columns( $columns ): array {
		$add_index = array_search( 'shipping_address', array_keys( $columns ) ) + 1;
		$pre_array = array_splice( $columns, 0, $add_index );
		$array     = [
			'wmp_invoice_no' => __( 'Invoice number', 'r2-sunpay-invoice' ),
		];
		return array_merge( $pre_array, $array, $columns );
	}
	/**
	 * 後台訂單列表增加開立發票按鈕
	 *
	 * @param string $column 欄位名稱
	 * @param int    $post_id POST ID
	 * @return void
	 */
	public function shop_order_column( $column, $post_id ): void {
		$order = \wc_get_order( $post_id );
		if ( 'wmp_invoice_no' !== $column || '0' === $order->get_total() ) {
			return;
		}
		$sunpay_invoice_data  = $order->get_meta( '_sunpay_invoice_data' );
		$invoice_type         = $sunpay_invoice_data['_invoice_type'] ?? 'individual';
		$invoice_company_name = $sunpay_invoice_data['_invoice_company_name'] ?? '';
		$invoice_tax_id       = $sunpay_invoice_data['_invoice_tax_id'] ?? '';

		if ( ! empty( $order->get_meta( '_sunpay_invoice_number' ) ) ) {
			echo \esc_html( $order->get_meta( '_sunpay_invoice_number' ) );
			printf(
				/*html*/'<br><button type="button" class="button btnInvalidInvoice" value="%1$s">%2$s</button>',
				\esc_attr( $post_id ),
				\__( 'Invalid invoice', 'r2-sunpay-invoice' )
			);
		} else {
			printf(
				/*html*/'<button type="button" class="button btnGenerateInvoice" value="%1$s" data-invoice_type="%2$s" data-invoice_tax_id="%3$s" data-invoice_company_name="%4$s">%5$s</button>',
				\esc_attr( $post_id ),
				\esc_attr( $invoice_type ),
				\esc_attr( $invoice_tax_id ),
				\esc_attr( $invoice_company_name ),
				\__( 'Generate invoice', 'r2-sunpay-invoice' )
			);
		}
	}
	/**
	 * 訂單詳情頁面增加發票meta box
	 *
	 * @return void
	 */
	public function add_metabox(): void {
		\add_meta_box(
			'sunpay_invoice_meta_box',
			__( '紅陽電子發票', 'r2-sunpay-invoice' ),
			[ $this, 'invoice_meta_box' ],
			'shop_order',
			'side',
			'high'
		);
	}
	/**
	 * Meta box callback
	 *
	 * @return void
	 */
	public function invoice_meta_box(): void {
		if ( ! isset( $_GET['post'] ) ) {
			return;
		}
		/*phpcs:ignore*/
		$order = wc_get_order( $_GET['post'] );
		if ( ! $order ) {
			return;
		}
		$product_type = '';

		foreach ( $order->get_items() as $item ) {
			/** @var WC_Order_Item_Product $item */
			$product_type = \WC_Product_Factory::get_product_type( $item->get_product_id() );
		}

		if ( '0' === $order->get_total() && strpos( $product_type, 'subscription' ) === false ) {
			return;
		}

		if ( ! $order->get_meta( '_sunpay_invoice_data' ) ) {
			$order->update_meta_data( '_sunpay_invoice_data', [] );
			$order->save();
		}

		$_invoice_type         = ( array_key_exists( '_invoice_type', $order->get_meta( '_sunpay_invoice_data' ) ) ) ? $order->get_meta( '_sunpay_invoice_data' )['_invoice_type'] : '';
		$_invoice_individual   = ( array_key_exists( '_invoice_individual', $order->get_meta( '_sunpay_invoice_data' ) ) ) ? $order->get_meta( '_sunpay_invoice_data' )['_invoice_individual'] : '';
		$_invoice_carrier      = ( array_key_exists( '_invoice_carrier', $order->get_meta( '_sunpay_invoice_data' ) ) ) ? $order->get_meta( '_sunpay_invoice_data' )['_invoice_carrier'] : '';
		$_invoice_company_name = ( array_key_exists( '_invoice_company_name', $order->get_meta( '_sunpay_invoice_data' ) ) ) ? $order->get_meta( '_sunpay_invoice_data' )['_invoice_company_name'] : '';
		$_invoice_tax_id       = ( array_key_exists( '_invoice_tax_id', $order->get_meta( '_sunpay_invoice_data' ) ) ) ? $order->get_meta( '_sunpay_invoice_data' )['_invoice_tax_id'] : '';
		$_invoice_donate       = ( array_key_exists( '_invoice_donate', $order->get_meta( '_sunpay_invoice_data' ) ) ) ? $order->get_meta( '_sunpay_invoice_data' )['_invoice_donate'] : '';
		$_invoice_number       = $order->get_meta( '_sunpay_invoice_number' ) ?? '';

		\printf(
			/*html*/'
			<p>%17$s<strong>%18$s</strong></p>
			<p><strong>%1$s</strong></p>
			<select name="_invoice_type" style="display:block;width:100%%;margin-top:-8px;">
				<option value="individual" %5$s >%2$s</option>
				<option value="company" %6$s >%3$s</option>
				<option value="donate" %7$s >%4$s</option>
			</select>
			<div id="invoiceCarrier" style="display:none"><p><strong>%8$s</strong></p>
			<p><input type="text" name="_invoice_carrier" value="%9$s" style="margin-top:-10px;width:100%%" /><p></div>
			<div id="invoiceCompanyName" style="display:none"><p><strong>%10$s</strong></p>
			<p><input type="text" name="_invoice_company_name" value="%11$s" style="margin-top:-10px;width:100%%" /><p></div>
			<div id="invoiceTaxId" style="display:none"><p><strong>%12$s</strong></p>
			<p><input type="text" name="_invoice_tax_id" value="%13$s" style="margin-top:-10px;width:100%%" /><p></div>
			<div id="invoiceDonate" style="display:none"><p><strong>%14$s</strong></p>
			<p><input type="text" name="_invoice_donate" value="%15$s" style="margin-top:-10px;width:100%%" /><p></div>
			%16$s
			',
			__( 'Invoice Type', 'r2-sunpay-invoice' ),
			__( 'individual', 'r2-sunpay-invoice' ),
			__( 'company', 'r2-sunpay-invoice' ),
			__( 'donate', 'r2-sunpay-invoice' ),
			selected( $_invoice_type, 'individual', false ),
			selected( $_invoice_type, 'company', false ),
			selected( $_invoice_type, 'donate', false ),
			__( 'Carrier Number', 'r2-sunpay-invoice' ),
			$_invoice_carrier ?? '',
			__( 'Company Name', 'r2-sunpay-invoice' ),
			$_invoice_company_name ?? '',
			__( 'TaxID', 'r2-sunpay-invoice' ),
			$_invoice_tax_id,
			__( 'Donate Number', 'r2-sunpay-invoice' ),
			$_invoice_donate,
			$this->set_invoice_button( $_GET['post'] ),/*phpcs:ignore*/
			__( 'Invoice Number: ', 'r2-sunpay-invoice' ),
			$_invoice_number
			);
	}

	/**
	 * 建立發票開立按鈕
	 *
	 * @param int $order_id 訂單ID
	 * @return string
	 */
	private function set_invoice_button( $order_id ) {

		$order  = \wc_get_order( $order_id );
		$output = '<div style="display:flex;justify-content:space-between">';

		// 產生按鈕，傳送 order id 給ajax js
		if ( empty( $order->get_meta( '_sunpay_invoice_number' ) ) ) {
			$output .= "<button class='button btnGenerateInvoice' type='button' value='" . $order_id . "'>開立發票</button><button class='button save_order button-primary' id='btnUpdateInvoiceData' type='submit' value='" . $order_id . "' disabled>更新發票資料</button>";
		} else {
			$output .= "<button class='button btnInvalidInvoice' type='button' value='" . $order_id . "'>作廢發票</button>";
		}

		$output .= '</div>';

		return $output;
	}
}
