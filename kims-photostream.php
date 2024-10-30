<?php
/*
Plugin Name: Kim's Photostream
Plugin URI: http://www.breathegrowclimb.com/bens-projects/kims-photostream-plugin/
Description: This plugin creates a Photostream of recent images from your posts.
Version: 1.2
Author: Benjamin M. Nave
Author URI: http://www.breathegrowclimb.com
License: GPL2
*/

/*  Copyright 2011  Benjamin Nave  (email : email AT breathegrowclimb.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/*
 * INLINE CONFIGURATION OPTIONS
 *  
 *  The options below are mainly for development purposes.
 *  You probably won't need to adjust them.
 */

// LOG_LEVEL Controls Logging Information Level
global $LOG_LEVEL;
$LOG_LEVEL = 1;

// PHOTOSTREAM_THUMB_DIR controls where image thumbnails are stored (under WP_CONTENT_DIR)
global $PHOTOSTREAM_THUMB_DIR;
$PHOTOSTREAM_THUMB_DIR = "uploads/kims_photostream_thumbs";

/*
 * END INLINE CONFIG
 */

// Data Array used for Photostream
global $photostreamData;
$photostreamData= array();

// Wordpress seems to execute the plugins_loaded action twice. 
//  kps_init_complete is true when photostreamData has been built
global $kps_init_complete;
if($kps_init_complete == null)
{
	$kps_init_complete = false;
}

require_once("kims-photostream-functions.php");
require_once("kims-photostream-admin.php");

// Register an activation hook to create the PHOTOSTREAM_DATA Directory
register_activation_hook( __FILE__, 'kims_photostream_activate');

// Initialize the photostreamData structure
add_action('plugins_loaded', 'kims_photostream_init');

?>