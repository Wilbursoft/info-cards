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
include_once dirname( __FILE__ ) .'/utils.php';
include_once dirname( __FILE__ ) .'/render.php';
include_once dirname( __FILE__ ) .'/settings.php';
include_once dirname( __FILE__ ) .'/custom-post.php';


// actions
dbg_trace("adding actions");



// hooks
dbg_trace("registering hooks");
register_activation_hook(__FILE__, 'info_cards_activate');
function info_cards_activate(){
    dbg_trace();
}

register_deactivation_hook(__FILE__, 'info_cards_deactivate');
function info_cards_deactivate(){
    dbg_trace();
}

// 'WilburSoft WordPress Plugins' font awesome kit
fa_custom_setup_kit('https://kit.fontawesome.com/79e09bb404.js');



// Java script
wp_enqueue_script( 
			'info-cards', 
			plugin_dir_url( __FILE__ ) . 'includes/js/info-cards.js',
			array( 'jquery' ),
			time()
			);



dbg_trace("creating ic_info_cards object");
new ic_info_card();

dbg_trace("creating ic_settings object");
new ic_settings();

dbg_trace("creating ic_render object");
new ic_render();




