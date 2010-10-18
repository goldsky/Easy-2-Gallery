<?php

/**
 * @http://www.jacksasylum.eu/ContentFlow/index.php
 */
// just to avoid direct call to this file. it's recommended to always use this.
if (!defined(E2G_SNIPPET_URL) && $slideshow != 'contentflow') {
    return;
}
// result with no images
elseif ($countSlideshowFiles == 0) {
    $ssDisplay = 'No image inside the gallery id ' . $gid;
    // this slideshow heavily dependent on any image existence.
    return;
} else {
    // just making a default selection
    if (!isset($ssConfig))
        $ssConfig = 'default';

    //**************************************************/
    //*            PREPARE THE HTML HEADERS            */
    //**************************************************/

    if ($ssConfig == 'default') {
        if (!empty($ssCss)) {
            $modx->regClientCSS($ssCss, 'screen');
        } else {
            $modx->regClientCSS(MODX_BASE_URL . 'assets/libs/slideshows/contentflow/contentflow.css', 'screen');
        }
    }
    // Javascript
    if ($ssConfig == 'default') {
        $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/contentflow/contentflow.js');
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
    if ($ssConfig == 'default') {

        // ------------- open slideshow wrapper ------------- //
        $ssDisplay = '
        <div class="ContentFlow">
            <div class="loadIndicator"><div class="indicator"></div></div>
            <div class="flow">';

        // ------------ start the images looping ------------ //
        $j = 0;
        for ($i = 0; $i < $countSlideshowFiles; $i++) {
            $ssDisplay .= '
                <div class="item" href="'
                    // making flexible FURL or not
                    . $modx->makeUrl(
                            $modx->documentIdentifier
                            , $modx->aliases
                            , 'fid=' . $_ssFile['id'][$i])
                    . '">
                    <img class="content" src="' . $_ssFile['src'][$i] . '" title="' . $_ssFile['title'][$i] . '" alt="" />
                </div>
                ';
            $j++;
            if ($j == $ssLimit)
                break;
        }
        // ------------- end the images looping ------------- //
        // ------------- close slideshow wrapper ------------- //
        $ssDisplay .= '
            </div>
            <div class="globalCaption"></div>
            <div class="scrollbar"><div class="slider"><div class="position"></div></div></div>
        </div>
';
    }

    echo $ssDisplay;
}