<?php

//header('Content-Type: text/html; charset=UTF-8');
//set_ini('display_errors', '1');

/**
 * EASY 2 GALLERY
 * Gallery Snippet Class for Easy 2 Gallery Module for MODx Evolution
 * @author Cx2 <inteldesign@mail.ru>
 * @author Temus <temus3@gmail.com>
 * @author goldsky <goldsky@modx-id.com>
 * @version 1.4.0
 */
//require_once E2G_SNIPPET_PATH . 'includes/utf8/utf8.php';

class e2g_snip extends e2g_pub {

    /**
     * The snippet's configurations in an array
     * @var mixed all the snippet's parameters
     */
    public $e2gsnip_cfg = array();
    /**
     * The internal variables of this class
     * @var mixed all the processing variables
     */
    private $_e2g = array();

    public function __construct($e2gsnip_cfg) {
        $this->e2gsnip_cfg = $e2gsnip_cfg;
        $this->_e2g = $_e2g;
    }

    /**
     * The main function.
     * @global mixed $modx modx's API
     * @return mixed the function calls
     */
    public function display() {
        /**
         * 1. '&gid' : full gallery directory (directory - &gid - default)
         * 2. '&fid' : one file only (file - $fid)
         * 3. '&rgid' : random file in a directory (random - $rgid)
         * 4. '&slideshow' : slideshow by fid-s or rgid-s or gid-s
         */
        global $modx;
        $gid = $this->e2gsnip_cfg['gid']; // default
        $static_gid = $this->e2gsnip_cfg['static_gid'];
        $fid = $this->e2gsnip_cfg['fid'];
        $static_fid = $this->e2gsnip_cfg['static_fid'];
        $rgid = $this->e2gsnip_cfg['rgid'];
        $slideshow = $this->e2gsnip_cfg['slideshow'];
        $landingpage = $this->e2gsnip_cfg['landingpage'];

        if ($modx->documentIdentifier != $landingpage) { // to avoid gallery's thumbnails display on the landingpage's page
            if (!empty($fid) && !isset($slideshow)) {
                echo $this->_image_file();
            }
            if (!empty($rgid) && !isset($slideshow)) {
                echo $this->_random_image();
            }
            if (empty($fid) && empty($rgid) && !isset($slideshow)) {
                echo $this->_gallery(); // default
            }
        }
        if (!empty($slideshow)) {
            echo $this->_slideshow();
        }
        if (isset($landingpage) && isset($_GET['fid'])) {
            echo $this->_landing_page($_GET['fid']);
        }
    }

    /**
     * Full gallery execution
     * @global mixed $modx modx's API
     * @return mixed false/images delivered in template
     */
    private function _gallery() {
        global $modx;

        $gdir = $this->e2gsnip_cfg['gdir'];
        $gid = $this->e2gsnip_cfg['gid'];
        $static_gid = $this->e2gsnip_cfg['static_gid'];
        $e2g_instances = $this->e2gsnip_cfg['e2g_instances'];
        $e2g_static_instances = $this->e2gsnip_cfg['e2g_static_instances'];
        $e2g_wrapper = $this->e2gsnip_cfg['e2g_wrapper'];

        $tag = $this->e2gsnip_cfg['tag'];
        $static_tag = $this->e2gsnip_cfg['static_tag'];

        if ($this->e2gsnip_cfg['orderby'] == 'random') {
            $orderby = 'rand()';
            $order = '';
        } else {
            $orderby = $this->e2gsnip_cfg['orderby'];
            $order = $this->e2gsnip_cfg['order'];
        }
        if ($this->e2gsnip_cfg['cat_orderby'] == 'random') {
            $cat_orderby = 'rand()';
            $cat_order = '';
        } else {
            $cat_orderby = $this->e2gsnip_cfg['cat_orderby'];
            $cat_order = $this->e2gsnip_cfg['cat_order'];
        }

        $gpn = $this->e2gsnip_cfg['gpn'];
        $limit = $this->e2gsnip_cfg['limit'];

        $charset = $this->e2gsnip_cfg['charset'];
        $mbstring = $this->e2gsnip_cfg['mbstring'];
        $title_len = $this->e2gsnip_cfg['cat_name_len'];

//        $notables = $this->e2gsnip_cfg['notables']; // deprecated
        $grid = $this->e2gsnip_cfg['grid'];
        $grid_class = $this->e2gsnip_cfg['grid_class'];
        $crumbs_classCurrent = $this->e2gsnip_cfg['crumbs_classCurrent'];
        $back_class = $this->e2gsnip_cfg['back_class'];
        $pagenum_class = $this->e2gsnip_cfg['pagenum_class'];
        $colls = $this->e2gsnip_cfg['colls'];
        $img_src = $this->e2gsnip_cfg['img_src'];

        $showonly = $this->e2gsnip_cfg['showonly'];
        $customgetparams = $this->e2gsnip_cfg['customgetparams'];
        $gal_desc = $this->e2gsnip_cfg['gal_desc'];
        $landingpage = $this->e2gsnip_cfg['landingpage'];
        $plugin = $this->e2gsnip_cfg['plugin'];

        // CRUMBS
        $crumbs = $this->e2gsnip_cfg['crumbs'];
        $crumbs_use = $this->e2gsnip_cfg['crumbs_use'];
        $crumbs_separator = $this->e2gsnip_cfg['crumbs_separator'];
        $crumbs_showHome = $this->e2gsnip_cfg['crumbs_showHome'];
        $crumbs_showAsLinks = $this->e2gsnip_cfg['crumbs_showAsLinks'];
        $crumbs_showCurrent = $this->e2gsnip_cfg['crumbs_showCurrent'];
        $crumbs_showPrevious = $this->e2gsnip_cfg['crumbs_showPrevious'];

        // PAGINATION
        $pagination = $this->e2gsnip_cfg['pagination'];

        // EXECUTE THE JAVASCRIPT LIBRARY'S HEADERS
        $jslibs = $this->_libs();
        if ($jslibs === false)
            return 'Javascript library error.';


        //**********************************************************************/
        //*   PAGINATION FIXING for multiple snippet calls on the same page    */
        //**********************************************************************/
        // for the UNselected &gid snippet call when the other &gid snippet call is selected
        if (isset($static_gid)
                && isset($_GET['gid'])
                && $this->_check_gid_decendant($_GET['gid'], $static_gid) == false
                || $e2g_instances != $e2g_static_instances
        ) {
            $gpn = 0;
        }
        // for the UNselected &gid snippet call when &tag snippet call is selected
        if (isset($static_gid)
                && !isset($static_tag)
                && isset($_GET['tag'])
                || $e2g_instances != $e2g_static_instances
        ) {
            $gpn = 0;
        }
        // for the UNselected &tag snippet call when &gid snippet call is selected
        if (isset($static_tag)
                && !isset($_GET['tag'])
                && isset($_GET['gid'])
                || $e2g_instances != $e2g_static_instances
        ) {
            $gpn = 0;
        }
        // for the UNselected &tag snippet call when the other &tag snippet call is selected
        if (isset($static_tag)
                && $tag != $static_tag
                || $e2g_instances != $e2g_static_instances
        ) {
            $gpn = 0;
        }

        // FREEZING using plugin
        if ($e2g_instances != $e2g_static_instances) {
            $gid = $static_gid;
            $tag = $static_tag;
            $gpn = 0;
        }

        /**
         * PATHS
         */
        // NOT the $e2g config
        $_e2g = array('content' => '', 'pages' => '', 'parent_id' => 0, 'back' => '');

        if ($gal_desc != '1')
            $_e2g['desc_class'] = 'style="display:none;"';

        // START the grid
        $_e2g['content'] = (($grid == 'css') ? '<div class="' . $grid_class . '">' : '<table class="' . $grid_class . '"><tr>');

        //******************************************************************/
        //*                 COUNT DIRECTORY WITHOUT LIMIT!                 */
        //******************************************************************/
        // dir_count is used for pagination. random can not have this.
        if ($showonly == 'images' || $orderby == 'rand()' || $cat_orderby == 'rand()') {
            $dir_count = 0;
        } else {
            if (isset($static_tag)) {
                $select_count = 'SELECT COUNT(DISTINCT cat_id) '
                        . 'FROM ' . $modx->db->config['table_prefix'] . 'easy2_dirs ';

                // OPEN the selected tagged folder
                if (isset($_GET['gid'])
                        && $static_tag == $tag
                        && $this->_tags_ids('dir', $tag, $_GET['gid'])) {
                    $select_count .= 'WHERE parent_id IN (' . $_GET['gid'] . ')';
                } else {
                    // the selected tag of multiple tags on the same page
                    if ($static_tag == $tag) {
                        $multiple_tags = @explode(',', $tag);
                    }
                    // the UNselected tag of multiple tags on the same page
                    else {
                        $multiple_tags = @explode(',', $static_tag);
                    }

                    for ($i = 0; $i < count($multiple_tags); $i++) {
                        if ($i == 0)
                            $select_count .= 'WHERE cat_tag LIKE \'%' . $multiple_tags[$i] . '%\' ';
                        else
                            $select_count .= 'OR cat_tag LIKE \'%' . $multiple_tags[$i] . '%\' ';
                    }
                }
            }
            // original &gid parameter
            else {
                $select_count = 'SELECT COUNT(DISTINCT d.cat_id) '
                        . 'FROM ' . $modx->db->config['table_prefix'] . 'easy2_dirs AS d ';

                if ($this->_check_gid_decendant((isset($_GET['gid']) ? $_GET['gid'] : $gid), $static_gid) == true) {
                    $select_count .= 'WHERE d.parent_id IN (' . $gid . ') ';
                } else {
                    $select_count .= 'WHERE d.parent_id IN (' . $static_gid . ') ';
                }

                $select_count .= 'AND d.cat_visible = 1 '
                        // ddim -- wrapping children folders
                        . 'AND (SELECT count(*) FROM ' . $modx->db->config['table_prefix'] . 'easy2_files F '
                        . 'WHERE F.dir_id in '
                        . '(SELECT A.cat_id FROM ' . $modx->db->config['table_prefix'] . 'easy2_dirs A, '
                        . $modx->db->config['table_prefix'] . 'easy2_dirs B '
                        . 'WHERE (B.cat_id=d.cat_id '
                        . 'AND A.cat_left >= B.cat_left '
                        . 'AND A.cat_right <= B.cat_right '
                        . 'AND A.cat_level >= B.cat_level '
                        . 'AND A.cat_visible = 1)'
                        . ')'
                        . ')<>0 ';
            }

            $dir_count = mysql_result(mysql_query($select_count), 0, 0);

            /**
             * Add the multiple IDs capability into the &gid
             * Check the valid params of each of snippet calls
             */
            if ($this->_check_gid_decendant((isset($_GET['gid']) ? $_GET['gid'] : $gid), $static_gid) == true) {
                $multiple_gids = explode(',', $gid);
            } else {
                $multiple_gids = explode(',', $static_gid);
            }

            $multiple_gids_count = count($multiple_gids);
            // reset the directory number
            $dir_num_rows = 0;
            unset($single_gid);
            foreach ($multiple_gids as $single_gid) {
                // get path from the $gid
                $path = $this->_get_path($single_gid);
                $crumbs_path = $this->_get_path($single_gid, $crumbs_use);

                // To limit the CRUMBS paths.
                if (($static_gid != '1') && !empty($crumbs_path) && !isset($tag)) {
                    $static_path = $this->_get_path($static_gid);
                    if (!$crumbs_showPrevious) {
                        $crumbs_path = array_slice($crumbs_path, (count($static_path) - 2), null, true);
                    }
                }

                // get "category name" from $path
                $_e2g['cat_name'] = is_array($path) ? end($path) : '';

                //******************************************************************/
                //*                             CRUMBS                             */
                //******************************************************************/
                // reset crumbs
                $breadcrumbs = '';

                /**
                 * Only use crumbs if it is a single gid.
                 * Otherwise, how can we make crumbs for merging directories in 1 page?
                 */
                if (isset($static_tag) && !$this->_tags_ids('dir', $static_tag, $single_gid))
                    continue;
                if ($multiple_gids_count == 1
                        && $crumbs == 1
                ) {
                    // if path more the none
                    if (count($crumbs_path) > 0) {
                        end($crumbs_path);
                        prev($crumbs_path);
                        $_e2g['parent_id'] = key($crumbs_path);
                        $_e2g['parent_name'] = $crumbs_path[$_e2g['parent_id']];

                        // create crumbs
                        $cnt = 0;
                        foreach ($crumbs_path as $k => $v) {
                            $cnt++;
                            if ($cnt == 1 && !$crumbs_showHome) {
                                continue;
                            }
                            if ($cnt == count($crumbs_path) && !$crumbs_showCurrent) {
                                continue;
                            }

                            if ($cnt != count($crumbs_path))
                                $breadcrumbs .= $crumbs_separator . ($crumbs_showAsLinks ?
                                                '<a href="'
                                                // making flexible FURL or not
                                                . $modx->makeUrl($modx->documentIdentifier
                                                        , $modx->aliases
                                                        , 'sid=' . $e2g_static_instances)
                                                . '&amp;gid=' . $k
                                                . '#' . $e2g_static_instances . '_' . $k
                                                . '">' . $v . '</a>' : $v);
                            else
                                $breadcrumbs .= $crumbs_separator . '<span class="' . $crumbs_classCurrent . '">' . $v . '</span>';
                        }
                        $breadcrumbs = substr_replace($breadcrumbs, '', 0, strlen($crumbs_separator));

                        // unset Easy 2-$crumbs_path value
                        unset($crumbs_path[1]);

                        // joining many of directory paths
                        $crumbs_path = implode('/', array_values($crumbs_path)) . '/';
                    } else { // if not many, path is set as empty
                        $crumbs_path = '';
                    } // if (count($path) > 1)
                    $_e2g['crumbs'] = $breadcrumbs;
                }
            }

            //******************************************************************/
            //*                 FOLDERS/DIRECTORIES/GALLERIES                  */
            //******************************************************************/
            if ($showonly != 'images') {
                // if &tag is set
                if (isset($static_tag)) {
                    $dir_select = 'SELECT * FROM ' . $modx->db->config['table_prefix'] . 'easy2_dirs ';

                    // OPEN the selected tagged folder
                    if (isset($_GET['gid'])
                            && $static_tag == $tag
                            && $this->_tags_ids('dir', $tag, $_GET['gid'])) {
                        $dir_select .= 'WHERE parent_id IN (' . $_GET['gid'] . ')';
                    } else {
                        // the selected tag of multiple tags on the same page
                        if ($static_tag == $tag) {
                            $multiple_tags = @explode(',', $tag);
                        }
                        // the UNselected tag of multiple tags on the same page
                        else {
                            $multiple_tags = @explode(',', $static_tag);
                        }

                        for ($i = 0; $i < count($multiple_tags); $i++) {
                            if ($i == 0)
                                $dir_select .= 'WHERE cat_tag LIKE \'%' . $multiple_tags[$i] . '%\' ';
                            else
                                $dir_select .= 'OR cat_tag LIKE \'%' . $multiple_tags[$i] . '%\' ';
                        }
                    }

                    $dir_select .= 'AND cat_visible = 1 '
                            . 'ORDER BY ' . $cat_orderby . ' ' . $cat_order . ' ';

                    // to separate the multiple &gid snippet parameters on the same page
                    $dir_select .= ( $_GET['tag'] == $static_tag) ? 'LIMIT ' . ( $gpn * $limit ) . ', ' . $limit : 'LIMIT 0, ' . $limit;
                }
                // original &gid parameter
                else {
                    $dir_select = 'SELECT DISTINCT d.* '
                            . 'FROM ' . $modx->db->config['table_prefix'] . 'easy2_dirs AS d ';

                    if ($this->_check_gid_decendant((isset($_GET['gid']) ? $_GET['gid'] : $gid), $static_gid) == true) {
                        $dir_select .= 'WHERE d.parent_id IN (' . $gid . ') ';
                    } else {
                        $dir_select .= 'WHERE d.parent_id IN (' . $static_gid . ') ';
                    }

                    // ddim -- http://modxcms.com/forums/index.php/topic,48314.msg286241.html#msg286241
                    $dir_select .= 'AND d.cat_visible = 1 '
                            . 'AND ('
                            . 'SELECT count(*) FROM ' . $modx->db->config['table_prefix'] . 'easy2_files F '
                            . 'WHERE F.dir_id IN ('
                            . 'SELECT A.cat_id FROM ' . $modx->db->config['table_prefix'] . 'easy2_dirs A, '
                            . $modx->db->config['table_prefix'] . 'easy2_dirs B '
                            . 'WHERE ('
                            . 'B.cat_id=d.cat_id '
                            . 'AND A.cat_left >= B.cat_left '
                            . 'AND A.cat_right <= B.cat_right '
                            . 'AND A.cat_level >= B.cat_level '
                            . 'AND A.cat_visible = 1'
                            . ')'
                            . ')'
                            . ')<>0 '
                            . 'ORDER BY ' . $cat_orderby . ' ' . $cat_order . ' ';

                    $dir_select .= 'LIMIT ' . ( $gpn * $limit ) . ', ' . $limit;

//                    // to separate the multiple &gid snippet parameters on the same page
//                    if ($this->_check_gid_decendant( (isset($_GET['gid'])? $_GET['gid'] : $gid) , $static_gid)==true) {
//                        $dir_select .= 'LIMIT ' . ( $gpn * $limit ) . ', ' . $limit;
//                    }
//                    else {
//                        $dir_select .= 'LIMIT 0, ' . $limit;
//                    }
                }

                $dir_query = mysql_query($dir_select);
                if (!$dir_query)
                    die(__LINE__ . ' : ' . mysql_error() . '<br />' . $dir_select);
                $dir_num_rows += mysql_num_rows($dir_query);

                // gallery's permalink
                if (isset($tag)
                        && ($this->_check_gid_decendant((isset($_GET['gid']) ? $_GET['gid'] : $gid), $static_gid) == true)
                ) {
                    $_e2g['permalink'] = '<a href="#" name="' . $e2g_static_instances . '_' . $static_tag . '"></a>';
                } elseif ($this->_check_gid_decendant((isset($_GET['gid']) ? $_GET['gid'] : $gid), $static_gid) == false) {
                    $_e2g['permalink'] = '<a href="#" name="' . $e2g_static_instances . '_' . $static_gid . '"></a>';
                } else {
                    $_e2g['permalink'] = '<a href="#" name="' . $e2g_static_instances . '_' . $gid . '"></a>';
                }

                // gallery's description
                if ($gal_desc == '1'
                        // exclude the multiple gids (comma separated)
                        && !strpos($static_gid, ',')
                ) {
                    $gallery_id = '';
                    if ($this->_check_gid_decendant((isset($_GET['gid']) ? $_GET['gid'] : $gid), $static_gid) == false) {
                        $gallery_id = $static_gid;
                    } else {
                        $gallery_id = $single_gid;
                    }

                    $_e2g['cat_description'] = $this->_get_dir_info($gallery_id, 'cat_description');
                    $_e2g['cat_title'] = $this->_get_dir_info($gallery_id, 'cat_alias');

                    $_e2g['title'] = ($_e2g['cat_title'] != '' ? $_e2g['cat_title'] : $_e2g['cat_name'] );
                    if ($_e2g['title'] == '' && $_e2g['cat_description'] == '') {
                        $_e2g['desc_class'] = 'style="display:none;"';
                    }
                } else {
                    $_e2g['desc_class'] = 'style="display:none;"';
                } // gallery's description

                $i = 0;
                while ($l = mysql_fetch_array($dir_query, MYSQL_ASSOC)) {
                    if (isset($static_tag))
                        $l['permalink'] = $e2g_static_instances . '_' . $static_tag;
                    else
                        $l['permalink'] = $e2g_static_instances . '_' . $l['cat_id'];

                    if (isset($tag)) {
                        $l['cat_tag'] = '&amp;tag=' . $static_tag;
                    } else {
                        $l['cat_tag'] = '';
                    }

                    // search image for subdir
                    $l1 = $this->_get_folder_img($l['cat_id']);
                    // if there is an empty folder, or invalid content
                    if (!$l1)
                        continue;

                    $l['count'] = $l1['count'];

                    // path to subdir's thumbnail
                    $path1 = $this->_get_path($l1['dir_id']);

                    // if path is more than one
                    if (count($path1) > 1) {
                        unset($path1[1]); // unset the 'Easy 2' root path only
                        $path1 = implode('/', array_values($path1)) . '/';
                    }
                    // if path is not many
                    else {
                        $path1 = '';
                    }

                    // Populate the grid with folder's thumbnails
                    if ($dir_num_rows > 0
                            && ( $i > 0 )
                            && ( $i % $colls == 0 )
                            && $grid == 'table'
                    ) {
                        $_e2g['content'] .= '</tr><tr>';
                    }

                    $l['title'] = ( $l['cat_alias'] != '' ? $l['cat_alias'] : $l['cat_name'] );
                    if ($l['title'] == '')
                        $l['title'] = '&nbsp;';
                    elseif ($mbstring) {
                        if (mb_strlen($l['title'], $charset) > $title_len)
                            $l['title'] = mb_substr($l['title'], 0, $title_len - 1, $charset) . '...';
                    }
                    elseif (strlen($l['title']) > $title_len)
                        $l['title'] = substr($l['title'], 0, $title_len - 1) . '...';

                    /**
                     * insert plugin for each gallery
                     */
                    if (isset($plugin) && preg_match('/gallery:/', $plugin))
                        $l['galleryplugin'] = $this->_plugin('gallery', $plugin, $l);

                    $l['w'] = $this->e2gsnip_cfg['w'];
                    $l['h'] = $this->e2gsnip_cfg['h'];
                    $thq = $this->e2gsnip_cfg['thq'];

                    $l['src'] = $this->_get_thumb($gdir, $path1 . $l1['filename'], $l['w'], $l['h'], $thq);

                    // making flexible FURL or not
                    $l['link'] = $modx->makeUrl($modx->documentIdentifier
                                    , $modx->aliases
                                    , 'sid=' . $e2g_static_instances) . '&amp;gid='
                    ;

                    // fill up the dir list with content
                    $_e2g['content'] .= ( ($grid == 'css') ? $this->_filler($this->_dir_tpl(), $l) : '<td>' . $this->_filler($this->_dir_tpl(), $l) . '</td>');
                    $i++;
                } // while ($l = mysql_fetch_array($dir_query, MYSQL_ASSOC))
            }
        }

        //******************************************************************/
        //*             FILE content for the current directory             */
        //******************************************************************/

        if ($dir_num_rows != $limit
                && $showonly != 'folders'
                && !empty($gid)
        ) {

            /**
             * goldsky -- manage the pagination limit between dirs and files
             * (join the pagination AND the table grid).
             */
            $modulus_dir_count = $dir_count % $limit;
            $file_thumb_offset = $limit - $modulus_dir_count;
            $file_page_offset = ceil($dir_count / $limit);

            if (isset($static_tag)) {
                $file_select = 'SELECT * FROM ' . $modx->db->config['table_prefix'] . 'easy2_files ';

                // OPEN the selected tagged folder
                if (isset($_GET['gid'])
                        && $static_tag == $tag
                        && $this->_tags_ids('dir', $tag, $_GET['gid'])
                ) {
                    $file_select .= 'WHERE dir_id IN (' . $_GET['gid'] . ')';
                } else {
                    // the selected tag of multiple tags on the same page
                    if ($static_tag == $tag) {
                        $multiple_tags = @explode(',', $tag);
                    }
                    // the UNselected tag of multiple tags on the same page
                    else {
                        $multiple_tags = @explode(',', $static_tag);
                    }

                    for ($i = 0; $i < count($multiple_tags); $i++) {
                        if ($i == 0)
                            $file_select .= 'WHERE tag LIKE \'%' . $multiple_tags[$i] . '%\' ';
                        else
                            $file_select .= 'OR tag LIKE \'%' . $multiple_tags[$i] . '%\' ';
                    }
                }
                $file_select .= 'AND status = 1 '
                        . 'ORDER BY ' . $orderby . ' ' . $order . ' '
                ;
            }
            // exclude &tag snippet call, for the &gid parameter
            else {
                $file_select = 'SELECT * FROM ' . $modx->db->config['table_prefix'] . 'easy2_files ';

                if ($this->_check_gid_decendant((isset($_GET['gid']) ? $_GET['gid'] : $gid), $static_gid) == true) {
                    $file_select .= 'WHERE dir_id IN (' . $gid . ') ';
                } else {
                    $file_select .= 'WHERE dir_id IN (' . $static_gid . ') ';
                }

                $file_select .= 'AND status = 1 '
                        . 'ORDER BY ' . $orderby . ' ' . $order . ' '
                ;
            }

            /**
             * Calculate the available grid to be floated
             */
            if ($file_thumb_offset > 0 && $file_thumb_offset < $limit) {
                $file_select .= 'LIMIT '
                        . ( $dir_num_rows > 0 ?
                                ( ' 0, ' . ( $file_thumb_offset ) ) :
                                ( ( ( $gpn - $file_page_offset) * $limit) + $file_thumb_offset ) . ', ' . $limit );
            } elseif ($file_thumb_offset != 0 || $file_thumb_offset == $limit) {
                $file_select .= 'LIMIT '
                        . ( $modulus_dir_count > 0 ?
                                ( ' 0, ' . ( $file_thumb_offset ) ) :
                                ( ( ( $gpn - $file_page_offset) * $limit) ) . ', ' . $limit );
            } else { // $file_thumb_offset == 0 --> No sub directory
                $file_select .= 'LIMIT ' . ( $gpn * $limit) . ', ' . $limit;
            }

            $file_query_result = mysql_query($file_select) or die(__LINE__ . ' : ' . mysql_error() . '<br />' . $file_select);
            $file_num_rows = mysql_num_rows($file_query_result);

            /**
             * retrieve the content
             */
            $i = 0;

            // checking the $dir_num_rows first
            if ($dir_num_rows > 0
                    && $dir_num_rows % $colls == 0
                    && $grid == 'table'
            ) {
                $_e2g['content'] .= '</tr><tr>';
            }

            while ($l = mysql_fetch_array($file_query_result, MYSQL_ASSOC)) {
                /**
                 * whether configuration setting is set with or without table, the template will adjust it
                 * goldsky -- this is where the file's thumb 'float' to the dirs' in TABLE grid
                 */
                if (( $i > 0 )
                        && ( ( $i + $dir_num_rows ) % $colls == 0 )
                        && $grid == 'table') {
                    $_e2g['content'] .= '</tr><tr>';
                }

                /**
                 * insert plugin for each thumb
                 */
                if (isset($plugin) && preg_match('/thumb:/', $plugin))
                    $l['thumbplugin'] = $this->_plugin('thumb', $plugin, $l);

                $l['w'] = $this->e2gsnip_cfg['w'];
                $l['h'] = $this->e2gsnip_cfg['h'];

                if (isset($landingpage)) {
                    $l['link'] = $modx->makeUrl($landingpage
                                    , $modx->aliases
                                    , 'lp=' . $landingpage) . '&amp;fid=' . $l['id']
                    ;
                } else {
                    if ($img_src == 'generated') {
                        $l['link'] = 'assets/modules/easy2/show.easy2gallery.php?fid=' . $l['id'];
                    } elseif ($img_src == 'original') {

                        // path to subdir's thumbnail
                        $path = $this->_get_path($l['dir_id']);

                        // if path is more than one
                        if (count($path) > 1) {
                            unset($path[1]); // unset the 'Easy 2' root path only
                            $path = implode('/', array_values($path)) . '/';
                        }
                        // if path is not many
                        else {
                            $path = '';
                        }

                        $l['link'] = $gdir . $path . $l['filename'];
                    }
                } // if ( isset($landingpage) )

                if ($l['description'] != '') {
                    $l['description'] = htmlspecialchars_decode(htmlspecialchars_decode($l['description'], ENT_QUOTES), ENT_QUOTES);
                }

                // whether configuration setting is set with or without table, the template will adjust it
                $_e2g['content'] .= ( ($grid == 'css') ? $this->_filler($this->_thumb_tpl(), $this->_thumb_libs($l)) : '<td>' . $this->_filler($this->_thumb_tpl(), $this->_thumb_libs($l)) . '</td>');
                $i++;
            } // while ($l = @mysql_fetch_array($file_query_result, MYSQL_ASSOC))
        } // if( $dir_num_rows!=$limit && $showonly!='folders' && !empty($gid) )

        $_e2g['content'] .= ( ($grid == 'css') ? '</div>' : '</tr></table>');

        //******************************************************************/
        //*                          BACK BUTTON                           */
        //******************************************************************/
        if ($_e2g['parent_id'] > 0
                && ($this->_check_gid_decendant((isset($_GET['gid']) ? $_GET['gid'] : $gid), $static_gid) == true)
                && (isset($static_tag) ? $static_tag == $tag : null)
        ) {
            $_e2g['back'] = '<span class="' . $back_class . '">&laquo; <a href="'
                    // making flexible FURL or not
                    . $modx->makeUrl($modx->documentIdentifier
                            , $modx->aliases
                            , 'sid=' . $e2g_static_instances)
                    . '&amp;gid=' . $_e2g['parent_id']
                    . (isset($static_tag) ? '&amp;tag=' . $static_tag : '' )
                    . '#' . $e2g_static_instances . '_'
                    . (isset($static_tag) ? $static_tag : $_e2g['parent_id'] )
                    . '">' . $_e2g['parent_name'] . '</a></p>';
        }

        //**********************************************************************/
        //*                       PAGINATION: PAGE LINKS                       */
        //*             joining between dirs and files pagination              */
        //**********************************************************************/
        if ($pagination == 1) {
            // count the files again, this time WITHOUT limit!
            if ($showonly == 'folders' || $orderby == 'rand()' || $cat_orderby == 'rand()') {
                $file_count = 0;
            } elseif (!empty($gid)) {
                if (isset($static_tag)) {
                    $file_count_select = 'SELECT COUNT(id) FROM ' . $modx->db->config['table_prefix'] . 'easy2_files ';

                    // OPEN the selected tagged folder
                    if (isset($_GET['gid'])
                            && $static_tag == $tag
                            && $this->_tags_ids('dir', $tag, $_GET['gid'])) {
                        $file_count_select .= 'WHERE dir_id IN (' . $_GET['gid'] . ')';
                    } else {
                        // the selected tag of multiple tags on the same page
                        if ($static_tag == $tag) {
                            $multiple_tags = @explode(',', $tag);
                        }
                        // the UNselected tag of multiple tags on the same page
                        else {
                            $multiple_tags = @explode(',', $static_tag);
                        }

                        for ($i = 0; $i < count($multiple_tags); $i++) {
                            if ($i == 0)
                                $file_count_select .= 'WHERE tag LIKE \'%' . $multiple_tags[$i] . '%\' ';
                            else
                                $file_count_select .= 'OR tag LIKE \'%' . $multiple_tags[$i] . '%\' ';
                        }
                    }
                }
                // default
                else {
                    $file_count_select = 'SELECT COUNT(id) FROM '
                            . $modx->db->config['table_prefix'] . 'easy2_files ';
                    if ($this->_check_gid_decendant((isset($_GET['gid']) ? $_GET['gid'] : $gid), $static_gid) == true) {
                        $file_count_select .= 'WHERE dir_id IN (' . $gid . ') ';
                    } else {
                        $file_count_select .= 'WHERE dir_id IN (' . $static_gid . ') ';
                    }
                }

                $file_count_query = mysql_query($file_count_select) or die(__LINE__ . ' : ' . mysql_error() . '<br />' . $file_count_select);
                $file_count = mysql_result($file_count_query, 0, 0);
            }

            $total_count = $dir_count + $file_count;

            if ($total_count > $limit) {
                $_e2g['pages'] = '<div class="' . $pagenum_class . '">';
                $i = 0;
                while ($i * $limit < $total_count) {
                    // using &tag parameter
                    if (isset($static_tag)) {
                        if ($i == $gpn) {
                            $_e2g['pages'] .= '<b>' . ($i + 1) . '</b> ';
                        } else {
                            $_e2g['pages'] .= '<a href="'
                                    // making flexible FURL or not
                                    . $modx->makeUrl($modx->documentIdentifier
                                            , $modx->aliases
                                            , 'sid=' . $e2g_static_instances)
                                    . '&amp;tag=' . $static_tag
                                    . ( isset($_GET['gid']) ? '&amp;gid=' . $_GET['gid'] : '' )
                                    . '&amp;gpn=' . $i . $customgetparams
                                    . '#' . $e2g_static_instances . '_' . $static_tag
                                    . '">' . ($i + 1) . '</a> ';
                        }
                    }
                    // original &gid parameter
                    else {
                        if ($i == $gpn) {
                            $_e2g['pages'] .= '<b>' . ($i + 1) . '</b> ';
                        } else {
                            $_e2g['pages'] .= '<a href="'
                                    // making flexible FURL or not
                                    . $modx->makeUrl($modx->documentIdentifier
                                            , $modx->aliases
                                            , 'sid=' . $e2g_static_instances)
                                    . ( ( isset($static_gid)
                                    && ( $this->_check_gid_decendant((isset($_GET['gid']) ? $_GET['gid'] : $gid), $static_gid) == true ) ) ? '&amp;gid=' . $gid : '&amp;gid=' . $static_gid )
                                    . '&amp;gpn=' . $i
                                    . $customgetparams
                                    . '#' . $e2g_static_instances . '_'
                                    . ( ( isset($static_gid)
                                    && ( $this->_check_gid_decendant((isset($_GET['gid']) ? $_GET['gid'] : $gid), $static_gid) == true ) ) ? $gid : $static_gid )
                                    . '">' . ($i + 1) . '</a> ';
                        }
                    }
                    $i++;
                }
                $_e2g['pages'] .= '</div>';
            }
        }

        // Gallery's wrapper ID
        $_e2g['wrapper'] = $e2g_wrapper;
        
        // MULTIPLE INSTANCES id
        $_e2g['sid'] = $e2g_static_instances;

        return $this->_filler($this->_gal_tpl(), $_e2g);
    }

    /**
     * Gallery for &fid parameter
     * @global mixed $modx modx's API
     * @return mixed the image's thumbail delivered in template
     */
    private function _image_file() {
        global $modx;
        $fid = $this->e2gsnip_cfg['fid'];
        $colls = $this->e2gsnip_cfg['colls'];
//        $notables = $this->e2gsnip_cfg['notables']; // deprecated
        $grid = $this->e2gsnip_cfg['grid'];
        $grid_class = $this->e2gsnip_cfg['grid_class'];
        $landingpage = $this->e2gsnip_cfg['landingpage'];
        $plugin = $this->e2gsnip_cfg['plugin'];
        $img_src = $this->e2gsnip_cfg['img_src'];
        $e2g_wrapper = $this->e2gsnip_cfg['e2g_wrapper'];

        $file_select = 'SELECT * FROM ' . $modx->db->config['table_prefix'] . 'easy2_files '
                . 'WHERE id IN (' . $fid . ') '
                . 'AND status = 1 ';
        $res = mysql_query($file_select) or die(__LINE__ . ' : ' . mysql_error() . '<br />' . $file_select);

        // just to hide gallery's description CSS box in gallery template
        if (!isset($_e2g['title']) || !isset($_e2g['cat_description'])) {
            $_e2g['desc_class'] = 'style="display:none;"';
        } else
            $_e2g['e2gdir_class'] = '';

        // START the grid
        $_e2g['content'] .= ( ($grid == 'css') ? '<div class="' . $grid_class . '">' : '<table class="' . $grid_class . '"><tr>');

        $this->_libs();
        $i = 0;
        while ($l = mysql_fetch_array($res, MYSQL_ASSOC)) {
            // create row grid
            if (( $i > 0 ) && ( $i % $colls == 0 ) && $grid == 'table')
                $_e2g['content'] .= '</tr><tr>';

            $l['w'] = $this->e2gsnip_cfg['w'];
            $l['h'] = $this->e2gsnip_cfg['h'];

            /**
             * insert plugin for each thumb
             */
            if (isset($plugin) && preg_match('/thumb:/', $plugin))
                $l['thumbplugin'] = $this->_plugin('thumb', $plugin, $l);

            if (isset($landingpage)) {
                $l['link'] = $modx->makeUrl($landingpage
                                , $modx->aliases
                                , 'lp=' . $landingpage) . '&amp;fid=' . $l['id']
                ;
            } else {
                if ($img_src == 'generated') {
                    $l['link'] = 'assets/modules/easy2/show.easy2gallery.php?fid=' . $l['id'];
                } elseif ($img_src == 'original') {

                    // path to subdir's thumbnail
                    $path = $this->_get_path($l['dir_id']);

                    // if path is more than one
                    if (count($path) > 1) {
                        unset($path[1]); // unset the 'Easy 2' root path only
                        $path = implode('/', array_values($path)) . '/';
                    }
                    // if path is not many
                    else {
                        $path = '';
                    }

                    $l['link'] = $gdir . $path . $l['filename'];
                }
            }
            // whether configuration setting is set with or without table, the template will adjust it
            $_e2g['content'] .= ( ($grid == 'css') ? $this->_filler($this->_thumb_tpl(), $this->_thumb_libs($l)) : '<td>' . $this->_filler($this->_thumb_tpl(), $this->_thumb_libs($l)) . '</td>');
            $i++;
        }

        // Gallery's wrapper ID
        $_e2g['wrapper'] = $e2g_wrapper;
        
        // END the grid
        $_e2g['content'] .= ( ($grid == 'css') ? '</div>' : '</tr></table>');

        return $this->_filler($this->_gal_tpl(), $_e2g);
    }

    /**
     * To create a random image
     * @global mixed $modx modx's API
     * @return mixed the image's thumbail delivered in template
     */
    private function _random_image() {
        global $modx;
        $limit = $this->e2gsnip_cfg['limit'];
        $rgid = $this->e2gsnip_cfg['rgid'];
//        $notables = $this->e2gsnip_cfg['notables'];  // deprecated
        $grid = $this->e2gsnip_cfg['grid'];
        $grid_class = $this->e2gsnip_cfg['grid_class'];
        $landingpage = $this->e2gsnip_cfg['landingpage'];
        $plugin = $this->e2gsnip_cfg['plugin'];
        $img_src = $this->e2gsnip_cfg['img_src'];
        $e2g_wrapper = $this->e2gsnip_cfg['e2g_wrapper'];

        $q = 'SELECT * FROM ' . $modx->db->config['table_prefix'] . 'easy2_files '
                . 'WHERE status = 1 '
                . 'AND dir_id IN (' . $rgid . ') '
                . 'ORDER BY RAND() '
                . 'LIMIT 1'
        ;

        $res = mysql_query($q);
        $num_rows = mysql_num_rows($res);
        if (!$num_rows)
            return;

        // START the grid
        $_e2g['content'] .= ( ($grid == 'css') ? '<div class="' . $grid_class . '">' : '<table class="' . $grid_class . '"><tr>');

        while ($l = mysql_fetch_array($res, MYSQL_ASSOC)) {
            // just to hide gallery's description CSS box in gallery template
            if (!isset($_e2g['title']) || !isset($_e2g['cat_description'])) {
                $_e2g['desc_class'] = 'style="display:none;"';
            } else
                $_e2g['e2gdir_class'] = '';


            $l['w'] = $this->e2gsnip_cfg['w'];
            $l['h'] = $this->e2gsnip_cfg['h'];

            $this->_libs();

            /**
             * insert plugin for each thumb
             */
            if (isset($plugin) && preg_match('/thumb:/', $plugin))
                $l['thumbplugin'] = $this->_plugin('thumb', $plugin, $l);

            if (isset($landingpage)) {
                $l['link'] = $modx->makeUrl($landingpage
                                , $modx->aliases
                                , 'lp=' . $landingpage) . '&amp;fid=' . $l['id']
                ;
            } else {
                if ($img_src == 'generated') {
                    $l['link'] = 'assets/modules/easy2/show.easy2gallery.php?fid=' . $l['id'];
                } elseif ($img_src == 'original') {

                    // path to subdir's thumbnail
                    $path = $this->_get_path($l['dir_id']);

                    // if path is more than one
                    if (count($path) > 1) {
                        unset($path[1]); // unset the 'Easy 2' root path only
                        $path = implode('/', array_values($path)) . '/';
                    }
                    // if path is not many
                    else {
                        $path = '';
                    }

                    $l['link'] = $gdir . $path . $l['filename'];
                }
            }

            $_e2g['content'] .= ( ($grid == 'css') ? $this->_filler($this->_random_tpl(), $this->_thumb_libs($l)) : '<td>' . $this->_filler($this->_random_tpl(), $this->_thumb_libs($l)) . '</td>');
        }

        // Gallery's wrapper ID
        $_e2g['wrapper'] = $e2g_wrapper;
        
        // END the grid
        $_e2g['content'] .= ( ($grid == 'css') ? '</div>' : '</tr></table>');

        return $this->_filler($this->_gal_tpl(), $_e2g);
    }

    /**
     * To get and create thumbnails
     * @param  int    $gdir        from $_GET['gid']
     * @param  string $path        directory path of each of thumbnail
     * @param  int    $w           thumbnail width
     * @param  int    $h           thumbnail height
     * @param  int    $thq         thumbnail quality
     * @param  string $resize_type 'inner' | 'resize'
     *                            'inner' = crop the thumbnail
     *                            'resize' = autofit the thumbnail
     * @param  int    $red         Red in RGB
     * @param  int    $green       Green in RGB
     * @param  int    $blue        Blue in RGB
     * @return mixed false/the thumbail's path
     */
    private function _get_thumb($gdir, $path, $w, $h, $thq, $resize_type=null, $red=null, $green=null, $blue=null, $wmtrigger = 0) {
        global $modx;
        // decoding UTF-8
        $gdir = $this->_e2g_decode($gdir);
        $path = $this->_e2g_decode($path);
        if (empty($path))
            return false;

        $w = !empty($w) ? $w : $this->e2gsnip_cfg['w'];
        $h = !empty($h) ? $h : $this->e2gsnip_cfg['h'];
        $thq = !empty($thq) ? $thq : $this->e2gsnip_cfg['thq'];
        $resize_type = isset($resize_type) ? $resize_type : $this->e2gsnip_cfg['resize_type'];
        $red = isset($red) ? $red : $this->e2gsnip_cfg['thbg_red'];
        $green = isset($green) ? $green : $this->e2gsnip_cfg['thbg_green'];
        $blue = isset($blue) ? $blue : $this->e2gsnip_cfg['thbg_blue'];

//        $thumb_path = '_thumbnails/'.substr($path, 0, strrpos($path, '.')).'_'.$w.'x'.$h.'.jpg';
        /**
         * Use document ID and session ID to separate between different snippet calls
         * on the same/different page(s) with different settings
         * but unfortunately with the same dimension.
         */
        $e2g_static_instances = $this->e2gsnip_cfg['e2g_static_instances'];
        $docid = $modx->documentIdentifier;
        $thumb_path = '_thumbnails/' . substr($path, 0, strrpos($path, '.')) . '_id' . $docid . '_sid' . $e2g_static_instances . '_' . $w . 'x' . $h . '.jpg';

        /**
         * CREATE THUMBNAIL
         */
        // goldsky -- alter the maximum execution time
        set_time_limit(0);

        if (!file_exists($gdir . $thumb_path) && file_exists($gdir . $path)) {
            // goldsky -- adds output buffer to avoid PHP's memory limit
            ob_start();

            $i = @getimagesize($gdir . $path);
            if (!$i)
                return false;

            if ($i[2] == 1)
                $im = imagecreatefromgif($gdir . $path);
            elseif ($i[2] == 2)
                $im = imagecreatefromjpeg($gdir . $path);
            elseif ($i[2] == 3)
                $im = imagecreatefrompng($gdir . $path);
            else
                return false;

            if ($i[0] / $w > 2.00 || $i[1] / $h > 2.00) {
                $tmp_w = $w * 2.00;
                $tmp_h = round($i[1] * ($tmp_w / $i[0]), 2);

                $temp = imagecreatetruecolor($tmp_w, $tmp_h);
                imagecopyresized($temp, $im, 0, 0, 0, 0, $tmp_w, $tmp_h, $i[0], $i[1]);

                $i[0] = $tmp_w;
                $i[1] = $tmp_h;

                imagedestroy($im);
                $im = $temp;
            }

            /**
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
                    $x = ($w2 - $w) / 2.00 * (-1.00);
                } else {
                    $h2 = round($i[1] * $w / $i[0], 2);
                    $y = ($h2 - $h) / 2.00 * (-1.00);
                }

                $pic = imagecreatetruecolor($w, $h);
                $bgc = imagecolorallocate($pic, $red, $green, $blue);
                imagefill($pic, 0, 0, $bgc);
                imagecopyresampled($pic, $im, $x, $y, 0, 0, $w2, $h2, $i[0], $i[1]);
            } elseif ($resize_type == 'shrink') {
                /**
                 * $resize_type == 'shrink'
                 * ugly shrink to default dimensions
                 */
//                if ($i[0] > $i[1]) $h = round($i[1] * $w / $i[0], 2);
//                else $w = round($i[0] * $h / $i[1], 2);

                $pic = imagecreatetruecolor($w, $h);
                $bgc = imagecolorallocate($pic, $red, $green, $blue);
                imagefill($pic, 0, 0, $bgc);
                imagecopyresampled($pic, $im, 0, 0, 0, 0, $w, $h, $i[0], $i[1]);
            } elseif ($resize_type == 'resize') {
                /**
                 * $resize_type == 'resize'
                 * resize image with original proportional dimensions
                 */
                // Shifts
                $x = 0;
                $y = 0;

                // Dimensions
                $w2 = $w;
                $h2 = $h;

                if ($w > $h) {          // landscape thumbnail box
                    $w2 = round($i[0] * $h / $i[1], 2);
                    $x = abs($w - $w2) / 2.00;
                } elseif ($w == $h) {     // square thumbnail box
                    if ($i[0] < $i[1]) {// portrait image
                        $w2 = round($i[0] * $h / $i[1], 2);
                        $x = abs($w - $w2) / 2.00;
                    } elseif ($i[0] == $i[1]) {
                        $w2 = $w;
                        $h2 = $h;
                        $x = 0;
                        $y = 0;
                    } else {              // landscape image
                        $h2 = round($i[1] * $w / $i[0], 2);
                        $y = abs($h - $h2) / 2.00;
                    }
                } else {                  // portrait thumbnail box
                    $h2 = round($i[1] * $w / $i[0], 2);
                    $y = abs($h - $h2) / 2.00;
                }

                $pic = imagecreatetruecolor($w, $h);
                $bgc = imagecolorallocate($pic, $red, $green, $blue);
                imagefill($pic, 0, 0, $bgc);
                imagecopyresampled($pic, $im, $x, $y, 0, 0, $w2, $h2, $i[0], $i[1]);
            }
            else
                return;

            /**
             * make directory of thumbnails
             */
            $dirs = explode('/', $path);
            $npath = $gdir . '_thumbnails';
            for ($c = 0; $c < count($dirs) - 1; $c++) {
                $npath .= '/' . $dirs[$c];
                if (is_dir($npath))
                    continue;
                if (!@mkdir($npath))
                    return false;
                @chmod($npath, 0755);
            }

            /**
             * create the thumbnails
             */
            imagejpeg($pic, $gdir . $thumb_path, $thq);
            /**
             * if set, this will create watermark
             */
            if ($wmtrigger == 1) {
                $this->_watermark($gdir . $thumb_path);
            }
            @chmod($gdir . $thumb_path, 0644);

            /**
             * image cache destroy
             */
            imagedestroy($pic);
            imagedestroy($im);

            // goldsky -- adds output buffer to avoid PHP's memory limit
            ob_end_clean();
        }
        // goldsky -- only to switch between localhost and live site.
        if (strpos($_SERVER['DOCUMENT_ROOT'], '/') === (int) 0) {
            $urlencoding = str_replace('%2F', '/', rawurlencode($gdir . $thumb_path));
        } else
            $urlencoding = $this->_e2g_encode($gdir . $thumb_path);
        return $urlencoding;
    }

    /**
     * To get image's path
     * @global mixed  $modx   modx's API
     * @param  int    $id     image's ID
     * @param  string $option the image's title options
     * @return string the path returns as an array
     */
    private function _get_path($id, $option=false) {
        global $modx;

        $result = array();

        $q = 'SELECT A.cat_id,A.cat_name,A.cat_alias '
                . 'FROM ' . $modx->db->config['table_prefix'] . 'easy2_dirs A, '
                . $modx->db->config['table_prefix'] . 'easy2_dirs B '
                . 'WHERE B.cat_id=' . $id . ' '
                . 'AND B.cat_left BETWEEN A.cat_left AND A.cat_right '
                . 'ORDER BY A.cat_left'
        ;

        $res = mysql_query($q);
        if (!$res) {
            return; // asuming there are multiple gids
        }
        if ($option === 'alias') {
            while ($l = mysql_fetch_array($res)) {
                if ($l['cat_alias'] != '')
                    $result[$l['cat_id']] = $l['cat_alias'];
                else
                    $result[$l['cat_id']] = $l['cat_name'];
            }
        }
        // default
        else {
            while ($l = mysql_fetch_row($res)) {
                $result[$l[0]] = $l[1];
            }
        }

        if (empty($result))
            return null;
        return $result;
    }

    /**
     * To get directory's information
     * @param  int    $dirid  gallery's ID
     * @param  string $field  database field
     * @return mixed  the directory's data in an array
     */
    private function _get_dir_info($dirid, $field) {
        return parent::get_dir_info($dirid, $field);
    }

    /**
     * To get file's information
     * @param  int    $fileid  file's ID
     * @param  string $field  database field
     * @return mixed  the file's data in an array
     */
    private function _get_file_info($fileid, $field) {
        return parent::get_file_info($fileid, $field);
    }

    /**
     * Gallery's TEMPLATE function
     * @param string $tpl = gallery's template (@FILE or chunk)
     * @param string $data = template's array data
     * @param string $prefix = placeholder's prefix
     * @param string $suffix = placeholder's suffix
     * @return string templated data
     */
    private function _filler($tpl, $data, $prefix = '[+easy2:', $suffix = '+]') {
        return parent::filler($tpl, $data, $prefix, $suffix);
    }

    /**
     * To get thumbnail for each folder
     * @global mixed  $modx modx's API
     * @param  int    $gid folder's ID
     * @return string image's source
     */
    private function _get_folder_img($gid) {
        global $modx;
        $cat_thumb_orderby = $this->e2gsnip_cfg['cat_thumb_orderby'];
        $cat_thumb_order = $this->e2gsnip_cfg['cat_thumb_order'];

        // http://modxcms.com/forums/index.php/topic,23177.msg273448.html#msg273448
        // ddim -- http://modxcms.com/forums/index.php/topic,48314.msg286241.html#msg286241
        $q = 'SELECT F.* '
                . 'FROM ' . $modx->db->config['table_prefix'] . 'easy2_files F '
                . 'WHERE F.dir_id in ('
                . 'SELECT A.cat_id FROM '
                . $modx->db->config['table_prefix'] . 'easy2_dirs A, '
                . $modx->db->config['table_prefix'] . 'easy2_dirs B '
                . 'WHERE ('
                . 'B.cat_id=' . $gid . ' '
                . 'AND A.cat_left >= B.cat_left '
                . 'AND A.cat_right <= B.cat_right '
                . 'AND A.cat_level >= B.cat_level '
                . 'AND A.cat_visible = 1'
                . ') '
                . 'ORDER BY A.cat_level ASC '
                . ') '
//                . 'ORDER BY F.id DESC '
        ;
        if ($cat_thumb_orderby == 'random') {
            $q .= 'ORDER BY rand() ';
        } else {
            $q .= 'ORDER BY F.' . $cat_thumb_orderby . ' ' . $cat_thumb_order . ' ';
        }
        $q .= 'LIMIT 1 ';

        $res = mysql_query($q);
        if ($res) {
            $result = mysql_fetch_array($res, MYSQL_ASSOC);
            $result['count'] = mysql_num_rows($res);
        } else {
            echo mysql_error() . ' ';
        }
        mysql_free_result($res);

        /**
         * returned as folder's thumbnail's info array
         */
        return $result;
    }

    /**
     * To insert included files into the page header
     * @global mixed $modx modx's API
     * @return mixed the file inclusion or false return
     */
    private function _libs() {
        global $modx;
        $css = $this->e2gsnip_cfg['css'];
        $glib = $this->e2gsnip_cfg['glib'];
        // SLIDESHOW
        $show_group = $this->e2gsnip_cfg['show_group'];

        // GLOBAL e2g CSS styles
        if (file_exists($css)) {
            $modx->regClientCSS($modx->config['base_url'] . $css, 'screen');
        }

        if (!isset($glibs)) {
            require E2G_SNIPPET_PATH . 'includes/configs/libs.config.easy2gallery.php';
        }
        // REGISTER the library from the libs.config.easy2gallery.php file.
        if (isset($glibs[$glib])) {
            // CSS STYLES
            foreach ($glibs[$glib]['regClient']['CSS']['screen'] as $vRegClientCSS) {
                $modx->regClientCSS($vRegClientCSS, 'screen');
            }
            // JS Libraries
            foreach ($glibs[$glib]['regClient']['JS'] as $vRegClientJS) {
                $modx->regClientStartupScript($vRegClientJS);
            }
            // HTMLBLOCK
            if (isset($glibs[$glib]['regClient']['htmlblock']) && $glibs[$glib]['regClient']['htmlblock'] != '') {
                foreach ($glibs[$glib]['regClient']['htmlblock'] as $vRegClientHtmlBlock) {
                    $modx->regClientStartupHTMLBlock($vRegClientHtmlBlock);
                }
            }
            unset($glib);
        }
        else
            return false;
    }

    /**
     * To generate the display of each of thumbnail pieces from the Javascript libraries
     * @global mixed $modx modx's API
     * @param  mixed $row  the thumbnail's data in an array
     * @return mixed the file inclusion, thumbnail sources, comment's controller
     */
    private function _thumb_libs($row) {
        global $modx;
        $gdir = $this->e2gsnip_cfg['gdir'];
        $css = $this->e2gsnip_cfg['css'];
        $glib = $this->e2gsnip_cfg['glib'];
        $landingpage = $this->e2gsnip_cfg['landingpage'];
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

        $fid = $row['id'];              // store the file ID for the library's caption below
        require E2G_SNIPPET_PATH . 'includes/configs/libs.config.easy2gallery.php'; // get the $glibs

        $row['title'] = $row['name'];
        if ($row['name'] == '')
            $row['name'] = '&nbsp;';
        elseif ($mbstring) {
            if (mb_strlen($row['name'], $charset) > $name_len)
                $row['name'] = mb_substr($row['name'], 0, $name_len - 1, $charset) . '...';
        }
        elseif (strlen($row['name']) > $name_len)
            $row['name'] = substr($row['name'], 0, $name_len - 1) . '...';

        $path = $this->_get_path($row['dir_id']);
        if (count($path) > 1) {
            unset($path[1]);
            $path = implode('/', array_values($path)) . '/';
        } else {
            $path = '';
        }

        $row['src'] = $this->_get_thumb($gdir, $path . $row['filename'], $w, $h, $thq);

        if (isset($landingpage)) {
            $row['glibact'] = '';
        }
        // gallery's javascript library activation
        elseif ($glibs[$glib]) {
            $row['glibact'] = $glibs[$glib]['glibact'];
        }

        /**
         * Comments on the thumbnails
         */
        // HIDE COMMENTS from Ignored IP Addresses
        $ip = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];

        $comstatusq = 'SELECT COUNT(ign_ip_address) '
                . 'FROM ' . $modx->db->config['table_prefix'] . 'easy2_ignoredip '
                . 'WHERE ign_ip_address=\'' . $ip . '\'';
        $comstatusres = mysql_query($comstatusq) or die(__LINE__ . ' : ' . mysql_error());
        while ($comrow = mysql_fetch_array($comstatusres)) {
            $count_ignored_ip = $comrow['COUNT(ign_ip_address)'];
        }

        if ($ecm == 1 && ($count_ignored_ip == 0)) {
            $row['com'] = 'e2gcom' . ($row['comments'] == 0 ? 0 : 1);

            // iframe activation
            if ($glibs[$glib]) {
                $row['comments'] = '<a href="' . E2G_SNIPPET_URL . 'comments.easy2gallery.php?id=' . $row['id'] . '" ' . $glibs[$glib]['comments'] . '>' . $row['comments'] . '</a>';
            }
        } else {
            $row['comments'] = '&nbsp;';
            $row['com'] = 'not_display';
        }
        if (isset($glib))
            unset($glib);
        return $row;
    }

    /**
     * Slideshow's controller
     * @global mixed  $modx
     * @return string the slideshow's images
     */
    private function _slideshow() {
        global $modx;
        // database selection
        $gdir = $this->e2gsnip_cfg['gdir'];
        $gid = $this->e2gsnip_cfg['gid'];
        $fid = $this->e2gsnip_cfg['fid'];
        $rgid = $this->e2gsnip_cfg['rgid'];
        $gpn = $this->e2gsnip_cfg['gpn'];
        $e2g_wrapper = $this->e2gsnip_cfg['e2g_wrapper'];

        if ($this->e2gsnip_cfg['ss_orderby'] == 'random') {
            $ss_orderby = 'rand()';
            $ss_order = '';
        } else {
            $ss_orderby = $this->e2gsnip_cfg['ss_orderby'];
            $ss_order = $this->e2gsnip_cfg['ss_order'];
        }

        $ss_limit = $this->e2gsnip_cfg['ss_limit'];

        // initial slideshow's controller and headers
        $slideshow = $this->e2gsnip_cfg['slideshow'];
        $ss_config = $this->e2gsnip_cfg['ss_config'];
        $ss_indexfile = $this->e2gsnip_cfg['ss_indexfile'];
        $ss_css = $this->e2gsnip_cfg['ss_css'];
        $ss_js = $this->e2gsnip_cfg['ss_js'];

        // thumbnail settings
        $w = $this->e2gsnip_cfg['w'];
        $h = $this->e2gsnip_cfg['h'];
        $thq = $this->e2gsnip_cfg['thq'];
        $resize_type = $this->e2gsnip_cfg['resize_type'];
        $thbg_red = $this->e2gsnip_cfg['thbg_red'];
        $thbg_green = $this->e2gsnip_cfg['thbg_green'];
        $thbg_blue = $this->e2gsnip_cfg['thbg_blue'];

        // slideshow's image settings
        $ss_img_src = $this->e2gsnip_cfg['ss_img_src'];
        $ss_w = $this->e2gsnip_cfg['ss_w'];
        $ss_h = $this->e2gsnip_cfg['ss_h'];
        $ss_thq = $this->e2gsnip_cfg['ss_thq'];
        $ss_resize_type = $this->e2gsnip_cfg['ss_resize_type'];
        $ss_bg = $this->e2gsnip_cfg['ss_bg'];
        $ss_red = $this->e2gsnip_cfg['ss_red'];
        $ss_green = $this->e2gsnip_cfg['ss_green'];
        $ss_blue = $this->e2gsnip_cfg['ss_blue'];

        // landscape/portrait image's ratio for the slideshow box
        $ss_allowedratio = $this->e2gsnip_cfg['ss_allowedratio'];

        // self landingpage
        $css = $this->e2gsnip_cfg['css'];
        $js = $this->e2gsnip_cfg['js'];
        $landingpage = $this->e2gsnip_cfg['landingpage'];

        $_ssfile = array();
        if (!empty($gid) && $modx->documentIdentifier != $landingpage) {
            $select = 'SELECT * FROM ' . $modx->db->config['table_prefix'] . 'easy2_files '
                    . 'WHERE dir_id IN (' . $gid . ') '
                    . 'AND status = 1 '
                    . 'ORDER BY ' . $ss_orderby . ' ' . $ss_order . ' '
                    . ( $ss_limit == 'none' ? '' : 'LIMIT ' . ( $gpn * $ss_limit ) . ', ' . $ss_limit )
            ;
            $query = mysql_query($select);
            if (!$query) {
                $o = 'snippet calls wrong gallery id:' . $gid . ', order, or wrong limit.<br />';
                $o .= $select . '<br />';
                $o .= mysql_error();
                return $o;
            }

            while ($fetch = mysql_fetch_array($query)) {
                $path = $this->_get_path($fetch['dir_id']);
                if (count($path) > 1) {
                    unset($path[1]);
                    $path = implode('/', array_values($path)) . '/';
                } else {
                    $path = '';
                }
                $_ssfile['id'][] = $fetch['id'];
                $_ssfile['dirid'][] = $fetch['dir_id'];
                $_ssfile['src'][] = $this->_e2g_decode($gdir . $path . $fetch['filename']);
                $_ssfile['filename'][] = $fetch['filename'];
                $_ssfile['title'][] = ($fetch['name'] != '' ? $fetch['name'] : $fetch['filename']);
                $_ssfile['name'][] = $fetch['name'];
                $_ssfile['description'][] = htmlspecialchars_decode($fetch['description'], ENT_QUOTES);
                $path = $this->_get_path($fetch['dir_id']);
                if (count($path) > 1) {
                    unset($path[1]);
                    $path = implode('/', array_values($path)) . '/';
                } else {
                    $path = '';
                }
                $_ssfile['thumbsrc'][] = $this->_get_thumb($gdir, $path . $fetch['filename'], $w, $h, $thq,
                                $resize_type, $thbg_red, $thbg_green, $thbg_blue);
                if ($ss_img_src == 'generated') {
                    /**
                     * + WATERMARK-ing
                     */
                    $_ssfile['resizedimg'][] = $this->_get_thumb($gdir, $path . $fetch['filename'], $ss_w, $ss_h, $ss_thq,
                                    $ss_resize_type, $ss_red, $ss_green, $ss_blue, 1);
                } elseif ($ss_img_src == 'original')
                    $_ssfile['resizedimg'][] = $this->_e2g_decode($gdir . $path . $fetch['filename']);
                /**
                 * @todo: Making a work around if _get_thumb returns an empty result
                 */
            }
        }

        if (!empty($fid)) {
            $select = 'SELECT * FROM ' . $modx->db->config['table_prefix'] . 'easy2_files '
                    . 'WHERE id IN (' . $fid . ') '
                    . 'AND status = 1 '
            ;
            $query = mysql_query($select);
            if (!$query) {
                return 'snippet calls wrong file id:' . $fid;
            }

            while ($fetch = mysql_fetch_array($query)) {
                $path = $this->_get_path($fetch['dir_id']);
                if (count($path) > 1) {
                    unset($path[1]);
                    $path = implode('/', array_values($path)) . '/';
                } else {
                    $path = '';
                }
                $_ssfile['id'][] = $fetch['id'];
                $_ssfile['dirid'][] = $fetch['dir_id'];
                $_ssfile['src'][] = $this->_e2g_decode($gdir . $path . $fetch['filename']);
                $_ssfile['filename'][] = $fetch['filename'];
                $_ssfile['title'][] = ($fetch['name'] != '' ? $fetch['name'] : $fetch['filename']);
                $_ssfile['name'][] = $fetch['name'];
                $_ssfile['description'][] = htmlspecialchars_decode($fetch['description'], ENT_QUOTES);
                $path = $this->_get_path($fetch['dir_id']);
                if (count($path) > 1) {
                    unset($path[1]);
                    $path = implode('/', array_values($path)) . '/';
                } else {
                    $path = '';
                }
                $_ssfile['thumbsrc'][] = $this->_get_thumb($gdir, $path . $fetch['filename'], $w, $h, $thq,
                                $resize_type, $thbg_red, $thbg_green, $thbg_blue);
                if ($ss_img_src == 'generated') {
                    /**
                     * + WATERMARK-ing
                     */
                    $_ssfile['resizedimg'][] = $this->_get_thumb($gdir, $path . $fetch['filename'], $ss_w, $ss_h, $ss_thq,
                                    $ss_resize_type, $ss_red, $ss_green, $ss_blue, 1);
                } elseif ($ss_img_src == 'original')
                    $_ssfile['resizedimg'][] = $this->_e2g_decode($gdir . $path . $fetch['filename']);
            }
        }

        if (!empty($rgid)) {
            $select = 'SELECT * FROM ' . $modx->db->config['table_prefix'] . 'easy2_files '
                    . 'WHERE status = 1 '
                    . 'AND dir_id IN (' . $rgid . ') '
                    . 'ORDER BY RAND() '
                    . ( $ss_limit == 'none' ? '' : 'LIMIT 0,' . $ss_limit . ' ' )
            ;
            $query = mysql_query($select);
            if (!$query) {
                return 'snippet calls wrong random file id:' . $gid . ', or wrong limit';
            }
            while ($fetch = mysql_fetch_array($query)) {
                $path = $this->_get_path($fetch['dir_id']);
                if (count($path) > 1) {
                    unset($path[1]);
                    $path = implode('/', array_values($path)) . '/';
                } else {
                    $path = '';
                }
                $_ssfile['id'][] = $fetch['id'];
                $_ssfile['dirid'][] = $fetch['dir_id'];
                $_ssfile['src'][] = $this->_e2g_decode($gdir . $path . $fetch['filename']);
                $_ssfile['filename'][] = $fetch['filename'];
                $_ssfile['title'][] = ($fetch['name'] != '' ? $fetch['name'] : $fetch['filename']);
                $_ssfile['name'][] = $fetch['name'];
                $_ssfile['description'][] = htmlspecialchars_decode($fetch['description'], ENT_QUOTES);
                $path = $this->_get_path($fetch['dir_id']);
                if (count($path) > 1) {
                    unset($path[1]);
                    $path = implode('/', array_values($path)) . '/';
                } else {
                    $path = '';
                }
                $_ssfile['thumbsrc'][] = $this->_get_thumb($gdir, $path . $fetch['filename'], $w, $h, $thq,
                                $resize_type, $thbg_red, $thbg_green, $thbg_blue);
                if ($ss_img_src == 'generated') {
                    /**
                     * + WATERMARK-ing
                     */
                    $_ssfile['resizedimg'][] = $this->_get_thumb($gdir, $path . $fetch['filename'], $ss_w, $ss_h, $ss_thq,
                                    $ss_resize_type, $ss_red, $ss_green, $ss_blue, 1);
                } elseif ($ss_img_src == 'original') {
                    $_ssfile['resizedimg'][] = $this->_e2g_decode($gdir . $path . $fetch['filename']);
                }
            }
        }

        /**
         * Filtering the slideshow size ratio
         */
        if ($ss_allowedratio != 'all') {
            // create min-max slideshow width/height ratio
            $ss_exratio = explode('-', $ss_allowedratio);
            $ss_minratio = trim($ss_exratio[0]);
            $ss_maxratio = trim($ss_exratio[1]);
        }

        /**
         * if the counting below = 0 (zero), then should be considered inside
         * the slideshow types, while in some slideshows this doesn't matter.
         */
        $count = count($_ssfile['src']);

        /**
         * added the &fid parameter inside the &slideshow, to open a full page of the clicked image.
         */
        if (isset($_GET['fid']) && isset($landingpage) && $modx->documentIdentifier != $landingpage) {
            // making flexible FURL or not
            $redirect_url = $modx->makeUrl($landingpage
                            , $modx->aliases
                            , 'sid=' . $e2g_static_instances)
                    . '&amp;lp=' . $landingpage . '&amp;fid=' . $_GET['fid'];
            $modx->sendRedirect(htmlspecialchars_decode($redirect_url));
        } elseif (isset($_GET['fid']) && !isset($landingpage)) {
            /**
             * self landingpage
             */
            if (!empty($css)) {
                $modx->regClientCSS($css, 'screen');
            }
            if (!empty($js)) {
                $modx->regClientStartupScript($js);
            }

            $select = 'SELECT * FROM ' . $modx->db->config['table_prefix'] . 'easy2_files '
                    . 'WHERE id = ' . $_GET['fid'] . ' '
            ;
            $query = mysql_query($select);
            if (!$query) {
                return 'snippet calls wrong file id.';
            }

            while ($fetch = mysql_fetch_array($query)) {
                $path = $this->_get_path($fetch['dir_id']);
                if (count($path) > 1) {
                    unset($path[1]);
                    $path = implode('/', array_values($path)) . '/';
                } else {
                    $path = '';
                }
                $src = $gdir . $path . $fetch['filename'];

                // goldsky -- only to switch between localhost and live site.
                // @todo : need review!
                if ($ss_img_src == 'original') {
                    if (strpos($_SERVER['DOCUMENT_ROOT'], '/') === (int) 0) {
                        $l['src'] = rawurldecode(str_replace('%2F', '/', rawurlencode($src)));
                    } else
                        $l['src'] = $src;
                }
                elseif ($ss_img_src == 'generated') {
                    /**
                     * + WATERMARK-ing
                     */
                    if (!isset($lp_w) || !isset($lp_h)) {
                        $img_size = getimagesize($this->_e2g_decode($src));
                        if (!isset($lp_w))
                            $lp_w = $img_size[0];
                        if (!isset($lp_h))
                            $lp_h = $img_size[1];
                        $img_size = array();
                        unset($img_size);
                    }
                    $filePath = $this->_get_thumb($gdir, $path . $fetch['filename'], $lp_w, $lp_h, $lp_thq, $lp_resize_type,
                                    $lp_red, $lp_green, $lp_blue, 1);
                    if (strpos($_SERVER['DOCUMENT_ROOT'], '/') === (int) 0) {
                        $l['src'] = rawurldecode(str_replace('%2F', '/', rawurlencode($filePath)));
                    } else
                        $l['src'] = $filePath;
                }

                $l['title'] = ($fetch['name'] != '' ? $fetch['name'] : $fetch['filename']);
                $l['name'] = $fetch['name'];
                $l['description'] = htmlspecialchars_decode($fetch['description'], ENT_QUOTES);
                $path = $this->_get_path($fetch['dir_id']);
                if (count($path) > 1) {
                    unset($path[1]);
                    $path = implode('/', array_values($path)) . '/';
                } else {
                    $path = '';
                }
            }
            $_ssfile = array();
            return $this->_filler($this->_page_tpl(), $l);
        } else {
            // use custom index file if it's been called.
            if (isset($ss_indexfile) && file_exists($ss_indexfile)) {
                include($ss_indexfile);
            } elseif (isset($ss_indexfile) && !file_exists($ss_indexfile)) {
                $ss_display = 'slideshow index file <b>' . $ss_indexfile . '</b> is not found.';
            }
            // include the available slideshow file config
            elseif (!isset($ss_indexfile) && !file_exists(E2G_SNIPPET_PATH . 'slideshows/' . $slideshow . '/' . $slideshow . '.php')) {
                $ss_display = 'slideshow config for <b>' . $slideshow . '</b> is not found.';
            } else {
                include(E2G_SNIPPET_PATH . 'slideshows/' . $slideshow . '/' . $slideshow . '.php');
            }
            $_ssfile = array();
        }
        $_ssfile = array();
        unset($_ssfile);

        // wrapping the slideshow with E2G's internal ID
        $output = '<div id="'.$e2g_wrapper.'">';

        // return the slideshow
        $output .= $ss_display;
        
        $output .= '</div>';
        return $output;
    }

    /**
     * A landing page to show the image, including information within it.
     * @global mixed $modx   modx's API
     * @param  int   $fileid file's ID
     * @return mixed scripts, images, and false return
     */
    private function _landing_page($fileid) {
        global $modx;

        $landingpage = $this->e2gsnip_cfg['landingpage'];
        if ($modx->documentIdentifier != $landingpage)
            return;
        $page_tpl_css = $this->e2gsnip_cfg['page_tpl_css'];

        $lp_img_src = $this->e2gsnip_cfg['lp_img_src'];
        $lp_w = $this->e2gsnip_cfg['lp_w'];
        $lp_h = $this->e2gsnip_cfg['lp_h'];
        $lp_thq = $this->e2gsnip_cfg['lp_thq'];
        $lp_resize_type = $this->e2gsnip_cfg['lp_resize_type'];
        $lp_bg = $this->e2gsnip_cfg['lp_bg'];
        $lp_red = $this->e2gsnip_cfg['lp_red'];
        $lp_green = $this->e2gsnip_cfg['lp_green'];
        $lp_blue = $this->e2gsnip_cfg['lp_blue'];

        $plugin = $this->e2gsnip_cfg['plugin'];
        $gdir = $this->e2gsnip_cfg['gdir'];
        $css = $this->e2gsnip_cfg['css'];
        $js = $this->e2gsnip_cfg['js'];
        $ecm = $this->e2gsnip_cfg['ecm'];
        $e2g_wrapper = $this->e2gsnip_cfg['e2g_wrapper'];

        if (!empty($css)) {
            $modx->regClientCSS($css, 'screen');
        }
        if (!empty($js)) {
            $modx->regClientStartupScript($js);
        }

        $select = 'SELECT * FROM ' . $modx->db->config['table_prefix'] . 'easy2_files '
                . 'WHERE id = ' . $fileid
        ;
        $query = mysql_query($select);
        if (!$query) {
            return __LINE__ . ' : snippet calls wrong file id.';
        }

        while ($fetch = mysql_fetch_array($query)) {
            $path = $this->_get_path($fetch['dir_id']);
            if (count($path) > 1) {
                unset($path[1]);
                $path = implode('/', array_values($path)) . '/';
            } else {
                $path = '';
            }

            // goldsky -- only to switch between localhost and live site.
            // @todo : need review!
            if ($lp_img_src == 'original') {
                $filePath = $gdir . $path . $fetch['filename'];
                if (strpos($_SERVER['DOCUMENT_ROOT'], '/') === (int) 0) {
                    $l['src'] = rawurldecode(str_replace('%2F', '/', rawurlencode($filePath)));
                } else
                    $l['src'] = $filePath;
            }
            elseif ($lp_img_src == 'generated') {
                /**
                 * + WATERMARK-ing
                 */
                if (!isset($lp_w) || !isset($lp_h)) {
                    $img_size = getimagesize($gdir . $this->_e2g_decode($path . $fetch['filename']));
                    if (!isset($lp_w))
                        $lp_w = $img_size[0];
                    if (!isset($lp_h))
                        $lp_h = $img_size[1];
                    $img_size = array();
                    unset($img_size);
                }
                $filePath = $this->_get_thumb($gdir, $path . $fetch['filename'], $lp_w, $lp_h, $lp_thq, $lp_resize_type,
                                $lp_red, $lp_green, $lp_blue, 1);
                if (strpos($_SERVER['DOCUMENT_ROOT'], '/') === (int) 0) {
                    $l['src'] = rawurldecode(str_replace('%2F', '/', rawurlencode($filePath)));
                } else
                    $l['src'] = $filePath;
            }

            $l['title'] = ($fetch['name'] != '' ? $fetch['name'] : $fetch['filename']);
            $l['name'] = $fetch['name'];
            $l['description'] = htmlspecialchars_decode($fetch['description'], ENT_QUOTES);
            $path = $this->_get_path($fetch['dir_id']);
            if (count($path) > 1) {
                unset($path[1]);
                $path = implode('/', array_values($path)) . '/';
            } else {
                $path = '';
            }

            /**
             * insert plugin for THE IMAGE
             */
            if (isset($plugin) && preg_match('/landingpage:/', $plugin))
                $l['landingpageplugin'] = $this->_plugin('landingpage', $plugin, $fetch);

            /**
             * Comments on the landing page
             */
            // HIDE COMMENTS from Ignored IP Addresses
            $ip = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];

            $comstatusq = 'SELECT COUNT(ign_ip_address) '
                    . 'FROM ' . $modx->db->config['table_prefix'] . 'easy2_ignoredip '
                    . 'WHERE ign_ip_address=\'' . $ip . '\'';
            $comstatusres = mysql_query($comstatusq) or die(__LINE__ . ' : ' . mysql_error());
            while ($comrow = mysql_fetch_array($comstatusres)) {
                $count_ignored_ip = $comrow['COUNT(ign_ip_address)'];
            }

            if ($ecm == 1 && ($count_ignored_ip == 0)) {

                $modx->regClientCSS($page_tpl_css);

                $l['com'] = 'e2gcom' . ($l['comments'] == 0 ? 0 : 1);
                $l['comments'] = $this->_comments($fileid);
            } else {
                $l['comments'] = '&nbsp;';
                $l['com'] = 'not_display';
            }
        }

        // Gallery's wrapper ID
        $l['wrapper'] = $e2g_wrapper;

        return $this->_filler($this->_page_tpl(), $l);
    }

    /**
     * Comment function for a page (landingpage or galley)
     * @param  string $fileid File ID of the comment's owner
     * @return mixed  return the comment's page content
     */
    private function _comments($fileid) {
        global $modx;
        $landingpage = $this->e2gsnip_cfg['landingpage'];
        $recaptcha = $this->e2gsnip_cfg['recaptcha'];
        $ecl_page = $this->e2gsnip_cfg['ecl_page'];
        $cpn = (empty($_GET['cpn']) || !is_numeric($_GET['cpn'])) ? 0 : (int) $_GET['cpn'];

        require_once(E2G_SNIPPET_PATH . 'includes/recaptchalib.php');
        // Get a key from https://www.google.com/recaptcha/admin/create
        $publickey = $this->e2gsnip_cfg['recaptcha_key_public'];
        $privatekey = $this->e2gsnip_cfg['recaptcha_key_private'];

        if (file_exists(E2G_SNIPPET_PATH . 'includes/langs/' . $modx->config['manager_language'] . '.comments.php')) {
            include_once E2G_SNIPPET_PATH . 'includes/langs/' . $modx->config['manager_language'] . '.comments.php';
            $lng_cmt = $e2g_lang[$modx->config['manager_language']];
        } else {
            include_once E2G_SNIPPET_PATH . 'includes/langs/english.comments.php';
            $lng_cmt = $e2g_lang['english'];
        }

        $_P['charset'] = $modx->config['modx_charset'];

        // output from language file
        $_P['title'] = $lng_cmt['title'];
        $_P['comment_add'] = $lng_cmt['comment_add'];
        $_P['name'] = $lng_cmt['name'];
        $_P['email'] = $lng_cmt['email'];
        $_P['usercomment'] = $lng_cmt['usercomment'];
        $_P['send_btn'] = $lng_cmt['send_btn'];
        $_P['comment_body'] = '';
        $_P['comment_pages'] = '';
        $_P['code'] = $lng_cmt['code'];

        // INSERT THE COMMENT INTO DATABASE
        if (!empty($_POST['name']) && !empty($_POST['comment'])) {
            $n = htmlspecialchars(trim($_POST['name']), ENT_QUOTES);
            $c = htmlspecialchars(trim($_POST['comment']), ENT_QUOTES);
            $e = htmlspecialchars(trim($_POST['email']), ENT_QUOTES);
            $ip = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];

            if ($this->_check_email_address($e) == FALSE) {
                $_P['comment_body'] .= '<h2>' . $lng_cmt['email_err'] . '</h2>';
            } elseif ($recaptcha == 1 && (trim($_POST['recaptcha_response_field']) == '')) {
                $_P['comment_body'] .= '<h2>' . $lng_cmt['recaptcha_err'] . '</h2>';
            }
            if ($recaptcha == 1 && $_POST['recaptcha_response_field']) {
                require_once E2G_SNIPPET_PATH . 'includes/recaptchalib.php';
                # the response from reCAPTCHA
                $resp = null;
                # the error code from reCAPTCHA, if any
                $error = null;

                # was there a reCAPTCHA response?
                if ($_POST["recaptcha_response_field"]) {
                    $resp = recaptcha_check_answer($privatekey,
                                    $_SERVER["REMOTE_ADDR"],
                                    $_POST["recaptcha_challenge_field"],
                                    $_POST["recaptcha_response_field"]);

                    if (!$resp->is_valid) {
                        # set the error code so that we can display it
                        $error = $resp->error;
                    } else {
                        $com_insert = 'INSERT INTO ' . $modx->db->config['table_prefix'] . 'easy2_comments (file_id,author,email,ip_address,comment,date_added) '
                                . "VALUES($fileid,'$n','$e','$ip','$c', NOW())";
                        if (mysql_query($com_insert)) {
                            mysql_query('UPDATE ' . $modx->db->config['table_prefix'] . 'easy2_files SET comments=comments+1 WHERE id=' . $fileid);
                            $_P['comment_body'] .= '<h3>' . $lng_cmt['comment_added'] . '</h3>';
                        } else {
                            $_P['comment_body'] .= '<h2>' . $lng_cmt['comment_add_err'] . '</h2>';
                        }
                    }
                }
            }
            // NOT USING reCaptcha
            else {
                $com_insert = 'INSERT INTO ' . $modx->db->config['table_prefix'] . 'easy2_comments (file_id,author,email,ip_address,comment,date_added) '
                        . "VALUES($fileid,'$n','$e','$ip','$c', NOW())";
                if (mysql_query($com_insert)) {
                    mysql_query('UPDATE ' . $modx->db->config['table_prefix'] . 'easy2_files SET comments=comments+1 WHERE id=' . $fileid);
                    $_P['comment_body'] .= '<h3>' . $lng_cmt['comment_added'] . '</h3>';
                } else {
                    $_P['comment_body'] .= '<h2>' . $lng_cmt['comment_add_err'] . '</h2>';
                }
            }
        }

        if ($_POST && empty($_POST['name']) && empty($_POST['comment'])) {
            $_P['comment_body'] .= '<h2>' . $lng_cmt['empty_name_comment'] . '</h2>';
        }

        // DISPLAY THE AVAILABLE COMMENTS
        $comments_query = 'SELECT * FROM ' . $modx->db->config['table_prefix'] . 'easy2_comments '
                . 'WHERE file_id = ' . $fileid . ' '
                . 'AND STATUS=1 '
                . 'ORDER BY id DESC '
                . 'LIMIT ' . ($cpn * $ecl_page) . ', ' . $ecl_page;
        $res = mysql_query($comments_query);
        $i = 0;
        while ($l = mysql_fetch_array($res, MYSQL_ASSOC)) {

            $l['i'] = $i % 2;

            $l['name_permalink'] = '<a href="#" name="lpcmtnm' . $l['id'] . '"></a> ';
            $l['name_w_permalink'] = '<a href="'
                    // making flexible FURL or not
                    . $modx->makeUrl($modx->documentIdentifier
                            , $modx->aliases
                            , 'sid=' . $e2g_static_instances)
                    . '&amp;lp=' . $landingpage . '&amp;fid=' . $fileid . '&amp;cpn=' . $cpn . '#lpcmtnm' . $l['id']
                    . '">' . $l['author'] . '</a> ';
            if (!empty($l['email']))
                $l['name_w_mail'] = '<a href="mailto:' . $l['email'] . '">' . $l['author'] . '</a>';
            else
                $l['name_w_mail'] = $l['author'];

            $_P['comment_body'] .= $this->_filler($this->_page_comment_row_tpl(), $l);
            $i++;
        }
        $_P['pages_permalink'] = '<a href="#" name="lpcmtpg' . $cpn . '"></a>';

        // COUNT PAGES
        $commentCountQuery = 'SELECT COUNT(*) FROM ' . $modx->db->config['table_prefix'] . 'easy2_comments WHERE file_id = ' . $fileid;
        $res = mysql_query($commentCountQuery);
        list($cnt) = mysql_fetch_row($res);
        if ($cnt > $ecl_page) {
            $_P['comment_pages'] = '<p class="pnums">' . $lng_cmt['pages'] . ':';
            $i = 0;
            while ($i * $ecl_page < $cnt) {
                if ($i == $cpn)
                    $_P['comment_pages'] .= '<b>' . ($i + 1) . '</b> ';
                else
                    $_P['comment_pages'] .=
                            '<a href="'
                            // making flexible FURL or not
                            . $modx->makeUrl($modx->documentIdentifier
                                    , $modx->aliases
                                    , 'sid=' . $e2g_static_instances)
                            . '&amp;lp=' . $landingpage . '&amp;fid=' . $fileid . '&amp;cpn=' . $i . '#lpcmtpg' . $i
                            . '">' . ($i + 1) . '</a> ';
                $i++;
            }
            $_P['comment_pages'] .= '</p>';
        }

        // COMMENT TEMPLATE
        if ($recaptcha == 1) {
            $_P['recaptcha'] = '
                <tr>
                    <td colspan="4">' . $this->_e2g_recaptcha_get_html($publickey, $error) . '</td>
                </tr>';
        } else {
            $_P['recaptcha'] = '';
        }
        return $this->_filler($this->_page_comments_tpl(), $_P);
    }

    /**
     * Template for the directory's thumbnail
     * @global mixed $modx the modx's API
     * @return mixed template from chunk or file, or returns false notification
     */
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
            echo 'Directory template ' . $dir_tpl . ' not found!';
        }
    }

    /**
     * Template for the image's thumbnail
     * @global mixed $modx the modx's API
     * @return mixed template from chunk or file, or returns false notification
     */
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

    /**
     * Template for the gallery wrapper
     * @global mixed $modx the modx's API
     * @return mixed template from chunk or file, or returns false notification
     */
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

    /**
     * Template for the random image
     * @global mixed $modx the modx's API
     * @return mixed template from chunk or file, or returns false notification
     */
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
            echo 'Random template ' . $rand_tpl . ' not found!';
        }
    }

    /**
     * Page template for the landing page
     * @global mixed $modx the modx's API
     * @return mixed template from chunk or file, or returns false notification
     */
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
            echo 'Landing page template ' . $page_tpl . ' not found!';
        }
    }

    /**
     * Comment row template for the thumbnails
     * @global mixed $modx the modx's API
     * @return mixed template from chunk or file, or returns false notification
     */
    private function _comment_row_tpl() {
        global $modx;
        $comments_row_tpl = $this->e2gsnip_cfg['comments_row_tpl'];
        if (file_exists($comments_row_tpl)) {
            $row_tpl = file_get_contents($comments_row_tpl);
            return $row_tpl;
        } elseif (!empty($modx->chunkCache[$comments_row_tpl])) {
            $row_tpl = $modx->chunkCache[$comments_row_tpl];
            return $row_tpl;
        } else {
            echo 'Comments row template ' . $comments_row_tpl . ' not found!';
        }
    }

    /**
     * Comment template for the landing page
     * @global mixed $modx the modx's API
     * @return mixed template from chunk or file, or returns false notification
     */
    private function _page_comments_tpl() {
        global $modx;
        $page_comments_tpl = $this->e2gsnip_cfg['page_comments_tpl'];
        if (file_exists($page_comments_tpl)) {
            $tpl = file_get_contents($page_comments_tpl);
            return $tpl;
        } elseif (!empty($modx->chunkCache[$page_comments_tpl])) {
            $tpl = $modx->chunkCache[$page_comments_tpl];
            return $tpl;
        } else {
            echo 'Comments template ' . $page_comments_tpl . ' not found!';
        }
    }

    /**
     * Comment row template for the landing page
     * @global mixed $modx the modx's API
     * @return mixed template from chunk or file, or returns false notification
     */
    private function _page_comment_row_tpl() {
        global $modx;
        $page_comments_row_tpl = $this->e2gsnip_cfg['page_comments_row_tpl'];
        if (file_exists($page_comments_row_tpl)) {
            $row_tpl = file_get_contents($page_comments_row_tpl);
            return $row_tpl;
        } elseif (!empty($modx->chunkCache[$page_comments_row_tpl])) {
            $row_tpl = $modx->chunkCache[$page_comments_row_tpl];
            return $row_tpl;
        } else {
            echo 'Page\'s comments row template ' . $page_comments_row_tpl . ' not found!';
        }
    }

    /**
     * email validation
     * @param  string $email
     * @return bool   true/false
     */
    function _check_email_address($email) {
        return parent::check_email_address($email);
    }

    /**
     * plugin implementation
     * @global mixed  $modx   modx's API
     * @param  string $target the object's type where the plugin is applied
     * @param  string $plugin plugin's name
     * @param  mixed  $row    the object's data
     * @return string
     */
    private function _plugin($target, $plugin, $row) {
        global $modx;
        // clear up
        $p_errs = array();
        if (isset($plugin_display))
            unset($plugin_display);

        if (!isset($plugin)) {
            return 'Please make a plugin selection.';
        } else {
            $badchars = array('`', ' ');
            $plugin = str_replace($badchars, '', trim($plugin));
            // get the plugin target: thumb:starrating,watermark | gallery:... | landingpage:...
            $xpldplugins = array();
            $xpldplugins = @explode('|', trim($plugin));
            // get the plugin' settings: starrating,watermark
            $p_category = array();

            foreach ($xpldplugins as $p_category) {
                $xpldsettings = @explode(':', trim($p_category));
                $p_target = $xpldsettings [0];
                $p_selections = $xpldsettings [1];

                if ($p_target == $target) { // if the snippet call == the function call
                    $xpldtypes = @explode(',', trim($p_selections));
                    foreach ($xpldtypes as $p_type) {
                        $xpldindexes = @explode('@', trim($p_type));
                        $p_name = $xpldindexes[0];
                        $p_indexfile = $xpldindexes[1];

                        // IMAGE / DIRECTORY ID HANDLER
                        if ($target == 'thumb')
                            $_plug['id'] = 'fid_' . $row['id'];
                        if ($target == 'gallery')
                            $_plug['id'] = 'gid_' . $row['cat_id'];
                        if ($target == 'landingpage')
                            $_plug['id'] = 'fid_' . $row['id'];

                        // LOAD DA FILE!
                        if (($p_indexfile) != '') {
                            if (!file_exists($p_indexfile)) {
                                $p_errs[] = __LINE__ . ' : File <b>' . $p_indexfile . '</b> does not exist.';
                            } else
                                include $p_indexfile;
                        } elseif (!file_exists(E2G_SNIPPET_PATH . 'plugins/' . $p_name . '/' . $p_name . '.php')) {
                            $p_errs[] = __LINE__ . ' : Plugin <b>' . $p_name . '</b> does not exist.';
                        } else {
                            include E2G_SNIPPET_PATH . 'plugins/' . $p_name . '/' . $p_name . '.php';
                        }
                    } // foreach ( $xpldtypes as $p_type )
                } // if ($p_target == $target)
            } // foreach ($xpldplugins as $p_category)
            foreach ($p_errs as $p_err) {
                $_plug_displays[] = '<span style="color:black;">' . $p_err . '</span><br />';
            }
            $p_errs = array();
            unset($p_errs);

            // JOINING MANY PLUGINS RESULTS
            foreach ($_plug_displays as $_play_display) {
                $plugin_display .= $_play_display;
            }

            return $plugin_display;
        } // if (isset($plugin))
    }

    /**
     * Unicode character encoding work around.<br />
     * For human reading.<br />
     * The value is set from the module's config page.
     * @link http://a4esl.org/c/charset.html
     * @param string $text the string to be encoded
     * @return string returns the encoding
     */
    private function _e2g_encode($text, $callback=false) {
        return parent::e2g_encode($text, $callback);
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
    private function _e2g_decode($text, $callback=false) {
        return parent::e2g_decode($text, $callback);
    }

    /**
     * To check the valid decendant of the given &gid parameter
     * @global mixed $modx
     * @param int    $id            single ID to be checked
     * @param string $static_id     comma separated IDs of valid decendants
     * @return bool  true/false
     */
    private function _check_gid_decendant($id, $static_id) {
        global $modx;
        $s = 'SELECT A.cat_id '
                . 'FROM ' . $modx->db->config['table_prefix'] . 'easy2_dirs A, '
                . $modx->db->config['table_prefix'] . 'easy2_dirs B '
                . 'WHERE B.cat_id IN (' . $static_id . ') '
                . 'AND A.cat_left BETWEEN B.cat_left AND B.cat_right '
        ;
        $q = mysql_query($s) or die(__LINE__ . ' : ' . mysql_error() . '<br />' . $s);
        while ($l = mysql_fetch_array($q, MYSQL_ASSOC)) {
            $check[$l['cat_id']] = $l['cat_id'];
        }
        $xpld_get_gids = explode(',', $id);
        foreach ($xpld_get_gids as $_id) {
            if (!$check[$_id] && ($static_id != 1)) {
                return false;
//                return $modx->sendUnauthorizedPage();
            } elseif (!$check[$_id] && ($static_id == 1)) {
                return false;
//                return $modx->sendErrorPage();
            } else
                return true;
        }
    }

    /**
     * CHECK THE REAL DESCENDANT OF fid ROOT
     * @global mixed    $modx       modx's API
     * @param int       $id         the decendant file's ID
     * @param int       $static_id  the original file's ID
     * @return bool     true | false
     */
    private function _check_fid_decendant($id, $static_id) {
        global $modx;
        $s = 'SELECT id '
                . 'FROM ' . $modx->db->config['table_prefix'] . 'easy2_files '
                . 'WHERE id IN (' . $static_id . ') '
        ;
        $q = mysql_query($s) or die(__LINE__ . ' : ' . mysql_error() . '<br />' . $s);
        while ($l = mysql_fetch_array($q, MYSQL_ASSOC)) {
            $check[$l['id']] = $l['id'];
        }
        $xpld_get_fids = explode(',', $id);
        foreach ($xpld_get_fids as $_id) {
            if (!$check[$_id]) {
                return $modx->sendErrorPage();
            } else
                return true;
        }
    }

    /**
     * CHECK the valid parent IDs OF &tag parameter
     * @global mixed $modx
     * @param string $dirorfile dir|file
     * @param string $tag from &tag parameter
     * @param int    $id  id of the specified dir/file
     * @return bool true | false
     */
    private function _tags_ids($dirorfile, $tag, $id=1) {
        global $modx;
        $tag = strtolower($tag);

        if ($dirorfile == 'dir') {
            $s = 'SELECT cat_id FROM ' . $modx->db->config['table_prefix'] . 'easy2_dirs ';

            $multiple_tags = @explode(',', $tag);
            for ($i = 0; $i < count($multiple_tags); $i++) {
                if ($i == 0)
                    $s .= 'WHERE LOWER(cat_tag) LIKE \'%' . $multiple_tags[$i] . '%\' ';
                else
                    $s .= 'OR LOWER(cat_tag) LIKE \'%' . $multiple_tags[$i] . '%\' ';
            }

            $tags_query = mysql_query($s) or die(__LINE__ . ': ' . mysql_error() . '<br />' . $s);
            while ($l = mysql_fetch_array($tags_query, MYSQL_ASSOC)) {
                $tags_dir[$l['cat_id']] = $l['cat_id'];
            }

            if (isset($tags_dir[$id])) {
                return TRUE;
            } else {
                return FALSE;
            }
        }
        if ($dirorfile == 'file') {
            $s = 'SELECT id FROM ' . $modx->db->config['table_prefix'] . 'easy2_files ';

            $multiple_tags = @explode(',', $tag);
            for ($i = 0; $i < count($multiple_tags); $i++) {
                if ($i == 0)
                    $s .= 'WHERE LOWER(tag) LIKE \'%' . $multiple_tags[$i] . '%\' ';
                else
                    $s .= 'OR LOWER(tag) LIKE \'%' . $multiple_tags[$i] . '%\' ';
            }

            $tags_query = mysql_query($s) or die(__LINE__ . ': ' . mysql_error() . '<br />' . $s);
            while ($l = mysql_fetch_array($tags_query, MYSQL_ASSOC)) {
                $tags_file[$l['id']] = $l['id'];
            }

            if (isset($tags_file[$id])) {
                return TRUE;
            } else {
                return FALSE;
            }
        }
    }

    /**
     * Gets the challenge HTML (javascript and non-javascript version).
     * This is called from the browser, and the resulting reCAPTCHA HTML widget
     * is embedded within the HTML form it was called from.
     * @param string $pubkey A public key for reCAPTCHA
     * @param string $error The error given by reCAPTCHA (optional, default is null)
     * @param boolean $use_ssl Should the request be made over ssl? (optional, default is false)
     * @return string - The HTML to be embedded in the user's form.
     */
    private function _e2g_recaptcha_get_html($pubkey, $error = null, $use_ssl = false) {
        require_once(E2G_SNIPPET_PATH . 'includes/recaptchalib.php');
        $theme = $this->e2gsnip_cfg['recaptcha_theme'];
        $theme_custom = $this->e2gsnip_cfg['recaptcha_theme_custom'];

        if ($pubkey == null || $pubkey == '') {
            return ("To use reCAPTCHA you must get an API key from <a href='https://www.google.com/recaptcha/admin/create'>https://www.google.com/recaptcha/admin/create</a>");
        }

        if ($use_ssl) {
            $server = RECAPTCHA_API_SECURE_SERVER;
        } else {
            $server = RECAPTCHA_API_SERVER;
        }

        $errorpart = "";
        if ($error) {
            $errorpart = "&amp;error=" . $error;
        }
        return '
            <script type="text/javascript">
            var RecaptchaOptions = {
            theme : \'' . $theme . '\'
                ' . ($theme == 'custom' ? ',custom_theme_widget: \'' . $theme_custom . '\'' : '') . '};
            </script>
            <script type="text/javascript" src="' . $server . '/challenge?k=' . $pubkey . $errorpart . '"></script>
            <noscript>
                <iframe src="' . $server . '/noscript?k=' . $pubkey . $errorpart . '" height="300" width="500" frameborder="0"></iframe><br/>
                <textarea name="recaptcha_challenge_field" rows="3" cols="40"></textarea>
                <input type="hidden" name="recaptcha_response_field" value="manual_challenge"/>
            </noscript>';
    }

    /**
     * Generating the watermark
     * @param string $fp file path
     * @return mixed image output
     */
    private function _watermark($fp) {
        $e2g = $this->e2gsnip_cfg;

        if ($e2g['ewm'] != 0) {
            $inf = getimagesize($fp);

            if ($inf[2] == 1)
                $im = imagecreatefromgif($fp);
            elseif ($inf[2] == 2)
                $im = imagecreatefromjpeg($fp);
            elseif ($inf[2] == 3)
                $im = imagecreatefrompng($fp);
            else
                return 'Imagecreate error';

            if ($e2g['wmtype'] == 'text') {
                // X
                $len = strlen($e2g['wmt']);
                if ($e2g['wmpos1'] == 3)
                    $x = $inf[0] - 10 - ($len * 6);
                elseif ($e2g['wmpos1'] == 2)
                    $x = ($inf[0] - ($len * 6)) / 2;
                else
                    $x = 10;

                // Y
                if ($e2g['wmpos2'] == 3)
                    $y = $inf[1] - 20;
                elseif ($e2g['wmpos2'] == 2)
                    $y = ($inf[1] / 2) - 5;
                else
                    $y = 10;

                $text_color = imagecolorallocate($im, 0, 0, 0);
                imagestring($im, 2, $x - 1, $y, $e2g['wmt'], $text_color);
                imagestring($im, 2, $x + 1, $y, $e2g['wmt'], $text_color);
                imagestring($im, 2, $x, $y - 1, $e2g['wmt'], $text_color);
                imagestring($im, 2, $x, $y + 1, $e2g['wmt'], $text_color);
                imagestring($im, 2, $x + 1, $y + 1, $e2g['wmt'], $text_color);
                imagestring($im, 2, $x - 1, $y - 1, $e2g['wmt'], $text_color);

                $text_color = imagecolorallocate($im, 255, 255, 255);
                imagestring($im, 2, $x, $y, $e2g['wmt'], $text_color);
            }
            elseif ($e2g['wmtype'] == 'image') {

                $wmfp = str_replace('../', '', $e2g['wmt']);
                if (!file_exists($wmfp)) {
                    return 'WM file not found';
                }

                $wminfo = getimagesize($wmfp);

                if ($wminfo[2] == 1)
                    $wmi = imagecreatefromgif($wmfp);
                elseif ($wminfo[2] == 2)
                    $wmi = imagecreatefromjpeg($wmfp);
                elseif ($wminfo[2] == 3)
                    $wmi = imagecreatefrompng($wmfp);
                else
                    return 'WM error';

                imageAlphaBlending($wmi, false);
                imageSaveAlpha($wmi, true);
                $wm_w = imageSX($wmi);
                $wm_h = imageSY($wmi);

                // X
                $len = strlen($e2g['wmt']);
                if ($e2g['wmpos1'] == 3)
                    $x = $inf[0] - 10 - $wm_w;
                elseif ($e2g['wmpos1'] == 2)
                    $x = ($inf[0] - $wm_w) / 2;
                else
                    $x = 10;

                // Y
                if ($e2g['wmpos2'] == 3)
                    $y = $inf[1] - 10 - $wm_h;
                elseif ($e2g['wmpos2'] == 2)
                    $y = ($inf[1] / 2) - $wm_h;
                else
                    $y = 10;


                imagecopy($im, $wmi, $x, $y, 0, 0, $wm_w, $wm_h);
                imagedestroy($wmi);
            }
            return imagejpeg($im, $fp);
        }
    }

}

// class e2g_snip
?>