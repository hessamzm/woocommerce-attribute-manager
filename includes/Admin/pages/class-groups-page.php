<?php

defined( 'ABSPATH' ) || exit;

final class WAM_Groups_Page {

	public static function render(): void {
		if ( ! WAM_Helper::can_manage() ) {
			wp_die( esc_html__( 'You do not have permission to manage attribute groups.', 'woocommerce-attribute-manager' ) );
		}

		$saved = false;

		if (
			isset( $_POST['wam_action'] ) &&
			'import_groups' === sanitize_key( wp_unslash( $_POST['wam_action'] ) )
		) {
			check_admin_referer( 'wam_import_groups' );

			$input = isset( $_POST['wam_group_input'] )
				? sanitize_textarea_field( wp_unslash( $_POST['wam_group_input'] ) )
				: '';

			WAM_Group_Manager::import( $input );
			$saved = true;
		}

		$groups = WAM_Group_Storage::get_all();
		?>
		<div class="wrap wam-wrap">
			<h1><?php esc_html_e( 'Attribute Groups', 'woocommerce-attribute-manager' ); ?></h1>

			<p>
				<?php esc_html_e( 'Groups are reusable templates. They do not create a new WooCommerce data type.', 'woocommerce-attribute-manager' ); ?>
			</p>

			<?php if ( $saved ) : ?>
				<div class="notice notice-success is-dismissible">
					<p><?php esc_html_e( 'Groups saved successfully.', 'woocommerce-attribute-manager' ); ?></p>
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
				<table class="widefat striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Group', 'woocommerce-attribute-manager' ); ?></th>
							<th><?php esc_html_e( 'Attributes', 'woocommerce-attribute-manager' ); ?></th>
						</tr>
					</thead>
					<tbody>
					<?php foreach ( $groups as $group ) : ?>
						<tr>
							<td><?php echo esc_html( $group['name'] ?? '' ); ?></td>
							<td><?php echo esc_html( implode( ', ', $group['attributes'] ?? array() ) ); ?></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>
		<?php
	}
}
