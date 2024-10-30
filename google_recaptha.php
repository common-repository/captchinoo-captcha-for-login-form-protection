<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if( ! class_exists( 'cap_free_ver_view_admin_login_recaptha' ) ) {
	class cap_free_ver_view_admin_login_recaptha{
		public function __construct() {
			if(!empty($_SERVER['QUERY_STRING'])){
				add_action( 'authenticate', array($this,'add_login_field_validate'), 100  );
			}
			add_action('login_init', array($this,'view_template'), 30 );
		}

		public function add_login_field_validate( $user) {

			$options = get_option( 'cap_free_ver_options' );

			if(!isset($options['cap_free_ver_status']) || $options['cap_free_ver_status'] != 1){

				return true;

			}

			if(!isset($options['cap_free_ver_mode'])){

				return true;

			}

			if(isset($_POST["g-recaptcha-response"])) $response = sanitize_text_field($_POST["g-recaptcha-response"]);

            if(!isset($response)){

                $user = new WP_Error( 'cap_free_ver_login_failed', '<strong>'.__('ERROR','captchinoo-captcha-for-login-form-protection').'</strong>: '.__('Google captcha Error !!','captchinoo-captcha-for-login-form-protection') );

                return $user;

            }

            $secretKey = $options['Google_reCAPTHA_secret_key'];


			$endpoint = 'https://www.google.com/recaptcha/api/siteverify';

			$body = array(
				'secret'  => $secretKey,
				'response' => $response,
				'remoteip' => $_SERVER['REMOTE_ADDR']
				);


			$options = array(
				'body'        => $body,
			);

			$response = wp_remote_post( $endpoint, $options );
			$response = json_decode($response['body']);
			if ($response->success==false) {
				$user = new WP_Error( 'cap_free_ver_login_failed', '<strong>'.__('ERROR','captchinoo-captcha-for-login-form-protection').'</strong>: '.__('Google captcha Error !!','captchinoo-captcha-for-login-form-protection') );
				return $user;
			}
			return $user;


		}

		public function view_template(){



			$options = get_option( 'cap_free_ver_options' );

			if(!isset($options['cap_free_ver_status']) || $options['cap_free_ver_status'] != 1){

				return;

			}

			if(!isset($options['cap_free_ver_mode'])){

				return;

			}

            add_action( 'login_enqueue_scripts', array($this,'IconCaptcha_enqueue') );

				add_action('login_form', array($this,'IconCaptcha'));





		}

		public function IconCaptcha(){

            $options = get_option( 'cap_free_ver_options' );
			?>
			<div class="captcha_wrapper" style="margin-left: -15px;margin-bottom: 15px;">
				<div class="g-recaptcha" data-sitekey="<?php echo esc_attr($options['Google_reCAPTHA_site_key']);?>"></div>
			</div>

			<?php

		}



		function IconCaptcha_enqueue() {
			wp_enqueue_script( 'google-recaptcha', 'https://www.google.com/recaptcha/api.js', array('jquery'), '3.0.0', false );

		}



	}

	new cap_free_ver_view_admin_login_recaptha();

}
if ( ! function_exists( 'cap_free_ver_gioga_add_async_defer_attribute' ) ) {

    function cap_free_ver_gioga_add_async_defer_attribute($tag, $handle)
    {
        if ('google-recaptcha' !== $handle) {
            return $tag;
        }
        return str_replace(' src', ' async defer src', $tag);
    }
    add_filter('script_loader_tag', 'cap_free_ver_gioga_add_async_defer_attribute', 10, 2);
}