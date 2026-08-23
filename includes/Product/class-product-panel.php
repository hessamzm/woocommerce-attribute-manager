<?php
defined( 'ABSPATH' ) || exit;

final class WAM_Product_Panel {

	public static function init(): void {
		add_action( 'woocommerce_product_options_attributes', array( __CLASS__, 'render' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
		WAM_Product_Terms_Ajax::init();
	}

	public static function render(): void {
		if ( ! WAM_Helper::can_manage() ) {
			return;
		}

		$groups = WAM_Group_Storage::get_all();
		global $post;
		$product_id = $post ? absint( $post->ID ) : 0;
		?>
		<div class="wam-product-tools">

			<?php if ( ! empty( $groups ) ) : ?>
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
					</p>

					<p class="description">
						<?php esc_html_e( 'Adds only the Attribute rows. Term values are not selected automatically.', 'woocommerce-attribute-manager' ); ?>
					</p>
				</div>
			<?php endif; ?>

			<?php if ( $product_id ) : ?>
				<hr>

				<div class="wam-product-terms-box">
					<h3><?php esc_html_e( 'AI Product Attribute Terms', 'woocommerce-attribute-manager' ); ?></h3>

					<p>
						<?php esc_html_e( 'First add the Attribute rows with a group. Then load the selected Attributes, give the AI the product context, and paste its term output here.', 'woocommerce-attribute-manager' ); ?>
					</p>

					<p>
						<button type="button" class="button" id="wam-load-product-attributes" data-product-id="<?php echo esc_attr( $product_id ); ?>">
							<?php esc_html_e( 'Load Selected Attributes', 'woocommerce-attribute-manager' ); ?>
						</button>

						<button type="button" class="button button-primary" id="wam-generate-terms-prompt">
							<?php esc_html_e( 'Generate AI Prompt', 'woocommerce-attribute-manager' ); ?>
						</button>
					</p>

					<p>
						<label for="wam-product-ai-context">
							<strong><?php esc_html_e( 'Product Context', 'woocommerce-attribute-manager' ); ?></strong>
						</label>
						<textarea id="wam-product-ai-context" rows="6" class="large-text" placeholder="<?php echo esc_attr__( 'Product name, model, brand, technical specifications, use case, package contents, and any other information needed to determine the exact attribute values.', 'woocommerce-attribute-manager' ); ?>"></textarea>
					</p>

					<p>
						<label for="wam-product-terms-input">
							<strong><?php esc_html_e( 'AI Terms Output', 'woocommerce-attribute-manager' ); ?></strong>
						</label>
						<textarea id="wam-product-terms-input" rows="14" class="large-text code wam-code" placeholder="[attribute]
id: 91
name: سری پردازنده (CPU)
slug: cpu
values: Intel Core i5"></textarea>
					</p>

					<p>
						<button type="button" class="button button-primary" id="wam-apply-product-terms">
							<?php esc_html_e( 'Apply Terms to Product', 'woocommerce-attribute-manager' ); ?>
						</button>
					</p>

					<div id="wam-product-attributes-preview"></div>
				</div>
			<?php endif; ?>

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

		wp_enqueue_script(
			'wam-product-terms',
			WAM_URL . 'assets/js/product-terms.js',
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
					'error' => __( 'Could not add the selected attribute group. Please check the browser console for details.', 'woocommerce-attribute-manager' ),
				),
			)
		);

		wp_localize_script(
			'wam-product-terms',
			'WAMProductTerms',
			array(
				'restUrl' => esc_url_raw( rest_url( 'wam/v1/' ) ),
				'nonce' => wp_create_nonce( 'wp_rest' ),
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonceApply' => wp_create_nonce( 'wam_product_terms' ),
				'i18n' => array(
					'noAttributes' => __( 'No taxonomy attributes are currently attached to this product.', 'woocommerce-attribute-manager' ),
					'loadFirst' => __( 'Load the selected Attributes first.', 'woocommerce-attribute-manager' ),
					'promptReady' => __( 'The AI prompt is ready. Copy it, add the product information, and send it to your AI tool.', 'woocommerce-attribute-manager' ),
					'applied' => __( 'Product attribute terms were applied successfully.', 'woocommerce-attribute-manager' ),
					'failed' => __( 'Could not apply product attribute terms.', 'woocommerce-attribute-manager' ),
				),
			)
		);
	}
}
