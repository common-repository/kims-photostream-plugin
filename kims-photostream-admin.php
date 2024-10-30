<?php
/*
 * Admin Options for Kim's Photostream Plugin
 */

// Add an admin_menu action to add the options page
add_action('admin_menu','kims_photostream_admin');

function kims_photostream_admin() {
	//create new plugins submenu
	add_submenu_page('plugins.php', 					// parent slug
					 'Kim\'s Photostream Settings', 	// page title
					 'Kim\'s Photostream Settings', 	// menu title
					 'manage_options', 					// capability
					 'kims-photostream-settings', 		// menu slug
					 'kims_photostream_settings_page');	// function

	//call register settings function
	add_action( 'admin_init', 'register_kims_photostream_settings' );
}


function register_kims_photostream_settings() {
	//register our settings
	register_setting( 'kims-photostream-settings-group', 'photostream-num-images' );
	register_setting( 'kims-photostream-settings-group', 'photostream-search-depth');
	register_setting( 'kims-photostream-settings-group', 'photostream-image-height' );
	register_setting( 'kims-photostream-settings-group', 'photostream-image-width' );
	register_setting( 'kims-photostream-settings-group', 'photostream-image-spacing');
	register_setting( 'kims-photostream-settings-group', 'photostream-rebuild-thumbs');
	
	global $PHOTOSTREAM_OPTION_DEFAULTS;
	$PHOTOSTREAM_OPTION_DEFAULTS = array ( 	'photostream-num-images'     => 9,
											'photostream-search-depth'   => 15,
											'photostream-image-height'   => 68,
											'photostream-image-width'    => 68,
											'photostream-image-spacing'  => 3,
											'photostream-rebuild-thumbs' => false
										  );
}

function kims_photostream_settings_page() {
	global $PHOTOSTREAM_OPTION_DEFAULTS;
	
	if(get_option('photostream-rebuild-thumbs',$PHOTOSTREAM_OPTION_DEFAULTS['photostream-rebuild-thumbs']) == true)
	{
		// Thumb rebuild requested by user.
		kims_photostream_rebuild_thumbs();
		update_option('photostream-rebuild-thumbs', false);
	}
?>
<div class="wrap">
<h2>Kim's Photostream Plugin</h2>

<form method="post" action="options.php">
    <?php settings_fields( 'kims-photostream-settings-group' ); ?>
    <table class="form-table">
        <tr valign="top">
        <th scope="row">Number of Images to Display in the Photostream</th>
        <td><input type="text" name="photostream-num-images" value="<?php echo get_option('photostream-num-images', 9); ?>" /></td>
        </tr>
        
        <tr valign="top">
        <th scope="row">How Many Recent Posts Should Be Searched for Images</th>
        <td><input type="text" name="photostream-search-depth" value="<?php echo get_option('photostream-search-depth', 15); ?>" /></td>
        </tr>
         
        <tr valign="top">
        <th scope="row">Photostream Image Height</th>
        <td><input type="text" name="photostream-image-height" value="<?php echo get_option('photostream-image-height', 68); ?>" /></td>
        </tr>

        <tr valign="top">
        <th scope="row">Photostream Image Width</th>
        <td><input type="text" name="photostream-image-width" value="<?php echo get_option('photostream-image-width'); ?>" /></td>
        </tr>
        
        <tr valign="top">
        <th scope="row">Photostream Image Spacing (between images)</th>
        <td><input type="text" name="photostream-image-spacing" value="<?php echo get_option('photostream-image-spacing'); ?>" /></td>
        </tr>
                
        <tr valign="top">
        <th scope="row">Rebuild Thumbs (If checked, all thumbnails will be deleted and rebuilt on save.  When the settings page reloads, the checkbox will be cleared.)</th>
        <?php if(get_option('photostream-rebuild-thumbs', $PHOTOSTREAM_OPTION_DEFAULTS['photostream-rebuild-thumbs']) == true) 
        { ?>
        <td><input type="checkbox" name="photostream-rebuild-thumbs" value="true" checked/></td>
        <?php } else {?>
        <td><input type="checkbox" name="photostream-rebuild-thumbs" value="true"/></td>
        <?php }?>
        </tr>
    </table>
    
    <p class="submit">
    <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
    </p>

</form>
</div>
<?php
}
?>