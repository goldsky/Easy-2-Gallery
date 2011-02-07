<?php

$_a = isset($_a) ? $_a : (int) $_GET['a'];
$_i = isset($_i) ? $_i : (int) $_GET['id'];
$lng = !empty($lng) ? $lng : array();
$e2gPages = !empty($e2gPages) ? $e2gPages : array();

$e2gPages = array(
    'dashboard' => array(
        'e2gpg' => '1'
        , 'title' => 'dashboard'
        , 'link' => 'index.php?a=' . $_a . '&amp;id=' . $_i . '&amp;e2gpg=1'
        , 'lng' => $lng['dashboard']
        , 'file' => 'dashboard.inc.php'
        , 'access' => '100'
        , 'icon' => '<img src="' . MODX_MANAGER_URL . 'media/style/MODxCarbon/images/icons/home.gif" width="16" height="16" border="0" alt="" />'
    )
    , 'files' => array(
        'e2gpg' => '2'
        , 'title' => 'files'
        , 'link' => 'index.php?a=' . $_a . '&amp;id=' . $_i . '&amp;e2gpg=2'
        , 'lng' => $lng['files']
        , 'file' => 'file.inc.php'
        , 'access' => '200'
        , 'icon' => '<img src="' . MODX_MANAGER_URL . 'media/style/MODxCarbon/images/icons/page_white_copy.png" width="16" height="16" border="0" alt="" />'
    )
    , 'upload' => array(
        'e2gpg' => '3'
        , 'title' => 'upload'
        , 'link' => 'index.php?a=' . $_a . '&amp;id=' . $_i . '&amp;e2gpg=3'
        , 'lng' => $lng['upload']
        , 'file' => 'upload.inc.php'
        , 'access' => '300'
        , 'icon' => '<img src="' . MODX_MANAGER_URL . 'media/style/MODxCarbon/images/icons/add.png" width="16" height="16" border="0" alt="" />'
    )
    , 'comments' => array(
        'e2gpg' => '4'
        , 'title' => 'comments'
        , 'link' => 'index.php?a=' . $_a . '&amp;id=' . $_i . '&amp;e2gpg=4'
        , 'lng' => $lng['comments']
        , 'file' => 'comment.inc.php'
        , 'access' => '400'
        , 'icon' => '<img src="' . E2G_MODULE_URL . 'includes/tpl/icons/comments.png" width="16" height="16" border="0" alt="" />'
    )
    , 'viewer' => array(
        'e2gpg' => '5'
        , 'title' => 'viewer'
        , 'link' => 'index.php?a=' . $_a . '&amp;id=' . $_i . '&amp;e2gpg=5'
        , 'lng' => $lng['viewer']
        , 'file' => 'viewer.inc.php'
        , 'access' => '500'
        , 'icon' => '<img src="' . MODX_MANAGER_URL . 'media/style/MODxCarbon/images/icons/preview.png" width="16" height="16" border="0" alt="" />'
    )
    , 'slideshow' => array(
        'e2gpg' => '6'
        , 'title' => 'slideshow'
        , 'link' => 'index.php?a=' . $_a . '&amp;id=' . $_i . '&amp;e2gpg=6'
        , 'lng' => $lng['slideshows']
        , 'file' => 'slideshow.inc.php'
        , 'access' => '600'
        , 'icon' => '<img src="' . E2G_MODULE_URL . 'includes/tpl/icons/slides.png" width="16" height="16" border="0" alt="" />'
    )
    , 'plugin' => array(
        'e2gpg' => '7'
        , 'title' => 'plugin'
        , 'link' => 'index.php?a=' . $_a . '&amp;id=' . $_i . '&amp;e2gpg=7'
        , 'lng' => $lng['plugins']
        , 'file' => 'plugin.inc.php'
        , 'access' => '700'
        , 'icon' => '<img src="' . E2G_MODULE_URL . 'includes/tpl/icons/plugin.png" width="16" height="16" border="0" alt="" />'
    )
    , 'user' => array(
        'e2gpg' => '8'
        , 'title' => 'user'
        , 'link' => 'index.php?a=' . $_a . '&amp;id=' . $_i . '&amp;e2gpg=8'
        , 'lng' => $lng['users']
        , 'file' => 'user.inc.php'
        , 'access' => '800'
        , 'icon' => '<img src="' . E2G_MODULE_URL . 'includes/tpl/icons/group.png" width="16" height="16" border="0" alt="" />'
    )
    , 'setting' => array(
        'e2gpg' => '9'
        , 'title' => 'setting'
        , 'link' => 'index.php?a=' . $_a . '&amp;id=' . $_i . '&amp;e2gpg=9'
        , 'lng' => $lng['settings']
        , 'file' => 'setting.inc.php'
        , 'access' => '900'
        , 'icon' => '<img src="' . E2G_MODULE_URL . 'includes/tpl/icons/settings.png" width="16" height="16" border="0" alt="" />'
    )
//    , 'option' => array(
//        'e2gpg' => '10'
//        , 'title' => 'option'
//        , 'link' => 'index.php?a=' . $_a . '&amp;id=' . $_i . '&amp;e2gpg=10'
//        , 'lng' => $lng['options']
//        , 'file' => 'option.inc.php'
//        , 'access' => '1000'
//    )
    , 'help' => array(
        'e2gpg' => '11'
        , 'title' => 'help'
        , 'link' => 'index.php?a=' . $_a . '&amp;id=' . $_i . '&amp;e2gpg=11'
        , 'lng' => $lng['help']
        , 'file' => 'help.inc.php'
        , 'access' => '1100'
        , 'icon' => '<img src="' . E2G_MODULE_URL . 'includes/tpl/icons/help.png" width="16" height="16" border="0" alt="" />'
    )
);

$e2gFilePageTpls = array(
    'file_default_table_tpl' => E2G_MODULE_PATH . 'includes/tpl/chunks/file_default_table.tpl'
    , 'file_default_table_row_dir_tpl' => E2G_MODULE_PATH . 'includes/tpl/chunks/file_default_table_row_dir.tpl'
    , 'file_default_table_row_file_tpl' => E2G_MODULE_PATH . 'includes/tpl/chunks/file_default_table_row_file.tpl'
    , 'file_thumb_gal_tpl' => E2G_MODULE_PATH . 'includes/tpl/chunks/file_thumb_gal.tpl'
    , 'file_thumb_dir_tpl' => E2G_MODULE_PATH . 'includes/tpl/chunks/file_thumb_dir.tpl'
    , 'file_thumb_file_tpl' => E2G_MODULE_PATH . 'includes/tpl/chunks/file_thumb_file.tpl'
    , 'file_tag_table_tpl' => E2G_MODULE_PATH . 'includes/tpl/chunks/file_tag_table.tpl'
    , 'file_tag_table_row_dir_tpl' => E2G_MODULE_PATH . 'includes/tpl/chunks/file_tag_table_row_dir.tpl'
    , 'file_tag_table_row_file_tpl' => E2G_MODULE_PATH . 'includes/tpl/chunks/file_tag_table_row_file.tpl'
);
