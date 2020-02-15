<?php


// class for info card custom post type
class ic_info_card {
	
	static public $post_type = 'ic_info_card';
	static public $meta_box_id = 'ic_meta_box_id';

	function ic_info_card() {
		
		// Actions
		add_action('add_meta_boxes', array ($this,'fn_add_meta_box') );
		add_action('init',array($this,'fn_create_post_type'));
		add_action('manage_ic_info_card_posts_columns',array($this,'fn_columns'),10,2);
		add_action('manage_ic_info_card_posts_custom_column',array($this,'fn_column_data'),11,2);
		add_action('quick_edit_custom_box',array($this,'fn_quick_edit_custom_box'),11,2);
		add_action('save_post', array($this,'fn_post_edit_save'),11,2);

		// Filters
		add_filter('posts_join',array($this,'join'),10,1);
		add_filter('posts_orderby',array($this,'set_default_sort'),20,2);

	}

	function fn_create_post_type() {
		$labels = array(
			'name'               => 'Info Cards',
			'singular_name'      => 'Info Card',
			'menu_name'          => 'Info Cards',
			'name_admin_bar'     => 'Info Cards',
			'add_new'            => 'Add New',
			'add_new_item'       => 'Add New Info Card',
			'new_item'           => 'New Card',
			'edit_item'          => 'Edit Card',
			'view_item'          => 'View Card',
			'all_items'          => 'All Cards',
			'search_items'       => 'Search Info Cards',
			'parent_item_colon'  => 'Parent Card',
			'not_found'          => 'No Info Cards Found',
			'not_found_in_trash' => 'No Info Cards Found in Trash'
		);

		$args = array(
			'labels'              => $labels,
			'public'              => true,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'show_ui'             => true,
			'show_in_nav_menus'   => true,
			'show_in_menu'        => true,
			'show_in_admin_bar'   => true,
			'menu_position'       => 5,
			'menu_icon'           => 'dashicons-admin-appearance',
			'capability_type'     => 'post',
			'hierarchical'        => false,
			'supports'            => array( 'title', 'editor', 'reorder' , /*'author', 'thumbnail', */ 'excerpt', 'comments'),
			'has_archive'         => true,
			'rewrite'             => array( 'slug' => 'info_cards' ),
			'query_var'           => true
		);

		// Regiter post
		register_post_type( self::$post_type, $args );
		

	}

	// Destroy the post type
	static function destroy_post_type(){
		unregister_post_type( self::$post_type );
	}
	
	// Call back to add the meta box
	function fn_add_meta_box(){
		
		
		// trace
		dbg_trace();
		
		add_meta_box( 
	        self::$meta_box_id,  					// string $id
	        'Contact & Icon', 						// string $title
	        array ($this, 'fn_render_meta_box'),	//  $callback
	        self::$post_type,						// $screen
	        'normal',								// $context
	        'high',									// $priority
	        array()									// $callback_args
			);
	}
    
    // Call back to render the meta box
	function fn_render_meta_box($post){
		
		// trace
		dbg_trace();
		
		// Get the id 
		$post_id = $post->ID;

    	// Nonce
		wp_nonce_field( 'ic_info_card_edit', 'ic_info_card_nonce' );

    	$email_value = get_post_meta( $post->ID, 'ic_contact_email', true );
    	$icon_value= get_post_meta( $post->ID, 'ic_icon', true );

		$this->hlp_render_icon_input_field($icon_value);
		$this->hlp_render_email_input_field($email_value);

	}
	
	// helper to render the email input field 
	function hlp_render_email_input_field($value, $quick_edit_mode = false){
					
		// Email field
		echo('
			<label ' . ( 	 $quick_edit_mode ? 'class="alignleft">' : '>') . '
			<span class="title">Email</span>
			<span class="input-text-wrap"><input id="quick_edit_ic_contact_email" type="text" name="email" value="' . $value .'" /></span>
			</label>'
			);
			
	}	

	// helper to render the icon input field 
	function hlp_render_icon_input_field($value, $quick_edit_mode = false){
	
	
	
		// Open  
		echo('
		
			<label ' . ( 	 $quick_edit_mode ? 'class="alignleft">' : '>') . '
			<span class="title">Icon</span>
			<span class="input-text-wrap">
			<select id="quick_edit_ic_icon" name="icon" value="' . $value . '">');
			
			
		// Get the list of font awesoem icons and build select input
		$icon_list = get_font_awesome_icon_list();
		foreach ($icon_list as $icon) {
			
			if( $icon === $value ) {
				echo '<option selected="true" value="' . $icon . '">' . $icon . '</option>';
			}
			else {
				echo '<option value="' . $icon . '">' . $icon . '</option>';
			}
		}
				
				
		// Close field select
		echo ('
			</select>
			</span>
			</label>'
			);
	}


	// Display quick edit custom fields 
	function fn_quick_edit_custom_box( $column_name,  $post_type,  $taxonomy ){

		// Check post type
		if( self::$post_type != $post_type) {
			return;
			}
	
		
		// Switch on ccolum 
		switch( $column_name ) :
			case 'ic_icon': {
	 
				// Nonce
				wp_nonce_field( 'ic_info_card_edit', 'ic_info_card_nonce' );
	 
				// Open fieldset and divs
				echo '	
					<fieldset class="inline-edit-col-right">
					<div class="inline-edit-col">
					<div class="inline-edit-group wp-clearfix">
					';
					
	 			// Icon field 
				$this->hlp_render_icon_input_field('');
			
	 
				break;
	 
			}
			case 'ic_contact_email': {
	 
				// Email field 
				$this->hlp_render_email_input_field('');
			
			
				// Close fieldset and divs
				echo ('</div></div></fieldset>');
	 
				break;
	 
			}
	 
		endswitch;
		
		// Done 
		return;
	}
	
	// Handle edit save (both quick edit and full edit)
	function fn_post_edit_save( $post_id ){
	 
		// check user capabilities
		if ( !current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
	 
		// check nonce - bail of not set or not valid
		if ( ( ! isset( $_POST['ic_info_card_nonce'] ) ) or !wp_verify_nonce( $_POST['ic_info_card_nonce'], 'ic_info_card_edit' ) ) {
			return;
		}
	 
		// update the email
		if ( isset( $_POST['email'] ) ) {
	 		update_post_meta( $post_id, 'ic_contact_email', $_POST['email'] );
		}
	 
		// update icon
		if ( isset( $_POST['icon'] ) ) {
	 		update_post_meta( $post_id, 'ic_icon', $_POST['icon'] );
		}
	 
	 
	}
	

	function fn_columns($columns) {
		unset($columns['date']);
		unset($columns['taxonomy-ic_info_card_attribute']);
		unset($columns['comments']);
		unset($columns['author']);
		return array_merge(
			
				
				// Title first
				$columns,
				
				// Excerpt and custom columns
				array(
				'ic_icon' => 'Icon',
				'excerpt' => 'Excerpt',
				'ic_contact_email' => 'Email',
			)
			);
	}

	function fn_column_data($column,$post_id) {
		switch($column) {
			case 'ic_contact_email' :
				echo get_post_meta($post_id,'ic_contact_email', true);
				break;
				
			case 'ic_icon' :
				$icon = get_post_meta($post_id,'ic_icon', true);
				echo "<span hidden='hidden'> " . $icon . "</span>";
				echo "<div style = 'font-size: xx-large;' class='ic_info_card_icon'>";
    			echo "<i class='fas " . $icon . "'></i>";
    			echo "</div>";   
				break;
				
			case 'excerpt':
				echo get_post($post_id)->post_excerpt;
				break;
		}
	}

	function join($wp_join) {
		global $wpdb;
		if(get_query_var('post_type') == self::$post_type ) {
			$wp_join .= " LEFT JOIN (
					SELECT post_id, meta_value AS contact_email
					FROM $wpdb->postmeta
					WHERE meta_key = 'contact_email' ) AS meta
					ON $wpdb->posts.ID = meta.post_id ";
		}
		return ($wp_join);
	}

	function set_default_sort($orderby,&$query) {
		global $wpdb;
		if(get_query_var('post_type') == self::$post_type ) {
			return "meta.contact_email DESC";
		}
	 	return $orderby;
	}
}



