<?php
header('Content-Type: text/html; charset=UTF-8');
//set_ini('display_errors', '1');
/**
 * EASY 2 GALLERY
 * Gallery Snippet Class for Easy 2 Gallery Module for MODx Evolution
 * @author Cx2 <inteldesign@mail.ru>
 * @author Temus
 * @author goldsky <goldsky@modx-id.com>
 * @version 1.4.0
 */
if (defined(E2G_SNIPPET_PATH)) require_once E2G_SNIPPET_PATH . 'includes/utf8/utf8.php';
if (defined(E2G_MODULE_PATH)) require_once E2G_MODULE_PATH . 'includes/utf8/utf8.php';
class e2g_pub {
    public $e2gpub_cfg = array();
    private $_e2g = array();

    public function  __construct($e2gpub_cfg) {
        $this->e2gpub_cfg = $e2gpub_cfg;
        $this->_e2g = $_e2g;
    }
}