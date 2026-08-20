<?php

defined( 'ABSPATH' ) || exit;

final class WAM_Product_Panel {

	public static function init(): void {
		add_action( 'woocommerce_product_options_attributes', array( __CLASS__, 'render' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
	}

	public static function render(): void {
		if ( ! WAM_Helper::can_manage() ) {
			return;
		}

		$groups = WAM_Group_Storage::get_all();

		if ( empty( $groups ) ) {
			return;
		}
		?>
		<div class="wam-product-group-box">
			<p class="form-field">
				<label for="wam_attribute_group">
					<?php esc_html_e( 'Attribute Group', 'woocommerce-attribute-manager' ); ?>
				</label>

				<select id="wam_attribute_group">
					<option value=""><?php esc_html_e( 'Select a group', 'woocommerce-attribute-manager' ); ?></option>
					<?php foreach ( $groups as $key => $group ) : ?>
						<option value="<?php echo esc_attr( $key ); ?>">
							<?php echo esc_html( $group['name'] ?? '' ); ?>
						</option>
					<?php endforeach; ?>
				</select>

				<button type="button" class="button" id="wam-add-attribute-group">
					<?php esc_html_e( 'Add Group', 'woocommerce-attribute-manager' ); ?>
				</button>

				<span class="description">
					<?php esc_html_e( 'Adds the group attributes to this product. You can then choose their values.', 'woocommerce-attribute-manager' ); ?>
				</span>
			</p>
		</div>
		<?php
	}

	public static function assets( string $hook ): void {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}

		$screen = get_current_screen();

		if ( ! $screen || 'product' !== $screen->post_type ) {
			return;
		}

		wp_enqueue_style(
			'wam-product',
			WAM_URL . 'assets/css/product.css',
			array(),
			WAM_VERSION
		);

		wp_enqueue_script(
			'wam-product',
			WAM_URL . 'assets/js/product.js',
			array( 'jquery' ),
			WAM_VERSION,
			true
		);

		wp_localize_script(
			'wam-product',
			'WAMProduct',
			array(
				'restUrl' => esc_url_raw( rest_url( 'wam/v1/' ) ),
				'nonce' => wp_create_nonce( 'wp_rest' ),
				'i18n' => array(
					'loading' => __( 'Loading...', 'woocommerce-attribute-manager' ),
					'error' => __( 'Could not load the selected group.', 'woocommerce-attribute-manager' ),
				),
			)
		);
	}
}
