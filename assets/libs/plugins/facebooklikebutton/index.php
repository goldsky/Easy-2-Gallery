<?php

if (!isset($e2gEvtName))
    return;

//echo __LINE__ . ' : $e2gEvtParams = ' . $e2gEvtParams . '<br />';
//echo '<pre>';
//var_dump($e2gEvtParams);
//echo '</pre>';
//die();

if (!function_exists('facebookLikeButton')) {

    function facebookLikeButton($link) {
        if (empty($link))
            return;

        $facebookLike['src'] = rawurlencode($link);
        $tplContent = file_get_contents(realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'e2g.facebooklikebutton.tpl'));

        $coreClassFile = realpath('assets/modules/easy2/includes/models/e2g.public.class.php');
        if (!file_exists($coreClassFile)) {
            return;
        }
        if (!class_exists('E2gPub')) {
            include $coreClassFile;
        }
        $e2g = new E2gPub($modx, $e2gPubCfg);
        return $e2g->filler($tplContent, $facebookLike);
    }

}

$modx->regClientStartupScript('assets/libs/plugins/facebooklikebutton/e2g.facebooklikebutton.css');

switch ($e2gEvtName) {
    case 'OnE2GWebThumbRender':
        echo facebookLikeButton(MODX_SITE_URL . $e2gEvtParams['link']);
        break;

    default:
        break;
}
