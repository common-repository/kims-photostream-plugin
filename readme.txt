=== Kim's Photostream ===
Contributors: bmnave
Tags: photostream
Requires at least: 3.0
Tested up to: 3.2.1
Stable tag: 1.2
License: GPLv2

Searches recent posts and creates a photo stream of images.

== Description ==

Kim's Photostream Plugin is a WordPress Plugin that I wrote for my wife Kim. She wanted to have a flickr style photostream on our blog with all the images from recent posts.  I found several plugins that would show the featured images from recent posts, but usually in a slideshow format.

== Installation ==

1. Upload Kim's Photostream to your blog.  The easiest way to do this is through the `Plugins` menu in WordPress.  You can either choose `Add New` on the `Plugins` menu and upload the ZIP file, or you can manually copy the ZIP file to the `/wp-content/plugins/` directory and unzip it.
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure the plugin using the `Kim's Photostream Settings` menu under the `Plugins` menu in WordPress.
4. Place `<?php kims_photostream(); ?>` in your templates where you want to display the Photostream.

== Changelog ==

= 1.2 = 
* Fixed a bug where images with an underscore in the filename were not found in posts.

= 1.1 =
* Fixed a bug where images which do not need to be resized show up twice in the photostream.

= 1.0 =
* Initial Release! Woo hoo!
* KNOWN ISSUE: This script can consume all the memory availible to PHP.  Google for "increase memory limit php.ini"
* KNOWN ISSUE: If you change the size of your thumbnails, you will have to reubild your thumbs.  See the settings page.
* KNOWN ISSUE: If you delete an image from a post after it's been published, you may have to rebuild your thumbs.  See the settings page.

== Upgrade Notice ==

= 1,2 =
Version 1.1 fixes a bug where images with an underscore in the filename were not found in posts.

= 1.1 =
Version 1.1 fixes a bug where images which do not need to be resized show up in the photostream twice.

= 1.0 =
INITIAL RELEASE! Plugin is NOW FUNCTIONAL!

== Frequently Asked Questions ==

= Where Can I Learn More About Your Plugin? =
Visit http://www.breathegrowclimb.com/bens-projects/kims-photostream-plugin/

