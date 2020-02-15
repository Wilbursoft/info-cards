


// Color picker 
(function( $ ) {
 
    // Add Color Picker to all inputs that have 'tc-color-field' class
    $(function() {
        $('.tc-colour-field').wpColorPicker();
    });
     
})( jQuery );


// Suppress IDE lint warnings 
/* global jQuery */
/* global inlineEditPost */

// populate the custom quick edit fields
jQuery(function($){
 
  // Check inlineEditPost exists 
  if ( typeof( inlineEditPost ) == 'object'){
    
  	// hook the edit method 
  	var original_inline_edit_function = inlineEditPost.edit;
   
  	// Define new function to hook it 
  	inlineEditPost.edit = function( post_id ) {
  	  
      // Call the original 
      original_inline_edit_function.apply( this, arguments );
      
      // get the post ID from the argument
      var id = 0;
      if ( typeof( post_id ) == 'object' ) { 
        // if it is object, get the ID number
      	id = parseInt( this.getId( post_id ), 10);
      }
      
      // Get the source table row for that post - this is where the values are 
      var row = document.getElementById("post-" + id);
      
      // helper function to copy the values over
      function hlp_copy_row_to_quick_edit(field_classname, field_quick_edit_id){
        
        // Get the elements with the specific class name, there is only one but concatenate.
        var fields=row.getElementsByClassName(field_classname);
        var curValue="";
        for (var i = 0; i < fields.length; i++) {
          curValue += fields[i].textContent.trim();
        }
        
        // Get the <form> field element an set its value
        var formInput=document.getElementById(field_quick_edit_id);
        formInput.value=curValue.trim();
          
      }
      
      // Function to remove unwanted default fields from the post edit. 
      function hlp_hide_unwanted_fields() {
      
           // Look for the date control
            $('.inline-edit-date').each(function (i) {
                
                // Remove the slug input above
                $(this).prev().remove();
                
                // The BR below
                $(this).next().next().remove();
                
                // The password below that
                $(this).next().remove();
                
                // Remove date control 
                $(this).remove();
            });
        }
  
      // Copy over the values 
      hlp_copy_row_to_quick_edit("ic_contact_email", "quick_edit_ic_contact_email" );
      hlp_copy_row_to_quick_edit("ic_icon", "quick_edit_ic_icon" );
  
      // Hide the unwanted fields
      hlp_hide_unwanted_fields();
  
  	}
  
  }
	
});