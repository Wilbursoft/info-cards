<?php

/**
* Test Utilities
*/

require_once "./utils.php";
require_once "./custom-post.php";



class IC_CustomPostTest extends WP_UnitTestCase
{

    /**
     * helpers for this and other tests
     **/
    
    // Helper to create the custom post type
    static function hlp_create_info_card_post_types(){
        
        // Get custom post types
        $get_cpt_args = array(
        'public'   => true,
        '_builtin' => false
        );
        
         
        // Create the ic_info custom post type
        $custom_post_type = new ic_info_card();
        $custom_post_type->fn_create_post_type();
        
        // Should be one now..
        $types = get_post_types( $get_cpt_args, 'object' );
        WP_UnitTestCase::assertTrue(isset($types['ic_info_card']));
        WP_UnitTestCase::assertTrue(1 == count($types));
        
        // Return the object
       return $custom_post_type;
    }
    
    // Helper to destroy the custom post type
    static function hlp_destroy_info_card_post_types(){
        
        // Get custom post types
        $get_cpt_args = array(
        'public'   => true,
        '_builtin' => false
        );
        
        // Check that is is there 
        $types = get_post_types( $get_cpt_args, 'object' );
        WP_UnitTestCase::assertTrue(1 == count($types));
        
        // Un register it
        ic_info_card::destroy_post_type();

        
        // Check that is has gone now
        $types = get_post_types( $get_cpt_args, 'object' );
        WP_UnitTestCase::assertTrue(! isset($types['ic_info_card']));
        WP_UnitTestCase::assertTrue(0 == count($types));
        
    }
    

    
    /**
     * Test cases
     **/
     
    public function test_methods(){
   		
        dbg_trace("checking custom post types");
        
        /*
   	    * Custom post type 
   	    */
   		
   	    // To simplify code 
   	    $custom_post_type = IC_CustomPostTest::hlp_create_info_card_post_types();

          
        /*
   	    * Test post 'ic_info_card' creation 
   	    */
   	    
        // Build the post query
        $args = array(
          'numberposts'	=> 200,
          'post_type'	=> 'ic_info_card'
        );
  	    
  
        // Get the posts - should not be any
        $ic_posts = get_posts( $args );
        $this->assertTrue(empty( $ic_posts ));
        
        
        // Create a post
        $post = $this->factory->post->create( array( 'post_title' => 'Test Post', 'post_type' => 'ic_info_card' ) );


        // This time should have one 
        $ic_posts = get_posts( $args );
        $this->assertTrue(! empty( $ic_posts ));



        /*
   	    * Test fn_quick_edit_custom_box ( $column_name,  $post_type )
   	    */
   	    
   	    
   	    // helper to capture echo
   	    function helper_fn_quick_edit_custom_box( $custom_post_type, $column_name,  $post_type){
   	        ob_start();
   	        $custom_post_type->fn_quick_edit_custom_box($column_name,  $post_type);
   	        $output = ob_get_contents();
            ob_end_clean();
   	        return $output;
   	    }
   	    
   	    $this->assertTrue(empty(helper_fn_quick_edit_custom_box($custom_post_type, "invalid","invalid")));
   	    $this->assertTrue(empty(helper_fn_quick_edit_custom_box($custom_post_type, "ic_contact_email","invalid")));
   	  
   	    $email_input = helper_fn_quick_edit_custom_box($custom_post_type, "ic_contact_email","ic_info_card");
   	    $this->assertTrue(!empty($email_input));
   	    $this->assertTrue(!is_valid_html($email_input));
   	    
   	    $icon_input = helper_fn_quick_edit_custom_box($custom_post_type, "ic_icon","ic_info_card");
   	    $this->assertTrue(!empty($icon_input));
   	    $this->assertTrue(!is_valid_html($icon_input));
   	    
   	    // Valid when put together the right way
   	    $combined = $icon_input . $email_input;
   	    $this->assertTrue(is_valid_html($combined ));

        /*
   	    * Test 'fn_post_edit_save( $post_id )
   	    */
   	    
   	    // Create a post
        $test_post_id = $this->factory->post->create( array( 'post_title' => 'another test post', 'post_type' => 'ic_info_card' ) );
        
        // Mark fields as unset
        $unsetvalue = 'unsetvalue';
        update_post_meta( $test_post_id, 'ic_contact_email', $unsetvalue );
        update_post_meta( $test_post_id, 'ic_icon', $unsetvalue );

        // Try and set with empty POST - should fail
        $custom_post_type->fn_post_edit_save($test_post_id);
        $this->assertTrue(get_post_meta($test_post_id,'ic_contact_email',true) == $unsetvalue );
   	    
   	    // Set the variable on the post  - should still fail
   	    $email_value = "user@example.com";
   	    $_POST['email'] = $email_value;
   	    $custom_post_type->fn_post_edit_save($test_post_id);
        $this->assertTrue(get_post_meta($test_post_id,'ic_contact_email',true) == $unsetvalue );

   	    // Give user edit rights - still fail nonse not set
   	    $this->assertTrue( ! current_user_can( 'edit_post', $test_post_id ) );
   	    $admin_user_id = $this->factory->user->create( array('role' => 'administrator') );
        wp_set_current_user( $admin_user_id );
        $this->assertTrue(current_user_can( 'edit_post', $test_post_id ) );
        $custom_post_type->fn_post_edit_save($test_post_id);
        $setvalue = get_post_meta($test_post_id,'ic_contact_email',true);
   	    $this->assertTrue(get_post_meta($test_post_id,'ic_contact_email',true) == $unsetvalue );
   	    
   	    // Spoof nonce - should work now
   	    $_POST['ic_info_card_nonce'] = wp_create_nonce('ic_info_card_edit');
        $custom_post_type->fn_post_edit_save($test_post_id);
   	    $this->assertTrue(get_post_meta($test_post_id,'ic_contact_email',true) == $email_value );

        // Use the icon now
        $icon_value = "testicon";
        $_POST['icon'] = $icon_value;
   	    $this->assertTrue(get_post_meta($test_post_id,'ic_icon',true) == $unsetvalue );

   	    // Standard user should fail
   	    $std_user_id = $this->factory->user->create( array('role' => 'user') );
        wp_set_current_user( $std_user_id );
        $this->assertTrue(! current_user_can( 'edit_post', $test_post_id ) );
        $custom_post_type->fn_post_edit_save($test_post_id);
   	    $this->assertTrue(get_post_meta($test_post_id,'ic_icon',true) == $unsetvalue );
        
        // Back to admin sould pass 
        wp_set_current_user( $admin_user_id );
        $custom_post_type->fn_post_edit_save($test_post_id);
        $this->assertTrue(get_post_meta($test_post_id,'ic_icon',true) == $icon_value );

        /*
   	    * Test fn_columns($columns)
   	    */
   	    $columns = array(
   	        'date' => 'testvalue'
   	        );
   	        
   	    $columns = $custom_post_type->fn_columns($columns);
   	    $this->assertTrue(! isset($columns['date']));
   	    $this->assertTrue(isset($columns['ic_contact_email']));
   	    
   	    
   	    /*
   	    * Test fn_column_data($column,$post_id)
   	    */
   	    function helper_fn_column_data($custom_post_type, $column, $post_id){
   	        ob_start();
   	        $custom_post_type->fn_column_data($column,$post_id);
   	        $output = ob_get_contents();
            ob_end_clean();
   	        return $output;
   	    }
   	    $this->assertTrue( $email_value == helper_fn_column_data($custom_post_type,'ic_contact_email',$test_post_id));
   	    $this->assertTrue( false !== strpos( helper_fn_column_data($custom_post_type,'ic_icon',$test_post_id), $icon_value));

   	    /*
   	    * Test join($wp_join) 
   	    */
   	    
   	    $wp_join = "";
   	    set_query_var('post_type', 'ic_info_card');
   	    $this->assertTrue( strpos($custom_post_type->join($wp_join), 'SELECT' ) !== false);
   	    set_query_var('post_type', 'somethingelse');
   	    $this->assertTrue( strpos($custom_post_type->join($wp_join), 'SELECT' ) === false);
   	    
   	    /*
   	    * Test set_default_sort($orderby,&$query) 
   	    */
   	    $orderby = "";
   	    $query = "";
  	    set_query_var('post_type', 'ic_info_card');
   	    $this->assertTrue( strpos($custom_post_type->set_default_sort($orderby,$query), 'contact_email' ) !== false);
   	    set_query_var('post_type', 'somethingelse');
   	    $this->assertTrue( strpos($custom_post_type->set_default_sort($orderby,$query), 'contact_email' ) === false);
   	    
   	    /*
   	    * Test n_add_meta_box()
   	    */
   	    
   	    $custom_post_type->fn_add_meta_box();
   	    
   	    /*
   	    * Test hlp_render_icon_input_field()
   	    */

   	    ob_start();
   	    $custom_post_type->hlp_render_icon_input_field("test_icon_value", false);
        $output = ob_get_contents();
        ob_end_clean();
    	$this->assertContains('test_icon_value', $output);
   	    $this->assertTrue(is_valid_html($output));  
   	    
   	    ob_start();
   	    $custom_post_type->hlp_render_icon_input_field("test_icon_value", true);
        $output = ob_get_contents();
        ob_end_clean();
    	$this->assertContains('test_icon_value', $output);
   	    $this->assertTrue(is_valid_html($output));  
   	    
   	    /*
   	    * Test hlp_render_email_input_field()
   	    */

   	    ob_start();
   	    $custom_post_type->hlp_render_email_input_field("test_email_value", false);
        $output = ob_get_contents();
        ob_end_clean();
    	$this->assertContains('test_email_value', $output);
   	    $this->assertTrue(is_valid_html($output));  
   	   
   	    ob_start();
   	    $custom_post_type->hlp_render_email_input_field("test_email_value", true);
        $output = ob_get_contents();
        ob_end_clean();
    	$this->assertContains('test_email_value', $output);
   	    $this->assertTrue(is_valid_html($output));   
   	    
   	    /*
   	    * Test fn_render_meta_box()
   	    */
   	    
   	    // Create a post
        $test_post_id = $this->factory->post->create( array( 'post_title' => 'meta box test post', 'post_type' => 'ic_info_card' ) );
 
  
   	    ob_start();
        $custom_post_type->fn_render_meta_box(get_post($test_post_id));
        $output = ob_get_contents();
        ob_end_clean();
   	    $this->assertTrue(!empty($output));
       	$this->assertTrue(is_valid_html($output));
   	    
   	    // Clean up
   	    IC_CustomPostTest::hlp_destroy_info_card_post_types();
   	    
    }

}

?>
