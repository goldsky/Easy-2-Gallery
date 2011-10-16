<?php
$ssParams = $this->_getSlideShowParams();
$ssFiles = $this->_getSlideShowFiles();

//echo __LINE__ . ' : $slideshow = ' . $slideshow . '<br />';
//echo __LINE__ . ' : $ssParams = ' . $ssParams . '<br />';
//echo '<pre>';
//print_r($ssParams);
//echo '</pre>';
//echo __LINE__ . ' : $ssFiles = ' . $ssFiles . '<br />';
//echo '<pre>';
//print_r($ssFiles);
//echo '</pre>';
//die();

/**
 * @link http://slidesjs.com/
 */
// just to avoid direct call to this file. it's recommended to always use this.
if (!defined('E2G_SNIPPET_URL') && $slideshow != 'slidejs') {
    return;
}

// just making a default selection
$ssParams['ss_config'] = 'product';

$modx->regClientCSS(MODX_BASE_URL . 'assets/libs/slideshows/slidejs/slidejs.css', 'screen');
$modx->regClientStartupScript('https://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js');
$modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/slidejs/source/slides.min.jquery.js');

$modx->regClientStartupHTMLBlock('
        <script type="text/javascript">
		$(function(){
			$(\'#products\').slides({
				preload: true,
				preloadImage: \'assets/libs/slideshows/slidejs/examples/Product/img/loading.gif\',
				effect: \'slide, fade\',
				crossfade: true,
				slideSpeed: 350,
				fadeSpeed: 500,
				generateNextPrev: true,
				generatePagination: false
			});
		});   
        </script> 
');

$output = '
<div id="container">
    <div id="products_example">
        <div id="products">
            <div class="slides_container">
';
for ($i = 0; $i < $ssFiles['count']; $i++) {
    $link = !empty($ssFiles['redirect_link'][$i]) ? $ssFiles['redirect_link'][$i] : 
                // making flexible FURL or not
                $modx->makeUrl(
                        $modx->documentIdentifier,
                        $modx->aliases,
                        'sid=' . $ssParams['sid']
                        . '&fid=' . $ssFiles['id'][$i]
                )
                . '#' . $ssParams['sid'] . '_' . $ssFiles['id'][$i] . '"';
    $output .= '
                <a href="' . $link . '" target="_blank">
                    <img src="' . $ssFiles['resizedimg'][$i] . '" width="' . $ssParams['ss_w'] . '" alt="' . $ssFiles['title'][$i] . '" />
                </a>
';
}
$output .= '
            </div>
            <ul class="pagination">
';

for ($i = 0; $i < $ssFiles['count']; $i++) {
    $link = !empty($ssFiles['redirect_link'][$i]) ? $ssFiles['redirect_link'][$i] : '#';
    $output .= '
                <li><a href="#"><img src="' . $ssFiles['thumbsrc'][$i] . '" width="' . $ssParams['w'] . '" alt="' . $ssFiles['title'][$i] . '" /></a></li>
';
}
$output .= '
            </ul>
        </div>
    </div>
</div>
';

return $output;