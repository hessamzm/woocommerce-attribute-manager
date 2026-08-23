<?php
defined( 'ABSPATH' ) || exit;

final class WAM_AI_Guide_Page {
	public static function render(): void {
		if ( ! WAM_Helper::can_manage() ) {
			wp_die( esc_html__( 'You do not have permission to view this page.', 'woocommerce-attribute-manager' ) );
		}

		$attribute_taxonomies = function_exists( 'wc_get_attribute_taxonomies' )
			? wc_get_attribute_taxonomies()
			: array();

		$attribute_lines = array();
		foreach ( $attribute_taxonomies as $attribute ) {
			$attribute_lines[] = sprintf(
				'- %s | slug: %s',
				$attribute->attribute_label,
				$attribute->attribute_name
			);
		}

		$all_attributes = ! empty( $attribute_lines )
			? implode( "\n", $attribute_lines )
			: '- No existing WooCommerce global Attributes found.';

		$attribute_prompt = <<<PROMPT
You are a WooCommerce attribute architecture specialist.

BUSINESS AND PRODUCT CONTEXT
- Business name:
- Business type / industry:
- Main product categories:
- Product types:
- Target customers:
- Market / country:
- Main brands:
- Important product specifications:
- Common options or variants:
- Existing attribute names:
- Existing terms / values:
- Special naming rules:
- Other useful business information:

TASK
Based on the business and product information above, generate a clean, practical WooCommerce global Attribute structure that fits the actual products.

RULES
1. Every attribute starts with [attribute].
2. name: is the customer-facing Attribute name.
3. slug: is REQUIRED and MUST be an English-only WooCommerce slug.
4. slug may contain only lowercase English letters a-z, numbers 0-9, hyphens or underscores.
5. values: contains reusable Terms separated by |.
6. Avoid duplicate Attributes and duplicate Terms.
7. Create only Attributes relevant to the described business and products.
8. Do not create Attribute Groups in this output.
9. Return only the Attribute structure and no explanation outside it.

OUTPUT FORMAT

[attribute]
name: Color
slug: color
values: Red | Blue | Green

[attribute]
name: Size
slug: size
values: S | M | L | XL
PROMPT;

		$group_prompt = <<<PROMPT
You are a WooCommerce Attribute Group architecture specialist.

Your task is to organize the EXISTING WooCommerce Attributes below into practical Attribute Groups for the described business and products.

IMPORTANT
The Attribute list below is automatically supplied by the WooCommerce store. The user does NOT need to copy or enter the Attributes manually.

EXISTING WOOCOMMERCE ATTRIBUTES
$all_attributes

BUSINESS AND PRODUCT CONTEXT
- Business name:
- Business type / industry:
- Main product categories:
- Product types:
- Target customers:
- Market / country:
- Main brands:
- Important product specifications:
- Common product types and use cases:
- Other useful business information:

TASK
Create practical groups that can be used when adding Attributes to WooCommerce products.

RULES
1. Use ONLY Attribute names from the EXISTING WOOCOMMERCE ATTRIBUTES list.
2. Do not create new Attributes.
3. Do not rename Attributes.
4. Do not create Terms.
5. A Group starts with [group].
6. name: is the customer-facing Group name.
7. attributes: contains existing Attribute names separated by |.
8. An Attribute may appear in more than one Group when it is useful.
9. Create groups based on actual product types and use cases.
10. Avoid meaningless, overly broad, or duplicate groups.
11. Return only [group] blocks and no explanations.

OUTPUT FORMAT

[group]
name: Camera Details
attributes: Camera Type | Resolution | Video Technology | Lens | Lens Type | Image Sensor

[group]
name: Network Details
attributes: Connectivity | Power Supply
PROMPT;

		?>
		<div class="wrap wam-wrap">
			<h1><?php esc_html_e( 'AI Structure Guide', 'woocommerce-attribute-manager' ); ?></h1>
			<p><?php esc_html_e( 'Use the Attribute Prompt to create global Attributes. Use the Group Prompt to organize the Attributes that already exist in WooCommerce into reusable groups.', 'woocommerce-attribute-manager' ); ?></p>

			<h2><?php esc_html_e( 'Attribute Creation Prompt', 'woocommerce-attribute-manager' ); ?></h2>
			<p><?php esc_html_e( 'Use this prompt when you want AI to design and create the Attribute structure for your business and products.', 'woocommerce-attribute-manager' ); ?></p>
			<textarea id="wam-ai-attribute-template" class="large-text code wam-code" rows="32" readonly><?php echo esc_textarea( $attribute_prompt ); ?></textarea>
			<p><button type="button" class="button button-primary wam-copy-ai-prompt" data-target="wam-ai-attribute-template"><?php esc_html_e( 'Copy Attribute Prompt', 'woocommerce-attribute-manager' ); ?></button></p>

			<h2><?php esc_html_e( 'Attribute Group Creation Prompt', 'woocommerce-attribute-manager' ); ?></h2>
			<p><?php esc_html_e( 'All existing WooCommerce Attributes are inserted automatically. You only need to provide your business and product context before sending the prompt to AI.', 'woocommerce-attribute-manager' ); ?></p>
			<textarea id="wam-ai-group-template" class="large-text code wam-code" rows="34" readonly><?php echo esc_textarea( $group_prompt ); ?></textarea>
			<p><button type="button" class="button button-primary wam-copy-ai-prompt" data-target="wam-ai-group-template"><?php esc_html_e( 'Copy Group Prompt', 'woocommerce-attribute-manager' ); ?></button></p>

			<h2><?php esc_html_e( 'Current WooCommerce Attributes', 'woocommerce-attribute-manager' ); ?></h2>
			<pre class="wam-code wam-pre"><?php echo esc_html( $all_attributes ); ?></pre>
		</div>
		<?php
	}
}
