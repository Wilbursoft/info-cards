<?php

/**
 * Info Cards Settings
 * Manages admin configurable options and settings for the plugin
 */


// includes 
require_once dirname( __FILE__ ) .'/wp-plugin-utils/lib/utils.php'; 
use wp_info_cards\plugin_utils as utils;

// Trace
utils\dbg_trace("");




// Class to handle the settings
class IC_Settings {
	
	// Static constants
	static public $plugin_file_name = "info-cards.php";
	static public $option_group_page = "info_cards_settings";
	static public $option_name = "info_cards_options";
	
	// Initialise in constructor 
	public $settings_sections =  null;
	public $settings_fields = null;
	
	// Constructor
	function __construct() {
		
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
		
		// Register init 
		add_action( 'admin_init', array($this,'fn_register_settings' ));
		
		// Register for settings sub menu
		add_action( 'admin_menu', array($this,'fn_init_settings_submenu' ));

		// Register to add settings link in the plugin page 
		add_filter( 'plugin_action_links', array($this,'fn_init_plugin_action_links'), 10, 2 );
		
		// Scripts and CSS
		add_action( 'admin_enqueue_scripts', array($this,'fn_enqueue_scripts' ));

	}
	
	
	// Enqueue scripts and styles
	function fn_enqueue_scripts($hook) {
	 
		 if("settings_page_" . self::$option_group_page != $hook){
		 	return;
		 }
		 
	    // Add the color picker css file       
	    wp_enqueue_style( 'wp-color-picker' ); 
	     
	    // Include our custom jQuery file with WordPress Color Picker dependency
	    wp_enqueue_script( 'custom-script-handle', plugins_url( 'custom-script.js', __FILE__ ), array( 'wp-color-picker' ), false, true ); 

    
	}
		
	// Init settings structure with wp
	function fn_register_settings() {
		
		// Trace
		utils\dbg_trace();

		// register a new setting
		register_setting( 
				self::$option_group_page, 
				self::$option_name,
				array($this, 'fn_validate_input')
				);
	  
        	
        // Loop through adding sections
		foreach($this->settings_sections as $section_id => $section_details) {

				// register appearance section 
				add_settings_section(
				 $section_id,								// string $id
				 $section_details['title'],					// string $title
				 array($this, 'fn_section_desc_render'),	// callable $callback
				 self::$option_group_page					// string $page
				);

    		}
	
			
		// Loop through adding fields
		$defaults = array();
		foreach($this->settings_fields as $field_id => $field_details) {
			
			// The section should exist	
			$section = $field_details['section'];
			assert(isset ( $this->settings_sections[ $section ] ), "bad section '$section' in field definitions.");

			// Add this field
			add_settings_field( 
				$field_id,								// string $id
				$field_details['title'],				// string $title
				array($this,'fn_field_render'),			// callable $callback
				self::$option_group_page,				// string $page
				$section,								// string $section = 'default' 
				['id' => $field_id] 					// array $args = array() )
			);
			
			// Look for any missing values that need defaults
			$defaults[$field_id] = utils\get_option_array_value(self::$option_name, $field_id, $field_details['default']);

		}
		
		// Update defaults
		update_option( self::$option_name , $defaults);
		
	}
	
	// un register settings
	static function unregister_settings() {
		
		// Trace
		utils\dbg_trace();
		
		// Delete options 
		delete_option( self::$option_name );
		
		// Un register settings
		unregister_setting( self::$option_group_page, self::$option_name );

	}
	
	
	// Init settings sub menu 
	function fn_init_settings_submenu() {
	
		// Trace
		utils\dbg_trace();
		
		// Add the menu item
		add_options_page(
			'Info Cards',							// $page_title
			'Info Cards',							// $menu_title
			'manage_options',						// $capability
			self::$option_group_page,				// $menu_slug
			array($this,'fn_render_options_page')	// $function
		);
		

	}
	
	// Add settings link in plugin page
	function fn_init_plugin_action_links($links_array, $plugin_file_name ){
	
		// Trace
		utils\dbg_trace();
		
		// check its this plugin
		if( false !== strpos( $plugin_file_name, self::$plugin_file_name ) ) {
			
			$url =  get_admin_url(null, 'options-general.php?page=' . self::$option_group_page );
			array_unshift( $links_array, '<a href="' . $url .'">Settings</a>' );
		}
		
		// done
		return $links_array;
	}
	
	// Section call back -  appearance  
	function fn_section_desc_render( $args ){
		
		// Trace
		utils\dbg_trace();
		
		// Check key
		if(!isset($args['id']) or !isset($this->settings_sections[$args['id']])){
			utils\dbg_trace("id not set or setting not found.");
			return;
		}
		
		// Get the setting
		$setting = $this->settings_sections[$args['id']];
		
		// out put the description 
		echo ($setting['desc_html']);
	
	}
	
	// Field call back - card_height
	function fn_field_render( $args ){
	
		// Trace
		utils\dbg_trace();
		
		// Check field 
	    if( !isset($args['id']) or !isset( $this->settings_fields[$args['id']] ) ) {
	    	$msg = "field is unknown";
	        utils\dbg_trace($msg);
	        echo("<p>" . $msg . "</p>");
	        return;
	    }
	     
		// Get the field details
		$id = $args['id'];
		$field_details = $this->settings_fields[$id];
	        
		// Get the current value
		$value = sanitize_text_field(utils\get_option_array_value(self::$option_name, $id, $field_details['default'] ));
		$units = utils\get_value($field_details, 'units', ' ');
		
		// Format the field html
		$field = "";

		// switch on type
        switch($field_details['type']){
        	
			
			case 'colour':
	        
				$field = "<input class='tc-colour-field' id='" . $id . "' type='text' name='" . self::$option_name . "[" . $id . "]' value='" . $value . "'> ". $units . " </input>";
				break;
		
			case 'integer':
			case 'text':
			default:
       
	            // integer, text are the same, assume anything else is the same
				$field = "<input id='" . $id . "' type='text' name='" . self::$option_name . "[" . $id . "]' value='" . $value . "'> ". $units . " </input>";
				break;
	        }
		
		// out put
		echo ($field);
	}
	
	// Input validation call back 
	function fn_validate_input( $input ) {
 
	    // Array to store validated options
	    $output = array();
	     
	    // Loop through incoming options
	    foreach( $input as $id => $value ) {
	         
	        // Check field 
	        if( ! isset( $this->settings_fields[$id] ) ) {
	        	
	        	// No field 
	        	utils\dbg_trace("field is unknown.");
	        	
	        	// Format error 
				add_settings_error( 
					self::$option_name, 
					$id, 
					__( 'Unknown field.', 'info_cards' ), 
					'error' 
					);
	        	
	        	// Next item
	        	continue;
	        }
	    
	        // Get the field details
	        $field_details = $this->settings_fields[$id];
	        
	        // Get current value or default
	        $current_value = utils\get_option_array_value (self::$option_name, $id, $field_details['default']);

	       
	        // switch on type
	        switch($field_details['type']){
	        	
	        	case 'integer':
	        		
	        		// Get min and max
	        		$min = utils\get_value($field_details,'min', - PHP_INT_MAX );
	        		$max = utils\get_value($field_details,'max', PHP_INT_MAX );
	        		
	        		// Check valid
	    			if( !utils\is_valid_integer_in_range($value, 1, 1000) ){
	     			
		     			// Format error 
		     			add_settings_error( 
		     				self::$option_name, 
		     				$id, 
		     				$field_details['format_msg'],
		     				'error'
		     			);
		     				
		     			// Restore current value or default
		     			$value = $current_value;
	    			}
	    			
	    			// Integer done
	        		break;
	        		
	        	case 'colour':
	        		
	        		// check valid
	    			if( !utils\is_valid_colour($value) ){
	     			
		     			// Format error 
		     			add_settings_error( 
		     				self::$option_name, 
		     				$id, 
		     				$field_details['format_msg'],
		     				'error'
		     			);
		     				
		     			// Restore current value or default
		     			$value = $current_value;
	    			}
	        		
	        		// Colour done
	        		break;
	        	
	        	default:
	       
		            // Strip tags and handle quoted strings
		            $value = strip_tags( stripslashes( $value ));
		            
		            // Format error 
					add_settings_error( 
						self::$option_name, 
						$id, 
						__( 'Thats an unkown field.', 'info_cards' ), 
						'error' );
						
					// Trace unexpected type
	            	utils\dbg_trace("unexpected field type.");
	       
	        }
	        
           // Set the output 
           $output [ $id ] = $value;
	    
	    } 
	    
	     
	    // Done 
	    return $output;
	 
	}
	
	
	// Render the form 
	function fn_render_options_page() {
		
		// check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
	 
		
		// open bock 
		echo ("<div class='wrap'>");
		
			// heading 
			echo("<h1>");
				echo esc_html( get_admin_page_title() ); 
			echo("</h1>");
			
			// open form 
			echo("<form action='options.php' method='post'>");
	
				// Security fields
				settings_fields( self::$option_group_page );
				
				// Settings them selves
				do_settings_sections( self::$option_group_page );
				
				// Submit button 
				submit_button( 'Save Settings' );
		
			// Close form
			echo("</form>");
		
		// Close block
		echo("</div>");
		
	}

}



