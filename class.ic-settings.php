<?php

/**
 * Info Cards Settings
 * Manages admin configurable options and settings for the plugin
 */


// includes 
require_once dirname( __FILE__ ) .'/wp-plugin-utils/lib/utils.php'; 
require_once dirname( __FILE__ ) .'/wp-plugin-utils/lib/class.settings.php'; 
use wp_info_cards\plugin_utils as utils;

// Trace
utils\dbg_trace("");


// Class to handle the settings
class IC_Settings extends utils\Settings {
	

	// Constructor
	function __construct() {
		
		// Call parent 
		parent::__construct(
							"Info Cards",			// $option_page_title
							"wp-info-cards.php",	// $plugin_file_name
							"info_cards_settings",	// $option_group_page
							"info_cards_options"	// $option_name
							);
		
		// Trace
		utils\dbg_trace();
		
		// Our settings sections 
		$this->settings_sections = array (
			
			// Card appearance
			'card_appearance' => array (
				'title'		=> __( 'Card Appearance', 'info_cards' ),
				'desc_html' => "<p>" . __('These options apply to each of the cards.', 'info_cards')  . "</p>"
				),
				
			// Layout 
			'layout' => array (
				'title'		=> __( 'Layout', 'info_cards' ),
				'desc_html' => "<p>" . __('These options apply the layout.', 'info_cards')  . "</p>"
				)
			);

		// Our settings fields 
		$this->settings_fields = array (
			
			// Card height
			'ic_card_min_height'	=> array (
				'title'			=> __('Card Height', 'info_cards'),
				'units'			=> 'px.',
				'section'		=> 'card_appearance',
				'type'			=> 'integer', 
				'default'		=> 300,
				'min'			=> 10, 
				'max'			=> 1000,
				'format_msg'	=> __( 'Card Height needs to be a whole number from 10-1000 px', 'info_cards' ), 
		     				
				),
				
			// Card border colour
			'ic_card_border_colour'	=> array (
				'title'			=> __('Card Border Colour', 'info_cards'),
				'section'		=> 'card_appearance',
				'type'			=> 'colour', 
				'default'		=> '#8224e3',
				'format_msg'	=> __( 'Choose a valid colour for the Card Border Colour field.', 'info_cards' ), 
		     				
				),
			
			// Max columns
			'ic_max_columns'	=> array (
				'title'			=> __('Columns', 'info_cards'),
				'units'			=> '',
				'section'		=> 'layout',
				'type'			=> 'integer', 
				'default'		=> 3,
				'min'			=> 1, 
				'max'			=> 20,
				'format_msg'	=> __( 'Columns needs to be a whole number from 1 to 20.', 'info_cards' ), 

				),		

			);
		
	}
	
	

}



