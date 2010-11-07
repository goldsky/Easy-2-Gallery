<?php

/**
 * EASY 2 GALLERY
 * Gallery Snippet Class for Easy 2 Gallery Module for MODx Evolution
 * @author Cx2 <inteldesign@mail.ru>
 * @author Temus <temus3@gmail.com>
 * @author goldsky <goldsky@modx-id.com>
 * @version 1.4.0
 */
class E2gSnippet extends E2gPub {

    /**
     * Inherit MODx functions
     * @var mixed modx's API
     */
    public $modx;
    /**
     * The snippet's configurations in an array
     * @var mixed all the snippet's parameters
     */
    public $e2gSnipCfg = array();
    /**
     * The internal variables of this class
     * @var mixed all the processing variables
     */
    private $galPh = array();

    public function __construct($modx, $e2gSnipCfg) {
        parent::__construct($modx, $e2gSnipCfg);
        $this->modx = & $modx;
        $this->e2gSnipCfg = $e2gSnipCfg;
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
//        $gid = $this->e2gSnipCfg['gid']; // default
//        $staticGid = $this->e2gSnipCfg['static_gid'];
        $fid = $this->e2gSnipCfg['fid'];
//        $static_fid = $this->e2gSnipCfg['static_fid'];
        $rgid = $this->e2gSnipCfg['rgid'];
        $slideshow = $this->e2gSnipCfg['slideshow'];
        $landingPage = $this->e2gSnipCfg['landingpage'];

        // to avoid gallery's thumbnails display on the landingpage's page
        if ($modx->documentIdentifier != $landingPage) {
            if (isset($fid) && !isset($slideshow)) {
                return $this->_imgFile();
            }
            if (isset($rgid) && !isset($slideshow)) {
                return $this->_imgRandom();
            }
            if (!isset($fid) && !isset($rgid) && !isset($slideshow)) {
                return $this->_gallery(); // default
            }
        }
        if (isset($slideshow)) {
            return $this->_slideshow($slideshow);
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
        $gdir = $this->e2gSnipCfg['gdir'];
        $gid = $this->e2gSnipCfg['gid'];
        $staticGid = $this->e2gSnipCfg['static_gid'];
        $e2gInstances = $this->e2gSnipCfg['e2g_instances'];
        $e2gStaticInstances = $this->e2gSnipCfg['e2g_static_instances'];
        $e2gWrapper = $this->e2gSnipCfg['e2g_wrapper'];

        $tag = $this->e2gSnipCfg['tag'];
        $staticTag = $this->e2gSnipCfg['static_tag'];

        if ($this->e2gSnipCfg['orderby'] == 'random') {
            $orderBy = 'rand()';
            $order = '';
        } else {
            $orderBy = $this->e2gSnipCfg['orderby'];
            $order = $this->e2gSnipCfg['order'];
        }
        if ($this->e2gSnipCfg['cat_orderby'] == 'random') {
            $catOrderBy = 'rand()';
            $catOrder = '';
        } else {
            $catOrderBy = $this->e2gSnipCfg['cat_orderby'];
            $catOrder = $this->e2gSnipCfg['cat_order'];
        }

        $whereDir = $this->e2gSnipCfg['where_dir'];
        $whereFile = $this->e2gSnipCfg['where_file'];
        $limit = $this->e2gSnipCfg['limit'];
        $gpn = $this->e2gSnipCfg['gpn'];

        $charSet = $this->e2gSnipCfg['charset'];
        $mbstring = $this->e2gSnipCfg['mbstring'];
        $catNameLen = $this->e2gSnipCfg['cat_name_len'];

//        $notables = $this->e2gSnipCfg['notables']; // deprecated
        $grid = $this->e2gSnipCfg['grid'];
        $gridClass = $this->e2gSnipCfg['grid_class'];
        $crumbsClassCurrent = $this->e2gSnipCfg['crumbs_classCurrent'];
        $backClass = $this->e2gSnipCfg['back_class'];
        $pageNumClass = $this->e2gSnipCfg['pagenum_class'];
        $colls = $this->e2gSnipCfg['colls'];
        $imgSrc = $this->e2gSnipCfg['img_src'];

        $showOnly = $this->e2gSnipCfg['showonly'];
        $customGetParams = $this->e2gSnipCfg['customgetparams'];
        $galDesc = $this->e2gSnipCfg['gal_desc'];
        $landingPage = $this->e2gSnipCfg['landingpage'];
        $plugin = $this->e2gSnipCfg['plugin'];

        // CRUMBS
        $crumbs = $this->e2gSnipCfg['crumbs'];
        $crumbsUse = $this->e2gSnipCfg['crumbs_use'];
        $crumbsSeparator = $this->e2gSnipCfg['crumbs_separator'];
        $crumbsShowHome = $this->e2gSnipCfg['crumbs_showHome'];
        $crumbsShowAsLinks = $this->e2gSnipCfg['crumbs_showAsLinks'];
        $crumbsShowCurrent = $this->e2gSnipCfg['crumbs_showCurrent'];
        $crumbsShowPrevious = $this->e2gSnipCfg['crumbs_showPrevious'];

        // Previous/Up/Next Navigation
        $navPrevUpNext = $this->e2gSnipCfg['nav_prevUpNext'];
        $navPrevSymbol = $this->e2gSnipCfg['nav_prevSymbol'];
        $navUpSymbol = $this->e2gSnipCfg['nav_upSymbol'];
        $navNextSymbol = $this->e2gSnipCfg['nav_nextSymbol'];

        // PAGINATION
        $pagination = $this->e2gSnipCfg['pagination'];

        $useRedirectLink = $this->e2gSnipCfg['use_redirect_link'];

        // EXECUTE THE JAVASCRIPT LIBRARY'S HEADERS
        $jsLibs = $this->_loadHeaders();
        if ($jsLibs === FALSE) {
            return FALSE;
        }

        //**********************************************************************/
        //*   PAGINATION FIXING for multiple snippet calls on the same page    */
        //**********************************************************************/
        // for the UNselected &gid snippet call when the other &gid snippet call is selected
        if (isset($staticGid)
                && isset($_GET['gid'])
                && !$this->_checkGidDecendant($_GET['gid'], $staticGid)
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
        $galPh = array(
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
            $galPh['desc_class'] = 'style="display:none;"';

        // START the grid
        $galPh['content'] = (($grid == 'css') ? '<div class="' . $gridClass . '">' : '<table class="' . $gridClass . '"><tr>');

        // Store the restricted galleries
        $excludeDirWebAccess = $this->_excludeWebAccess('dir');
        $excludeFileWebAccess = $this->_excludeWebAccess('file');

        //******************************************************************/
        //*                 COUNT DIRECTORY WITHOUT LIMIT!                 */
        //******************************************************************/
        // dir_count is used for pagination. random can not have this.
        // TODO: conflict with the &gid=`*` parameter. moved the limitation to the PAGINATION section instead.
        // TODO: delete all of these SELECT COUNT queries.
        //       just use the original dir query, and split the limit variable.
        //       this also will avoid non-consistence queries between counting and result.
//        if ($showOnly == 'images' || $orderBy == 'rand()' || $catOrderBy == 'rand()') {
        if ($showOnly == 'images') {
            $resultCountDirs = 0;
        } else {
            if (isset($staticTag)) {
                $selectDirCount = $this->_dirSqlStatements('COUNT(DISTINCT cat_id)', 'd');
            } else {
                $selectDirCount = $this->_dirSqlStatements('COUNT(DISTINCT d.cat_id)', 'd');
            }
            $querySelectDirCount = mysql_query($selectDirCount);
            if (!$querySelectDirCount) {
                echo __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectDirCount . '<br />';
                return FALSE;
            }

            $resultCountDirs = mysql_result($querySelectDirCount, 0, 0);
            mysql_free_result($querySelectDirCount);

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
                $pathArray = $this->_getPath($singleGid, NULL, 'array');
                // get "folder's name" from $pathArray
                $galPh['cat_name'] = is_array($pathArray) ? end($pathArray) : '';

                /**
                 * Only use crumbs if it is a single gid.
                 * Otherwise, how can we make crumbs for merging directories of multiple galleries on 1 page?
                 */
                if (isset($staticTag) && !$this->_checkTaggedDirIds($staticTag, $singleGid))
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

                    $crumbsPathArray = $this->_getPath($singleGid, $crumbsUse, 'array');

                    // To limit the CRUMBS paths.
                    if (($staticGid != '1') && !empty($crumbsPathArray) && !isset($tag)) {
                        $staticPath = $this->_getPath($staticGid, NULL, 'array');
                        if (!$crumbsShowPrevious) {
                            $crumbsPathArray = array_slice($crumbsPathArray, (count($staticPath) - 1), NULL, TRUE);
                        }
                    }

                    if ($crumbs == 1) {
                        // reset crumbs
                        $breadcrumbs = '';
                        // if path more the none
                        if (count($crumbsPathArray) > 0) {
                            end($crumbsPathArray);
                            prev($crumbsPathArray);
                            $galPh['parent_id'] = key($crumbsPathArray);
                            $galPh['parent_name'] = $crumbsPathArray[$galPh['parent_id']];

                            // create crumbs
                            $cnt = 0;
                            foreach ($crumbsPathArray as $k => $v) {
                                $cnt++;
                                if ($cnt == 1 && !$crumbsShowHome) {
                                    continue;
                                }
                                if ($cnt == count($crumbsPathArray) && !$crumbsShowCurrent) {
                                    continue;
                                }

                                if ($cnt != count($crumbsPathArray))
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

//                            // unset the value of Easy 2's ROOT gallery ID/name
//                            unset($crumbsPathArray[1]);
                            // joining many of directory paths
                            $crumbsPathArray = implode('/', array_values($crumbsPathArray)) . '/';
                        } else { // if not many, path is set as empty
                            $crumbsPathArray = '';
                        } // if (count($pathArray) > 1)
                        $galPh['crumbs'] = $breadcrumbs;
                    } // if ($crumbs == 1)
                    //******************************************************************/
                    //*                  Previous/Up/Next Navigation                   */
                    //******************************************************************/
                    if ($navPrevUpNext == 1
                            && $orderBy != 'rand()'
                            && $catOrderBy != 'rand()'
                    ) {
                        if (isset($staticTag)) {
                            $staticKey = $staticTag;
                            $dynamicKey = $tag;
                        } else {
                            $staticKey = $staticGid;
                            $dynamicKey = $gid;
                        }

                        $navPrev = $this->_navPrevUpNext('prev', $staticKey, $dynamicKey, $catOrderBy, $catOrder);
                        if ($navPrev !== FALSE) {
                            $galPh['prev_cat_link'] = $navPrev['link'];
                            $galPh['prev_cat_name'] = $navPrev['cat_name'];
                            $galPh['prev_cat_symbol'] = $navPrevSymbol;
                            if (isset($staticTag))
                                $galPh['prev_cat_permalink'] = '#' . $e2gStaticInstances . '_' . $staticTag;
                            else
                                $galPh['prev_cat_permalink'] = '#' . $e2gStaticInstances . '_' . $navPrev['cat_id'];

                            // complete link
                            $galPh['prev_link'] = '<a href="' . $galPh['prev_cat_link'] . $galPh['prev_cat_permalink'] . '">'
                                    . $galPh['prev_cat_symbol'] . ' ' . $galPh['prev_cat_name']
                                    . '</a>';
                        }

                        $navUp = $this->_navPrevUpNext('up', $staticKey, $dynamicKey, $catOrderBy, $catOrder);
                        if ($navUp !== FALSE) {
                            $galPh['up_cat_link'] = $navUp['link'];
                            $galPh['up_cat_name'] = $navUp['cat_name'];
                            $galPh['up_cat_symbol'] = $navUpSymbol;
                            if (isset($staticTag))
                                $galPh['up_cat_permalink'] = '#' . $e2gStaticInstances . '_' . $staticTag;
                            else
                                $galPh['up_cat_permalink'] = '#' . $e2gStaticInstances . '_' . $navUp['cat_id'];

                            // complete link
                            $galPh['up_link'] = '<a href="' . $galPh['up_cat_link'] . $galPh['up_cat_permalink'] . '">'
                                    . $galPh['up_cat_symbol'] . ' ' . $galPh['up_cat_name']
                                    . '</a>';
                        }

                        $navNext = $this->_navPrevUpNext('next', $staticKey, $dynamicKey, $catOrderBy, $catOrder);
                        if ($navNext !== FALSE) {
                            $galPh['next_cat_link'] = $navNext['link'];
                            $galPh['next_cat_name'] = $navNext['cat_name'];
                            $galPh['next_cat_symbol'] = $navNextSymbol;
                            if (isset($staticTag))
                                $galPh['next_cat_permalink'] = '#' . $e2gStaticInstances . '_' . $staticTag;
                            else
                                $galPh['next_cat_permalink'] = '#' . $e2gStaticInstances . '_' . $navNext['cat_id'];

                            // complete link
                            $galPh['next_link'] = '<a href="' . $galPh['next_cat_link'] . $galPh['next_cat_permalink'] . '">'
                                    . $galPh['next_cat_name'] . ' ' . $galPh['next_cat_symbol']
                                    . '</a>';
                        }
                    } // if ($navPrevUpNext == 1)
                } // if ($multipleGidsCount == 1)
            } // foreach ($multipleGids as $singleGid)
            //******************************************************************/
            //*                 FOLDERS/DIRECTORIES/GALLERIES                  */
            //******************************************************************/
            if ($showOnly != 'images') {
                if (isset($staticTag)) {
                    $selectDirs = $this->_dirSqlStatements('*', 'd');
                } else {
                    $selectDirs = $this->_dirSqlStatements('d.*', 'd');
                }

                $selectDirs .= ' ORDER BY ' . $catOrderBy . ' ' . $catOrder;
                $selectDirs .= ' LIMIT ' . ( $gpn * $limit ) . ', ' . $limit;

                $querySelectDirs = mysql_query($selectDirs);
                if (!$querySelectDirs) {
                    echo __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectDirs . '<br />';
                    return FALSE;
                }
                $dirNumRows += mysql_num_rows($querySelectDirs);

                // gallery's permalink
                if (isset($tag)
                        && ($this->_checkGidDecendant((isset($_GET['gid']) ? $_GET['gid'] : $gid), $staticGid) == TRUE)
                ) {
                    $permalinkName = $e2gStaticInstances . '_' . $staticTag;
                } elseif (!$this->_checkGidDecendant((isset($_GET['gid']) ? $_GET['gid'] : $gid), $staticGid)) {
                    $permalinkName = $e2gStaticInstances . '_' . $staticGid;
                } else {
                    $permalinkName = $e2gStaticInstances . '_' . $gid;
                }
                $permalinkName = $modx->stripAlias($permalinkName);
                $galPh['permalink'] = '<a href="#" name="' . $permalinkName . '"></a>';

                // gallery's description
                if ($galDesc == '1'
                        // exclude the multiple gids (comma separated)
                        && !strpos($staticGid, ',')
                ) {
                    $galleryId = '';
                    if (!$this->_checkGidDecendant((isset($_GET['gid']) ? $_GET['gid'] : $gid), $staticGid)) {
                        $galleryId = $staticGid;
                    } else {
                        $galleryId = $singleGid;
                    }

                    $galPh['cat_description'] = $this->_getDirInfo($galleryId, 'cat_description');
                    $galPh['cat_title'] = $this->_getDirInfo($galleryId, 'cat_alias');

                    $galPh['title'] = ($galPh['cat_title'] != '' ? $galPh['cat_title'] : $galPh['cat_name'] );
                    if ($galPh['title'] == '' && $galPh['cat_description'] == '') {
                        $galPh['desc_class'] = 'style="display:none;"';
                    }
                } else {
                    $galPh['desc_class'] = 'style="display:none;"';
                } // gallery's description
                //******************************************************************/
                //*       Fill up the current directory's thumbnails content       */
                //******************************************************************/

                $i = 0;

                while ($l = mysql_fetch_array($querySelectDirs, MYSQL_ASSOC)) {
                    if (isset($staticTag)) {
                        $l['permalink'] = $e2gStaticInstances . '_' . $staticTag;
                        $permalink = $e2gStaticInstances . '_' . $staticTag;
                    } else {
                        $l['permalink'] = $e2gStaticInstances . '_' . $l['cat_id'];
                        $permalink = $e2gStaticInstances . '_' . $l['cat_id'];
                    }

                    if (isset($tag)) {
                        $l['cat_tag'] = '&amp;tag=' . $staticTag;
                    } else {
                        $l['cat_tag'] = '';
                    }

                    $folderImgInfos = $this->_folderImg($l['cat_id'], $gdir);

                    // if there is an empty folder, or invalid content
                    if (!$folderImgInfos)
                        continue;

                    $l['count'] = $folderImgInfos['count'];

                    // path to subdir's thumbnail
                    $getPath = $this->_getPath($folderImgInfos['dir_id']);

                    // Populate the grid with folder's thumbnails
                    if ($dirNumRows > 0
                            && ( $i > 0 )
                            && ( $i % $colls == 0 )
                            && $grid == 'table'
                    ) {
                        $galPh['content'] .= '</tr><tr>';
                    }

                    $l['w'] = $this->e2gSnipCfg['w'];
                    $l['h'] = $this->e2gSnipCfg['h'];
                    $thq = $this->e2gSnipCfg['thq'];

                    $imgShaper = $this->_imgShaper($gdir, $getPath . $folderImgInfos['filename'], $l['w'], $l['h'], $thq);
                    if (!$imgShaper) {
                        continue;
                    } else {
                        $l['src'] = $imgShaper;
                    }

                    $l['title'] = ( $l['cat_alias'] != '' ? $l['cat_alias'] : $l['cat_name'] );
                    $l['title'] = $this->_cropName($mbstring, $charSet, $catNameLen, $l['title']);

                    if ($useRedirectLink === TRUE && !empty($l['cat_redirect_link'])) {
                        $l['link'] = $l['cat_redirect_link'];
                    } else {
                        // making flexible FURL or not
                        $l['link'] = $modx->makeUrl(
                                        $modx->documentIdentifier
                                        , $modx->aliases
                                        , 'sid=' . $e2gStaticInstances)
                                . '&amp;gid=' . $l['cat_id'] . (isset($staticTag) ? '&amp;tag=' . $staticTag : '') . '#' . $permalink
                        ;
                    }

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
                    $_filler = $this->_filler($this->_getTpl('dir_tpl'), $l);
                    $galPh['content'] .= ( ($grid == 'css') ? $_filler : '<td>' . $_filler . '</td>');
                    $i++;
                } // while ($l = mysql_fetch_array($querySelectDirs, MYSQL_ASSOC))
                mysql_free_result($querySelectDirs);
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
            $modulusDirCount = $resultCountDirs % $limit;
            $fileThumbOffset = $limit - $modulusDirCount;
            $filePageOffset = ceil($resultCountDirs / $limit);

            $selectFiles = $this->_fileSqlStatements('*');
            $selectFiles .= ' ORDER BY ' . $orderBy . ' ' . $order . ' ';
            /**
             * Calculate the available grid to be floated
             */
            if ($fileThumbOffset > 0 && $fileThumbOffset < $limit) {
                $selectFiles .= 'LIMIT '
                        . ( $dirNumRows > 0 ?
                                ( ' 0, ' . ( $fileThumbOffset ) ) :
                                ( ( ( $gpn - $filePageOffset) * $limit) + $fileThumbOffset ) . ', ' . $limit );
            } elseif ($fileThumbOffset != 0 || $fileThumbOffset == $limit) {
                $selectFiles .= 'LIMIT '
                        . ( $modulusDirCount > 0 ?
                                ( ' 0, ' . ( $fileThumbOffset ) ) :
                                ( ( ( $gpn - $filePageOffset) * $limit) ) . ', ' . $limit );
            } else { // $fileThumbOffset == 0 --> No sub directory
                $selectFiles .= 'LIMIT ' . ( $gpn * $limit) . ', ' . $limit;
            }

            $querySelectFiles = mysql_query($selectFiles);
            if (!$querySelectFiles) {
                echo __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectFiles . '<br />';
                return FALSE;
            }

            $fileNumRows = mysql_num_rows($querySelectFiles);

            /**
             * retrieve the content
             */
            $i = 0;

            // checking the $dirNumRows first
            if ($dirNumRows > 0
                    && $dirNumRows % $colls == 0
                    && $grid == 'table'
            ) {
                $galPh['content'] .= '</tr><tr>';
            }

            while ($l = mysql_fetch_array($querySelectFiles, MYSQL_ASSOC)) {
                /**
                 * whether configuration setting is set with or without table, the template will adjust it
                 * goldsky -- this is where the file's thumb 'float' to the dirs' in TABLE grid
                 */
                if (( $i > 0 )
                        && ( ( $i + $dirNumRows ) % $colls == 0 )
                        && $grid == 'table') {
                    $galPh['content'] .= '</tr><tr>';
                }

                $thumbPlacholders = $this->_loadThumbPlaceholders($l);
                if ($thumbPlacholders === FALSE)
                    continue;

                // whether configuration setting is set with or without table, the template will adjust it
                $_filler = $this->_filler($this->_getTpl('thumb_tpl'), $thumbPlacholders);
                $galPh['content'] .= ( ($grid == 'css') ? $_filler : '<td>' . $_filler . '</td>');
                $i++;
            } // while ($l = @mysql_fetch_array($querySelectFiles, MYSQL_ASSOC))
            mysql_free_result($querySelectFiles);
        } // if( $dirNumRows!=$limit && $showOnly!='folders' && !empty($gid) )

        $galPh['content'] .= ( ($grid == 'css') ? '</div>' : '</tr></table>');

        //******************************************************************/
        //*                          BACK BUTTON                           */
        //******************************************************************/
        if ($galPh['parent_id'] > 0
                && $this->_checkGidDecendant((isset($_GET['gid']) ? $_GET['gid'] : $gid), $staticGid) == TRUE
                && $staticTag == $tag
        ) {
            $galPh['back'] = '&laquo; <a href="'
                    // making flexible FURL or not
                    . $modx->makeUrl($modx->documentIdentifier
                            , $modx->aliases
                            , 'sid=' . $e2gStaticInstances)
                    . '&amp;gid=' . $galPh['parent_id']
                    . (isset($staticTag) ? '&amp;tag=' . $staticTag : '' )
                    . '#' . $e2gStaticInstances . '_'
                    . (isset($staticTag) ? $staticTag : $galPh['parent_id'] )
                    . '">' . $galPh['parent_name'] . '</a>';
        }

        //**********************************************************************/
        //*                       PAGINATION: PAGE LINKS                       */
        //*             joining between dirs and files pagination              */
        //**********************************************************************/
        if ($pagination == 1 && $orderBy != 'rand()' && $catOrderBy != 'rand()') {
            // count the files again, this time WITHOUT limit!
            if ($showOnly == 'folders') {
                $fileCount = 0;
            } elseif (!empty($gid)) {
                $selectCountFiles = $this->_fileSqlStatements('COUNT(id)');
                $querySelectCountFiles = mysql_query($selectCountFiles);
                if (!$querySelectCountFiles) {
                    echo __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectCountFiles . '<br />';
                    return FALSE;
                }
                $resultCountFiles = mysql_result($querySelectCountFiles, 0, 0);
                mysql_free_result($querySelectCountFiles);
            }

            $totalCount = $resultCountDirs + $resultCountFiles;

            // Terminate all the outputs, when the result is empty.
            if ($totalCount === 0)
                return FALSE;

            $galPh['page_num_class'] = $pageNumClass;
            if ($totalCount <= $limit) {
                $galPh['pages'] = '&nbsp;';
            }
            if ($totalCount > $limit) {

                $galPh['pages'] = '';
                $pages = array();
                $pages['totalCount'] = $totalCount;
                $pages['totalPageNum'] = ceil($totalCount / $limit);
                $indexPage = $modx->makeUrl($modx->documentIdentifier, $modx->aliases, 'sid=' . $e2gStaticInstances);
                $i = 0;
                while ($i * $limit < $totalCount) {
                    if ($i == $gpn) {
//                        $galPh['pages'] .= '<b>' . ($i + 1) . '</b> ';
                        $pages['pages'][$i + 1] = '<b>' . ($i + 1) . '</b> ';
                        $pages['currentPage'] = ($i + 1);
                    } else {
                        // using &tag parameter
                        if (isset($staticTag)) {
                            $permalinkName = $modx->stripAlias($e2gStaticInstances . '_' . $staticTag);
                            // making flexible FURL or not
                            $pagesLink = $indexPage . '&amp;tag=' . $staticTag
                                    . ( isset($_GET['gid']) ? '&amp;gid=' . $_GET['gid'] : '' )
                                    . '&amp;gpn=' . $i . $customGetParams . '#' . $permalinkName;
                        }
                        // original &gid parameter
                        else {
                            $permalinkName = $e2gStaticInstances . '_' . ( isset($staticGid)
                                    && ( $this->_checkGidDecendant((isset($_GET['gid']) ? $_GET['gid'] : $gid), $staticGid) == TRUE ) ?
                                            $gid : $staticGid );
                            $permalinkName = $modx->stripAlias($permalinkName);
                            // making flexible FURL or not
                            $pagesLink = $indexPage . ( isset($staticGid)
                                    && ( $this->_checkGidDecendant((isset($_GET['gid']) ? $_GET['gid'] : $gid) , $staticGid) == TRUE ) ?
                                            '&amp;gid=' . $gid :
                                            '&amp;gid=' . $staticGid )
                                    . '&amp;gpn=' . $i . $customGetParams . '#' . $permalinkName;
                        }

                        $pagesLink = str_replace(' ', '', $pagesLink);
//                        $galPh['pages'] .= '<a href="' . $pagesLink . '">' . ($i + 1) . '</a> ';

                        $pages['pages'][$i + 1] = '<a href="' . $pagesLink . '">' . ($i + 1) . '</a> ';
                    }

                    if (isset($staticTag)) {
                        $previousLink = $indexPage . '&amp;tag=' . $staticTag
                                . ( isset($_GET['gid']) ? '&amp;gid=' . $_GET['gid'] : '' )
                                . '&amp;gpn=' . ($i - 1) . $customGetParams . '#' . $permalinkName;
                        $nextLink = $indexPage . '&amp;tag=' . $staticTag
                                . ( isset($_GET['gid']) ? '&amp;gid=' . $_GET['gid'] : '' )
                                . '&amp;gpn=' . ($i + 1) . $customGetParams . '#' . $permalinkName;
                    } else {
                        $previousLink = $indexPage
                                . ( isset($staticGid)
                                && ( $this->_checkGidDecendant((isset($_GET['gid']) ? $_GET['gid'] : $gid) , $staticGid) == TRUE ) ?
                                        '&amp;gid=' . $gid :
                                        '&amp;gid=' . $staticGid )
                                . '&amp;gpn=' . ($i - 1) . $customGetParams . '#' . $permalinkName;
                        $nextLink = $indexPage
                                . ( isset($staticGid)
                                && ( $this->_checkGidDecendant((isset($_GET['gid']) ? $_GET['gid'] : $gid), $staticGid) == TRUE ) ?
                                        '&amp;gid=' . $gid :
                                        '&amp;gid=' . $staticGid )
                                . '&amp;gpn=' . ($i + 1) . $customGetParams . '#' . $permalinkName;
                    }

                    $pages['previousLink'][$i + 1] = $previousLink;
                    $pages['nextLink'][$i + 1] = $nextLink;

                    $i++;
                }
                $galPh['pages'] .= $this->_paginationFormat($pages);
            }
        }

        ########################## END OF PAGINATION ###########################
        // Gallery's wrapper ID
        $galPh['wrapper'] = $e2gWrapper;

        // MULTIPLE INSTANCES id
        $galPh['sid'] = $e2gStaticInstances;

        /**
         * invoke plugin for the MAIN gallery
         */
        $galPh['gallerypluginprerender'] = $this->_plugin('OnE2GWebGalleryPrerender', array(
                    'pages' => $galPh['pages']
                    , 'parent_id' => $galPh['parent_id']
                    , 'desc_class' => $galPh['desc_class']
                    , 'cat_name' => $galPh['cat_name']
                    , 'permalink' => $galPh['permalink']
                    , 'wrapper' => $galPh['wrapper']
                    , 'sid' => $galPh['sid']
                ));
        $galPh['gallerypluginrender'] = $this->_plugin('OnE2GWebGalleryRender', array(
                    'pages' => $galPh['pages']
                    , 'parent_id' => $galPh['parent_id']
                    , 'desc_class' => $galPh['desc_class']
                    , 'cat_name' => $galPh['cat_name']
                    , 'permalink' => $galPh['permalink']
                    , 'wrapper' => $galPh['wrapper']
                    , 'sid' => $galPh['sid']
                ));

        return $this->_filler($this->_getTpl('tpl'), $galPh);
    }

    /**
     * Gallery for &fid parameter
     * @return mixed the image's thumbail delivered in template
     */
    private function _imgFile() {
        $modx = $this->modx;
        $gdir = $this->e2gSnipCfg['gdir'];
        $fid = $this->e2gSnipCfg['fid'];
        $colls = $this->e2gSnipCfg['colls'];
        $grid = $this->e2gSnipCfg['grid'];
        $gridClass = $this->e2gSnipCfg['grid_class'];
        $landingPage = $this->e2gSnipCfg['landingpage'];
        $imgSrc = $this->e2gSnipCfg['img_src'];
        $e2gWrapper = $this->e2gSnipCfg['e2g_wrapper'];

        $selectFiles = 'SELECT * FROM ' . $modx->db->config['table_prefix'] . 'easy2_files '
                . 'WHERE id IN (' . $fid . ') '
                . 'AND status = 1 ';
        $querySelectFiles = mysql_query($selectFiles);
        if (!$querySelectFiles) {
            echo __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectFiles . '<br />';
            return FALSE;
        }
        $fileNumRows = mysql_num_rows($querySelectFiles);
        if ($fileNumRows === 0) {
            return FALSE;
        }

        // just to hide gallery's description CSS box in gallery template
        if (!isset($galPh['title']) || !isset($galPh['cat_description'])) {
            $galPh['desc_class'] = 'style="display:none;"';
        } else {
            $galPh['e2gdir_class'] = '';
        }

        // START the grid
        $galPh['content'] .= ( ($grid == 'css') ? '<div class="' . $gridClass . '">' : '<table class="' . $gridClass . '"><tr>');

        $this->_loadHeaders();
        $i = 0;
        while ($l = mysql_fetch_array($querySelectFiles, MYSQL_ASSOC)) {
            // create row grid
            if (( $i > 0 ) && ( $i % $colls == 0 ) && $grid == 'table')
                $galPh['content'] .= '</tr><tr>';

            $thumbPlaceholder = $this->_loadThumbPlaceholders($l);
            if ($thumbPlaceholder === FALSE)
                return FALSE;

            // whether configuration setting is set with or without table, the template will adjust it
            $_filler = $this->_filler($this->_getTpl('thumb_tpl'), $thumbPlaceholder);
            $galPh['content'] .= ( ($grid == 'css') ? $_filler : '<td>' . $_filler . '</td>');
            $i++;
        }
        mysql_free_result($querySelectFiles);

        // Gallery's wrapper ID
        $galPh['wrapper'] = $e2gWrapper;

        // END the grid
        $galPh['content'] .= ( ($grid == 'css') ? '</div>' : '</tr></table>');

        return $this->_filler($this->_getTpl('tpl'), $galPh);
    }

    /**
     * To create a random image usng the &rgid parameter
     * @return mixed the image's thumbail delivered in template
     */
    private function _imgRandom() {
        $modx = $this->modx;
        $limit = $this->e2gSnipCfg['limit'];
        $gdir = $this->e2gSnipCfg['gdir'];
        $rgid = $this->e2gSnipCfg['rgid'];
        $grid = $this->e2gSnipCfg['grid'];
        $gridClass = $this->e2gSnipCfg['grid_class'];
        $landingPage = $this->e2gSnipCfg['landingpage'];
        $imgSrc = $this->e2gSnipCfg['img_src'];
        $e2gWrapper = $this->e2gSnipCfg['e2g_wrapper'];

        $selectFiles = 'SELECT * FROM ' . $modx->db->config['table_prefix'] . 'easy2_files '
                . 'WHERE status = 1 '
                . 'AND dir_id IN (' . $rgid . ') '
                . 'ORDER BY RAND() '
                . 'LIMIT 1'
        ;

        $querySelectFiles = mysql_query($selectFiles);
        $fileNumRows = mysql_num_rows($querySelectFiles);
        if ($fileNumRows === 0)
            return NULL;

        // START the grid
        $galPh['content'] .= ( ($grid == 'css') ? '<div class="' . $gridClass . '">' : '<table class="' . $gridClass . '"><tr>');

        $this->_loadHeaders();

        while ($l = mysql_fetch_array($querySelectFiles, MYSQL_ASSOC)) {
            // just to hide gallery's description CSS box in gallery template
            if (!isset($galPh['title']) || !isset($galPh['cat_description'])) {
                $galPh['desc_class'] = 'style="display:none;"';
            } else
                $galPh['e2gdir_class'] = '';

            $thumbPlaceholder = $this->_loadThumbPlaceholders($l);
            if ($thumbPlaceholder === FALSE)
                return FALSE;

            // whether configuration setting is set with or without table, the template will adjust it
            $_filler = $this->_filler($this->_getTpl('rand_tpl'), $thumbPlaceholder);
            $galPh['content'] .= ( ($grid == 'css') ? $_filler : '<td>' . $_filler . '</td>');
        }
        mysql_free_result($querySelectFiles);

        // Gallery's wrapper ID
        $galPh['wrapper'] = $e2gWrapper;

        // END the grid
        $galPh['content'] .= ( ($grid == 'css') ? '</div>' : '</tr></table>');

        return $this->_filler($this->_getTpl('tpl'), $galPh);
    }

    /**
     * To get and create thumbnails
     * @param  int    $gdir             from $_GET['gid']
     * @param  string $path             directory path of each of thumbnail
     * @param  int    $w                thumbnail width
     * @param  int    $h                thumbnail height
     * @param  int    $thq              thumbnail quality
     * @param  string $resizeType       'inner' | 'resize'
     *                                  'inner' = crop the thumbnail
     *                                  'resize' = autofit the thumbnail
     * @param  int    $red              Red in RGB
     * @param  int    $green            Green in RGB
     * @param  int    $blue             Blue in RGB
     * @param  bool   $createWaterMark  create water mark
     * @return mixed FALSE/the thumbail's path
     */
    private function _imgShaper($gdir, $path, $w, $h, $thq, $resizeType=NULL, $red=NULL, $green=NULL, $blue=NULL, $createWaterMark = 0) {
        $modx = $this->modx;
        $e2gSnipCfg = $this->e2gSnipCfg;

        // decoding UTF-8
        $gdir = $this->_e2gDecode($gdir);
        $path = $this->_e2gDecode($path);
        if (empty($path))
            return FALSE;

        $w = !empty($w) ? $w : $this->e2gSnipCfg['w'];
        $h = !empty($h) ? $h : $this->e2gSnipCfg['h'];
        $thq = !empty($thq) ? $thq : $this->e2gSnipCfg['thq'];
        $resizeType = isset($resizeType) ? $resizeType : $this->e2gSnipCfg['resize_type'];
        $red = isset($red) ? $red : $this->e2gSnipCfg['thbg_red'];
        $green = isset($green) ? $green : $this->e2gSnipCfg['thbg_green'];
        $blue = isset($blue) ? $blue : $this->e2gSnipCfg['thbg_blue'];

        /**
         * Use document ID and session ID to separate between different snippet calls
         * on the same/different page(s) with different settings
         * but unfortunately with the same dimension.
         */
        $e2gStaticInstances = $this->e2gSnipCfg['e2g_static_instances'];
        $docid = $modx->documentIdentifier;
        $thumbPath = '_thumbnails/'
                . substr($path, 0, strrpos($path, '.'))
                . '_id' . $docid
                . '_sid' . $e2gStaticInstances
                . '_' . $w . 'x' . $h
                . '.jpg';

        if (!class_exists('E2gThumb')) {
            if (!file_exists(realpath(E2G_SNIPPET_PATH . 'includes/classes/e2g.public.thumbnail.class.php'))) {
                echo __LINE__ . ' : File <b>' . E2G_SNIPPET_PATH . 'includes/classes/e2g.public.thumbnail.class.php</b> does not exist.';
                return FALSE;
            } else {
                include_once E2G_SNIPPET_PATH . 'includes/classes/e2g.public.thumbnail.class.php';
            }
        }

        $imgShaper = new E2gThumb($modx, $e2gSnipCfg);
        $urlEncoding = $imgShaper->imgShaper($gdir, $path, $w, $h, $thq, $resizeType, $red, $green, $blue, $createWaterMark, $thumbPath);
        if ($urlEncoding !== FALSE) {
            return $urlEncoding;
        } else {
            return FALSE;
        }
    }

    /**
     * To get paths from the parent directory up to the Easy 2's ROOT gallery
     * @param int       $dirId      parent directory's ID
     * @param string    $option     output options: cat_name | cat_alias
     * @param mixed     $format     output formats: string | array
     * @return string
     */
    private function _getPath($dirId, $option='cat_name', $format='string') {
        return parent::getPath($dirId, $option, $format);
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
     * Get template
     * @param string    $tpl Template
     * @return string   Template's content
     */
    private function _getTpl($tpl) {
        return parent::getTpl($tpl);
    }

    /**
     * To get thumbnail for each folder
     * @param int       $gid    folder's ID
     * @param string    $gdir   gallery's ROOT path
     * @return string image's source
     */
    private function _folderImg($gid, $gdir) {
        return parent::folderImg($gid, $gdir);
    }

    /**
     * To insert included files into the page header
     * @return mixed the file inclusion or FALSE return
     */
    private function _loadHeaders() {
        $modx = $this->modx;
        $css = $this->e2gSnipCfg['css'];
        $autoloadCss = $this->e2gSnipCfg['autoload_css'];
        $js = $this->e2gSnipCfg['js'];
        $autoloadJs = $this->e2gSnipCfg['autoload_js'];
        $autoloadHtml = $this->e2gSnipCfg['autoload_html'];
        $glib = $this->e2gSnipCfg['glib'];

        // return empty, not FALSE!
        if ($glib == '0') {
            return NULL;
        }

        // GLOBAL e2g CSS styles
        if ($css !== '0' && file_exists(realpath($css))) {
            $modx->regClientCSS($modx->config['base_url'] . $css, 'screen');
        }

        // Load the library from database.
        $glibs = $this->_loadViewerConfigs($glib);
        if (!$glibs)
            return FALSE;

        if (!isset($glibs[$glib])) {
            return FALSE;
        }

        // CSS STYLES
        if (!empty($glibs[$glib]['headers_css'])
                && $glibs[$glib]['autoload_css'] == '1'
                && $autoloadCss != '0'
        ) {
            foreach ($glibs[$glib]['headers_css'] as $vRegClientCSS) {
                $modx->regClientCSS($vRegClientCSS, 'screen');
            }
        }

        // JS Libraries
        if (!empty($glibs[$glib]['headers_js'])
                && $glibs[$glib]['autoload_js'] == '1'
                && $autoloadJs != '0'
        ) {
            foreach ($glibs[$glib]['headers_js'] as $vRegClientJS) {
                $modx->regClientStartupScript($vRegClientJS);
            }
        }

        // HTMLBLOCK
        if (!empty($glibs[$glib]['headers_html'])
                && $glibs[$glib]['autoload_html'] == '1'
                && $autoloadHtml != '0'
        ) {
            $modx->regClientStartupHTMLBlock($glibs[$glib]['headers_html']);
        }

        return TRUE;
    }

    /**
     * To generate the display of each of thumbnail pieces from the Javascript libraries
     * @param  mixed $row  the thumbnail's data in an array
     * @return mixed the file inclusion, thumbnail sources, comment's controller
     */
    private function _loadThumbPlaceholders($row) {
        $modx = $this->modx;
        $gdir = $this->e2gSnipCfg['gdir'];

        // check the picture existance before continue
        if (!file_exists(realpath($gdir . $this->_getPath($row['dir_id']) . $row['filename']))) {
            return FALSE;
        }

        $css = $this->e2gSnipCfg['css'];
        $glib = $this->e2gSnipCfg['glib'];
        $landingPage = $this->e2gSnipCfg['landingpage'];
        $mbstring = $this->e2gSnipCfg['mbstring'];
        $charSet = $this->e2gSnipCfg['charset'];
        $nameLen = $this->e2gSnipCfg['name_len'];
//        $w = $this->e2gSnipCfg['w'];
//        $h = $this->e2gSnipCfg['h'];
        $thq = $this->e2gSnipCfg['thq'];
        $imgSrc = $this->e2gSnipCfg['img_src'];
        $e2gStaticInstances = $this->e2gSnipCfg['e2g_static_instances'];

        // COMMENT
        $ecm = $this->e2gSnipCfg['ecm'];

        $row['w'] = $this->e2gSnipCfg['w'];
        $row['h'] = $this->e2gSnipCfg['h'];
        $useRedirectLink = $this->e2gSnipCfg['use_redirect_link'];

        // SLIDESHOW
        $showGroup = $this->e2gSnipCfg['show_group'];
        $modx->setPlaceholder('easy2:show_group', $showGroup);

        ########################################################################

        $glibs = $this->_loadViewerConfigs($glib, $row['id']);
        $modx->setPlaceholder('easy2:fid', $row['id']);

        $row['glibact'] = '';
        if (isset($landingPage) || $glib == '0') {
            $row['glibact'] = NULL;
        }
        // gallery's javascript library activation
        elseif (isset($glibs[$glib])) {
            $row['glibact'] = $glibs[$glib]['glibact'];
        }
        else
            return FALSE;

        $title = trim($row['alias']) != '' ? $row['alias'] : $row['filename'];
        $row['title'] = $this->_cropName($mbstring, $charSet, $nameLen, $title);

        $path = $this->_getPath($row['dir_id']);
        $imgShaper = $this->_imgShaper($gdir, $path . $row['filename'], $row['w'], $row['h'], $thq);
        if ($imgShaper !== FALSE) {
            $row['src'] = $imgShaper;
        } else {
            $row['src'] = 'assets/modules/easy2/show.easy2gallery.php?w=' . $row['w'] . '&amp;h=' . $row['h'] . '&amp;th=5';
        }
        unset($imgShaper);

        if (isset($landingPage)) {
            $row['link'] = $modx->makeUrl($landingPage
                            , $modx->aliases
                            , 'lp=' . $landingPage)
                    . '&amp;fid=' . $row['id']
            ;
        } elseif ($useRedirectLink === TRUE && !empty($row['redirect_link'])) {
            $row['link'] = $row['redirect_link'];
            $row['glibact'] = '';
        } else {
            if ($imgSrc == 'generated') {
                $row['link'] = 'assets/modules/easy2/show.easy2gallery.php?fid=' . $row['id'];
            } elseif ($imgSrc == 'original') {
                // path to subdir's thumbnail
                $path = $this->_getPath($row['dir_id']);
                $row['link'] = $gdir . $path . $row['filename'];
            }
        } // if ( isset($landingPage) )

        if ($row['description'] != '') {
            $row['description'] = $this->_stripHTMLTags(htmlspecialchars_decode($row['description'], ENT_QUOTES));
        }

        /**
         * invoke plugin for EACH thumb
         */
        // creating the plugin array's content
        $e2gEvtParams = array();
        $row['sid'] = $e2gStaticInstances;
        foreach ($row as $k => $v) {
            $e2gEvtParams[$k] = $v;
        }

        $row['thumbpluginprerender'] = $this->_plugin('OnE2GWebThumbPrerender', $e2gEvtParams);
        $row['thumbpluginrender'] = $this->_plugin('OnE2GWebThumbRender', $e2gEvtParams);

        // conversion
        $row['name'] = $row['alias'];

        /**
         * Comments on the thumbnails
         */
        // HIDE COMMENTS from Ignored IP Addresses
        $ip = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];

        $checkIgnoredIp = $this->_checkIgnoredIp($ip);

        if ($ecm == 1 && (!$checkIgnoredIp)) {
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

        return $row;
    }

    private function _loadViewerConfigs($glib, $fid=NULL) {
        $modx = $this->modx;
        // SLIDESHOW
        $showGroup = $this->e2gSnipCfg['show_group'];
        $modx->setPlaceholder('easy2:show_group', $showGroup);
        $fid = $this->e2gSnipCfg['fid'];
//        $modx->toPlaceholder('fid', $fid, 'easy2:');
        $modx->setPlaceholder('easy2:fid', $fid);

        // if &glib=`0`, empty($glib) returns TRUE.
        // http://us2.php.net/empty
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
    private function _slideshow($slideshow) {
        $modx = $this->modx;
        // database selection
        $gdir = $this->e2gSnipCfg['gdir'];

        $ssIndexFile = $this->e2gSnipCfg['ss_indexfile'];
        $ssCss = $this->e2gSnipCfg['ss_css'];
        $ssJs = $this->e2gSnipCfg['ss_js'];

        // slideshow's image settings
        $ssImgSrc = $this->e2gSnipCfg['ss_img_src'];

        // self landingpage
        $css = $this->e2gSnipCfg['css'];
        $js = $this->e2gSnipCfg['js'];
        $landingPage = $this->e2gSnipCfg['landingpage'];
        $e2gStaticInstances = $this->e2gSnipCfg['e2g_static_instances'];

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
            return $this->_landingPage($_GET['fid']);
        } else {
            /**
             * The DEFAULT display
             */
            // use custom index file if it's been set inside snippet call.
            if (isset($ssIndexFile)) {
                if (file_exists(realpath($ssIndexFile))) {
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
                    echo __LINE__ . ' : ' . mysql_error() . '<br />' . $selectIndexFile . '<br />';
                    return FALSE;
                }
//                $dbIndexFile = mysql_result($queryIndexFile, 0, 0);
                $row = mysql_fetch_row($queryIndexFile);
                $dbIndexFile = $row[0];
                if ($dbIndexFile == '') {
                    echo __LINE__ . ' : Empty index file in database.';
                    return FALSE;
                } elseif (file_exists(realpath($dbIndexFile))) {
                    ob_start();
                    include($dbIndexFile);
                    $ssDisplay = ob_get_contents();
                    ob_end_clean();
                } else {
                    echo __LINE__ . ' : Slideshow index file <b>' . $dbIndexFile . '</b> is not found.<br />';
                    return FALSE;
                }
            }
        }

        $output = array();
        $output['slideshow'] = $ssDisplay;
        $output['wrapper'] = $this->e2gSnipCfg['e2g_wrapper'];
        $output['sid'] = $this->e2gSnipCfg['e2g_static_instances'];

        return $this->_filler($this->_getTpl('slideshow-tpl'), $output);
    }

    private function _getSlideShowParams() {
        // database selection
        $ssParams['gdir'] = $this->e2gSnipCfg['gdir'];
        $ssParams['gid'] = $this->e2gSnipCfg['gid'];
        $ssParams['gid'] = $this->e2gSnipCfg['gid'];
        $ssParams['fid'] = $this->e2gSnipCfg['fid'];
        $ssParams['rgid'] = $this->e2gSnipCfg['rgid'];
        $ssParams['where_dir'] = $this->e2gSnipCfg['where_dir'];
        $ssParams['where_file'] = $this->e2gSnipCfg['where_file'];
        $ssParams['ss_allowedratio'] = $this->e2gSnipCfg['ss_allowedratio'];
        /**
         * Filtering the slideshow size ratio
         */
        if ($ssParams['ss_allowedratio'] != 'all') {
            // create min-max slideshow width/height ratio
            $ssXpldRatio = explode('-', $ssParams['ss_allowedratio']);

            $ssMinRatio = trim($ssXpldRatio[0]);
            $ssMinRatio = str_replace(',', '.', $ssMinRatio);
            $ssMinRatio = @explode('.', $ssMinRatio);
            $ssParams['ss_minratio'] = @implode('.', array(intval($ssMinRatio[0]), intval($ssMinRatio[1])));

            $ssMaxRatio = trim($ssXpldRatio[1]);
            $ssMaxRatio = str_replace(',', '.', $ssMaxRatio);
            $ssMaxRatio = @explode('.', $ssMaxRatio);
            $ssParams['ss_maxratio'] = @implode('.', array(intval($ssMaxRatio[0]), intval($ssMaxRatio[1])));
        }

        $ssParams['gpn'] = $this->e2gSnipCfg['gpn'];
        $ssParams['ss_limit'] = $this->e2gSnipCfg['ss_limit'];
        if ($this->e2gSnipCfg['orderby'] == 'random') {
            $ssParams['orderby'] = 'rand()';
            $ssParams['order'] = '';
        } else {
            $ssParams['orderby'] = $this->e2gSnipCfg['orderby'];
            $ssParams['order'] = $this->e2gSnipCfg['order'];
        }
        if ($this->e2gSnipCfg['cat_orderby'] == 'random') {
            $ssParams['cat_orderby'] = 'rand()';
            $ssParams['cat_order'] = '';
        } else {
            $ssParams['cat_orderby'] = $this->e2gSnipCfg['cat_orderby'];
            $ssParams['cat_order'] = $this->e2gSnipCfg['cat_order'];
        }
        $ssParams['ss_orderby'] = $this->e2gSnipCfg['ss_orderby'];
        $ssParams['ss_order'] = $this->e2gSnipCfg['ss_order'];

        // self landingpage
        $ssParams['css'] = $this->e2gSnipCfg['css'];
        $ssParams['js'] = $this->e2gSnipCfg['js'];
        $ssParams['landingpage'] = $this->e2gSnipCfg['landingpage'];

        // initial slideshow's controller and headers
        $ssParams['ss_css'] = $this->e2gSnipCfg['ss_css'];
        $ssParams['ss_js'] = $this->e2gSnipCfg['ss_js'];
        $ssParams['ss_config'] = $this->e2gSnipCfg['ss_config'];

        // thumbnail settings
        $ssParams['w'] = $this->e2gSnipCfg['w'];
        $ssParams['h'] = $this->e2gSnipCfg['h'];
        $ssParams['thq'] = $this->e2gSnipCfg['thq'];
        $ssParams['resize_type'] = $this->e2gSnipCfg['resize_type'];
        $ssParams['thbg_red'] = $this->e2gSnipCfg['thbg_red'];
        $ssParams['thbg_green'] = $this->e2gSnipCfg['thbg_green'];
        $ssParams['thbg_blue'] = $this->e2gSnipCfg['thbg_blue'];

        // slideshow's image settings
        $ssParams['ss_img_src'] = $this->e2gSnipCfg['ss_img_src'];
        $ssParams['ss_w'] = $this->e2gSnipCfg['ss_w'];
        $ssParams['ss_h'] = $this->e2gSnipCfg['ss_h'];
        $ssParams['ss_thq'] = $this->e2gSnipCfg['ss_thq'];
        $ssParams['ss_resize_type'] = $this->e2gSnipCfg['ss_resize_type'];
        $ssParams['ss_bg'] = $this->e2gSnipCfg['ss_bg'];
        $ssParams['ss_red'] = $this->e2gSnipCfg['ss_red'];
        $ssParams['ss_green'] = $this->e2gSnipCfg['ss_green'];
        $ssParams['ss_blue'] = $this->e2gSnipCfg['ss_blue'];

        return $ssParams;
    }

    private function _getSlideShowFiles() {
        $modx = $this->modx;
        // database selection
        $gdir = $this->e2gSnipCfg['gdir'];
        $gid = $this->e2gSnipCfg['gid'];
        $fid = $this->e2gSnipCfg['fid'];
        $rgid = $this->e2gSnipCfg['rgid'];

        $whereFile = $this->e2gSnipCfg['where_file'];

        if ($this->e2gSnipCfg['ss_orderby'] == 'random') {
            $ssOrderBy = 'rand()';
            $ssOrder = '';
        } else {
            $ssOrderBy = $this->e2gSnipCfg['ss_orderby'];
            $ssOrder = $this->e2gSnipCfg['ss_order'];
        }

        $gpn = $this->e2gSnipCfg['gpn'];
        $ssLimit = $this->e2gSnipCfg['ss_limit'];

        // thumbnail settings
        $w = $this->e2gSnipCfg['w'];
        $h = $this->e2gSnipCfg['h'];
        $thq = $this->e2gSnipCfg['thq'];
        $resizeType = $this->e2gSnipCfg['resize_type'];
        $thbgRed = $this->e2gSnipCfg['thbg_red'];
        $thbgGreen = $this->e2gSnipCfg['thbg_green'];
        $thbgBlue = $this->e2gSnipCfg['thbg_blue'];

        // slideshow's image settings
        $ssImgSrc = $this->e2gSnipCfg['ss_img_src'];
        $ssW = $this->e2gSnipCfg['ss_w'];
        $ssH = $this->e2gSnipCfg['ss_h'];
        $ssThq = $this->e2gSnipCfg['ss_thq'];
        $ssResizeType = $this->e2gSnipCfg['ss_resize_type'];
        $ssBg = $this->e2gSnipCfg['ss_bg'];
        $ssRed = $this->e2gSnipCfg['ss_red'];
        $ssGreen = $this->e2gSnipCfg['ss_green'];
        $ssBlue = $this->e2gSnipCfg['ss_blue'];

        // landscape/portrait image's ratio for the slideshow box
        $ssAllowedRatio = $this->e2gSnipCfg['ss_allowedratio'];

        // self landingpage
        $landingPage = $this->e2gSnipCfg['landingpage'];

        /**
         * Filtering the slideshow size ratio
         */
        if ($ssAllowedRatio != 'all') {
            // create min-max slideshow width/height ratio
            $ssXpldRatio = explode('-', $ssAllowedRatio);

            $ssMinRatio = trim($ssXpldRatio[0]);
            $ssMinRatio = str_replace(',', '.', $ssMinRatio);
            $ssMinRatio = @explode('.', $ssMinRatio);
            $ssMinRatio = @implode('.', array(intval($ssMinRatio[0]), intval($ssMinRatio[1])));

            $ssMaxRatio = trim($ssXpldRatio[1]);
            $ssMaxRatio = str_replace(',', '.', $ssMaxRatio);
            $ssMaxRatio = @explode('.', $ssMaxRatio);
            $ssMaxRatio = @implode('.', array(intval($ssMaxRatio[0]), intval($ssMaxRatio[1])));
        }

        $ssFiles = array();
        $errorThumb = 'assets/modules/easy2/show.easy2gallery.php?w=' . $w . '&amp;h=' . $h . '&amp;th=2';
        $errorImg = 'assets/modules/easy2/show.easy2gallery.php?w=' . $ssW . '&amp;h=' . $ssH . '&amp;th=5';

        if (isset($gid) && $modx->documentIdentifier != $landingPage) {
            $selectFiles = 'SELECT * FROM ' . $modx->db->config['table_prefix'] . 'easy2_files WHERE ';

            if ($gid != '*') {
                $selectFiles .= 'dir_id IN (' . $gid . ') AND ';
            }

            if (isset($whereFile)) {
                $where = $this->_whereClause($whereFile);
                if (!$where) {
                    echo __LINE__ . ' : ' . $whereFile . '<br />';
                    return FALSE;
                } else {
                    $selectFiles .= $where . ' AND ';
                }
            }

            if ($ssAllowedRatio != 'all') {
                $selectFiles .= 'width/height >=\'' . floatval($ssMinRatio) . '\' AND width/height<=\'' . floatval($ssMaxRatio) . '\' AND ';
            }

            $selectFiles .= 'status = 1 '
                    . 'ORDER BY ' . $ssOrderBy . ' ' . $ssOrder . ' '
                    . ( $ssLimit == 'none' ? '' : 'LIMIT ' . ( $gpn * $ssLimit ) . ', ' . $ssLimit )
            ;
            $querySelectFiles = mysql_query($selectFiles);
            if (!$querySelectFiles) {
                echo __LINE__ . ' : ' . mysql_error() . '<br />' . $selectFiles . '<br />';
                return FALSE;
            }

            while ($row = mysql_fetch_array($querySelectFiles)) {
                $path = $this->_getPath($row['dir_id']);

                $thumbImg = $this->_imgShaper($gdir, $path . $row['filename'], $w, $h, $thq,
                                $resizeType, $thbgRed, $thbgGreen, $thbgBlue);
                // thumbnail first...
                if ($thumbImg !== FALSE) {
                    // ... then the slideshow's images
                    if ($ssImgSrc == 'generated') {
                        /**
                         * + WATERMARK-ing
                         */
                        $ssImg = $this->_imgShaper($gdir, $path . $row['filename'], $ssW, $ssH, $ssThq,
                                        $ssResizeType, $ssRed, $ssGreen, $ssBlue, 1);
                        if ($ssImg !== FALSE) {
                            $ssFiles['resizedimg'][] = $ssImg;
                        } else {
//                            $ssFiles['resizedimg'][] = $errorImg;
                            continue;
                        }
                        unset($ssImg);
                    } elseif ($ssImgSrc == 'original') {
                        $ssFiles['resizedimg'][] = $this->_e2gDecode($gdir . $path . $row['filename']);
                    }

                    // if the slideshow's images were created successfully
                    $ssFiles['thumbsrc'][] = $thumbImg;
                } else {
//                    $ssFiles['thumbsrc'][] = $errorThumb . '&amp;text=' . __LINE__;
                    continue;
                }
                unset($thumbImg);

                $ssFiles['id'][] = $row['id'];
                $ssFiles['dirid'][] = $row['dir_id'];
                $ssFiles['src'][] = $this->_e2gDecode($gdir . $path . $row['filename']);
                $ssFiles['filename'][] = $row['filename'];
                $ssFiles['title'][] = ($row['alias'] != '' ? $row['alias'] : $row['filename']);
                $ssFiles['alias'][] = $row['alias'];
                $ssFiles['name'][] = $row['alias'];
                $ssFiles['description'][] = $this->_stripHTMLTags(htmlspecialchars_decode($row['description'], ENT_QUOTES));
                $ssFiles['tag'][] = $row['tag'];
                $ssFiles['summary'][] = $row['summary'];
            }
            mysql_free_result($querySelectFiles);
        }

        if (isset($fid)) {
            $selectFiles = 'SELECT * FROM ' . $modx->db->config['table_prefix'] . 'easy2_files '
                    . 'WHERE id IN (' . $fid . ') ';

            // disable this, since the file is accessed directly
//            if ($ssAllowedRatio != 'all') {
//                $selectFiles .= 'AND width/height >=\'' . floatval($ssMinRatio) . '\' OR width/height<=\'' . floatval($ssMaxRatio) . '\' ';
//            }

            $selectFiles .= 'AND status = 1 '
            ;
            $querySelectFiles = mysql_query($selectFiles);
            if (!$querySelectFiles) {
                echo __LINE__ . ' : ' . mysql_error() . '<br />' . $selectFiles . '<br />';
                return FALSE;
            }

            while ($row = mysql_fetch_array($querySelectFiles)) {
                $path = $this->_getPath($row['dir_id']);

                $thumbImg = $this->_imgShaper($gdir, $path . $row['filename'], $w, $h, $thq,
                                $resizeType, $thbgRed, $thbgGreen, $thbgBlue);
                // thumbnail first...
                if ($thumbImg !== FALSE) {
                    // ... then the slideshow's images
                    if ($ssImgSrc == 'generated') {
                        /**
                         * + WATERMARK-ing
                         */
                        $ssImg = $this->_imgShaper($gdir, $path . $row['filename'], $ssW, $ssH, $ssThq,
                                        $ssResizeType, $ssRed, $ssGreen, $ssBlue, 1);
                        if ($ssImg !== FALSE) {
                            $ssFiles['resizedimg'][] = $ssImg;
                        } else {
//                            $ssFiles['resizedimg'][] = $errorImg;
                            continue;
                        }
                        unset($ssImg);
                    } elseif ($ssImgSrc == 'original') {
                        $ssFiles['resizedimg'][] = $this->_e2gDecode($gdir . $path . $row['filename']);
                    }

                    // if the slideshow's images were created successfully
                    $ssFiles['thumbsrc'][] = $thumbImg;
                } else {
//                    $ssFiles['thumbsrc'][] = $errorThumb . '&amp;text=' . __LINE__;
                    continue;
                }
                unset($thumbImg);

                $ssFiles['id'][] = $row['id'];
                $ssFiles['dirid'][] = $row['dir_id'];
                $ssFiles['src'][] = $this->_e2gDecode($gdir . $path . $row['filename']);
                $ssFiles['filename'][] = $row['filename'];
                $ssFiles['title'][] = ($row['alias'] != '' ? $row['alias'] : $row['filename']);
                $ssFiles['alias'][] = $row['alias'];
                $ssFiles['name'][] = $row['alias'];
                $ssFiles['description'][] = htmlspecialchars_decode($row['description'], ENT_QUOTES);
                $ssFiles['tag'][] = $row['tag'];
                $ssFiles['summary'][] = $row['summary'];
            }
        }

        if (isset($rgid)) {
            $selectFiles = 'SELECT * FROM ' . $modx->db->config['table_prefix'] . 'easy2_files '
                    . 'WHERE status = 1 '
                    . 'AND dir_id IN (' . $rgid . ') ';

            if ($ssAllowedRatio != 'all') {
                $selectFiles .= 'AND width/height >=\'' . floatval($ssMinRatio) . '\' OR width/height<=\'' . floatval($ssMaxRatio) . '\' ';
            }

            $selectFiles .= 'ORDER BY RAND() '
                    . ( $ssLimit == 'none' ? '' : 'LIMIT ' . ( $gpn * $ssLimit ) . ',' . $ssLimit . ' ' )
            ;
            $querySelectFiles = mysql_query($selectFiles);
            if (!$querySelectFiles) {
                echo __LINE__ . ' : ' . mysql_error() . '<br />' . $selectFiles . '<br />';
                return FALSE;
            }
            while ($row = mysql_fetch_array($querySelectFiles)) {
                $path = $this->_getPath($row['dir_id']);

                $thumbImg = $this->_imgShaper($gdir, $path . $row['filename'], $w, $h, $thq,
                                $resizeType, $thbgRed, $thbgGreen, $thbgBlue);
                // thumbnail first...
                if ($thumbImg !== FALSE) {
                    // ... then the slideshow's images
                    if ($ssImgSrc == 'generated') {
                        /**
                         * + WATERMARK-ing
                         */
                        $ssImg = $this->_imgShaper($gdir, $path . $row['filename'], $ssW, $ssH, $ssThq,
                                        $ssResizeType, $ssRed, $ssGreen, $ssBlue, 1);
                        if ($ssImg !== FALSE) {
                            $ssFiles['resizedimg'][] = $ssImg;
                        } else {
//                            $ssFiles['resizedimg'][] = $errorImg;
                            continue;
                        }
                        unset($ssImg);
                    } elseif ($ssImgSrc == 'original') {
                        $ssFiles['resizedimg'][] = $this->_e2gDecode($gdir . $path . $row['filename']);
                    }

                    // if the slideshow's images were created successfully
                    $ssFiles['thumbsrc'][] = $thumbImg;
                } else {
//                    $ssFiles['thumbsrc'][] = $errorThumb . '&amp;text=' . __LINE__;
                    continue;
                }
                unset($thumbImg);

                $ssFiles['id'][] = $row['id'];
                $ssFiles['dirid'][] = $row['dir_id'];
                $ssFiles['src'][] = $this->_e2gDecode($gdir . $path . $row['filename']);
                $ssFiles['filename'][] = $row['filename'];
                $ssFiles['title'][] = ($row['alias'] != '' ? $row['alias'] : $row['filename']);
                $ssFiles['alias'][] = $row['alias'];
                $ssFiles['name'][] = $row['alias'];
                $ssFiles['description'][] = htmlspecialchars_decode($row['description'], ENT_QUOTES);
                $ssFiles['tag'][] = $row['tag'];
                $ssFiles['summary'][] = $row['summary'];
            }
            mysql_free_result($querySelectFiles);
        }

        /**
         * if the counting below = 0 (zero), then should be considered inside
         * the slideshow types, while for some slideshows this doesn't really matter.
         */
        $ssFiles['count'] = count($ssFiles['src']);

        return $ssFiles;
    }

    /**
     * A landing page to show the image, including information within it.
     * @param  int   $fileId file's ID
     * @return mixed scripts, images, and FALSE return
     */
    private function _landingPage($fileId) {
        $modx = $this->modx;

        $landingPage = $this->e2gSnipCfg['landingpage'];
//        if ($modx->documentIdentifier != $landingPage)
//            return NULL;
        $page_tpl_css = $this->e2gSnipCfg['page_tpl_css'];

        // page settings
        $lp_img_src = $this->e2gSnipCfg['lp_img_src'];
        $lpW = $this->e2gSnipCfg['lp_w'];
        $lpH = $this->e2gSnipCfg['lp_h'];
        $lpThq = $this->e2gSnipCfg['lp_thq'];
        $lpResizeType = $this->e2gSnipCfg['lp_resize_type'];
        $lpBg = $this->e2gSnipCfg['lp_bg'];
        $lpRed = $this->e2gSnipCfg['lp_red'];
        $lpGreen = $this->e2gSnipCfg['lp_green'];
        $lpBlue = $this->e2gSnipCfg['lp_blue'];

//        $plugin = $this->e2gSnipCfg['plugin'];
        $gdir = $this->e2gSnipCfg['gdir'];
        $css = $this->e2gSnipCfg['css'];
        $js = $this->e2gSnipCfg['js'];
        $ecm = $this->e2gSnipCfg['ecm'];
        $e2gWrapper = $this->e2gSnipCfg['e2g_wrapper'];

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
            echo __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $select . '<br />';
            return FALSE;
        }

        while ($fetch = mysql_fetch_array($query)) {
            $path = $this->_getPath($fetch['dir_id']);

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
                    $imgSize = @getimagesize($gdir . $this->_e2gDecode($path . $fetch['filename']));
                    if (!isset($lpW))
                        $lpW = $imgSize[0];
                    if (!isset($lpH))
                        $lpH = $imgSize[1];
                    $imgSize = array();
                    unset($imgSize);
                }
                $imgShaper = $this->_imgShaper($gdir, $path . $fetch['filename'], $lpW, $lpH, $lpThq, $lpResizeType,
                                $lpRed, $lpGreen, $lpBlue, 1);
                if ($imgShaper !== FALSE) {
                    $filePath = $imgShaper;
                } else {
                    $filePath = 'assets/modules/easy2/show.easy2gallery.php?w=' . $lpW . '&amp;h=' . $lpH . '&amp;th=5';
                }
                unset($imgShaper);

//                if (strpos($_SERVER['DOCUMENT_ROOT'], '/') === (int) 0) {
                if (strtoupper(substr(PHP_OS, 0, 3) != 'WIN')) {
                    $l['src'] = rawurldecode(str_replace('%2F', '/', rawurlencode($filePath)));
                } else
                    $l['src'] = $filePath;
            }

            $l['title'] = ($fetch['alias'] != '' ? $fetch['alias'] : $fetch['filename']);
            $l['alias'] = $fetch['alias'];
            $l['name'] = $fetch['alias'];
            $l['description'] = htmlspecialchars_decode($fetch['description'], ENT_QUOTES);

            /**
             * Comments on the landing page
             */
            // HIDE COMMENTS from Ignored IP Addresses
            $ip = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];

            $checkIgnoredIp = $this->_checkIgnoredIp($ip);

            if ($ecm == 1 && (!$checkIgnoredIp)) {

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

        return $this->_filler($this->_getTpl('page_tpl'), $l);
    }

    /**
     * Comment function for a page (landingpage or galley)
     * @param  string $fileId File ID of the comment's owner
     * @return mixed  return the comment's page content
     */
    private function _comments($fileId) {
        $modx = $this->modx;
        $landingPage = $this->e2gSnipCfg['landingpage'];
        $recaptcha = $this->e2gSnipCfg['recaptcha'];
        $eclPage = $this->e2gSnipCfg['ecl_page'];
        $cpn = (empty($_GET['cpn']) || !is_numeric($_GET['cpn'])) ? 0 : (int) $_GET['cpn'];

        require_once(E2G_SNIPPET_PATH . 'includes/recaptchalib.php');
        // Get a key from https://www.google.com/recaptcha/admin/create
        $publicKey = $this->e2gSnipCfg['recaptcha_key_public'];
        $privatekey = $this->e2gSnipCfg['recaptcha_key_private'];

        if (file_exists(realpath(E2G_SNIPPET_PATH . 'includes/langs/' . $modx->config['manager_language'] . '.comments.php'))) {
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

            if (!$this->_checkEmailAddress($e)) {
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
        $selectComments = 'SELECT * FROM ' . $modx->db->config['table_prefix'] . 'easy2_comments '
                . 'WHERE file_id = ' . $fileId . ' '
                . 'AND STATUS=1 '
                . 'ORDER BY id DESC '
                . 'LIMIT ' . ($cpn * $eclPage) . ', ' . $eclPage;
        $querySelectComments = mysql_query($selectComments);
        if (!$querySelectComments) {
            echo __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectComments . '<br />';
            return FALSE;
        }

        $rowClassNum = 0;
        while ($l = mysql_fetch_array($querySelectComments, MYSQL_ASSOC)) {

            $l['i'] = $rowClassNum % 2;

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

            $_P['comment_body'] .= $this->_filler($this->_getTpl('page_comments_row_tpl'), $l);
            $rowClassNum++;
        }
        mysql_free_result($querySelectComments);

        $_P['pages_permalink'] = '<a href="#" name="lpcmtpg' . $cpn . '"></a>';

        // Comment pages
        $selectCountComments = 'SELECT COUNT(*) FROM ' . $modx->db->config['table_prefix'] . 'easy2_comments WHERE file_id = ' . $fileId;
        $querySelectCountComments = mysql_query($selectCountComments);
        if (!$querySelectCountComments) {
            echo __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectCountComments . '<br />';
            return FALSE;
        }

        list($cnt) = mysql_fetch_row($querySelectCountComments);
        mysql_free_result($querySelectCountComments);

        if ($cnt > $eclPage) {
            $_P['comment_pages'] = '<p class="pnums">' . $lngCmt['pages'] . ':';
            $commentPageNum = 0;
            while ($commentPageNum * $eclPage < $cnt) {
                if ($commentPageNum == $cpn)
                    $_P['comment_pages'] .= '<b>' . ($commentPageNum + 1) . '</b> ';
                else
                    $_P['comment_pages'] .=
                            '<a href="'
                            // making flexible FURL or not
                            . $modx->makeUrl($modx->documentIdentifier
                                    , $modx->aliases
                                    , 'sid=' . $e2gStaticInstances)
                            . '&amp;lp=' . $landingPage . '&amp;fid=' . $fileId . '&amp;cpn=' . $commentPageNum . '#lpcmtpg' . $commentPageNum
                            . '">' . ($commentPageNum + 1) . '</a> ';
                $commentPageNum++;
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
        return $this->_filler($this->_getTpl('page_comments_tpl'), $_P);
    }

    /**
     * email validation
     * @param  string $email
     * @return bool   TRUE/FALSE
     */
    private function _checkEmailAddress($email) {
        return parent::checkEmailAddress($email);
    }

    /**
     * Invoking the script with plugin, at any specified places.
     * @param string    $e2gEvtName     event trigger.
     * @param mixed     $e2gEvtParams   parameters array: depends on the event trigger.
     * @return mixed    if TRUE, will return the indexfile. Otherwise this will return FALSE.
     */
    private function _plugin($e2gEvtName, $e2gEvtParams=array()) {
        $plugin = $this->e2gSnipCfg['plugin'];

        // if the user set &plugin=`none`
        if ($plugin == 'none')
            return NULL;

        // call plugin from the database as default
        if (!isset($plugin)) {
            return parent::plugin($e2gEvtName, $e2gEvtParams);
        }

        // if the plugins are called from the snippet
        // example: &plugin=`thumb:starrating#Prerender, watermark@custom/index/file.php | gallery:... | landingpage:...`
        // clean up
        $badChars = array('`', ' ');
        $plugin = str_replace($badChars, '', trim($plugin));

        // generate the splitting targets with their names, area, and parameters
        $xpldPlugins = array();
        $xpldPlugins = @explode('|', trim($plugin));
        // read them one by one
        foreach ($xpldPlugins as $p_category) {
            // get the plugins' targets and names
            $xpldsettings = array();
            $xpldsettings = @explode(':', trim($p_category));

            // get the plugins' targets: thumb | gallery | landingpage
            $pluginTarget = $xpldsettings [0];
            // get the plugins' names: starrating#Prerender, watermark
            $p_selections = $xpldsettings [1];

            // to disable the default action of the registered plugin in database
            // eg: thumb:none
            if ($p_selections == 'none')
                return NULL;

            $xpldTypes = array();
            $xpldTypes = @explode(',', trim($p_selections));

            foreach ($xpldTypes as $pluginType) {
                $xpldIndexes = array();
                $xpldIndexes = @explode('@', trim($pluginType));
                $pluginIndexFile = $xpldIndexes[1];

                $xpldNames = array();
                $xpldNames = @explode('#', $xpldIndexes[0]);
                $pluginName = $xpldNames[0];
                $pluginArea = strtolower($xpldNames[1]);
                if (empty($pluginArea))
                    $pluginArea = 'prerender';

                // to disable the default action of the registered plugin in database
                // eg: thumb:starrating#none
                if ($pluginArea == 'none')
                    return NULL;

                $convertEvtName = '';
                if ($pluginTarget == 'thumb' && $pluginArea == 'prerender')
                    $convertEvtName = 'OnE2GWebThumbPrerender';
                elseif ($pluginTarget == 'thumb' && $pluginArea == 'render')
                    $convertEvtName = 'OnE2GWebThumbRender';
                elseif ($pluginTarget == 'dir' && $pluginArea == 'prerender')
                    $convertEvtName = 'OnE2GWebDirPrerender';
                elseif ($pluginTarget == 'dir' && $pluginArea == 'render')
                    $convertEvtName = 'OnE2GWebDirRender';
                elseif ($pluginTarget == 'gallery' && $pluginArea == 'prerender')
                    $convertEvtName = 'OnE2GWebGalleryPrerender';
                elseif ($pluginTarget == 'gallery' && $pluginArea == 'render')
                    $convertEvtName = 'OnE2GWebGalleryRender';
                elseif ($pluginTarget == 'landingpage' && $pluginArea == 'prerender')
                    $convertEvtName = 'OnE2GWebLandingpagePrerender';
                elseif ($pluginTarget == 'landingpage' && $pluginArea == 'render')
                    $convertEvtName = 'OnE2GWebLandingpageRender';
                else
                    $convertEvtName = '';

                if ($convertEvtName != $e2gEvtName)
                    return FALSE;

                unset($convertEvtName);

                // LOAD DA FILE!
                if (empty($pluginIndexFile)) {
                    // surpress the disabled plugin by adding the 4th parameter as 'FALSE'.
                    $out = parent::plugin($e2gEvtName, $e2gEvtParams, $pluginName, FALSE);
                    if ($out !== FALSE)
                        return $out;
                } else {
                    if (!file_exists(realpath($pluginIndexFile))) {
                        echo __LINE__ . ' : File <b>' . $pluginIndexFile . '</b> does not exist.';
                        return FALSE;
                    }
                    ob_start();
                    include $pluginIndexFile;
                    $out = ob_get_contents();
                    ob_end_clean();
                    return $out;
                } // if (empty($pluginIndexFile))
            } // foreach ($xpldTypes as $pluginType)
        } // foreach ($xpldPlugins as $p_category)
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

        // for global variable: '*' (star), always returns TRUE
        if ($staticId == '*')
            return TRUE;

        $selectDirs = 'SELECT A.cat_id FROM ' . $modx->db->config['table_prefix'] . 'easy2_dirs A, '
                . $modx->db->config['table_prefix'] . 'easy2_dirs B '
                . 'WHERE B.cat_id IN (' . $staticId . ') '
                . 'AND A.cat_left BETWEEN B.cat_left AND B.cat_right '
        ;
        $querySelectDirs = mysql_query($selectDirs);
        if (!$querySelectDirs) {
            echo __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectDirs . '<br />';
            return FALSE;
        }
        while ($l = mysql_fetch_array($querySelectDirs, MYSQL_ASSOC)) {
            $check[$l['cat_id']] = $l['cat_id'];
        }
        mysql_free_result($querySelectDirs);

        $xpldGids = explode(',', $id);
        foreach ($xpldGids as $gid) {
            if (!$check[$gid] && ($staticId != 1)) {
                return FALSE;
//                return $modx->sendUnauthorizedPage();
            } elseif (!$check[$gid] && ($staticId == 1)) {
                return FALSE;
//                return $modx->sendErrorPage();
            }
        }
        return TRUE;
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
        $selectFiles = 'SELECT id FROM ' . $modx->db->config['table_prefix'] . 'easy2_files '
                . 'WHERE id IN (' . $staticId . ') '
        ;
        $querySelectFiles = mysql_query($selectFiles);
        if (!$querySelectFiles) {
            echo __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectFiles . '<br />';
            return FALSE;
        }
        while ($l = mysql_fetch_array($querySelectFiles, MYSQL_ASSOC)) {
            $check[$l['id']] = $l['id'];
        }
        mysql_free_result($querySelectFiles);

        $xpldFids = explode(',', $id);
        foreach ($xpldFids as $fid) {
            if (!$check[$fid]) {
                return $modx->sendErrorPage();
            }
        }
        return TRUE;
    }

    /**
     * CHECK the valid parent IDs of the &tag parameter
     * @param string $dirOrFile dir|file
     * @param string $tag from &tag parameter
     * @param int    $id  id of the specified dir/file
     * @return bool TRUE | FALSE
     */
    private function _checkTaggedDirIds($tag, $id=1) {
        $modx = $this->modx;
        $tag = strtolower($tag);

        $selectTaggedDirs = 'SELECT cat_id FROM ' . $modx->db->config['table_prefix'] . 'easy2_dirs ';

        $xpldDirTags = @explode(',', $tag);
        $countDirTags = count($xpldDirTags);
        for ($i = 0; $i < $countDirTags; $i++) {
            if ($i === 0)
                $selectTaggedDirs .= 'WHERE LOWER(cat_tag) LIKE \'%' . $xpldDirTags[$i] . '%\' ';
            else
                $selectTaggedDirs .= 'OR LOWER(cat_tag) LIKE \'%' . $xpldDirTags[$i] . '%\' ';
        }

        $excludeDirWebAccess = $this->_excludeWebAccess('dir');

        if ($excludeDirWebAccess !== FALSE) {
            $selectTaggedDirs .= 'AND cat_id NOT IN (' . $excludeDirWebAccess . ') ';
        }

        $querySelectTaggedDirs = mysql_query($selectTaggedDirs);
        if (!$querySelectTaggedDirs) {
            echo __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectTaggedDirs . '<br />';
            return FALSE;
        }

        while ($l = mysql_fetch_array($querySelectTaggedDirs, MYSQL_ASSOC)) {
            $taggedDirs[$l['cat_id']] = $l['cat_id'];
        }
        mysql_free_result($querySelectTaggedDirs);

        if (!isset($taggedDirs[$id])) {
            return FALSE;
        }

        return TRUE;
    }

    /**
     * CHECK the valid parent IDs of the &tag parameter
     * @param string $dirOrFile dir|file
     * @param string $tag from &tag parameter
     * @param int    $id  id of the specified dir/file
     * @return bool TRUE | FALSE
     */
    private function _checkTaggedFileIds($tag, $id) {
        $modx = $this->modx;
        $tag = strtolower($tag);



        $selectTaggedFiles = 'SELECT id FROM ' . $modx->db->config['table_prefix'] . 'easy2_files ';

        $xpldFileTags = @explode(',', $tag);
        $countFileTags = count($xpldFileTags);
        for ($i = 0; $i < $countFileTags; $i++) {
            if ($i === 0)
                $selectTaggedFiles .= 'WHERE LOWER(tag) LIKE \'%' . $xpldFileTags[$i] . '%\' ';
            else
                $selectTaggedFiles .= 'OR LOWER(tag) LIKE \'%' . $xpldFileTags[$i] . '%\' ';
        }

        $excludeFileWebAccess = $this->_excludeWebAccess('file');

        if ($excludeFileWebAccess !== FALSE) {
            $selectFiles .= ' AND id NOT IN (' . $excludeFileWebAccess . ') ';
        }

        $querySelectTaggedFiles = mysql_query($selectTaggedFiles);
        if (!$querySelectTaggedFiles) {
            echo __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectTaggedFiles . '<br />';
            return FALSE;
        }
        while ($l = mysql_fetch_array($querySelectTaggedFiles, MYSQL_ASSOC)) {
            $taggedFiles[$l['id']] = $l['id'];
        }
        mysql_free_result($querySelectTaggedFiles);

        if (!isset($taggedFiles[$id])) {
            return FALSE;
        }

        return TRUE;
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
        $theme = $this->e2gSnipCfg['recaptcha_theme'];
        $themeCustom = $this->e2gSnipCfg['recaptcha_theme_custom'];

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
                ' . ($theme == 'custom' ? ',custom_theme_widget: \'' . $themeCustom . '\'' : '') . '};
            </script>
            <script type="text/javascript" src="' . $server . '/challenge?k=' . $pubkey . $errorpart . '"></script>
            <noscript>
                <iframe src="' . $server . '/noscript?k=' . $pubkey . $errorpart . '" height="300" width="500" frameborder="0"></iframe><br/>
                <textarea name="recaptcha_challenge_field" rows="3" cols="40"></textarea>
                <input type="hidden" name="recaptcha_response_field" value="manual_challenge"/>
            </noscript>';
    }

    /**
     *
     * @param string $direction     prev/up/next
     * @param string $staticKey     static identifier's value
     * @param string $dynamicKey    dynamic identifier's value, from the $_GET value
     * @param string $orderBy       image order
     * @param string $order         ASC | DESC
     * @return mixed The text and link of the navigator direction
     */
    private function _navPrevUpNext($direction, $staticKey, $dynamicKey, $catOrderBy, $catOrder) {
        $modx = $this->modx;
        $gid = $this->e2gSnipCfg['gid'];
        $staticGid = $this->e2gSnipCfg['static_gid'];
        $tag = $this->e2gSnipCfg['tag'];
        $staticTag = $this->e2gSnipCfg['static_tag'];
        $e2gStaticInstances = $this->e2gSnipCfg['e2g_static_instances'];

        // if the gallery is the parent ID of the snippet call, disable the up navigation
        if ($staticGid == $gid) {
            return FALSE;
        } else {
            $prevUpNext = array();

            if ($direction == 'prev') {
                if (isset($staticTag)) {
                    $sibling = $this->_getSiblingInfo('tag', $dynamicKey, 'cat_name', $catOrderBy, $catOrder, -1);
                } else {
                    $sibling = $this->_getSiblingInfo(NULL, $dynamicKey, 'cat_name', $catOrderBy, $catOrder, -1);
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
                    $sibling = $this->_getSiblingInfo('tag', $dynamicKey, 'cat_name', $catOrderBy, $catOrder, 1);
                } else {
                    $sibling = $this->_getSiblingInfo(NULL, $dynamicKey, 'cat_name', $catOrderBy, $catOrder, 1);
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

    /**
     * Get parent directory information
     * @param string    $trigger    catch the 'tag' trigget
     * @param int       $dynamicId  changing ID from $_GET variable
     * @param string    $field      database field
     * @return string   parent's info on TRUE return, or EMPTY on FALSE
     */
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
            echo __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectParent . '<br />';
            return FALSE;
        }
        while ($row = mysql_fetch_array($queryParent)) {
            $parent['cat_id'] = $row['parent_id'];
        }

        if (empty($parent['cat_id'])) {
            return FALSE;
        }
        $parent['cat_name'] = $this->_getDirInfo($parent['cat_id'], 'cat_name');

        return $parent;
    }

    /**
     * Get information about sibling directory
     * @param string    $trigger        catch the 'tag' parameter
     * @param int       $dynamicId      changing ID from $_GET variable
     * @param string    $field          database field
     * @param string    $catOrderBy    directory's ordering
     * @param string    $catOrder      directory's ordering orientation
     * @param int       $siblingCounter sibling counter
     * @return string   Sibling's info on TRUE return, or EMPTY on FALSE
     */
    private function _getSiblingInfo($trigger, $dynamicId, $field, $catOrderBy, $catOrder, $siblingCounter) {
        $modx = $this->modx;
        $gid = $this->e2gSnipCfg['gid'];

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

        $selectChildren .= 'ORDER BY a.' . $catOrderBy . ' ' . $catOrder;

        $queryChildren = mysql_query($selectChildren);
        if (!$queryChildren) {
            echo __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectChildren . '<br />';
            return FALSE;
        }

        while ($row = mysql_fetch_array($queryChildren)) {
            $siblings['cat_id'][] = $row['cat_id'];
            $siblings['cat_tag'][] = $row['cat_tag'];
            $siblings[$field][] = $row[$field];
        }

        $countSiblings = count($siblings['cat_id']);

        if ($countSiblings <= 1) {
            return FALSE;
        }
        $thesibling = array();
        for ($i = 0; $i <= $countSiblings; $i++) {
            $j = intval($i + $siblingCounter);
            if ($j < 0) {
                continue;
            }
            if ($trigger == 'tag') {
                if ($siblings['cat_id'][$i] == $gid) {
                    $thesibling['cat_id'] = $siblings['cat_id'][$j];
                    $thesibling['cat_tag'] = $siblings['cat_tag'][$j];
                    $thesibling['cat_name'] = $siblings['cat_name'][$j];
                }
            } else {
                if ($siblings['cat_id'][$i] == $dynamicId) {
                    $thesibling['cat_id'] = $siblings['cat_id'][$j];
                    $thesibling['cat_name'] = $siblings['cat_name'][$j];
                }
            }
        }
        if (!empty($thesibling['cat_id']) || !empty($thesibling['cat_tag'])) {
            return $thesibling;
        }
    }

    /**
     * fetching the &where_* parameters, and attach this into the query
     * @param string $whereParams  the parameter
     * @param string $prefix        the table prefix on joins
     * @return mixed FALSE | the where clause array
     */
    private function _whereClause($whereParams = NULL, $prefix = NULL) {
        if (empty($whereParams)) {
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

            $xpldOrs = @explode(' OR ', $whereClause);
            $countXpldOrs = count($xpldOrs);
            $whereClauseTemp = '';
            for ($i = 0; $i < $countXpldOrs; $i++) {
                // the first loop has been prefixed from above loop
                $whereClauseTemp .= ( $i === 0 ? '' : $prefix . '.') . trim($xpldOrs[$i]) . ' ';
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
        $validOperators = array(
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
        if (!array_key_exists($operator, $validOperators))
            return FALSE;

        return $validOperators[$operator];
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

    /**
     * Strips HTML tags
     * @param <type> $string
     * @param <type> $stripped
     */
    private function _stripHTMLTags($string, $strippedTags=array('p', 'div', 'span')) {
        foreach ($strippedTags as $tag) {
            $string = preg_replace('~\<(.*?)' . $tag . '(.*?)\>~', '', $string);
        }
        return $string;
    }

    /**
     * Check if the given IP is ignored
     * @param string    $ip     IP Address
     * @return bool     TRUE if it is ignored | FALSE if it is not.
     */
    private function _checkIgnoredIp($ip) {
        $modx = $this->modx;

        $selectCountIgnIps = 'SELECT COUNT(ign_ip_address) '
                . 'FROM ' . $modx->db->config['table_prefix'] . 'easy2_ignoredip '
                . 'WHERE ign_ip_address=\'' . $ip . '\'';
        $querySelectCountIgnIp = mysql_query($selectCountIgnIps);
        if (!$querySelectCountIgnIp) {
            echo __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectCountIgnIps . '<br />';
            return FALSE;
        }
//        while ($comRow = mysql_fetch_array($querySelectCountIgnIp)) {
//            $countIgnoredIp = $comRow['COUNT(ign_ip_address)'];
//        }
        $resultCountIgnIps = mysql_result($querySelectCountIgnIp, 0, 0);
        mysql_free_result($querySelectCountIgnIp);

        if ($resultCountIgnIps > 0) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Crop text by length
     * @param   string  $charSet    character set
     * @param   int     $nameLen    text's length
     * @param   string  $text       text to be cropped
     * @return  string  shorthened text
     */
    private function _cropName($mbstring, $charSet, $nameLen, $text) {
        return parent::cropName($mbstring, $charSet, $nameLen, $text);
    }

    /**
     * Centralized the SQL statements for directories fetching
     * @param string    $select     SELECT statement
     * @param string    $prefix     field's prefix
     * @return string   The complete SQL's statement with additional parameters
     */
    private function _dirSqlStatements($select, $prefix = NULL) {
        $modx = $this->modx;
        $tag = $this->e2gSnipCfg['tag'];
        $staticTag = $this->e2gSnipCfg['static_tag'];
        $gid = $this->e2gSnipCfg['gid'];
        $staticGid = $this->e2gSnipCfg['static_gid'];
        $whereDir = $this->e2gSnipCfg['where_dir'];
        $excludeDirWebAccess = $this->_excludeWebAccess('dir');

        $prefixDot = '';
        if (isset($prefix))
            $prefixDot = $prefix . '.';

        if (isset($staticTag)) {
            $dirSqlStatements = 'SELECT ' . $select . ' FROM ' . $modx->db->config['table_prefix'] . 'easy2_dirs WHERE ';

            // OPEN the selected tagged folder
            if (isset($_GET['gid'])
                    && $staticTag == $tag
                    && $this->_checkTaggedDirIds($tag, $_GET['gid'])) {
                $dirSqlStatements .= 'parent_id IN (' . $_GET['gid'] . ') AND ';
            } else {
                // the selected tag of multiple tags on the same page
                if ($staticTag == $tag) {
                    $multipleTags = @explode(',', $tag);
                }
                // the UNselected tag of multiple tags on the same page
                else {
                    $multipleTags = @explode(',', $staticTag);
                }

                $countMultipleTags = count($multipleTags);
                for ($i = 0; $i < $countMultipleTags; $i++) {
                    if ($i === 0)
                        $dirSqlStatements .= 'cat_tag LIKE \'%' . $multipleTags[$i] . '%\' ';
                    else
                        $dirSqlStatements .= 'OR cat_tag LIKE \'%' . $multipleTags[$i] . '%\' ';
                }
                $dirSqlStatements .= 'AND ';
            }

            if ($excludeDirWebAccess !== FALSE) {
                $dirSqlStatements .= 'cat_id NOT IN (' . $excludeDirWebAccess . ') AND ';
            }

            $dirSqlStatements .= 'cat_visible=1 ';
        }
        // original &gid parameter
        else {
            $dirSqlStatements = 'SELECT ' . $select . ' FROM ' . $modx->db->config['table_prefix'] . 'easy2_dirs AS ' . $prefix . ' WHERE ';

            if ($gid != '*') {
                if ($this->_checkGidDecendant((isset($_GET['gid']) ? $_GET['gid'] : $gid), $staticGid) == TRUE) {
                    $dirSqlStatements .= $prefixDot . 'parent_id IN (' . $gid . ') ';
                } else {
                    $dirSqlStatements .= $prefixDot . 'parent_id IN (' . $staticGid . ') ';
                }
                $dirSqlStatements .= 'AND ';
            }

            if (isset($whereDir)) {
                $where = $this->_whereClause($whereDir, $prefix);
                if (!$where) {
                    return FALSE;
                }
                $dirSqlStatements .= $where . ' AND ';
            }

            if ($excludeDirWebAccess !== FALSE) {
                $dirSqlStatements .= $prefixDot . 'cat_id NOT IN (' . $excludeDirWebAccess . ') AND ';
            }

            // ddim -- wrapping children folders
            $dirSqlStatements .=
                    '(SELECT count(*) FROM ' . $modx->db->config['table_prefix'] . 'easy2_files F WHERE F.dir_id IN '
                    . '('
                    . 'SELECT A.cat_id FROM ' . $modx->db->config['table_prefix'] . 'easy2_dirs A, '
                    . $modx->db->config['table_prefix'] . 'easy2_dirs B WHERE '
                    . '('
                    . 'B.cat_id = ' . $prefixDot . 'cat_id '
                    . 'AND A.cat_left >= B.cat_left '
                    . 'AND A.cat_right <= B.cat_right '
                    . 'AND A.cat_level >= B.cat_level '
                    . 'AND A.cat_visible = 1'
                    . ')'
                    . ')'
                    . ')<>0 AND ';
            $dirSqlStatements .= $prefixDot . 'cat_visible=1 ';
        }

        return $dirSqlStatements;
    }

    /**
     * Centralized the SQL statements for files fetching
     * @param string    $select     SELECT statement
     * @return string   The complete SQL's statement with additional parameters
     */
    private function _fileSqlStatements($select) {
        $modx = $this->modx;
        $tag = $this->e2gSnipCfg['tag'];
        $staticTag = $this->e2gSnipCfg['static_tag'];
        $gid = $this->e2gSnipCfg['gid'];
        $staticGid = $this->e2gSnipCfg['static_gid'];
        $whereFile = $this->e2gSnipCfg['where_file'];
        $excludeDirWebAccess = $this->_excludeWebAccess('dir');
        $excludeFileWebAccess = $this->_excludeWebAccess('file');

        $fileSqlStatements = 'SELECT ' . $select . ' FROM ' . $modx->db->config['table_prefix'] . 'easy2_files WHERE ';

        if (isset($staticTag)) {
            // OPEN the selected tagged folder
            if (isset($_GET['gid'])
                    && $staticTag == $tag
                    && $this->_checkTaggedDirIds($tag, $_GET['gid'])
            ) {
                $fileSqlStatements .= 'dir_id IN (' . $_GET['gid'] . ') AND ';
            } else {
                // the selected tag of multiple tags on the same page
                if ($staticTag == $tag) {
                    $multipleTags = @explode(',', $tag);
                }
                // the UNselected tag of multiple tags on the same page
                else {
                    $multipleTags = @explode(',', $staticTag);
                }
                $countMultipleTags = count($multipleTags);
                for ($i = 0; $i < $countMultipleTags; $i++) {
                    if ($i === 0)
                        $fileSqlStatements .= 'tag LIKE \'%' . $multipleTags[$i] . '%\' ';
                    else
                        $fileSqlStatements .= 'OR tag LIKE \'%' . $multipleTags[$i] . '%\' ';
                }
                $fileSqlStatements .= 'AND ';
            }
        }
        // exclude &tag snippet call, for the &gid parameter
        else {
            if ($gid != '*') {
                if ($this->_checkGidDecendant((isset($_GET['gid']) ? $_GET['gid'] : $gid), $staticGid) == TRUE) {
                    $fileSqlStatements .= 'dir_id IN (' . $gid . ') ';
                } else {
                    $fileSqlStatements .= 'dir_id IN (' . $staticGid . ') ';
                }
                $fileSqlStatements .= 'AND ';
            }

            if (isset($whereFile)) {
                $where = $this->_whereClause($whereFile);
                if (!$where) {
                    return FALSE;
                }
                $fileSqlStatements .= $where . ' AND ';
            }
        }

        if ($excludeDirWebAccess !== FALSE) {
            $dirSqlStatements .= 'dir_id NOT IN (' . $excludeDirWebAccess . ') AND ';
        }

        if ($excludeFileWebAccess !== FALSE) {
            $fileSqlStatements .= 'id NOT IN (' . $excludeFileWebAccess . ') AND ';
        }

        $fileSqlStatements .= 'status = 1 ';

        return $fileSqlStatements;
    }

    /**
     * Formating the pagination
     * @param mixed     $pages  The variable of page contents, number, and links.
     * @return string   The formatted pagination
     */
    private function _paginationFormat($pages) {
        $adjacents = $this->e2gSnipCfg['pagination_adjacents'];
        $middleSpread = $this->e2gSnipCfg['pagination_spread'];
        $textPrevious = $this->e2gSnipCfg['pagination_text_previous'];
        $textNext = $this->e2gSnipCfg['pagination_text_next'];
        $splitter = $this->e2gSnipCfg['pagination_splitter'];

        $pagination = '';
        if ($pages['totalPageNum'] > 1) {
            //previous button
            if ($pages['currentPage'] > 1) {
                $pagination.= '<a href="' . $pages['previousLink'][$pages['currentPage']] . '">' . $textPrevious . '</a>';
            } else {
                $pagination.= '<span class="disabled">' . $textPrevious . '</span>';
            }

            // no split
            if ($pages['totalPageNum'] <= ($middleSpread + ($adjacents * 2))) {
                for ($i = 1; $i <= $pages['totalPageNum']; $i++) {
                    if ($i == $pages['currentPage'])
                        $pagination.= '<b>' . $i . '</b>';
                    else
                        $pagination.= $pages['pages'][$i];
                }
            } else {
                // start splitting
                if ($pages['currentPage'] < ($adjacents + floor($middleSpread/2) + 1)) {
                    for ($i = 1; $i < ($adjacents + $middleSpread + 1); $i++) {
                        if ($i == $pages['currentPage']) {
                            $pagination.= '<b>' . $i . '</b>';
                        } else {
                            $pagination.= $pages['pages'][$i];
                        }
                    }
                    $pagination.= $splitter;
                    // the last pages
                    for ($i = ($pages['totalPageNum'] - $adjacents + 1); $i <= $pages['totalPageNum']; $i++) {
                        $pagination.= $pages['pages'][$i];
                    }
                } elseif ( $pages['currentPage'] >= ($adjacents + floor($middleSpread/2) + 1) // front
                        && $pages['currentPage'] < ($pages['totalPageNum'] - ($adjacents + ceil($middleSpread/2) - 1)) // end
                        ) {
                    $pagination.= $pages['pages'][1];
                    $pagination.= $pages['pages'][2];
                    $pagination.= $splitter;
                    for ($i = ($pages['currentPage'] - floor($middleSpread/2)); $i <= $pages['currentPage']  + floor($middleSpread/2); $i++) {
                        if ($i == $pages['currentPage']) {
                            $pagination.= '<b>' . $i . '</b>';
                        } else {
                            $pagination.= $pages['pages'][$i];
                        }
                    }
                    $pagination.= $splitter;
                    // the last pages
                    for ($i = ($pages['totalPageNum'] - $adjacents + 1); $i <= $pages['totalPageNum']; $i++) {
                        $pagination.= $pages['pages'][$i];
                    }
                } else {
                    $pagination.= $pages['pages'][1];
                    $pagination.= $pages['pages'][2];
                    $pagination.= $splitter;
                    
                    for ($i = $pages['totalPageNum'] - ($adjacents + $middleSpread-1); $i <= $pages['totalPageNum']; $i++) {
                        if ($i == $pages['currentPage']) {
                            $pagination.= '<b>' . $i . '</b>';
                        } else {
                            $pagination.= $pages['pages'][$i];
                        }
                    }
                }
            }

            //next button
            if ($pages['currentPage'] < $pages['totalPageNum']) {
                $pagination.= '<a href="' . $pages['nextLink'][$pages['currentPage']] . '">' . $textNext . '</a>';
            } else {
                $pagination.= '<span class="disabled">' . $textNext . '</span>';
            }
        }
        return $pagination;
    }

}