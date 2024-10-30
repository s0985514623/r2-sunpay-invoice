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
		// 後台訂單列表增加單號欄位
		Admin\OrderButton::instance();
		Ajax\Ajax::instance();
		FrontEnd\Checkout::instance();

		// 前端載入 script
		\add_action('admin_enqueue_scripts', [ $this, 'admin_enqueue_script' ], 99);

		// 增加電子發票設定頁面
		\add_filter( 'woocommerce_get_settings_pages', [ $this,'add_setting_page' ] );
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
	 * @param string $hook current page hook
	 *
	 * @return void
	 */
	public static function admin_enqueue_script( $hook ): void {
		// 只在訂單頁面載入Sunpay-invoice-admin.js
		$screen = \get_current_screen();
		if ( $screen && $screen->post_type === 'shop_order' ) {
			// 載入 sunpay-invoice-admin.js
			wp_enqueue_script( 'sunpay-invoice-admin', Plugin::$url . '/inc/classes/Admin/js/sunpay-invoice-admin.js', [ 'jquery' ], Plugin::$version, true );
		}

		self::enqueue_script();
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
					'APP1_SELECTOR' => Base::APP1_SELECTOR,
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
}
