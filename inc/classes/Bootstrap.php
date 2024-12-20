<?php
/**
 * Bootstrap
 */

declare(strict_types=1);

namespace J7\R2SunpayInvoice;

use J7\R2SunpayInvoice\Utils\Base;
use Kucrut\Vite;

if (class_exists('J7\R2SunpayInvoice\Bootstrap')) {
	return;
}
/**
 * Class Bootstrap
 */
final class Bootstrap {

	use \J7\WpUtils\Traits\SingletonTrait;

	/**
	 * Constructor
	 */
	public function __construct() {
		Admin\OrderButton::instance();// 後台訂單列表及詳情增加單號欄位/按鈕
		Admin\Admin::instance();// 管理自動開立發票or手動開立 Class
		Ajax\Ajax::instance();// 後台發票列表查詢
		FrontEnd\Checkout::instance();// 前台結帳頁面
		Admin\InvoiceList::instance();// 查詢發票列表 Class

		// 前端載入 script
		\add_action('admin_enqueue_scripts', [ $this, 'admin_enqueue_script' ], 99);

		// 增加電子發票設定頁面
		\add_filter( 'woocommerce_get_settings_pages', [ $this,'add_setting_page' ] );
		// 引入翻譯文件
		\add_action('plugins_loaded', [ $this , 'plugin_load_textdomain' ], 99);
	}
	/**
	 * Add Setting Page
	 *
	 * @param array<mixed> $settings settings
	 * @return array<mixed> $settings
	 */
	public function add_setting_page( $settings ) {
		$settings[] = Admin\WoocommerceSettingInvoice::instance();
		return $settings;
	}


	/**
	 * Admin Enqueue script
	 * You can load the script on demand
	 *
	 * @return void
	 */
	public static function admin_enqueue_script(): void {
		// 只在訂單頁面載入Sunpay-invoice-admin.js
		$screen = \get_current_screen();
		if ( $screen && $screen->post_type === 'shop_order' ) {
			// 載入 sunpay-invoice-admin.js
			wp_enqueue_script( 'sunpay-invoice-admin', Plugin::$url . '/inc/classes/Admin/js/sunpay-invoice-admin.js', [ 'jquery' ], Plugin::$version, true );
			self::enqueue_script();
		}
	}


	/**
	 * Front-end Enqueue script
	 * You can load the script on demand
	 *
	 * @return void
	 */
	public static function frontend_enqueue_script(): void {
		self::enqueue_script();
	}

	/**
	 * Enqueue script
	 * You can load the script on demand
	 *
	 * @return void
	 */
	public static function enqueue_script(): void {

		Vite\enqueue_asset(
			Plugin::$dir . '/js/dist',
			'js/src/main.tsx',
			[
				'handle'    => Plugin::$kebab,
				'in-footer' => true,
			]
		);

		$post_id = \get_the_ID();

		\wp_localize_script(
			Plugin::$kebab,
			Plugin::$snake . '_data',
			[
				'env' => [
					'siteUrl'       => \untrailingslashit(\site_url()),
					'ajaxUrl'       => \untrailingslashit(\admin_url('admin-ajax.php')),
					'userId'        => \get_current_user_id(),
					'postId'        => $post_id,
					'APP_NAME'      => Plugin::$app_name,
					'KEBAB'         => Plugin::$kebab,
					'SNAKE'         => Plugin::$snake,
					'BASE_URL'      => Base::BASE_URL,
					'APP1_SELECTOR' => '#' . Plugin::$snake,
					'APP2_SELECTOR' => Base::APP2_SELECTOR,
					'API_TIMEOUT'   => Base::API_TIMEOUT,
					'nonce'         => \wp_create_nonce(Plugin::$kebab),
				],
			]
		);
		\wp_localize_script(
			Plugin::$kebab,
			'wpApiSettings',
			[
				'root'  => \untrailingslashit(\esc_url_raw(rest_url())),
				'nonce' => \wp_create_nonce(Plugin::$kebab),
			]
		);
	}

	/**
	 * Load plugin textdomain.
	 *
	 * @return void
	 */
	public function plugin_load_textdomain(): void {
		\load_plugin_textdomain('r2-sunpay-invoice', false, dirname(plugin_basename(__FILE__), 3) . '/languages');
	}
}
