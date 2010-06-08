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
//require_once MODX_BASE_PATH . 'assets/modules/easy2/includes/utf8/utf8.php';

class e2g_pub { // public/protected class
    public $e2gpub_cfg;
    public $e2gpub_e2g;
    public $e2gpub_lng;
    private $_e2g = array();

    public function  __construct($e2gpub_cfg, $e2g, $lng) {
        set_time_limit(0);
        $this->e2gpub_cfg = $e2gpub_cfg;
        $this->e2gpub_e2g = $e2g;
        $this->e2gpub_lng = $lng;
        $this->_e2g = $_e2g;
    }


    /**
     * Unicode character encoding work around.<br />
     * For human reading.<br />
     * The value is set from the module's config page.
     *
     * @link http://a4esl.org/c/charset.html
     * @param string $text the string to be encoded
     * @return string returns the encoding
     */
    protected function e2g_encode($text) {
        $e2g_encode = $this->e2gpub_cfg['e2g_encode'];

        if ($e2g_encode == 'none') {
            return $text;
        }
        if ($e2g_encode == 'UTF-8') {
            return utf8_encode($text);
        }
        if ($e2g_encode == 'UTF-8 (Rin)') {
            require_once E2G_MODULE_PATH.'includes/UTF8-2.1.0/UTF8.php';
            require_once E2G_MODULE_PATH.'includes/UTF8-2.1.0/ReflectionTypehint.php';
            /*
             * http://forum.dklab.ru/viewtopic.php?p=91015#91015
             */
            return UTF8::convert_from($text,mb_detect_encoding($text));
        }
    }

    /**
     * Unicode character decoding work around.<br />
     * For file system reading.<br />
     * The value is set from the module's config page.
     *
     * @link http://a4esl.org/c/charset.html
     * @param string $text the string to be decoded
     * @return string returns the decoding
     */
    protected function e2g_decode($text) {
        $e2g_encode = $this->e2gpub_cfg['e2g_encode'];

        if ($e2g_encode == 'none') {
            return $text;
        }
        if ($e2g_encode == 'UTF-8') {
            return utf8_decode($text);
        }
        if ($e2g_encode == 'UTF-8 (Rin)') {
            require_once E2G_MODULE_PATH.'includes/UTF8-2.1.0/UTF8.php';
            require_once E2G_MODULE_PATH.'includes/UTF8-2.1.0/ReflectionTypehint.php';
            /*
             * http://forum.dklab.ru/viewtopic.php?p=91015#91015
             */
            // fixedmachine -- http://modxcms.com/forums/index.php/topic,49266.msg292206.html#msg292206
            if($mb_detect_encoding != 'ASCII' || $mb_detect_encoding != 'UTF-8')
                return UTF8::convert_from( $text, "ASCII" );
            else
                return UTF8::convert_from( $text, mb_detect_encoding($text) );
        }
    }

    /**
     * function get_dir_info
     * function to get directory's information
     * @param int $dirid = gallery's ID
     */
    protected function get_dir_info($dirid,$field) {
        global $modx;

        $dirinfo = array();

        $q = 'SELECT '.$field.' FROM '.$modx->db->config['table_prefix'].'easy2_dirs '
                . 'WHERE cat_id='.$dirid.' '
        ;

        if (!($res = mysql_query($q))) return ('Wrong field.');
        while ($l = mysql_fetch_array($res)) {
            $dirinfo[$field] = $l[$field];
        }
        mysql_free_result($res);
        if (empty($dirinfo[$field])) return null;
        return $dirinfo[$field];
    }
}