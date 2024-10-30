<?php ob_start(); // Start output buffering
if ( ! defined( 'ABSPATH' ) ) exit;
// Exit if accessed directly
if( ! class_exists( 'cap_free_ver_view_admin_login_slide' ) ) {
    class cap_free_ver_view_admin_login_slide{
        public function __construct() {
            if (session_status() == PHP_SESSION_NONE && !headers_sent()) {
                session_start();
            }
            if(!isset($_SESSION["ondrage_number"])){
                $_SESSION["ondrage_number"] = rand();
            }
            add_action( 'authenticate', array($this,'add_login_field_validate'), 100  );
            add_action('login_init', array($this,'view_template'), 30 );
        ob_end_flush(); // Flush the output buffer and send it to the browser
		}
        public function add_login_field_validate( $user) {
            $options = get_option( 'cap_free_ver_options' );
            if(!isset($options['cap_free_ver_status']) || $options['cap_free_ver_status'] != 1){
                return;
            }
            if(!isset($options['cap_free_ver_mode'])){
                return;
            }
            if(isset($_POST) || !empty($_POST)){
                if(isset($_POST['ondrage'])){
                    if(!isset($_SESSION["ondrage_number"]) ||  $_SESSION["ondrage_number"] != $_POST['ondrage']) {
                        $user = new WP_Error( 'cap_free_ver_login_failed', '<strong>'.__('ERROR','captchinoo-captcha-for-login-form-protection').'</strong>: '.__('captcha Error !!','captchinoo-captcha-for-login-form-protection') );
                    }
                }else if (!empty($_SERVER['QUERY_STRING'])){
                    $user = new WP_Error( 'cap_free_ver_login_failed', '<strong>'.__('ERROR','captchinoo-captcha-for-login-form-protection').'</strong>: '.__('captcha Error !!','captchinoo-captcha-for-login-form-protection') );
                }
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
            add_action('login_form', array($this,'SliderCaptcha'));
        }
        public function SliderCaptcha(){
            ?>
            <input type="hidden" id="ondrage" name="ondrage" value="">
            <div id="slider_filled_parent">
				<div id="slider_filled"></div>
				<div style="width:100%;margin-bottom: 25px;">
					<div class="slider_captcha_arrow"></div>
				</div>
			</div>
            <?php
        }
        public function IconCaptcha_enqueue() {
            wp_enqueue_style( 'slider-captcha', cap_free_ver_PLUGIN_URL.'assets/css/slider-captcha.css?v=1' );
            wp_enqueue_script( 'jquery-ui-core' );
            wp_enqueue_script( 'jquery-ui-widget' );
            wp_enqueue_script( 'jquery-ui-mouse' );
            wp_enqueue_script( 'jquery-ui-draggable' );
            wp_enqueue_script( 'jquery-ui-droppable' );
            wp_enqueue_script( 'slider-improved', cap_free_ver_PLUGIN_URL.'assets/js/jquery.ui.touch-punch-improved.js', array('jquery'), '2.0.0', true );
            wp_enqueue_script( 'slider-captcha', cap_free_ver_PLUGIN_URL.'assets/js/slider-captcha.js', array('jquery'), '2.0.0', true );
            wp_enqueue_script( 'script-slide-scripts', cap_free_ver_PLUGIN_URL.'assets/js/script_slide.js?ss', array('jquery'), '3.0.0', true );
            wp_localize_script('script-slide-scripts','captcha_link_class',array('hintText'=>__('Swipe to the point','captchinoo-captcha-for-login-form-protection') ,'hintTextAfterUnlock'=>__('You can submit now','captchinoo-captcha-for-login-form-protection') ,'ondrage_number'=>absint($_SESSION["ondrage_number"]) ,));
        }
    }
    new cap_free_ver_view_admin_login_slide();
}