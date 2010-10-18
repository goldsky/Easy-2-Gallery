<?php

/**
 * EASY 2 GALLERY
 * Gallery Snippet Class for Easy 2 Gallery Module for MODx Evolution
 * @author Cx2 <inteldesign@mail.ru>
 * @author Temus <temus3@gmail.com>
 * @author goldsky <goldsky@modx-id.com>
 * @version 1.4.0
 */
class e2g_pub { // public/protected class
    /**
     * Inherit MODx functions
     * @var mixed modx's API
     */

    public $modx;
    /**
     * Parameter configuration from the snippet or module
     * @var mixed parameters' configurations
     */
    public $e2gpub_cfg;
    /**
     * The default configuration from the config fils
     * @var mixed default configuration
     */
    public $e2gpub_e2g;
    /**
     * The translation variables based on the manager's language setting
     * @var string language translation
     */
    public $e2gpub_lng;
    /**
     * The internal variables of this class
     * @var mixed all the processing variables
     */
    private $_e2g = array();

    public function __construct($modx, $e2gpub_cfg, $e2g, $lng) {
        set_time_limit(0);
        $this->modx = & $modx;
        $this->e2gpub_cfg = $e2gpub_cfg;
        $this->e2gpub_e2g = $e2g;
        $this->e2gpub_lng = $lng;
    }

    /**
     * Unicode character encoding work around.<br />
     * For human reading.<br />
     * The value is set from the module's config page.
     *
     * @link http://a4esl.org/c/charset.html
     * @param  string $text the string to be encoded
     * @return string returns the encoding
     */
    protected function e2gEncode($text, $callback=false) {
        $e2gEncode = $this->e2gpub_cfg['e2g_encode'];

        if ($e2gEncode == 'none') {
            if ($callback == false) {
                $convertedText = $text;
            }
            if ($callback == 'ucfirst') {
                $convertedText = ucfirst($text);
            }

            // if no matching criteria, just display plain text
            if ($convertedText == false)
                $convertedText = $text;

            return $convertedText;
        }

        if ($e2gEncode == 'UTF-8') {
            if ($callback == false) {
                $convertedText = utf8_encode($text);
            }
            // http://bytes.com/topic/php/answers/444382-ucfirst-utf-8-setlocale#post1693669
            if ($callback == 'ucfirst') {
                $fc = mb_strtoupper(mb_substr($text, 0, 1, 'UTF-8'), 'UTF-8');
                $convertedText = $fc . mb_substr($text, 1, mb_strlen($text, 'UTF-8'), 'UTF-8');
            }

            // if no matching criteria, just display plain text
            if ($convertedText == false)
                $convertedText = $text;

            return $convertedText;
        }

        /**
         * Using the class from <br />
         * http://forum.dklab.ru/viewtopic.php?p=91015#91015
         */
        if ($e2gEncode == 'UTF-8 (Rin)') {
            /**
             * using Unicode conversion class.
             * @todo Need more work work on i18n stuff
             */
            include_once MODX_BASE_PATH . 'assets/modules/easy2/includes/UTF8-2.1.0/UTF8.php';
            include_once MODX_BASE_PATH . 'assets/modules/easy2/includes/UTF8-2.1.0/ReflectionTypehint.php';

            if ($callback == false) {
                // fixedmachine -- http://modxcms.com/forums/index.php/topic,49266.msg292206.html#msg292206
                $convertedText = UTF8::convert_to($text, mb_detect_encoding($text));
            }
            if ($callback == 'ucfirst') {
                $convertedText = UTF8::ucfirst($text);
            }

            // if no matching criteria, just display plain text
            if ($convertedText == false)
                $convertedText = $text;

            return $convertedText;
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
    protected function e2gDecode($text, $callback=false) {
        $e2gEncode = $this->e2gpub_cfg['e2g_encode'];

        if ($e2gEncode == 'none') {
            return $text;
        }
        if ($e2gEncode == 'UTF-8') {
            return utf8_decode($text);
        }
        /**
         * Using the class from <br />
         * http://forum.dklab.ru/viewtopic.php?p=91015#91015
         */
        if ($e2gEncode == 'UTF-8 (Rin)') {
            /**
             * using Unicode conversion class.
             * @todo Need more work work on i18n stuff
             */
            include_once MODX_BASE_PATH . 'assets/modules/easy2/includes/UTF8-2.1.0/UTF8.php';
            include_once MODX_BASE_PATH . 'assets/modules/easy2/includes/UTF8-2.1.0/ReflectionTypehint.php';
            $mbDetectEncoding = mb_detect_encoding($text);
            // fixedmachine -- http://modxcms.com/forums/index.php/topic,49266.msg292206.html#msg292206
            if ($mbDetectEncoding != 'ASCII' || $mbDetectEncoding != 'UTF-8') {
                if (!$mbDetectEncoding) {
                    $convertedText = UTF8::convert_from($text, "ASCII");
                    if ($convertedText != false)
                        $text = $convertedText;
                    return $text;
                }
                else {
                    $convertedText = UTF8::convert_from($text, $mbDetectEncoding);
                    if ($convertedText != false)
                        $text = $convertedText;
                    return $text;
                }
            }
            else
                return $text;
        } // if ($e2gEncode == 'UTF-8 (Rin)')
    }

    /**
     * To get directory's information
     * @param  int    $dirId  gallery's ID
     * @param  string $field  database field
     * @return mixed  the directory's data in an array
     */
    protected function getDirInfo($dirId, $field) {
        $modx = $this->modx;

        $dirInfo = array();

        $q = 'SELECT ' . $field . ' FROM ' . $modx->db->config['table_prefix'] . 'easy2_dirs '
                . 'WHERE cat_id=' . $dirId . ' '
        ;

        if (!($res = mysql_query($q)))
            return ('Wrong field.');
        while ($l = mysql_fetch_array($res)) {
            $dirInfo[$field] = $l[$field];
        }
        mysql_free_result($res);
        if (empty($dirInfo[$field]))
            return null;
        return htmlspecialchars_decode($dirInfo[$field], ENT_QUOTES);
    }

    /**
     * To get file's information
     * @param  int    $fileId  file's ID
     * @param  string $field  database field
     * @return mixed  the file's data in an array
     */
    protected function getFileInfo($fileId, $field) {
        $modx = $this->modx;

        $fileInfo = array();

        $q = 'SELECT ' . $field . ' FROM ' . $modx->db->config['table_prefix'] . 'easy2_files '
                . 'WHERE id=' . $fileId . ' '
        ;

        if (!($res = mysql_query($q)))
            return ('Wrong field.');
        while ($l = mysql_fetch_array($res)) {
            $fileInfo[$field] = $l[$field];
        }
        mysql_free_result($res);
        if (empty($fileInfo[$field]))
            return null;
        return htmlspecialchars_decode($fileInfo[$field], ENT_QUOTES);
    }

    /**
     * To check the specified resource has a valid file extenstion.
     * @author goldsky <goldsky@modx-id.com>
     * @todo need a rework to make it more extendable
     * @param string $filename the filename
     */
    protected function validExt($filename) {
        $ext = strtolower(end(@explode('.', $filename)));
        $allowedExt = array(
            'jpg' => TRUE,
            'jpeg' => TRUE,
            'gif' => TRUE,
            'png' => TRUE
        );
        return $allowedExt[$ext];
    }

    /**
     * To check the specified resource is a valid file.<br />
     * It will be checked against the folder validation first.
     * @author goldsky <goldsky@modx-id.com>
     * @param string $filename the filename
     */
    protected function validFile($filename) {
        $e2gDebug = $this->e2gpub_cfg['e2g_debug'];

        $f = $this->basenameSafe($filename);
        $f = $this->e2gEncode($f);
        if ($this->validFolder($filename)) {
            if ($e2gDebug == 1) {
                $_SESSION['easy2err'][] = __LINE__ . ' : <b style="color:red;">' . $filename . '</b> is not a file, it\'s a valid folder.';
            }
            return FALSE;
        } elseif ($f != '' && !$this->validFolder($filename)) {
            if (file_exists($filename)) {
                $size = getimagesize($filename);
                $fp = fopen($filename, "rb");
                $allowedExt = array(
                    'image/jpeg' => TRUE,
                    'image/gif' => TRUE,
                    'image/png' => TRUE
                );
                if ($allowedExt[$size["mime"]] && $fp) {
                    if ($e2gDebug == 1) {
                        $fileInfo = 'Filename <b style="color:red;">' . $f . '</b> is a valid image file: ' . $size["mime"] . ' - ' . $size[3];
                    }
                    else
                        return TRUE;
                } else {
                    if ($e2gDebug == 1)
                        $fileInfo = 'Filename <b style="color:red;">' . $f . '</b> is an invalid image file: ' . $size[2] . ' - ' . $size[3];
                    else {
//                        $_SESSION['easy2err'][] = __LINE__.' : '.$filename;
                        return FALSE;
                    }
                }
            } else {
                if ($e2gDebug == 1)
                    $fileInfo .= 'Filename <b style="color:red;">' . $f . '</b> is NOT exists.<br />';
                else {
                    $_SESSION['easy2err'][] = __LINE__ . ' : ' . $filename . ' does not exist.';
                    return FALSE;
                }
            }
            if ($e2gDebug == 1)
                return $fileInfo;
            else
                return TRUE;
        }
        else
            continue;
    }

    /**
     * To check the specified resource is a valid folder, although it has a DOT in it.
     * @author goldsky <goldsky@modx-id.com>
     * @param string $foldername the folder's name
     */
    protected function validFolder($foldername) {
        $e2gDebug = $this->e2gpub_cfg['e2g_debug'];

        $openFolder = @opendir($foldername);
        if (!$openFolder) {
            if ($e2gDebug == 1) {
                $_SESSION['easy2err'][] = __LINE__ . ' : <b style="color:red;">' . $foldername . '</b> is NOT a valid folder, probably a file.';
            }
            return FALSE;
        } else {
            if ($e2gDebug == 1) {
                echo '<h2>' . $foldername . '</h2>';
                echo '<ul>';
                $file = array();
                while (( FALSE !== ( $file = readdir($openFolder) ))) {
                    if ($file != "." && $file != "..") {
                        if (filetype($file) == 'dir') {
                            echo '<li>dir: <b style="color:green;">' . $file . '</b></li>';
                        }
                        else
                            echo "<li> $file </li>";
                        clearstatcache();
                    }
                }
                echo '</ul>';
            }
            closedir($openFolder);
        }
        if ($e2gDebug == 1)
            return '<br /><b style="color:red;">' . $foldername . '</b> is a valid folder.';
        else
            return TRUE;
    }

    /**
     * Replace the basename function with this to grab non-unicode character.
     * @link http://drupal.org/node/278425#comment-2571500
     * @param  string $path the file path
     * @return string the path's basename
     */
    protected function basenameSafe($path) {
        $path = rtrim($path, '/');
        $path = explode('/', $path);

        // encoding
        $endPath = end($path);
//        $encodingHtml= htmlspecialchars($this->e2gEncode($endPath), ENT_QUOTES);
//        $encodingHtml= htmlspecialchars($endPath, ENT_QUOTES);
        $encodingHtml = $endPath;
        return $encodingHtml;
    }

    /**
     * to check email validation
     * @param  string $email
     * @return bool   true/false
     */
    public function checkEmailAddress($email) {
        if (!preg_match("/^([a-zA-Z0-9])+([\.a-zA-Z0-9_-])*@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-]+)*\.([a-zA-Z]{2,6})$/", $email)) {
            return false;
        }
        return true;
    }

    /**
     * Gallery's TEMPLATE function
     * @param string $tpl    gallery's template (@FILE or chunk)
     * @param string $data   template's array data
     * @param string $prefix placeholder's prefix
     * @param string $suffix placeholder's suffix
     * @return string templated data
     */
    public function filler($tpl, $data, $prefix = '[+easy2:', $suffix = '+]') {
        foreach ($data as $k => $v) {
            $tpl = str_replace($prefix . (string) $k . $suffix, (string) $v, $tpl);
        }
        return $tpl;
    }

    /**
     * Invoking the script with plugin, at any specified places.
     * @param string    $e2gEvtName         event trigger.
     * @param mixed     $e2gEvtParams       parameters array: depends on the event trigger.
     * @param bool      $respectDisabling   using the disabled option as query filter
     * @return mixed    if TRUE, will return the indexfile. Otherwise this will return FALSE.
     */
    protected function plugin($e2gEvtName, $e2gEvtParams = array(), $e2gPluginName = null, $respectDisabling = TRUE) {
        $modx = $this->modx;
        $lng = $this->lng;

        if (!$e2gEvtName)
            return false;
        if (!file_exists(MODX_BASE_PATH . 'assets/modules/easy2/includes/configs/config.events.easy2gallery.php'))
            return false;
        else {
            // include the event's names
            include MODX_BASE_PATH . 'assets/modules/easy2/includes/configs/config.events.easy2gallery.php';
            foreach ($e2gEvents as $k => $v) {
                if ($v != $e2gEvtName)
                    continue;
                $evtid = $k;
            }
        }

        $selectIndexFile = 'SELECT p.indexfile FROM ' . $modx->db->config['table_prefix'] . 'easy2_plugins p '
                . 'LEFT JOIN ' . $modx->db->config['table_prefix'] . 'easy2_plugin_events e '
                . 'ON p.id=e.pluginid '
                . 'WHERE ';
        if ($e2gPluginName != null)
            $selectIndexFile .= 'p.name=\'' . $e2gPluginName . '\' AND ';
        if ($respectDisabling !== FALSE) {
            $selectIndexFile .= 'p.disabled=\'0\' AND ';
        }
        
        $selectIndexFile .= 'e.evtid=\'' . $evtid . '\' '
                . 'ORDER BY priority,pluginid ASC';

        $queryIndexFile = mysql_query($selectIndexFile);
        if (!$queryIndexFile) {
            return __LINE__ . ' : ' . $lng['invoke_event_err'] . '<br />' . mysql_error()
            . '<br />' . $selectIndexFile;
        } else {
            while ($row = mysql_fetch_array($queryIndexFile)) {
                $indexFiles[] = $row['indexfile'];
            }

            if (!empty($indexFiles)) {
                ob_start();
                foreach ($indexFiles as $indexFile) {
                    if (file_exists(MODX_BASE_PATH . $indexFile)) {
                        include MODX_BASE_PATH . $indexFile;
                    }
                }
                $out = ob_get_contents();
                ob_end_clean();
                return $out;
            }
            else
                return false;
        }
        // just for a clean exit
        return false;
    }

}