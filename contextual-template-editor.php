<?php
/*
Plugin Name: Contextual Page Template Editor
Plugin URI: http://www.noextratime.com/
Description: Edit page templates from within the page they are being used for
Version: 1.0
Author: Josh Ronk      
Author URI: http://www.noextratime.com/
License: GPL
*/

	function add_edit_box() {
	    add_meta_box('thecontextualeditor','Page Template Editor','add_the_editor','page','normal','high');
	}

function add_the_editor() {
global $post;
	if($post->page_template =='default' || $post->page_template==''){
	echo 'You are not using any page template.';
	return;
	}
if($post->page_template || $post->page_template !='default' || $post->page_template!=''){

$themes = get_themes();

if (empty($theme)) {
	$theme = get_current_theme();
} else {
	$theme = stripslashes($theme);
}
$file = $themes[$theme]['Template Dir'].'/'.$post->page_template;

if ( ! isset($themes[$theme]) )
	wp_die(__('The requested theme does not exist.'));

$allowed_files = array_merge($themes[$theme]['Stylesheet Files'], $themes[$theme]['Template Files']);
echo '<pre>';
echo '</pre>';
if (empty($file)) {
	$file = $allowed_files[0];
} else {
	$file = stripslashes($file);
	if ( 'theme' == $dir ) {
		$file = dirname(dirname($themes[$theme]['Template Dir'])) . $file ;
	} else if ( 'style' == $dir) {
		$file = dirname(dirname($themes[$theme]['Stylesheet Dir'])) . $file ;
	}
}
validate_file_to_edit($file, $allowed_files);
$scrollto = isset($_REQUEST['scrollto']) ? (int) $_REQUEST['scrollto'] : 0;
$file_show = basename( $file );

	update_recently_edited($file);

	if ( !is_file($file) )
		$error = 1;

	if ( !$error && filesize($file) > 0 ) {
		$f = fopen($file, 'r');
		$content = fread($f, filesize($file));

		if ( '.php' == substr( $file, strrpos( $file, '.' ) ) ) {
			$functions = wp_doc_link_parse( $content );

			$docs_select = '<select name="docs-list" id="docs-list">';
			$docs_select .= '<option value="">' . esc_attr__( 'Function Name...' ) . '</option>';
			foreach ( $functions as $function ) {
				$docs_select .= '<option value="' . esc_attr( urlencode( $function ) ) . '">' . htmlspecialchars( $function ) . '()</option>';
			}
			$docs_select .= '</select>';
		}

		$content = htmlspecialchars( $content );
	}

global $form_action;
 if (!$error) { 
 echo '<h2 style="margin:0;">File: '.$post->page_template.'</h2>';
 ?>

	<form name="template" id="template" action="post.php" method="post" <?php do_action('post_edit_form_tag'); ?>>

		 <div><textarea style="width:100%;clear:both;" rows="25" name="newcontent" id="newcontent" tabindex="1"><?php echo $content ?></textarea>
		
		 <?php  global $nonce_action; wp_nonce_field($nonce_action); ?>
		<input type="hidden" id="user-id" name="user_ID" value="<?php global $user_ID; echo (int) $user_ID ?>" />
		<input type="hidden" id="hiddenaction" name="action" value="<?php echo esc_attr($form_action) ?>" />
		<input type="hidden" id="originalaction" name="originalaction" value="<?php echo esc_attr($form_action) ?>" />
		<input type="hidden" id="post_author" name="post_author" value="<?php echo esc_attr( $post->post_author ); ?>" />
		<input type="hidden" id="post_type" name="post_type" value="<?php global $post_type; echo esc_attr($post_type) ?>" />
		<input type="hidden" id="original_post_status" name="original_post_status" value="<?php echo esc_attr($post->post_status) ?>" />
		<input type="hidden" id="referredby" name="referredby" value="<?php echo esc_url(stripslashes(wp_get_referer())); ?>" />

		 <input type="hidden" name="tempaction" value="update" />
		 <input type="hidden" name="file" value="<?php echo esc_attr($file) ?>" />
		 <input type="hidden" name="theme" value="<?php echo esc_attr($theme) ?>" />
		 <input type="hidden" name="scrollto" id="scrollto" value="<?php echo $scrollto; ?>" />
		 </div>
	<?php if ( isset($functions ) && count($functions) ) { ?>
		<div id="documentation" class="hide-if-no-js">
		<label for="docs-list"><?php _e('Documentation:') ?></label>
		<?php echo $docs_select; ?>
		<input type="button" class="button" value=" <?php esc_attr_e( 'Lookup' ); ?> " onclick="if ( '' != jQuery('#docs-list').val() ) { window.open( 'http://api.wordpress.org/core/handbook/1.0/?function=' + escape( jQuery( '#docs-list' ).val() ) + '&amp;locale=<?php echo urlencode( get_locale() ) ?>&amp;version=<?php echo urlencode( $wp_version ) ?>&amp;redirect=true'); }" />
		</div>
	<?php } ?>

		<div>
<?php if ( is_writeable($file) ) : ?>
			<p class="submit">
<?php
	echo "<input type='submit' name='submit' class='button-primary' value='" . esc_attr__('Update File') . "' tabindex='2' />";
?>
</p>
<?php else : ?>
<p><em><?php _e('You need to make this file writable before you can save your changes. See <a href="http://codex.wordpress.org/Changing_File_Permissions">the Codex</a> for more information.'); ?></em></p>
<?php endif; ?>
		</div>
	</form>
<?php
	} else {
		echo '<div class="error"><p>' . __('Oops, no such file exists! Double check the name and try again, merci.') . '</p></div>';
	}
?>
<br class="clear" />

<?php

} else {
echo 'You are not using any page template.';
}

}//end function


function edit_save_postdata ( $post_id ) {
if (isset($_POST['newcontent'])){
	$newcontent = stripslashes($_POST['newcontent']);
	$theme = urlencode($_POST['theme']);
	$file = $_POST['file'];
	if (is_writeable($file)) {
		//is_writable() not always reliable, check return value. see comments @ http://uk.php.net/is_writable
		$f = fopen($file, 'w+');
		if ($f !== FALSE) {
			fwrite($f, $newcontent);
			fclose($f);
			}
	}
}
}


add_action('admin_menu', 'add_edit_box');
add_action('save_post', 'edit_save_postdata');
?>