<?php
function bpfr_whats_new_tiny_editor() {
    // building the what's new textarea
    $content= ( isset( $_GET['r'] ) ) ? esc_textarea( $_GET['r'] ) : ""; 


    // adding tinymce tools
    $editor_id = 'whats-new';
    $settings = array(
        'textarea_name' => 'whats-new',
        'quicktags' => false,
        'media_buttons' => false,
        'teeny' => false,               //must be false for @mentions to work
        'textarea_rows' => 10,
        'tinymce'=> get_tiny_toolbars() //found in functions.php
    );  
    
    // get the editor
    add_filter( 'tiny_mce_before_init', 'bpfr_remove_menu_tiny_editor', 11 ); //this function is found in myFOSSIL2016 functions.php and removes the menu bar
    wp_editor( $content, $editor_id, $settings );
}
add_action( 'whats_new_textarea', 'bpfr_whats_new_tiny_editor' );

function bpfr_messaging_tiny_editor() {
    // building the what's new textarea
    $content = esc_textarea( bp_get_messages_content_value()); 


    // adding tinymce tools
    $editor_id = 'message_content';
    $settings = array(
        'textarea_name' => 'content',
        'quicktags' => false,
        'media_buttons' => false,
        'teeny' => false,               //must be false for @mentions to work
        'textarea_rows' => 10,
        'tinymce'=> get_tiny_toolbars() //found in functions.php
    );  
    
    // get the editor
    add_filter( 'tiny_mce_before_init', 'bpfr_remove_menu_tiny_editor', 11 ); //this function is found in myFOSSIL2016 functions.php and removes the menu bar
    wp_editor( $content, $editor_id, $settings );
}
add_action( 'bp_messaging_textarea', 'bpfr_messaging_tiny_editor' );