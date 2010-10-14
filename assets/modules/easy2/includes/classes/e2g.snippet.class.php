<?php

/**
 * EASY 2 GALLERY
 * Gallery Snippet Class for Easy 2 Gallery Module for MODx Evolution
 * @author Cx2 <inteldesign@mail.ru>
 * @author Temus <temus3@gmail.com>
 * @author goldsky <goldsky@modx-id.com>
 * @version 1.4.0
 */
class e2g_snip extends e2g_pub {

    /**
     * Inherit MODx functions
     * @var mixed modx's API
     */
    public $modx;
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

    public function __construct($modx, $e2gsnip_cfg) {
        parent::__construct($modx, $e2gsnip_cfg, $e2g, $lng);
        $this->modx = & $modx;
        $this->e2gsnip_cfg = $e2gsnip_cfg;
        $this->_e2g = $_e2g;
    }

    /**
     * The main function.
     * @return mixed the function calls
     */
    public function display() {
        /**
         * 1. '&gid' : full gallery directory (directory - &gid - default)
         * 2. '&fid' : one file only (file - $fid)
         * 3. '&rgid' : random file in a directory (random - $rgid)
         * 4. '&slideshow' : slideshow by fid-s or rgid-s or gid-s
         */
        $modx = $this->modx;
//        $gid = $this->e2gsnip_cfg['gid']; // default
//        $staticGid = $this->e2gsnip_cfg['static_gid'];
        $fid = $this->e2gsnip_cfg['fid'];
//        $static_fid = $this->e2gsnip_cfg['static_fid'];
        $rgid = $this->e2gsnip_cfg['rgid'];
        $slideshow = $this->e2gsnip_cfg['slideshow'];
        $landingPage = $this->e2gsnip_cfg['landingpage'];

        // to avoid gallery's thumbnails display on the landingpage's page
        if ($modx->documentIdentifier != $landingPage) {
            if (!empty($fid) && !isset($slideshow)) {
                return $this->_imgFile();
            }
            if (!empty($rgid) && !isset($slideshow)) {
                return $this->_imgRandom();
            }
            if (empty($fid) && empty($rgid) && !isset($slideshow)) {
                return $this->_gallery(); // default
            }
        }
        if (!empty($slideshow)) {
            return $this->_slideshow();
        }
        if (isset($landingPage) && isset($_GET['fid'])) {
            return $this->_landingPage($_GET['fid']);
        }
    }

    /**
     * Full gallery execution
     * @return mixed FALSE/images delivered in template
     */
    private function _gallery() {
        $modx = $this->modx;
        $gdir = $this->e2gsnip_cfg['gdir'];
        $gid = $this->e2gsnip_cfg['gid'];
        $staticGid = $this->e2gsnip_cfg['static_gid'];
        $e2gInstances = $this->e2gsnip_cfg['e2g_instances'];
        $e2gStaticInstances = $this->e2gsnip_cfg['e2g_static_instances'];
        $e2gWrapper = $this->e2gsnip_cfg['e2g_wrapper'];

        $tag = $this->e2gsnip_cfg['tag'];
        $staticTag = $this->e2gsnip_cfg['static_tag'];

        if ($this->e2gsnip_cfg['orderby'] == 'random') {
            $orderBy = 'rand()';
            $order = '';
        } else {
            $orderBy = $this->e2gsnip_cfg['orderby'];
            $order = $this->e2gsnip_cfg['order'];
        }
        if ($this->e2gsnip_cfg['cat_orderby'] == 'random') {
            $cat_orderBy = 'rand()';
            $cat_order = '';
        } else {
            $cat_orderBy = $this->e2gsnip_cfg['cat_orderby'];
            $cat_order = $this->e2gsnip_cfg['cat_order'];
        }

        $whereDir = $this->e2gsnip_cfg['where_dir'];
        $whereFile = $this->e2gsnip_cfg['where_file'];
        $limit = $this->e2gsnip_cfg['limit'];
        $gpn = $this->e2gsnip_cfg['gpn'];

        $charset = $this->e2gsnip_cfg['charset'];
        $mbstring = $this->e2gsnip_cfg['mbstring'];
        $catNameLen = $this->e2gsnip_cfg['cat_name_len'];

//        $notables = $this->e2gsnip_cfg['notables']; // deprecated
        $grid = $this->e2gsnip_cfg['grid'];
        $gridClass = $this->e2gsnip_cfg['grid_class'];
        $crumbsClassCurrent = $this->e2gsnip_cfg['crumbs_classCurrent'];
        $backClass = $this->e2gsnip_cfg['back_class'];
        $pageNumClass = $this->e2gsnip_cfg['pagenum_class'];
        $colls = $this->e2gsnip_cfg['colls'];
        $imgSrc = $this->e2gsnip_cfg['img_src'];

        $showOnly = $this->e2gsnip_cfg['showonly'];
        $customGetParams = $this->e2gsnip_cfg['customgetparams'];
        $galDesc = $this->e2gsnip_cfg['gal_desc'];
        $landingPage = $this->e2gsnip_cfg['landingpage'];
        $plugin = $this->e2gsnip_cfg['plugin'];

        // CRUMBS
        $crumbs = $this->e2gsnip_cfg['crumbs'];
        $crumbsUse = $this->e2gsnip_cfg['crumbs_use'];
        $crumbsSeparator = $this->e2gsnip_cfg['crumbs_separator'];
        $crumbsShowHome = $this->e2gsnip_cfg['crumbs_showHome'];
        $crumbsShowAsLinks = $this->e2gsnip_cfg['crumbs_showAsLinks'];
        $crumbsShowCurrent = $this->e2gsnip_cfg['crumbs_showCurrent'];
        $crumbsShowPrevious = $this->e2gsnip_cfg['crumbs_showPrevious'];

        // Previous/Up/Next Navigation
        $navPrevUpNext = $this->e2gsnip_cfg['nav_prevUpNext'];
        $navPrevSymbol = $this->e2gsnip_cfg['nav_prevSymbol'];
        $navUpSymbol = $this->e2gsnip_cfg['nav_upSymbol'];
        $navNextSymbol = $this->e2gsnip_cfg['nav_nextSymbol'];

        // PAGINATION
        $pagination = $this->e2gsnip_cfg['pagination'];

        // EXECUTE THE JAVASCRIPT LIBRARY'S HEADERS
        $jsLibs = $this->_libs();
        if ($jsLibs === FALSE)
            return 'Javascript library error.';

        //**********************************************************************/
        //*   PAGINATION FIXING for multiple snippet calls on the same page    */
        //**********************************************************************/
        // for the UNselected &gid snippet call when the other &gid snippet call is selected
        if (isset($staticGid)
                && isset($_GET['gid'])
                && $this->_checkGidDecendant($_GET['gid'], $staticGid) == FALSE
                || $e2gInstances != $e2gStaticInstances
        ) {
            $gpn = 0;
        }
        // for the UNselected &gid snippet call when &tag snippet call is selected
        if (isset($staticGid)
                && !isset($staticTag)
                && isset($_GET['tag'])
                || $e2gInstances != $e2gStaticInstances
        ) {
            $gpn = 0;
        }

        // for the UNselected &tag snippet call when &gid snippet call is selected
        if (isset($staticTag)
                && !isset($_GET['tag'])
                && isset($_GET['gid'])
                || $e2gInstances != $e2gStaticInstances
        ) {
            $gpn = 0;
        }
        // for the UNselected &tag snippet call when the other &tag snippet call is selected
        if (isset($staticTag)
                && $tag != $staticTag
                || $e2gInstances != $e2gStaticInstances
        ) {
            $gpn = 0;
        }

        // FREEZING using plugin
        if ($e2gInstances != $e2gStaticInstances) {
            $gid = $staticGid;
            $tag = $staticTag;
            $gpn = 0;
        }

        /**
         * Clearing the internal parameter.
         * This is NOT the $e2g config.
         */
        $_e2g = array(
            'content' => ''
            , 'pages' => ''
            , 'parent_id' => 0
            , 'back' => ''
            , 'desc_class' => ''
            , 'cat_name' => ''
            , 'crumbs' => ''
            , 'permalink' => ''
            , 'wrapper' => ''
            , 'sid' => ''
        );

        if ($galDesc != '1')
            $_e2g['desc_class'] = 'style="display:none;"';

        // START the grid
        $_e2g['content'] = (($grid == 'css') ? '<div class="' . $gridClass . '">' : '<table class="' . $gridClass . '"><tr>');

        // Store the restricted galleries
        $excludeDirsWebAccess = $this->_excludeWebAccess('dir');
        $excludeFilesWebAccess = $this->_excludeWebAccess('file');

        //******************************************************************/
        //*                 COUNT DIRECTORY WITHOUT LIMIT!                 */
        //******************************************************************/
        // dir_count is used for pagination. random can not have this.
        // TODO: conflict with the &gid=`*` parameter. moved the limitation to the PAGINATION section instead.
//        if ($showOnly == 'images' || $orderBy == 'rand()' || $cat_orderBy == 'rand()') {
        if ($showOnly == 'images') {
            $dirCount = 0;
        } else {
            if (isset($staticTag)) {
                $selectCount = 'SELECT COUNT(DISTINCT cat_id) '
                        . 'FROM ' . $modx->db->config['table_prefix'] . 'easy2_dirs ';

                // OPEN the selected tagged folder
                if (isset($_GET['gid'])
                        && $staticTag == $tag
                        && $this->_tagsIds('dir', $tag, $_GET['gid'])) {
                    $selectCount .= 'WHERE parent_id IN (' . $_GET['gid'] . ')';
                } else {
                    // the selected tag of multiple tags on the same page
                    if ($staticTag == $tag) {
                        $multipleTags = @explode(',', $tag);
                    }
                    // the UNselected tag of multiple tags on the same page
                    else {
                        $multipleTags = @explode(',', $staticTag);
                    }

                    for ($i = 0; $i < count($multipleTags); $i++) {
                        if ($i == 0)
                            $selectCount .= 'WHERE cat_tag LIKE \'%' . $multipleTags[$i] . '%\' ';
                        else
                            $selectCount .= 'OR cat_tag LIKE \'%' . $multipleTags[$i] . '%\' ';
                    }
                }

                if ($excludeDirsWebAccess !== FALSE) {
                    $selectCount .= 'AND cat_id NOT IN (' . $excludeDirsWebAccess . ')';
                }
            }
            // original &gid parameter
            else {
                $selectCount = 'SELECT COUNT(DISTINCT d.cat_id) '
                        . 'FROM ' . $modx->db->config['table_prefix'] . 'easy2_dirs AS d '
                        . 'WHERE '
                ;
                if ($gid != '*') {
                    if ($this->_checkGidDecendant((isset($_GET['gid']) ? $_GET['gid'] : $gid), $staticGid) == TRUE) {
                        $selectCount .= 'd.parent_id IN (' . $gid . ') AND ';
                    } else {
                        $selectCount .= 'd.parent_id IN (' . $staticGid . ') AND ';
                    }
                }

                if (isset($whereDir)) {
                    $where = $this->_whereClause($whereDir, 'd');
                    if ($where === FALSE)
                        return FALSE;
                    else
                        $selectCount .= $where . ' AND ';
                }

                $selectCount .= 'd.cat_visible = 1 '
                        // ddim -- wrapping children folders
                        . 'AND (SELECT count(*) FROM ' . $modx->db->config['table_prefix'] . 'easy2_files F '
                        . 'WHERE F.dir_id IN '
                        . '(SELECT A.cat_id FROM ' . $modx->db->config['table_prefix'] . 'easy2_dirs A, '
                        . $modx->db->config['table_prefix'] . 'easy2_dirs B '
                        . 'WHERE (B.cat_id = d.cat_id '
                        . 'AND A.cat_left >= B.cat_left '
                        . 'AND A.cat_right <= B.cat_right '
                        . 'AND A.cat_level >= B.cat_level '
                        . 'AND A.cat_visible = 1)'
                        . ')'
                        . ')<>0 ';

                if ($excludeDirsWebAccess !== FALSE) {
                    $selectCount .= 'AND d.cat_id NOT IN (' . $excludeDirsWebAccess . ')';
                }
            }

            $dirCountQuery = mysql_query($selectCount);
            if (!$dirCountQuery) {
                echo __LINE__ . ' : ' . mysql_error() . '<br />' . $selectCount . '<br />';
                return FALSE;
            }

            $dirCount = mysql_result($dirCountQuery, 0, 0);

            /**
             * Add the multiple IDs capability into the &gid
             * Check the valid params of each of snippet calls
             */
            if ($this->_checkGidDecendant((isset($_GET['gid']) ? $_GET['gid'] : $gid), $staticGid) == TRUE) {
                $multipleGids = explode(',', $gid);
            } else {
                $multipleGids = explode(',', $staticGid);
            }

            $multipleGidsCount = count($multipleGids);
            // reset the directory's counter
            $dirNumRows = 0;
            unset($singleGid);
            foreach ($multipleGids as $singleGid) {
                // get path from the $gid
                $path = $this->_getPath($singleGid);
                $crumbsPath = $this->_getPath($singleGid, $crumbsUse);

                // To limit the CRUMBS paths.
                if (($staticGid != '1') && !empty($crumbsPath) && !isset($tag)) {
                    $staticPath = $this->_getPath($staticGid);
                    if (!$crumbsShowPrevious) {
                        $crumbsPath = array_slice($crumbsPath, (count($staticPath) - 1), NULL, TRUE);
                    }
                }

                // get "category name" from $path
                $_e2g['cat_name'] = is_array($path) ? end($path) : '';

                /**
                 * Only use crumbs if it is a single gid.
                 * Otherwise, how can we make crumbs for merging directories of multiple galleries on 1 page?
                 */
                if (isset($staticTag) && $this->_tagsIds('dir', $staticTag, $singleGid) === FALSE)
                    continue;
                elseif ($multipleGidsCount == 1) {
                    /**
                     * In here, the script generates:
                     * - the CRUMBS, and
                     * - the PREV/UP/JUMP NAVIGATION
                     */
                    //******************************************************************/
                    //*                             CRUMBS                             */
                    //******************************************************************/

                    if ($crumbs == 1) {
                        // reset crumbs
                        $breadcrumbs = '';
                        // if path more the none
                        if (count($crumbsPath) > 0) {
                            end($crumbsPath);
                            prev($crumbsPath);
                            $_e2g['parent_id'] = key($crumbsPath);
                            $_e2g['parent_name'] = $crumbsPath[$_e2g['parent_id']];

                            // create crumbs
                            $cnt = 0;
                            foreach ($crumbsPath as $k => $v) {
                                $cnt++;
                                if ($cnt == 1 && !$crumbsShowHome) {
                                    continue;
                                }
                                if ($cnt == count($crumbsPath) && !$crumbsShowCurrent) {
                                    continue;
                                }

                                if ($cnt != count($crumbsPath))
                                    $breadcrumbs .= $crumbsSeparator . ($crumbsShowAsLinks ?
                                                    '<a href="'
                                                    // making flexible FURL or not
                                                    . $modx->makeUrl($modx->documentIdentifier
                                                            , $modx->aliases
                                                            , 'sid=' . $e2gStaticInstances)
                                                    . '&amp;gid=' . $k
                                                    . '#' . $e2gStaticInstances . '_' . $k
                                                    . '">' . $v . '</a>' : $v);
                                else
                                    $breadcrumbs .= $crumbsSeparator . '<span class="' . $crumbsClassCurrent . '">' . $v . '</span>';
                            }
                            $breadcrumbs = substr_replace($breadcrumbs, '', 0, strlen($crumbsSeparator));

                            // unset Easy 2-$crumbsPath value
                            unset($crumbsPath[1]);

                            // joining many of directory paths
                            $crumbsPath = implode('/', array_values($crumbsPath)) . '/';
                        } else { // if not many, path is set as empty
                            $crumbsPath = '';
                        } // if (count($path) > 1)
                        $_e2g['crumbs'] = $breadcrumbs;
                    } // if ($crumbs == 1)
                    //******************************************************************/
                    //*                  Previous/Up/Next Navigation                   */
                    //******************************************************************/
                    if ($navPrevUpNext == 1
                            && $orderBy != 'rand()'
                            && $cat_orderBy != 'rand()'
                    ) {
                        if (isset($staticTag)) {
                            $staticKey = $staticTag;
                            $dynamicKey = $tag;
                        } else {
                            $staticKey = $staticGid;
                            $dynamicKey = $gid;
                        }

                        $navPrev = $this->_navPrevUpNext('prev', $staticKey, $dynamicKey, $cat_orderBy, $cat_order);
                        if ($navPrev !== FALSE) {
                            $_e2g['prev_cat_link'] = $navPrev['link'];
                            $_e2g['prev_cat_name'] = $navPrev['cat_name'];
                            $_e2g['prev_cat_symbol'] = $navPrevSymbol;
                            if (isset($staticTag))
                                $_e2g['prev_cat_permalink'] = '#' . $e2gStaticInstances . '_' . $staticTag;
                            else
                                $_e2g['prev_cat_permalink'] = '#' . $e2gStaticInstances . '_' . $navPrev['cat_id'];

                            // complete link
                            $_e2g['prev_link'] = '<a href="' . $_e2g['prev_cat_link'] . $_e2g['prev_cat_permalink'] . '">'
                                    . $_e2g['prev_cat_symbol'] . ' ' . $_e2g['prev_cat_name']
                                    . '</a>';
                        }

                        $navUp = $this->_navPrevUpNext('up', $staticKey, $dynamicKey, $cat_orderBy, $cat_order);
                        if ($navUp !== FALSE) {
                            $_e2g['up_cat_link'] = $navUp['link'];
                            $_e2g['up_cat_name'] = $navUp['cat_name'];
                            $_e2g['up_cat_symbol'] = $navUpSymbol;
                            if (isset($staticTag))
                                $_e2g['up_cat_permalink'] = '#' . $e2gStaticInstances . '_' . $staticTag;
                            else
                                $_e2g['up_cat_permalink'] = '#' . $e2gStaticInstances . '_' . $navUp['cat_id'];

                            // complete link
                            $_e2g['up_link'] = '<a href="' . $_e2g['up_cat_link'] . $_e2g['up_cat_permalink'] . '">'
                                    . $_e2g['up_cat_symbol'] . ' ' . $_e2g['up_cat_name']
                                    . '</a>';
                        }

                        $navNext = $this->_navPrevUpNext('next', $staticKey, $dynamicKey, $cat_orderBy, $cat_order);
                        if ($navNext !== FALSE) {
                            $_e2g['next_cat_link'] = $navNext['link'];
                            $_e2g['next_cat_name'] = $navNext['cat_name'];
                            $_e2g['next_cat_symbol'] = $navNextSymbol;
                            if (isset($staticTag))
                                $_e2g['next_cat_permalink'] = '#' . $e2gStaticInstances . '_' . $staticTag;
                            else
                                $_e2g['next_cat_permalink'] = '#' . $e2gStaticInstances . '_' . $navNext['cat_id'];

                            // complete link
                            $_e2g['next_link'] = '<a href="' . $_e2g['next_cat_link'] . $_e2g['next_cat_permalink'] . '">'
                                    . $_e2g['next_cat_name'] . ' ' . $_e2g['next_cat_symbol']
                                    . '</a>';
                        }
                    } // if ($navPrevUpNext == 1)
                } // if ($multipleGidsCount == 1)
            } // foreach ($multipleGids as $singleGid)
            //******************************************************************/
            //*                 FOLDERS/DIRECTORIES/GALLERIES                  */
            //******************************************************************/
            if ($showOnly != 'images') {
                // if &tag is set
                if (isset($staticTag)) {
                    $dir_select = 'SELECT * FROM ' . $modx->db->config['table_prefix'] . 'easy2_dirs ';

                    // OPEN the selected tagged folder
                    if (isset($_GET['gid'])
                            && $staticTag == $tag
                            && $this->_tagsIds('dir', $tag, $_GET['gid'])) {
                        $dir_select .= 'WHERE parent_id IN (' . $_GET['gid'] . ')';
                    } else {
                        // the selected tag of multiple tags on the same page
                        if ($staticTag == $tag) {
                            $multipleTags = @explode(',', $tag);
                        }
                        // the UNselected tag of multiple tags on the same page
                        else {
                            $multipleTags = @explode(',', $staticTag);
                        }

                        for ($i = 0; $i < count($multipleTags); $i++) {
                            if ($i == 0)
                                $dir_select .= 'WHERE cat_tag LIKE \'%' . $multipleTags[$i] . '%\' ';
                            else
                                $dir_select .= 'OR cat_tag LIKE \'%' . $multipleTags[$i] . '%\' ';
                        }
                    }

                    if ($excludeDirsWebAccess !== FALSE) {
                        $dir_select .= 'AND cat_id NOT IN (' . $excludeDirsWebAccess . ') ';
                    }

                    $dir_select .= 'AND cat_visible = 1 ORDER BY ' . $cat_orderBy . ' ' . $cat_order . ' ';

                    // to separate the multiple &gid snippet parameters on the same page
                    $dir_select .= ( $_GET['tag'] == $staticTag) ? 'LIMIT ' . ( $gpn * $limit ) . ', ' . $limit : 'LIMIT 0, ' . $limit;
                }
                // original &gid parameter
                else {
                    $dir_select = 'SELECT DISTINCT d.* '
                            . 'FROM ' . $modx->db->config['table_prefix'] . 'easy2_dirs AS d '
                            . 'WHERE '
                    ;

                    if ($gid != '*') {
                        if ($this->_checkGidDecendant((isset($_GET['gid']) ? $_GET['gid'] : $gid), $staticGid) == TRUE) {
                            $dir_select .= 'd.parent_id IN (' . $gid . ') AND ';
                        } else {
                            $dir_select .= 'd.parent_id IN (' . $staticGid . ') AND ';
                        }
                    }

                    if (isset($whereDir)) {
                        $where = $this->_whereClause($whereDir, 'd');
                        if ($where === FALSE)
                            return FALSE;
                        else
                            $dir_select .= $where . ' AND ';
                    }

                    if ($excludeDirsWebAccess !== FALSE) {
                        $dir_select .= 'd.cat_id NOT IN (' . $excludeDirsWebAccess . ') AND ';
                    }

                    // ddim -- http://modxcms.com/forums/index.php/topic,48314.msg286241.html#msg286241
                    $dir_select .= 'd.cat_visible = 1 '
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
                            . 'ORDER BY ' . $cat_orderBy . ' ' . $cat_order . ' ';
                    $dir_select .= 'LIMIT ' . ( $gpn * $limit ) . ', ' . $limit;

//                    // to separate the multiple &gid snippet parameters on the same page
//                    if ($this->_checkGidDecendant( (isset($_GET['gid'])? $_GET['gid'] : $gid) , $staticGid)==TRUE) {
//                        $dir_select .= 'LIMIT ' . ( $gpn * $limit ) . ', ' . $limit;
//                    }
//                    else {
//                        $dir_select .= 'LIMIT 0, ' . $limit;
//                    }
                }

                $dir_query = mysql_query($dir_select);
                if (!$dir_query) {
                    echo __LINE__ . ' : ' . mysql_error() . '<br />' . $dir_select . '<br />';
                    return FALSE;
//                    die(__LINE__ . ' : ' . mysql_error() . '<br />' . $dir_select);
                }
                $dirNumRows += mysql_num_rows($dir_query);

                // gallery's permalink
                if (isset($tag)
                        && ($this->_checkGidDecendant((isset($_GET['gid']) ? $_GET['gid'] : $gid), $staticGid) == TRUE)
                ) {
                    $permalinkName = $e2gStaticInstances . '_' . $staticTag;
                } elseif ($this->_checkGidDecendant((isset($_GET['gid']) ? $_GET['gid'] : $gid), $staticGid) == FALSE) {
                    $permalinkName = $e2gStaticInstances . '_' . $staticGid;
                } else {
                    $permalinkName = $e2gStaticInstances . '_' . $gid;
                }
                $permalinkName = $modx->stripAlias($permalinkName);
                $_e2g['permalink'] = '<a href="#" name="' . $permalinkName . '"></a>';

                // gallery's description
                if ($galDesc == '1'
                        // exclude the multiple gids (comma separated)
                        && !strpos($staticGid, ',')
                ) {
                    $gallery_id = '';
                    if ($this->_checkGidDecendant((isset($_GET['gid']) ? $_GET['gid'] : $gid), $staticGid) == FALSE) {
                        $gallery_id = $staticGid;
                    } else {
                        $gallery_id = $singleGid;
                    }

                    $_e2g['cat_description'] = $this->_getDirInfo($gallery_id, 'cat_description');
                    $_e2g['cat_title'] = $this->_getDirInfo($gallery_id, 'cat_alias');

                    $_e2g['title'] = ($_e2g['cat_title'] != '' ? $_e2g['cat_title'] : $_e2g['cat_name'] );
                    if ($_e2g['title'] == '' && $_e2g['cat_description'] == '') {
                        $_e2g['desc_class'] = 'style="display:none;"';
                    }
                } else {
                    $_e2g['desc_class'] = 'style="display:none;"';
                } // gallery's description
                //******************************************************************/
                //*       Fill up the current directory's thumbnails content       */
                //******************************************************************/

                $i = 0;
                while ($l = mysql_fetch_array($dir_query, MYSQL_ASSOC)) {
                    if (isset($staticTag))
                        $l['permalink'] = $e2gStaticInstances . '_' . $staticTag;
                    else
                        $l['permalink'] = $e2gStaticInstances . '_' . $l['cat_id'];

                    if (isset($tag)) {
                        $l['cat_tag'] = '&amp;tag=' . $staticTag;
                    } else {
                        $l['cat_tag'] = '';
                    }

                    // search image for subdir
                    $l1 = $this->_folderImg($l['cat_id']);
                    // if there is an empty folder, or invalid content
                    if (!$l1)
                        continue;

                    $l['count'] = $l1['count'];

                    // path to subdir's thumbnail
                    $path1 = $this->_getPath($l1['dir_id']);

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
                    if ($dirNumRows > 0
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
                        if (mb_strlen($l['title'], $charset) > $catNameLen)
                            $l['title'] = mb_substr($l['title'], 0, $catNameLen - 1, $charset) . '...';
                    }
                    elseif (strlen($l['title']) > $catNameLen)
                        $l['title'] = substr($l['title'], 0, $catNameLen - 1) . '...';

                    $l['w'] = $this->e2gsnip_cfg['w'];
                    $l['h'] = $this->e2gsnip_cfg['h'];
                    $thq = $this->e2gsnip_cfg['thq'];

                    $l['src'] = $this->_imgShaper($gdir, $path1 . $l1['filename'], $l['w'], $l['h'], $thq);

                    // making flexible FURL or not
                    $l['link'] = $modx->makeUrl(
                                    $modx->documentIdentifier
                                    , $modx->aliases
                                    , 'sid=' . $e2gStaticInstances)
                            . '&amp;gid='
                    ;

                    /**
                     * invoke plugin for EACH gallery
                     */
                    // creating the plugin array's content
                    $e2gEvtParams = array();
                    $l['sid'] = $e2gStaticInstances;
                    foreach ($l as $k => $v) {
                        $e2gEvtParams[$k] = $v;
                    }

                    $l['dirpluginprerender'] = $this->_plugin('OnE2GWebDirPrerender', $e2gEvtParams);
                    $l['dirpluginrender'] = $this->_plugin('OnE2GWebDirRender', $e2gEvtParams);

                    // fill up the dir list with content
                    $_filler = $this->_filler($this->_tplDir(), $l);
                    $_e2g['content'] .= ( ($grid == 'css') ? $_filler : '<td>' . $_filler . '</td>');
                    $i++;
                } // while ($l = mysql_fetch_array($dir_query, MYSQL_ASSOC))
            } // if ($showOnly != 'images')
        } // else of if ($showOnly == 'images')
        //******************************************************************/
        //*             FILE content for the current directory             */
        //******************************************************************/

        if ($dirNumRows != $limit
                && $showOnly != 'folders'
                && !empty($gid)
        ) {

            /**
             * goldsky -- manage the pagination limit between dirs and files
             * (join the pagination AND the table grid).
             */
            $modulusDirCount = $dirCount % $limit;
            $fileThumbOffset = $limit - $modulusDirCount;
            $filePageOffset = ceil($dirCount / $limit);

            if (isset($staticTag)) {
                $fileSelect = 'SELECT * FROM ' . $modx->db->config['table_prefix'] . 'easy2_files ';

                // OPEN the selected tagged folder
                if (isset($_GET['gid'])
                        && $staticTag == $tag
                        && $this->_tagsIds('dir', $tag, $_GET['gid'])
                ) {
                    $fileSelect .= 'WHERE dir_id IN (' . $_GET['gid'] . ') AND ';
                } else {
                    // the selected tag of multiple tags on the same page
                    if ($staticTag == $tag) {
                        $multipleTags = @explode(',', $tag);
                    }
                    // the UNselected tag of multiple tags on the same page
                    else {
                        $multipleTags = @explode(',', $staticTag);
                    }

                    for ($i = 0; $i < count($multipleTags); $i++) {
                        if ($i == 0)
                            $fileSelect .= 'WHERE tag LIKE \'%' . $multipleTags[$i] . '%\' ';
                        else
                            $fileSelect .= 'OR tag LIKE \'%' . $multipleTags[$i] . '%\' ';
                        $fileSelect .= ' AND ';
                    }
                }
            }
            // exclude &tag snippet call, for the &gid parameter
            else {
                $fileSelect = 'SELECT * FROM ' . $modx->db->config['table_prefix'] . 'easy2_files '
                        . 'WHERE ';

                if ($gid != '*') {
                    if ($this->_checkGidDecendant((isset($_GET['gid']) ? $_GET['gid'] : $gid), $staticGid) == TRUE) {
                        $fileSelect .= 'dir_id IN (' . $gid . ') ';
                    } else {
                        $fileSelect .= 'dir_id IN (' . $staticGid . ') ';
                    }
                    $fileSelect .= ' AND ';
                }

                if (isset($whereFile)) {
                    $where = $this->_whereClause($whereFile);
                    if ($where === FALSE)
                        return FALSE;
                    else
                        $fileSelect .= $where . ' AND ';
                }

                if ($excludeFilesWebAccess !== FALSE) {
                    $fileSelect .= 'id NOT IN (' . $excludeFilesWebAccess . ') AND ';
                }
            }

            $fileSelect .= 'status = 1 ORDER BY ' . $orderBy . ' ' . $order . ' ';

            /**
             * Calculate the available grid to be floated
             */
            if ($fileThumbOffset > 0 && $fileThumbOffset < $limit) {
                $fileSelect .= 'LIMIT '
                        . ( $dirNumRows > 0 ?
                                ( ' 0, ' . ( $fileThumbOffset ) ) :
                                ( ( ( $gpn - $filePageOffset) * $limit) + $fileThumbOffset ) . ', ' . $limit );
            } elseif ($fileThumbOffset != 0 || $fileThumbOffset == $limit) {
                $fileSelect .= 'LIMIT '
                        . ( $modulusDirCount > 0 ?
                                ( ' 0, ' . ( $fileThumbOffset ) ) :
                                ( ( ( $gpn - $filePageOffset) * $limit) ) . ', ' . $limit );
            } else { // $fileThumbOffset == 0 --> No sub directory
                $fileSelect .= 'LIMIT ' . ( $gpn * $limit) . ', ' . $limit;
            }

            $fileQueryResult = mysql_query($fileSelect);
            if (!$fileQueryResult) {
                echo __LINE__ . ' : ' . mysql_error() . '<br />' . $fileSelect . '<br />';
                return FALSE;
//                die(__LINE__ . ' : ' . mysql_error() . '<br />' . $fileSelect);
            }

            $fileNumRows = mysql_num_rows($fileQueryResult);

            /**
             * retrieve the content
             */
            $i = 0;

            // checking the $dirNumRows first
            if ($dirNumRows > 0
                    && $dirNumRows % $colls == 0
                    && $grid == 'table'
            ) {
                $_e2g['content'] .= '</tr><tr>';
            }

            while ($l = mysql_fetch_array($fileQueryResult, MYSQL_ASSOC)) {
                /**
                 * whether configuration setting is set with or without table, the template will adjust it
                 * goldsky -- this is where the file's thumb 'float' to the dirs' in TABLE grid
                 */
                if (( $i > 0 )
                        && ( ( $i + $dirNumRows ) % $colls == 0 )
                        && $grid == 'table') {
                    $_e2g['content'] .= '</tr><tr>';
                }

                $l['w'] = $this->e2gsnip_cfg['w'];
                $l['h'] = $this->e2gsnip_cfg['h'];

                if (isset($landingPage)) {
                    $l['link'] = $modx->makeUrl($landingPage
                                    , $modx->aliases
                                    , 'lp=' . $landingPage) . '&amp;fid=' . $l['id']
                    ;
                } else {
                    if ($imgSrc == 'generated') {
                        $l['link'] = 'assets/modules/easy2/show.easy2gallery.php?fid=' . $l['id'];
                    } elseif ($imgSrc == 'original') {

                        // path to subdir's thumbnail
                        $path = $this->_getPath($l['dir_id']);

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
                } // if ( isset($landingPage) )

                if ($l['description'] != '') {
                    $l['description'] = htmlspecialchars_decode(htmlspecialchars_decode($l['description'], ENT_QUOTES), ENT_QUOTES);
                }

                /**
                 * invoke plugin for EACH thumb
                 */
                // creating the plugin array's content
                $e2gEvtParams = array();
                $l['sid'] = $e2gStaticInstances;
                foreach ($l as $k => $v) {
                    $e2gEvtParams[$k] = $v;
                }

                $l['thumbpluginprerender'] = $this->_plugin('OnE2GWebThumbPrerender', $e2gEvtParams);
                $l['thumbpluginrender'] = $this->_plugin('OnE2GWebThumbRender', $e2gEvtParams);

                // whether configuration setting is set with or without table, the template will adjust it
                $_filler = $this->_filler($this->_tplThumb(), $this->_libsThumb($l));
                $_e2g['content'] .= ( ($grid == 'css') ? $_filler : '<td>' . $_filler . '</td>');
                $i++;
            } // while ($l = @mysql_fetch_array($fileQueryResult, MYSQL_ASSOC))
        } // if( $dirNumRows!=$limit && $showOnly!='folders' && !empty($gid) )

        $_e2g['content'] .= ( ($grid == 'css') ? '</div>' : '</tr></table>');

        //******************************************************************/
        //*                          BACK BUTTON                           */
        //******************************************************************/
        if ($_e2g['parent_id'] > 0
                && ($this->_checkGidDecendant((isset($_GET['gid']) ? $_GET['gid'] : $gid), $staticGid) == TRUE)
                && (isset($staticTag) ? $staticTag == $tag : NULL)
        ) {
            $_e2g['back'] = '<span class="' . $backClass . '">&laquo; <a href="'
                    // making flexible FURL or not
                    . $modx->makeUrl($modx->documentIdentifier
                            , $modx->aliases
                            , 'sid=' . $e2gStaticInstances)
                    . '&amp;gid=' . $_e2g['parent_id']
                    . (isset($staticTag) ? '&amp;tag=' . $staticTag : '' )
                    . '#' . $e2gStaticInstances . '_'
                    . (isset($staticTag) ? $staticTag : $_e2g['parent_id'] )
                    . '">' . $_e2g['parent_name'] . '</a></p>';
        }

        //**********************************************************************/
        //*                       PAGINATION: PAGE LINKS                       */
        //*             joining between dirs and files pagination              */
        //**********************************************************************/
        if ($pagination == 1 && $orderBy != 'rand()' && $cat_orderBy != 'rand()') {
            // count the files again, this time WITHOUT limit!
            if ($showOnly == 'folders') {
                $fileCount = 0;
            } elseif (!empty($gid)) {
                if (isset($staticTag)) {
                    $fileCountSelect = 'SELECT COUNT(id) FROM ' . $modx->db->config['table_prefix'] . 'easy2_files ';

                    // OPEN the selected tagged folder
                    if (isset($_GET['gid'])
                            && $staticTag == $tag
                            && $this->_tagsIds('dir', $tag, $_GET['gid'])) {
                        $fileCountSelect .= 'WHERE dir_id IN (' . $_GET['gid'] . ')';
                    } else {
                        // the selected tag of multiple tags on the same page
                        if ($staticTag == $tag) {
                            $multipleTags = @explode(',', $tag);
                        }
                        // the UNselected tag of multiple tags on the same page
                        else {
                            $multipleTags = @explode(',', $staticTag);
                        }

                        for ($i = 0; $i < count($multipleTags); $i++) {
                            if ($i == 0)
                                $fileCountSelect .= 'WHERE tag LIKE \'%' . $multipleTags[$i] . '%\' ';
                            else
                                $fileCountSelect .= 'OR tag LIKE \'%' . $multipleTags[$i] . '%\' ';
                        }
                    }
                }
                // default
                else {
                    $fileCountSelect = 'SELECT COUNT(id) FROM ' . $modx->db->config['table_prefix'] . 'easy2_files ';

                    if ($gid != '*') {
                        if ($this->_checkGidDecendant((isset($_GET['gid']) ? $_GET['gid'] : $gid), $staticGid) == TRUE) {
                            $fileCountSelect .= 'WHERE dir_id IN (' . $gid . ') ';
                        } else {
                            $fileCountSelect .= 'WHERE dir_id IN (' . $staticGid . ') ';
                        }
                    }

                    if (isset($whereFile)) {
                        $where = $this->_whereClause($whereFile);
                        if ($where === FALSE)
                            return FALSE;
                        else {
                            if ($gid != '*')
                                $fileCountSelect .= ' AND ' . $where;
                            else
                                $fileCountSelect .= ' WHERE ' . $where;
                        }
                    }

                    if ($excludeFilesWebAccess !== FALSE) {
                        $fileCountSelect .= ' AND id NOT IN (' . $excludeFilesWebAccess . ')';
                    }
                }

                $fileCountQuery = mysql_query($fileCountSelect);
                if (!$fileCountQuery) {
                    echo __LINE__ . ' : ' . mysql_error() . '<br />' . $fileCountSelect . '<br />';
                    return FALSE;
//                    or die(__LINE__ . ' : ' . mysql_error() . '<br />' . $fileCountSelect);
                }
                $fileCount = mysql_result($fileCountQuery, 0, 0);
            }

            $totalCount = $dirCount + $fileCount;

            if ($totalCount === 0)
                return;
            if ($totalCount > $limit) {
                $_e2g['pages'] = '<div class="' . $pageNumClass . '">';
                $i = 0;
                while ($i * $limit < $totalCount) {
                    // using &tag parameter
                    if (isset($staticTag)) {
                        if ($i == $gpn) {
                            $_e2g['pages'] .= '<b>' . ($i + 1) . '</b> ';
                        } else {
                            $permalinkName = $modx->stripAlias($e2gStaticInstances . '_' . $staticTag);
                            // making flexible FURL or not
                            $pagesLink = $modx->makeUrl($modx->documentIdentifier
                                            , $modx->aliases
                                            , 'sid=' . $e2gStaticInstances)
                                    . '&amp;tag=' . $staticTag
                                    . ( isset($_GET['gid']) ? '&amp;gid=' . $_GET['gid'] : '' )
                                    . '&amp;gpn=' . $i . $customGetParams
                                    . '#' . $permalinkName;
                            $pagesLink = str_replace(' ', '', $pagesLink);
                            $_e2g['pages'] .= '<a href="' . $pagesLink . '">' . ($i + 1) . '</a> ';
                        }
                    }
                    // original &gid parameter
                    else {
                        if ($i == $gpn) {
                            $_e2g['pages'] .= '<b>' . ($i + 1) . '</b> ';
                        } else {
                            $permalinkName = $e2gStaticInstances . '_'
                                    . ( ( isset($staticGid)
                                    && ( $this->_checkGidDecendant((isset($_GET['gid']) ? $_GET['gid'] : $gid), $staticGid) == TRUE ) ) ? $gid : $staticGid );
                            $permalinkName = $modx->stripAlias($permalinkName);
                            // making flexible FURL or not
                            $pagesLink = $modx->makeUrl($modx->documentIdentifier
                                            , $modx->aliases
                                            , 'sid=' . $e2gStaticInstances)
                                    . ( ( isset($staticGid)
                                    && ( $this->_checkGidDecendant((isset($_GET['gid']) ? $_GET['gid'] : $gid), $staticGid) == TRUE ) ) ? '&amp;gid=' . $gid : '&amp;gid=' . $staticGid )
                                    . '&amp;gpn=' . $i
                                    . $customGetParams
                                    . '#' . $permalinkName;
                            $pagesLink = str_replace(' ', '', $pagesLink);
                            $_e2g['pages'] .= '<a href="' . $pagesLink . '">' . ($i + 1) . '</a> ';
                        }
                    }
                    $i++;
                }
                $_e2g['pages'] .= '</div>';
            }
        }

        // Gallery's wrapper ID
        $_e2g['wrapper'] = $e2gWrapper;

        // MULTIPLE INSTANCES id
        $_e2g['sid'] = $e2gStaticInstances;

        /**
         * invoke plugin for the MAIN gallery
         */
        $_e2g['gallerypluginprerender'] = $this->_plugin('OnE2GWebGalleryPrerender', array(
                    'pages' => $_e2g['pages']
                    , 'parent_id' => $_e2g['parent_id']
                    , 'desc_class' => $_e2g['desc_class']
                    , 'cat_name' => $_e2g['cat_name']
                    , 'permalink' => $_e2g['permalink']
                    , 'wrapper' => $_e2g['wrapper']
                    , 'sid' => $_e2g['sid']
                ));
        $_e2g['gallerypluginrender'] = $this->_plugin('OnE2GWebGalleryRender', array(
                    'pages' => $_e2g['pages']
                    , 'parent_id' => $_e2g['parent_id']
                    , 'desc_class' => $_e2g['desc_class']
                    , 'cat_name' => $_e2g['cat_name']
                    , 'permalink' => $_e2g['permalink']
                    , 'wrapper' => $_e2g['wrapper']
                    , 'sid' => $_e2g['sid']
                ));

        return $this->_filler($this->_tplGal(), $_e2g);
    }

    /**
     * Gallery for &fid parameter
     * @return mixed the image's thumbail delivered in template
     */
    private function _imgFile() {
        $modx = $this->modx;
        $fid = $this->e2gsnip_cfg['fid'];
        $colls = $this->e2gsnip_cfg['colls'];
//        $notables = $this->e2gsnip_cfg['notables']; // deprecated
        $grid = $this->e2gsnip_cfg['grid'];
        $gridClass = $this->e2gsnip_cfg['grid_class'];
        $landingPage = $this->e2gsnip_cfg['landingpage'];
//        $plugin = $this->e2gsnip_cfg['plugin'];
        $imgSrc = $this->e2gsnip_cfg['img_src'];
        $e2gWrapper = $this->e2gsnip_cfg['e2g_wrapper'];

        $fileSelect = 'SELECT * FROM ' . $modx->db->config['table_prefix'] . 'easy2_files '
                . 'WHERE id IN (' . $fid . ') '
                . 'AND status = 1 ';
        $res = mysql_query($fileSelect);
        if (!$res) {
            return __LINE__ . ' : ' . mysql_error() . '<br />' . $fileSelect;
        }
        $resNum = mysql_num_rows($res);
        if ($resNum === 0)
            return;

        // just to hide gallery's description CSS box in gallery template
        if (!isset($_e2g['title']) || !isset($_e2g['cat_description'])) {
            $_e2g['desc_class'] = 'style="display:none;"';
        } else
            $_e2g['e2gdir_class'] = '';

        // START the grid
        $_e2g['content'] .= ( ($grid == 'css') ? '<div class="' . $gridClass . '">' : '<table class="' . $gridClass . '"><tr>');

        $this->_libs();
        $i = 0;
        while ($l = mysql_fetch_array($res, MYSQL_ASSOC)) {
            // create row grid
            if (( $i > 0 ) && ( $i % $colls == 0 ) && $grid == 'table')
                $_e2g['content'] .= '</tr><tr>';

            $l['w'] = $this->e2gsnip_cfg['w'];
            $l['h'] = $this->e2gsnip_cfg['h'];

            if (isset($landingPage)) {
                $l['link'] = $modx->makeUrl($landingPage
                                , $modx->aliases
                                , 'lp=' . $landingPage) . '&amp;fid=' . $l['id']
                ;
            } else {
                if ($imgSrc == 'generated') {
                    $l['link'] = 'assets/modules/easy2/show.easy2gallery.php?fid=' . $l['id'];
                } elseif ($imgSrc == 'original') {

                    // path to subdir's thumbnail
                    $path = $this->_getPath($l['dir_id']);

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

            /**
             * invoke plugin for EACH thumb
             */
            // creating the plugin array's content
            $e2gEvtParams = array();
            $l['sid'] = $e2gStaticInstances;
            foreach ($l as $k => $v) {
                $e2gEvtParams[$k] = $v;
            }

            $l['thumbpluginprerender'] = $this->_plugin('OnE2GWebThumbPrerender', $e2gEvtParams);
            $l['thumbpluginrender'] = $this->_plugin('OnE2GWebThumbRender', $e2gEvtParams);

            // whether configuration setting is set with or without table, the template will adjust it
            $_filler = $this->_filler($this->_tplThumb(), $this->_libsThumb($l));
            $_e2g['content'] .= ( ($grid == 'css') ? $_filler : '<td>' . $_filler . '</td>');
            $i++;
        }

        // Gallery's wrapper ID
        $_e2g['wrapper'] = $e2gWrapper;

        // END the grid
        $_e2g['content'] .= ( ($grid == 'css') ? '</div>' : '</tr></table>');

        return $this->_filler($this->_tplGal(), $_e2g);
    }

    /**
     * To create a random image usng the &rgid parameter
     * @return mixed the image's thumbail delivered in template
     */
    private function _imgRandom() {
        $modx = $this->modx;
        $limit = $this->e2gsnip_cfg['limit'];
        $rgid = $this->e2gsnip_cfg['rgid'];
//        $notables = $this->e2gsnip_cfg['notables'];  // deprecated
        $grid = $this->e2gsnip_cfg['grid'];
        $gridClass = $this->e2gsnip_cfg['grid_class'];
        $landingPage = $this->e2gsnip_cfg['landingpage'];
//        $plugin = $this->e2gsnip_cfg['plugin'];
        $imgSrc = $this->e2gsnip_cfg['img_src'];
        $e2gWrapper = $this->e2gsnip_cfg['e2g_wrapper'];

        $q = 'SELECT * FROM ' . $modx->db->config['table_prefix'] . 'easy2_files '
                . 'WHERE status = 1 '
                . 'AND dir_id IN (' . $rgid . ') '
                . 'ORDER BY RAND() '
                . 'LIMIT 1'
        ;

        $res = mysql_query($q);
        $numRows = mysql_num_rows($res);
        if ($numRows === 0)
            return;

        // START the grid
        $_e2g['content'] .= ( ($grid == 'css') ? '<div class="' . $gridClass . '">' : '<table class="' . $gridClass . '"><tr>');

        $this->_libs();

        while ($l = mysql_fetch_array($res, MYSQL_ASSOC)) {
            // just to hide gallery's description CSS box in gallery template
            if (!isset($_e2g['title']) || !isset($_e2g['cat_description'])) {
                $_e2g['desc_class'] = 'style="display:none;"';
            } else
                $_e2g['e2gdir_class'] = '';


            $l['w'] = $this->e2gsnip_cfg['w'];
            $l['h'] = $this->e2gsnip_cfg['h'];

            /**
             * invoke plugin for EACH thumb
             */
            // creating the plugin array's content
            $e2gEvtParams = array();
            foreach ($l as $k => $v) {
                $e2gEvtParams[$k] = $v;
            }

            $l['thumbpluginprerender'] = $this->_plugin('OnE2GWebThumbPrerender', $e2gEvtParams);
            $l['thumbpluginrender'] = $this->_plugin('OnE2GWebThumbRender', $e2gEvtParams);

            if (isset($landingPage)) {
                $l['link'] = $modx->makeUrl($landingPage
                                , $modx->aliases
                                , 'lp=' . $landingPage) . '&amp;fid=' . $l['id']
                ;
            } else {
                if ($imgSrc == 'generated') {
                    $l['link'] = 'assets/modules/easy2/show.easy2gallery.php?fid=' . $l['id'];
                } elseif ($imgSrc == 'original') {

                    // path to subdir's thumbnail
                    $path = $this->_getPath($l['dir_id']);

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
            $_filler = $this->_filler($this->_tplRandom(), $this->_libsThumb($l));
            $_e2g['content'] .= ( ($grid == 'css') ? $_filler : '<td>' . $_filler . '</td>');
        }

        // Gallery's wrapper ID
        $_e2g['wrapper'] = $e2gWrapper;

        // END the grid
        $_e2g['content'] .= ( ($grid == 'css') ? '</div>' : '</tr></table>');

        return $this->_filler($this->_tplGal(), $_e2g);
    }

    /**
     * To get and create thumbnails
     * @param  int    $gdir        from $_GET['gid']
     * @param  string $path        directory path of each of thumbnail
     * @param  int    $w           thumbnail width
     * @param  int    $h           thumbnail height
     * @param  int    $thq         thumbnail quality
     * @param  string $resizeType 'inner' | 'resize'
     *                             'inner' = crop the thumbnail
     *                             'resize' = autofit the thumbnail
     * @param  int    $red         Red in RGB
     * @param  int    $green       Green in RGB
     * @param  int    $blue        Blue in RGB
     * @param  bool   $wmtrigger   Watermark trigger, to create water mark
     * @return mixed FALSE/the thumbail's path
     */
    private function _imgShaper($gdir, $path, $w, $h, $thq, $resizeType=NULL, $red=NULL, $green=NULL, $blue=NULL, $wmtrigger = 0) {
        $modx = $this->modx;
        // decoding UTF-8
        $gdir = $this->_e2gDecode($gdir);
        $path = $this->_e2gDecode($path);
        if (empty($path))
            return FALSE;

        $w = !empty($w) ? $w : $this->e2gsnip_cfg['w'];
        $h = !empty($h) ? $h : $this->e2gsnip_cfg['h'];
        $thq = !empty($thq) ? $thq : $this->e2gsnip_cfg['thq'];
        $resizeType = isset($resizeType) ? $resizeType : $this->e2gsnip_cfg['resize_type'];
        $red = isset($red) ? $red : $this->e2gsnip_cfg['thbg_red'];
        $green = isset($green) ? $green : $this->e2gsnip_cfg['thbg_green'];
        $blue = isset($blue) ? $blue : $this->e2gsnip_cfg['thbg_blue'];

//        $thumbPath = '_thumbnails/'.substr($path, 0, strrpos($path, '.')).'_'.$w.'x'.$h.'.jpg';
        /**
         * Use document ID and session ID to separate between different snippet calls
         * on the same/different page(s) with different settings
         * but unfortunately with the same dimension.
         */
        $e2gStaticInstances = $this->e2gsnip_cfg['e2g_static_instances'];
        $docid = $modx->documentIdentifier;
        $thumbPath = '_thumbnails/' . substr($path, 0, strrpos($path, '.')) . '_id' . $docid . '_sid' . $e2gStaticInstances . '_' . $w . 'x' . $h . '.jpg';

        /**
         * CREATE THUMBNAIL
         */
        // goldsky -- alter the maximum execution time
        if (function_exists('set_time_limit'))
            @set_time_limit(0);

        if (!file_exists($gdir . $thumbPath) && file_exists($gdir . $path)) {
            // goldsky -- adds output buffer to avoid PHP's memory limit
            ob_start();

            $inf = @getimagesize($gdir . $path);
            if (!$inf)
                return FALSE;

            if ($inf[2] == 1)
                $im = imagecreatefromgif($gdir . $path);
            elseif ($inf[2] == 2)
                $im = imagecreatefromjpeg($gdir . $path);
            elseif ($inf[2] == 3)
                $im = imagecreatefrompng($gdir . $path);
            else
                return FALSE;

            if ($inf[0] / $w > 2.00 || $inf[1] / $h > 2.00) {
                $tmp_w = $w * 2.00;
                $tmp_h = round($inf[1] * ($tmp_w / $inf[0]), 2);

                $temp = imagecreatetruecolor($tmp_w, $tmp_h);
                imagecopyresized($temp, $im, 0, 0, 0, 0, $tmp_w, $tmp_h, $inf[0], $inf[1]);

                $inf[0] = $tmp_w;
                $inf[1] = $tmp_h;

                imagedestroy($im);
                $im = $temp;
            }

            /**
             * $resizeType == 'inner'
             * trim to default dimensions
             */
            if ($resizeType == 'inner') {
                // Shifts
                $x = $y = 0;

                // Dimensions
                $w2 = $w;
                $h2 = $h;

                if (($inf[0] / $inf[1]) > ($w / $h)) {
                    $w2 = round($inf[0] * $h / $inf[1], 2);
                    $x = ($w2 - $w) / 2.00 * (-1.00);
                } else {
                    $h2 = round($inf[1] * $w / $inf[0], 2);
                    $y = ($h2 - $h) / 2.00 * (-1.00);
                }

                $pic = imagecreatetruecolor($w, $h);
                $bgc = imagecolorallocate($pic, $red, $green, $blue);
                imagefill($pic, 0, 0, $bgc);
                imagecopyresampled($pic, $im, $x, $y, 0, 0, $w2, $h2, $inf[0], $inf[1]);
            } elseif ($resizeType == 'shrink') {
                /**
                 * $resizeType == 'shrink'
                 * ugly shrink to default dimensions
                 */
//                if ($inf[0] > $inf[1]) $h = round($inf[1] * $w / $inf[0], 2);
//                else $w = round($inf[0] * $h / $inf[1], 2);

                $pic = imagecreatetruecolor($w, $h);
                $bgc = imagecolorallocate($pic, $red, $green, $blue);
                imagefill($pic, 0, 0, $bgc);
                imagecopyresampled($pic, $im, 0, 0, 0, 0, $w, $h, $inf[0], $inf[1]);
            } elseif ($resizeType == 'resize') {
                /**
                 * $resizeType == 'resize'
                 * resize image with original proportional dimensions
                 */
                // Shifts
                $x = 0;
                $y = 0;

                // Dimensions
                $w2 = $w;
                $h2 = $h;

                if ($w > $h) {          // landscape thumbnail box
                    $w2 = round($inf[0] * $h / $inf[1], 2);
                    $x = abs($w - $w2) / 2.00;
                } elseif ($w == $h) {     // square thumbnail box
                    if ($inf[0] < $inf[1]) {// portrait image
                        $w2 = round($inf[0] * $h / $inf[1], 2);
                        $x = abs($w - $w2) / 2.00;
                    } elseif ($inf[0] == $inf[1]) {
                        $w2 = $w;
                        $h2 = $h;
                        $x = 0;
                        $y = 0;
                    } else {              // landscape image
                        $h2 = round($inf[1] * $w / $inf[0], 2);
                        $y = abs($h - $h2) / 2.00;
                    }
                } else {                  // portrait thumbnail box
                    $h2 = round($inf[1] * $w / $inf[0], 2);
                    $y = abs($h - $h2) / 2.00;
                }

                $pic = imagecreatetruecolor($w, $h);
                $bgc = imagecolorallocate($pic, $red, $green, $blue);
                imagefill($pic, 0, 0, $bgc);
                imagecopyresampled($pic, $im, $x, $y, 0, 0, $w2, $h2, $inf[0], $inf[1]);
            }
            else
                return FALSE;

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
                    return FALSE;
                @chmod($npath, 0755);
            }

            /**
             * create the thumbnails
             */
            imagejpeg($pic, $gdir . $thumbPath, $thq);
            /**
             * if set, this will create watermark
             */
            if ($wmtrigger == 1) {
                $this->_watermark($gdir . $thumbPath);
            }
            @chmod($gdir . $thumbPath, 0644);

            /**
             * image cache destroy
             */
            imagedestroy($pic);
            imagedestroy($im);

            // goldsky -- adds output buffer to avoid PHP's memory limit
            ob_end_clean();
        }
        // goldsky -- only to switch between localhost and live site.
//        if (strpos($_SERVER['DOCUMENT_ROOT'], '/') === (int) 0) {
        if (strtoupper(substr(PHP_OS, 0, 3) != 'WIN')) {
            $urlEncoding = $gdir . str_replace('%2F', '/', rawurlencode($thumbPath));
        } else
            $urlEncoding = $gdir . $this->_e2gEncode($thumbPath);
        return $urlEncoding;
    }

    /**
     * To get image's path
     * @param  int    $id     image's ID
     * @param  string $option the image's title options
     * @return string the path returns as an array
     */
    private function _getPath($id, $option=FALSE) {
        $modx = $this->modx;

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
            return NULL;
        return $result;
    }

    /**
     * To get directory's information
     * @param  int    $dirId  gallery's ID
     * @param  string $field  database field
     * @return mixed  the directory's data in an array
     */
    private function _getDirInfo($dirId, $field) {
        return parent::getDirInfo($dirId, $field);
    }

    /**
     * To get file's information
     * @param  int    $fileId  file's ID
     * @param  string $field  database field
     * @return mixed  the file's data in an array
     */
    private function _getFileInfo($fileId, $field) {
        return parent::getFileInfo($fileId, $field);
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
     * @param  int    $gid folder's ID
     * @return string image's source
     */
    private function _folderImg($gid) {
        $modx = $this->modx;
        $catThumbOrderBy = $this->e2gsnip_cfg['cat_thumb_orderby'];
        $catThumbOrder = $this->e2gsnip_cfg['cat_thumb_order'];

        // http://modxcms.com/forums/index.php/topic,23177.msg273448.html#msg273448
        // ddim -- http://modxcms.com/forums/index.php/topic,48314.msg286241.html#msg286241
        $q = 'SELECT F.* '
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
                . 'AND A.cat_visible = 1'
                . ') '
                . 'ORDER BY A.cat_level ASC '
                . ') '
//                . 'ORDER BY F.id DESC '
        ;
        if ($catThumbOrderBy == 'random') {
            $q .= 'ORDER BY rand() ';
        } else {
            $q .= 'ORDER BY F.' . $catThumbOrderBy . ' ' . $catThumbOrder . ' ';
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
     * @return mixed the file inclusion or FALSE return
     */
    private function _libs() {
        $modx = $this->modx;
        $css = $this->e2gsnip_cfg['css'];
        $glib = $this->e2gsnip_cfg['glib'];

        // GLOBAL e2g CSS styles
        if (file_exists($css)) {
            $modx->regClientCSS($modx->config['base_url'] . $css, 'screen');
        }

        // Load the library from database.
        if (!isset($glibs)) {
            $glibs = $this->_loadsViewerConfigs($glib);
            if ($glibs === FALSE)
                return FALSE;
        }

        if (isset($glibs[$glib])) {
            // CSS STYLES
            if (!empty($glibs[$glib]['headers_css']) && $glibs[$glib]['autoload_css'] == 1) {
                foreach ($glibs[$glib]['headers_css'] as $vRegClientCSS) {
                    $modx->regClientCSS($vRegClientCSS, 'screen');
                }
            }
            // JS Libraries
            if (!empty($glibs[$glib]['headers_js']) && $glibs[$glib]['autoload_js'] == 1) {
                foreach ($glibs[$glib]['headers_js'] as $vRegClientJS) {
                    $modx->regClientStartupScript($vRegClientJS);
                }
            }
            // HTMLBLOCK

            if (!empty($glibs[$glib]['headers_html']) && $glibs[$glib]['autoload_html'] == 1) {
                $modx->regClientStartupHTMLBlock($glibs[$glib]['headers_html']);
            }
            unset($glib);
        }
        else
            return FALSE;
    }

    /**
     * To generate the display of each of thumbnail pieces from the Javascript libraries
     * @param  mixed $row  the thumbnail's data in an array
     * @return mixed the file inclusion, thumbnail sources, comment's controller
     */
    private function _libsThumb($row) {
        $modx = $this->modx;
        $gdir = $this->e2gsnip_cfg['gdir'];
        $css = $this->e2gsnip_cfg['css'];
        $glib = $this->e2gsnip_cfg['glib'];
        $landingPage = $this->e2gsnip_cfg['landingpage'];
        $mbstring = $this->e2gsnip_cfg['mbstring'];
        $charset = $this->e2gsnip_cfg['charset'];
        $nameLen = $this->e2gsnip_cfg['name_len'];
        $w = $this->e2gsnip_cfg['w'];
        $h = $this->e2gsnip_cfg['h'];
        $thq = $this->e2gsnip_cfg['thq'];
        // COMMENT
        $ecm = $this->e2gsnip_cfg['ecm'];
        $fid = $this->e2gsnip_cfg['fid'];
//        $modx->toPlaceholder('fid', $fid, 'easy2:');
        $modx->setPlaceholder('easy2:fid', $fid);

        // SLIDESHOW
        $showGroup = $this->e2gsnip_cfg['show_group'];
        $modx->setPlaceholder('easy2:show_group', $showGroup);

        // store the file ID for the library's caption below
        $fid = $row['id'];
        $glibs = $this->_loadsViewerConfigs($glib, $fid);

        if (isset($landingPage)) {
            $row['glibact'] = '';
        }
        // gallery's javascript library activation
        elseif (isset($glibs[$glib])) {
            $row['glibact'] = $glibs[$glib]['glibact'];
        }
        else
            return FALSE;

        $row['title'] = $row['name'];
        if ($row['name'] == '')
            $row['name'] = '&nbsp;';
        elseif ($mbstring) {
            if (mb_strlen($row['name'], $charset) > $nameLen)
                $row['name'] = mb_substr($row['name'], 0, $nameLen - 1, $charset) . '...';
        }
        elseif (strlen($row['name']) > $nameLen)
            $row['name'] = substr($row['name'], 0, $nameLen - 1) . '...';

        $path = $this->_getPath($row['dir_id']);
        if (count($path) > 1) {
            unset($path[1]);
            $path = implode('/', array_values($path)) . '/';
        } else {
            $path = '';
        }

        $row['src'] = $this->_imgShaper($gdir, $path . $row['filename'], $w, $h, $thq);

        /**
         * Comments on the thumbnails
         */
        // HIDE COMMENTS from Ignored IP Addresses
        $ip = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];

        $comStatusQuery = 'SELECT COUNT(ign_ip_address) '
                . 'FROM ' . $modx->db->config['table_prefix'] . 'easy2_ignoredip '
                . 'WHERE ign_ip_address=\'' . $ip . '\'';
        $comStatusRes = mysql_query($comStatusQuery);
        if (!$comStatusRes) {
            echo __LINE__ . ' : ' . mysql_error() . ' ' . $comStatusQuery . '<br />';
            return FALSE;
//            or die(__LINE__ . ' : ' . mysql_error());
        }
        while ($comRow = mysql_fetch_array($comStatusRes)) {
            $countIgnoredIp = $comRow['COUNT(ign_ip_address)'];
        }

        if ($ecm == 1 && ($countIgnoredIp == 0)) {
            $row['com'] = 'e2gcom' . ($row['comments'] == 0 ? 0 : 1);

            // iframe activation
            if (isset($glibs[$glib])) {
                $row['comments'] = '<a href="' . E2G_SNIPPET_URL . 'comments.easy2gallery.php?id=' . $row['id'] . '" ' . $glibs[$glib]['clibact'] . '>' . $row['comments'] . '</a>';
//                $row['comments'] = '<a href="' . E2G_SNIPPET_URL . 'comments.easy2gallery.php?id=' . $row['id'] . '" ' . $glibs[$glib]['comments'] . '>' . $row['comments'] . '</a>';
//                $row['commentslink'] = E2G_SNIPPET_URL . 'comments.easy2gallery.php?id=' . $row['id'] . '" ' . @rtrim($glibs[$glib]['comments'], '"');
            }
        } else {
            $row['comments'] = '&nbsp;';
            $row['com'] = 'not_display';
        }
        if (isset($glib))
            unset($glib);
        return $row;
    }

    private function _loadsViewerConfigs($glib, $fid=null) {
        $modx = $this->modx;
        // SLIDESHOW
        $showGroup = $this->e2gsnip_cfg['show_group'];
        $modx->setPlaceholder('easy2:show_group', $showGroup);
        $fid = $this->e2gsnip_cfg['fid'];
//        $modx->toPlaceholder('fid', $fid, 'easy2:');
        $modx->setPlaceholder('easy2:fid', $fid);

        if (empty($glib))
            return FALSE;

        $selectGlibs = 'SELECT * FROM ' . $modx->db->config['table_prefix'] . 'easy2_viewers '
                . 'WHERE name=\'' . $glib . '\'';

        $glibs = $modx->db->makeArray($modx->db->query($selectGlibs));

        if (empty($glibs))
            return FALSE;

        foreach ($glibs as $k => $v) {
            $glibs[$glib] = $v;
        }
        foreach ($glibs[$glib] as $deepKey => $deepVal) {
            $glibs[$glib][$deepKey] = htmlspecialchars_decode(trim($deepVal));
        }

        // remove the numeric key duplication after make a new string key
        unset($glibs[0]);

        $glibs[$glib]['headers_css'] = @explode('|', $glibs[$glib]['headers_css']);
        foreach ($glibs[$glib]['headers_css'] as $k => $v) {
            $glibs[$glib]['headers_css'][$k] = trim($v);
        }

        $glibs[$glib]['headers_js'] = @explode('|', $glibs[$glib]['headers_js']);
        foreach ($glibs[$glib]['headers_js'] as $k => $v) {
            $glibs[$glib]['headers_js'][$k] = trim($v);
        }

        // work around for non-parsed placeholder inside the <head> tag
        $glibs[$glib]['headers_html'] = str_replace('[+easy2:show_group+]', $showGroup, $glibs[$glib]['headers_html']);
        $glibs[$glib]['headers_html'] = str_replace('[+easy2:fid+]', $fid, $glibs[$glib]['headers_html']);

        return $glibs;
    }

    /**
     * Slideshow's controller
     * @return string the slideshow's images
     */
    private function _slideshow() {
        $modx = $this->modx;
        // database selection
        $gdir = $this->e2gsnip_cfg['gdir'];
        $gid = $this->e2gsnip_cfg['gid'];
        $fid = $this->e2gsnip_cfg['fid'];
        $rgid = $this->e2gsnip_cfg['rgid'];
        $gpn = $this->e2gsnip_cfg['gpn'];
        $e2gWrapper = $this->e2gsnip_cfg['e2g_wrapper'];

        if ($this->e2gsnip_cfg['orderby'] == 'random') {
            $orderBy = 'rand()';
            $order = '';
        } else {
            $orderBy = $this->e2gsnip_cfg['orderby'];
            $order = $this->e2gsnip_cfg['order'];
        }
        if ($this->e2gsnip_cfg['cat_orderby'] == 'random') {
            $cat_orderBy = 'rand()';
            $cat_order = '';
        } else {
            $cat_orderBy = $this->e2gsnip_cfg['cat_orderby'];
            $cat_order = $this->e2gsnip_cfg['cat_order'];
        }

        if ($this->e2gsnip_cfg['ss_orderby'] == 'random') {
            $ssOrderBy = 'rand()';
            $ssOrder = '';
        } else {
            $ssOrderBy = $this->e2gsnip_cfg['ss_orderby'];
            $ssOrder = $this->e2gsnip_cfg['ss_order'];
        }

        $ssLimit = $this->e2gsnip_cfg['ss_limit'];

        // initial slideshow's controller and headers
        $slideshow = $this->e2gsnip_cfg['slideshow'];
        $ssConfig = $this->e2gsnip_cfg['ss_config'];
        $ssIndexFile = $this->e2gsnip_cfg['ss_indexfile'];
        $ssCss = $this->e2gsnip_cfg['ss_css'];
        $ssJs = $this->e2gsnip_cfg['ss_js'];

        // thumbnail settings
        $w = $this->e2gsnip_cfg['w'];
        $h = $this->e2gsnip_cfg['h'];
        $thq = $this->e2gsnip_cfg['thq'];
        $resizeType = $this->e2gsnip_cfg['resize_type'];
        $thbgRed = $this->e2gsnip_cfg['thbg_red'];
        $thbgGreen = $this->e2gsnip_cfg['thbg_green'];
        $thbgBlue = $this->e2gsnip_cfg['thbg_blue'];

        // slideshow's image settings
        $ssImgSrc = $this->e2gsnip_cfg['ss_img_src'];
        $ssW = $this->e2gsnip_cfg['ss_w'];
        $ssH = $this->e2gsnip_cfg['ss_h'];
        $ssThq = $this->e2gsnip_cfg['ss_thq'];
        $ssResizeType = $this->e2gsnip_cfg['ss_resize_type'];
        $ssBg = $this->e2gsnip_cfg['ss_bg'];
        $ssRed = $this->e2gsnip_cfg['ss_red'];
        $ssGreen = $this->e2gsnip_cfg['ss_green'];
        $ssBlue = $this->e2gsnip_cfg['ss_blue'];

        // landscape/portrait image's ratio for the slideshow box
        $ssAllowedRatio = $this->e2gsnip_cfg['ss_allowedratio'];

        // self landingpage
        $css = $this->e2gsnip_cfg['css'];
        $js = $this->e2gsnip_cfg['js'];
        $landingPage = $this->e2gsnip_cfg['landingpage'];

        /**
         * Filtering the slideshow size ratio
         */
        if ($ssAllowedRatio != 'all') {
            // create min-max slideshow width/height ratio
            $ssXpldRatio = explode('-', $ssAllowedRatio);
            $ssMinRatio = trim($ssXpldRatio[0]);
            $ssMaxRatio = trim($ssXpldRatio[1]);
        }

        $_ssFile = array();
        if (!empty($gid) && $modx->documentIdentifier != $landingPage) {
            $select = 'SELECT * FROM ' . $modx->db->config['table_prefix'] . 'easy2_files '
                    . 'WHERE dir_id IN (' . $gid . ') ';

            if ($ssAllowedRatio != 'all') {
                $select .= 'AND width/height >=' . floatval($ssMinRatio) . ' AND width/height<=' . floatval($ssMaxRatio) .' ';
            }

            $select .= 'AND status = 1 '
                    . 'ORDER BY ' . $ssOrderBy . ' ' . $ssOrder . ' '
                    . ( $ssLimit == 'none' ? '' : 'LIMIT ' . ( $gpn * $ssLimit ) . ', ' . $ssLimit )
            ;
            $query = mysql_query($select);
            if (!$query) {
                $o = 'snippet calls wrong gallery id:' . $gid . ', order, or wrong limit.<br />';
                $o .= $select . '<br />';
                $o .= mysql_error();
                return $o;
            }

            while ($fetch = mysql_fetch_array($query)) {
                $path = $this->_getPath($fetch['dir_id']);
                if (count($path) > 1) {
                    unset($path[1]);
                    $path = implode('/', array_values($path)) . '/';
                } else {
                    $path = '';
                }

                $_ssFile['id'][] = $fetch['id'];
                $_ssFile['dirid'][] = $fetch['dir_id'];
                $_ssFile['src'][] = $this->_e2gDecode($gdir . $path . $fetch['filename']);
                $_ssFile['filename'][] = $fetch['filename'];
                $_ssFile['title'][] = ($fetch['name'] != '' ? $fetch['name'] : $fetch['filename']);
                $_ssFile['name'][] = $fetch['name'];
                $_ssFile['description'][] = htmlspecialchars_decode($fetch['description'], ENT_QUOTES);
                $path = $this->_getPath($fetch['dir_id']);
                if (count($path) > 1) {
                    unset($path[1]);
                    $path = implode('/', array_values($path)) . '/';
                } else {
                    $path = '';
                }
                $_ssFile['thumbsrc'][] = $this->_imgShaper($gdir, $path . $fetch['filename'], $w, $h, $thq,
                                $resizeType, $thbgRed, $thbgGreen, $thbgBlue);
                if ($ssImgSrc == 'generated') {
                    /**
                     * + WATERMARK-ing
                     */
                    $_ssFile['resizedimg'][] = $this->_imgShaper($gdir, $path . $fetch['filename'], $ssW, $ssH, $ssThq,
                                    $ssResizeType, $ssRed, $ssGreen, $ssBlue, 1);
                } elseif ($ssImgSrc == 'original')
                    $_ssFile['resizedimg'][] = $this->_e2gDecode($gdir . $path . $fetch['filename']);
                /**
                 * TODO: Making a work around if _imgShaper returns an empty result
                 */
            }
        }

        if (!empty($fid)) {
            $select = 'SELECT * FROM ' . $modx->db->config['table_prefix'] . 'easy2_files '
                    . 'WHERE id IN (' . $fid . ') ';

            if ($ssAllowedRatio != 'all') {
                $select .= 'AND width/height >=' . floatval($ssMinRatio) . ' OR width/height<=' . floatval($ssMaxRatio) .' ';
            }

            $select .= 'AND status = 1 '
            ;
            $query = mysql_query($select);
            if (!$query) {
                return 'snippet calls wrong file id:' . $fid;
            }

            while ($fetch = mysql_fetch_array($query)) {
                $path = $this->_getPath($fetch['dir_id']);
                if (count($path) > 1) {
                    unset($path[1]);
                    $path = implode('/', array_values($path)) . '/';
                } else {
                    $path = '';
                }
                $_ssFile['id'][] = $fetch['id'];
                $_ssFile['dirid'][] = $fetch['dir_id'];
                $_ssFile['src'][] = $this->_e2gDecode($gdir . $path . $fetch['filename']);
                $_ssFile['filename'][] = $fetch['filename'];
                $_ssFile['title'][] = ($fetch['name'] != '' ? $fetch['name'] : $fetch['filename']);
                $_ssFile['name'][] = $fetch['name'];
                $_ssFile['description'][] = htmlspecialchars_decode($fetch['description'], ENT_QUOTES);
                $path = $this->_getPath($fetch['dir_id']);
                if (count($path) > 1) {
                    unset($path[1]);
                    $path = implode('/', array_values($path)) . '/';
                } else {
                    $path = '';
                }
                $_ssFile['thumbsrc'][] = $this->_imgShaper($gdir, $path . $fetch['filename'], $w, $h, $thq,
                                $resizeType, $thbgRed, $thbgGreen, $thbgBlue);
                if ($ssImgSrc == 'generated') {
                    /**
                     * + WATERMARK-ing
                     */
                    $_ssFile['resizedimg'][] = $this->_imgShaper($gdir, $path . $fetch['filename'], $ssW, $ssH, $ssThq,
                                    $ssResizeType, $ssRed, $ssGreen, $ssBlue, 1);
                } elseif ($ssImgSrc == 'original')
                    $_ssFile['resizedimg'][] = $this->_e2gDecode($gdir . $path . $fetch['filename']);
            }
        }

        if (!empty($rgid)) {
            $select = 'SELECT * FROM ' . $modx->db->config['table_prefix'] . 'easy2_files '
                    . 'WHERE status = 1 '
                    . 'AND dir_id IN (' . $rgid . ') ';

            if ($ssAllowedRatio != 'all') {
                $select .= 'AND width/height >=' . floatval($ssMinRatio) . ' OR width/height<=' . floatval($ssMaxRatio) .' ';
            }

            $select .= 'ORDER BY RAND() '
                    . ( $ssLimit == 'none' ? '' : 'LIMIT 0,' . $ssLimit . ' ' )
            ;
            $query = mysql_query($select);
            if (!$query) {
                return 'snippet calls wrong random file id:' . $gid . ', or wrong limit';
            }
            while ($fetch = mysql_fetch_array($query)) {
                $path = $this->_getPath($fetch['dir_id']);
                if (count($path) > 1) {
                    unset($path[1]);
                    $path = implode('/', array_values($path)) . '/';
                } else {
                    $path = '';
                }
                $_ssFile['id'][] = $fetch['id'];
                $_ssFile['dirid'][] = $fetch['dir_id'];
                $_ssFile['src'][] = $this->_e2gDecode($gdir . $path . $fetch['filename']);
                $_ssFile['filename'][] = $fetch['filename'];
                $_ssFile['title'][] = ($fetch['name'] != '' ? $fetch['name'] : $fetch['filename']);
                $_ssFile['name'][] = $fetch['name'];
                $_ssFile['description'][] = htmlspecialchars_decode($fetch['description'], ENT_QUOTES);
                $path = $this->_getPath($fetch['dir_id']);
                if (count($path) > 1) {
                    unset($path[1]);
                    $path = implode('/', array_values($path)) . '/';
                } else {
                    $path = '';
                }
                $_ssFile['thumbsrc'][] = $this->_imgShaper($gdir, $path . $fetch['filename'], $w, $h, $thq,
                                $resizeType, $thbgRed, $thbgGreen, $thbgBlue);
                if ($ssImgSrc == 'generated') {
                    /**
                     * + WATERMARK-ing
                     */
                    $_ssFile['resizedimg'][] = $this->_imgShaper($gdir, $path . $fetch['filename'], $ssW, $ssH, $ssThq,
                                    $ssResizeType, $ssRed, $ssGreen, $ssBlue, 1);
                } elseif ($ssImgSrc == 'original') {
                    $_ssFile['resizedimg'][] = $this->_e2gDecode($gdir . $path . $fetch['filename']);
                }
            }
        }

        /**
         * if the counting below = 0 (zero), then should be considered inside
         * the slideshow types, while in some slideshows this doesn't matter.
         */
        $countSlideshowFiles = count($_ssFile['src']);

        /**
         * added the &fid parameter inside the &slideshow, to open a full page of the clicked image
         * into the specified landingpage ID
         */
        if (isset($_GET['fid']) && isset($landingPage) && $modx->documentIdentifier != $landingPage) {
            // making flexible FURL or not
            $redirectUrl = $modx->makeUrl($landingPage
                            , $modx->aliases
                            , 'sid=' . $e2gStaticInstances)
                    . '&amp;lp=' . $landingPage . '&amp;fid=' . $_GET['fid'];
            $modx->sendRedirect(htmlspecialchars_decode($redirectUrl));
        } elseif (isset($_GET['fid']) && !isset($landingPage)) {
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
                $path = $this->_getPath($fetch['dir_id']);
                if (count($path) > 1) {
                    unset($path[1]);
                    $path = implode('/', array_values($path)) . '/';
                } else {
                    $path = '';
                }
                $src = $gdir . $path . $fetch['filename'];

                // goldsky -- only to switch between localhost and live site.
                // TODO: need review!
                if ($ssImgSrc == 'original') {
//                    if (strpos($_SERVER['DOCUMENT_ROOT'], '/') === (int) 0) {
                    if (strtoupper(substr(PHP_OS, 0, 3) != 'WIN')) {
                        $l['src'] = rawurldecode(str_replace('%2F', '/', rawurlencode($src)));
                    } else
                        $l['src'] = $src;
                }
                elseif ($ssImgSrc == 'generated') {
                    /**
                     * + WATERMARK-ing
                     */
                    if (!isset($lpW) || !isset($lpH)) {
                        $inf = @getimagesize($this->_e2gDecode($src));
                        if (!isset($lpW))
                            $lpW = $inf[0];
                        if (!isset($lpH))
                            $lpH = $inf[1];
                        $inf = array();
                        unset($inf);
                    }
                    $filePath = $this->_imgShaper($gdir, $path . $fetch['filename'], $lpW, $lpH, $lpThq, $lpResizeType,
                                    $lpRed, $lpGreen, $lpBlue, 1);
//                    if (strpos($_SERVER['DOCUMENT_ROOT'], '/') === (int) 0) {
                    if (strtoupper(substr(PHP_OS, 0, 3) != 'WIN')) {
                        $l['src'] = rawurldecode(str_replace('%2F', '/', rawurlencode($filePath)));
                    } else
                        $l['src'] = $filePath;
                }

                $l['title'] = ($fetch['name'] != '' ? $fetch['name'] : $fetch['filename']);
                $l['name'] = $fetch['name'];
                $l['description'] = htmlspecialchars_decode($fetch['description'], ENT_QUOTES);
                $path = $this->_getPath($fetch['dir_id']);
                if (count($path) > 1) {
                    unset($path[1]);
                    $path = implode('/', array_values($path)) . '/';
                } else {
                    $path = '';
                }
            }
            $_ssFile = array();
            return $this->_filler($this->_tplPage(), $l);
        }
        /**
         * The DEFAULT display
         */ else {
            // use custom index file if it's been set inside snippet call.
            if (isset($ssIndexFile)) {
                if (file_exists($ssIndexFile)) {
                    ob_start();
                    include($ssIndexFile);
                    $ssDisplay = ob_get_contents();
                    ob_end_clean();
                } else {
                    $ssDisplay = 'slideshow index file <b>' . $ssIndexFile . '</b> is not found.';
                }
            }
            // include the available slideshow from database
            else {
                $selectIndexFile = 'SELECT indexfile FROM ' . $modx->db->config['table_prefix'] . 'easy2_slideshows '
                        . 'WHERE name = \'' . $slideshow . '\'';
                $queryIndexFile = mysql_query($selectIndexFile);
                if (!$queryIndexFile) {
                    return __LINE__ . ' : snippet calls wrong slideshow\'s name.';
                }
//                $dbIndexFile = mysql_result($queryIndexFile, 0, 0);
                $row = mysql_fetch_row($queryIndexFile);
                $dbIndexFile = $row[0];
                if ($dbIndexFile == '') {
                    return __LINE__ . ' : empty index file in database.';
                } elseif (file_exists($dbIndexFile)) {
                    ob_start();
                    include($dbIndexFile);
                    $ssDisplay = ob_get_contents();
                    ob_end_clean();
                } else {
                    return __LINE__ . ' : slideshow index file <b>' . $dbIndexFile . '</b> is not found.';
                }
            }
            $_ssFile = array();
        }
        $_ssFile = array();
        unset($_ssFile);

        /**
         * wrapping the slideshow with E2G's internal ID
         * TODO: create placeholder for slideshow wrapper
         */
        $output = '
<div id="' . $e2gWrapper . '">
';
        $output .= $ssDisplay;
        $output .= '
</div>
';

        return $output;
    }

    /**
     * A landing page to show the image, including information within it.
     * @param  int   $fileId file's ID
     * @return mixed scripts, images, and FALSE return
     */
    private function _landingPage($fileId) {
        $modx = $this->modx;

        $landingPage = $this->e2gsnip_cfg['landingpage'];
        if ($modx->documentIdentifier != $landingPage)
            return;
        $page_tpl_css = $this->e2gsnip_cfg['page_tpl_css'];

        $lp_img_src = $this->e2gsnip_cfg['lp_img_src'];
        $lpW = $this->e2gsnip_cfg['lp_w'];
        $lpH = $this->e2gsnip_cfg['lp_h'];
        $lpThq = $this->e2gsnip_cfg['lp_thq'];
        $lpResizeType = $this->e2gsnip_cfg['lp_resize_type'];
        $lpBg = $this->e2gsnip_cfg['lp_bg'];
        $lpRed = $this->e2gsnip_cfg['lp_red'];
        $lpGreen = $this->e2gsnip_cfg['lp_green'];
        $lpBlue = $this->e2gsnip_cfg['lp_blue'];

//        $plugin = $this->e2gsnip_cfg['plugin'];
        $gdir = $this->e2gsnip_cfg['gdir'];
        $css = $this->e2gsnip_cfg['css'];
        $js = $this->e2gsnip_cfg['js'];
        $ecm = $this->e2gsnip_cfg['ecm'];
        $e2gWrapper = $this->e2gsnip_cfg['e2g_wrapper'];

        if (!empty($css)) {
            $modx->regClientCSS($css, 'screen');
        }
        if (!empty($js)) {
            $modx->regClientStartupScript($js);
        }

        $select = 'SELECT * FROM ' . $modx->db->config['table_prefix'] . 'easy2_files '
                . 'WHERE id = ' . $fileId
        ;
        $query = mysql_query($select);
        if (!$query) {
            return __LINE__ . ' : snippet calls wrong file id.';
        }

        while ($fetch = mysql_fetch_array($query)) {
            $path = $this->_getPath($fetch['dir_id']);
            if (count($path) > 1) {
                unset($path[1]);
                $path = implode('/', array_values($path)) . '/';
            } else {
                $path = '';
            }

            // goldsky -- only to switch between localhost and live site.
            // TODO: need review!
            if ($lp_img_src == 'original') {
                $filePath = $gdir . $path . $fetch['filename'];
//                if (strpos($_SERVER['DOCUMENT_ROOT'], '/') === (int) 0) {
                if (strtoupper(substr(PHP_OS, 0, 3) != 'WIN')) {
                    $l['src'] = rawurldecode(str_replace('%2F', '/', rawurlencode($filePath)));
                } else
                    $l['src'] = $filePath;
            }
            elseif ($lp_img_src == 'generated') {
                /**
                 * + WATERMARK-ing
                 */
                if (!isset($lpW) || !isset($lpH)) {
                    $inf = @getimagesize($gdir . $this->_e2gDecode($path . $fetch['filename']));
                    if (!isset($lpW))
                        $lpW = $inf[0];
                    if (!isset($lpH))
                        $lpH = $inf[1];
                    $inf = array();
                    unset($inf);
                }
                $filePath = $this->_imgShaper($gdir, $path . $fetch['filename'], $lpW, $lpH, $lpThq, $lpResizeType,
                                $lpRed, $lpGreen, $lpBlue, 1);
//                if (strpos($_SERVER['DOCUMENT_ROOT'], '/') === (int) 0) {
                if (strtoupper(substr(PHP_OS, 0, 3) != 'WIN')) {
                    $l['src'] = rawurldecode(str_replace('%2F', '/', rawurlencode($filePath)));
                } else
                    $l['src'] = $filePath;
            }

            $l['title'] = ($fetch['name'] != '' ? $fetch['name'] : $fetch['filename']);
            $l['name'] = $fetch['name'];
            $l['description'] = htmlspecialchars_decode($fetch['description'], ENT_QUOTES);
            $path = $this->_getPath($fetch['dir_id']);
            if (count($path) > 1) {
                unset($path[1]);
                $path = implode('/', array_values($path)) . '/';
            } else {
                $path = '';
            }

            /**
             * Comments on the landing page
             */
            // HIDE COMMENTS from Ignored IP Addresses
            $ip = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];

            $comStatusQuery = 'SELECT COUNT(ign_ip_address) '
                    . 'FROM ' . $modx->db->config['table_prefix'] . 'easy2_ignoredip '
                    . 'WHERE ign_ip_address=\'' . $ip . '\'';
            $comStatusRes = mysql_query($comStatusQuery);
            if (!$comStatusRes) {
                echo __LINE__ . ' : ' . mysql_error() . ' ' . $comStatusQuery . '<br />';
                return FALSE;
//                or die(__LINE__ . ' : ' . mysql_error());
            }
            while ($comRow = mysql_fetch_array($comStatusRes)) {
                $countIgnoredIp = $comRow['COUNT(ign_ip_address)'];
            }

            if ($ecm == 1 && ($countIgnoredIp == 0)) {

                $modx->regClientCSS($page_tpl_css);

                $l['com'] = 'e2gcom' . ($l['comments'] == 0 ? 0 : 1);
                $l['comments'] = $this->_comments($fileId);
            } else {
                $l['comments'] = '&nbsp;';
                $l['com'] = 'not_display';
            }
        }

        // Gallery's wrapper ID
        $l['wrapper'] = $e2gWrapper;

        /**
         * invoke plugin for THE IMAGE
         */
        // feeding additional parameters for the plugin
        $l['fid'] = $fileId;
        $l['landingpage'] = $landingPage;
        // creating the plugin array's content
        $e2gEvtParams = array();
        foreach ($l as $k => $v) {
            $e2gEvtParams[$k] = $v;
        }

        $l['landingpagepluginprerender'] = $this->_plugin('OnE2GWebLandingpagePrerender', $e2gEvtParams);
        $l['landingpagepluginrender'] = $this->_plugin('OnE2GWebLandingpageRender', $e2gEvtParams);

        return $this->_filler($this->_tplPage(), $l);
    }

    /**
     * Comment function for a page (landingpage or galley)
     * @param  string $fileId File ID of the comment's owner
     * @return mixed  return the comment's page content
     */
    private function _comments($fileId) {
        $modx = $this->modx;
        $landingPage = $this->e2gsnip_cfg['landingpage'];
        $recaptcha = $this->e2gsnip_cfg['recaptcha'];
        $eclPage = $this->e2gsnip_cfg['ecl_page'];
        $cpn = (empty($_GET['cpn']) || !is_numeric($_GET['cpn'])) ? 0 : (int) $_GET['cpn'];

        require_once(E2G_SNIPPET_PATH . 'includes/recaptchalib.php');
        // Get a key from https://www.google.com/recaptcha/admin/create
        $publicKey = $this->e2gsnip_cfg['recaptcha_key_public'];
        $privatekey = $this->e2gsnip_cfg['recaptcha_key_private'];

        if (file_exists(E2G_SNIPPET_PATH . 'includes/langs/' . $modx->config['manager_language'] . '.comments.php')) {
            include_once E2G_SNIPPET_PATH . 'includes/langs/' . $modx->config['manager_language'] . '.comments.php';
            $lngCmt = $e2g_lang[$modx->config['manager_language']];
        } else {
            include_once E2G_SNIPPET_PATH . 'includes/langs/english.comments.php';
            $lngCmt = $e2g_lang['english'];
        }

        $_P['charset'] = $modx->config['modx_charset'];

        // output from language file
        $_P['title'] = $lngCmt['title'];
        $_P['comment_add'] = $lngCmt['comment_add'];
        $_P['name'] = $lngCmt['name'];
        $_P['email'] = $lngCmt['email'];
        $_P['usercomment'] = $lngCmt['usercomment'];
        $_P['send_btn'] = $lngCmt['send_btn'];
        $_P['comment_body'] = '';
        $_P['comment_pages'] = '';
        $_P['code'] = $lngCmt['code'];
        $_P['waitforapproval'] = $lngCmt['waitforapproval'];

        // INSERT THE COMMENT INTO DATABASE
        if (!empty($_POST['name']) && !empty($_POST['comment'])) {
            $n = htmlspecialchars(trim($_POST['name']), ENT_QUOTES);
            $c = htmlspecialchars(trim($_POST['comment']), ENT_QUOTES);
            $e = htmlspecialchars(trim($_POST['email']), ENT_QUOTES);
            $ip = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];

            if ($this->_checkEmailAddress($e) == FALSE) {
                $_P['comment_body'] .= '<h2>' . $lngCmt['email_err'] . '</h2>';
            } elseif ($recaptcha == 1 && (trim($_POST['recaptcha_response_field']) == '')) {
                $_P['comment_body'] .= '<h2>' . $lngCmt['recaptcha_err'] . '</h2>';
            }
            if ($recaptcha == 1 && $_POST['recaptcha_response_field']) {
                require_once E2G_SNIPPET_PATH . 'includes/recaptchalib.php';
                # the response from reCAPTCHA
                $resp = NULL;
                # the error code from reCAPTCHA, if any
                $error = NULL;

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
                        $comInsert = 'INSERT INTO ' . $modx->db->config['table_prefix'] . 'easy2_comments (file_id,author,email,ip_address,comment,date_added) '
                                . "VALUES($fileId,'$n','$e','$ip','$c', NOW())";
                        if (mysql_query($comInsert)) {
                            mysql_query('UPDATE ' . $modx->db->config['table_prefix'] . 'easy2_files SET comments=comments+1 WHERE id=' . $fileId);
                            $_P['comment_body'] .= '<h3>' . $lngCmt['comment_added'] . '</h3>';
                        } else {
                            $_P['comment_body'] .= '<h2>' . $lngCmt['comment_add_err'] . '</h2>';
                        }
                    }
                }
            }
            // NOT USING reCaptcha
            else {
                $comInsert = 'INSERT INTO ' . $modx->db->config['table_prefix'] . 'easy2_comments (file_id,author,email,ip_address,comment,date_added) '
                        . "VALUES($fileId,'$n','$e','$ip','$c', NOW())";
                if (mysql_query($comInsert)) {
                    mysql_query('UPDATE ' . $modx->db->config['table_prefix'] . 'easy2_files SET comments=comments+1 WHERE id=' . $fileId);
                    $_P['comment_body'] .= '<h3>' . $lngCmt['comment_added'] . '</h3>';
                } else {
                    $_P['comment_body'] .= '<h2>' . $lngCmt['comment_add_err'] . '</h2>';
                }
            }
        }

        if ($_POST && empty($_POST['name']) && empty($_POST['comment'])) {
            $_P['comment_body'] .= '<h2>' . $lngCmt['empty_name_comment'] . '</h2>';
        }

        // DISPLAY THE AVAILABLE COMMENTS
        $commentsQuery = 'SELECT * FROM ' . $modx->db->config['table_prefix'] . 'easy2_comments '
                . 'WHERE file_id = ' . $fileId . ' '
                . 'AND STATUS=1 '
                . 'ORDER BY id DESC '
                . 'LIMIT ' . ($cpn * $eclPage) . ', ' . $eclPage;
        $res = mysql_query($commentsQuery);
        $i = 0;
        while ($l = mysql_fetch_array($res, MYSQL_ASSOC)) {

            $l['i'] = $i % 2;

            $l['name_permalink'] = '<a href="#" name="lpcmtnm' . $l['id'] . '"></a> ';
            $l['name_w_permalink'] = '<a href="'
                    // making flexible FURL or not
                    . $modx->makeUrl($modx->documentIdentifier
                            , $modx->aliases
                            , 'sid=' . $e2gStaticInstances)
                    . '&amp;lp=' . $landingPage . '&amp;fid=' . $fileId . '&amp;cpn=' . $cpn . '#lpcmtnm' . $l['id']
                    . '">' . $l['author'] . '</a> ';
            if (!empty($l['email']))
                $l['name_w_mail'] = '<a href="mailto:' . $l['email'] . '">' . $l['author'] . '</a>';
            else
                $l['name_w_mail'] = $l['author'];

            $_P['comment_body'] .= $this->_filler($this->_tplRowCommentLandingPage(), $l);
            $i++;
        }
        $_P['pages_permalink'] = '<a href="#" name="lpcmtpg' . $cpn . '"></a>';

        // COUNT PAGES
        $commentCountQuery = 'SELECT COUNT(*) FROM ' . $modx->db->config['table_prefix'] . 'easy2_comments WHERE file_id = ' . $fileId;
        $res = mysql_query($commentCountQuery);
        list($cnt) = mysql_fetch_row($res);
        if ($cnt > $eclPage) {
            $_P['comment_pages'] = '<p class="pnums">' . $lngCmt['pages'] . ':';
            $i = 0;
            while ($i * $eclPage < $cnt) {
                if ($i == $cpn)
                    $_P['comment_pages'] .= '<b>' . ($i + 1) . '</b> ';
                else
                    $_P['comment_pages'] .=
                            '<a href="'
                            // making flexible FURL or not
                            . $modx->makeUrl($modx->documentIdentifier
                                    , $modx->aliases
                                    , 'sid=' . $e2gStaticInstances)
                            . '&amp;lp=' . $landingPage . '&amp;fid=' . $fileId . '&amp;cpn=' . $i . '#lpcmtpg' . $i
                            . '">' . ($i + 1) . '</a> ';
                $i++;
            }
            $_P['comment_pages'] .= '</p>';
        }

        // COMMENT TEMPLATE
        if ($recaptcha == 1) {
            $_P['recaptcha'] = '
                <tr>
                    <td colspan="4">' . $this->_recaptchaForm($publicKey, $error) . '</td>
                </tr>';
        } else {
            $_P['recaptcha'] = '';
        }
        return $this->_filler($this->_tplCommentLandingPage(), $_P);
    }

    /**
     * Template for the directory's thumbnail
     * @return mixed template from chunk or file, or returns FALSE notification
     */
    private function _tplDir() {
        $modx = $this->modx;
        $tplDir = $this->e2gsnip_cfg['dir_tpl'];
        if (file_exists($tplDir)) {
            $tpl_dir = file_get_contents($tplDir);
            return $tpl_dir;
        } elseif (!empty($modx->chunkCache[$tplDir])) {
            $tpl_dir = $modx->chunkCache[$tplDir];
            return $tpl_dir;
        } else {
            echo 'Directory template ' . $tplDir . ' is not found!<br />';
        }
    }

    /**
     * Template for the image's thumbnail
     * @return mixed template from chunk or file, or returns FALSE notification
     */
    private function _tplThumb() {
        $modx = $this->modx;
        $tplThumb = $this->e2gsnip_cfg['thumb_tpl'];
        if (file_exists($tplThumb)) {
            $tpl_thumb = file_get_contents($tplThumb);
            return $tpl_thumb;
        } elseif (!empty($modx->chunkCache[$tplThumb])) {
            $tpl_thumb = $modx->chunkCache[$tplThumb];
            return $tpl_thumb;
        } else {
            echo 'Thumbnail template ' . $tplThumb . ' is not found!<br />';
        }
    }

    /**
     * Template for the gallery wrapper
     * @return mixed template from chunk or file, or returns FALSE notification
     */
    private function _tplGal() {
        $modx = $this->modx;
        $tpl = $this->e2gsnip_cfg['tpl'];
        if (file_exists($tpl)) {
            $gal_tpl = file_get_contents($tpl);
            return $gal_tpl;
        } elseif (!empty($modx->chunkCache[$tpl])) {
            $gal_tpl = $modx->chunkCache[$tpl];
            return $gal_tpl;
        } else {
            echo 'Gallery template ' . $tpl . ' is not found!<br />';
        }
    }

    /**
     * Template for the random image
     * @return mixed template from chunk or file, or returns FALSE notification
     */
    private function _tplRandom() {
        $modx = $this->modx;
        $tplRandom = $this->e2gsnip_cfg['rand_tpl'];
        if (file_exists($tplRandom)) {
            $tplRandom = file_get_contents($tplRandom);
            return $tplRandom;
        } elseif (!empty($modx->chunkCache[$tplRandom])) {
            $tplRandom = $modx->chunkCache[$tplRandom];
            return $tplRandom;
        } else {
            echo 'Random template ' . $tplRandom . ' is not found!<br />';
        }
    }

    /**
     * Page template for the landing page
     * @return mixed template from chunk or file, or returns FALSE notification
     */
    private function _tplPage() {
        $modx = $this->modx;
        $tplLandingPage = $this->e2gsnip_cfg['page_tpl'];
        if (file_exists($tplLandingPage)) {
            $tplLandingPage = file_get_contents($tplLandingPage);
            return $tplLandingPage;
        } elseif (!empty($modx->chunkCache[$tplLandingPage])) {
            $tplLandingPage = $modx->chunkCache[$tplLandingPage];
            return $tplLandingPage;
        } else {
            echo 'Landing page template ' . $tplLandingPage . ' is not found!<br />';
        }
    }

    /**
     * Comment row template for the thumbnails
     * @return mixed template from chunk or file, or returns FALSE notification
     * @todo is this function disposabled?
     */
    private function _tplRowComment() {
        $modx = $this->modx;
        $tplCommentRow = $this->e2gsnip_cfg['comments_row_tpl'];
        if (file_exists($tplCommentRow)) {
            $row_tpl = file_get_contents($tplCommentRow);
            return $row_tpl;
        } elseif (!empty($modx->chunkCache[$tplCommentRow])) {
            $row_tpl = $modx->chunkCache[$tplCommentRow];
            return $row_tpl;
        } else {
            echo 'Comments row template ' . $tplCommentRow . ' is not found!<br />';
        }
    }

    /**
     * Comment template for the landing page
     * @return mixed template from chunk or file, or returns FALSE notification
     */
    private function _tplCommentLandingPage() {
        $modx = $this->modx;
        $tplCommentLandingPage = $this->e2gsnip_cfg['page_comments_tpl'];
        if (file_exists($tplCommentLandingPage)) {
            $tpl = file_get_contents($tplCommentLandingPage);
            return $tpl;
        } elseif (!empty($modx->chunkCache[$tplCommentLandingPage])) {
            $tpl = $modx->chunkCache[$tplCommentLandingPage];
            return $tpl;
        } else {
            echo 'Comments template ' . $tplCommentLandingPage . ' is not found!<br />';
        }
    }

    /**
     * Comment row template for the landing page
     * @return mixed template from chunk or file, or returns FALSE notification
     */
    private function _tplRowCommentLandingPage() {
        $modx = $this->modx;
        $tplCommentRowLandingPage = $this->e2gsnip_cfg['page_comments_row_tpl'];
        if (file_exists($tplCommentRowLandingPage)) {
            $row_tpl = file_get_contents($tplCommentRowLandingPage);
            return $row_tpl;
        } elseif (!empty($modx->chunkCache[$tplCommentRowLandingPage])) {
            $row_tpl = $modx->chunkCache[$tplCommentRowLandingPage];
            return $row_tpl;
        } else {
            echo 'Page\'s comments row template ' . $tplCommentRowLandingPage . ' is not found!<br />';
        }
    }

    /**
     * email validation
     * @param  string $email
     * @return bool   TRUE/FALSE
     */
    function _checkEmailAddress($email) {
        return parent::checkEmailAddress($email);
    }

    /**
     * Invoking the script with plugin, at any specified places.
     * @param string    $e2gEvtName     event trigger.
     * @param mixed     $e2gEvtParams   parameters array: depends on the event trigger.
     * @return mixed    if TRUE, will return the indexfile. Otherwise this will return FALSE.
     */
    private function _plugin($e2gEvtName, $e2gEvtParams=array()) {
        $plugin = $this->e2gsnip_cfg['plugin'];

        if ($plugin == 'none')
            return;

        /**
         * check the direct plugin settings from the snippet call
         */
        if (isset($plugin)) {
            // example: &plugin=`thumb:starrating#Prerender, watermark@custom/index/file.php | gallery:... | landingpage:...`
            // clean up
            $badchars = array('`', ' ');
            $plugin = str_replace($badchars, '', trim($plugin));

            // generate the splitting targets with their names, area, and parameters
            $xpldPlugins = array();
            $xpldPlugins = @explode('|', trim($plugin));
            // read them one by one
            foreach ($xpldPlugins as $p_category) {
                // get the plugins' targets and names
                $xpldsettings = array();
                $xpldsettings = @explode(':', trim($p_category));

                // get the plugins' targets: thumb | gallery | landingpage
                $p_target = $xpldsettings [0];
                // get the plugins' names: starrating#Prerender, watermark
                $p_selections = $xpldsettings [1];

                // to disable the default action of the registered plugin in database
                // eg: thumb:none
                if ($p_selections == 'none')
                    return;

                $xpldTypes = array();
                $xpldTypes = @explode(',', trim($p_selections));

                foreach ($xpldTypes as $p_type) {
                    $xpldIndexes = array();
                    $xpldIndexes = @explode('@', trim($p_type));
                    $p_indexfile = $xpldIndexes[1];

                    $xpldNames = array();
                    $xpldNames = @explode('#', $xpldIndexes[0]);
                    $p_name = $xpldNames[0];
                    $p_area = strtolower($xpldNames[1]);
                    if (empty($p_area))
                        $p_area = 'prerender';

                    // to disable the default action of the registered plugin in database
                    // eg: thumb:starrating#none
                    if ($p_area == 'none')
                        return;

                    $check_e2gEvtName = '';
                    if ($p_target == 'thumb' && $p_area == 'prerender')
                        $check_e2gEvtName = 'OnE2GWebThumbPrerender';
                    elseif ($p_target == 'thumb' && $p_area == 'render')
                        $check_e2gEvtName = 'OnE2GWebThumbRender';
                    elseif ($p_target == 'dir' && $p_area == 'prerender')
                        $check_e2gEvtName = 'OnE2GWebDirPrerender';
                    elseif ($p_target == 'dir' && $p_area == 'render')
                        $check_e2gEvtName = 'OnE2GWebDirRender';
                    elseif ($p_target == 'gallery' && $p_area == 'prerender')
                        $check_e2gEvtName = 'OnE2GWebGalleryPrerender';
                    elseif ($p_target == 'gallery' && $p_area == 'render')
                        $check_e2gEvtName = 'OnE2GWebGalleryRender';
                    elseif ($p_target == 'landingpage' && $p_area == 'prerender')
                        $check_e2gEvtName = 'OnE2GWebLandingpagePrerender';
                    elseif ($p_target == 'landingpage' && $p_area == 'render')
                        $check_e2gEvtName = 'OnE2GWebLandingpageRender';
                    else
                        $check_e2gEvtName = '';

                    if ($check_e2gEvtName != $e2gEvtName)
                        return FALSE;

                    unset($check_e2gEvtName);

                    // LOAD DA FILE!
                    if (empty($p_indexfile)) {
                        // surpress the disabled plugin by adding the 4th parameter as 'FALSE'.
                        $out = parent::plugin($e2gEvtName, $e2gEvtParams, $p_name, FALSE);
                        if ($out !== FALSE)
                            return $out;
                    } else {
                        if (!file_exists($p_indexfile)) {
                            return __LINE__ . ' : File <b>' . $p_indexfile . '</b> does not exist.';
                        } else {
                            ob_start();
                            include $p_indexfile;
                            $out = ob_get_contents();
                            ob_end_clean();
                            return $out;
                        }
                    } // if (empty($p_indexfile))
                } // foreach ($xpldTypes as $p_type)
            } // foreach ($xpldPlugins as $p_category)
        } // if (isset($plugin))
        // if $plugin parameter is not set, then snippet will retrieve all enabled plugins from the database
        else {
            return parent::plugin($e2gEvtName, $e2gEvtParams);
        }

        // this function should not go to this part.
        // if something weird happens, this should mark the line!
        echo __LINE__ . ' : The plugin does not exist.';
        return FALSE;
    }

    /**
     * Unicode character encoding work around.<br />
     * For human reading.<br />
     * The value is set from the module's config page.
     * @link http://a4esl.org/c/charset.html
     * @param string $text the string to be encoded
     * @return string returns the encoding
     */
    private function _e2gEncode($text, $callback=FALSE) {
        return parent::e2gEncode($text, $callback);
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
    private function _e2gDecode($text, $callback=FALSE) {
        return parent::e2gDecode($text, $callback);
    }

    /**
     * To check the valid decendant of the given &gid parameter
     * @param int    $id            single ID to be checked
     * @param string $staticId     comma separated IDs of valid decendants
     * @return bool  TRUE/FALSE
     */
    private function _checkGidDecendant($id, $staticId) {
        $modx = $this->modx;

        // for global star (*) selection
        if ($staticId == '*')
            return TRUE;

        $s = 'SELECT A.cat_id FROM ' . $modx->db->config['table_prefix'] . 'easy2_dirs A, '
                . $modx->db->config['table_prefix'] . 'easy2_dirs B '
                . 'WHERE B.cat_id IN (' . $staticId . ') '
                . 'AND A.cat_left BETWEEN B.cat_left AND B.cat_right '
        ;
        $q = mysql_query($s);
        if (!$q) {
            echo __LINE__ . ' : ' . mysql_error() . '<br />' . $s . '<br />';
            return FALSE;
//            or die(__LINE__ . ' : ' . mysql_error() . '<br />' . $s);
        }
        while ($l = mysql_fetch_array($q, MYSQL_ASSOC)) {
            $check[$l['cat_id']] = $l['cat_id'];
        }
        $xpldGids = explode(',', $id);
        foreach ($xpldGids as $_id) {
            if (!$check[$_id] && ($staticId != 1)) {
                return FALSE;
//                return $modx->sendUnauthorizedPage();
            } elseif (!$check[$_id] && ($staticId == 1)) {
                return FALSE;
//                return $modx->sendErrorPage();
            } else
                return TRUE;
        }
    }

    /**
     * CHECK THE REAL DESCENDANT OF fid ROOT
     * @param int       $id         the decendant file's ID
     * @param int       $staticId  the original file's ID
     * @return bool     TRUE | FALSE
     * @todo is this function disposabled?
     */
    private function _checkFidDecendant($id, $staticId) {
        $modx = $this->modx;
        $s = 'SELECT id FROM ' . $modx->db->config['table_prefix'] . 'easy2_files '
                . 'WHERE id IN (' . $staticId . ') '
        ;
        $q = mysql_query($s);
        if (!$q) {
            echo __LINE__ . ' : ' . mysql_error() . '<br />' . $s . '<br />';
            return FALSE;
//            or die(__LINE__ . ' : ' . mysql_error() . '<br />' . $s);
        }
        while ($l = mysql_fetch_array($q, MYSQL_ASSOC)) {
            $check[$l['id']] = $l['id'];
        }
        $xpldFids = explode(',', $id);
        foreach ($xpldFids as $_id) {
            if (!$check[$_id]) {
                return $modx->sendErrorPage();
            } else
                return TRUE;
        }
    }

    /**
     * CHECK the valid parent IDs of the &tag parameter
     * @param string $dirOrFile dir|file
     * @param string $tag from &tag parameter
     * @param int    $id  id of the specified dir/file
     * @return bool TRUE | FALSE
     */
    private function _tagsIds($dirOrFile, $tag, $id=1) {
        $modx = $this->modx;
        $tag = strtolower($tag);

        if ($dirOrFile == 'dir') {
            $tagSelectDirIds = 'SELECT cat_id FROM ' . $modx->db->config['table_prefix'] . 'easy2_dirs ';

            $multipleTags = @explode(',', $tag);
            for ($i = 0; $i < count($multipleTags); $i++) {
                if ($i == 0)
                    $tagSelectDirIds .= 'WHERE LOWER(cat_tag) LIKE \'%' . $multipleTags[$i] . '%\' ';
                else
                    $tagSelectDirIds .= 'OR LOWER(cat_tag) LIKE \'%' . $multipleTags[$i] . '%\' ';
            }

            $excludeDirsWebAccess = $this->_excludeWebAccess('dir');

            if ($excludeDirsWebAccess !== FALSE) {
                $tagSelectDirIds .= 'AND cat_id NOT IN (' . $excludeDirsWebAccess . ') ';
            }

            $tagsQuery = mysql_query($tagSelectDirIds);
            if (!$tagsQuery) {
                echo __LINE__ . ' : ' . mysql_error() . '<br />' . $tagSelectDirIds . '<br />';
                return FALSE;
//                or die(__LINE__ . ' : ' . mysql_error() . '<br />' . $tagSelectDirIds);
            }
            while ($l = mysql_fetch_array($tagsQuery, MYSQL_ASSOC)) {
                $tagsDir[$l['cat_id']] = $l['cat_id'];
            }

            if (isset($tagsDir[$id])) {
                return TRUE;
            } else {
                return FALSE;
            }
        }
        if ($dirOrFile == 'file') {
            $tagSelectFileIds = 'SELECT id FROM ' . $modx->db->config['table_prefix'] . 'easy2_files ';

            $multipleTags = @explode(',', $tag);
            for ($i = 0; $i < count($multipleTags); $i++) {
                if ($i == 0)
                    $tagSelectFileIds .= 'WHERE LOWER(tag) LIKE \'%' . $multipleTags[$i] . '%\' ';
                else
                    $tagSelectFileIds .= 'OR LOWER(tag) LIKE \'%' . $multipleTags[$i] . '%\' ';
            }

            $excludeFilesWebAccess = $this->_excludeWebAccess('file');

            if ($excludeFilesWebAccess !== FALSE) {
                $fileSelect .= ' AND id NOT IN (' . $excludeFilesWebAccess . ') ';
            }

            $tagsQuery = mysql_query($tagSelectFileIds);
            if (!$tagsQuery) {
                echo __LINE__ . ' : ' . mysql_error() . '<br />' . $tagSelectFileIds . '<br />';
                return FALSE;
//                or die(__LINE__ . ' : ' . mysql_error() . '<br />' . $tagSelectFileIds);
            }
            while ($l = mysql_fetch_array($tagsQuery, MYSQL_ASSOC)) {
                $tagsFile[$l['id']] = $l['id'];
            }

            if (isset($tagsFile[$id])) {
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
     * @param string $error The error given by reCAPTCHA (optional, default is NULL)
     * @param boolean $use_ssl Should the request be made over ssl? (optional, default is FALSE)
     * @return string - The HTML to be embedded in the user's form.
     */
    private function _recaptchaForm($pubkey, $error = NULL, $use_ssl = FALSE) {
        require_once(E2G_SNIPPET_PATH . 'includes/recaptchalib.php');
        $theme = $this->e2gsnip_cfg['recaptcha_theme'];
        $theme_custom = $this->e2gsnip_cfg['recaptcha_theme_custom'];

        if ($pubkey == NULL || $pubkey == '') {
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
            $inf = @getimagesize($fp);

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

                $wminfo = @getimagesize($wmfp);

                if ($wminfo[2] == 1)
                    $wmi = imagecreatefromgif($wmfp);
                elseif ($wminfo[2] == 2)
                    $wmi = imagecreatefromjpeg($wmfp);
                elseif ($wminfo[2] == 3)
                    $wmi = imagecreatefrompng($wmfp);
                else
                    return 'WM error';

                imageAlphaBlending($wmi, FALSE);
                imageSaveAlpha($wmi, TRUE);
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

    /**
     *
     * @param string $direction     prev/up/next
     * @param string $staticKey    static identifier's value
     * @param string $dynamicKey   dynamic identifier's value, from the $_GET value
     * @param string $orderBy       image order
     * @param string $order         ASC | DESC
     * @return mixed The text and link of the navigator direction
     */
    private function _navPrevUpNext($direction, $staticKey, $dynamicKey, $cat_orderBy, $cat_order) {
        $modx = $this->modx;
        $gid = $this->e2gsnip_cfg['gid'];
        $staticGid = $this->e2gsnip_cfg['static_gid'];
        $tag = $this->e2gsnip_cfg['tag'];
        $staticTag = $this->e2gsnip_cfg['static_tag'];
        $e2gStaticInstances = $this->e2gsnip_cfg['e2g_static_instances'];

        // if the gallery is the parent ID of the snippet call, disable the up navigation
        if ($staticGid == $gid) {
            return FALSE;
        } else {
            $prevUpNext = array();

            if ($direction == 'prev') {
                if (isset($staticTag)) {
                    $sibling = $this->_getSiblingInfo('tag', $dynamicKey, 'cat_name', $cat_orderBy, $cat_order, -1);
                } else {
                    $sibling = $this->_getSiblingInfo(NULL, $dynamicKey, 'cat_name', $cat_orderBy, $cat_order, -1);
                }
                if (!empty($sibling)) {
                    $prevUpNext['cat_id'] = $sibling['cat_id'];
                    $prevUpNext['link'] = $modx->makeUrl(
                                    $modx->documentIdentifier
                                    , $modx->aliases
                                    , 'sid=' . $e2gStaticInstances)
                            . '&amp;gid=' . $sibling['cat_id']
                    ;
                    if (isset($tag)) {
                        $prevUpNext['link'] .= '&amp;tag=' . $staticTag;
                    }
                    $prevUpNext['cat_name'] = $sibling['cat_name'];
                } else {
                    return FALSE;
                }
            } elseif ($direction == 'up') {
                if (isset($staticTag)) {
                    $parent = $this->_getParentInfo('tag', $dynamicKey, 'cat_name');
                } else {
                    $parent = $this->_getParentInfo(NULL, $dynamicKey, 'cat_name');
                }
                $prevUpNext['cat_id'] = $parent['cat_id'];
                $prevUpNext['link'] = $modx->makeUrl(
                                $modx->documentIdentifier
                                , $modx->aliases
                                , 'sid=' . $e2gStaticInstances)
                        . '&amp;gid=' . $parent['cat_id']
                ;
                if (isset($tag)) {
                    $prevUpNext['link'] .= '&amp;tag=' . $staticTag;
                }
                $prevUpNext['cat_name'] = $parent['cat_name'];
            } elseif ($direction == 'next') {
                if (isset($staticTag)) {
                    $sibling = $this->_getSiblingInfo('tag', $dynamicKey, 'cat_name', $cat_orderBy, $cat_order, 1);
                } else {
                    $sibling = $this->_getSiblingInfo(NULL, $dynamicKey, 'cat_name', $cat_orderBy, $cat_order, 1);
                }
                if (!empty($sibling)) {
                    $prevUpNext['cat_id'] = $sibling['cat_id'];
                    $prevUpNext['link'] = $modx->makeUrl(
                                    $modx->documentIdentifier
                                    , $modx->aliases
                                    , 'sid=' . $e2gStaticInstances)
                            . '&amp;gid=' . $sibling['cat_id']
                    ;
                    if (isset($tag)) {
                        $prevUpNext['link'] .= '&amp;tag=' . $staticTag;
                    }
                    $prevUpNext['cat_name'] = $sibling['cat_name'];
                } else {
                    return FALSE;
                }
            }
            return $prevUpNext;
        }
    }

    private function _getParentInfo($trigger, $dynamicId, $field) {
        $modx = $this->modx;

        $selectParent = 'SELECT parent_id, ' . $field . ' FROM ' . $modx->db->config['table_prefix'] . 'easy2_dirs ';

        if ($dynamicId != '*') {
            if ($trigger == 'tag') {
                $selectParent .= 'WHERE cat_tag LIKE \'%' . $dynamicId . '%\' ';
            } else {
                $selectParent .= 'WHERE cat_id IN(' . $dynamicId . ') ';
            }
        }

        $queryParent = mysql_query($selectParent);
        if (!$queryParent) {
            echo __LINE__ . ' : ' . mysql_error() . '<br />' . $selectParent . '<br />';
            return FALSE;
//            or die(__LINE__ . ': ' . mysql_error() . '<br />' . $selectParent);
        }
        while ($row = mysql_fetch_array($queryParent)) {
            $parent['cat_id'] = $row['parent_id'];
        }

        if (!empty($parent['cat_id'])) {
            $parent['cat_name'] = $this->_getDirInfo($parent['cat_id'], 'cat_name');
            return $parent;
        }
        else
            return FALSE;
    }

    private function _getSiblingInfo($trigger, $dynamicId, $field, $cat_orderBy, $cat_order, $siblingCounter) {
        $modx = $this->modx;
        $gid = $this->e2gsnip_cfg['gid'];

        $selectChildren = 'SELECT a.cat_id, a.cat_tag, a.' . $field . ' FROM ' . $modx->db->config['table_prefix'] . 'easy2_dirs a '
                . 'WHERE a.parent_id IN ('
                . 'SELECT b.parent_id FROM ' . $modx->db->config['table_prefix'] . 'easy2_dirs b '
                . 'WHERE ';

        if ($dynamicId != '*') {
            if ($trigger == 'tag') {
                $selectChildren .= 'b.cat_tag LIKE \'%' . $dynamicId . '%\' AND ';
            } else {
                $selectChildren .= 'b.cat_id IN (' . $dynamicId . ') AND ';
            }
        }

        $selectChildren .= 'b.cat_visible = 1 ) '
                . 'AND a.cat_visible = 1 ';

        if ($trigger == 'tag' && $dynamicId != '*') {
            $selectChildren .= 'AND a.cat_tag LIKE \'%' . $dynamicId . '%\' ';
        }

        $selectChildren .= 'AND (SELECT count(F.id) FROM ' . $modx->db->config['table_prefix'] . 'easy2_files F '
                . 'WHERE F.dir_id IN '
                . '(SELECT a.cat_id FROM ' . $modx->db->config['table_prefix'] . 'easy2_dirs c, '
                . $modx->db->config['table_prefix'] . 'easy2_dirs d '
                . 'WHERE (d.cat_id = a.cat_id '
                . 'AND c.cat_left >= d.cat_left '
                . 'AND c.cat_right <= d.cat_right '
                . 'AND c.cat_level >= d.cat_level '
                . 'AND c.cat_visible = 1)'
                . ')'
                . ')<>0 ';

        $selectChildren .= 'ORDER BY a.' . $cat_orderBy . ' ' . $cat_order;

        $queryChildren = mysql_query($selectChildren);
        if (!$queryChildren) {
            echo __LINE__ . ' : ' . mysql_error() . '<br />' . $selectChildren . '<br />';
            return FALSE;
//            or die(__LINE__ . ': ' . mysql_error() . '<br />' . $selectChildren);
        }
        $row = array();

        while ($row = mysql_fetch_array($queryChildren)) {
            $siblings['cat_id'][] = $row['cat_id'];
            $siblings['cat_tag'][] = $row['cat_tag'];
            $siblings[$field][] = $row[$field];
        }

        $thesibling = '';
        if (count($siblings) > 1) {
            for ($i = 0; $i < count($siblings); $i++) {
                $j = intval($i + $siblingCounter);
                if ($j < 0)
                    continue;
                else {
                    if ($trigger == 'tag') {
                        foreach ($siblings['cat_tag'] as $k => $v) {
                            if ($siblings['cat_id'][$i] == $gid) {
                                $thesibling['cat_id'] = $siblings['cat_id'][$j];
                                $thesibling['cat_tag'] = $siblings['cat_tag'][$j];
                                $thesibling['cat_name'] = $siblings['cat_name'][$j];
                            }
                        }
                    } else {
                        foreach ($siblings['cat_id'] as $k => $v) {
                            if ($siblings['cat_id'][$i] == $dynamicId) {
                                $thesibling['cat_id'] = $siblings['cat_id'][$j];
                                $thesibling['cat_name'] = $siblings['cat_name'][$j];
                            }
                        }
                    }
                }
            }

            if (!empty($thesibling['cat_id']) || !empty($thesibling['cat_tag'])) {
                return $thesibling;
            }
        }
        else
            return '';
    }

    /**
     * fetching the &where_* parameters, and attach this into the query
     * @param string $whereParams  the parameter
     * @param string $prefix        the table prefix on joins
     * @return mixed FALSE | the where clause array
     */
    private function _whereClause($whereParams = NULL, $prefix = NULL) {
        if (!$whereParams) {
            return FALSE;
        }

        $xpldCommas = explode(',', $whereParams);
        $countXpldCommas = count($xpldCommas);
        $whereClause = '';
        for ($i = 0; $i < $countXpldCommas; $i++) {
            $op = $this->_whereClauseOperator(trim($xpldCommas[$i]));
            if ($op !== FALSE)
                $xpldCommas[$i] = $op;
            /**
             * DO NOT use 'else' here because this loop checks all the array contents,
             * not only the operator arrays.
             */
            $whereClause .= $xpldCommas[$i] . ' ';
        }

        if (isset($prefix)) {
            $xpldAnds = @explode(' AND ', $whereClause);
            $countXpldAnds = count($xpldAnds);
            $whereClauseTemp = '';
            for ($i = 0; $i < $countXpldAnds; $i++) {
                $whereClauseTemp .= $prefix . '.' . trim($xpldAnds[$i]) . ' ';
                if ($i < ($countXpldAnds - 1))
                    $whereClauseTemp .= 'AND ';
            }

            $whereClause = $whereClauseTemp;

            $xpld_ors = @explode(' OR ', $whereClause);
            $count_xpld_ors = count($xpld_ors);
            $whereClauseTemp = '';
            for ($i = 0; $i < $count_xpld_ors; $i++) {
                // the first loop has been prefixed from above loop
                $whereClauseTemp .= ( $i == 0 ? '' : $prefix . '.') . trim($xpld_ors[$i]) . ' ';
                if ($i < ($countXpldAnds - 1))
                    $whereClauseTemp .= 'OR ';
            }

            $whereClause = $whereClauseTemp;
        }

        return $whereClause;
    }

    /**
     * Checking the &where_* operator
     * @param string $operator the operator
     * @return string clean operator
     */
    private function _whereClauseOperator($operator) {
        $operators = array(
            "NULL safe equal" => '<=>'
            , "equal" => '='
            , "greater equal" => '>='
            , "greater" => '>'
            , "left shift" => '<<'
            , "less equal" => '<='
            , "left shift" => '<<'
            , "less" => '<'
            , "not equal" => '!='
            , "right shift" => '>>'
        );
        if (!array_key_exists($operator, $operators))
            return FALSE;
        else
            return $operators[$operator];
    }

    /**
     * Filter the web access to the restricted galleries/pictures.
     * @param string $type  dir/file selection
     * @return string the excluded ids from the SQL parameter
     */
    private function _excludeWebAccess($type) {
        $modx = $this->modx;

        /**
         * Get all the restricted list ids
         */
        $allWebAccess = array();
        $allWebAccessQuery = 'SELECT DISTINCT id FROM ' . $modx->db->config['table_prefix'] . 'easy2_webgroup_access WHERE '
                . ' type=\'' . $type . '\' ';
        $allWebAccess = $modx->db->makeArray($modx->db->query($allWebAccessQuery));

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
            $webUserGroupIdQuery = 'SELECT id FROM ' . $modx->db->config['table_prefix'] . 'webgroup_names '
                    . 'WHERE name=\'' . $groupName . '\'';
            $webUserGroupId = '';
            $webUserGroupId = $modx->db->getValue($modx->db->query($webUserGroupIdQuery));
            if (empty($webUserGroupId))
                continue;

            $userWebAccessQuery = 'SELECT DISTINCT id FROM ' . $modx->db->config['table_prefix'] . 'easy2_webgroup_access WHERE '
                    . 'webgroup_id=\'' . $webUserGroupId . '\' '
                    . 'AND type=\'' . $type . '\' ';

            $userWebAccess = array();
            $userWebAccess = $modx->db->makeArray($modx->db->query($userWebAccessQuery));
        }

        foreach ($userWebAccess as $k => $v) {
            $userWebAccess[$k] = $v['id'];
        }

        /**
         * Get the difference
         */
        $excludeWebAccess = array_diff($allWebAccess, $userWebAccess);
        if (empty($excludeWebAccess)) {
            return FALSE;
        }
        $excludeWebAccessString = @implode(',', $excludeWebAccess);
        return $excludeWebAccessString;
    }

}