<?php

/**
 * EASY 2 GALLERY
 * Gallery Snippet Class for Easy 2 Gallery Module for MODx Evolution
 * @author Cx2 <inteldesign@mail.ru>
 * @author Temus <temus3@gmail.com>
 * @author goldsky <goldsky@modx-id.com>
 * @version 1.4.0
 */
class E2gPub { // public/protected class
    /**
     * Inherit MODx functions
     * @var mixed modx's API
     */

    public $modx;
    /**
     * Parameter configuration from the snippet or module
     * @var mixed parameters' configurations
     */
    public $e2gPubCfg;
    /**
     * The internal variables of this class
     * @var mixed all the processing variables
     */
    private $_e2g = array();

    public function __construct($modx, $e2gPubCfg) {
        set_time_limit(0);
        $this->modx = & $modx;
        $this->e2gPubCfg = $e2gPubCfg;
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
    protected function e2gEncode($text, $callback=FALSE) {
        $e2gEncode = $this->e2gPubCfg['e2g_encode'];

        if ($e2gEncode == 'none') {
            if ($callback == FALSE) {
                $convertedText = $text;
            }
            if ($callback == 'ucfirst') {
                $convertedText = ucfirst($text);
            }

            // if no matching criteria, just display plain text
            if ($convertedText == FALSE)
                $convertedText = $text;

            return $convertedText;
        }

        if ($e2gEncode == 'UTF-8') {
            if ($callback == FALSE) {
                $convertedText = utf8_encode($text);
            }
            // http://bytes.com/topic/php/answers/444382-ucfirst-utf-8-setlocale#post1693669
            if ($callback == 'ucfirst') {
                $fc = mb_strtoupper(mb_substr($text, 0, 1, 'UTF-8'), 'UTF-8');
                $convertedText = $fc . mb_substr($text, 1, mb_strlen($text, 'UTF-8'), 'UTF-8');
            }

            // if no matching criteria, just display plain text
            if ($convertedText == FALSE)
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

            if ($callback == FALSE) {
                // fixedmachine -- http://modxcms.com/forums/index.php/topic,49266.msg292206.html#msg292206
                $convertedText = UTF8::convert_to($text, mb_detect_encoding($text));
            }
            if ($callback == 'ucfirst') {
                $convertedText = UTF8::ucfirst($text);
            }

            // if no matching criteria, just display plain text
            if ($convertedText == FALSE)
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
    protected function e2gDecode($text, $callback=FALSE) {
        $e2gEncode = $this->e2gPubCfg['e2g_encode'];

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
                    if ($convertedText != FALSE)
                        $text = $convertedText;
                    return $text;
                }
                else {
                    $convertedText = UTF8::convert_from($text, $mbDetectEncoding);
                    if ($convertedText != FALSE)
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
            return NULL;
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
            return NULL;
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
        $e2gDebug = $this->e2gPubCfg['e2g_debug'];

        $f = $this->basenameSafe($filename);
        $f = $this->e2gEncode($f);
        if ($this->validFolder($filename)) {
            if ($e2gDebug == 1) {
                echo __LINE__ . ' : <b style="color:red;">' . $filename . '</b> is not a file, it\'s a valid folder.';
            }
            return FALSE;
        } elseif ($f != '' && !$this->validFolder($filename)) {
            if (file_exists(realpath($filename))) {
                $size = getimagesize($filename);
                $fp = fopen($filename, "rb");
                $allowedExt = array(
                    'image/jpeg' => TRUE,
                    'image/gif' => TRUE,
                    'image/png' => TRUE
                );
                if (!empty($size["mime"]) && $allowedExt[$size["mime"]] && $fp) {
                    if ($e2gDebug == 1) {
                        $fileInfo = 'Filename <b style="color:red;">' . $f . '</b> is a valid image file: ' . $size["mime"] . ' - ' . $size[3];
                    }
                    else
                        return TRUE;
                } else {
                    if ($e2gDebug == 1)
                        $fileInfo = 'Filename <b style="color:red;">' . $f . '</b> is an invalid image file: ' . $size[2] . ' - ' . $size[3];
                    else {
                        return FALSE;
                    }
                }
            } else {
                if ($e2gDebug == 1)
                    $fileInfo .= 'Filename <b style="color:red;">' . $f . '</b> is NOT exists.<br />';
                else {
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
        $e2gDebug = $this->e2gPubCfg['e2g_debug'];

        $openFolder = @opendir($foldername);
        if (!$openFolder) {
            if ($e2gDebug == 1) {
                echo __LINE__ . ' : <b style="color:red;">' . $foldername . '</b> is NOT a valid folder, probably a file.';
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
        if ($e2gDebug == 1) {
            echo __LINE__ . ' : <br /><b style="color:red;">' . $foldername . '</b> is a valid folder.';
            return FALSE;
        }

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
     * @return bool   true/FALSE
     */
    public function checkEmailAddress($email) {
        if (!preg_match("/^([a-zA-Z0-9])+([\.a-zA-Z0-9_-])*@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-]+)*\.([a-zA-Z]{2,6})$/", $email)) {
            return FALSE;
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
    protected function filler($tpl, $data, $prefix = '[+easy2:', $suffix = '+]') {
        foreach ($data as $k => $v) {
            $tpl = str_replace($prefix . (string) $k . $suffix, (string) $v, $tpl);
        }
        return $tpl;
    }

    /**
     * Get template
     * @param string    $tpl Template
     * @return string   Template's content
     */
    protected function getTpl($tpl) {
        $modx = $this->modx;
        // this parameter convert $tpl to be a path
        $tplCfg = $this->e2gPubCfg[$tpl];

        if (file_exists(realpath($tplCfg))) {
            $tplContent = file_get_contents($tplCfg);
            return $tplContent;
        } elseif (!empty($modx->chunkCache[$tplCfg])) {
            $tplContent = $modx->chunkCache[$tplCfg];
            return $tplContent;
        } else {
            echo 'Template ' . $tpl . ' is not found!<br />';
            return FALSE;
        }
    }

    /**
     * Invoking the script with plugin, at any specified places.
     * @param string    $e2gEvtName         event trigger.
     * @param mixed     $e2gEvtParams       parameters array: depends on the event trigger.
     * @param bool      $respectDisabling   using the disabled option as query filter
     * @return mixed    if TRUE, will return the indexfile. Otherwise this will return FALSE.
     */
    protected function plugin($e2gEvtName, $e2gEvtParams = array(), $e2gPluginName = NULL, $respectDisabling = TRUE) {
        $modx = $this->modx;

        if (!$e2gEvtName)
            return FALSE;
        if (!file_exists(realpath(MODX_BASE_PATH . 'assets/modules/easy2/includes/configs/config.events.easy2gallery.php')))
            return FALSE;
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
        if ($e2gPluginName != NULL)
            $selectIndexFile .= 'p.name=\'' . $e2gPluginName . '\' AND ';
        if ($respectDisabling !== FALSE) {
            $selectIndexFile .= 'p.disabled=\'0\' AND ';
        }

        $selectIndexFile .= 'e.evtid=\'' . $evtid . '\' '
                . 'ORDER BY priority,pluginid ASC';

        $queryIndexFile = mysql_query($selectIndexFile);
        if (!$queryIndexFile) {
            echo __METHOD__ . ', ' . __LINE__ . ' : ' . mysql_error() . '<br />' . $selectIndexFile;
            return FALSE;
        } else {
            while ($row = mysql_fetch_array($queryIndexFile)) {
                $indexFiles[] = $row['indexfile'];
            }

            if (!empty($indexFiles)) {
                ob_start();
                foreach ($indexFiles as $indexFile) {
                    if (file_exists(realpath(MODX_BASE_PATH . $indexFile))) {
                        include MODX_BASE_PATH . $indexFile;
                    }
                }
                $out = ob_get_contents();
                ob_end_clean();
                return $out;
            }
            else
                return FALSE;
        }
        // just for a clean exit
        return FALSE;
    }

    /**
     * To get thumbnail for each folder
     * @param int       $gid    folder's ID
     * @param string    $gdir   gallery's ROOT path
     * @return string image's source
     */
    protected function folderImg($gid, $gdir) {
        $modx = $this->modx;
        $catThumbOrderBy = $this->e2gPubCfg['cat_thumb_orderby'];
        $catThumbOrder = $this->e2gPubCfg['cat_thumb_order'];

        // http://modxcms.com/forums/index.php/topic,23177.msg273448.html#msg273448
        // ddim -- http://modxcms.com/forums/index.php/topic,48314.msg286241.html#msg286241
        $selectFiles = 'SELECT F.* '
                . 'FROM ' . $modx->db->config['table_prefix'] . 'easy2_files F '
                . 'WHERE F.dir_id IN ('
                . 'SELECT A.cat_id FROM '
                . $modx->db->config['table_prefix'] . 'easy2_dirs A, '
                . $modx->db->config['table_prefix'] . 'easy2_dirs B '
                . 'WHERE ('
                . 'B.cat_id=' . $gid . ' '
                . 'AND A.cat_left >= B.cat_left '
                . 'AND A.cat_right <= B.cat_right '
                . 'AND A.cat_level >= B.cat_level '
//                . 'AND A.cat_visible = 1'
                . ') '
                . 'ORDER BY A.cat_level ASC '
                . ') '
                . 'AND F.status = 1 '
        ;
        if ($catThumbOrderBy == 'random') {
            $selectFiles .= 'ORDER BY rand() ';
        } else {
            $selectFiles .= 'ORDER BY F.' . $catThumbOrderBy . ' ' . $catThumbOrder . ' ';
        }

        $queryFiles = mysql_query($selectFiles);
        if (!$queryFiles) {
            $o = __LINE__ . ': __METHOD__ = ' . __METHOD__ . '<br />';
            $o .= mysql_error() . '<br />' . $selectFiles . '<br />';
            echo $o;
            return FALSE;
        }

        while ($l = mysql_fetch_array($queryFiles, MYSQL_ASSOC)) {
            $files[] = $l;
        }
        mysql_free_result($queryFiles);

        $countFiles = count($files);
        if ($countFiles === 0)
            return FALSE;

        /**
         * This part is to check whether the file exists in the file
         * system or not, and stops at which ever returns TRUE.
         */
        $folderImgInfos = array();
        $folderImgInfos['count'] = $countFiles;
        foreach ($files as $file) {
            // search image for subdir
            $getPath = $this->getPath($file['dir_id']);
            $imagePath = $gdir . $getPath . $file['filename'];
            if (!$this->validFile($imagePath)) {
                continue;
            } else {
                $folderImgInfos = $file;
                break;
            }
        }

        /**
         * returned as folder's thumbnail's info array
         */
        return $folderImgInfos;
    }

    /**
     * To get paths from the parent directory up to the Easy 2's ROOT gallery
     * @param int       $dirId      parent directory's ID
     * @param string    $option     output options: cat_name | cat_alias
     * @param mixed     $format     output formats: string | array
     * @return string
     */
    protected function getPath($dirId, $option='cat_name', $format='string') {
        $modx = $this->modx;

        $selectDir = 'SELECT A.parent_id, A.cat_id,A.cat_name,A.cat_alias '
                . 'FROM ' . $modx->db->config['table_prefix'] . 'easy2_dirs A, '
                . $modx->db->config['table_prefix'] . 'easy2_dirs B '
                . 'WHERE B.cat_id=' . $dirId . ' '
                . 'AND B.cat_left BETWEEN A.cat_left AND A.cat_right '
                . 'ORDER BY A.cat_left ASC '
        ;

        $queryDir = mysql_query($selectDir);
        if (!$queryDir) {
            return NULL; // do not set FALSE here, asuming there are multiple gids
        }

        $resultArray = array();
        $resultString = array();
        $result = '';
        while ($l = mysql_fetch_array($queryDir)) {
            if ($option != 'cat_name' && empty($l[$option]))
                $l[$option] = $l['cat_name'];
            $resultArray[$l['cat_id']] = $l[$option];
            $resultString[$l['parent_id']] = $l[$option];
        }
        mysql_free_result($queryDir);

        if (empty($resultArray))
            return NULL;

        if ('array' == $format) {
            $result = $resultArray;
        } else {
            // skip the value of Easy 2's ROOT gallery ID/name
            unset($resultString['0']);
            $result = implode('/', array_values($resultString));
            $result .= empty($resultString) ? '' : '/';
        }

        return $result;
    }

    /**
     * Crop text by length
     * @param   string  $charSet    character set
     * @param   int     $nameLen    text's length
     * @param   string  $text       text to be cropped
     * @return  string  shorthened text
     */
    protected function cropName($mbstring, $charSet, $nameLen, $text) {
        if (empty($charSet) || empty($nameLen))
            return FALSE;

        $croppedName = $text;
        if (trim(htmlspecialchars_decode($text)) == '') {
            $croppedName = '&nbsp;';
        } elseif ($mbstring) {
            if (mb_strlen($text, $charSet) > (int)$nameLen)
                $croppedName = mb_substr($text, 0, (int)$nameLen - 1, $charSet) . '...';
        } elseif (strlen($text) >(int) $nameLen) {
            $croppedName = substr($text, 0, (int)$nameLen - 1) . '...';
        }
        return $croppedName;
    }

}