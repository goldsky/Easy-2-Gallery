<?php
// just to avoid direct call to this file. it's recommended to always use this.
if ( !defined(E2G_SNIPPET_URL) && $slideshow!='contentflow') {
    return;
}
// result with no images
elseif ($count == 0) {
    $ss_display = 'No image inside the gallery id '.$gid;
    // this slideshow heavily dependent on any image existence.
    return;
}
// http://www.jacksasylum.eu/ContentFlow/index.php
else {

    /**************************************************/
    /*            PREPARE THE HTML HEADERS            */
    /**************************************************/

    if ($ss_config=='default') {
        if (!empty($ss_css)) {
            $modx->regClientCSS($ss_css,'screen');
        } else {
            $modx->regClientCSS(E2G_SNIPPET_URL.'includes/slideshow/contentflow/contentflow.css','screen');
        }
    }
    // Javascript
    if ($ss_config=='default') {
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'includes/slideshow/contentflow/contentflow.js');
    }

    /**************************************************/
    /**************************************************/
    /***                                            ***/
    /***           THE SLIDESHOW DISPLAY            ***/
    /***                                            ***/
    /**************************************************/
    /**************************************************/

    /**************************************************/
    /*               THE default CONFIG               */
    /**************************************************/
    if ($ss_config=='default') {

        // ------------- open slideshow wrapper ------------- //
        $ss_display = '
        <div class="ContentFlow">
            <div class="loadIndicator"><div class="indicator"></div></div>
            <div class="flow">';

        // ------------ start the images looping ------------ //
        $j=0;
        for ($i=0;$i<$count;$i++) {
            $ss_display .= '
                <img class="item" src="'.$_ssfile['src'][$i].'" title="'.$_ssfile['title'][$i].'"/>';
            $j++;
            if ($j==$ss_limit) break;
        }
        // ------------- end the images looping ------------- //

        // ------------- close slideshow wrapper ------------- //
        $ss_display .= '
            </div>
            <div class="globalCaption"></div>
            <div class="scrollbar"><div class="slider"><div class="position"></div></div></div>
        </div>
';
    }
}