<?php
defined( 'ABSPATH' ) || exit;

final class WAM_AI_Guide_Page {
	public static function render(): void {
		if ( ! WAM_Helper::can_manage() ) {
			wp_die( esc_html__( 'You do not have permission to view this page.', 'woocommerce-attribute-manager' ) );
		}

		$prompt = <<<PROMPT
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
Based on the business and product information above, generate a clean, practical WooCommerce attribute structure that fits the actual products.

RULES
1. Every attribute starts with [attribute].
2. name: is the customer-facing attribute name.
3. slug: is REQUIRED and MUST be an English-only WooCommerce slug.
4. slug: may contain only lowercase English letters a-z, numbers 0-9, hyphens or underscores.
5. Prefer clear semantic English slugs such as color, size, material, brand, capacity, voltage.
6. Do not use Persian, Arabic or other non-English characters in slug.
7. values: contains values separated by |.
8. Avoid duplicate attributes and duplicate values.
9. Create only attributes relevant to the described business and products.
10. Create reusable groups with [group].
11. Group attributes must reference names defined in the attribute section.
12. Return only the supported structure and no explanation outside it.

OUTPUT FORMAT

[attribute]
name: Color
slug: color
values: Red | Blue | Green

[attribute]
name: Size
slug: size
values: S | M | L | XL

[group]
name: Clothing Details
attributes: Color | Size

IMPORTANT
Use the business and product context as the primary source for deciding which attributes are useful. Do not invent irrelevant specifications. If the context is incomplete, make only conservative, practical assumptions.
PROMPT;
		?>
		<div class="wrap wam-wrap">
			<h1><?php esc_html_e( 'AI Structure Guide', 'woocommerce-attribute-manager' ); ?></h1>
			<p><?php esc_html_e( 'Fill in the business and product context, then copy the prompt into ChatGPT or another AI tool. The generated structure can be pasted into the Attribute Manager.', 'woocommerce-attribute-manager' ); ?></p>

			<textarea id="wam-ai-template" class="large-text code wam-code" rows="40" readonly><?php echo esc_textarea( $prompt ); ?></textarea>

			<p><button type="button" class="button button-primary" id="wam-copy-ai-prompt"><?php esc_html_e( 'Copy AI Prompt', 'woocommerce-attribute-manager' ); ?></button></p>

			<h2><?php esc_html_e( 'Attribute Input Structure', 'woocommerce-attribute-manager' ); ?></h2>
			<pre class="wam-code wam-pre"><?php echo esc_html( "[attribute]\nname: رنگ\nslug: color\nvalues: قرمز | آبی | سبز\n\n[attribute]\nname: سایز\nslug: size\nvalues: S | M | L | XL\n\n[group]\nname: مشخصات لباس\nattributes: رنگ | سایز" ); ?></pre>
		</div>
		<?php
	}
}
