<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author Joomunited
 * @version 1.0
 */

// No direct access.
defined( 'ABSPATH' ) || die();
?>
<style>
    ul.tagit{background: none;background-color:#f5f5f5;}
    .wpfdparams ul.tagit {margin: 0 !important;}
    .tagit-hidden-field{display:none;}
    ul.tagit input[type="text"]{background-color: #f5f5f5;}
    ul.tagit li {
        font-weight: normal !important;
        color: #6B6B6B !important;
        font-size: 14px;
    }
</style>
<?php echo $this->form; ?>
<script type="text/javascript">
    jQuery(document).ready(function(){
        jQuery("#file_tags").tagit({
            availableTags: <?php echo $this->allTagsFiles; ?>,
            afterTagAdded:function(e){e.preventDefault();},
            allowSpaces :true
        });
    });
    <?php echo wpfd_calendartranslation();?>
</script>
