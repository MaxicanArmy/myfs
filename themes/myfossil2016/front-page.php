<?php 
use myFOSSIL\Plugin\Specimen\Fossil;
get_header();
?>

<?php 
if (is_user_logged_in()) :
    do_action('bp_before_directory_activity');
    include get_template_directory()."/homepage-logged-in.php";

else :
    include get_template_directory()."/homepage-logged-out.php";
    
endif; ?>
<?php get_footer(); ?>
