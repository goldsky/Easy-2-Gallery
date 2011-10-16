<?php

/**
 * Distributed slideshows for Easy 2 Gallery
 * @package Easy 2 Gallery
 * @subpackage install
 */
if (IN_MANAGER_MODE != "true")
    die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

$updateSlideShows = array(
	"simple" => array(
		"description" => "A Simple jQuery slideshow by &lt;a href=&quot;http://jonraasch.com/blog/a-simple-jquery-slideshow&quot; target=&quot;_blank&quot;&gt;Jon Raasch&lt;/a&gt;",
		"indexfile" => "assets/libs/slideshows/simplejquery/simple.php",
	),
	"galleryview" => array(
		"description" => "&lt;a href=&quot;http://spaceforaname.com/galleryview&quot; target=&quot;_blank&quot;&gt;http://spaceforaname.com/galleryview&lt;/a&gt;",
		"indexfile" => "assets/libs/slideshows/galleryview/galleryview.php",
	),
	"galleriffic" => array(
		"description" => "&lt;a href=&quot;http://www.twospy.com/galleriffic/&quot; target=&quot;_blank&quot;&gt;http://www.twospy.com/galleriffic/&lt;/a&gt;",
		"indexfile" => "assets/libs/slideshows/galleriffic/galleriffic.php",
	),
	"smoothgallery" => array(
		"description" => "&lt;a href=&quot;http://smoothgallery.jondesign.net/&quot; target=&quot;_blank&quot;&gt;http://smoothgallery.jondesign.net/&lt;/a&gt;",
		"indexfile" => "assets/libs/slideshows/smoothgallery/smoothgallery.php",
	),
	"contentflow" => array(
		"description" => "&lt;a href=&quot;http://www.jacksasylum.eu/ContentFlow/index.php&quot; target=&quot;_blank&quot;&gt;http://www.jacksasylum.eu/ContentFlow/index.php&lt;/a&gt;",
		"indexfile" => "assets/libs/slideshows/contentflow/contentflow.php",
	),
	"slidejs" => array(
		"description" => "&lt;a href=&quot;http://slidesjs.com/&quot; title=&quot;slidejs&quot; target=&quot;_blank&quot;&gt;http://slidesjs.com/&lt;/a&gt; Slides, A Slideshow Plugin for jQuery",
		"indexfile" => "assets/libs/slideshows/slidejs/slidejs.php",
	),
);

return $updateSlideShows;