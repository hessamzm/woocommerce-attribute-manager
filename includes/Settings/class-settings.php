<?php
defined( 'ABSPATH' ) || exit;

final class WAM_Settings {

	private const OPTION = 'wam_settings';

	public static function init(): void {
		add_action( 'admin_init', array( __CLASS__, 'register' ) );
	}

	public static function defaults(): array {
		return array(
			'language'    => 'site',
			'ai_provider' => '',
			'ai_api_url'  => '',
			'ai_api_key'  => '',
			'ai_model'    => '',
		);
	}

	public static function get_all(): array {
		$saved = get_option( self::OPTION, array() );
		return wp_parse_args( is_array( $saved ) ? $saved : array(), self::defaults() );
	}

	public static function get( string $key, $default = null ) {
		$settings = self::get_all();
		return array_key_exists( $key, $settings ) ? $settings[ $key ] : $default;
	}

	public static function register(): void {
		register_setting(
			'wam_settings',
			self::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
				'default'           => self::defaults(),
			)
		);

		add_settings_section(
			'wam_general',
			__( 'General Settings', 'woocommerce-attribute-manager' ),
			'__return_false',
			'wam-settings'
		);

		add_settings_field(
			'language',
			__( 'Interface Language', 'woocommerce-attribute-manager' ),
			array( __CLASS__, 'language_field' ),
			'wam-settings',
			'wam_general'
		);

		add_settings_section(
			'wam_ai',
			__( 'AI API', 'woocommerce-attribute-manager' ),
			array( __CLASS__, 'ai_section' ),
			'wam-settings'
		);

		foreach (
			array(
				'ai_provider' => __( 'AI Provider', 'woocommerce-attribute-manager' ),
				'ai_api_url'  => __( 'API Endpoint', 'woocommerce-attribute-manager' ),
				'ai_api_key'  => __( 'API Key', 'woocommerce-attribute-manager' ),
				'ai_model'    => __( 'Model', 'woocommerce-attribute-manager' ),
			) as $key => $label
		) {
			add_settings_field(
				$key,
				$label,
				array( __CLASS__, 'text_field' ),
				'wam-settings',
				'wam_ai',
				array( 'key' => $key )
			);
		}
	}

	public static function sanitize( $input ): array {
		$input = is_array( $input ) ? $input : array();

		return array(
			'language'    => isset( $input['language'] ) ? sanitize_text_field( $input['language'] ) : 'site',
			'ai_provider' => isset( $input['ai_provider'] ) ? sanitize_text_field( $input['ai_provider'] ) : '',
			'ai_api_url'  => isset( $input['ai_api_url'] ) ? esc_url_raw( $input['ai_api_url'] ) : '',
			'ai_api_key'  => isset( $input['ai_api_key'] ) ? sanitize_text_field( $input['ai_api_key'] ) : '',
			'ai_model'    => isset( $input['ai_model'] ) ? sanitize_text_field( $input['ai_model'] ) : '',
		);
	}

	public static function language_field(): void {
		$value = self::get( 'language', 'site' );
		?>
		<select name="<?php echo esc_attr( self::OPTION ); ?>[language]">
			<option value="site" <?php selected( $value, 'site' ); ?>><?php esc_html_e( 'WordPress Site Language', 'woocommerce-attribute-manager' ); ?></option>
			<option value="en_US" <?php selected( $value, 'en_US' ); ?>>English</option>
			<option value="fa_IR" <?php selected( $value, 'fa_IR' ); ?>>فارسی</option>
		</select>
		<p class="description"><?php esc_html_e( 'Choose the interface language. Additional translations can be added through WordPress language files.', 'woocommerce-attribute-manager' ); ?></p>
		<?php
	}

	public static function ai_section(): void {
		?>
		<p class="description"><?php esc_html_e( 'API configuration is reserved for the automated AI workflow planned for future versions. Version 2 stores these settings but does not send requests automatically.', 'woocommerce-attribute-manager' ); ?></p>
		<?php
	}

	public static function text_field( array $args ): void {
		$key = $args['key'];
		$value = (string) self::get( $key, '' );
		$type = 'ai_api_key' === $key ? 'password' : 'text';
		?>
		<input type="<?php echo esc_attr( $type ); ?>"
			class="regular-text"
			name="<?php echo esc_attr( self::OPTION ); ?>[<?php echo esc_attr( $key ); ?>]"
			value="<?php echo esc_attr( $value ); ?>"
			autocomplete="off">
		<?php
	}

	public static function render(): void {
		if ( ! WAM_Helper::can_manage() ) {
			wp_die( esc_html__( 'You do not have permission to view this page.', 'woocommerce-attribute-manager' ) );
		}
		?>
		<div class="wrap wam-wrap">
			<h1><?php esc_html_e( 'Settings', 'woocommerce-attribute-manager' ); ?></h1>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'wam_settings' );
				do_settings_sections( 'wam-settings' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}
}
