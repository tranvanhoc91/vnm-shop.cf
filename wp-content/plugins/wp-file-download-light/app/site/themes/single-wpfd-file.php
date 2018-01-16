<?php
/**
 * The template for displaying the header
 *
 * Displays all of the head element and everything up until the "site-content" div.
 *
 * @package WordPress
 * @subpackage Twenty_Fifteen
 * @since Twenty Fifteen 1.0
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
<head>
	
	<?php wp_head(); ?>
</head>

<body class='wpfdviewer'>
<div id="primary" class="content-area">
<?php
wpfd_file_viewer();
?>
</div>
<?php wp_footer(); ?>

</body>
</html>
