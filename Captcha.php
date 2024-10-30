<?php
/*
* Plugin Name: Captchinoo, admin login page protection with Google recaptcha
* Description: Captchinoo Captcha plugin is the best security solution that protects your WordPress login form from spam entries.
* Version: 4.1
* Author: wp-buy
* Text Domain: captchinoo-captcha-for-login-form-protection
* Domain Path: /languages
* Author URI: https://profiles.wordpress.org/wp-buy/#content-plugins
* License: GPL2
 */
if ( ! defined( 'ABSPATH' ) ) exit;// Exit if accessed directly include('notifications.php');

function cap_free_ver_deactivate_pro() {
    if ( is_plugin_active( 'captchinoo-captcha-for-login-form-protection-pro/Captcha.php' ) )
    {
        deactivate_plugins('captchinoo-captcha-for-login-form-protection-pro/Captcha.php');
    }
}

register_activation_hook(__FILE__, 'cap_free_ver_deactivate_pro');

include plugin_dir_path( __FILE__ ) . '/inc/plugin_menues_full.php';

//---------------------------------------------------------------------------------------------
//The upgrade hook process
//---------------------------------------------------------------------------------------------
add_action( 'upgrader_process_complete', function(){ cap_free_ver_modify_old_settings(); },10, 2);
cap_free_ver_modify_old_settings();
function cap_free_ver_modify_old_settings()
{
	$old_option_name = 'logincform_options';
	
	$old_options = get_option( $old_option_name );
	
	$new_options = array();
	
	if(is_array($old_options) && !empty($old_options))
	{
		// Call the function to replace the prefix in the array
		$new_options = cap_free_ver_replacePrefix($old_options, "logincform_", "cap_free_ver_");
	}
	
	if(is_array($new_options)  && !empty($new_options))
	{
		if(get_option( $old_option_name ) != null)
		{
			update_option( 'cap_free_ver_options' , $new_options );
		}
		else
		{
			$deprecated = ' ';
			$autoload = 'no';
			add_option( 'cap_free_ver_options' , $new_options, $deprecated, $autoload );
		}
	}
	else
	{
		$defaults_array = array(
			"cap_free_ver_status" => "1",
			"cap_free_ver_mode" => "Swipecaptcha",
			"cap_free_ver_place" => array("login_form" => "login_form"),
			"Google_reCAPTHA_site_key" => "",
			"Google_reCAPTHA_secret_key" => "",
			"Google_reCAPTHA3_site_key" => "",
			"Google_reCAPTHA3_secret_key" => "");
		if(get_option( $old_option_name ) != null)
		{
			update_option( 'cap_free_ver_options' , $defaults_array );
		}
		else
		{
			$deprecated = ' ';
			$autoload = 'no';
			add_option( 'cap_free_ver_options' , $defaults_array, $deprecated, $autoload );
		}
	}
	
	delete_option( $old_option_name ); //Remove the old options
}

// Function to replace prefix in the array
function cap_free_ver_replacePrefix(&$array, $oldPrefix, $newPrefix) {
	$newArray = array();
	foreach ($array as $key => $value) {
		$newKey = str_replace($oldPrefix, $newPrefix, $key);
		if (is_array($value)) {
			$newValue = cap_free_ver_replacePrefix($value, $oldPrefix, $newPrefix);
		} else {
			$newValue = $value;
		}
		$newArray[$newKey] = $newValue;
	}
	return $newArray;
}

//---------------------------------------------------------------------------------------------
//Load plugin textdomain to load translations/
//---------------------------------------------------------------------------------------------
if ( ! function_exists( 'cap_free_ver_load_textdomain' ) ) {
    function cap_free_ver_load_textdomain(){
        load_plugin_textdomain('captchinoo-captcha-for-login-form-protection', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    add_action('init', 'cap_free_ver_load_textdomain');}
//--------------------------------------------------------------------------------------------

define( 'cap_free_ver_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'cap_free_ver_PLUGIN_URL', plugin_dir_url(__FILE__) );
require_once( cap_free_ver_PLUGIN_DIR . '/admin/setting.php' );
$options = get_option( 'cap_free_ver_options' );
if(isset($options['cap_free_ver_status']) && $options['cap_free_ver_status'] == 1 && isset($options['cap_free_ver_mode'])){
    if($options['cap_free_ver_mode'] == 'IconCaptcha'){
        if(extension_loaded('gd')){
            require_once( cap_free_ver_PLUGIN_DIR . '/template_one.php' );
        }
    }else if($options['cap_free_ver_mode'] == 'Google_reCAPTHA'){
        require_once( cap_free_ver_PLUGIN_DIR . '/google_recaptha.php' );
    }else{
        require_once( cap_free_ver_PLUGIN_DIR . '/template_two.php' );
    }
}
if ( ! function_exists( 'cap_free_ver_filter_action_links' ) ) {
    function cap_free_ver_filter_action_links($links){
        $links['settings'] = sprintf('<a href="%s">%s</a>', admin_url('admin.php?page=cap_free_ver_options_page'), __('Settings', 'captchinoo-captcha-for-login-form-protection'));
        return $links;
    }
    add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'cap_free_ver_filter_action_links', 10, 1);
}
add_action('updated_option', function( $option_name, $old_value, $value ) {
    if($option_name == 'cap_free_ver_options' && !isset($_POST['vertified'])){
        $options = get_option( 'cap_free_ver_options' );
        if(isset($options['cap_free_ver_status']) && $options['cap_free_ver_status'] == 1){
            if(isset($options['cap_free_ver_mode']) && $options['cap_free_ver_mode'] == 'Google_reCAPTHA'){
                $options['cap_free_ver_status'] = 0;
                update_option( 'cap_free_ver_options', $options );
                wp_redirect( admin_url( '/admin.php?page=cap_free_ver_google_recaptcha_v2' ) );
                exit;
            }
        }
    }
}, 10, 3);