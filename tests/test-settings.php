<?php

/**
* Test Utilities
*/
require_once dirname( __FILE__ ) .'/../wp-plugin-utils/lib/utils.php'; 
use wp_info_cards\plugin_utils as utils;

require_once "./settings.php";



class IC_SettingsTest extends WP_UnitTestCase
{

    // Check if settings exists
    static function settings_exist(){
        
        // See if we can get a settings 
        $invalid = 'invalid';
        $value = utils\get_option_array_value(ic_settings::$option_name, 'ic_card_min_height', $invalid);
        return ($invalid != $value);
    }
    
    // Helper to create the custom post type
    static function hlp_create_settings(){

        // Our settings shoud NOT be present
        WP_UnitTestCase::assertTrue(! IC_SettingsTest::settings_exist());
        
        // Create the is_settings 
        $ic_settings = new ic_settings();
        $ic_settings->fn_register_settings();
        
        // Our settings should be present
        WP_UnitTestCase::assertTrue(IC_SettingsTest::settings_exist());
        
        // Return the object
       return $ic_settings;
    }
    
    // Helper to destroy the custom post type
    static function hlp_destroy_settings(){
        
        // Our settings should be present
        WP_UnitTestCase::assertTrue(IC_SettingsTest::settings_exist());
        
        // Unregister them
        ic_settings::unregister_settings();
        
        // Our settings shoud NOT be present
        WP_UnitTestCase::assertTrue(! IC_SettingsTest::settings_exist());

    }
    
   
    
    // Run the tests
    public function test_methods(){
        
        
      
        
        // Switch to admin user
        $admin_user_id = $this->factory->user->create( array('role' => 'administrator') );
        wp_set_current_user( $admin_user_id );
        $this->assertTrue( current_user_can( 'manage_options' ));

        // Register settings and set default options 
        $settings = IC_SettingsTest::hlp_create_settings();
        
        
        // Test - fn_enqueue_scripts
        $settings->fn_enqueue_scripts("wronghook");
        $this->assertTrue(false === utils\is_script_enqueued('custom-script.js'));
        $settings->fn_enqueue_scripts("settings_page_info_cards_settings");
        $this->assertTrue(true === utils\is_script_enqueued('custom-script.js'));

        
        /**
         * Test fn_init_settings_submenu
         **/
         
        // Exercise this code - nothing to test
        $settings->fn_init_settings_submenu();
   
         /**
         * Test fn_init_plugin_action_links
         **/
         
        // Check link not inserted 
        $links_array = $settings->fn_init_plugin_action_links(array(),  "otherpage.php");
        $this->assertTrue( 0 == count($links_array));
        
        // Check link inserted 
        $links_array = $settings->fn_init_plugin_action_links(array(),  "info-cards.php");
        $this->assertTrue( 1 == count($links_array));
        $this->assertTrue( utils\is_valid_html($links_array[0]));
   
        /**
        * Test fn_section_desc_render
        **/
        ob_start();
        $settings->fn_section_desc_render(array());
       // $settings->fn_section_desc_render(array('id' => 'bad_id'));
        $output = ob_get_contents();
        $this->assertTrue( utils\is_valid_html($output));
        ob_end_clean();
         
        /**
         * Test fn_field_render
         **/
        ob_start();
        $settings->fn_field_render(array());
        $output = ob_get_contents();
        $this->assertTrue( utils\is_valid_html($output));
        ob_end_clean();    
        
         /**
         * Test fn_render_options_page
         **/     
        
        // Standard user should produce no form 
   	    $std_user_id = $this->factory->user->create( array('role' => 'user') );
        wp_set_current_user( $std_user_id );
        $this->assertTrue(! current_user_can( 'manage_options' ) );
        
        ob_start();
        $settings->fn_render_options_page();
        $output = ob_get_contents();
        $this->assertTrue( utils\is_valid_html($output));
        $this->assertTrue( false === strpos($output, 'form'));
        ob_end_clean();
        
        // Switch back admin user - should produce form 
        $admin_user_id = $this->factory->user->create( array('role' => 'administrator') );
        wp_set_current_user( $admin_user_id );
        $this->assertTrue( current_user_can( 'manage_options' ) );
    
        ob_start();
        $settings->fn_render_options_page();
        $output = ob_get_contents();
        $this->assertTrue( utils\is_valid_html($output));
        $this->assertTrue( false !== strpos($output, 'form'));
        ob_end_clean();    
        
        /**
         * Test fn_validate_input
         **/     

        
        // No errors yet
        $error_count = 0;
        $this->assertTrue($error_count === count(get_settings_errors(ic_settings::$option_name)));
        
        // Not a number 
        $input = array(
            'ic_card_min_height' => 'not a number'
        );    
        $error_count++;
        $settings->fn_validate_input($input);
        $this->assertTrue($error_count === count(get_settings_errors(ic_settings::$option_name)));

        // Out of range
        $input = array(
            'ic_card_min_height' => '-12'
        );
        $error_count++;
        $settings->fn_validate_input($input);
        $this->assertTrue($error_count === count(get_settings_errors(ic_settings::$option_name)));
        
        // unknown field
        $input = array(
            'bad_index' => 'some field value'
        );
        $error_count++;
        $settings->fn_validate_input($input);
        $this->assertTrue($error_count === count(get_settings_errors(ic_settings::$option_name)));
        
        // Bad colour
        $input = array(
            'ic_card_border_colour' => 'dead beef is bad colour'
        );
        $error_count++;
        $settings->fn_validate_input($input);
        $this->assertTrue($error_count === count(get_settings_errors(ic_settings::$option_name)));
     
        // Good colour
        $input = array(
            'ic_card_border_colour' => '#8994e3'
        );
        $settings->fn_validate_input($input);
        $this->assertTrue($error_count === count(get_settings_errors(ic_settings::$option_name)));
     
        // Bad type
        $settings->settings_fields['ic_bad_field'] = array (
				'title'			=> 'title',
				'section'		=> 'card_appearance',
				'type'			=> 'invalid_type', 
				'default'		=> 'default',
				'format_msg'	=> 'bad msg'

				);
				
	 
        $input = array(
            'ic_bad_field' => 'some value'
        );
        $error_count++;
        $settings->fn_validate_input($input);
        $this->assertTrue($error_count === count(get_settings_errors(ic_settings::$option_name)));
			
		unset($settings->settings_fields['ic_bad_field']);
      
        // Clean up
        IC_SettingsTest::hlp_destroy_settings();

       
    }

}


