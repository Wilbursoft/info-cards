<?php

/**
* Test Utilities
*/

require_once "./render.php";
require_once "./tests/test-custom-post.php";
require_once "./tests/test-settings.php";

class IC_RenderTest extends WP_UnitTestCase
{

    // Helper to create new item
    public function create_post_item($emailValue, $iconValue){
            
        $test_post_id = $this->factory->post->create( array( 'post_title' => 'another test post', 'post_type' => 'ic_info_card' ) );
        update_post_meta( $test_post_id, 'ic_contact_email', $emailValue );
        update_post_meta( $test_post_id, 'ic_icon', $iconValue );
        
        return $test_post_id;
    }
    
    // Helper to generate dynamic css and capture 
    public function hlp_check_if_render_dynamic_css_contains($renderer, $needle){
        
        // Capture the CSS output
        ob_start();
        $renderer->render_dynamic_css();
        $output = ob_get_contents();
        ob_end_clean();
        
        // See if it contains the test 
        return (strpos($output, $needle ) !== false);
    }

    
    // Run the tests
    public function test_methods(){
        
   		
        /**
         *  Test init  
         **/
         
        // Capture state before init
        global $shortcode_tags;
        $before = count($shortcode_tags);
        $this->assertTrue(!isset($shortcode_tags['info-cards']));

        // Do the init
        ob_start();
        $renderer = new ic_render();
        $renderer->fn_register_short_codes();
        $output = ob_get_contents();
        $this->assertTrue("" == $output);
        ob_end_clean();
        
        // Capture state after init
        $after = count($shortcode_tags);
        
        // Assert
        $this->assertTrue($after - 1 == $before);
        $this->assertTrue(isset($shortcode_tags['info-cards']));
        
        // fn_enqueue_scripts
        $this->assertTrue(false === is_style_enqueued('dynamic_css'));
        $renderer->fn_enqueue_scripts();
        $this->assertTrue(false !== is_style_enqueued('dynamic_css'));


        /**
         *  Test rendering - with settings not initialised
         **/
         
        // Create the custom post types
   	    $custom_post_type=IC_CustomPostTest::hlp_create_info_card_post_types();
         
        // Create empty output
        $emptyRender =  $renderer->fn_render_shortcode_info_cards();
        $this->assertTrue(is_valid_html($emptyRender));
        
        // Create a post
        $test_post_id = $this->create_post_item('test@example.com', 'test_icon_value');
        
        // Should have one item
        $oneItemRender = $renderer->fn_render_shortcode_info_cards();
        $this->assertTrue(is_valid_html($oneItemRender));
        
        // Create 15
        for ($x = 0; $x <= 15; $x++) {
            
            // Add the next one
            $email = 'user' . $x . '-@example.com';
            $icon = 'icon_' . $x;
            $test_post_id = $this->create_post_item($email, $icon);

            // Render it all 
            $manyItems = $renderer->fn_render_shortcode_info_cards();
            
            // Check its good
            $this->assertTrue(is_valid_html($manyItems, true));
            $this->assertTrue(false !== strpos($manyItems, $email));
            $this->assertTrue(false !== strpos($manyItems, $icon));
        }


        /**
         *  Test rendering with different settings 
         **/
         
        // Create the settings 
   	    $settings=IC_SettingsTest::hlp_create_settings();
   	    
   	    // Test - ic_card_border_colour
   	    $options = get_option('info_cards_options');
   	    $options ['ic_card_border_colour'] = '#33196d';
   	    
   	    
        $this->assertTrue(false  === $this->hlp_check_if_render_dynamic_css_contains($renderer, '#33196d' ));
   	    update_option('info_cards_options',$options);
        $this->assertTrue(true  === $this->hlp_check_if_render_dynamic_css_contains($renderer, '#33196d' ));

   	    
   	    // Test - ic_card_min_height
   	    $options ['ic_card_min_height'] = '398';

   	    
        $this->assertTrue(false  === $this->hlp_check_if_render_dynamic_css_contains($renderer, '398' ));
   	    update_option('info_cards_options',$options);
        $this->assertTrue(true  === $this->hlp_check_if_render_dynamic_css_contains($renderer, '398' ));

   	    
   	    // Test - ic_max_columns
   	    // Go from 1 to 16 columns
        for ($x = 1; $x <= 16; $x++) {
            
            // Add the next one
            $options = array(
   	        'ic_max_columns' => $x,
   	        );
   	        
   	        $options ['ic_card_min_height'] = $x;

            // Render it all 
            $manyItems = $renderer->fn_render_shortcode_info_cards();
            
            // Check its good
            $this->assertTrue(is_valid_html($manyItems, true));

        }
   	    
   	     
        // Clean up
        IC_SettingsTest::hlp_destroy_settings();
   	    IC_CustomPostTest::hlp_destroy_info_card_post_types();
    }

}

