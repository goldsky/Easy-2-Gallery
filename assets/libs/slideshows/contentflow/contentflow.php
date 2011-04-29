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
 * @http://www.jacksasylum.eu/ContentFlow/index.php
 */
// just to avoid direct call to this file. it's recommended to always use this.
if (!defined('E2G_SNIPPET_URL') && $slideshow != 'contentflow') {
    return;
}

// this slideshow heavily dependent on any image existence, returns with no images
if ($ssFiles['count'] == 0) {
    return 'No image inside the specified id(s),'
    . (!empty($ssParams['gid']) ? ' gid:' . $ssParams['gid'] : '')
    . (!empty($ssParams['fid']) ? ' fid:' . $ssParams['fid'] : '');
}

// just making a default selection
if (!isset($ssParams['ss_config']))
    $ssParams['ss_config'] = 'default';

//**************************************************/
//*            PREPARE THE HTML HEADERS            */
//**************************************************/

if ($ssParams['ss_config'] == 'default') {
    if (!empty($ssParams['ss_css'])) {
        $modx->regClientCSS($ssParams['ss_css'], 'screen');
    } else {
        $modx->regClientCSS(MODX_BASE_URL . 'assets/libs/slideshows/contentflow/contentflow.css', 'screen');
    }
}
// Javascript
if ($ssParams['ss_config'] == 'default') {
    if (!empty($ssParams['ss_js'])) {
        $modx->regClientStartupScript($ssParams['ss_js']);
    } else {
        $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/contentflow/contentflow.js');
    }
}

//**************************************************/
//**************************************************/
//***                                            ***/
//***           THE SLIDESHOW DISPLAY            ***/
//***                                            ***/
//**************************************************/
//**************************************************/
//**************************************************/
//*               THE default CONFIG               */
//**************************************************/
if ($ssParams['ss_config'] == 'default') {

    // ------------- open slideshow wrapper ------------- //
    $output = '
        <div class="ContentFlow">
            <div class="loadIndicator"><div class="indicator"></div></div>
            <div class="flow">';

    // ------------ start the images looping ------------ //
    for ($i = 0; $i < $ssFiles['count']; $i++) {
        $output .= '
                <div class="item" href="'
                // making flexible FURL or not
                . $modx->makeUrl(
                        $modx->documentIdentifier
                        , $modx->aliases
                        , 'fid=' . $ssFiles['id'][$i])
                . '">
                    <img class="content" src="' . $ssFiles['src'][$i] . '" title="' . $ssFiles['title'][$i] . '" alt="" />
                </div>
                ';
    }
    // ------------- end the images looping ------------- //
    // ------------- close slideshow wrapper ------------- //
    $output .= '
            </div>
            <div class="globalCaption"></div>
            <div class="scrollbar"><div class="slider"><div class="position"></div></div></div>
        </div>
';
}

return $output;