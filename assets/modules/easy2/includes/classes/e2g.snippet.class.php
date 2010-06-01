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
    public $e2gsnip_cfg = array();
    private $_e2g = array();

    public function  __construct($e2gsnip_cfg) {
        $this->e2gsnip_cfg = $e2gsnip_cfg;
        $this->_e2g = $_e2g;
    }

    public function display() {
        /*
         * 1. '&gid' : full gallery directory (directory - &gid - default)
         * 2. '&fid' : one file only (file - $fid)
         * 3. '&rgid' : random file in a directory (random - $rgid)
         * 4. '&slideshow' : slideshow by fid-s or rgid-s or gid-s
        */
        global $modx;
        $fid = $this->e2gsnip_cfg['fid'];
        $rgid = $this->e2gsnip_cfg['rgid'];
        $gid = $this->e2gsnip_cfg['gid']; // default
        $slideshow = $this->e2gsnip_cfg['slideshow'];
        $landingpage = $this->e2gsnip_cfg['landingpage'];

        if ( !(isset($landingpage)) ) {
            if ( !empty($fid) && !isset($slideshow) ) {
                echo $this->_imagefile();
            }
            if ( !empty($rgid) && !isset($slideshow) ) {
                echo $this->_randomimage();
            }
            if ( empty($fid) && empty($rgid) && !isset($slideshow) ) {
                echo $this->_gallery(); // default
            }
        }
        if ( !empty($slideshow) ) {
            echo $this->_slideshow();
        }
        if ( isset($landingpage) && isset($_GET['fid']) ) {
            echo $this->_landingpage($_GET['fid']);
        }
    }

    /*
     * full gallery execution
    */
    private function _gallery() {
        global $modx;

        $gdir = $this->e2gsnip_cfg['gdir'];
        $gid = $this->e2gsnip_cfg['gid'];
        $static_gid = $this->e2gsnip_cfg['static_gid'];
        $tags = $this->e2gsnip_cfg['tags'];
        $cat_orderby = $this->e2gsnip_cfg['cat_orderby'];
        $cat_order = $this->e2gsnip_cfg['cat_order'];
        $gpn = $this->e2gsnip_cfg['gpn'];
        $limit = $this->e2gsnip_cfg['limit'];
        
        $charset = $this->e2gsnip_cfg['charset'];
        $mbstring = $this->e2gsnip_cfg['mbstring'];
        $title_len = $this->e2gsnip_cfg['cat_name_len'];
//        $notables = $this->e2gsnip_cfg['notables']; // deprecated
        $grid = $this->e2gsnip_cfg['grid'];
        $grid_class = $this->e2gsnip_cfg['grid_class'];
        $e2g_currentcrumb_class = $this->e2gsnip_cfg['e2g_currentcrumb_class'];
        $e2gback_class = $this->e2gsnip_cfg['e2gback_class'];
        $e2gpnums_class = $this->e2gsnip_cfg['e2gpnums_class'];
        $colls = $this->e2gsnip_cfg['colls'];
        $orderby = $this->e2gsnip_cfg['orderby'];
        $order = $this->e2gsnip_cfg['order'];
        $showonly = $this->e2gsnip_cfg['showonly'];
        $customgetparams = $this->e2gsnip_cfg['customgetparams'];
        $gal_desc = $this->e2gsnip_cfg['gal_desc'];
        $plugins = $this->e2gsnip_cfg['plugins'];
        
        // CRUMBS
        $crumbs_separator = $this->e2gsnip_cfg['crumbs_separator'];
        $crumbs_showHome = $this->e2gsnip_cfg['crumbs_showHome'];
        $crumbs_showAsLinks = $this->e2gsnip_cfg['crumbs_showAsLinks'];
        $crumbs_showCurrent = $this->e2gsnip_cfg['crumbs_showCurrent'];
        $crumbs_showPrevious = $this->e2gsnip_cfg['crumbs_showPrevious'];

        if ( isset($tags) ) {
            $gid = isset($_GET['gid']) ? $_GET['gid'] : $this->_tags_ids('dir', $tags);
            $static_gid = $this->_tags_ids('dir', $tags);
        }
//echo __LINE__.': $tags = '.$tags.'<br />';
        /*
         * CHECK THE REAL DECENDANT OF THE &gid CALL.
         * OTHERWISE, Restricted Access
         */
        $this->_check_gid_decendant($gid, $static_gid);

        /*
         * PATHS
        */
        // NOT the $e2g config
        $_e2g = array('content'=>'','pages'=>'','parent_id'=>0,'back'=>'');

        // START the grid
        $_e2g['content'] = (($grid=='css') ? '<div class="'.$grid_class.'">':'<table class="'.$grid_class.'"><tr>') ;

        // count the directories WITHOUT limit!
        if ($showonly=='images' || !isset($gid)) {
            $dir_count = 0;
        } else {
            if ( isset($tags) && !isset($_GET['gid']) ) {
                $select_count = 'SELECT COUNT(DISTINCT cat_id) '
                        . 'FROM '.$modx->db->config['table_prefix'].'easy2_dirs '
                        . 'WHERE cat_tags LIKE \'%'.$tags.'%\'';
            } else {
                $select_count = 'SELECT COUNT(DISTINCT d.cat_id) '
                        . 'FROM '.$modx->db->config['table_prefix'].'easy2_dirs AS d '
                        . 'WHERE d.cat_tags IN ('.$gid.') '
                        . 'AND d.cat_visible = 1 '
                        // ddim -- wrapping children folders
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
                                    .')<>0 ';
            }

//echo __LINE__.': $select_count = '.$select_count.'<hr />';
            $dir_count = mysql_result(mysql_query($select_count), 0 ,0);
            ########################################################################
            /*
             * Add the multiple IDs capability into the &gid
            */
            $multiple_gids = explode(',',$gid);
            $multiple_gids_count = count($multiple_gids);
            // reset the directory number
            $dir_num_rows = 0;
            unset($single_gid);
            foreach ($multiple_gids as $single_gid) {
                // get path from the $gid
                $path = $this->_get_path($single_gid);

                /*
                 * limiting the CRUMBS paths.
                 */
                if ( ($static_gid !=1) && !empty($path) && !isset($tags) ) {
                    $static_path = $this->_get_path($static_gid);
                    if (!$crumbs_showPrevious) {
                        $path = array_slice($path, (count($static_path)-2),null,true);
                    }
                }
                
                // get "category name" from $path
                $_e2g['cat_name'] = is_array($path) ? end($path) : '';
                // reset crumbs
                $crumbs='';

                /*
                 * Only use crumbs if it is a single gid.
                 * Otherwise, how can we make crumbs for merging directories in 1 page?
                */
                if ($multiple_gids_count==1 && !isset($tags)) {
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
                            else $crumbs .= $crumbs_separator.'<span class="'.$e2g_currentCrumb.'">'.$v.'</span>';
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

            $this->_libs();

            /*
             * FOLDERS
             */
            if ( $showonly != 'images' ) {
                // SUBDIRS & THUMBS FOR SUBDIRS
                if ( isset($tags) && !isset($_GET['gid']) ) {
                    $query = 'SELECT * '
                            . 'FROM '.$modx->db->config['table_prefix'].'easy2_dirs '
                            . 'WHERE cat_tags LIKE \'%'.$tags.'%\' '
                            . 'AND cat_visible = 1 '
                            . 'ORDER BY ' . $cat_orderby . ' ' . $cat_order . ' '
                            . 'LIMIT ' . ( $gpn * $limit ) . ', ' . $limit
                            ;
                } else {
                    $query = 'SELECT DISTINCT d.* '
                            . 'FROM '.$modx->db->config['table_prefix'].'easy2_dirs AS d '
                            . 'WHERE d.parent_id IN (' . $gid . ') '
                            // ddim -- http://modxcms.com/forums/index.php/topic,48314.msg286241.html#msg286241
                            . 'AND d.cat_visible = 1 '
                            . 'AND ('
                                . 'SELECT count(*) FROM '.$modx->db->config['table_prefix'].'easy2_files F '
                                . 'WHERE F.dir_id IN ('
                                    . 'SELECT A.cat_id FROM '.$modx->db->config['table_prefix'].'easy2_dirs A, '
                                    . $modx->db->config['table_prefix'].'easy2_dirs B '
                                    . 'WHERE ('
                                        . 'B.cat_id=d.cat_id '
                                        . 'AND A.cat_left >= B.cat_left '
                                        . 'AND A.cat_right <= B.cat_right '
                                        . 'AND A.cat_level >= B.cat_level '
                                        . 'AND A.cat_visible = 1'
                                    . ')'
                                . ')'
                            .')<>0 '
                            . 'ORDER BY ' . $cat_orderby . ' ' . $cat_order . ' '
                            . 'LIMIT ' . ( $gpn * $limit ) . ', ' . $limit
                            ;
                }

                 $dirquery = mysql_query($query);
                if (!$dirquery) die(__LINE__.' : '.mysql_error().'<br />'.$query);
                $dir_num_rows += mysql_num_rows($dirquery);

//echo __LINE__.': $dir_num_rows = '.$dir_num_rows.'<hr />';
//echo __LINE__.': $dir_count = '.$dir_count.'<hr />';
//echo __LINE__.': $gid = '.$gid.'<hr />';
//echo __LINE__.': $static_gid = '.$static_gid.'<hr />';
//echo __LINE__.': $query = '.$query.'<hr />';

//                $_e2g['permalink'] = '<a href="#" name="'.$this->_get_dir_info($single_gid,'cat_id').'"></a>';
                if ( isset($tags) && !isset($_GET['gid']) ) {
                    $_e2g['permalink'] = '<a href="#" name="'.$tags.'"></a>';
                } else {
                    $_e2g['permalink'] = '<a href="#" name="'.$gid.'"></a>';
                }

                if ($gal_desc=='1') {
                    $_e2g['cat_description'] = $this->_get_dir_info($single_gid,'cat_description');
                    $_e2g['cat_title'] = $this->_get_dir_info($single_gid,'cat_alias');
                    $_e2g['title'] = ($_e2g['cat_title'] != '' ? $_e2g['cat_title'] : $_e2g['cat_name'] );
                    if ( $_e2g['title']=='' && $_e2g['cat_description']=='' ) {
                        $_e2g['desc_class']= 'style="display:none;"';
                    }
                } else {
                    $_e2g['desc_class']= 'style="display:none;"';
                }
                
                $i = 0;
                while ($l = mysql_fetch_array($dirquery, MYSQL_ASSOC)) {
                    $l['permalink'] = $l['cat_id'];
                    if ( isset($tags) && !isset($_GET['gid']) ) {
                        $l['cat_tags'] = '&tags='.$tags;
                    } else {
                        $l['cat_tags'] = '';
                    }

                    // search image for subdir
                    $l1=$this->_get_folder_img($l['cat_id']);
                    // if there is an empty folder, or invalid content
                    if (!$l1) continue;

                    $l['count'] = $l1['count'];

                    // path to subdir's thumbnail
                    $path1=$this->_get_path($l1['dir_id']);

                    // if path is more than one
                    if (count($path1) > 1) {
                        unset($path1[1]); // unset the 'Easy 2' root path only
                        $path1 = implode('/', array_values($path1)).'/';
                    }
                    // if path is not many
                    else {
                        $path1 = '';
                    }

                    // Populate the grid with folder's thumbnails
                    if ( ( $i > 0 ) && ( $i % $colls == 0 ) && $grid == 'table' ) $_e2g['content'] .= '</tr><tr>';

                    $l['title'] = ( $l['cat_alias'] != '' ? $l['cat_alias'] : $l['cat_name'] ) ;
                    if ($l['title'] == '') $l['title'] = '&nbsp;';
                    elseif ($mbstring) {
                        if (mb_strlen($l['title'], $charset ) > $title_len ) $l['title'] = mb_substr($l['title'], 0, $title_len-1, $charset).'...';
                    }
                    elseif (strlen($l['title']) > $title_len) $l['title'] = substr($l['title'], 0, $title_len-1).'...';

                    /*
                     * insert plugins for each gallery
                     */
                    if (isset($plugins) && preg_match('/gallery:/', $plugins))
                        $l['galleryplugin'] = $this->_plugin('gallery',$plugins,$l);

                    $l['w'] = $this->e2gsnip_cfg['w'];
                    $l['h'] = $this->e2gsnip_cfg['h'];
                    $thq = $this->e2gsnip_cfg['thq'];

                    $l['src'] = $this->_get_thumb( $gdir, $path1.$l1['filename'], $l['w'], $l['h'], $thq );

                    // fill up the dir list with content
                    $_e2g['content'] .= (($grid == 'css') ? $this->_filler($this->_dir_tpl(), $l) : '<td>'. $this->_filler($this->_dir_tpl(), $l ).'</td>');
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

            if ( isset($tags) && !isset($_GET['gid']) ) {
                $filequery = 'SELECT * FROM '.$modx->db->config['table_prefix'].'easy2_files '
                            . 'WHERE tags LIKE \'%'.$tags.'%\' '
                            . 'AND status = 1 '
                            . 'ORDER BY ' . $orderby . ' ' . $order . ' '
                            ;
            }
            else {
                $filequery = 'SELECT * FROM '.$modx->db->config['table_prefix'].'easy2_files '
                            . 'WHERE dir_id IN ('.$gid.') '
                            . 'AND status = 1 '
                            . 'ORDER BY ' . $orderby . ' ' . $order . ' '
                            ;
            }

//echo __LINE__.': $file_thumb_offset = '.$file_thumb_offset.'<hr />';
//echo __LINE__.': $dir_num_rows = '.$dir_num_rows.'<hr />';
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
            $file_query_result = mysql_query($filequery) or die(__LINE__.' : '.mysql_error().'<br />'.$filequery);
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
                        && $grid == 'table' ) {
                    $_e2g['content'] .= '</tr><tr>';
                }

                /*
                 * insert plugins for each thumb
                 */
                if (isset($plugins) && preg_match('/thumb:/', $plugins))
                    $l['thumbplugin'] = $this->_plugin('thumb',$plugins,$l);
                
                $l['w'] = $this->e2gsnip_cfg['w'];
                $l['h'] = $this->e2gsnip_cfg['h'];

                // whether configuration setting is set with or without table, the template will adjust it
                $_e2g['content'] .= (($grid == 'css') ?  $this->_filler( $this->_thumb_tpl(), $this->_activate_libs($l) ) : '<td>'. $this->_filler( $this->_thumb_tpl(), $this->_activate_libs($l) ).'</td>');
                $i++;
            } // while ($l = @mysql_fetch_array($file_query_result, MYSQL_ASSOC))
        } // if( $dir_num_rows!=$limit )

        ########################################################################

        $_e2g['content'] .= (($grid == 'css') ? '</div>' : '</tr></table>');


        /*
         * BACK BUTTON
        */
        if ($_e2g['parent_id'] > 0) {
            $_e2g['back'] = '<p class="'.$e2gback_class.'">&laquo; <a href="[~[*id*]~]&gid='.$_e2g['parent_id'].'">'.$_e2g['parent_name'].'</a></p>';
        }

        /*
         * CRUMBS
        */
        $_e2g['crumbs']=$crumbs;

        /*
        *  PAGINATION: PAGE LINKS - joining between dirs and files pagination
        */
        // count the files again, this time WITHOUT limit!
        if ($showonly=='folders') {
            $file_count = 0;
        } elseif ( !empty($gid) ) {
            if ( isset($tags) && !isset($_GET['gid']) ) {
                $file_count_select = 'SELECT COUNT(id) FROM '.$modx->db->config['table_prefix'].'easy2_files WHERE tags LIKE \'%'.$tags.'%\' ';
            } else {
                $file_count_select = 'SELECT COUNT(id) FROM '.$modx->db->config['table_prefix'].'easy2_files WHERE dir_id IN ('.$gid.') ';
            }

            $file_count_query = mysql_query($file_count_select) or  die(__LINE__.' : '.mysql_error().'<br />'.$file_count_select);
            $file_count = mysql_result($file_count_query, 0, 0);
        }

        $total_count = $dir_count+$file_count;

        if ($total_count > $limit) {
            $_e2g['pages'] = '<div class="'.$e2gpnums_class.'">';
            $i = 0;
            while ($i*$limit < $total_count) {
                if ( isset($tags) && !isset($_GET['gid']) ) {
                    if ($i == $gpn) $_e2g['pages'] .= '<b>'.($i+1).'</b> ';
                    else $_e2g['pages'] .= '<a href="[~[*id*]~]'.$customgetparams.'&tags='.$tags.'&gpn='.$i.'#'.$tags.'">'.($i+1).'</a> ';
                } else {
                    if ($i == $gpn) $_e2g['pages'] .= '<b>'.($i+1).'</b> ';
                    else $_e2g['pages'] .= '<a href="[~[*id*]~]'.$customgetparams.'&gid='.$gid.'&gpn='.$i.'#'.$gid.'">'.($i+1).'</a> ';
                }
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
        global $modx;
        $fid = $this->e2gsnip_cfg['fid'];
        $colls = $this->e2gsnip_cfg['colls'];
//        $notables = $this->e2gsnip_cfg['notables']; // deprecated
        $grid = $this->e2gsnip_cfg['grid'];
        $grid_class = $this->e2gsnip_cfg['grid_class'];

        $filequery = 'SELECT * FROM '.$modx->db->config['table_prefix'].'easy2_files '
                . 'WHERE id IN ('.$fid.') '
                . 'AND status = 1 ';
        $res = mysql_query($filequery) or die(__LINE__.' : '.mysql_error().'<br />'.$filequery);

        // just to hide gallery's description CSS box in gallery template
        if ( !isset($_e2g['title']) || !isset($_e2g['cat_description']) ) {
            $_e2g['desc_class']= 'style="display:none;"';
        } else $_e2g['e2gdir_class']='';
        
        // START the grid
        $_e2g['content'] .= (($grid == 'css') ? '<div class="'.$grid_class.'">':'<table class="'.$grid_class.'"><tr>');

        $this->_libs();
        $i = 0;
        while ($l = mysql_fetch_array($res, MYSQL_ASSOC)) {
            // create row grid
            if ( ( $i > 0 ) && ( $i % $colls == 0 ) && $grid == 'table' ) $_e2g['content'] .= '</tr><tr>';

            $l['w'] = $this->e2gsnip_cfg['w'];
            $l['h'] = $this->e2gsnip_cfg['h'];

            // whether configuration setting is set with or without table, the template will adjust it
            $_e2g['content'] .= (($grid == 'css') ?  $this->_filler( $this->_thumb_tpl(), $this->_activate_libs($l) ) : '<td>'. $this->_filler( $this->_thumb_tpl(), $this->_activate_libs($l) ).'</td>');
            $i++;
        }

        // END the grid
        $_e2g['content'] .= (($grid == 'css') ? '</div>':'</tr></table>');
        return $this->_filler($this->_gal_tpl(), $_e2g);
    }

    /*
     * RANDOM IMAGE
     * To create a random image
     * @param string $orderby == 'random'
     * @param int $limit == 1
    */
    private function _randomimage() {
        global $modx;
        $limit = $this->e2gsnip_cfg['limit'];
        $rgid = $this->e2gsnip_cfg['rgid'];
//        $notables = $this->e2gsnip_cfg['notables'];  // deprecated
        $grid = $this->e2gsnip_cfg['grid'];
        $grid_class = $this->e2gsnip_cfg['grid_class'];

        $q = 'SELECT * FROM '.$modx->db->config['table_prefix'].'easy2_files '
                . 'WHERE status = 1 '
                . 'AND dir_id IN ('. $rgid .') '
                . 'ORDER BY RAND() LIMIT 1'
        ;

        $res = mysql_query($q);
        $num_rows = mysql_num_rows($res);
        if (!$num_rows) return;

        // just to hide gallery's description CSS box in gallery template
        if ( !isset($_e2g['title']) || !isset($_e2g['cat_description']) ) {
            $_e2g['desc_class']= 'style="display:none;"';
        } else $_e2g['e2gdir_class']='';

        // START the grid
        $_e2g['content'] .= (($grid == 'css') ? '<div class="'.$grid_class.'">':'<table class="'.$grid_class.'"><tr>');
        $l = mysql_fetch_array($res, MYSQL_ASSOC);

        $l['w'] = $this->e2gsnip_cfg['w'];
        $l['h'] = $this->e2gsnip_cfg['h'];

        $this->_libs();
        $this->_activate_libs($l);

        $_e2g['content'] .= (($grid == 'css') ? $this->_filler($this->_random_tpl(), $this->_activate_libs($l)) : '<td>'.$this->_filler($this->_random_tpl(), $this->_activate_libs($l)).'</td>');

        // END the grid
        $_e2g['content'] .= (($grid == 'css') ? '</div>':'</tr></table>');
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
    private function _get_thumb ( $gdir, $path, $w = 150, $h = 150, $thq=80, $resize_type = 'inner', $red = 255, $green = 255, $blue = 255) {
        global $modx;
        // decoding UTF-8
        $gdir = $this->_e2g_decode($gdir);
        $path = $this->_e2g_decode($path);

        $w = ( ( !empty($w) && $w!=$this->e2gsnip_cfg['w'] ) ? $w : $this->e2gsnip_cfg['w'] );
        $h =  ( ( !empty($h) && $h!=$this->e2gsnip_cfg['h'] ) ? $h : $this->e2gsnip_cfg['h'] );
        $thq = $this->e2gsnip_cfg['thq'];
        $resize_type = $this->e2gsnip_cfg['resize_type'];
        $red = isset($this->e2gsnip_cfg['thbg_red']) ? $this->e2gsnip_cfg['thbg_red'] : $red ;
        $green = isset($this->e2gsnip_cfg['thbg_green']) ? $this->e2gsnip_cfg['thbg_green'] : $green ;
        $blue = isset($this->e2gsnip_cfg['thbg_blue']) ? $this->e2gsnip_cfg['thbg_blue'] : $blue ;

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

            $i = @getimagesize($gdir.$path);
            if (!$i) return false;
            
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
                if (!@mkdir($npath)) return false;
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
        } else $urlencoding = $this->_e2g_encode($gdir.$thumb_path);
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
        if (!$res) return; // asuming there are multiple gids
        while ($l = mysql_fetch_row($res)) {
            $result[$l[0]] = $l[1];
        }
        if (empty($result)) return null;
        return $result;
    }

    /*
     * function get_dir_info
     * function to get directory's information
     * @param int $dirid = gallery's ID
    */
    private function _get_dir_info($dirid,$field) {
        global $modx;

        $dirinfo = array();

        $q = 'SELECT '.$field.' FROM '.$modx->db->config['table_prefix'].'easy2_dirs '
            . 'WHERE cat_id='.$dirid.' '
        ;

        if (!($res = mysql_query($q))) return ('Wrong field.');
        while ($l = mysql_fetch_array($res)) {
            $dirinfo[$field] = $l[$field];
        }
        if (empty($dirinfo[$field])) return null;
        return $dirinfo[$field];
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
    private function _get_folder_img ($gid) {
        global $modx;
        $orderby = $this->e2gsnip_cfg['orderby'];
        $order = $this->e2gsnip_cfg['order'];

        // http://modxcms.com/forums/index.php/topic,23177.msg273448.html#msg273448
        // ddim -- http://modxcms.com/forums/index.php/topic,48314.msg286241.html#msg286241
        $q = 'SELECT F.* '
            . 'FROM '. $modx->db->config['table_prefix'].'easy2_files F '
            . 'WHERE F.dir_id in ('
                . 'SELECT A.cat_id FROM '
                . $modx->db->config['table_prefix'].'easy2_dirs A, '
                . $modx->db->config['table_prefix'].'easy2_dirs B '
                . 'WHERE ('
                    . 'B.cat_id=' . $gid . ' '
                    . 'AND A.cat_left >= B.cat_left '
                    . 'AND A.cat_right <= B.cat_right '
                    . 'AND A.cat_level >= B.cat_level '
                    . 'AND A.cat_visible = 1'
                    .') '
                . 'ORDER BY A.cat_level ASC '
                .') '
            . 'ORDER BY F.id DESC '
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

    private function _libs() {
        global $modx;
        $css = $this->e2gsnip_cfg['css'];
        $glib = $this->e2gsnip_cfg['glib'];

        // GLOBAL e2g CSS styles
        if (file_exists($css)) {
            $modx->regClientCSS($modx->config['base_url'].$css,'screen');
        }

        if (!isset($glibs)) {
            require E2G_SNIPPET_PATH.'includes/configs/libs.config.easy2gallery.php';
        }
        // REGISTER the library from the libs.config.easy2gallery.php file.
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

    private function _activate_libs($row) {
        require E2G_SNIPPET_PATH.'includes/configs/libs.config.easy2gallery.php';
        global $modx;
        $gdir = $this->e2gsnip_cfg['gdir'];
        $css = $this->e2gsnip_cfg['css'];
        $glib = $this->e2gsnip_cfg['glib'];
        $mbstring = $this->e2gsnip_cfg['mbstring'];
        $charset = $this->e2gsnip_cfg['charset'];
        $name_len = $this->e2gsnip_cfg['name_len'];
        $w = $this->e2gsnip_cfg['w'];
        $h = $this->e2gsnip_cfg['h'];
        $thq = $this->e2gsnip_cfg['thq'];
        // COMMENT
        $ecm = $this->e2gsnip_cfg['ecm'];
        // SLIDESHOW
        $show_group = $this->e2gsnip_cfg['show_group'];

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

        $row['src'] = $this->_get_thumb($gdir, $path.$row['filename'], $w, $h, $thq);

        // gallery activation
        if ( $glibs[$glib] ) {
            $row['glibact'] = $glibs[$glib]['glibact'];
        }

        // HIDE COMMENTS from Ignored IP Addresses
        $comstatusq = 'SELECT ign_ip_address FROM '.$modx->db->config['table_prefix'].'easy2_ignoredip ';
        $comstatusres = mysql_query($comstatusq) or die(__LINE__.' : '.mysql_error());
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

    private function _slideshow() {
        global $modx;
        $slideshow = $this->e2gsnip_cfg['slideshow'];
        $gdir = $this->e2gsnip_cfg['gdir'];
        $gid = $this->e2gsnip_cfg['gid'];
        $fid = $this->e2gsnip_cfg['fid'];
        $rgid = $this->e2gsnip_cfg['rgid'];
        $gpn = $this->e2gsnip_cfg['gpn'];
        $orderby = $this->e2gsnip_cfg['orderby'];
        $order = $this->e2gsnip_cfg['order'];
        $w = $this->e2gsnip_cfg['w'];
        $h = $this->e2gsnip_cfg['h'];
        $thq = $this->e2gsnip_cfg['thq'];
        $css = $this->e2gsnip_cfg['css'];
        $landingpage = $this->e2gsnip_cfg['landingpage'];
        $ss_indexfile = $this->e2gsnip_cfg['ss_indexfile'];
        $ss_w = $this->e2gsnip_cfg['ss_w'];
        $ss_h = $this->e2gsnip_cfg['ss_h'];
        $ss_bg = $this->e2gsnip_cfg['ss_bg'];
        $ss_allowedratio = $this->e2gsnip_cfg['ss_allowedratio'];
        $ss_limit = $this->e2gsnip_cfg['ss_limit'];
        $ss_config = $this->e2gsnip_cfg['ss_config'];
        $ss_css = $this->e2gsnip_cfg['ss_css'];
        $ss_js = $this->e2gsnip_cfg['ss_js'];

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
                $_ssfile['src'][] .= $this->_e2g_decode($gdir.$path.$fetch['filename']);
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
                $_ssfile['thumbsrc'][] .= $this->_get_thumb($gdir, $path.$fetch['filename'], $w, $h, $thq);
                $_ssfile['resizedimg'][] .= $this->_get_thumb($gdir, $path.$fetch['filename'], $ss_w, $ss_h, $thq);
                /*
                 * @todo: Making a work around if _get_thumb returns an empty result
                 */
            }
        }

        if (!empty($fid)) {
            $select = 'SELECT * FROM '.$modx->db->config['table_prefix'].'easy2_files '
                    . 'WHERE id IN ('.$fid.') '
                    . 'AND status = 1 '
            ;
            $query = mysql_query($select);
            if (!$query) {
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
                $_ssfile['src'][] .= $this->_e2g_decode($gdir.$path.$fetch['filename']);
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
                $_ssfile['thumbsrc'][] .= $this->_get_thumb($gdir, $path.$fetch['filename'], $w, $h, $thq);
                $_ssfile['resizedimg'][] .= $this->_get_thumb($gdir, $path.$fetch['filename'], $ss_w, $ss_h, $thq);
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
                $_ssfile['src'][] .= $this->_e2g_decode($gdir.$path.$fetch['filename']);
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
                $_ssfile['thumbsrc'][] .= $this->_get_thumb($gdir, $path.$fetch['filename'], $w, $h, $thq);
                $_ssfile['resizedimg'][] .= $this->_get_thumb($gdir, $path.$fetch['filename'], $ss_w, $ss_h, $thq);
            }
        }

        /*
         * Storing the slideshow size ratio
         */
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
        $count = count($_ssfile['src']);

        /*
         * added the &fid parameter inside the &slideshow, to open a full page of the clicked image.
         */
        if ( isset($_GET['fid']) && isset($landingpage) && $modx->documentIdentifier!=$landingpage ) {
            $modx->sendRedirect($modx->makeUrl($landingpage).'&fid='.$_GET['fid'].'&lp='.$landingpage);
        }
        elseif ( isset($_GET['fid']) && !isset($landingpage) ) {
            $modx->regClientCSS($css,'screen');
            $select = 'SELECT * FROM '.$modx->db->config['table_prefix'].'easy2_files '
                    . 'WHERE id = '.$_GET['fid'].' '
            ;
            $query = mysql_query($select);
            if (!$query) {
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
                $l['src'] = $this->_e2g_decode($gdir.$path.$fetch['filename']);
//                $l['src'] = $gdir.$path.$fetch['filename'];
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
            return $this->_filler( $this->_page_tpl(), $l );
        } else {
            // use custom index file if it's been called.
            if ( isset($ss_indexfile) && file_exists($ss_indexfile) ) {
                include($ss_indexfile);
            } elseif ( isset($ss_indexfile) && !file_exists($ss_indexfile) ) {
                $ss_display = 'slideshow index file <b>'.$ss_indexfile.'</b> is not found.';
            }
            // include the available slideshow file config
            elseif ( !isset($ss_indexfile) && !file_exists(E2G_SNIPPET_PATH.'slideshows/'.$slideshow.'/'.$slideshow.'.php')) {
                $ss_display = 'slideshow config for <b>'.$slideshow.'</b> is not found.';
            } else {
                include(E2G_SNIPPET_PATH.'slideshows/'.$slideshow.'/'.$slideshow.'.php');
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
    private function _landingpage($fileid) {
        global $modx;
        $landingpage = $this->e2gsnip_cfg['landingpage'];
        $gdir = $this->e2gsnip_cfg['gdir'];
        $plugins = $this->e2gsnip_cfg['plugins'];
        $css = $this->e2gsnip_cfg['css'];
        $js = $this->e2gsnip_cfg['js'];
        $ss_css = $this->e2gsnip_cfg['ss_css'];
        $ss_js = $this->e2gsnip_cfg['ss_js'];

        $modx->regClientCSS($css,'screen');
        if (!empty($js)) {
            $modx->regClientStartupScript($js);
        }
        
        $select = 'SELECT * FROM '.$modx->db->config['table_prefix'].'easy2_files '
                . 'WHERE id = '.$fileid
        ;
        $query = mysql_query($select);
        if (!$query) {
            return __LINE__.' : snippet calls wrong file id.';
        }

        while ($fetch = mysql_fetch_array($query)) {
            $path = $this->_get_path($fetch['dir_id']);
            if (count($path) > 1) {
                unset($path[1]);
                $path = implode('/', array_values($path)).'/';
            } else {
                $path = '';
            }
//            $l['src'] = $this->_e2g_decode($gdir.$path.$fetch['filename']);
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

            /*
             * insert plugins for THE IMAGE
             */
            if (isset($plugins) && preg_match('/landingpage:/', $plugins))
                $l['landingpageplugin'] = $this->_plugin('landingpage',$plugins,$fetch);
        }
        return $this->_filler( $this->_page_tpl(), $l );
    }

    // DIRECTORY TEMPLATE
    private function _dir_tpl() {
        global $modx;
        $dir_tpl = $this->e2gsnip_cfg['dir_tpl'];
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
        $thumb_tpl = $this->e2gsnip_cfg['thumb_tpl'];
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
        $tpl = $this->e2gsnip_cfg['tpl'];
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
        $rand_tpl = $this->e2gsnip_cfg['rand_tpl'];
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
    private function _page_tpl() {
        global $modx;
        $page_tpl = $this->e2gsnip_cfg['page_tpl'];
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

    /*
     * plugins interception for thumbnails
    */
    private function _plugin($target, $plugins, $row) {
        global $modx;
        // clear up
        $p_errs = array();
        if (isset($plugin_display)) unset($plugin_display);

        if (!isset($plugins)) {
            return 'Please make a plugin selection.';
        } else {
            $badchars = array('`',' ');
            $plugins = str_replace($badchars, '', trim($plugins));
            // get the plugins target: thumb:starrating,watermark | gallery:... | landingpage:...
            $xpldplugins = array();
            $xpldplugins = @explode('|', trim($plugins));
            // get the plugins' settings: starrating,watermark
            $p_category = array();

            foreach ($xpldplugins as $p_category) {
                $xpldsettings = @explode(':', trim($p_category));
                $p_target = $xpldsettings [0];
                $p_selections = $xpldsettings [1];

                if ($p_target == $target) { // if the snippet call == the function call
                    $xpldtypes = @explode(',', trim($p_selections));
                    foreach ( $xpldtypes as $p_type ) {
                        $xpldindexes = @explode('@', trim($p_type));
                        $p_name = $xpldindexes[0];
                        $p_indexfile = $xpldindexes[1];

                        // IMAGE / DIRECTORY ID HANDLER
                        if ($target=='thumb') $_plug['id']='fid_'.$row['id'];
                        if ($target=='gallery') $_plug['id']='gid_'.$row['cat_id'];
                        if ($target=='landingpage') $_plug['id']='fid_'.$row['id'];

                        // LOAD DA FILE!
                        if (($p_indexfile)!='') {
                            if (!file_exists($p_indexfile)) {
                                $p_errs[] = __LINE__.' : File <b>'.$p_indexfile .'</b> does not exist.';
                            } else include $p_indexfile;
                        } elseif (!file_exists( E2G_SNIPPET_PATH.'plugins/'.$p_name.'/'.$p_name.'.php' )) {
                            $p_errs[] = __LINE__.' : Plugin <b>'.$p_name .'</b> does not exist.';
                        } else {
                            include E2G_SNIPPET_PATH.'plugins/'.$p_name.'/'.$p_name.'.php';
                        }
                    } // foreach ( $xpldtypes as $p_type )
                } // if ($p_target == $target)
            } // foreach ($xpldplugins as $p_category)
            foreach ($p_errs as $p_err) {
                $_plug_displays[] = '<span style="color:black;">'.$p_err.'</span><br />';
            }
            $p_errs = array();
            unset($p_errs);
            
            // JOINING MANY PLUGINS RESULTS
            foreach ( $_plug_displays as $_play_display ) {
                $plugin_display .= $_play_display;
            }
            
            return $plugin_display;
        } // if (isset($plugins))
    }

    /*
     * UTF encoding work around
     */
    private function _e2g_encode($text) {
        $e2g_encode = $this->e2gsnip_cfg['e2g_encode'];
        if ($e2g_encode == 'none') {
            return $text;
        }
        if ($e2g_encode == 'UTF-8') {
            return utf8_encode($text);
        }
    }

    /*
     * UTF decoding work around
     */
    private function _e2g_decode($text) {
        $e2g_encode = $this->e2gsnip_cfg['e2g_encode'];
        if ($e2g_encode == 'none') {
            return $text;
        }
        if ($e2g_encode == 'UTF-8') {
            return utf8_decode($text);
        }
    }

    /*
     * CHECK THE REAL DESCENDANT OF gid ROOT
     */
    private function _check_gid_decendant($id,$static_id) {
        global $modx;

        $s = 'SELECT A.cat_id '
            . 'FROM '.$modx->db->config['table_prefix'].'easy2_dirs A, '
            . $modx->db->config['table_prefix'].'easy2_dirs B '
            . 'WHERE B.cat_id IN ('.$static_id.') '
            . 'AND A.cat_left BETWEEN B.cat_left AND B.cat_right '
        ;
        $q = mysql_query($s) or die(__LINE__.' : '.mysql_error().'<br />'.$s);
        while ($l = mysql_fetch_array($q, MYSQL_ASSOC)) {
            $check[$l['cat_id']] = $l['cat_id'];
        }

        $xpld_get_gids = explode(',', $id);
        foreach ($xpld_get_gids as $_id) {
            if ( !$check[$_id] && ($static_id!=1) ) {
                return $modx->sendUnauthorizedPage();
            } elseif (!$check[$_id] && ($static_id==1)) {
                return $modx->sendErrorPage();
            }
        }
    }

    /*
     * GET IDs OF &tags parameter
     */
    private function _tags_ids($dirorfile, $tags) {
        global $modx;

        if ($dirorfile=='dir') {
            $s = 'SELECT cat_id '
                . 'FROM '.$modx->db->config['table_prefix'].'easy2_dirs '
                . 'WHERE cat_tags LIKE \'%'.$tags.'%\' ';
            $tags_query = mysql_query($s) or die(__LINE__.': '.mysql_error().'<br />'.$s);
            while ($l = mysql_fetch_array($tags_query, MYSQL_ASSOC)) {
                $tags_dir[] = $l['cat_id'];
            }
            $tag_gids = implode(',',$tags_dir);
            return $tag_gids;
        }
        if ($dirorfile=='file') {
            $s = 'SELECT id '
                . 'FROM '.$modx->db->config['table_prefix'].'easy2_files '
                . 'WHERE (tags LIKE \'%'.$tags.'%\' ';
            $tags_query = mysql_query($s) or die(__LINE__.': '.mysql_error().'<br />'.$s);
            while ($l = mysql_fetch_array($tags_query, MYSQL_ASSOC)) {
                $tags_file[] = $l['id'];
            }
            $tag_ids = implode(',',$tags_file);
            return $tag_ids;
        }
    }
} // class e2g_snip
?>