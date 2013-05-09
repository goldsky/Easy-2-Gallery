<?php

/**
 * EASY 2 GALLERY
 * Gallery Snippet Class for Easy 2 Gallery Module for MODx Evolution
 * @author Cx2 <inteldesign@mail.ru>
 * @author Temus <temus3@gmail.com>
 * @author goldsky <goldsky@fastmail.fm>
 */
class E2gPub { // public/public class
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
    /**
     * Directory information
     * @var array
     */
    private $_dirInfo = array();
    /**
     * File information
     * @var array
     */
    private $_fileInfo = array();

    public function __construct($modx, $e2gPubCfg) {
        // Apache's timeout: 300 secs
        if (function_exists('ini_get') && !ini_get('safe_mode')) {
            if (function_exists('set_time_limit')) {
                set_time_limit(300);
            }
            if (function_exists('ini_set')) {
                if (ini_get('max_execution_time') !== 300) {
                    ini_set('max_execution_time', 300);
                }
            }
        }

        $this->modx = & $modx;
        $this->e2gPubCfg = $e2gPubCfg;
    }

    /**
     * Only to load files
     * @author  Rin <http://forum.dklab.ru/profile.php?mode=viewprofile&u=3940>
     * @link    http://forum.dklab.ru/viewtopic.php?p=91015#91015
     * @return  bool    directly from the class.
     */
    public function loadUtfRin() {
        include_once MODX_BASE_PATH . 'assets/modules/easy2/includes/UTF8-2.1.1/UTF8.php';
        include_once MODX_BASE_PATH . 'assets/modules/easy2/includes/UTF8-2.1.1/ReflectionTypehint.php';

        return null;
    }

    /**
     * Encoding using the class from
     * @author  Rin <http://forum.dklab.ru/profile.php?mode=viewprofile&u=3940>
     * @link    http://forum.dklab.ru/viewtopic.php?p=91015#91015
     * @param   string  $text           text to be converted
     * @param   string  $callback       call back function's name
     * @param   array   $callbackParams call back parameters (in an array)
     * @return  string  converted text
     */
    public function utfRinEncode($text, $callback = FALSE, $callbackParams = array()) {
        $this->loadUtfRin();
        $convertedText = FALSE;

        if ($callback !== FALSE) {
            $callbackParams = array_merge(array($text), $callbackParams);
            $convertedText = call_user_func_array(array('UTF8', $callback), $callbackParams);
        } else {
            // fixedmachine -- http://modxcms.com/forums/index.php/topic,49266.msg292206.html#msg292206
            $convertedText = UTF8::convert_to($text, mb_detect_encoding($text));
        }

        return $convertedText;
    }

    /**
     * Decoding using the class from
     * @author  Rin <http://forum.dklab.ru/profile.php?mode=viewprofile&u=3940>
     * @link    http://forum.dklab.ru/viewtopic.php?p=91015#91015
     * @param   string  $text           text to be converted
     * @param   string  $callback       call back function's name
     * @param   array   $callbackParams call back parameters (in an array)
     * @return  string  converted text
     */
    public function utfRinDecode($text, $callback = FALSE, $callbackParams = array()) {
        $this->loadUtfRin();
        $convertedText = FALSE;

        $mbDetectEncoding = mb_detect_encoding($text);
        if ($callback !== FALSE) {
            $callbackParams = array_merge(array($text), $callbackParams);
            $convertedText = call_user_func_array(array('UTF8', $callback), $callbackParams);
        } elseif (!$mbDetectEncoding || ($mbDetectEncoding != 'ASCII' && $mbDetectEncoding != 'UTF-8')) {
            // fixedmachine -- http://modxcms.com/forums/index.php/topic,49266.msg292206.html#msg292206
            $convertedText = UTF8::convert_from($text, "ASCII");
        } else {
//            $convertedText = UTF8::convert_from($text, $mbDetectEncoding);
            $convertedText = utf8_decode($text);
        }

        return $convertedText;
    }

    /**
     * Unicode character encoding work around.<br />
     * For human reading.<br />
     * The value is set from the module's config page.
     *
     * @link http://a4esl.org/c/charset.html
     * @param   string  $text           the string to be encoded
     * @param   string  $callback       call back function
     * @param   string  $callbackParams call back parameters
     * @return  string  returns the encoding
     */
    public function e2gEncode($text, $callback=FALSE, $callbackParams = array()) {
        $convertedText = FALSE;

        if ($this->e2gPubCfg['e2g_encode'] == 'none') {
            if ($callback !== FALSE) {
                $callbackParams = array_merge(array($text), $callbackParams);
                $convertedText = call_user_func($callback, $callbackParams);
            } else {
                $convertedText = $text;
            }
        }

        if ($this->e2gPubCfg['e2g_encode'] == 'UTF-8') {
            if ($callback !== FALSE && $callback != 'ucfirst') {
                $callbackParams = array_merge($text, $callbackParams);
                $convertedText = call_user_func($callback, $callbackParams);
            } elseif ($callback == 'ucfirst') {
                // http://bytes.com/topic/php/answers/444382-ucfirst-utf-8-setlocale#post1693669
                $fc = mb_strtoupper(mb_substr($text, 0, 1, 'UTF-8'), 'UTF-8');
                $convertedText = $fc . mb_substr($text, 1, mb_strlen($text, 'UTF-8'), 'UTF-8');
            } else {
                $convertedText = utf8_encode($text);
            }
        }

        if ($this->e2gPubCfg['e2g_encode'] == 'UTF-8 (Rin)') {
            $convertedText = $this->utfRinEncode($text, $callback, $callbackParams);
        }

        return $convertedText;
    }

    /**
     * Unicode character decoding work around.<br />
     * For file system reading.<br />
     * The value is set from the module's config page.
     *
     * @link http://a4esl.org/c/charset.html
     * @param   string  $text           the string to be decoded
     * @param   string  $callback       call back function
     * @param   string  $callbackParams call back parameters
     * @return  string  returns the decoding
     */
    public function e2gDecode($text, $callback=FALSE, $callbackParams = array()) {
        $convertedText = FALSE;

        if ($this->e2gPubCfg['e2g_encode'] == 'none') {
            if ($callback !== FALSE) {
                $callbackParams = array_merge(array($text), $callbackParams);
                $convertedText = call_user_func($callback, $callbackParams);
            } else {
                $convertedText = $text;
            }
        }

        if ($this->e2gPubCfg['e2g_encode'] == 'UTF-8') {
            if ($callback !== FALSE) {
                $callbackParams = array_merge($text, $callbackParams);
                $convertedText = call_user_func($callback, $callbackParams);
            } else {
                $convertedText = utf8_decode($text);
            }
        }

        if ($this->e2gPubCfg['e2g_encode'] == 'UTF-8 (Rin)') {
            $convertedText = $this->utfRinDecode($text, $callback, $callbackParams);
        }

        return $convertedText;
    }

    /**
     * Sanitizing string from specified characters
     * @param   string  $string         the text
     * @param   array   $chars          filtered characters
     * @param   array   $allowedTags    allowed characters
     * @param   bool    $preg           using preg_match
     * @return  string  filtered string
     */
    public function sanitizedString($string, $chars = array('/', "'", '"', '(', ')', ';', '>', '<'), $allowedTags = array(), $preg=FALSE) {
        $string = trim($string);

        $allowedTagStr = @implode('', $allowedTags);
        $string = strip_tags($string, $allowedTagStr);
        $string = str_replace($chars, '', $string);
        $string = preg_replace('/[[:space:]]/', ' ', $string);

        if ($preg) {
            $allowedTagPreg = (!empty($allowedTags) ? '|\\' . @implode('\\', $allowedTags) : '');
            $string = preg_replace("/[^A-Za-z0-9_\-\.\/[[:space:]]" . $allowedTagPreg . "]/", '', $string);
        }

        $string = htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
        $string = mysql_real_escape_string($string);

        return $string;
    }

    /**
     * To get directory's information
     * @param   int     $dirId  gallery's ID
     * @param   string  $field  database field
     * @return  string  the directory's data
     */
    public function getDirInfo($dirId, $field) {
        if (!empty($this->_dirInfo[$dirId][$field])) {
            return $this->_dirInfo[$dirId][$field];
        }

        $dirInfo = array();

        $q = 'SELECT ' . $field . ' FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_dirs '
                . 'WHERE cat_id=' . $dirId . ' ';

        if (!($res = mysql_query($q))) {
            if ($this->e2gPubCfg['e2g_debug'] == 1) {
                return (__LINE__ . ' Wrong field: ' . $field);
            } else {
                return;
            }
        }
        while ($l = mysql_fetch_assoc($res)) {
            $dirInfo[$field] = $l[$field];
        }
        mysql_free_result($res);
        if (empty($dirInfo[$field])) {
            return;
        }

        $this->_dirInfo[$dirId][$field] = htmlspecialchars_decode($dirInfo[$field], ENT_QUOTES);

        return $this->_dirInfo[$dirId][$field];
    }

    /**
     * To get file's information
     * @param   int     $fileId  file's ID
     * @param   string  $field  database field
     * @return  string  the file's data
     */
    public function getFileInfo($fileId, $field) {
        if (!empty($this->_fileInfo[$fileId][$field])) {
            return $this->_fileInfo[$fileId][$field];
        }

        $fileInfo = array();

        $q = 'SELECT ' . $field . ' FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_files '
                . 'WHERE id=' . $fileId . ' ';

        if (!($res = mysql_query($q)))
            return (__LINE__ . ' Wrong field: ' . $field);
        while ($l = mysql_fetch_assoc($res)) {
            $fileInfo[$field] = $l[$field];
        }
        mysql_free_result($res);
        if (empty($fileInfo[$field]))
            return;

        $this->_fileInfo[$fileId][$field] = htmlspecialchars_decode($fileInfo[$field], ENT_QUOTES);

        return $this->_fileInfo[$fileId][$field];
    }

    /**
     * To check the specified resource has a valid file extenstion.
     * @todo need a rework to make it more extendable
     * @param   string  $filename the filename
     * @return  boolean valid extension
     */
    public function validExt($filename) {
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
     * @param   strin   $filename   filename
     * @return  mixed   boolean | fileinfo's array
     */
    public function validFile($filename) {
        if (basename($filename) === 'index.html') {
            return FALSE;
        }
        $fileRealPath = realpath($filename);
        if (empty($fileRealPath)) {
            if ($this->e2gPubCfg['e2g_debug'] == 1) {
                echo __LINE__ . 'Filename is not real ' . $filename;
            }
            return FALSE;
        }
        $f = $this->basenameSafe($fileRealPath);
        $f = $this->e2gEncode($f);
        if ($this->validFolder($fileRealPath)) {
            if ($this->e2gPubCfg['e2g_debug'] == 1) {
                echo __LINE__ . ' : <b style="color:red;">' . $filename . '</b> is not a file, it\'s a valid folder.';
            }
            return FALSE;
        } elseif ($f != '' && !$this->validFolder($fileRealPath)) {
            if (file_exists($fileRealPath)) {
                $size = getimagesize($fileRealPath);
                $fp = fopen($fileRealPath, "rb");
                $allowedExt = array(
                    'image/jpeg' => TRUE,
                    'image/gif' => TRUE,
                    'image/png' => TRUE
                );
                if (!empty($size["mime"]) && $allowedExt[$size["mime"]] && $fp) {
                    if ($this->e2gPubCfg['e2g_debug'] == 1) {
                        $fileInfo = 'Filename <b style="color:red;">' . $f . '</b> is a valid image file: ' . $size["mime"] . ' - ' . $size[3];
                    }
                    else
                        return TRUE;
                } else {
                    if ($this->e2gPubCfg['e2g_debug'] == 1)
                        $fileInfo = 'Filename <b style="color:red;">' . $f . '</b> is an invalid image file: ' . $size[2] . ' - ' . $size[3];
                    else {
                        return FALSE;
                    }
                }
            } else {
                if ($this->e2gPubCfg['e2g_debug'] == 1)
                    $fileInfo .= 'Filename <b style="color:red;">' . $f . '</b> is NOT exists.<br />';
                else {
                    return FALSE;
                }
            }
            if ($this->e2gPubCfg['e2g_debug'] == 1)
                return $fileInfo;
            else
                return TRUE;
        }
    }

    /**
     * To check the specified resource is a valid folder, although it has a DOT in it.
     * @param   string  $foldername the folder's name
     * @return  boolean valid or not
     */
    public function validFolder($foldername) {
        $foldername = str_replace('//', '/', $foldername);
        $folderRealPath = realpath($foldername);
        if (empty($folderRealPath)) {
            if ($this->e2gPubCfg['e2g_debug'] == 1) {
                echo __LINE__ . '<b style="color:red;">' . $foldername . '</b> is NOT a valid folder.<br />';
            }
            return FALSE;
        }
        $openFolder = @opendir($folderRealPath);
        if (!$openFolder) {
            if ($this->e2gPubCfg['e2g_debug'] == 1) {
                echo __LINE__ . ' : <b style="color:red;">' . $foldername . '</b> is NOT a valid folder, probably a file.<br />';
            }
            return FALSE;
        } else {
            if ($this->e2gPubCfg['e2g_debug'] == 1) {
                echo '<h2>' . $foldername . '</h2>';
                echo '<ul>';
                $file = array();
                while (( FALSE !== ( $file = readdir($openFolder) ))) {
                    if ($file != "." && $file != "..") {
                        if (@filetype($file) == 'dir') {
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
        if ($this->e2gPubCfg['e2g_debug'] == 1) {
            echo __LINE__ . ' : <br /><b style="color:red;">' . $foldername . '</b> is a valid folder.<br />';
        }

        return TRUE;
    }

    /**
     * Replace the basename function with this to grab non-unicode character.
     * @link http://drupal.org/node/278425#comment-2571500
     * @param  string $path the file path
     * @return string the path's basename
     */
    public function basenameSafe($path) {
        $path = rtrim($path, DIRECTORY_SEPARATOR);
        $path = explode(DIRECTORY_SEPARATOR, $path);

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
    public function filler($tpl, $data, $prefix = '[+easy2:', $suffix = '+]') {
        if (empty($data) || !is_array($data)) {
            return FALSE;
        }
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
    public function getTpl($tpl) {
        if (!empty($this->e2gPubCfg[$tpl]) && file_exists(realpath($this->e2gPubCfg[$tpl]))) {
            $tplContent = file_get_contents(realpath($this->e2gPubCfg[$tpl]));
            return $tplContent;
        } elseif (!empty($this->modx->chunkCache[$this->e2gPubCfg[$tpl]])) {
            $tplContent = $this->modx->chunkCache[$this->e2gPubCfg[$tpl]];
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
     * @param string    $e2gPluginName      plugin's name
     * @param bool      $respectDisabling   using the disabled option as query filter
     * @return mixed    if TRUE, will return the indexfile. Otherwise this will return FALSE.
     */
    public function plugin($e2gEvtName, $e2gEvtParams = array(), $e2gPluginName = NULL, $respectDisabling = TRUE) {
        // shorthand for the plugin index file
        $modx = $this->modx;

        if (!$e2gEvtName)
            return FALSE;

        $eventConfigFile = realpath(MODX_BASE_PATH . 'assets/modules/easy2/includes/configs/config.events.easy2gallery.php');
        if (empty($eventConfigFile) || !file_exists($eventConfigFile)) {
            return FALSE;
        } else {
            // include the event's names
            include $eventConfigFile;
            foreach ($e2gEvents as $k => $v) {
                if ($v != $e2gEvtName)
                    continue;
                $evtid = $k;
            }
        }

        $selectIndexFile = 'SELECT p.indexfile FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_plugins p '
                . 'LEFT JOIN ' . $this->modx->db->config['table_prefix'] . 'easy2_plugin_events e '
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
            while ($row = mysql_fetch_assoc($queryIndexFile)) {
                $indexFiles[] = $row['indexfile'];
            }

            if (!empty($indexFiles)) {
                ob_start();
                foreach ($indexFiles as $indexFile) {
                    $realPathFile = realpath(MODX_BASE_PATH . $indexFile);
                    if (!empty($realPathFile) && file_exists($realPathFile)) {
                        include $realPathFile;
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
     * To get thumbnail for each folder, from manual selection or DB generated
     * @param int       $gid    folder's ID
     * @param string    $gdir   gallery's ROOT path
     * @return string image's source
     */
    public function folderImg($gid, $gdir) {

        $excludeDirWebAccess = $this->checkWebAccess('dir');
        $excludeFileWebAccess = $this->checkWebAccess('file');

        /**
         * Select the file from the manual selected thumbnail
         */
        $selectDbFile = 'SELECT cat_thumb_id '
                . 'FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_dirs '
                . 'WHERE cat_id = ' . $gid . ' ';

        if ($excludeDirWebAccess !== FALSE) {
            $selectDbFile .= 'AND cat_id NOT IN (' . $excludeDirWebAccess . ') ';
        }

        if ($excludeFileWebAccess !== FALSE) {
            $selectDbFile .= 'AND cat_thumb_id NOT IN (' . $excludeFileWebAccess . ') ';
        }

        $queryDbFile = mysql_query($selectDbFile);
        if (!$queryDbFile) {
            $o = __LINE__ . ': __METHOD__ = ' . __METHOD__ . '<br />';
            $o .= mysql_error() . '<br />' . $selectDbFile . '<br />';
            echo $o;
            return FALSE;
        }

        while ($l = mysql_fetch_assoc($queryDbFile)) {
            $catThumbId = $l['cat_thumb_id'];
        }

		$specifiedFolderImg = '';
        if (!empty($catThumbId)) {
            $catThumbPath = $this->getPath($this->getFileInfo($catThumbId, 'dir_id'));
            $catThumbName = $this->getFileInfo($catThumbId, 'filename');
            if (file_exists(realpath($this->e2gDecode($gdir . $catThumbPath . $catThumbName)))) {
                $selectThumbFile = 'SELECT * '
                        . 'FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_files '
                        . 'WHERE id = ' . $catThumbId
                ;
                $queryThumbFile = mysql_query($selectThumbFile);
                if (!$queryThumbFile) {
                    $o = __LINE__ . ': __METHOD__ = ' . __METHOD__ . '<br />';
                    $o .= mysql_error() . '<br />' . $selectThumbFile . '<br />';
                    echo $o;
                    return FALSE;
                }

                $specifiedFolderImg = mysql_fetch_assoc($queryThumbFile);
                mysql_free_result($queryThumbFile);
            }
        }

        /**
         * Select the file from DB generated
         * http://modxcms.com/forums/index.php/topic,23177.msg273448.html#msg273448
         * ddim -- http://modxcms.com/forums/index.php/topic,48314.msg286241.html#msg286241
         */
        $selectFiles = 'SELECT F.* '
                . 'FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_files F '
                . 'WHERE F.dir_id IN ('
                . 'SELECT A.cat_id FROM '
                . $this->modx->db->config['table_prefix'] . 'easy2_dirs A, '
                . $this->modx->db->config['table_prefix'] . 'easy2_dirs B '
                . 'WHERE ('
                . 'B.cat_id=' . $gid . ' '
                . 'AND A.cat_left >= B.cat_left '
                . 'AND A.cat_right <= B.cat_right '
                . 'AND A.cat_level >= B.cat_level '
//                . 'AND A.cat_visible = 1'         // disabled, because Module needs to see them ALL
                . ') '
                . 'ORDER BY A.cat_level ASC '
                . ') '
                . 'AND F.status = 1 '
        ;

        if ($excludeDirWebAccess !== FALSE) {
            $selectDbFile .= 'AND A.cat_id NOT IN (' . $excludeDirWebAccess . ') ';
        }

        if ($excludeFileWebAccess !== FALSE) {
            $selectDbFile .= 'AND F.id NOT IN (' . $excludeFileWebAccess . ') ';
        }

        if ($this->e2gPubCfg['cat_thumb_orderby'] == 'random') {
            $selectFiles .= 'ORDER BY rand() ';
        } else {
            $selectFiles .= 'ORDER BY F.' . $this->e2gPubCfg['cat_thumb_orderby'] . ' ' . $this->e2gPubCfg['cat_thumb_order'] . ' ';
        }

        $queryFiles = mysql_query($selectFiles);
        if (!$queryFiles) {
            $o = __LINE__ . ': __METHOD__ = ' . __METHOD__ . '<br />';
            $o .= mysql_error() . '<br />' . $selectFiles . '<br />';
            echo $o;
            return FALSE;
        }

        while ($l = mysql_fetch_assoc($queryFiles)) {
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
		if (empty($specifiedFolderImg)) {
			foreach ($files as $file) {
				// search image for subdir
				$getPath = $this->getPath($file['dir_id']);
				$imagePath = $this->e2gDecode($gdir . $getPath . $file['filename']);
				if (!$this->validFile($imagePath)) {
					continue;
				} else {
					$folderImgInfos = $file;
					break;
				}
			}
		} else {
			$folderImgInfos = $specifiedFolderImg;
		}
        $folderImgInfos['count'] = $countFiles;

        /**
         * returned as folder's thumbnail's info array
         */
        return $folderImgInfos;
    }

    /**
     * Filter the web access to the restricted galleries/pictures.
     * @param string $type  dir/file selection
     * @return string the excluded ids from the SQL parameter
     */
    public function checkWebAccess($type) {
        /**
         * Get all the restricted list ids
         */
        $allWebAccess = array();
        $allWebAccessQuery = 'SELECT DISTINCT id FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_webgroup_access WHERE '
                . ' type=\'' . $type . '\' ';
        $allWebAccess = $this->modx->db->makeArray($this->modx->db->query($allWebAccessQuery));

        if (empty($allWebAccess))
            return FALSE;

        foreach ($allWebAccess as $k => $v) {
            $allWebAccess[$k] = $v['id'];
        }

        /**
         * Filtering the logged in member resources
         */
        if (empty($_SESSION['webUserGroupNames'])) {
            $allWebAccessString = @implode(',', $allWebAccess);
            return $allWebAccessString;
        }

        $webUserGroupNames = $_SESSION['webUserGroupNames'];

        foreach ($webUserGroupNames as $groupName) {
            $webUserGroupIdQuery = 'SELECT id FROM ' . $this->modx->db->config['table_prefix'] . 'webgroup_names '
                    . 'WHERE name=\'' . $groupName . '\'';
            $webUserGroupId = '';
            $webUserGroupId = $this->modx->db->getValue($this->modx->db->query($webUserGroupIdQuery));
            if (empty($webUserGroupId))
                continue;

            $userWebAccessQuery = 'SELECT DISTINCT id FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_webgroup_access WHERE '
                    . 'webgroup_id=\'' . $webUserGroupId . '\' '
                    . 'AND type=\'' . $type . '\' ';

            $userWebAccess = array();
            $userWebAccess = $this->modx->db->makeArray($this->modx->db->query($userWebAccessQuery));
        }

        foreach ($userWebAccess as $k => $v) {
            $userWebAccess[$k] = $v['id'];
        }

        /**
         * Get the difference
         */
        $checkWebAccess = array_diff($allWebAccess, $userWebAccess);
        if (empty($checkWebAccess)) {
            return FALSE;
        }
        $excludeWebAccessString = @implode(',', $checkWebAccess);
        return $excludeWebAccessString;
    }

    /**
     * To get paths from the parent directory up to the Easy 2's ROOT gallery
     * @param int       $dirId      parent directory's ID
     * @param string    $option     output options: cat_name | cat_alias
     * @param mixed     $format     output formats: string | array
     * @return string
     */
    public function getPath($dirId, $option='cat_name', $format='string') {
        $selectDir = 'SELECT A.parent_id, A.cat_id,A.cat_name,A.cat_alias '
                . 'FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_dirs A, '
                . $this->modx->db->config['table_prefix'] . 'easy2_dirs B '
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
        while ($l = mysql_fetch_assoc($queryDir)) {
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
    public function cropName($mbstring, $charSet, $nameLen, $text) {
        if (empty($charSet) || empty($nameLen))
            return FALSE;

        $croppedName = $text;
        if (trim(htmlspecialchars_decode($text)) == '') {
            $croppedName = '&nbsp;';
        } elseif ($mbstring) {
            if (mb_strlen($text, $charSet) > (int) $nameLen)
                $croppedName = mb_substr($text, 0, (int) $nameLen - 1, $charSet) . '...';
        } elseif (strlen($text) > (int) $nameLen) {
            $croppedName = substr($text, 0, (int) $nameLen - 1) . '...';
        }
        return $croppedName;
    }

    /**
     * Check if the given IP is ignored
     * @param string    $ip     IP Address
     * @return bool     TRUE if it is ignored | FALSE if it is not.
     */
    public function checkIgnoredIp() {
        // getting the real ip address
        $ip = empty($_SERVER['HTTP_CLIENT_IP']) ?
                (empty($_SERVER['HTTP_X_FORWARDED_FOR']) ?
                        $_SERVER['REMOTE_ADDR'] :
                        $_SERVER['HTTP_X_FORWARDED_FOR']) :
                $_SERVER['HTTP_CLIENT_IP'];

        $ip = $this->sanitizedString($ip);

        if (empty($ip)) {
            return FALSE;
        }

        $selectCountIgnIps = 'SELECT COUNT(ign_ip_address) '
                . 'FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_ignoredip '
                . 'WHERE ign_ip_address=\'' . $ip . '\'';
        $querySelectCountIgnIp = mysql_query($selectCountIgnIps);
        if (!$querySelectCountIgnIp) {
            echo __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectCountIgnIps . '<br />';
            return FALSE;
        }
        $resultCountIgnIps = mysql_result($querySelectCountIgnIp, 0, 0);
        mysql_free_result($querySelectCountIgnIp);

        if ($resultCountIgnIps > 0) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Loading language file with the default text switcher if the specifed value is empty
     * @param   string  $modPath            module's path. The default is '../' for ajax controller files.
     * @param   string  $managerLanguage    the manager's language
     * @return  array   the lexicon strings
     */
    public static function languageSwitch($managerLanguage, $modPath = null) {
        $modPath = isset($modPath) ? $modPath : '../';

        $langFile = realpath($modPath . 'includes/langs/' . $managerLanguage . '.inc.php');
        $engLangFile = realpath($modPath . 'includes/langs/english.inc.php');

        if (empty($engLangFile) || !file_exists($engLangFile)) {
            throw new Exception (__FILE__ . ', ' . __LINE__ . ': missing english language file: ' . $modPath . 'includes/langs/english.inc.php');
        }

        if (!empty($langFile) && file_exists($langFile)) {
            include $langFile; // loading $e2g_lang
            // if there is a blank language parameter, english will fill it as the default.
            $oldLangKey = $oldLangVal = array();
            foreach ($e2g_lang[$managerLanguage] as $olk => $olv) {
                $oldLangKey[$olk] = $olk; // other languages
                $oldLangVal[$olk] = $olv;
            }

            include $engLangFile; // loading $e2g_lang
            foreach ($e2g_lang['english'] as $enk => $env) {
                if (!isset($oldLangKey[$enk])) {
                    $e2g_lang[$managerLanguage][$enk] = $env;
                }
            }

            $lng = $e2g_lang[$managerLanguage];
        } else {
            include $engLangFile;
            $lng = $e2g_lang['english'];
        }

        return $lng;
    }

    /**
     * To make an Unauthorized page to avoid direct access to the folder
     * @param   string  $dir    path
     * @param   string  $text   language string
     * @return  mixed file creation
     */
    public function createIndexHtml($dir, $text = '') {
        $indexHtml = realpath($dir) . DIRECTORY_SEPARATOR . 'index.html';
        if (is_dir($dir) && !file_exists($indexHtml)) {
            $fh = fopen($indexHtml, 'w');
            if (!$fh)
                $_SESSION['easy2err'][] = __LINE__ . " : Could not open file " . $indexHtml;
            else {
                fwrite($fh, htmlspecialchars_decode($text));
                fclose($fh);
                $this->changeModOwnGrp('file', $indexHtml);
            }
        }
    }

    /**
     * Change chmod and chown
     * @param string    $type           dir/file
     * @param string    $fullPath       dir/file path
     * @param bool      $changeMode     TRUE | FALSE to initiate chmod
     * @param bool      $changeGroup    TRUE | FALSE to initiate chown
     * @return bool     TRUE | FALSE
     */
    public function changeModOwnGrp($type, $fullPath, $checkPreviousMode = TRUE, $changeGroup = TRUE) {
        if (!$this->e2gPubCfg['chmod_enabled']) {
            return FALSE;
        }

        $fullRealPath = realpath($fullPath);
        if (empty($fullRealPath)) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['chmod_err'] . ' fullPath = ' . $fullPath;
            return FALSE;
        }
        if ($checkPreviousMode) {
            $oldPermission = substr(sprintf('%o', fileperms($fullRealPath)), -4);
            clearstatcache();
        }

        $newFolderPerm = sprintf("%04o", octdec($this->e2gPubCfg['chmod_folder']));
        if ($type == 'dir' && $oldPermission != $newFolderPerm) {
            $newPermission = @chmod($fullRealPath, $newFolderPerm);
            clearstatcache();
            if (!$newPermission && $this->e2gPubCfg['e2g_debug'] == '1') {
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['chmod_err'] . ' fullPath = ' . $fullPath;
                $_SESSION['easy2err'][] = __LINE__ . ' : oldPermission = ' . $oldPermission;
                return FALSE;
            }
        }

        $newFilePrem = sprintf("%04o", octdec($this->e2gPubCfg['chmod_file']));
        if ($type == 'file' && $oldPermission != $newFilePrem) {
            $newPermission = @chmod($fullRealPath, $newFilePrem);
            clearstatcache();
            if ($checkPreviousMode === TRUE
                    && !$newPermission
                    && $this->e2gPubCfg['e2g_debug'] == '1'
            ) {
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['chmod_err'] . ' fullPath = ' . $fullPath;
                $_SESSION['easy2err'][] = __LINE__ . ' : oldPermission = ' . $oldPermission;
                return FALSE;
            }
        }

        if ($changeGroup === TRUE) {
            if (file_exists(realpath("index.php"))) {
                $modxPath = "index.php";
            } elseif (file_exists(realpath("../../../../../manager/index.php"))) {
                $modxPath = realpath("../../../../../manager/index.php");
            } else {
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['chown_err'] . ' manager/index.php was not detected';
                return FALSE;
            }
            $modxStat = stat($modxPath);
            clearstatcache();
            $ownerCore = $modxStat['uid'];
            $groupCore = $modxStat['gid'];
            $oldFullPath = $fullRealPath;
            $oldStat = stat($oldFullPath);
            if (!$oldStat) {
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['chown_err'] . ' stat error:' . $oldStat;
                return FALSE;
            }
            clearstatcache();
            $ownerOld = $oldStat['uid'];
            $groupOld = $oldStat['gid'];

            if (!function_exists('chown')) {
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['chown_err'] . ' ' . $this->lng['chown_err_disabled'];
                return FALSE;
            }

            if ($ownerOld != $ownerCore || $groupOld != $groupCore) {
                // Set the user
                $newOwner = @chown($fullRealPath, $ownerCore);
                clearstatcache();
                if (!$newOwner && $this->e2gPubCfg['e2g_debug'] == '1') {
                    $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['chown_err'] . ' fullPath = ' . $fullPath;
                    $_SESSION['easy2err'][] = __LINE__ . ' : old Owner/Group = ' . $ownerOld . '/' . $groupOld;
                    return FALSE;
                }
            }
        }

        return TRUE;
    }

}