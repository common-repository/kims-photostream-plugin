<?php

/*
 * Utility Logger Function
 *
 * Logs a message to the plugin's logfile if the loglevel parameter is <= LOG_LEVEL
 *  LOG_LEVEL	Meaning
 *  ---------   -------
 *      5       Trace
 *      4		Debug
 *      3		Info
 *      2		Warn
 *      1		Error
 *      0       No Logging
 */
function logger($msg, $loglevel = 5)
{
	global $LOG_LEVEL, $PHOTOSTREAM_THUMB_DIR;

	if($loglevel <= $LOG_LEVEL)
	{
		$debugArray = debug_backtrace(false);

		$timeStamp = date("M j H:i:s");
		$fileName = basename($debugArray[0]["file"]);
		$lineNum = $debugArray[0]["line"];


		$logLine = $timeStamp." ".$fileName.":".$lineNum." [".$loglevel."] ".$msg;

		$logFileName = WP_CONTENT_DIR.'/'.$PHOTOSTREAM_THUMB_DIR.'/'.'kims-photostream.log';

		$fh = fopen($logFileName, "a");
		fwrite($fh, $logLine . "\n");
		fclose($fh);
	}
}

/*
 * Activation Hook Function
 */
function kims_photostream_activate()
{
	global $PHOTOSTREAM_THUMB_DIR;

	// Check to see if the PHOTOSTREAM_DATA dir exists yet
	if( !is_dir(WP_CONTENT_DIR.'/'.$PHOTOSTREAM_THUMB_DIR) )
	{
		if(mkdir(WP_CONTENT_DIR.'/'.$PHOTOSTREAM_THUMB_DIR, 0755))
		{
			logger('Sucessfully created PHOTOSTREAM_DATA Directory: '.WP_CONTENT_DIR.'/'.$PHOTOSTREAM_THUMB_DIR, 3);
		}
		else
		{
			echo 'FAILED To Create PHOTOSTREAM_DATA Directory: '.WP_CONTENT_DIR.'/'.$PHOTOSTREAM_THUMB_DIR;
		}
	}
}

/*
 * Builds the Photostream Data Structure
 */
function kims_photostream_build_photostream_data()
{
	global $PHOTOSTREAM_THUMB_DIR, $PHOTOSTREAM_OPTION_DEFAULTS, $photostreamData, $kps_init_complete, $wpdb;

	if($kps_init_complete)
	{
		// photostreamData has already been populated;
		return;
	}

	$numImages = get_option('photostream-num-images', $PHOTOSTREAM_OPTION_DEFAULTS['photostream-num-images']);
	$searchDepth = get_option('photostream-search-depth', $PHOTOSTREAM_OPTION_DEFAULTS['photostream-search-depth']);

	$numImagesFound = 0;

	// Build query string
	$query = 'SELECT ID,post_content FROM '.$wpdb->prefix.'posts WHERE post_parent = 0 AND post_status = "publish" AND post_type = "post" ORDER BY post_date DESC LIMIT '.$searchDepth;
	logger("query=".$query);

	// Run the query
	$posts = $wpdb->get_results($query);

	foreach($posts as $post)
	{
		// Find the pictures in the post
		preg_match_all('!http://[a-z0-9_\-\.\/]+\.(?:jpe?g|png|gif)!Ui',$post->post_content,$pregResults);

		// preg_match_all stores the the actual matches in $pregResults[0]
		$matches = $pregResults[0];

		foreach($matches as $key=>$photoUrl)
		{
			logger("key=".$key." photoUrl=".$photoUrl);

			// Ignore the resized version of the image (ie name-768x1024.jpg)
			if(preg_match('/[0-9]+x[0-9]+/',$photoUrl))
			{
				logger("Ignoring ".$photoUrl);
				continue;
			}
			
			// Check to see if we've already loaded this image
			if(kims_photostream_already_loaded($photostreamData, $photoUrl))
			{
				logger("Ignoring ".$photoUrl. " as it is already loaded.");
				continue;
			}

			// Figure out what the thumb is going to be called
			$thumbFileName = basename($photoUrl);
			$thumbFileParts = explode('.',$thumbFileName);

			if(count($thumbFileParts) != 2)
			{
				logger("WARNING! File name has more than one period: ".$photoUrl, 2);
				continue;
			}

			$thumbFileName = $thumbFileParts[0].'-thumb.'.$thumbFileParts[1];

			// Build the data structure
			$aPhoto = array(
				"postUrl"   => get_permalink($post->ID),
			    "photoUrl"  => $photoUrl,
				"thumbUrl"  => WP_CONTENT_URL.'/'.$PHOTOSTREAM_THUMB_DIR.'/'.$thumbFileName,
				"thumbFile" => WP_CONTENT_DIR.'/'.$PHOTOSTREAM_THUMB_DIR.'/'.$thumbFileName
			);

			array_push($photostreamData, $aPhoto);
			$numImagesFound++;

			if($numImagesFound >= $numImages)
			{
				// Found enough photos for the stream
				$kps_init_complete = true;
				return;
			}
		}
	}

}

/*
 * Checks to see if the photoUrl is already in the photostreamData
 */
function kims_photostream_already_loaded($photostreamData, $photoUrl)
{
	foreach($photostreamData as $photo)
	{
		if($photo['photoUrl'] == $photoUrl)
		{
			return true;
		}
	}
}

/*
 * Checks to see if the first image has a thumb built
 */
function kims_photostream_quick_thumb_check()
{
	global $photostreamData;

	if($photostreamData == null)
	{
		logger("ERROR! $photostreamData is NULL", 1);
	}
	else
	{
		if(!file_exists($photostreamData[0]['thumbFile']))
		{
			logger("Thumb File ".$photostreamData[0]['thumbFile']." not found. Building thumbs.");
			kims_photostream_build_thumbs();
		}
	}
}

/*
 * Builds the thumbnails for the Photostream
 */
function kims_photostream_build_thumbs()
{
	global $PHOTOSTREAM_OPTION_DEFAULTS, $photostreamData;

	$height = get_option('photostream-image-height',$PHOTOSTREAM_OPTION_DEFAULTS['photostream-image-height']);
	$width = get_option('photostream-image-width',$PHOTOSTREAM_OPTION_DEFAULTS['photostream-image-width']);

	foreach($photostreamData as $photo)
	{
		logger("photo=".print_r($photo,true));

		if(!file_exists($photo['thumbFile']))
		{
			logger("Creating thumb for ".$photo['photoUrl']);

			$imageInfo = @getimagesize($photo['photoUrl']);

			if($imageInfo != false)
			{
				$imageType = $imageInfo[2];
					
				$imageContents = file_get_contents($photo['photoUrl']);

				if($imageContents != null)
				{
					$image = @imagecreatefromstring($imageContents);

					$thumb = @imagecreatetruecolor($width, $height);
					@imagecopyresampled($thumb, $image, 0, 0, 0, 0, $width, $height, $imageInfo[0], $imageInfo[1]);

					if( $imageType == IMAGETYPE_JPEG )
					{
						imagejpeg($thumb,$photo['thumbFile'],75);
					}
					elseif( $image_type == IMAGETYPE_GIF )
					{
						imagegif($thumb,$photo['thumbFile']);
					}
					elseif( $image_type == IMAGETYPE_PNG )
					{
						imagepng($thumb,$photo['thumbFile']);
					}
					else
					{
						logger("WARNING! Unhandled image type for ".$photo['photoUrl'],1);
					}
				}
				else
				{
					logger("WARNING! Unable to get image contents from ".$photo['photoUrl'],2);
				}
			}
			else
			{
				logger("WARNING! Unable to get image info from ".$photo['photoUrl'],2);
			}
		}
	}
	
	// Clean up any old thumbnails
	kims_photostream_cleanup_thumbs();
}

/*
 * Removes any thumb that isn't in the photostreamData
 */
function kims_photostream_cleanup_thumbs()
{
	global $PHOTOSTREAM_THUMB_DIR, $photostreamData;
	
	$thumbDirContents = scandir ( WP_CONTENT_DIR.'/'.$PHOTOSTREAM_THUMB_DIR );
	
	if($thumbDirContents != null)
	{
		foreach($thumbDirContents as $aFile)
		{
			logger("Found thumb ".$aFile);
			
			// First make sure that it's a file
			if(is_file(WP_CONTENT_DIR.'/'.$PHOTOSTREAM_THUMB_DIR.'/'.$aFile))
			{
				// If the file is the logfile ingore it
				if($aFile == 'kims-photostream.log')
				{
					logger("Ignoring ".$aFile);
				}
				else
				{
					$deleteIt = true;
					
					// Check to see if aFile is in the photostreamData
					foreach($photostreamData as $photo)
					{
						if(WP_CONTENT_DIR.'/'.$PHOTOSTREAM_THUMB_DIR.'/'.$aFile == $photo['thumbFile'])
						{
							$deleteIt = false;
							break; 
						}
					}
					
					if($deleteIt)
					{
						logger("Deleting old thumb file ".WP_CONTENT_DIR.'/'.$PHOTOSTREAM_THUMB_DIR.'/'.$aFile);
						unlink(WP_CONTENT_DIR.'/'.$PHOTOSTREAM_THUMB_DIR.'/'.$aFile);
					}
				}
			}
			else
			{
				logger(WP_CONTENT_DIR.'/'.$PHOTOSTREAM_THUMB_DIR.'/'.$aFile." is not a file.");
			}
		}
	}
	else
	{
		logger("Thumb Dir ".$PHOTOSTREAM_THUMB_DIR." was empty. No cleanup needed");
	}
}

// Delete all the thumbs and recreate them
function kims_photostream_rebuild_thumbs()
{
global $PHOTOSTREAM_THUMB_DIR;
	
	$thumbDirContents = scandir ( WP_CONTENT_DIR.'/'.$PHOTOSTREAM_THUMB_DIR );
	
	if($thumbDirContents != null)
	{
		foreach($thumbDirContents as $aFile)
		{
			logger("Found thumb ".$aFile);
			
			// First make sure that it's a file
			if(is_file(WP_CONTENT_DIR.'/'.$PHOTOSTREAM_THUMB_DIR.'/'.$aFile))
			{
				// If the file is the logfile ingore it
				if($aFile == 'kims-photostream.log')
				{
					logger("Ignoring ".$aFile);
				}
				else
				{
					logger("Deleting old thumb file ".WP_CONTENT_DIR.'/'.$PHOTOSTREAM_THUMB_DIR.'/'.$aFile);
					unlink(WP_CONTENT_DIR.'/'.$PHOTOSTREAM_THUMB_DIR.'/'.$aFile);
				}
			}
			else
			{
				logger(WP_CONTENT_DIR.'/'.$PHOTOSTREAM_THUMB_DIR.'/'.$aFile." is not a file.");
			}
		}
	}
	else
	{
		logger("Thumb Dir ".$PHOTOSTREAM_THUMB_DIR." was empty. No cleanup needed");
	}
	
	kims_photostream_build_thumbs();
}

/*
 * Init the photostreamData structure
 */
function kims_photostream_init()
{
	global $photostreamData;

	// Build the photostreamData structure
	kims_photostream_build_photostream_data();

	logger("photostreamData=".print_r($photostreamData, true));

	// Checks to see if the first image's thumb exists
	kims_photostream_quick_thumb_check();
}

/*
 * Displays the photostream
 */
function kims_photostream()
{
	global $PHOTOSTREAM_OPTION_DEFAULTS, $photostreamData;
	
	$height = get_option('photostream-image-height',$PHOTOSTREAM_OPTION_DEFAULTS['photostream-image-height']);
	$width = get_option('photostream-image-width',$PHOTOSTREAM_OPTION_DEFAULTS['photostream-image-width']);
	$spacing = get_option('photostream-image-spacing',$PHOTOSTREAM_OPTION_DEFAULTS['photostream-image-spacing']);
	
	// I know it's lame to use a table, but DIVs will come later
	echo "<table border=0 cellspacing=\"".$spacing."\" style=\"border: 0px solid #424242; border-collapse: separate; border-spacing: ".$spacing."px\">\n";
	echo "<tr>\n";

	foreach($photostreamData as $photo)
	{
		echo "<td><a href='".$photo['postUrl']."'><img style=\"border: 1px solid #424242;\" src='".$photo['thumbUrl']."' height='".$height."' width='".$width."'/></a></td>\n";
	}
	
	echo "</tr>\n";
	echo "</table>\n";
}
?>