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
        /**
         * Using the class from <br />
         * http://forum.dklab.ru/viewtopic.php?p=91015#91015
         */
        if ($e2g_encode == 'UTF-8 (Rin)') {
            /**
             * using Unicode conversion class.
             * @todo Need more work work on i18n stuff
             */
            include_once MODX_BASE_PATH.'assets/modules/easy2/includes/UTF8-2.1.0/UTF8.php';
            include_once MODX_BASE_PATH.'assets/modules/easy2/includes/UTF8-2.1.0/ReflectionTypehint.php';
            // fixedmachine -- http://modxcms.com/forums/index.php/topic,49266.msg292206.html#msg292206
            $converted_text = UTF8::convert_to($text,mb_detect_encoding($text));
            if ($converted_text!=false) $text=$converted_text;
            return $text;
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
        /**
         * Using the class from <br />
         * http://forum.dklab.ru/viewtopic.php?p=91015#91015
         */
        if ($e2g_encode == 'UTF-8 (Rin)') {
            /**
             * using Unicode conversion class.
             * @todo Need more work work on i18n stuff
             */
            include_once MODX_BASE_PATH.'assets/modules/easy2/includes/UTF8-2.1.0/UTF8.php';
            include_once MODX_BASE_PATH.'assets/modules/easy2/includes/UTF8-2.1.0/ReflectionTypehint.php';
            $mb_detect_encoding = mb_detect_encoding($text);
            // fixedmachine -- http://modxcms.com/forums/index.php/topic,49266.msg292206.html#msg292206
            if($mb_detect_encoding != 'ASCII' || $mb_detect_encoding != 'UTF-8'){
                if(!$mb_detect_encoding){
                    $converted_text = UTF8::convert_from( $text, "ASCII" );
                    if ($converted_text!=false) $text=$converted_text;
                    return $text;
                }
                else {
                    $converted_text = UTF8::convert_from( $text, $mb_detect_encoding );
                    if ($converted_text!=false) $text=$converted_text;
                    return $text;
                }
            }
            else return $text;
        } // if ($e2g_encode == 'UTF-8 (Rin)')
    } // protected function e2g_decode($text)

    /**
     * function get_dir_info
     * function to get directory's information
     * @param int    $dirid  gallery's ID
     * @param string $field  database field
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

    /**
     * To check the specified resource has a valid file extenstion.
     * @author goldsky <goldsky@modx-id.com>
     * @todo need a rework to make it more extendable
     */
    protected function is_validext($filename) {
        $ext = strtolower(end(@explode('.', $filename)));
        $allowedext = array(
                'jpg' => TRUE,
                'jpeg' => TRUE,
                'gif' => TRUE,
                'png' => TRUE
        );
        return $allowedext[$ext];
    }

    /**
     * To check the specified resource is a valid file.<br />
     * It will be checked against the folder validation first.
     * @author goldsky <goldsky@modx-id.com>
     */
    protected function is_validfile ($filename) {
        $e2g_debug = $this->e2gpub_cfg['e2g_debug'];
        $f = $this->basename_safe($filename);
        $f = $this->e2g_encode($f);
        if ($this->is_validfolder($filename)) {
            if ($e2g_debug==1) {
                $_SESSION['easy2err'][] = __LINE__.' : <b style="color:red;">'.$filename.'</b> is not a file, it\'s a valid folder.';
            }
            return FALSE;
        }
        elseif ( $f != '' && !$this->is_validfolder($filename) ) {
            if (file_exists($filename)) {
                $size = getimagesize($filename);
                $fp = fopen($filename, "rb");
                $allowedext = array(
                        'image/jpeg' => TRUE,
                        'image/gif' => TRUE,
                        'image/png' => TRUE
                );
                if ( $allowedext[$size["mime"]] && $fp ) {
                    if ($e2g_debug==1) {
                        $fileinfo = 'Filename <b style="color:red;">'.$f.'</b> is a valid image file: '.$size["mime"].' - '.$size[3];
                    }
                    else return TRUE;
                } else {
                    if ($e2g_debug==1) $fileinfo = 'Filename <b style="color:red;">'.$f.'</b> is an invalid image file: '.$size[2].' - '.$size[3];
                    else {
//                        $_SESSION['easy2err'][] = __LINE__.' : '.$filename;
                        return FALSE;
                    }
                }
            }
            else {
                if ($e2g_debug==1) $fileinfo .= 'Filename <b style="color:red;">'.$f.'</b> is NOT exists.<br />';
                else {
                    $_SESSION['easy2err'][] = __LINE__.' : '.$filename .' does not exist.';
                    return FALSE;
                }
            }
            if ($e2g_debug==1) return $fileinfo;
            else return TRUE;
        }
        else continue;
    }

    /**
     * To check the specified resource is a valid folder, although it has a DOT in it.
     * @author goldsky <goldsky@modx-id.com>
     */
    protected function is_validfolder($foldername) {
        $e2g_debug = $this->e2gpub_cfg['e2g_debug'];
        $openfolder = @opendir($foldername);
        if (!$openfolder) {
            if ($e2g_debug==1) {
                $_SESSION['easy2err'][] = __LINE__.' : <b style="color:red;">'.$foldername.'</b> is NOT a valid folder, probably a file.';
            }
            return FALSE;
        } else {
            if ($e2g_debug==1) {
                echo '<h2>' . $foldername . '</h2>';
                echo '<ul>';
                $file = array();
                while ( ( FALSE !== ( $file = readdir ( $openfolder ) ) ) ) {
                    if ( $file != "." && $file != ".." ) {
                        if (filetype($file)=='dir') {
                            echo '<li>dir: <b style="color:green;">'.$file.'</b></li>';
                        }
                        else echo "<li> $file </li>";
                        clearstatcache();
                    }
                }
                echo '</ul>';
            }
            closedir ( $openfolder );
        }
        if ($e2g_debug==1) return '<br /><b style="color:red;">'.$foldername.'</b> is a valid folder.';
        else return TRUE;
    }

    /**
     * Replace the basename function with this to grab non-unicode character.
     * @link http://drupal.org/node/278425#comment-2571500
     */
    protected function basename_safe($path) {
        $path = rtrim($path,'/');
        $path = explode('/',$path);

        // encoding
        $endpath = end($path);
//        $encodinghtml= htmlspecialchars($this->e2g_encode($endpath), ENT_QUOTES);
        $encodinghtml= htmlspecialchars($endpath, ENT_QUOTES);
        return $encodinghtml;
    }
}