<?php
/*
Plugin Name: MyLiveSignature - Make it personal
Plugin URI: https://www.mylivesignature.com
Description: Add your own free personal signatures to each one of your posts and/or pages automatically.
Version: 2.5.0
Author: MyLiveSignature - support@mylivesignature.com
Author URI: https://www.mylivesignature.com
License: GNU V2
*/

/*  Copyright 2017  MyLiveSignature  (support@mylivesignature.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
?><?php

// some definition we will use
define( 'MLS_PUGIN_NAME', 'MyLiveSignature Plugin');
define( 'MLS_PLUGIN_DIRECTORY', 'my-live-signature');
define( 'MLS_CURRENT_VERSION', '2.5.0' );
define( 'MLS_CURRENT_BUILD', '2' );
define( 'MLS_DEBUG', false);		# never use debug mode on productive systems
// i18n plugin domain for language files
define( 'EMU2_I18N_DOMAIN', 'MLS' );

// load language files
function mls_set_lang_file() {
	# set the language file
	$currentLocale = get_locale();
	if(!empty($currentLocale)) {
		$moFile = dirname(__FILE__) . "/lang/" . $currentLocale . ".mo";
		if (@file_exists($moFile) && is_readable($moFile)) {
			load_textdomain(EMU2_I18N_DOMAIN, $moFile);
		}

	}
}
mls_set_lang_file();

// create custom plugin settings menu
add_action( 'admin_menu', 'mls_create_menu' );

//call register settings function
add_action( 'admin_init', 'mls_register_settings' );

register_activation_hook(__FILE__, 'mls_activate');
register_deactivation_hook(__FILE__, 'mls_deactivate');
register_uninstall_hook(__FILE__, 'mls_uninstall');

// activating the default values
function mls_activate() {
	add_option('mls_sig_id', '');
	add_option('mls_base_url', 'https://www.mylivesignature.com/showsig.php?sid=');
}

// deactivating
function mls_deactivate() {
	// needed for proper deletion of every option
	delete_option('mls_sig_id');
	delete_option('mls_base_url');
}

// uninstalling
function mls_uninstall() {
	# delete all data stored
	delete_option('mls_sig_id');
	delete_option('mls_base_url');
	// delete log files and folder only if needed
	//if (function_exists('mls_deleteLogFolder')) mls_deleteLogFolder();
}

function mls_create_menu() {

	// create new top-level menu
	add_menu_page( 
	__('HTML Title', EMU2_I18N_DOMAIN),
	__('HTML Title', EMU2_I18N_DOMAIN),
	0,
	MLS_PLUGIN_DIRECTORY.'/mls_settings_page.php',
	'',
	plugins_url('/images/icon.png', __FILE__));
	
	
	add_submenu_page( 
	MLS_PLUGIN_DIRECTORY.'/mls_settings_page.php',
	__("MyLiveSignature", EMU2_I18N_DOMAIN),
	__("MyLiveSignature", EMU2_I18N_DOMAIN),
	0,
	MLS_PLUGIN_DIRECTORY.'/mls_settings_page.php'
	);

	// or create options menu page
	add_options_page(__('MyLiveSignature', EMU2_I18N_DOMAIN), __("MyLiveSignature", EMU2_I18N_DOMAIN), 9,  MLS_PLUGIN_DIRECTORY.'/mls_settings_page.php');

}


function mls_register_settings() {
	//register settings
	register_setting( 'mls-settings-group', 'mls_sig_id' );
	register_setting( 'mls-settings-group', 'mls_base_url' );
}

if( !function_exists("plugins_url")){
	function plugins_url($path = '', $plugin = '') {
	 
		$mu_plugin_dir = WPMU_PLUGIN_DIR;
		foreach ( array('path', 'plugin', 'mu_plugin_dir') as $var ) {
			$$var = str_replace('\\' ,'/', $$var); // sanitize for Win32 installs
			$$var = preg_replace('|/+|', '/', $$var);
		}
	 
		if ( !empty($plugin) && 0 === strpos($plugin, $mu_plugin_dir) )
			$url = WPMU_PLUGIN_URL;
		else
			$url = WP_PLUGIN_URL;
	 
		if ( 0 === strpos($url, 'http') && is_ssl() )
			$url = str_replace( 'http://', 'https://', $url );
	 
		if ( !empty($plugin) && is_string($plugin) ) {
			$folder = dirname(plugin_basename($plugin));
			if ( '.' != $folder )
				$url .= '/' . ltrim($folder, '/');
		}
	 
		if ( !empty($path) && is_string($path) && strpos($path, '..') === false )
			$url .= '/' . ltrim($path, '/');
	 
		return apply_filters('plugins_url', $url, $path, $plugin);
	}
}

if( !function_exists("mls_add_top_post")){
	function mls_add_top_post($content){
		$mySigId    = trim(get_option('mls_sig_id'));
		$baseMLSUrl = trim(get_option('mls_base_url'));
		if(sizeof($mySigId)>0 && sizeof($baseMLSUrl)>0) {
			$MLSAddition = '<br><a href="https://www.mylivesignature.com" alt="Signature Maker"><img border="0" style="border:0;!important" src="' . $baseMLSUrl . $mySigId . '" alt="signature" /></a>';
			return $content . $MLSAddition;
		}
		else return $content;
		}
}

/*	Add the MLS filter function to the hook */
add_filter('the_content', 'mls_add_top_post');

?>