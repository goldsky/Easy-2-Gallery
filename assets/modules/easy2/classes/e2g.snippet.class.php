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
require_once E2G_SNIPPET_PATH . 'includes/utf8/utf8.php';

class e2g_snip {
    public $cl_cfg = array();
    private $_e2g = array();

    public function  __construct($cl_cfg) {
        $this->cl_cfg = $cl_cfg;
        $this->_e2g = $_e2g;
    }

    public function display($cl_cfg) {
        /*
         * 1. '&gid' : full gallery directory (directory - &gid - default)
         * 2. '&fid' : one file only (file - $fid)
         * 3. '&rgid' : random file in a directory (random - $rgid)
         * 4. '&slideshow' : slideshow by fid-s or rgid-s or gid-s
        */
        global $modx;
        $fid = $this->cl_cfg['fid'];
        $rgid = $this->cl_cfg['rgid'];
        $gid = $this->cl_cfg['gid']; // default
        $slideshow = $this->cl_cfg['slideshow'];
        $landingpage = $this->cl_cfg['landingpage'];

        if ( !(isset($landingpage)) ) {
            if ( !empty($fid) && !isset($slideshow) ) {
                echo $this->_imagefile($cl_cfg);
            }
            if ( !empty($rgid) && !isset($slideshow) ) {
                echo $this->_randomimage($cl_cfg);
            }
            if ( empty($fid) && empty($rgid) && !isset($slideshow) ) {
                echo $this->_gallery($cl_cfg); // default
            }
        }
        if ( !empty($slideshow) ) {
            echo $this->_slideshow($cl_cfg);
        }
    }

    /*
     * full gallery execution
    */
    private function _gallery($cl_cfg) {
        global $modx;

        $gdir = $this->cl_cfg['gdir'];
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
        $showonly = $this->cl_cfg['showonly'];
        $customgetparams = $this->cl_cfg['customgetparams'];

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


        // START the grid
        $_e2g['content'] = $notables == 1 ? '<div class="e2g">':'<table class="e2g"><tr>' ;

        // count the directories WITHOUT limit!
        if ($showonly=='images' || !isset($gid)) {
            $dir_count = 0;
        } else {
            $dir_count = mysql_result(mysql_query(
                    'SELECT COUNT(DISTINCT d.cat_id) '
                    . 'FROM '.$modx->db->config['table_prefix'].'easy2_dirs AS d '
//                    . 'LEFT JOIN '.$modx->db->config['table_prefix'].'easy2_files AS f '
//                    . 'ON d.cat_id = f.dir_id '
                    . 'WHERE d.parent_id IN ('.$gid.') '
                    . 'AND d.cat_visible = 1 '
//                    . 'AND (SELECT COUNT(*) '
//                    . 'FROM '.$modx->db->config['table_prefix'].'easy2_files AS f '
//                    . 'WHERE f.dir_id = d.cat_id '
//                    . ')<>0 '
                    . 'AND (SELECT count(*) FROM '.$modx->db->config['table_prefix'].'easy2_files F '
                    . 'WHERE F.dir_id in '
                    . '(SELECT A.cat_id FROM '.$modx->db->config['table_prefix'].'easy2_dirs A, '
                    . $modx->db->config['table_prefix'].'easy2_dirs B '
                    . 'WHERE (B.cat_id=d.cat_id '
                    . 'AND A.cat_left >= B.cat_left '
                    . 'AND A.cat_right <= B.cat_right '
                    . 'AND A.cat_level >= B.cat_level '
                    . 'AND A.cat_visible = 1)'
                    .')'
                    .')<>0 '
                    ), 0 ,0);

            ########################################################################
            /*
             * Add the multiple IDs capability into the &gid
            */
            $multiple_gids = explode(',',$gid);
            $multiple_gids_count = count($multiple_gids);
            // reset the directory number
            $dir_num_rows = 0;

            foreach ($multiple_gids as $single_gid) {
                // get path from the $gid
                $path = $this->_get_path($single_gid);
                // get "category name" from $path
                $_e2g['cat_name'] = is_array($path) ? end($path) : '';
                // reset crumbs
                $crumbs='';

                /*
                 * Only use crumbs if it is a single gid.
                 * Otherwise, how can we make crumbs for merging directory in 1 page?
                */
                if ($multiple_gids_count==1) {
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
                }
            }

            $this->_libs($cl_cfg);

            if ( $showonly != 'images' ) {
                // SUBDIRS & THUMBS FOR SUBDIRS
                $query = 'SELECT DISTINCT d.* '
                        . 'FROM '.$modx->db->config['table_prefix'].'easy2_dirs AS d '
                        . 'WHERE d.parent_id = ' . $single_gid . ' '
                        . 'AND d.cat_visible = 1 '
                        // ddim -- http://modxcms.com/forums/index.php/topic,48314.msg286241.html#msg286241
                        . 'AND (SELECT count(*) FROM '.$modx->db->config['table_prefix'].'easy2_files F '
                        . 'WHERE F.dir_id in '
                        . '(SELECT A.cat_id FROM '.$modx->db->config['table_prefix'].'easy2_dirs A, '
                        . $modx->db->config['table_prefix'].'easy2_dirs B '
                        . 'WHERE (B.cat_id=d.cat_id '
                        . 'AND A.cat_left >= B.cat_left '
                        . 'AND A.cat_right <= B.cat_right '
                        . 'AND A.cat_level >= B.cat_level '
                        . 'AND A.cat_visible = 1)'
                        .')'
                        .')<>0 '
                        . 'ORDER BY ' . $cat_orderby . ' ' . $cat_order . ' '
                        . 'LIMIT ' . ( $gpn * $limit ) . ', ' . $limit // goldsky -- limit the subdirs per page
                ;

                $dirquery = mysql_query($query);
                if (!$dirquery) die('198 '.mysql_error());
                $dir_num_rows += mysql_num_rows($dirquery);

                $i = 0;
                while ($l = mysql_fetch_array($dirquery, MYSQL_ASSOC)) {

                    // search image for subdir
                    $l1=$this->_get_folder_img($cl_cfg, $l['cat_id']);
                    // if there is an empty folder, or invalid content
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

                    // Populate the grid with folder's thumbnails
                    if ( ( $i > 0 ) && ( $i % $colls == 0 ) && $notables == 0 ) $_e2g['content'] .= '</tr><tr>';

                    $l['title'] = ( $l['cat_alias'] != '' ? $l['cat_alias'] : $l['cat_name'] ) ;
                    if ($l['title'] == '') $l['title'] = '&nbsp;';
                    elseif ($mbstring) {
                        if (mb_strlen($l['title'], $charset ) > $title_len ) $l['title'] = mb_substr($l['title'], 0, $title_len-1, $charset).'...';
                    }
                    elseif (strlen($l['title']) > $title_len) $l['title'] = substr($l['title'], 0, $title_len-1).'...';

                    $l['w'] = $this->cl_cfg['w'];
                    $l['h'] = $this->cl_cfg['h'];
                    $thq = $this->cl_cfg['thq'];

                    $l['src'] = $this->_get_thumb($cl_cfg, $gdir, $path1.$l1['filename'], $l['w'], $l['h'], $thq );

                    // fill up the dir list with content
                    $_e2g['content'] .= $notables == 1 ? $this->_filler($this->_dir_tpl($cl_cfg), $l) : '<td>'. $this->_filler($this->_dir_tpl($cl_cfg), $l ).'</td>';
                    $i++;
                } // while ($l = mysql_fetch_array($dirquery, MYSQL_ASSOC))
            }
        }

        /*
         * FILE thumbs for the dir
        */
        if( $dir_num_rows!=$limit && $showonly!='folders' && !empty($gid) ) {

            /*
             * goldsky -- manage the pagination limit between dirs and files
             * (join the pagination AND the table grid).
            */

            $modulus_dir_count = $dir_count%$limit;
            $file_thumb_offset = $limit-$modulus_dir_count;
            $file_page_offset = ceil($dir_count/$limit);

            $filequery = 'SELECT * FROM '.$modx->db->config['table_prefix'].'easy2_files '
                    . 'WHERE dir_id IN ('.$gid.') '
                    . 'AND status = 1 '
                    . 'ORDER BY ' . $orderby . ' ' . $order . ' '
            ;

            if ( $file_thumb_offset > 0 && $file_thumb_offset < $limit ) {
                $filequery .= 'LIMIT '
                        . ( $dir_num_rows > 0 ?
                        ( ' 0, ' . ( $file_thumb_offset ) ) :
                        ( ( ( $gpn - $file_page_offset) * $limit) + $file_thumb_offset ) . ', ' . $limit );
            }
            elseif ( $file_thumb_offset != 0 || $file_thumb_offset == $limit ) {
                $filequery .= 'LIMIT '
                        . ( $modulus_dir_count > 0 ?
                        ( ' 0, ' . ( $file_thumb_offset ) ) :
                        ( ( ( $gpn - $file_page_offset) * $limit) ) . ', ' . $limit );
            }
            else { // $file_thumb_offset == 0 --> No sub directory
                $filequery .= 'LIMIT ' . ( $gpn * $limit) . ', ' . $limit ;
            }

            $file_query_result = mysql_query($filequery);

            $file_num_rows = mysql_num_rows($file_query_result);

            /*
             * retrieve the content
            */
            $i = 0;

            // checking the $dir_num_rows first
            if ( $dir_num_rows % $colls == 0 ) $_e2g['content'] .= '</tr><tr>';

            while ($l = mysql_fetch_array($file_query_result, MYSQL_ASSOC)) {
                // whether configuration setting is set with or without table, the template will adjust it
                /*
                * goldsky -- this is where the file's thumb 'float' to the dirs' in TABLE grid
                */
                if ( ( $i > 0 )
                        && ( ( $i + $dir_num_rows ) % $colls == 0 )
                        && $notables == 0 ) {
                    $_e2g['content'] .= '</tr><tr>';
                }

                $l['w'] = $this->cl_cfg['w'];
                $l['h'] = $this->cl_cfg['h'];

                // whether configuration setting is set with or without table, the template will adjust it
                $_e2g['content'] .= $notables == 1 ?  $this->_filler( $this->_thumb_tpl($cl_cfg), $this->_activate_libs($cl_cfg, $l) ) : '<td>'. $this->_filler( $this->_thumb_tpl($cl_cfg), $this->_activate_libs($cl_cfg, $l) ).'</td>';
                $i++;
            } // while ($l = @mysql_fetch_array($file_query_result, MYSQL_ASSOC))
        } // if( $dir_num_rows!=$limit )

        ########################################################################

        $_e2g['content'] .= $notables == 1 ? '</div>' : '</tr></table>';


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
        // count the files again, this time WITHOUT limit!
        if ($showonly=='folders') {
            $file_count = 0;
        } elseif ( !empty($gid) ) {
            $file_count = mysql_result(mysql_query(
                    'SELECT COUNT(id) FROM '.$modx->db->config['table_prefix'].'easy2_files '
                    .'WHERE dir_id IN ('.$gid.')'
                    ), 0, 0);
        }

        $total_count = $dir_count+$file_count;

        if ($total_count > $limit) {
            $_e2g['pages'] = '<div class="e2gpnums">';
            $i = 0;
            while ($i*$limit < $total_count) {
                if ($i == $gpn) $_e2g['pages'] .= '<b>'.($i+1).'</b> ';
                else $_e2g['pages'] .= '<a href="[~[*id*]~]'.$customgetparams.'&gid='.$gid.'&gpn='.$i.'">'.($i+1).'</a> ';
                $i++;
            }
            $_e2g['pages'] .= '</div>';
        }
        return $this->_filler($this->_gal_tpl($cl_cfg), $_e2g);
    }

    /*
     * $fid is set
    */
    private function _imagefile($cl_cfg) {
        global $modx;
        $fid = $this->cl_cfg['fid'];
        $colls = $this->cl_cfg['colls'];

        $filequery = 'SELECT * FROM '.$modx->db->config['table_prefix'].'easy2_files '
                . 'WHERE id IN ('.$fid.') '
                . 'AND status = 1 ';
        $res = mysql_query($filequery) or die('368 '.mysql_error());

        // START the grid
        $_e2g['content'] .= $notables == 1 ? '<div class="e2g">':'<table class="e2g"><tr>';

        $this->_libs($cl_cfg);
        $i = 0;
        while ($l = mysql_fetch_array($res, MYSQL_ASSOC)) {
            // create row grid
            if ( ( $i > 0 ) && ( $i % $colls == 0 ) && $notables == 0 ) $_e2g['content'] .= '</tr><tr>';

            $l['w'] = $this->cl_cfg['w'];
            $l['h'] = $this->cl_cfg['h'];

            // whether configuration setting is set with or without table, the template will adjust it
            $_e2g['content'] .= $notables == 1 ?  $this->_filler( $this->_thumb_tpl($cl_cfg), $this->_activate_libs($cl_cfg, $l) ) : '<td>'. $this->_filler( $this->_thumb_tpl($cl_cfg), $this->_activate_libs($cl_cfg, $l) ).'</td>';
            $i++;
        }

        // END the grid
        $_e2g['content'] .= $notables == 1 ? '</div>':'</tr></table>';
        return $this->_filler($this->_gal_tpl($cl_cfg), $_e2g);
    }

    /*
     * RANDOM IMAGE
     * To create a random image
     * @param string $orderby == 'random'
     * @param int $limit == 1
    */
    private function _randomimage($cl_cfg) {
        global $modx;

        $limit = $this->cl_cfg['limit'];
        $rgid = $this->cl_cfg['rgid'];

        $q = 'SELECT * FROM '.$modx->db->config['table_prefix'].'easy2_files '
                . 'WHERE status = 1 '
                . 'AND dir_id IN ('. $rgid .') '
                . 'ORDER BY RAND() LIMIT 1'
        ;

        $res = mysql_query($q);
        $num_rows = mysql_num_rows($res);
        if (!$num_rows) return;

        // START the grid
        $_e2g['content'] .= $notables == 1 ? '<div class="e2g">':'<table class="e2g"><tr>';
        $l = mysql_fetch_array($res, MYSQL_ASSOC);

        $l['w'] = $this->cl_cfg['w'];
        $l['h'] = $this->cl_cfg['h'];

        $this->_libs($cl_cfg);
        $this->_activate_libs($cl_cfg, $l);

        $_e2g['content'] .= $notables == 1 ? $this->_filler($this->_random_tpl($cl_cfg), $this->_activate_libs($cl_cfg, $l)) : '<td>'.$this->_filler($this->_random_tpl($cl_cfg), $this->_activate_libs($cl_cfg, $l)).'</td>';

        // END the grid
        $_e2g['content'] .= $notables == 1 ? '</div>':'</tr></table>';
        return $this->_filler($this->_gal_tpl($cl_cfg), $_e2g );
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
    private function _get_thumb ($cl_cfg, $gdir, $path, $w = 150, $h = 150, $thq=80, $resize_type = 'inner', $red = 255, $green = 255, $blue = 255) {
        global $modx;
        // decoding UTF-8
        $gdir = utf8_decode($gdir);
        $path = utf8_decode($path);

        $w = ( ( !empty($w) && $w!=$this->cl_cfg['w'] ) ? $w : $this->cl_cfg['w'] );
        $h =  ( ( !empty($h) && $h!=$this->cl_cfg['h'] ) ? $h : $this->cl_cfg['h'] );
        $thq = $this->cl_cfg['thq'];
        $resize_type = $this->cl_cfg['resize_type'];
        $red = isset($this->cl_cfg['thbg_red']) ? $this->cl_cfg['thbg_red'] : $red ;
        $green = isset($this->cl_cfg['thbg_green']) ? $this->cl_cfg['thbg_green'] : $green ;
        $blue = isset($this->cl_cfg['thbg_blue']) ? $this->cl_cfg['thbg_blue'] : $blue ;

        if (empty($path)) return false;

        $thumb_path = '_thumbnails/'.substr($path, 0, strrpos($path, '.')).'_'.$w.'x'.$h.'.jpg';

        /*
         * CREATE THUMBNAIL
        */
        // goldsky -- alter the maximum execution time
        set_time_limit(0);

        if (!file_exists($gdir.$thumb_path) && file_exists($gdir.$path)) {
            // goldsky -- adds output buffer to avoid PHP's memory limit
            ob_start();

            $i = getimagesize($gdir.$path);
            if ($i[2] == 1) $im = imagecreatefromgif ($gdir.$path);
            elseif ($i[2] == 2) $im = imagecreatefromjpeg ($gdir.$path);
            elseif ($i[2] == 3) $im = imagecreatefrompng ($gdir.$path);
            else return false;

            if ( $i[0]/$w > 2.00 || $i[1]/$h > 2.00 ) {
                $tmp_w = $w*2.00;
                $tmp_h = round($i[1] * ($tmp_w/$i[0]), 2);

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
                
                if (($i[0] / $i[1]) > ($w / $h)) {
                    $w2 = round($i[0] * $h / $i[1], 2);
                    $x = ($w2 - $w)/2.00 * (-1.00);
                } else {
                    $h2 = round($i[1] * $w / $i[0], 2);
                    $y = ($h2 - $h)/2.00 * (-1.00);
                }

                $pic = imagecreatetruecolor($w, $h);
                $bgc = imagecolorallocate($pic, $red, $green, $blue);
                imagefill($pic, 0, 0, $bgc);
                imagecopyresampled($pic, $im, $x, $y, 0, 0, $w2, $h2, $i[0], $i[1]);

            }
            elseif ($resize_type == 'shrink') {
                /*
                 * $resize_type == 'shrink'
                 * shrink to default dimensions
                */

                if ($i[0] > $i[1]) $h = round($i[1] * $w / $i[0], 2);
                else $w = round($i[0] * $h / $i[1], 2);

                $pic = imagecreatetruecolor($w, $h);
                $bgc = imagecolorallocate($pic, $red, $green, $blue);
                imagefill($pic, 0, 0, $bgc);
                imagecopyresampled($pic, $im, 0, 0, 0, 0, $w, $h, $i[0], $i[1]);
            }
            elseif ($resize_type == 'resize') {
                /*
                 * $resize_type == 'resize'
                 * proportionally reduce to default dimensions
                */
                // Shifts
                $x = $y = 0;

                // Dimensions
                $w2 = $w;
                $h2 = $h;

                if ($w > $h) {          // landscape thumbnail box
                    $w2 = round($i[0] * $h / $i[1], 2);
                    $x = abs($w-$w2)/2.00 ;
                } else {                // portrait thumbnail box
                    $h2 = round($i[1] * $w / $i[0], 2);
                    $y = abs($h-$h2)/2.00 ;
                }

                $pic = imagecreatetruecolor($w, $h);
                $bgc = imagecolorallocate($pic, $red, $green, $blue);
                imagefill($pic, 0, 0, $bgc);
                imagecopyresampled($pic, $im, $x, $y, 0, 0, $w2, $h2, $i[0], $i[1]);
            }
            else return;

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
            ob_end_clean();
        }
        // goldsky -- only to switch between localhost and live site.
        if ( strpos($_SERVER['DOCUMENT_ROOT'],'/') === (int)0 ) {
            $urlencoding = str_replace('%2F','/',rawurlencode($gdir.$thumb_path));
        } else $urlencoding = utf8_encode($gdir.$thumb_path);
        return $urlencoding;
    }

    /*
     * function get_path
     * function to get image's path
     * @param int $id = image's ID
    */
    private function _get_path ($id) {
        global $modx;

        $result = array();

        $q = 'SELECT A.cat_id,A.cat_name '
                . 'FROM '.$modx->db->config['table_prefix'].'easy2_dirs A, '
                . $modx->db->config['table_prefix'].'easy2_dirs B '
                . 'WHERE B.cat_id='.$id.' '
                . 'AND B.cat_left BETWEEN A.cat_left AND A.cat_right '
                . 'ORDER BY A.cat_left'
        ;

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
     * @param string $prefix = placeholder's prefix
     * @param string $suffix = placeholder's suffix
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
    private function _get_folder_img ($cl_cfg, $gid) {
        global $modx;
        $orderby = $this->cl_cfg['orderby'];
        $order = $this->cl_cfg['order'];

//        $res = mysql_query(
//                'SELECT DISTINCT F.* '
//                . 'FROM '. $modx->db->config['table_prefix'].'easy2_dirs A, '
//                . $modx->db->config['table_prefix'].'easy2_dirs B, '
//                . $modx->db->config['table_prefix'].'easy2_files F '
//                . 'WHERE (B.cat_id='.$gid.' '
//                . 'AND A.cat_left >= B.cat_left '
//                . 'AND A.cat_right <= B.cat_right '
//                . 'AND A.cat_level > B.cat_level '
//                . 'AND A.cat_visible = 1 '
//                . 'AND F.dir_id = A.cat_id)'
//                . 'OR F.dir_id = '.$gid.' '
//                .' ORDER BY A.cat_level ASC, F.id DESC'
//        );
        // http://modxcms.com/forums/index.php/topic,23177.msg273448.html#msg273448
        // ddim -- http://modxcms.com/forums/index.php/topic,48314.msg286241.html#msg286241
        $q = 'SELECT F.* '
                . 'FROM '. $modx->db->config['table_prefix'].'easy2_files F '
                . 'WHERE F.dir_id in (SELECT A.cat_id FROM '
                . $modx->db->config['table_prefix'].'easy2_dirs A, '
                . $modx->db->config['table_prefix'].'easy2_dirs B '
                . 'WHERE (B.cat_id=' . $gid . ' '
                . 'AND A.cat_left >= B.cat_left '
                . 'AND A.cat_right <= B.cat_right '
                . 'AND A.cat_level >= B.cat_level '
                . 'AND A.cat_visible = 1) '
                . 'ORDER BY A.cat_level ASC ) '
//                . 'ORDER BY F.id DESC '
                . 'LIMIT 1 '
        ;
        $res = mysql_query($q);
        $result = mysql_fetch_array($res, MYSQL_ASSOC);
        if ($result) $result['count'] = mysql_num_rows($res);
        mysql_free_result($res);

        /*
         * returned as folder's thumbnail's info array
        */
        return $result;
    }

    private function _libs($cl_cfg) {
        global $modx;
        $css = $this->cl_cfg['css'];
        $glib = $this->cl_cfg['glib'];

        // GLOBAL e2g CSS styles
        if (file_exists($css)) {
            $modx->regClientCSS($modx->config['base_url'].$css,'screen');
        }

        if (!isset($glibs)) {
            require E2G_SNIPPET_PATH.'includes/config.libs.easy2gallery.php';
        }
        // REGISTER the library from the config.libs.easy2gallery.php file.
        if ( isset($glibs[$glib] ) ) {
            // CSS STYLES
            foreach ( $glibs[$glib]['regClient']['CSS']['screen'] as $vRegClientCSS ) {
                $modx->regClientCSS($vRegClientCSS,'screen');
            }
            // JS Libraries
            foreach ( $glibs[$glib]['regClient']['JS'] as $vRegClientJS ) {
                $modx->regClientStartupScript($vRegClientJS);
            }
            unset($glib);
        }
    }

    private function _activate_libs($cl_cfg, $row) {
        require E2G_SNIPPET_PATH.'includes/config.libs.easy2gallery.php';
        global $modx;
        $gdir = $this->cl_cfg['gdir'];
        $css = $this->cl_cfg['css'];
        $glib = $this->cl_cfg['glib'];
        $charset = $this->cl_cfg['charset'];
        $name_len = $this->cl_cfg['name_len'];
        $mbstring = $this->cl_cfg['mbstring'];
        $w = $this->cl_cfg['w'];
        $h = $this->cl_cfg['h'];
        $thq = $this->cl_cfg['thq'];
        // COMMENT
        $ecm = $this->cl_cfg['ecm'];
        // SLIDESHOW
        $show_group = $this->cl_cfg['show_group'];

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

        $row['src'] = $this->_get_thumb($cl_cfg, $gdir, $path.$row['filename'], $w, $h, $thq);

        // gallery activation
        if ( $glibs[$glib] ) {
            $row['glibact'] = $glibs[$glib]['glibact'];
        }

        // HIDE COMMENTS from Ignored IP Addresses
        $comstatusq = 'SELECT ign_ip_address FROM '.$modx->db->config['table_prefix'].'easy2_ignoredip ';
        $comstatusres = mysql_query($comstatusq) or die('784 '.mysql_error());
        while ($comrow = mysql_fetch_array($comstatusres)) {
            $ignored_ip[$comrow['ign_ip_address']] = $comrow['ign_ip_address'];
        }

        $ip = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];

        /*
         * COMMENTS
        */
        if ($ecm == 1 && !isset($ignored_ip[$ip]) ) {
            $row['com'] = 'e2gcom'.($row['comments']==0?0:1);

            // iframe activation
            if ( $glibs[$glib] ) {
                $row['comments'] = '<a href="' . E2G_SNIPPET_URL . 'comments.easy2gallery.php?id='.$row['id'].'" '.$glibs[$glib]['comments'].'>'.$row['comments'].'</a>';
            }
        } else {
            $row['comments'] = '';
            $row['com'] = 'not_display';
        }
        if (isset($glib)) unset($glib);
        return $row;
    }

    private function _slideshow($cl_cfg) {
        global $modx;
        $slideshow = $this->cl_cfg['slideshow'];
        $gdir = $this->cl_cfg['gdir'];
        $gid = $this->cl_cfg['gid'];
        $fid = $this->cl_cfg['fid'];
        $rgid = $this->cl_cfg['rgid'];
        $gpn = $this->cl_cfg['gpn'];
        $orderby = $this->cl_cfg['orderby'];
        $order = $this->cl_cfg['order'];
        $w = $this->cl_cfg['w'];
        $h = $this->cl_cfg['h'];
        $thq = $this->cl_cfg['thq'];
        $css = $this->cl_cfg['css'];
        $landingpage = $this->cl_cfg['landingpage'];
        $ss_indexfile = $this->cl_cfg['ss_indexfile'];
        $ss_w = $this->cl_cfg['ss_w'];
        $ss_h = $this->cl_cfg['ss_h'];
        $ss_bg = $this->cl_cfg['ss_bg'];
        $ss_allowedratio = $this->cl_cfg['ss_allowedratio'];
        $ss_limit = $this->cl_cfg['ss_limit'];
        $ss_config = $this->cl_cfg['ss_config'];
        $ss_css = $this->cl_cfg['ss_css'];
        $ss_js = $this->cl_cfg['ss_js'];

//        $_ssfile = array();
        if (!empty($gid) && $modx->documentIdentifier!=$landingpage ) {
            $select = 'SELECT * FROM '.$modx->db->config['table_prefix'].'easy2_files '
                    . 'WHERE dir_id IN (' . $gid . ') '
                    . 'AND status = 1 '
                    . 'ORDER BY ' . $orderby . ' ' . $order . ' '
                    . ( $ss_limit == 'none' ? '' : 'LIMIT ' . ( $gpn * $ss_limit ) . ', ' . $ss_limit )
            ;
            $query = mysql_query($select);
            if (!$query) {
//                die('843 '.mysql_error());
                $o = 'snippet calls wrong gallery id:'.$gid.', order, or wrong limit.<br />';
                $o .= $select.'<br />';
                $o .= mysql_error();
                return $o;
            }

            while ($fetch = mysql_fetch_array($query)) {
                $path = $this->_get_path($fetch['dir_id']);
                if (count($path) > 1) {
                    unset($path[1]);
                    $path = implode('/', array_values($path)).'/';
                } else {
                    $path = '';
                }
                $_ssfile['id'][] .= $fetch['id'];
                $_ssfile['dirid'][] .= $fetch['dir_id'];
                $_ssfile['src'][] .= utf8_decode($gdir.$path.$fetch['filename']);
                $_ssfile['filename'][] .= $fetch['filename'];
                $_ssfile['title'][] .= ($fetch['name']!='' ? $fetch['name'] : $fetch['filename']);
                $_ssfile['name'][] .= $fetch['name'];
                $_ssfile['description'][] .= $fetch['description'];
                $path = $this->_get_path($fetch['dir_id']);
                if (count($path) > 1) {
                    unset($path[1]);
                    $path = implode('/', array_values($path)).'/';
                } else {
                    $path = '';
                }
                $_ssfile['thumbsrc'][] .= $this->_get_thumb($cl_cfg, $gdir, $path.$fetch['filename'], $w, $h, $thq);
                $_ssfile['resizedimg'][] .= $this->_get_thumb($cl_cfg, $gdir, $path.$fetch['filename'], $ss_w, $ss_h, $thq);
            }
        }

        if (!empty($fid)) {
            $select = 'SELECT * FROM '.$modx->db->config['table_prefix'].'easy2_files '
                    . 'WHERE id IN ('.$fid.') '
                    . 'AND status = 1 '
            ;
            $query = mysql_query($select);
            if (!$query) {
//                die('884 '.mysql_error());
                return 'snippet calls wrong file id:'.$fid;
            }

            while ($fetch = mysql_fetch_array($query)) {
                $path = $this->_get_path($fetch['dir_id']);
                if (count($path) > 1) {
                    unset($path[1]);
                    $path = implode('/', array_values($path)).'/';
                } else {
                    $path = '';
                }
                $_ssfile['id'][] .= $fetch['id'];
                $_ssfile['dirid'][] .= $fetch['dir_id'];
                $_ssfile['src'][] .= utf8_decode($gdir.$path.$fetch['filename']);
                $_ssfile['filename'][] .= $fetch['filename'];
                $_ssfile['title'][] .= ($fetch['name']!='' ? $fetch['name'] : $fetch['filename']);
                $_ssfile['name'][] .= $fetch['name'];
                $_ssfile['description'][] .= $fetch['description'];
                $path = $this->_get_path($fetch['dir_id']);
                if (count($path) > 1) {
                    unset($path[1]);
                    $path = implode('/', array_values($path)).'/';
                } else {
                    $path = '';
                }
                $_ssfile['thumbsrc'][] .= $this->_get_thumb($cl_cfg, $gdir, $path.$fetch['filename'], $w, $h, $thq);
                $_ssfile['resizedimg'][] .= $this->_get_thumb($cl_cfg, $gdir, $path.$fetch['filename'], $ss_w, $ss_h, $thq);
            }
        }

        if (!empty($rgid)) {
            $select = 'SELECT * FROM '.$modx->db->config['table_prefix'].'easy2_files '
                    . 'WHERE status = 1 '
                    . 'AND dir_id IN ('. $rgid .') '
                    . 'ORDER BY RAND() '
                    . ( $ss_limit == 'none' ? '' : 'LIMIT 0,'.$ss_limit.' ' )
            ;
            $query = mysql_query($select);
            if (!$query) {
//                die('924 '.mysql_error());
                return 'snippet calls wrong random file id:'.$gid.', or wrong limit';
            }
            while ($fetch = mysql_fetch_array($query)) {
                $path = $this->_get_path($fetch['dir_id']);
                if (count($path) > 1) {
                    unset($path[1]);
                    $path = implode('/', array_values($path)).'/';
                } else {
                    $path = '';
                }
                $_ssfile['id'][] .= $fetch['id'];
                $_ssfile['dirid'][] .= $fetch['dir_id'];
                $_ssfile['src'][] .= utf8_decode($gdir.$path.$fetch['filename']);
                $_ssfile['filename'][] .= $fetch['filename'];
                $_ssfile['title'][] .= ($fetch['name']!='' ? $fetch['name'] : $fetch['filename']);
                $_ssfile['name'][] .= $fetch['name'];
                $_ssfile['description'][] .= $fetch['description'];
                $path = $this->_get_path($fetch['dir_id']);
                if (count($path) > 1) {
                    unset($path[1]);
                    $path = implode('/', array_values($path)).'/';
                } else {
                    $path = '';
                }
                $_ssfile['thumbsrc'][] .= $this->_get_thumb($cl_cfg, $gdir, $path.$fetch['filename'], $w, $h, $thq);
                $_ssfile['resizedimg'][] .= $this->_get_thumb($cl_cfg, $gdir, $path.$fetch['filename'], $ss_w, $ss_h, $thq);
            }
        }

        if ($ss_allowedratio != 'none') {
            // create min-max slideshow width/height ratio
            $ss_exratio = explode('-', $ss_allowedratio);
            $ss_minratio = $ss_exratio[0];
            $ss_maxratio = $ss_exratio[1];
        }

        /*
         * if the counting below = 0 (zero), then should be considered inside
         * the slideshow types, while in some slideshows this doesn't matter.
        */
        $count = count($_ssfile['resizedimg']);

        // added the &fid parameter inside the &slideshow, to open a full page of the clicked image.
        if ( isset($_GET['fid']) ) {
            $modx->regClientCSS($css,'screen');
            $select = 'SELECT * FROM '.$modx->db->config['table_prefix'].'easy2_files '
                    . 'WHERE id = '.$_GET['fid'].' '
            ;
            $query = mysql_query($select);
            if (!$query) {
//                die('975 '.mysql_error());
                return 'snippet calls wrong file id.';
            }

            while ($fetch = mysql_fetch_array($query)) {
                $path = $this->_get_path($fetch['dir_id']);
                if (count($path) > 1) {
                    unset($path[1]);
                    $path = implode('/', array_values($path)).'/';
                } else {
                    $path = '';
                }
//                $l['src'] = utf8_decode($gdir.$path.$fetch['filename']);
                $l['src'] = $gdir.$path.$fetch['filename'];
                $l['title'] = ($fetch['name']!='' ? $fetch['name'] : $fetch['filename']);
                $l['name'] = $fetch['name'];
                $l['description'] = $fetch['description'];
                $path = $this->_get_path($fetch['dir_id']);
                if (count($path) > 1) {
                    unset($path[1]);
                    $path = implode('/', array_values($path)).'/';
                } else {
                    $path = '';
                }
            }
            if ( isset($landingpage) && $modx->documentIdentifier!=$landingpage ) {
                $modx->sendForward($landingpage).'&fid='.$fileid.'&lp='.$landingpage;
            }
            return $this->_filler( $this->_page_tpl($cl_cfg), $l );
        } else {
            // use custom index file if it's been called.
            if ( isset($ss_indexfile) && file_exists($ss_indexfile) ) {
                include($ss_indexfile);
            } elseif ( isset($ss_indexfile) && !file_exists($ss_indexfile) ) {
                $ss_display = 'slideshow index file <b>'.$ss_indexfile.'</b> is not found.';
            }
            // include the available slideshow file config
            elseif ( !isset($ss_indexfile) && !file_exists(E2G_SNIPPET_PATH.'includes/slideshow/'.$slideshow.'/'.$slideshow.'.php')) {
                $ss_display = 'slideshow config for <b>'.$slideshow.'</b> is not found.';
            } else {
                include(E2G_SNIPPET_PATH.'includes/slideshow/'.$slideshow.'/'.$slideshow.'.php');
            }
        }
//        $_ssfile = array();
//        unset($_ssfile);
        // return the slideshow
        return $ss_display;
    }

    /*
     * function landingpage
     * a whole page to show the image, including informations within it.
    */
    private function _landingpage($cl_cfg, $fileid) {
        global $modx;
        $landingpage = $this->cl_cfg['landingpage'];
    }

    // DIRECTORY TEMPLATE
    private function _dir_tpl($cl_cfg) {
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
    private function _thumb_tpl($cl_cfg) {
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
    private function _gal_tpl($cl_cfg) {
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
    private function _random_tpl($cl_cfg) {
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

    // RANDOM TEMPLATE
    private function _page_tpl($cl_cfg) {
        global $modx;
        $page_tpl = $this->cl_cfg['page_tpl'];
        if (file_exists($page_tpl)) {
            $page_tpl = file_get_contents($page_tpl);
            return $page_tpl;
        } elseif (!empty($modx->chunkCache[$page_tpl])) {
            $page_tpl = $modx->chunkCache[$page_tpl];
            return $page_tpl;
        } else {
            echo 'Landing page template '.$page_tpl.' not found!';
        }
    }
}
?>