<?php

/**
* Plugin Name: Info Cards 
* Plugin URI: https://wilbursoft.com
* Description: Display a customisable and reactive grid of 'info cards' with icon, description, contact info for each.
* Version: 1.0.0
* Author: Guy Roberts @ Wilbursoft 
* Author URI: https://wilbursoft.com
* License: GPL2
* Text Domain: info-cards
*/



// includes 
require_once dirname( __FILE__ ) .'/wp-plugin-utils/lib/utils.php'; 
use wp_info_cards\plugin_utils as utils;
require_once dirname( __FILE__ ) .'/class.ic-render.php';
require_once dirname( __FILE__ ) .'/class.ic-settings.php';
require_once dirname( __FILE__ ) .'/class.ic-custom-post.php';


// actions
utils\dbg_trace("adding actions");



// hooks
utils\dbg_trace("registering hooks");
register_activation_hook(__FILE__, 'info_cards_activate');
function info_cards_activate(){
    utils\dbg_trace();
}

register_deactivation_hook(__FILE__, 'info_cards_deactivate');
function info_cards_deactivate(){
    utils\dbg_trace();
}

// 'WilburSoft WordPress Plugins' font awesome kit
utils\fa_custom_setup_kit('https://kit.fontawesome.com/79e09bb404.js');

// The app class
class IC_Info_Cards_App {
	

	// Constructor
	function __construct() {
		
	
    	// Scripts and CSS
		add_action( 'wp_enqueue_scripts', array($this, 'fn_enqueue_scripts') );
		add_action( 'admin_enqueue_scripts', array($this, 'fn_enqueue_scripts') );
		
	}
	
	// Enqueue scripts and styles
	function fn_enqueue_scripts() {
		
		// Java script
		wp_enqueue_script( 
			'info-cards', 
			plugin_dir_url( __FILE__ ) . 'includes/js/info-cards.js',
			array( 'jquery' ),
			time()
			);
	 
	}
	

}


utils\dbg_trace("creating IC_Info_Cards_App object");
new IC_Info_Cards_App();

utils\dbg_trace("creating IC_Info_Card object");
new IC_Info_Card();

utils\dbg_trace("creating IC_Settings object");
new IC_Settings();

utils\dbg_trace("creating IC_Render object");
new IC_Render();




