<?php
/**
 * Custom Post Type: R2 Sunpay Invoice
 */

declare(strict_types=1);

namespace J7\R2SunpayInvoice\Admin;

use J7\R2SunpayInvoice\Plugin;

if (class_exists('J7\R2SunpayInvoice\Admin\CPT')) {
	return;
}
/**
 * Class CPT
 */
final class CPT {
	use \J7\WpUtils\Traits\SingletonTrait;

	/**
	 * Constructor
	 */
	public function __construct() {
		\add_action( 'init', [ __CLASS__, 'register_cpt' ] );
		\add_action( 'load-post.php', [ __CLASS__, 'init_metabox' ] );
		\add_action( 'load-post-new.php', [ __CLASS__, 'init_metabox' ] );
	}

	/**
	 * Register r2-sunpay-invoice custom post type
	 */
	public static function register_cpt(): void {

		$labels = [
			'name'                     => \esc_html__( 'r2-sunpay-invoice', 'r2_sunpay_invoice' ),
			'singular_name'            => \esc_html__( 'r2-sunpay-invoice', 'r2_sunpay_invoice' ),
			'add_new'                  => \esc_html__( 'Add new', 'r2_sunpay_invoice' ),
			'add_new_item'             => \esc_html__( 'Add new item', 'r2_sunpay_invoice' ),
			'edit_item'                => \esc_html__( 'Edit', 'r2_sunpay_invoice' ),
			'new_item'                 => \esc_html__( 'New', 'r2_sunpay_invoice' ),
			'view_item'                => \esc_html__( 'View', 'r2_sunpay_invoice' ),
			'view_items'               => \esc_html__( 'View', 'r2_sunpay_invoice' ),
			'search_items'             => \esc_html__( 'Search r2-sunpay-invoice', 'r2_sunpay_invoice' ),
			'not_found'                => \esc_html__( 'Not Found', 'r2_sunpay_invoice' ),
			'not_found_in_trash'       => \esc_html__( 'Not found in trash', 'r2_sunpay_invoice' ),
			'parent_item_colon'        => \esc_html__( 'Parent item', 'r2_sunpay_invoice' ),
			'all_items'                => \esc_html__( 'All', 'r2_sunpay_invoice' ),
			'archives'                 => \esc_html__( 'r2-sunpay-invoice archives', 'r2_sunpay_invoice' ),
			'attributes'               => \esc_html__( 'r2-sunpay-invoice attributes', 'r2_sunpay_invoice' ),
			'insert_into_item'         => \esc_html__( 'Insert to this r2-sunpay-invoice', 'r2_sunpay_invoice' ),
			'uploaded_to_this_item'    => \esc_html__( 'Uploaded to this r2-sunpay-invoice', 'r2_sunpay_invoice' ),
			'featured_image'           => \esc_html__( 'Featured image', 'r2_sunpay_invoice' ),
			'set_featured_image'       => \esc_html__( 'Set featured image', 'r2_sunpay_invoice' ),
			'remove_featured_image'    => \esc_html__( 'Remove featured image', 'r2_sunpay_invoice' ),
			'use_featured_image'       => \esc_html__( 'Use featured image', 'r2_sunpay_invoice' ),
			'menu_name'                => \esc_html__( 'r2-sunpay-invoice', 'r2_sunpay_invoice' ),
			'filter_items_list'        => \esc_html__( 'Filter r2-sunpay-invoice list', 'r2_sunpay_invoice' ),
			'filter_by_date'           => \esc_html__( 'Filter by date', 'r2_sunpay_invoice' ),
			'items_list_navigation'    => \esc_html__( 'r2-sunpay-invoice list navigation', 'r2_sunpay_invoice' ),
			'items_list'               => \esc_html__( 'r2-sunpay-invoice list', 'r2_sunpay_invoice' ),
			'item_published'           => \esc_html__( 'r2-sunpay-invoice published', 'r2_sunpay_invoice' ),
			'item_published_privately' => \esc_html__( 'r2-sunpay-invoice published privately', 'r2_sunpay_invoice' ),
			'item_reverted_to_draft'   => \esc_html__( 'r2-sunpay-invoice reverted to draft', 'r2_sunpay_invoice' ),
			'item_scheduled'           => \esc_html__( 'r2-sunpay-invoice scheduled', 'r2_sunpay_invoice' ),
			'item_updated'             => \esc_html__( 'r2-sunpay-invoice updated', 'r2_sunpay_invoice' ),
		];
		$args   = [
			'label'                 => \esc_html__( 'r2-sunpay-invoice', 'r2_sunpay_invoice' ),
			'labels'                => $labels,
			'description'           => '',
			'public'                => true,
			'hierarchical'          => false,
			'exclude_from_search'   => true,
			'publicly_queryable'    => true,
			'show_ui'               => true,
			'show_in_nav_menus'     => false,
			'show_in_admin_bar'     => false,
			'show_in_rest'          => true,
			'query_var'             => false,
			'can_export'            => true,
			'delete_with_user'      => true,
			'has_archive'           => false,
			'rest_base'             => '',
			'show_in_menu'          => true,
			'menu_position'         => 6,
			'menu_icon'             => 'dashicons-store',
			'capability_type'       => 'post',
			'supports'              => [ 'title', 'editor', 'thumbnail', 'custom-fields', 'author' ],
			'taxonomies'            => [],
			'rest_controller_class' => 'WP_REST_Posts_Controller',
			'rewrite'               => [
				'with_front' => true,
			],
		];

		\register_post_type( 'r2-sunpay-invoice', $args );
	}


	/**
	 * Meta box initialization.
	 */
	public static function init_metabox(): void {
		\add_action( 'add_meta_boxes', [ __CLASS__, 'add_metabox' ] );
	}

	/**
	 * Adds the meta box.
	 *
	 * @param string $post_type Post type.
	 */
	public static function add_metabox( string $post_type ): void {
		if ( in_array( $post_type, [ Plugin::$kebab ], true ) ) {
			\add_meta_box(
				Plugin::$kebab . '-metabox',
				__( 'R2 Sunpay Invoice', 'r2_sunpay_invoice' ),
				[ __CLASS__, 'render_meta_box' ],
				$post_type,
				'advanced',
				'high'
			);
		}
	}

	/**
	 * Render meta box.
	 */
	public static function render_meta_box(): void {
		echo '<div id="r2_sunpay_invoice_metabox"></div>';
	}
}
