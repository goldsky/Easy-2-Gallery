<?php
//set_ini('display_errors', '1');
/**
 * EASY 2 GALLERY
 * Gallery Snippet Class for Easy 2 Gallery Module for MODx Evolution
 * @author Cx2 <inteldesign@mail.ru>
 * @author Temus
 * @author goldsky <goldsky@modx-id.com>
 * @version 1.3.6
 */

class e2g_snip {
    public $cl_cfg = array();
    private $_e2g = array();

    public function  __construct($cl_cfg) {
        $this->cl_cfg = $cl_cfg;
        $this->_e2g = $_e2g;
    }

    public function gallery() {
        /*
         * $type:
         * 1. 'gallery' : full gallery directory (directory - &gid - default)
         * 2. 'img' : one file only (file - $fid)
         * 3. 'random' : random file in a directory (random - $rgid)
         * 4. 'imggal' : images inside a directory, excluding the sub-directories
         * 5. 'dirgal' : sub-directories inside a directory, excluding the images
        */

        $fid = (int) $this->cl_cfg['fid'];
        $rgid = (int) $this->cl_cfg['rgid'];
        $gid = (int) $this->cl_cfg['gid']; // default
        $notables = $this->cl_cfg['notables'];

        if ($fid) {
            return $this->_imagefile();
        }
        elseif ($rgid) {
            return $this->_randomimage();
        }
        elseif (!$fid) {
            return $this->_gallery(); // default
        }
    }

    /*
     * full gallery execution
    */
    private function _gallery() {
        require E2G_SNIPPET_PATH.'config.easy2gallery.php';
        global $modx;

        $gid = $this->cl_cfg['gid'];
        $cat_orderby = $this->cl_cfg['cat_orderby'];
        $cat_order = $this->cl_cfg['cat_order'];
        $gpn = $this->cl_cfg['gpn'];
        $limit = $this->cl_cfg['limit'];
        $charset = $this->cl_cfg['charset'];
        $cat_name_len = $this->cl_cfg['cat_name_len'];
        $notables = $this->cl_cfg['notables'];
        $colls = $this->cl_cfg['colls'];
        $orderby = $this->cl_cfg['orderby'];
        $order = $this->cl_cfg['order'];

        // CRUMBS
        $crumbs_separator = $this->cl_cfg['crumbs_separator'];
        $crumbs_showHome = $this->cl_cfg['crumbs_showHome'];
        $crumbs_showAsLinks = $this->cl_cfg['crumbs_showAsLinks'];
        $crumbs_showCurrent = $this->cl_cfg['crumbs_showCurrent'];

        /*
         * PATHS
        */
        // NOT the $e2g config
        $_e2g = array('content'=>'','pages'=>'','parent_id'=>0,'back'=>'');
        // get path from the $_GET['gid']
        $path = $this->_get_path($gid);
        // get "category name" from $path
        $_e2g['cat_name'] = is_array($path) ? end($path) : '';
        // reset crumbs
        $crumbs='';
        // if path more the one
        if (count($path) > 1) {
            end($path);
            prev($path);
            $_e2g['parent_id'] = key($path);
            $_e2g['parent_name'] = $path[$_e2g['parent_id']];

            // create crumbs
            $cnt=0;
            foreach ($path as $k=>$v) {
                $cnt++;
                if ($cnt==1 && !$crumbs_showHome) {
                    continue;
                }
                if ($cnt==count($path) && !$crumbs_showCurrent) {
                    continue;
                }
                if ($cnt!=count($path)) $crumbs .= $crumbs_separator.($crumbs_showAsLinks ? '<a href="[~[*id*]~]&gid='.$k.'">'.$v.'</a>' : $v);
                else $crumbs .= $crumbs_separator.'<span class="e2g_currentCrumb">'.$v.'</span>';
            }
            $crumbs = substr_replace($crumbs,'',0,strlen($crumbs_separator));

            // unset Easy 2-$path value
            unset($path[1]);

            // joining many of directory paths
            $path = implode('/', array_values($path)).'/';
        } else { // if not many, path is set as empty
            $path = '';
        }

        // SUBDIRS & THUMBS FOR SUBDIRS
        $query = 'SELECT A.* '
                . 'FROM '.$modx->db->config['table_prefix'].'easy2_dirs A, '
                . $modx->db->config['table_prefix'].'easy2_dirs B '
                . 'WHERE B.cat_id=' . $gid . ' '
                . 'AND A.cat_left >= B.cat_left '
                . 'AND A.cat_right <= B.cat_right '
                . 'AND A.cat_level = B.cat_level + 1 '
                . 'AND A.cat_visible = 1 '
                . 'GROUP BY A.cat_id '
                . 'ORDER BY A.' . $cat_orderby . ' ' . $cat_order . ' '
                . 'LIMIT ' . ( $gpn * $limit ) . ', ' . $limit; // goldsky -- limit the subdirs per page

        $dirquery = mysql_query($query);
        if (!$dirquery) die('130 '.mysql_error());
        $dir_num_rows = mysql_num_rows($dirquery);

        // START the grid
        $_e2g['content'] = $dir_num_rows ? ( $notables ? '<div class="e2g">':'<table class="e2g"><tr>') : '';

        $this->_libs();

        $i = 0;
        while ($l = mysql_fetch_array($dirquery, MYSQL_ASSOC)) {

            // search image for subdir
            $l1=$this->_get_folder_img($l['cat_id']);
            if (!$l1) continue;
            $l['count'] = $l1['count'];

            // path to subdir's thumbnail
            $path1=$this->_get_path($l1['dir_id']);

            // if path is more than one
            if (count($path1) > 1) {
                unset($path1[1]); // unset the Easy 2 base path only
                $path1 = implode('/', array_values($path1)).'/';
            }
            // if path is not many
            else {
                $path1 = '';
            }

            // Populate the grid with Directories' thumb
            if ( ( $i > 0 ) && ( $i % $colls == 0 ) && !$notables ) $_e2g['content'] .= '</tr><tr>';

            $l['title'] = $l['cat_name'];
            if ($l['cat_name'] == '') $l['cat_name'] = '&nbsp;';
            elseif ($mbstring) {
                if (mb_strlen($l['cat_name'], $charset ) > $cat_name_len ) $l['cat_name'] = mb_substr($l['cat_name'], 0, $cat_name_len-1, $charset).'...';
            }
            elseif (strlen($l['cat_name']) > $cat_name_len) $l['cat_name'] = substr($l['cat_name'], 0, $cat_name_len-1).'...';

            $w = $l['w'] = $this->cl_cfg['w'];
            $h = $l['h'] = $this->cl_cfg['h'];
            $thq = $this->cl_cfg['thq'];

            $l['src'] = $this->_get_thumb($e2g['dir'], $path1.$l1['filename'], $w, $h, $thq );

            // fill up the dir list with content
            $_e2g['content'] .= $notables ? $this->_filler($this->_dir_tpl(), $l) : '<td>'. $this->_filler($this->_dir_tpl(), $l ).'</td>';
            $i++;
        } // while ($l = mysql_fetch_array($dirquery, MYSQL_ASSOC))

        /*
         * FILE thumbs for current dir
        */
        if( $dir_num_rows!=$limit ) {

            /*
             * goldsky -- manage the pagination limit between dirs and files
             * (join the pagination AND the table grid).
            */
            // count the directories again, this time WITHOUT limit!
            $dir_count = mysql_result(mysql_query('SELECT COUNT(cat_id) FROM ' . $modx->db->config['table_prefix'].'easy2_dirs WHERE parent_id='.$gid), 0 ,0);
            $modulus_dir_count = $dir_count%$limit;
            $file_thumb_offset = $limit-$modulus_dir_count;
            $file_page_offset = ceil($dir_count/$limit);

            $filequery = 'SELECT * FROM '.$modx->db->config['table_prefix'].'easy2_files '
                    . 'WHERE dir_id='.$gid.' '
                    . 'AND status = 1 '
                    . 'ORDER BY ' . $orderby . ' ' . $order . ' '
            ;

            if ( $file_thumb_offset > 0 && $file_thumb_offset < $limit ) {
                $filequery .= 'LIMIT
                ' . ( $dir_num_rows > 0 ?
                        ( ' 0, ' . ( $file_thumb_offset ) ) :
                        ( ( ( $gpn - $file_page_offset) * $limit) + $file_thumb_offset ) . ', ' . $limit );
            }
            elseif ( $file_thumb_offset != 0 || $file_thumb_offset == $limit ) {
                $filequery .= 'LIMIT
                ' . ( $modulus_dir_count > 0 ?
                        ( ' 0, ' . ( $file_thumb_offset ) ) :
                        ( ( ( $gpn - $file_page_offset) * $limit) ) . ', ' . $limit );
            }
            else { // $file_thumb_offset == 0 --> No sub directory
                $filequery .= 'LIMIT ' . ( $gpn * $limit) . ', ' . $limit ;
            }

            $file_query_result = mysql_query($filequery);

            $file_num_rows = mysql_num_rows($file_query_result);

            /*
             * goldsky -- to merge the file's thumb starter positions between dirs and files
            */
            // goldsky -- if no dir list
            if ( $dir_num_rows==0 || $dir_num_rows==$limit ) {
                $_e2g['content'] .= $file_num_rows ? ($notables ? '<div class="e2g">':'<table class="e2g"><tr>') : '';
            }
            // goldsky -- if starts from a new row
            elseif ( $file_thumb_offset%$colls==0 ) {
                $_e2g['content'] .= $file_num_rows ? ($notables ? '':'<tr>') : '';
            }
            // goldsky -- if JOINING the same row with the last result of the dir list
            else {
                $_e2g['content'] .= $file_num_rows ? ($notables ? '':'') : '';
            }
            
            /*
             * retrieve the content
            */
            $i = 0;
            while ($l = mysql_fetch_array($file_query_result, MYSQL_ASSOC)) {
                // whether configuration setting is set with or without table, the template will adjust it
                /*
                * goldsky -- this is where the file's thumb 'float' to the dirs' in TABLE grid
                */
                if ( ( $i > 0 )
                        && ( abs( $i - ( $dir_num_rows == 0 ? 0 : $file_thumb_offset ) ) % $colls == 0 )
                        && !$notables ) {
                    $_e2g['content'] .= '</tr><tr>';
                }

                // whether configuration setting is set with or without table, the template will adjust it
                $_e2g['content'] .= $notables ?  $this->_filler( $this->_thumb_tpl(), $this->_activate_libs($l) ) : '<td>'. $this->_filler( $this->_thumb_tpl(), $this->_activate_libs($l) ).'</td>';
                $i++;
            } // while ($l = @mysql_fetch_array($file_query_result, MYSQL_ASSOC))
        } // if( $dir_num_rows!=$limit )

        $_e2g['content'] .= $notables ? '</div>' : '</tr></table>';

        /*
         * BACK BUTTON
        */
        if ($_e2g['parent_id'] > 0) {
            $_e2g['back'] = '<p class="e2gback">&laquo; <a href="[~[*id*]~]&gid='.$_e2g['parent_id'].'">'.$_e2g['parent_name'].'</a></p>';
        }

        /*
         * CRUMBS
        */
        $_e2g['crumbs']=$crumbs;

        /*
        *  PAGES LINKS - joining between dirs and files pagination
        */
        $dir_count = mysql_result(mysql_query('SELECT COUNT(cat_id) FROM ' . $modx->db->config['table_prefix'].'easy2_dirs WHERE parent_id='.$gid), 0 ,0);
        $file_count = mysql_result(mysql_query('SELECT COUNT(id) FROM '.$modx->db->config['table_prefix'].'easy2_files WHERE dir_id='.$gid), 0, 0);

        $total_count = $dir_count+$file_count;

        if ($total_count > $limit) {
            $_e2g['pages'] = '<div class="e2gpnums">';
            $i = 0;
            while ($i*$limit < $total_count) {
                if ($i == $gpn) $_e2g['pages'] .= '<b>'.($i+1).'</b> ';
                else $_e2g['pages'] .= '<a href="[~[*id*]~]&gid='.$gid.'&gpn='.$i.'">'.($i+1).'</a> ';
                $i++;
            }
            $_e2g['pages'] .= '</div>';
        }
        return $this->_filler($this->_gal_tpl(), $_e2g);
    }

    /*
     * $fid is set
    */
    private function _imagefile() {
        require E2G_SNIPPET_PATH.'config.easy2gallery.php';
        global $modx;
        $fid = $this->cl_cfg['fid'];

        $filequery = 'SELECT * FROM '.$modx->db->config['table_prefix'].'easy2_files '
                . 'WHERE id='.$fid.' '
                . 'AND status = 1 ';
        $res = mysql_query($filequery) or die('299 '.mysql_error());

        // START the grid
        $_e2g['content'] .= $notables ? '<div class="e2g">':'<table class="e2g"><tr>';

        $l = mysql_fetch_array($res, MYSQL_ASSOC);

        $this->_libs();
        $this->_activate_libs($l);

        $_e2g['content'] .= $notables ? $this->_filler($this->_thumb_tpl(), $this->_activate_libs($l)) : '<td>'.$this->_filler($this->_thumb_tpl(), $this->_activate_libs($l)).'</td>';

        // END the grid
        $_e2g['content'] .= $notables ? '</div>':'</tr></table>';
        return $this->_filler($this->_gal_tpl(), $_e2g);
    }


    /*
     * RANDOM IMAGE
     * To create a random image
     * @param string $orderby == 'random'
     * @param int $limit == 1
    */
    private function _randomimage() {
        require E2G_SNIPPET_PATH.'config.easy2gallery.php';
        global $modx;
        $orderby = $this->cl_cfg['orderby'];
        $limit = $this->cl_cfg['limit'];
        $rgid = $this->cl_cfg['rgid'];

        $q = 'SELECT * FROM '.$modx->db->config['table_prefix'].'easy2_files '
                . 'WHERE status = 1 '
                . 'AND dir_id='. $rgid .' '
                . 'ORDER BY RAND() LIMIT 1'
        ;
        $res = mysql_query($q);
        $num_rows = mysql_num_rows($res);
        if (!$num_rows) return;

        // START the grid
        $_e2g['content'] .= $notables ? '<div class="e2g">':'<table class="e2g"><tr>';
        $l = mysql_fetch_array($res, MYSQL_ASSOC);

        $this->_libs();
        $this->_activate_libs($l);

        $_e2g['content'] .= $notables ? $this->_filler($this->_random_tpl(), $this->_activate_libs($l)) : '<td>'.$this->_filler($this->_random_tpl(), $this->_activate_libs($l)).'</td>';

        // END the grid
        $_e2g['content'] .= $notables ? '</div>':'</tr></table>';
        return $this->_filler($this->_gal_tpl(), $_e2g );
    }

    /*
     * function get_thumb
     * function to get and create thumbnails
     * @param int $gdir = from $_GET['gid']
     * @param string $path = directory path of each of thumbnail
     * @param int $w = thumbnail width
     * @param int $h = thumbnail height
     * @param int $thq = thumbnail quality
     * @param string $resize_type = 'inner' | 'resize'
     *          'inner' = crop the thumbnail
     *          'resize' = autofit the thumbnail
     *
    */
    private function _get_thumb ($gdir, $path, $w = 150, $h = 150, $thq=80, $resize_type = 'inner') {
        global $modx;

//        $w = $this->cl_cfg['w'];
//        $h = $this->cl_cfg['h'];

        if (empty($path)) return false;

        $thumb_path = '_thumbnails/'.substr($path, 0, strrpos($path, '.')).'_'.$w.'x'.$h.'.jpg';

        /*
         * CREATE THUMBNAIL
        */
        // goldsky -- alter the maximum execution time
        //        set_time_limit(0);

        if (!file_exists($gdir.$thumb_path) && file_exists($gdir.$path)) {
            // goldsky -- adds output buffer to avoid PHP's memory limit
            //            ob_start();

            $i = getimagesize($gdir.$path);
            if ($i[2] == 1) $im = imagecreatefromgif ($gdir.$path);
            elseif ($i[2] == 2) $im = imagecreatefromjpeg ($gdir.$path);
            elseif ($i[2] == 3) $im = imagecreatefrompng ($gdir.$path);
            else return false;

            if ($i[0]/$w > 2 || $i[1]/$h > 2) {
                $tmp_w = $w*2;
                $tmp_h = round($i[1] * ($tmp_w/$i[0]));

                $temp = imagecreatetruecolor ($tmp_w, $tmp_h);
                imagecopyresized ($temp, $im, 0, 0, 0, 0, $tmp_w, $tmp_h, $i[0], $i[1]);

                $i[0] = $tmp_w;
                $i[1] = $tmp_h;

                imagedestroy($im);
                $im = $temp;
            }

            /*
             * $resize_type == 'inner'
             * trim to default dimensions
            */
            if ($resize_type == 'inner') {
                // Shifts
                $x = $y = 0;

                // Dimensions
                $w2 = $w;
                $h2 = $h;

                if ($i[0] > $i[1]) {
                    $w2 = round($i[0] * $h / $i[1]);
                    if ($w2 > $w) $x = ($w2 - $w)/2 * (-1);
                } else {
                    $h2 = round($i[1] * $w / $i[0]);
                    if ($h2 > $h) $y = ($h2 - $h)/2 * (-1);
                }

                $pic = imagecreatetruecolor($w, $h);
                $bgc = imagecolorallocate($pic, 255, 255, 255);
                imagefill($pic, 0, 0, $bgc);
                imagecopyresampled($pic, $im, $x, $y, 0, 0, $w2, $h2, $i[0], $i[1]);

            } else {
                /*
                 * $resize_type == 'resize'
                 * proportionally reduce to default dimensions
                */

                if ($i[0] > $i[1]) $h = round($i[1] * $w / $i[0]);
                else $w = round($i[0] * $h / $i[1]);

                $pic = imagecreatetruecolor($w, $h);
                $bgc = imagecolorallocate($pic, 255, 255, 255);
                imagefill($pic, 0, 0, $bgc);
                imagecopyresampled($pic, $im, 0, 0, 0, 0, $w, $h, $i[0], $i[1]);
            }

            /*
             * make directory of thumbnails
            */

            $dirs = explode('/', $path);
            $npath = $gdir.'_thumbnails';
            for ($c = 0; $c < count($dirs) - 1; $c++) {
                $npath .= '/'.$dirs[$c];
                if (is_dir($npath)) continue;
                if (!mkdir($npath)) return false;
                @chmod($npath, 0755);
            }

            /*
             * create the thumbnails
            */
            imagejpeg($pic, $gdir.$thumb_path, $thq);
            @chmod($gdir.$thumb_path, 0644);

            /*
             * image cache destroy
            */
            imagedestroy($pic);
            imagedestroy($im);

            // goldsky -- adds output buffer to avoid PHP's memory limit
            //            ob_end_clean();
        }

        /*
         * returned as thumbnail's path
        */
        return $gdir.$thumb_path;
    }

    /*
     * function get_path
     * function to get image's path
     * @param int $id = image's ID
    */

    private function _get_path ($id) {
        global $modx;

        $result = array();

        $q = '
            SELECT A.cat_id,
            A.cat_name FROM '
                .$modx->db->config['table_prefix']
                .'easy2_dirs A, '
                .$modx->db->config['table_prefix']
                .'easy2_dirs B
                    WHERE B.cat_id='.$id.
                ' AND B.cat_left
                    BETWEEN A.cat_left
                    AND A.cat_right
                    ORDER BY A.cat_left';

        $res = mysql_query($q);
        while ($l = mysql_fetch_row($res)) {
            $result[$l[0]] = $l[1];
        }
        if (empty($result)) return null;
        return $result;
    }

    /*
     * function filler
     * Gallery's TEMPLATE function
     * @param string $tpl = gallery's template (@FILE or chunk)
     * @param string $data = template's array data
     * $param string $prefix = placeholder's prefix
     * $param string $suffix = placeholder's suffix
    */

    private function _filler ($tpl, $data, $prefix = '[+easy2:', $suffix = '+]') {
        foreach($data as $k => $v) {
            $tpl = str_replace($prefix.(string)$k.$suffix, (string)$v, $tpl);
        }
        /*
         * returned as image's template
        */
        return $tpl;
    }

    /*
     * function get_folder_img
     * To get thumbnail for each folder
     * @param int $gid folder's ID
    */
    private function _get_folder_img ($gid) {
        global $modx;

//        $res = mysql_query('
//                    SELECT DISTINCT F.* FROM '
//                . $modx->db->config['table_prefix'].'easy2_dirs A, '
//                . $modx->db->config['table_prefix'].'easy2_dirs B, '
//                . $modx->db->config['table_prefix'].'easy2_files F '
//                . 'WHERE (
//                    B.cat_id='.$gid
//                .' AND A.cat_left >= B.cat_left
//                    AND A.cat_right <= B.cat_right
//                    AND A.cat_level > B.cat_level
//                    AND A.cat_visible = 1
//                    AND F.dir_id = A.cat_id
//                            )
//                    OR F.dir_id = '.$gid
//                .' ORDER BY A.cat_level ASC, F.id DESC
//                    ');
        // http://modxcms.com/forums/index.php/topic,23177.msg273448.html#msg273448
        $res = mysql_query('
        SELECT F.* FROM '
                . $modx->db->config['table_prefix'].'easy2_files F
                    WHERE F.dir_id in (SELECT A.cat_id FROM '
                . $modx->db->config['table_prefix'].'easy2_dirs A, '
                . $modx->db->config['table_prefix'].'easy2_dirs B
                    WHERE (B.cat_id=' . $gid
                . ' AND A.cat_left >= B.cat_left '
                . ' AND A.cat_right <= B.cat_right '
                . ' AND A.cat_level >= B.cat_level '
                . ' AND A.cat_visible = 1)
                    ORDER BY A.cat_level ASC )
                    ORDER BY F.id DESC
        ');
        $result = mysql_fetch_array($res, MYSQL_ASSOC);
        if ($result) $result['count'] = mysql_num_rows($res);
        mysql_free_result($res);

        /*
         * returned as folder's thumbnail's info array
        */
        return $result;
    }

    private function _libs() {
        global $modx;
        $css = $this->cl_cfg['css'];
        $glib = $this->cl_cfg['glib'];

        // CSS STYLES
        if (file_exists($css)) {
            $libsout .= $modx->regClientCSS($modx->config['base_url'].$css,'screen');
        }

        if ($glib == 'fancybox') {
            $libsout .= $modx->regClientCSS($modx->config['base_url'].'assets/libs/fancybox/fancybox.css','screen');
            $libsout .= $modx->regClientStartupScript('http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js');
            $libsout .= $modx->regClientStartupScript($modx->config['base_url'].'assets/libs/fancybox/fancybox.js');
            return $libsout;
        }
        if ($glib == 'floatbox') {
            $libsout .= $modx->regClientCSS($modx->config['base_url'].'assets/libs/floatbox/floatbox.css','screen');
            $libsout .= $modx->regClientStartupScript($modx->config['base_url'].'assets/libs/floatbox/floatbox.js');
            return $libsout;
        }
        if ($glib == 'highslide') {
            $libsout .= $modx->regClientCSS($modx->config['base_url'].'assets/libs/highslide/highslide.css','screen');
            $libsout .= $modx->regClientStartupScript($modx->config['base_url'].'assets/libs/highslide/highslide.js');
            $libsout .= $modx->regClientStartupScript($modx->config['base_url'].'assets/libs/highslide/highslide-settings.js');
            return $libsout;
        }
        if ($glib == 'shadowbox') {
            $libsout .= $modx->regClientCSS($modx->config['base_url'].'assets/libs/shadowbox/shadowbox.css','screen');
            $libsout .= $modx->regClientStartupScript($modx->config['base_url'].'assets/libs/shadowbox/shadowbox.js');
            $libsout .= $modx->regClientStartupScript($modx->config['base_url'].'assets/libs/shadowbox/shadowbox-settings.js');
            return $libsout;
        }
        if ($glib == 'slimbox') {
            $libsout .= $modx->regClientCSS($modx->config['base_url'].'assets/libs/slimbox/css/slimbox.css','screen');
            $libsout .= $modx->regClientStartupScript('http://ajax.googleapis.com/ajax/libs/mootools/1.2.2/mootools-yui-compressed.js');
            $libsout .= $modx->regClientStartupScript($modx->config['base_url'].'assets/libs/slimbox/js/slimbox.js');
            return $libsout;
        }
        if ($glib == 'slimbox2') {
            $libsout .= $modx->regClientCSS($modx->config['base_url'].'assets/libs/slimbox/css/slimbox.css','screen');
            $libsout .= $modx->regClientStartupScript('http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js');
            $libsout .= $modx->regClientStartupScript($modx->config['base_url'].'assets/libs/slimbox/js/slimbox2.js');
            return $libsout;
        }
        if ($glib == 'lightwindow') {
            $libsout .= $modx->regClientCSS($modx->config['base_url'].'assets/libs/lightwindow/css/lightwindow.css','screen');
            $libsout .= $modx->regClientStartupScript('http://ajax.googleapis.com/ajax/libs/prototype/1.6.0.3/prototype.js');
            $libsout .= $modx->regClientStartupScript('http://ajax.googleapis.com/ajax/libs/scriptaculous/1.8.2/scriptaculous.js?load=effects');
            $libsout .= $modx->regClientStartupScript($modx->config['base_url'].'assets/libs/lightwindow/js/lightwindow.js');
            return $libsout;
        }
        if ($glib == 'colorbox') {
            $libsout .= $modx->regClientCSS($modx->config['base_url'].'assets/libs/colorbox/colorbox.css','screen');
            $libsout .= $modx->regClientStartupScript('http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js');
            $libsout .= $modx->regClientStartupScript($modx->config['base_url'].'assets/libs/colorbox/jquery.colorbox-min.js');
            return $libsout;
        }
    }

    private function _activate_libs($row) {
        require E2G_SNIPPET_PATH.'config.easy2gallery.php';
        global $modx;
        $css = $this->cl_cfg['css'];
        $glib = $this->cl_cfg['glib'];

        $row['title'] = $row['name'];
        if ($row['name'] == '') $row['name'] = '&nbsp;';
        elseif ($mbstring) {
            if (mb_strlen($row['name'], $charset) > $name_len) $row['name'] = mb_substr($row['name'], 0, $name_len-1, $charset).'...';
        }
        elseif (strlen($row['name']) > $name_len) $row['name'] = substr($row['name'], 0, $name_len-1).'...';

        $path = $this->_get_path($row['dir_id']);
        if (count($path) > 1) {
            unset($path[1]);
            $path = implode('/', array_values($path)).'/';
        } else {
            $path = '';
        }

        $w = $row['w'] = $this->cl_cfg['w'];
        $h = $row['h'] = $this->cl_cfg['h'];
        $thq = $this->cl_cfg['thq'];

        $row['src'] = $this->_get_thumb($e2g['dir'], $path.$row['filename'], $w, $h, $thq);

//        $this->_libs();

        $show_group = $this->cl_cfg['show_group'];

        // gallery activation
        if ($glib == 'highslide') {
            $row['glibact']='class="highslide" onclick="return hs.expand(this, {slideshowGroup: \'mygroup\'})"';
        }
        elseif ($glib == 'slimbox' || $glib == 'slimbox2' || $glib == 'fancybox' || $glib == 'colorbox') {
            $row['glibact']='rel="lightbox['.$show_group.']"';
        }
        elseif ($glib == 'shadowbox') {
            $row['glibact']='rel="shadowbox['.$show_group.'];player=img"';
        }
        elseif ($glib == 'floatbox') {
            $row['glibact']='class="floatbox" rev="doSlideshow:true group:'.$show_group.'"';

        }
        elseif ($glib == 'lightwindow') {
            $row['glibact']='class="lightwindow" rel="Gallery['.$show_group.']" params="lightwindow_type=image"';
        }

        $ecm = $this->cl_cfg['ecm'];
        /*
         * COMMENTS
        */
        if ($ecm == 1) {
            $row['com'] = 'e2gcom'.($row['comments']==0?0:1);

            // iframe activation
            if ($glib == 'highslide') {
                $row['comments'] = '<a href="' . E2G_SNIPPET_URL . 'comments.easy2gallery.php?id='.$row['id'].'" onclick="return hs.htmlExpand(this, { objectType: \'iframe\'} )">'.$row['comments'].'</a>';
            }
            elseif ($glib == 'slimbox' || $glib == 'slimbox2') {
                $modx->regClientCSS($modx->config['base_url'].'assets/libs/highslide/highslide.css','screen');
                $modx->regClientStartupScript($modx->config['base_url'].'assets/libs/highslide/highslide-iframe.js');
                $row['comments'] = '<a href="' . E2G_SNIPPET_URL . 'comments.easy2gallery.php?id='.$row['id'].'" onclick="return hs.htmlExpand(this, { objectType: \'iframe\'} )">'.$row['comments'].'</a>';
            }
            elseif ($glib == 'fancybox' || $glib == 'colorbox') {
                $row['comments'] = '<a href="' . E2G_SNIPPET_URL . 'comments.easy2gallery.php?id='.$row['id'].'" class="iframe">'.$row['comments'].'</a>';
            }
            elseif ($glib == 'shadowbox') {
                $row['comments'] = '<a href="' . E2G_SNIPPET_URL . 'comments.easy2gallery.php?id='.$row['id'].'" rel="shadowbox;width=400;height=250;player=iframe">'.$row['comments'].'</a>';
            }
            elseif ($glib == 'floatbox') {
                $row['comments'] = '<a href="' . E2G_SNIPPET_URL . 'comments.easy2gallery.php?id='.$row['id'].'" rel="floatbox" rev="type:iframe width:400 height:250 enableDragResize:true controlPos:tr innerBorder:0">'.$row['comments'].'</a>';
            }
            elseif ($glib == 'lightwindow') {
                $row['comments'] = '<a href="' . E2G_SNIPPET_URL . 'comments.easy2gallery.php?id='.$row['id'].'" class="lightwindow" params="lightwindow_type=external,lightwindow_width=400,lightwindow_height=250">'.$row['comments'].'</a>';
            }

        } else {
            $row['comments'] = '';
            $row['com'] = 'not_display';
        }
        return $row;
    }

    // DIRECTORY TEMPLATE
    private function _dir_tpl() {
        global $modx;
        $dir_tpl = $this->cl_cfg['dir_tpl'];
        if (file_exists($dir_tpl)) {
            $tpl_dir = file_get_contents($dir_tpl);
            return $tpl_dir;
        } elseif (!empty($modx->chunkCache[$dir_tpl])) {
            $tpl_dir = $modx->chunkCache[$dir_tpl];
            return $tpl_dir;
        } else {
            echo 'Directory template '.$dir_tpl.' not found!';
        }
    }

    // THUMBNAIL TEMPLATE
    private function _thumb_tpl() {
        global $modx;
        $thumb_tpl = $this->cl_cfg['thumb_tpl'];
        if (file_exists($thumb_tpl)) {
            $tpl_thumb = file_get_contents($thumb_tpl);
            return $tpl_thumb;
        } elseif (!empty($modx->chunkCache[$thumb_tpl])) {
            $tpl_thumb = $modx->chunkCache[$thumb_tpl];
            return $tpl_thumb;
        } else {
            echo 'Thumbnail template not found!';
        }
    }

    // GALLERY TEMPLATE
    private function _gal_tpl() {
        global $modx;
        $tpl = $this->cl_cfg['tpl'];
        if (file_exists($tpl)) {
            $gal_tpl = file_get_contents($tpl);
            return $gal_tpl;
        } elseif (!empty($modx->chunkCache[$tpl])) {
            $gal_tpl = $modx->chunkCache[$tpl];
            return $gal_tpl;
        } else {
            echo 'Gallery template not found!';
        }
    }

    // RANDOM TEMPLATE
    private function _random_tpl() {
        global $modx;
        $rand_tpl = $this->cl_cfg['rand_tpl'];
        if (file_exists($rand_tpl)) {
            $rand_tpl = file_get_contents($rand_tpl);
            return $rand_tpl;
        } elseif (!empty($modx->chunkCache[$rand_tpl])) {
            $rand_tpl = $modx->chunkCache[$rand_tpl];
            return $rand_tpl;
        } else {
            echo 'Random template '.$rand_tpl.' not found!';
        }
    }
}
?>