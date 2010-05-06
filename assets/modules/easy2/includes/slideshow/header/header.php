<?php
// just to avoid direct call to this file. it's recommended to always use this.
if ( !defined(E2G_SNIPPET_URL) && $slideshow!='header') {
    return;
}
// result with no images
elseif ($count == 0) {
    $ss_display = 'No image inside the gallery id '.$gid;
    // this slideshow heavily dependent on any image existence.
    return;
}
else {
    // start create the slideshow box
	if (!defined('PHP_THUMB_PATH')) define('PHP_THUMB_PATH',MODX_BASE_PATH.'assets/libs/phpthumb/');
	if (!defined('IMG_CACHE_PATH')) define('IMG_CACHE_PATH',MODX_BASE_PATH.'assets/images/cache/');
	if (!defined('IMG_CACHE_URL')) define('IMG_CACHE_URL',MODX_BASE_URL.'assets/images/cache/');
    require_once PHP_THUMB_PATH.'ThumbLib.inc.php';
    $ss_display = '
            <div id="gallery" class="content">
                <div class="slideshow-container">
                    <div id="loading" class="loader"></div>
                    <div id="slideshow" class="slideshow"></div>
                    <div id="caption" class="caption-container"></div>
                </div>
            </div>
            <div id="thumbs" class="navigation" style="width:0px;height:0px;">
            <ul class="thumbs noscript">'
    ;
    $j=0;
	$crop=1;
    for ($i=0;$i<$count;$i++) {
		$source_file = MODX_BASE_PATH.$_ssfile['src'][$i];
		$image_file = IMG_CACHE_PATH.$ss_h.'_'.$ss_w.'_'.$crop.'_'.$_ssfile['filename'][$i];
		$image_url =  IMG_CACHE_URL.$ss_h.'_'.$ss_w.'_'.$crop.'_'.$_ssfile['filename'][$i];
    if (!file_exists($image_file)) {
        $options = array('resizeUp' => true, 'jpegQuality' => 80);
        try
        {
             $thumb = PhpThumbFactory::create($source_file,$options);
        }
        catch (Exception $e)
        {
             echo 'Processing Error: '.$source_file;
        }    
        $thumb->adaptiveResize($ss_w, $ss_h);
        $thumb->save($image_file);
    }
        $ss_display .= '
                <li><a class="thumb" href="'.$image_url.'" title="'.$_ssfile['title'][$i].'">'.$_ssfile['title'][$i].'</a>';
        if ($_ssfile['title'][$i]!=''){$ss_display .= '
                    <div class="caption">'.$_ssfile['title'][$i].'</div>';}
        $ss_display .= '
                </li>' ;
        // if there is a image number limitation
        $j++;
        if ($j==$ss_limit) break;
    }
    // end the slideshow box
    // slideshow always returns as $ss_display !
    $ss_display .= '
            </ul></div>';
}

