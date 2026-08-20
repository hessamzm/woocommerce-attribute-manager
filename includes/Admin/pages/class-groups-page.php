<?php

defined( 'ABSPATH' ) || exit;

final class WAM_Groups_Page {

	public static function render(): void {
		if ( ! WAM_Helper::can_manage() ) {
			wp_die( esc_html__( 'You do not have permission to manage attribute groups.', 'woocommerce-attribute-manager' ) );
		}

		$saved = false;
		$delete_result = null;

		if ( isset( $_POST['wam_action'] ) ) {
			$action = sanitize_key( wp_unslash( $_POST['wam_action'] ) );

			if ( 'import_groups' === $action ) {
				check_admin_referer( 'wam_import_groups' );

				$input = isset( $_POST['wam_group_input'] )
					? sanitize_textarea_field( wp_unslash( $_POST['wam_group_input'] ) )
					: '';

				WAM_Group_Manager::import( $input );
				$saved = true;
			}

			if ( 'delete_group' === $action ) {
				check_admin_referer( 'wam_delete_group' );

				$key = isset( $_POST['wam_group_key'] )
					? sanitize_title( wp_unslash( $_POST['wam_group_key'] ) )
					: '';

				$delete_result = WAM_Group_Storage::delete_many( array( $key ) );
			}

			if ( 'delete_groups' === $action ) {
				check_admin_referer( 'wam_delete_groups' );

				$keys = isset( $_POST['wam_group_keys'] )
					? (array) wp_unslash( $_POST['wam_group_keys'] )
					: array();

				$delete_result = WAM_Group_Storage::delete_many( $keys );
			}
		}

		$groups = WAM_Group_Storage::get_all();
		?>
		<div class="wrap wam-wrap">
			<h1><?php esc_html_e( 'Attribute Groups', 'woocommerce-attribute-manager' ); ?></h1>

			<p>
				<?php esc_html_e( 'Groups are reusable templates. They only add the selected Attribute rows to a product. Terms are not automatically selected for the product.', 'woocommerce-attribute-manager' ); ?>
			</p>

			<?php if ( $saved ) : ?>
				<div class="notice notice-success is-dismissible">
					<p><?php esc_html_e( 'Groups saved successfully.', 'woocommerce-attribute-manager' ); ?></p>
				</div>
			<?php endif; ?>

			<?php if ( is_array( $delete_result ) ) : ?>
				<div class="notice notice-warning is-dismissible">
					<p>
						<?php
						printf(
							esc_html__( 'Deleted %d attribute groups.', 'woocommerce-attribute-manager' ),
							count( $delete_result['deleted'] )
						);
						?>
					</p>
				</div>
			<?php endif; ?>

			<form method="post">
				<?php wp_nonce_field( 'wam_import_groups' ); ?>
				<input type="hidden" name="wam_action" value="import_groups">

				<textarea
					name="wam_group_input"
					class="large-text code wam-code"
					rows="16"
					placeholder="[group]
name: Clothing Details
attributes: Color | Size | Material

[group]
name: Technical Details
attributes: Weight | Dimensions | Voltage"
				></textarea>

				<p>
					<button type="submit" class="button button-primary">
						<?php esc_html_e( 'Save Groups', 'woocommerce-attribute-manager' ); ?>
					</button>
				</p>
			</form>

			<hr>

			<h2><?php esc_html_e( 'Saved Groups', 'woocommerce-attribute-manager' ); ?></h2>

			<?php if ( empty( $groups ) ) : ?>
				<p><?php esc_html_e( 'No groups have been created yet.', 'woocommerce-attribute-manager' ); ?></p>
			<?php else : ?>
				<form method="post" id="wam-bulk-delete-groups">
					<?php wp_nonce_field( 'wam_delete_groups' ); ?>
					<input type="hidden" name="wam_action" value="delete_groups">

					<table class="widefat striped">
						<thead>
							<tr>
								<th class="check-column">
									<input type="checkbox" id="wam-select-all-groups">
								</th>
								<th><?php esc_html_e( 'Group', 'woocommerce-attribute-manager' ); ?></th>
								<th><?php esc_html_e( 'Attributes', 'woocommerce-attribute-manager' ); ?></th>
								<th><?php esc_html_e( 'Actions', 'woocommerce-attribute-manager' ); ?></th>
							</tr>
						</thead>
						<tbody>
						<?php foreach ( $groups as $key => $group ) : ?>
							<tr>
								<th class="check-column">
									<input
										type="checkbox"
										class="wam-group-checkbox"
										name="wam_group_keys[]"
										value="<?php echo esc_attr( $key ); ?>"
									>
								</th>
								<td><?php echo esc_html( $group['name'] ?? '' ); ?></td>
								<td><?php echo esc_html( implode( ', ', $group['attributes'] ?? array() ) ); ?></td>
								<td>
									<button
										type="submit"
										class="button-link-delete wam-delete-single-group"
										formaction=""
										name="wam_single_delete"
										value="<?php echo esc_attr( $key ); ?>"
										data-group-name="<?php echo esc_attr( $group['name'] ?? '' ); ?>"
									>
										<?php esc_html_e( 'Delete', 'woocommerce-attribute-manager' ); ?>
									</button>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>

					<p>
						<button type="submit" class="button button-secondary" id="wam-delete-selected-groups">
							<?php esc_html_e( 'Delete Selected Groups', 'woocommerce-attribute-manager' ); ?>
						</button>
					</p>

					<p class="description">
						<?php esc_html_e( 'Deleting a group does not delete its WooCommerce Attributes or Terms. Only the reusable group is removed.', 'woocommerce-attribute-manager' ); ?>
					</p>
				</form>

				<form method="post" id="wam-single-delete-group-form" style="display:none;">
					<?php wp_nonce_field( 'wam_delete_group' ); ?>
					<input type="hidden" name="wam_action" value="delete_group">
					<input type="hidden" name="wam_group_key" id="wam-single-group-key" value="">
				</form>
			<?php endif; ?>
		</div>
		<?php
	}
}
