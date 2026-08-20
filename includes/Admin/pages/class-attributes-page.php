<?php
defined( 'ABSPATH' ) || exit;

final class WAM_Attributes_Page {

	public static function render(): void {
		if ( ! WAM_Helper::can_manage() ) {
			wp_die( esc_html__( 'You do not have permission to manage attributes.', 'woocommerce-attribute-manager' ) );
		}

		$create = null;
		$delete = null;

		if ( isset( $_POST['wam_action'] ) ) {
			$action = sanitize_key( wp_unslash( $_POST['wam_action'] ) );

			if ( 'create_attributes' === $action ) {
				check_admin_referer( 'wam_create_attributes' );
				$input = isset( $_POST['wam_attribute_input'] ) ? sanitize_textarea_field( wp_unslash( $_POST['wam_attribute_input'] ) ) : '';
				$create = WAM_Attribute_Manager::import( $input );
			}

			if ( 'delete_attributes' === $action ) {
				check_admin_referer( 'wam_delete_attributes' );
				$ids = isset( $_POST['wam_attribute_ids'] ) ? (array) wp_unslash( $_POST['wam_attribute_ids'] ) : array();
				$delete = WAM_Attribute_Manager::delete_many( $ids );
			}
		}

		$attributes = wc_get_attribute_taxonomies();
		?>
		<div class="wrap wam-wrap">
			<h1><?php esc_html_e( 'Create WooCommerce Attributes', 'woocommerce-attribute-manager' ); ?></h1>

			<p><?php esc_html_e( 'The slug is required for new attributes and must be English-only: lowercase letters, numbers, hyphens or underscores.', 'woocommerce-attribute-manager' ); ?></p>

			<?php if ( is_array( $create ) ) : ?>
				<div class="notice notice-success is-dismissible"><p>
					<?php printf(
						esc_html__( 'Processed: %d new, %d existing, %d errors.', 'woocommerce-attribute-manager' ),
						count( $create['created'] ), count( $create['existing'] ), count( $create['errors'] )
					); ?>
				</p></div>
			<?php endif; ?>

			<?php if ( is_array( $delete ) ) : ?>
				<div class="notice notice-warning is-dismissible"><p>
					<?php printf(
						esc_html__( 'Deleted: %d attributes. Errors: %d.', 'woocommerce-attribute-manager' ),
						count( $delete['deleted'] ), count( $delete['errors'] )
					); ?>
				</p></div>
			<?php endif; ?>

			<form method="post">
				<?php wp_nonce_field( 'wam_create_attributes' ); ?>
				<input type="hidden" name="wam_action" value="create_attributes">
				<textarea name="wam_attribute_input" class="large-text code wam-code" rows="18" placeholder="[attribute]
name: رنگ
slug: color
values: قرمز | آبی | سبز

[attribute]
name: سایز
slug: size
values: S | M | L | XL"></textarea>
				<p class="description"><?php esc_html_e( 'For Persian or other non-English names, provide the English semantic slug explicitly, e.g. slug: color.', 'woocommerce-attribute-manager' ); ?></p>
				<p><button type="submit" class="button button-primary"><?php esc_html_e( 'Create / Update Attributes', 'woocommerce-attribute-manager' ); ?></button></p>
			</form>

			<hr>
			<h2><?php esc_html_e( 'Current WooCommerce Attributes', 'woocommerce-attribute-manager' ); ?></h2>

			<form method="post" id="wam-bulk-delete-form">
				<?php wp_nonce_field( 'wam_delete_attributes' ); ?>
				<input type="hidden" name="wam_action" value="delete_attributes">

				<table class="widefat striped">
					<thead><tr>
						<th class="check-column"><input type="checkbox" id="wam-select-all-attributes"></th>
						<th><?php esc_html_e( 'Name', 'woocommerce-attribute-manager' ); ?></th>
						<th><?php esc_html_e( 'Slug', 'woocommerce-attribute-manager' ); ?></th>
						<th><?php esc_html_e( 'Terms', 'woocommerce-attribute-manager' ); ?></th>
					</tr></thead>
					<tbody>
					<?php foreach ( $attributes as $attribute ) : ?>
						<?php
						$taxonomy = WAM_Helper::attribute_taxonomy( (int) $attribute->attribute_id );
						$terms = get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => false, 'fields' => 'names' ) );
						?>
						<tr>
							<th class="check-column"><input type="checkbox" class="wam-attribute-checkbox" name="wam_attribute_ids[]" value="<?php echo esc_attr( $attribute->attribute_id ); ?>"></th>
							<td><?php echo esc_html( $attribute->attribute_label ); ?></td>
							<td><?php echo esc_html( $attribute->attribute_name ); ?></td>
							<td><?php echo esc_html( is_wp_error( $terms ) ? '' : implode( ', ', $terms ) ); ?></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>

				<p><button type="submit" class="button button-secondary" id="wam-delete-selected-attributes"><?php esc_html_e( 'Delete Selected Attributes', 'woocommerce-attribute-manager' ); ?></button></p>
				<p class="description"><?php esc_html_e( 'Warning: deletion is permanent and may remove terms used by products. Review your selection before confirming.', 'woocommerce-attribute-manager' ); ?></p>
			</form>
		</div>
		<?php
	}
}
