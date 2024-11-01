<?php
/**
 * Front-end Checkout
 */

declare(strict_types=1);

namespace J7\R2SunpayInvoice\FrontEnd;

// 禁用該文件的SnakeCase檢查
// phpcs:disable WordPress.Security.NonceVerification.Missing
// phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotValidated
// phpcs:disable WordPress.Security.ValidatedSanitizedInput.MissingUnslash
// phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized


use J7\R2SunpayInvoice\Plugin;

if (class_exists('J7\R2SunpayInvoice\FrontEnd\Checkout')) {
	return;
}
/**
 * Class Checkout
 */
final class Checkout {
	use \J7\WpUtils\Traits\SingletonTrait;

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'woocommerce_after_checkout_billing_form', [ $this, 'set_invoice_field' ] );
		add_action( 'woocommerce_checkout_process', [ $this, 'set_invoice_field_validate' ] );
		add_action( 'woocommerce_checkout_update_order_meta', [ $this, 'set_invoice_meta' ] );
	}

	/**
	 * 發票欄位
	 */
	public function set_invoice_field() {

		// 判斷是否為訂閱商品,暫時用不到
		// if ( '0' === WC()->cart->total && strpos( $this->get_cart_info( 'product_type' ), 'subscription' ) === false ) {
		// return;
		// }

		// 發票開立類型。個人、公司、捐贈發票
		$this->add_wc_field(
			'invoice-type',
			'select',
			__( 'Invoice Type', 'r2-sunpay-invoice' ),
			[],
			'invoice-label',
			[ // 發票開立選項
				'individual' => __( 'individual', 'r2-sunpay-invoice' ),
				'company'    => __( 'company', 'r2-sunpay-invoice' ),
				// 'donate'     => __( 'donate', 'r2-sunpay-invoice' ),//不開啟
			]
		);

		// 個人發票選項
		if ( ! get_option( 'wc_woomp_sunpay_invoice_carrier_type' ) ) {
			// 僅開啟雲端發票
			update_option( 'wc_woomp_sunpay_invoice_carrier_type', [ '雲端發票' ] );
			// update_option( 'wc_woomp_sunpay_invoice_carrier_type', [ '雲端發票', '手機條碼', '自然人憑證', '紙本發票' ] );
		}
		$type_option = [];
		foreach ( get_option( 'wc_woomp_sunpay_invoice_carrier_type' ) as $value ) {
			$type_option[ $value ] = $value;
		}

		$this->add_wc_field(
			'individual-invoice',
			'select',
			__( 'Individual Invoice Type', 'r2-sunpay-invoice' ),
			[ 'no-search' ],
			'invoice-label',
			$type_option,
		);

		// 自然人憑證與手機條碼 載具編號欄位,不開啟
		// $this->add_wc_field(
		// 'carrier-number',
		// 'text',
		// __( 'Carrier Number', 'r2-sunpay-invoice' ),
		// [ 'hide-option-field' ],
		// 'invoice-label',
		// []
		// );

		// 公司統一編號欄位
		$this->add_wc_field(
			'company-name',
			'text',
			__( 'Company Name', 'r2-sunpay-invoice' ),
			[ 'hide-option-field' ],
			'invoice-label',
			[]
		);

		$this->add_wc_field(
			'taxid-number',
			'text',
			__( 'TaxID', 'r2-sunpay-invoice' ),
			[ 'hide-option-field' ],
			'invoice-label',
			[]
		);

		// 捐贈捐贈碼欄位,不開啟
		// $this->add_wc_field(
		// 'donate-number',
		// 'select',
		// __( 'Donate Number', 'r2-sunpay-invoice' ),
		// [ 'hide-option-field' ],
		// 'invoice-label',
		// $this->get_donate_org(),
		// );
	}

	/**
	 * 前端結帳頁面客製化欄位驗證
	 */
	public function set_invoice_field_validate() {
		// 如果選了自然人憑證，就要參加資料驗證。比對前 2 碼大寫英文，後 14 碼數字
		// if ( $_POST['individual-invoice'] == '自然人憑證' && preg_match( '/^[A-Z]{2}\d{14}$/', $_POST['carrier-number'] ) == false ) {
		// wc_add_notice( __( '<strong>電子發票 自然人憑證</strong> 請輸入前 2 位大寫英文與 14 位數字自然人憑證號碼' ), 'error' );
		// }

		// 如果選了手機條碼，就要參加資料驗證。比對 7 位英數字
		// if ( $_POST['individual-invoice'] == '手機條碼' && preg_match( '/^\/[A-Za-z0-9+-\.]{7}$/', $_POST['carrier-number'] ) == false ) {
		// wc_add_notice( __( '<strong>電子發票 手機條碼</strong> 請輸入第 1 碼為「/」，後 7 碼為大寫英文、數字、「+」、「-」或「.」' ), 'error' );
		// }

		// 如果選了公司，就要參加資料驗證。比對 8 位數字資料，如果失敗顯示錯誤訊息。
		if ( $_POST['invoice-type'] == 'company' && preg_match( '/^\d{8}$/', $_POST['taxid-number'] ) == false ) {
			wc_add_notice( __( '<strong>統一編號</strong> 請輸入 8 位數字組成統一編號' ), 'error' );
		}

		if ( $_POST['invoice-type'] == 'company' && preg_match( '/./s', $_POST['company-name'] ) == false ) {
			wc_add_notice( __( '<strong>公司名稱</strong> 為必填欄位' ), 'error' );
		}

		// // 如果選了捐贈發票，就要參加資料驗證。比對 3~7 位數字資料，如果失敗顯示錯誤訊息。
		// if ( $_POST['invoice-type'] == 'donate' && preg_match( '/^\d{3,7}$/', $_POST['donate-number'] ) == false ) {
		// wc_add_notice( __( '<strong>捐贈碼</strong> 請輸入 3~7 位數字' ), 'error' );
		// }
	}

	/**
	 * 發票資料寫入
	 *
	 * @param int $order_id 訂單ID
	 */
	public function set_invoice_meta( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( '0' === $order->get_total() ) {
			return;
		}

		$invoice_data = [];
		// 新增發票開立類型
		if ( isset( $_POST['invoice-type'] ) ) {
			$invoice_data['_invoice_type'] = wp_unslash( $_POST['invoice-type'] );
		}
		// 新增個人發票選項
		if ( isset( $_POST['individual-invoice'] ) ) {
			$invoice_data['_invoice_individual'] = wp_unslash( $_POST['individual-invoice'] );
		} else {
			$invoice_data['_invoice_individual'] = false;
		}
		// 新增載具編號
		// if ( isset( $_POST['carrier-number'] ) && ( $_POST['individual-invoice'] == '手機條碼' || $_POST['individual-invoice'] == '自然人憑證' ) ) {
		// $invoice_data['_invoice_carrier'] = wp_unslash( $_POST['carrier-number'] );
		// }
		// 新增公司名稱
		if ( isset( $_POST['company-name'] ) ) {
			$invoice_data['_invoice_company_name'] = wp_unslash( $_POST['company-name'] );
		}
		// 新增統一編號
		if ( isset( $_POST['taxid-number'] ) ) {
			$invoice_data['_invoice_tax_id'] = wp_unslash( $_POST['taxid-number'] );
		}
		// 新增捐贈碼
		// if ( isset( $_POST['donate-number'] ) ) {
		// $invoice_data['_invoice_donate'] = wp_unslash( $_POST['donate-number'] );
		// }

		if ( count( $invoice_data ) > 0 ) {
			$order->update_meta_data( '_sunpay_invoice_data', $invoice_data );
			$order->save();
		}
	}

	/**
	 * 引入 JS
	 */
	public function enqueue_scripts() {
		if ( is_checkout() ) {
			wp_register_script( 'woomp_sunpay_invoice', Plugin::$url . '/inc/classes/FrontEnd/js/checkout.js', [ 'jquery' ], Plugin::$version, true );
			wp_enqueue_script( 'woomp_sunpay_invoice' );
		}
	}

	/**
	 * 新增WC欄位
	 *
	 * @param string      $name 欄位名稱
	 * @param string      $type 欄位類型
	 * @param string      $label 欄位標籤
	 * @param array       $class 欄位類別
	 * @param string      $label_class 標籤類別
	 * @param array       $options 選項
	 * @param string|null $placeholder 預設值
	 * @return void
	 */
	private function add_wc_field( $name, $type, $label, $class, $label_class, $options, $placeholder = null ) {
		woocommerce_form_field(
			$name,
			[
				'type'        => $type,
				'label'       => $label,
				'class'       => $class,
				'label_class' => $label_class,
				'options'     => $options,
				'placeholder' => $placeholder,
			],
		);
	}
}
