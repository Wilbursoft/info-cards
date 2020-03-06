<?php

/**
 * class.ic-render.php
 * Public (non admin) rendering 
 */

require_once dirname( __FILE__ ) .'/wp-plugin-utils/lib/utils.php'; 
require_once dirname( __FILE__ ) .'/wp-plugin-utils/lib/class.render.php'; 
use wp_info_cards\plugin_utils as utils;

 
// Our plugins render class 
class IC_Render extends utils\Render {
	

	// Constructor
	function __construct() {
	  
	  // Call parent constructor
	  parent::__construct(
	      "info-cards"            // $short_code_tag
	      );
    
  	}
  
  
  // Generate the CSS 
  function render_dynamic_css(){

  
    // Get the option values
    $card_border_colour = utils\get_option_array_value ('info_cards_options', 'ic_card_border_colour', '#000000');
    $card_min_height = utils\get_option_array_value ('info_cards_options', 'ic_card_min_height', '0');

  
    // Set the css properties
    echo (":root {\n");
    echo ("  --card_border_colour: $card_border_colour;\n");
    echo ("  --card_min_height:  $card_min_height" . "px;\n");
    echo ("}\n");
    
  
    // Read the CSS ofthe disk 
    $style_sheet_path = dirname( __FILE__ ) .'/includes/css/render.css';
    readfile($style_sheet_path);
  
  }
  
  // Render a space filler for a card 
  function render_card_space_filler(){
      
     $output = "<div style='visibility: hidden; ' class = 'ic_info_card_block'> </div>";
     return $output;

  }

  // Render a single card
  function render_card($post){
    
    // Open Card
    $output ="<div class='ic_info_card_block'>";
    
      // Title
      $output .="<div class='ic_info_card_title'>";
      $output .= $post->post_title;
      $output .="</div>";  
      
      // Icon
      $icon =get_post_meta($post->ID,'ic_icon',1);
      $output .="<div class='ic_info_card_icon'>";
      $output .="<i class='fas " . $icon . "'></i>";
      $output .="</div>";   
      
      // Excerpt
      $output .="<div class='ic_info_card_excerpt'>";
      $output .=  $post->post_excerpt;
      $output .="</div>";   
      
      // Email
      $email = get_post_meta($post->ID,'ic_contact_email',1);
      $subject = "RE: " . $post->post_title;
      $output .="<a class='ic_info_card_email' href='mailto:" . $email . "?subject=" . $subject . "' >";
      $output .=  $email;
      $output .="</a>";   
    
    // Close card
    $output .="</div>";
    
    // done
    return $output;
  
  }

  // Render the short code 
  function render_shortcode(){
    
    $output = "";
    
    // Trace
    utils\dbg_trace();
    
    // Cols per row
    $max_cols = utils\get_option_array_value('info_cards_options','ic_max_columns',3);
    
    // Build the post query
    $args = array(
    	'numberposts'	=> 200,
    	'post_type'		=> 'ic_info_card'
    );
    
    // Get the posts & check not empty
    $my_posts = get_posts( $args );
    if( ! empty( $my_posts ) ){
  
      // Open block & inner container 
      $output .= "<div class='ic_info_cards_list_block' >";
        $output .= "<div class='ic_info_cards_list_inner_container' >"; 
        
          // Loop posts
          $cur_col = 0;
          $row_open = false;
        	foreach ( $my_posts as $p ){
        	  
        	  // Open new row ??
        	  if (0 == $cur_col) {
        	    $output .= "<div class='ic_info_cards_list_row' >";
        	    $row_open = true;
        	  }
        	  
        	  // Do card
        	  $output .= $this->render_card($p);
        	  
            // Inc. row
            $cur_col++;
        		
        	  // Close  row ??
    	      if ($max_cols == $cur_col) {
              $output .= "</div>";
              $cur_col = 0;
              $row_open = false;
        	  }
        		
        	}
  
          // Do we need close the last row ??
        	if ($row_open) {
        	  
              // Fill spaces
              $space_fillers = $max_cols - $cur_col;
              for ($count = 0; $count <  $space_fillers; $count++ ){
                $output .= $this->render_card_space_filler();
              }
              
              // Close the row
              $output .= "</div>";
          }
        	  
        
        // Close block & inner container 
        $output .= '</div>';
      $output .= '</div>';
    }
  
    // Done
    return $output;
  }

}

