<?php

$e2gPages = array(
    'dashboard' => array(
        'e2gpg' => '1'
        , 'title' => 'dashboard'
        , 'link' => 'index.php?a=' . $_a . '&amp;id=' . $_i . '&amp;e2gpg=1'
        , 'lng' => $lng['dashboard']
        , 'file' => 'pane.dashboard.inc.php'
        , 'access' => '100'
    )
    , 'files' => array(
        'e2gpg' => '2'
        , 'title' => 'files'
        , 'link' => 'index.php?a=' . $_a . '&amp;id=' . $_i . '&amp;e2gpg=2'
        , 'lng' => $lng['files']
        , 'file' => 'pane.files.inc.php'
        , 'access' => '200'
    )
    , 'upload' => array(
        'e2gpg' => '3'
        , 'title' => 'upload'
        , 'link' => 'index.php?a=' . $_a . '&amp;id=' . $_i . '&amp;e2gpg=3'
        , 'lng' => $lng['upload']
        , 'file' => 'pane.upload.inc.php'
        , 'access' => '300'
    )
    , 'comments' => array(
        'e2gpg' => '4'
        , 'title' => 'comments'
        , 'link' => 'index.php?a=' . $_a . '&amp;id=' . $_i . '&amp;e2gpg=4'
        , 'lng' => $lng['comments']
        , 'file' => 'pane.comments.inc.php'
        , 'access' => '400'
    )
    , 'viewer' => array(
        'e2gpg' => '5'
        , 'title' => 'viewer'
        , 'link' => 'index.php?a=' . $_a . '&amp;id=' . $_i . '&amp;e2gpg=5'
        , 'lng' => $lng['viewer']
        , 'file' => 'pane.viewer.inc.php'
        , 'access' => '500'
    )
    , 'slideshow' => array(
        'e2gpg' => '6'
        , 'title' => 'slideshow'
        , 'link' => 'index.php?a=' . $_a . '&amp;id=' . $_i . '&amp;e2gpg=6'
        , 'lng' => $lng['slideshows']
        , 'file' => 'pane.slideshow.inc.php'
        , 'access' => '600'
    )
    , 'plugin' => array(
        'e2gpg' => '7'
        , 'title' => 'plugin'
        , 'link' => 'index.php?a=' . $_a . '&amp;id=' . $_i . '&amp;e2gpg=7'
        , 'lng' => $lng['plugins']
        , 'file' => 'pane.plugin.inc.php'
        , 'access' => '700'
    )
    , 'user' => array(
        'e2gpg' => '8'
        , 'title' => 'user'
        , 'link' => 'index.php?a=' . $_a . '&amp;id=' . $_i . '&amp;e2gpg=8'
        , 'lng' => $lng['users']
        , 'file' => 'pane.user.inc.php'
        , 'access' => '800'
    )
    , 'config' => array(
        'e2gpg' => '9'
        , 'title' => 'config'
        , 'link' => 'index.php?a=' . $_a . '&amp;id=' . $_i . '&amp;e2gpg=9'
        , 'lng' => $lng['config']
        , 'file' => 'pane.config.inc.php'
        , 'access' => '900'
    )
//    , 'option' => array(
//        'e2gpg' => '10'
//        , 'title' => 'option'
//        , 'link' => 'index.php?a=' . $_a . '&amp;id=' . $_i . '&amp;e2gpg=10'
//        , 'lng' => $lng['options']
//        , 'file' => 'pane.option.inc.php'
//        , 'access' => '1000'
//    )
    , 'help' => array(
        'e2gpg' => '11'
        , 'title' => 'help'
        , 'link' => 'index.php?a=' . $_a . '&amp;id=' . $_i . '&amp;e2gpg=11'
        , 'lng' => $lng['help']
        , 'file' => 'pane.help.inc.php'
        , 'access' => '1100'
    )
);