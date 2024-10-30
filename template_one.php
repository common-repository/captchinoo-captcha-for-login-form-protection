<?php
if( ! class_exists( 'cap_free_ver_view_admin_login_different_icons' ) ) {
    class cap_free_ver_view_admin_login_different_icons{
        public function __construct() {
            add_action( 'authenticate', array($this,'add_login_field_validate'), 100  );

            add_action('login_init', array($this,'view_template'), 30 );
            add_action( 'wp_ajax_captcha_action_callback', array($this,'captcha_action_callback' ));
            add_action( 'wp_ajax_nopriv_captcha_action_callback', array($this,'captcha_action_callback' ));
            require(cap_free_ver_PLUGIN_DIR.'/inc/captcha-session.class.php');
            require(cap_free_ver_PLUGIN_DIR.'/inc/captcha.class.php');

            add_action( 'wp_ajax_my_ajax_get_icon', array($this,'my_ajax_get_icon') );
            add_action( 'wp_ajax_nopriv_my_ajax_get_icon', array($this,'my_ajax_get_icon') );
        }
        public function add_login_field_validate( $user) {
            $options = get_option( 'cap_free_ver_options' );

            if(!isset($options['cap_free_ver_status']) || $options['cap_free_ver_status'] != 1){
                return;
            }

            if(!isset($options['cap_free_ver_mode'])){
                return;
            }
            if(!IconCaptcha::validateSubmission($_POST) && IconCaptcha::validateSubmission($_POST) != true  && !empty($_SERVER['QUERY_STRING'])) {
                $user = new WP_Error( 'cap_free_ver_login_failed', '<strong>'.__('ERROR','captchinoo-captcha-for-login-form-protection').'</strong>: '.__('captcha Error !!','captchinoo-captcha-for-login-form-protection') );
            }
            return $user;
        }
        public function view_template(){
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            $options = get_option( 'cap_free_ver_options' );
            if(!isset($options['cap_free_ver_status']) || $options['cap_free_ver_status'] != 1){
                return;
            }
            if(!isset($options['cap_free_ver_mode'])){
                return;
            }
            add_action( 'login_enqueue_scripts', array($this,'IconCaptcha_enqueue') );
            IconCaptcha::setIconsFolderPath(cap_free_ver_PLUGIN_DIR.'/assets/icons/');
            IconCaptcha::setIconNoiseEnabled(true);
            add_action('login_form', array($this,'IconCaptcha'));
        }
        public function IconCaptcha(){
            ?>
            <div class="captcha-holder"></div>
            <?php
        }
        public function captcha_action_callback1() {
            // HTTP GET - Requesting the actual image.
            if((isset($_GET['hash']) && strlen($_GET['hash']) === 48) && (isset($_GET['cid']) && is_numeric($_GET['cid'])) && !$this->isAjaxRequest()) {
                IconCaptcha::getIconFromHash($_GET['hash'], $_GET['cid']);
                exit;
            }
        }
        public function captcha_action_callback() {
            // HTTP GET - Requesting the actual image.
            if((isset($_GET['hash']) && strlen($_GET['hash']) === 48) && (isset($_GET['cid']) && is_numeric($_GET['cid'])) && !$this->isAjaxRequest()) {
                IconCaptcha::getIconFromHash($_GET['hash'], $_GET['cid']);
                exit;
            }
            // HTTP POST - Either the captcha has been submitted or an image has been selected by the user.
            if(!empty($_POST) && $this->isAjaxRequest()) {
                if(isset($_POST['rT']) && is_numeric($_POST['rT']) && isset($_POST['cID']) && is_numeric($_POST['cID'])) {
                    switch((int)$_POST['rT']) {
                        case 1: // Requesting the image hashes
                            $captcha_theme = (isset($_POST['tM']) && ($_POST['tM'] === 'light' || $_POST['tM'] === 'dark')) ? $_POST['tM'] : 'light';
                            // Echo the JSON encoded array
                            header('Content-type: application/json');
                            exit(IconCaptcha::getCaptchaData($captcha_theme, $_POST['cID']));
                        case 2: // Setting the user's choice
                            if(IconCaptcha::setSelectedAnswer($_POST)) {
                                header('HTTP/1.0 200 OK');
                                exit;
                            }
                            break;
                        default:
                            break;
                    }
                }
            }
            header('HTTP/1.1 400 Bad Request');
            exit;
            // Adds another level of security to the Ajax call.
            // Only requests made through Ajax are allowed.
            // NOTE: THE HEADER CAN BE SPOOFED
            wp_die();
            // this is required to terminate immediately and return a proper response
        }
        public function isAjaxRequest() {
            return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
        }
        function IconCaptcha_enqueue(){
            wp_enqueue_style( 'IconCaptcha-icon-captcha', cap_free_ver_PLUGIN_URL.'assets/css/icon-captcha.min.css?v=1' );
            wp_enqueue_script( 'IconCaptcha-icon-captcha', cap_free_ver_PLUGIN_URL.'assets/js/icon-captcha.min.js', array('jquery'), '3.0.0', true );
            wp_enqueue_script( 'IconCaptcha-scripts', cap_free_ver_PLUGIN_URL.'assets/js/scripts.js', array('jquery'), '2.0.0', true );
            wp_localize_script('IconCaptcha-scripts','captcha_link_class',array('captcha_link' => admin_url( 'admin-ajax.php' ),'header'=>__('Select the image that does not belong in the row','captchinoo-captcha-for-login-form-protection') ,	'correct_top'=>__('Great!','captchinoo-captcha-for-login-form-protection') ,'correct_bottom'=>__('You do not appear to be a robot.','captchinoo-captcha-for-login-form-protection') ,				'incorrect_top'=>__('Oops!','captchinoo-captcha-for-login-form-protection') ,				'incorrect_bottom'=>__('You have selected the wrong image.','captchinoo-captcha-for-login-form-protection') ,)			);
            //wp_localize_script('IconCaptcha-scripts','captcha_link_class',array('captcha_link' => parse_url(cap_free_ver_PLUGIN_URL)['path'].'inc/captcha-request.php','header'=>__('Select the image that does not belong in the row','captchinoo-captcha-for-login-form-protection') ,	'correct_top'=>__('Great!','captchinoo-captcha-for-login-form-protection') ,'correct_bottom'=>__('You do not appear to be a robot.','captchinoo-captcha-for-login-form-protection') ,				'incorrect_top'=>__('Oops!','captchinoo-captcha-for-login-form-protection') ,				'incorrect_bottom'=>__('You have selected the wrong image.','captchinoo-captcha-for-login-form-protection') ,)			);
        }

        function my_ajax_get_icon() {
            session_start();

            // HTTP GET - Requesting the actual image.

            if((isset($_GET['hash']) && strlen($_GET['hash']) === 48) &&
                (isset($_GET['cid']) && is_numeric($_GET['cid'])) && !(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')) {
                IconCaptcha::getIconFromHash($_GET['hash'], $_GET['cid']);
                exit;
            }

            // HTTP POST - Either the captcha has been submitted or an image has been selected by the user.

            if(!empty($_POST) && (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')) {

                if(isset($_POST['rT']) && is_numeric($_POST['rT']) && isset($_POST['cID']) && is_numeric($_POST['cID'])) {

                    switch((int)$_POST['rT']) {

                        case 1: // Requesting the image hashes

                            $captcha_theme = (isset($_POST['tM']) && ($_POST['tM'] === 'light' || $_POST['tM'] === 'dark')) ? $_POST['tM'] : 'light';



                            // Echo the JSON encoded array

                            header('Content-type: application/json');

                            exit(IconCaptcha::getCaptchaData($captcha_theme, $_POST['cID']));

                        case 2: // Setting the user's choice

                            if(IconCaptcha::setSelectedAnswer($_POST)) {
                                header('HTTP/1.0 200 OK');
                                exit;
                            }else{
                                header('HTTP/1.1 400 Bad Request');
                                exit;
                            }

                            break;

                        default:

                            break;

                    }

                }

            }



            header('HTTP/1.1 400 Bad Request');

            wp_die(); // this is required to terminate immediately and return a proper response
        }
    }
    new cap_free_ver_view_admin_login_different_icons();
}



