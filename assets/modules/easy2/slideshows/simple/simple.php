<?php
// just to avoid direct call to this file. it's recommended to always use this.
if ( !defined(E2G_SNIPPET_URL) && $slideshow!='simple') {
    return;
}
// result with no images
elseif ($count == 0) {
    $ss_display = 'No image inside the gallery id '.$gid;
    // this slideshow heavily dependent on any image existence.
    return;
}
// http://jonraasch.com/blog/a-simple-jquery-slideshow
else {
    if (!empty($ss_css)) {
        $modx->regClientCSS($ss_css,'screen');
    } else {
        $modx->regClientCSS(E2G_SNIPPET_URL.'slideshows/simple/simple.css','screen');
        // amend dimension variables into CSS
        $modx->regClientStartupHTMLBlock('
        <style type="text/css" media="screen">
        #slideshow {
            width: '.$ss_w.'px;
            height: '.$ss_h.'px;
            background-color: '.( $ss_bg=='rgb' ? 'rgb('.$thbg_red.','.$thbg_green.','.$thbg_blue.')' : $ss_bg ).';
         }
        </style>
            ');
    }

    $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/jquery/jquery-1.4.2.min.js');
    
    if (!empty($ss_js)) {
        $modx->regClientStartupScript($ss_js);
    } else {
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'slideshows/simple/simple.js');
    }

    // start create the slideshow box
    $ss_display = '<div id="slideshow"><div>';
    $j=0;
    for ($i=0;$i<$count;$i++) {
        $dim = getimagesize($_ssfile['src'][$i]);
        $width[$i] = $dim[0];
        $height[$i] = $dim[1];
        $image_ratio[$i] = $width[$i]/$height[$i];

        if ($ss_allowedratio != 'none') {
            // skipping ratio exclusion
            if ( $ss_minratio > $image_ratio[$i] || $ss_maxratio < $image_ratio[$i] ) continue;
        }
//        echo $ss_w/$ss_h .'=>'. $image_ratio[$i].'<br />';
        $ss_display .= '
                <img src="'.utf8_encode($_ssfile['src'][$i]).'" alt="" title="'.$_ssfile['title'][$i].'" '
                . ( $i == 0 ? 'class="active" ' : '' )
                . ( ( ($ss_w/$ss_h) < $image_ratio[$i] ) ?
                'height="'.$ss_h.'px" style="left:'.(($ss_w - ($width[$i]*$ss_h/$height[$i]))/2).'px;" ' :
                'width="'.$ss_w.'px" style="top:'.(($ss_h - ($height[$i]*$ss_w/$width[$i]))/2).'px;" ' )
                . '/>';

        // if there is a image number limitation
        $j++;
        if ($j==$ss_limit) break;
    }
    // end the slideshow box
    // slideshow always returns as $ss_display !
    $ss_display .= '</div></div>';
}