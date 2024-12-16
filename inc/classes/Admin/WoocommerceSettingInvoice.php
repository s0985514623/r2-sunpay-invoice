<?php
/**
 * Woocommerce Invoice Setting Page
 */

declare(strict_types=1);

namespace J7\R2SunpayInvoice\Admin;

if (class_exists('J7\R2SunpayInvoice\Admin\WoocommerceSettingInvoice')) {
	return;
}

/**
 * Class Woomp_Setting_Invoice
 */
final class WoocommerceSettingInvoice extends \WC_Settings_Page {
	use \R2SunpayInvoice\vendor\J7\WpUtils\Traits\SingletonTrait;

	/**
	 * @var array<mixed> $setting_default
	 */
	public $setting_default = [];

	/**
	 * Bootstraps the class and hooks required actions & filters.
	 */
	public function __construct() {
		$this->id    = 'woomp_setting_invoice';
		$this->label = __( '電子發票設定', 'r2-sunpay-invoice' );
		add_filter( 'woocommerce_settings_tabs_array', [ $this, 'add_settings_tab' ], 51 );
		add_action( 'woocommerce_sections_' . $this->id, [ $this, 'output_sections' ] );
		add_action( 'woocommerce_settings_' . $this->id, [ $this, 'output' ] );
		add_action( 'woocommerce_settings_save_' . $this->id, [ $this, 'save' ] );
	}

	/**
	 * Add a new settings tab to the WooCommerce settings tabs array.
	 *
	 * @param array<mixed> $settings_tabs Array of WooCommerce setting tabs & their labels, excluding the Subscription tab.
	 * @return array<mixed> $settings_tabs Array of WooCommerce setting tabs & their labels, including the Subscription tab.
	 */
	public function add_settings_tab( $settings_tabs ) {
		$settings_tabs['woomp_setting_invoice'] = __( '電子發票設定', 'r2-sunpay-invoice' );
		return $settings_tabs;
	}


	/**
	 * Uses the WooCommerce admin fields API to output settings via the @see woocommerce_admin_fields() function.
	 *
	 * @uses woocommerce_admin_fields()
	 * @uses $this->get_settings()
	 * return void
	 */
	public function settings_tab(): void {
		\woocommerce_admin_fields( $this->get_settings() );
	}


	/**
	 * Uses the WooCommerce options API to save settings via the @see woocommerce_update_options() function.
	 *
	 * @uses woocommerce_update_options()
	 * @uses $this->get_settings()
	 * return void
	 */
	public function update_settings(): void {
		woocommerce_update_options( $this->get_settings() );
	}


	/**
	 * Get all the settings for this plugin for @see woocommerce_admin_fields() function.
	 *
	 * @param string $section Section name.
	 * @return array<array<mixed>> Array of settings for @see woocommerce_admin_fields() function.
	 */
	public function get_settings( $section = null ) {

		switch ( $section ) {
			case 'sunpay':
				$settings = [
					[
						'title' => '紅陽電子發票設定',
						'type'  => 'title',
						'id'    => 'wc_woomp_general_setting',
					],
					[
						'title'   => '除錯資訊',
						'type'    => 'checkbox',
						'default' => 'no',
						'desc'    => sprintf( '紀錄日誌於以下路徑：<code>%s</code>', wc_get_log_file_path( 'r2-sunpay-invoice-sunpay-invoice' ) ),
						'id'      => 'wc_woomp_sunpay_invoice_debug_log_enabled',
					],
					[
						'title'    => __( 'Order number prefix', 'r2-sunpay-invoice' ),
						'id'       => 'wc_woomp_sunpay_invoice_order_prefix',
						'type'     => 'text',
						'desc'     => __( 'The prefix string of order number. Only letters and numbers allowed.', 'r2-sunpay-invoice' ),
						'desc_tip' => true,
					],

					[
						'type' => 'sectionend',
						'id'   => 'wc_woomp_general_setting',
					],
					[
						'title' => __( 'Invoice options', 'r2-sunpay-invoice' ),
						'id'    => 'invoice_options',
						'type'  => 'title',
					],
					[
						'name'     => __( 'Issue Mode', 'r2-sunpay-invoice' ),
						'type'     => 'select',
						'desc'     => __( 'You can issue the e-invoice manually even if you choose Automatic mode' ),
						'class'    => 'wc-enhanced-select',
						'desc_tip' => true,
						'id'       => 'wc_woomp_sunpay_invoice_issue_mode',
						'options'  => [
							'manual' => __( 'Issue Manual', 'r2-sunpay-invoice' ),
							'auto'   => __( 'Issue automatic', 'r2-sunpay-invoice' ),
						],
						'default'  => 'manual',
					],
					[
						'name'     => __( 'Allowed Order Status for issue', 'r2-sunpay-invoice' ),
						'type'     => 'select',
						'class'    => 'wc-enhanced-select',
						'desc'     => __( 'When order status changes to the status, the e-invoice will be issued automatically.' ),
						'id'       => 'wc_woomp_sunpay_invoice_issue_at',
						'desc_tip' => true,
						'options'  => wc_get_order_statuses(),
					],
					// 取消自動作廢發票功能
					// [
					// 'name'     => __( 'Invalid mode', 'r2-sunpay-invoice' ),
					// 'type'     => 'select',
					// 'desc'     => __( 'You can issue the e-invoice manually even if you choose Automatic mode' ),
					// 'class'    => 'wc-enhanced-select',
					// 'desc_tip' => true,
					// 'id'       => 'wc_woomp_sunpay_invoice_invalid_mode',
					// 'options'  => [
					// 'manual' => __( 'Invalid manual', 'r2-sunpay-invoice' ),
					// 'auto'   => __( 'Invalid automatic', 'r2-sunpay-invoice' ),
					// ],
					// 'default'  => 'auto',
					// ],
					// [
					// 'name'     => __( 'Allowed Order Status for invalid', 'r2-sunpay-invoice' ),
					// 'type'     => 'select',
					// 'class'    => 'wc-enhanced-select',
					// 'desc'     => __( 'When order status changes to the status, the e-invoice will be invalid automatically.' ),
					// 'id'       => 'wc_woomp_sunpay_invoice_invalid_at',
					// 'desc_tip' => true,
					// 'options'  => [
					// 'wc-refunded' => __( 'Refunded', 'woocommerce' ),
					// 'wc-failed'   => __( 'Failed', 'woocommerce' ),
					// ],
					// ],
					[
						'name'    => __( 'Carrier Type', 'r2-sunpay-invoice' ),
						'desc'    => __( 'Allowed invoice carrier type', 'r2-sunpay-invoice' ),
						'id'      => 'wc_woomp_sunpay_invoice_carrier_type',
						'class'   => 'wc-enhanced-select',
						'type'    => 'multiselect',
						'options' => [
							__( 'Cloud Invoice', 'r2-sunpay-invoice' ) => __( 'Cloud Invoice', 'r2-sunpay-invoice' ),
							__( 'Mobile Code', 'r2-sunpay-invoice' ) => __( 'Mobile Code', 'r2-sunpay-invoice' ),
							__( 'Citizen Digital Certificate', 'r2-sunpay-invoice' ) => __( 'Citizen Digital Certificate', 'r2-sunpay-invoice' ),
							__( 'Paper Invoice', 'r2-sunpay-invoice' ) => __( 'Paper Invoice', 'r2-sunpay-invoice' ),
						],
					],

					[
						'name'        => __( 'Donated Organization', 'r2-sunpay-invoice' ),
						'type'        => 'textarea',
						'desc'        => '輸入捐增機構(每行一筆)，格式為：愛心碼|社福團體名稱，預設為伊甸社會福利基金會',
						'desc_tip'    => true,
						'id'          => 'wc_woomp_sunpay_invoice_donate_org',
						'placeholder' => '25885|伊甸社會福利基金會',
					],
					[
						'id'   => 'invoice_options',
						'type' => 'sectionend',
					],
					[
						'title' => '商家資料設定',
						'type'  => 'title',
						'id'    => 'wc_woomp_sunpay_invoice_api_settings',
					],
					[
						'title' => '測試模式',
						'type'  => 'checkbox',
						'desc'  => '請勾選時會測試模式，未勾選則會使用下方資料作為正式交易環境',
						'id'    => 'wc_woomp_sunpay_invoice_testmode_enabled',
					],
					[
						'title' => '商家統編',
						'type'  => 'text',
						'desc'  => '請輸入商家統編(測試模式也要填寫)',
						'id'    => 'wc_woomp_sunpay_invoice_company_id',
					],
					[
						'title' => '商家編號',
						'type'  => 'text',
						'desc'  => '請輸入正式商家代號',
						'id'    => 'wc_woomp_sunpay_invoice_merchant_id',
					],
					[
						'title' => '正式 HashKey',
						'type'  => 'text',
						'desc'  => '請輸入 HashKey',
						'id'    => 'wc_woomp_sunpay_invoice_hashkey',
					],
					[
						'title' => '正式 HashIV',
						'type'  => 'text',
						'desc'  => '請輸入 HashIV',
						'id'    => 'wc_woomp_sunpay_invoice_hashiv',
					],
					[
						'type' => 'sectionend',
						'id'   => 'wc_woomp_sunpay_invoice_api_settings',
					],
				];
				return $settings;
			default:
				// code...
				return [];
		}
	}

	/**
	 * Output the settings
	 *
	 * @return void
	 */
	public function output(): void {
		global $current_section, $hide_save_button;
		if ( $current_section == '' ) {
			wp_safe_redirect( admin_url( 'admin.php?page=wc-settings&tab=woomp_setting_invoice&section=sunpay' ) );
		}
		$settings = $this->get_settings( $current_section );
		\WC_Admin_Settings::output_fields( $settings );
	}
	/**
	 * Save settings
	 *
	 * @return void
	 */
	public function save(): void {
		global $current_section;
		$settings = $this->get_settings( $current_section );
		\WC_Admin_Settings::save_fields( $settings );
	}
}
