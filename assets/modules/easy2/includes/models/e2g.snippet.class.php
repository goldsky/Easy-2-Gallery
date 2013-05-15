<?php

/**
 * EASY 2 GALLERY
 * Gallery Snippet Class for Easy 2 Gallery Module for MODx Evolution
 * @author Cx2 <inteldesign@mail.ru>
 * @author Temus <temus3@gmail.com>
 * @author goldsky <goldsky@fastmail.fm>
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
     * Total directories number to be used for thumbnail's pagination
     * @var int     total number
     */
    private $_countAllDirs = 0;

    /**
     * Directories number for the current query to be used for the file's limit
     * @var int     directories number
     */
    private $_dirNumRows = 0;

    /**
     * Total thumbnails number to be used for thumbnail's pagination
     * @var int     total number
     */
    private $_totalCount = 0;

    /**
     * @var array   $_GET variables
     */
    private $_sanitizedGets = array();

    public function __construct($modx, $e2gSnipCfg) {
        parent::__construct($modx, $e2gSnipCfg);
        $this->modx = & $modx;
        $this->e2gSnipCfg = $e2gSnipCfg;

        $this->_fixMultiCallPagination();

        if ($this->e2gSnipCfg['orderby'] === 'random') {
            $this->e2gSnipCfg['orderby'] = 'rand()';
            $this->e2gSnipCfg['order'] = '';
        }
        if ($this->e2gSnipCfg['cat_orderby'] === 'random') {
            $this->e2gSnipCfg['cat_orderby'] = 'rand()';
            $this->e2gSnipCfg['cat_order'] = '';
        }

        // clean up the $_GET variables
        // @todo: sanitize $_GET['tag'] with non-ASCII characters
        foreach ($_GET as $k => $v) {
            $this->_sanitizedGets[$k] = $this->sanitizedString($v);
        }
    }

    /**
     * Helper function to fix variables because of the multiple snippet calls
     * @return void
     */
    private function _fixMultiCallPagination() {
        //**********************************************************************/
        //*   PAGINATION FIXING for multiple snippet calls on the same page    */
        //**********************************************************************/
        // for the UNselected &gid snippet call when the other &gid snippet call is selected
        if (isset($this->e2gSnipCfg['static_gid'])
                && isset($this->_sanitizedGets['gid'])
                && !$this->_checkGidDescendant($this->_sanitizedGets['gid'], $this->e2gSnipCfg['static_gid'])
                || $this->e2gSnipCfg['e2g_instances'] != $this->e2gSnipCfg['e2g_static_instances']
        ) {
            $this->e2gSnipCfg['gpn'] = 0;
        }
        // for the UNselected &gid snippet call when &tag snippet call is selected
        if (isset($this->e2gSnipCfg['static_gid'])
                && !isset($this->e2gSnipCfg['static_tag'])
                && isset($_GET['tag'])
                || $this->e2gSnipCfg['e2g_instances'] != $this->e2gSnipCfg['e2g_static_instances']
        ) {
            $this->e2gSnipCfg['gpn'] = 0;
        }

        // for the UNselected &tag snippet call when &gid snippet call is selected
        if (isset($this->e2gSnipCfg['static_tag'])
                && !isset($_GET['tag'])
                && isset($this->_sanitizedGets['gid'])
                || $this->e2gSnipCfg['e2g_instances'] != $this->e2gSnipCfg['e2g_static_instances']
        ) {
            $this->e2gSnipCfg['gpn'] = 0;
        }
        // for the UNselected &tag snippet call when the other &tag snippet call is selected
        if (isset($this->e2gSnipCfg['static_tag'])
                && $this->e2gSnipCfg['tag'] != $this->e2gSnipCfg['static_tag']
                || $this->e2gSnipCfg['e2g_instances'] != $this->e2gSnipCfg['e2g_static_instances']
        ) {
            $this->e2gSnipCfg['gpn'] = 0;
        }

        // FREEZING using plugin
        if ($this->e2gSnipCfg['e2g_instances'] != $this->e2gSnipCfg['e2g_static_instances']) {
            $this->e2gSnipCfg['gid'] = $this->e2gSnipCfg['static_gid'];
            $this->e2gSnipCfg['tag'] = $this->e2gSnipCfg['static_tag'];
            $this->e2gSnipCfg['gpn'] = 0;
        }

        return;
    }

    /**
     * Thumbnail gallery
     * @return  string  complete rendered gallery
     */
    public function thumbsGallery() {
        // Execute the Javascript library's headers
        $jsLibs = $this->_loadHeaders();
        if ($jsLibs === FALSE) {
            return FALSE;
        }

        /**
         * Clearing the internal parameter.
         * This is NOT the $e2g config.
         */
        $phs = array(
            'sid' => '',
            'wrapper' => '',
            'permalink' => '',
            'content' => '',
            'cat_name' => '',
            'parent_id' => 0,
            'back_tpl' => '',
            'description_tpl' => '',
            'crumbs_tpl' => '',
            'prevupnext_tpl' => '',
            'pagination_tpl' => '',
        );

        // session ID
        $phs['sid'] = $this->e2gSnipCfg['e2g_static_instances'];
        // Gallery's wrapper ID
        $phs['wrapper'] = $this->e2gSnipCfg['e2g_wrapper'];
        $phs['permalink'] = $this->_permalink();
        $phs['content'] = $this->_thumbsContent();
        $parentInfo = $this->_getParentInfo($this->e2gSnipCfg['gid']);
        $phs['cat_name'] = $parentInfo['cat_name'];
        $phs['parent_id'] = $parentInfo['cat_id'];
        $phs['back_tpl'] = $this->_back();
        $phs['description_tpl'] = $this->_description();
        $phs['crumbs_tpl'] = $this->_breadcrumbs();
        $phs['prevUpNext_tpl'] = $this->_prevUpNext();
        $phs['pagination_tpl'] = $this->_thumbsPagination();

        /**
         * invoke plugin for the MAIN gallery
         */
        $phs['gallerypluginprerender'] = $this->_plugin('OnE2GWebGalleryPrerender', array(
            'pages' => $phs['pages']
            , 'parent_id' => $phs['parent_id']
            , 'desc_class' => $phs['desc_class']
            , 'cat_name' => $phs['cat_name']
            , 'permalink' => $phs['permalink']
            , 'wrapper' => $phs['wrapper']
            , 'sid' => $phs['sid']
                ));
        $phs['gallerypluginrender'] = $this->_plugin('OnE2GWebGalleryRender', array(
            'pages' => $phs['pages']
            , 'parent_id' => $phs['parent_id']
            , 'desc_class' => $phs['desc_class']
            , 'cat_name' => $phs['cat_name']
            , 'permalink' => $phs['permalink']
            , 'wrapper' => $phs['wrapper']
            , 'sid' => $phs['sid']
                ));

        return $this->filler($this->getTpl('tpl'), $phs);
    }

    /**
     * Thumbnail contents
     * @return string   formated thumbnails
     */
    private function _thumbsContent() {
        $this->_countAllDirs = $this->_countAllDirs();
        $dirThumbs = $this->_dirThumbs();
        $dirThumbs = !empty($dirThumbs) ? $dirThumbs : array();
        $imageThumbs = $this->_imageThumbs();
        $imageThumbs = !empty($imageThumbs) ? $imageThumbs : array();
        $galThumbs = array_merge($dirThumbs, $imageThumbs);

        $content = '';
        if (strtolower($this->e2gSnipCfg['grid']) === 'table') {
            $countThumbs = count($galThumbs);
            $i = 0;
            $content .= '<table class="' . $this->e2gSnipCfg['grid_class'] . '">';
            foreach ($galThumbs as $v) {
                if (0 === $i % $this->e2gSnipCfg['colls']) {
                    $content .= '<tr>';
                }
                $content .= '<td>' . $v . '</td>';
                if ((0 === ($i + 1 + $this->e2gSnipCfg['colls']) % $this->e2gSnipCfg['colls'])
                        || ($i + 1 === $countThumbs)
                ) {
                    $content .= '</tr>';
                }
                $i++;
            }
            $content .= '</table>';
        } elseif (strtolower($this->e2gSnipCfg['grid']) === 'div') {
            $content .= '<div class="' . $this->e2gSnipCfg['grid_class'] . '">';
            foreach ($galThumbs as $v) {
                $content .= $v;
            }
            $content .= '</div>';
        } elseif (strtolower($this->e2gSnipCfg['grid']) === 'nowrapper') {
            foreach ($galThumbs as $v) {
                $content .= $v;
            }
        }

        return $content;
    }

    /**
     * Returns the folder's description if it is set and enabled
     * @return  string  Gallery's description
     */
    private function _description() {
        if ($this->e2gSnipCfg['gal_desc'] !== '1') {
            return '';
        }

        $phs = array();
        $phs['cat_description'] = '';
        $phs['title'] = '';
        if ($this->e2gSnipCfg['gal_desc'] === '1'
                && $this->e2gSnipCfg['gal_desc_continuous'] === '0'
                && (int) $this->e2gSnipCfg['gpn'] > 0
        ) {
            $this->e2gSnipCfg['gal_desc'] = '0';
        }
        if ($this->e2gSnipCfg['gal_desc'] === '1'
                // exclude the multiple gids (comma separated)
                && !strstr($this->e2gSnipCfg['static_gid'], ',')
        ) {
            $galleryId = '';
            if (!$this->_checkGidDescendant(
                            (isset($this->_sanitizedGets['gid']) ? $this->_sanitizedGets['gid'] : $this->e2gSnipCfg['gid'])
                            , $this->e2gSnipCfg['static_gid'])
            ) {
                $galleryId = $this->e2gSnipCfg['static_gid'];
            } else {
                $galleryId = $this->e2gSnipCfg['gid'];
            }

            $phs['cat_description'] = htmlspecialchars_decode($this->getDirInfo($galleryId, 'cat_description'), ENT_QUOTES);
            $phs['cat_title'] = htmlspecialchars_decode($this->getDirInfo($galleryId, 'cat_alias'), ENT_QUOTES);
            $phs['title'] = $phs['cat_title'] != '' ? $phs['cat_title'] : htmlspecialchars_decode($phs['cat_name'], ENT_QUOTES);
        }
        if (empty($phs['title']) && empty($phs['cat_description'])) {
            return '';
        }

        return $this->filler($this->getTpl('desc_tpl'), $phs);
    }

    private function _permalink() {
        if (isset($this->e2gSnipCfg['tag'])
                && ($this->_checkGidDescendant((isset($this->_sanitizedGets['gid']) ? $this->_sanitizedGets['gid'] : $this->e2gSnipCfg['gid']), $this->e2gSnipCfg['static_gid']) === TRUE)
        ) {
            $permalinkName = $this->e2gSnipCfg['e2g_static_instances'] . '_' . $this->e2gSnipCfg['static_tag'];
        } elseif (!$this->_checkGidDescendant((isset($this->_sanitizedGets['gid']) ? $this->_sanitizedGets['gid'] : $this->e2gSnipCfg['gid']), $this->e2gSnipCfg['static_gid'])) {
            $permalinkName = $this->e2gSnipCfg['e2g_static_instances'] . '_' . $this->e2gSnipCfg['static_gid'];
        } else {
            $permalinkName = $this->e2gSnipCfg['e2g_static_instances'] . '_' . $this->e2gSnipCfg['gid'];
        }
        $permalinkName = str_replace(',', '', $permalinkName);
        return '<a href="#" name="' . $this->sanitizedString($permalinkName) . '"></a>';
    }

    /**
     * BACK BUTTON
     * @return  string  parsed back_tpl
     */
    private function _back() {
        $parentInfo = $this->_getParentInfo($this->e2gSnipCfg['gid']);
        $phs = '';
        if ($parentInfo['cat_id'] > 0
                && $this->_checkGidDescendant((isset($this->_sanitizedGets['gid']) ? $this->_sanitizedGets['gid'] : $this->e2gSnipCfg['gid']), $this->e2gSnipCfg['static_gid']) === TRUE
                && (!empty($this->e2gSnipCfg['static_tag']) ? $this->_checkTaggedFileIds($this->e2gSnipCfg['static_tag'], $parentInfo['cat_id']) === TRUE : NULL)
        ) {
            $phs = '&laquo; <a href="'
                    // making flexible FURL or not
                    . $this->modx->makeUrl($this->modx->documentIdentifier
                            , $this->modx->aliases
                            , 'sid=' . $this->e2gSnipCfg['e2g_static_instances'])
                    . '&amp;gid=' . $parentInfo['cat_id']
                    . (isset($this->e2gSnipCfg['static_tag']) ? '&amp;tag=' . $this->e2gSnipCfg['static_tag'] : '' )
                    . '#' . $this->e2gSnipCfg['e2g_static_instances'] . '_'
                    . (isset($this->e2gSnipCfg['static_tag']) ? $this->e2gSnipCfg['static_tag'] : $parentInfo['cat_id'] )
                    . '">' . $parentInfo['cat_name'] . '</a>';
        }
        if (!empty($phs))
            return $this->filler($this->getTpl('back_tpl'), $phs);
        else
            return '';
    }

    /**
     * PAGINATION: PAGE LINKS
     * joining between dirs and files paginations
     * @return  string  formated pagination
     */
    private function _thumbsPagination() {

        if ($this->e2gSnipCfg['pagination'] !== '1'
                || $this->e2gSnipCfg['orderby'] === 'rand()'
                || $this->e2gSnipCfg['cat_orderby'] === 'rand()'
        ) {
            return '';
        }

        // count the files again, this time WITHOUT limit!
        if ($this->e2gSnipCfg['showonly'] === 'folders') {
            $resultCountFiles = 0;
        } elseif (!empty($this->e2gSnipCfg['gid'])) {
            $selectCountFiles = $this->_fileSqlStatement('COUNT(id)');
            $querySelectCountFiles = mysql_query($selectCountFiles);
            if (!$querySelectCountFiles) {
                echo __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectCountFiles . '<br />';
                return FALSE;
            }
            $resultCountFiles = mysql_result($querySelectCountFiles, 0, 0);
            mysql_free_result($querySelectCountFiles);
        }

        $this->_totalCount = $this->_countAllDirs + $resultCountFiles;

        // Terminate all the outputs, when the result is empty.
        if ($this->_totalCount === 0)
            return FALSE;

        $phs = array();
        if ($this->_totalCount <= $this->e2gSnipCfg['limit']) {
            $phs['pages'] = '';
        }
        if ($this->_totalCount > $this->e2gSnipCfg['limit']) {
            $phs['pages'] = $this->_paginationNumbers();
        }

        if (!empty($phs))
            return $this->filler($this->getTpl('pagination_tpl'), $phs);
        else
            return '';
    }

    /**
     * File thumbnails
     * @return string   formated thumbnails
     */
    private function _imageThumbs() {
        if ($this->_dirNumRows === intval($this->e2gSnipCfg['limit'])
                || strtolower($this->e2gSnipCfg['showonly']) === 'folders'
                || empty($this->e2gSnipCfg['gid'])
        ) {
            return array();
        }

        /**
         * manage the pagination limit between dirs and files
         * (join the pagination AND the table grid).
         */
        $modulusDirCount = $this->_countAllDirs % $this->e2gSnipCfg['limit'];
        $fileThumbOffset = $this->e2gSnipCfg['limit'] - $modulusDirCount;
        $filePageOffset = ceil($this->_countAllDirs / $this->e2gSnipCfg['limit']);

        $selectFiles = $this->_fileSqlStatement('*');
        $selectFiles .= ' ORDER BY ' . $this->e2gSnipCfg['orderby'] . ' ' . $this->e2gSnipCfg['order'] . ' ';
        /**
         * Calculate the available grid to be floated
         */
        if ($fileThumbOffset > 0 && $fileThumbOffset < $this->e2gSnipCfg['limit']) {
            $selectFiles .= 'LIMIT '
                    . ( $this->_dirNumRows > 0 ?
                            ( ' 0, ' . ( $fileThumbOffset ) ) :
                            ( ( ( $this->e2gSnipCfg['gpn'] - $filePageOffset) * $this->e2gSnipCfg['limit']) + $fileThumbOffset ) . ', ' . $this->e2gSnipCfg['limit'] );
        } elseif ($fileThumbOffset !== 0 || $fileThumbOffset === intval($this->e2gSnipCfg['limit'])) {
            $selectFiles .= 'LIMIT '
                    . ( $modulusDirCount > 0 ?
                            ( ' 0, ' . ( $fileThumbOffset ) ) :
                            ( ( ( $this->e2gSnipCfg['gpn'] - $filePageOffset) * $this->e2gSnipCfg['limit']) ) . ', ' . $this->e2gSnipCfg['limit'] );
        } else { // $fileThumbOffset === 0 --> No sub directory
            $selectFiles .= 'LIMIT ' . ( $this->e2gSnipCfg['gpn'] * $this->e2gSnipCfg['limit']) . ', ' . $this->e2gSnipCfg['limit'];
        }

        $querySelectFiles = mysql_query($selectFiles);
        if (!$querySelectFiles) {
            echo __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectFiles . '<br />';
            return FALSE;
        }

        $fileNumRows = mysql_num_rows($querySelectFiles);
        if ($fileNumRows === 0) {
            return;
        }
        $fileThumbs = array();
        while ($l = mysql_fetch_assoc($querySelectFiles)) {
            $thumbPlaceholders = $this->_loadThumbPlaceholders($l);
            if ($thumbPlaceholders === FALSE) {
                continue;
            }

            // whether configuration setting is set with or without table, the template will adjust it
            $fileThumbs[] = $this->filler($this->getTpl('thumb_tpl'), $thumbPlaceholders);
        }
        mysql_free_result($querySelectFiles);

        return $fileThumbs;
    }

    /**
     * Counts the directories
     * @return  int number of directories
     */
    private function _countAllDirs() {
        if (strtolower($this->e2gSnipCfg['showonly']) === 'images') {
            return 0;
        }
        if (isset($this->e2gSnipCfg['static_tag'])) {
            $selectDirCount = $this->_dirSqlStatement('COUNT(DISTINCT cat_id)', 'd');
        } else {
            $selectDirCount = $this->_dirSqlStatement('COUNT(DISTINCT d.cat_id)', 'd');
        }
        $querySelectDirCount = mysql_query($selectDirCount);
        if (!$querySelectDirCount) {
            echo __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectDirCount . '<br />';
            return FALSE;
        }

        $resultCountDirs = mysql_result($querySelectDirCount, 0, 0);
        mysql_free_result($querySelectDirCount);

        return $resultCountDirs;
    }

    /**
     * Breadcrumbs
     * @return  string  formated breadcrumbs
     */
    private function _breadcrumbs() {
        if ($this->e2gSnipCfg['crumbs'] !== '1'
                || (!empty($this->e2gSnipCfg['gid']) && strpos(',', $this->e2gSnipCfg['gid']) !== false) // it must be a single gid
                || isset($this->e2gSnipCfg['static_tag'])
        ) {
            return '';
        }

        $crumbsPathArray = $this->getPath($this->e2gSnipCfg['gid'], $this->e2gSnipCfg['crumbs_use'], 'array');

        // To limit the CRUMBS paths.
        if ($this->e2gSnipCfg['static_gid'] !== 1
                && !empty($crumbsPathArray)
                && !isset($this->e2gSnipCfg['tag'])
        ) {
            $staticPath = $this->getPath($this->e2gSnipCfg['static_gid'], NULL, 'array');
            if (!$this->e2gSnipCfg['crumbs_showPrevious']) {
                $crumbsPathArray = array_slice($crumbsPathArray, (count($staticPath) - 1), NULL, TRUE);
            }
        }

        // reset crumbs
        $breadcrumbs = '';
        // if path more the none
        if (count($crumbsPathArray) > 0) {
            end($crumbsPathArray);
            prev($crumbsPathArray);

            // create crumbs
            $cnt = 0;
            foreach ($crumbsPathArray as $k => $v) {
                $cnt++;
                if ($cnt === 1 && !$this->e2gSnipCfg['crumbs_showHome']) {
                    continue;
                }
                if ($cnt === count($crumbsPathArray) && !$this->e2gSnipCfg['crumbs_showCurrent']) {
                    continue;
                }

                if ($cnt != count($crumbsPathArray))
                    $breadcrumbs .= $this->e2gSnipCfg['crumbs_separator'] . ($this->e2gSnipCfg['crumbs_showAsLinks'] ?
                                    '<a href="'
                                    // making flexible FURL or not
                                    . $this->modx->makeUrl($this->modx->documentIdentifier
                                            , $this->modx->aliases
                                            , 'sid=' . $this->e2gSnipCfg['e2g_static_instances'])
                                    . '&amp;gid=' . $k
                                    . '#' . $this->e2gSnipCfg['e2g_static_instances'] . '_' . $k
                                    . '">' . $v . '</a>' : $v);
                else
                    $breadcrumbs .= $this->e2gSnipCfg['crumbs_separator'] . '<span class="' . $this->e2gSnipCfg['crumbs_classCurrent'] . '">' . $v . '</span>';
            }
            $breadcrumbs = substr_replace($breadcrumbs, '', 0, strlen($this->e2gSnipCfg['crumbs_separator']));

            // unset the value of Easy 2's ROOT gallery ID/name
//            unset($crumbsPathArray[1]);
            // joining many of directory paths
            $crumbsPathArray = implode('/', array_values($crumbsPathArray)) . '/';
        } else { // if not many, path is set as empty
            $crumbsPathArray = '';
        } // if (count($pathArray) > 1)

        return $breadcrumbs;
    }

    /**
     * Directory thumbnails
     * @return string   formated thumbnails
     */
    private function _dirThumbs() {
        if (strtolower($this->e2gSnipCfg['showonly']) === 'images') {
            return array();
        }

        if (isset($this->e2gSnipCfg['static_tag'])) {
            $selectDirs = $this->_dirSqlStatement('*', 'd');
        } else {
            $selectDirs = $this->_dirSqlStatement('d.*', 'd');
        }

        $selectDirs .= ' ORDER BY ' . $this->e2gSnipCfg['cat_orderby'] . ' ' . $this->e2gSnipCfg['cat_order'];
        $selectDirs .= ' LIMIT ' . ( $this->e2gSnipCfg['gpn'] * $this->e2gSnipCfg['limit'] ) . ', ' . $this->e2gSnipCfg['limit'];

        $querySelectDirs = mysql_query($selectDirs);
        if (!$querySelectDirs) {
            echo __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectDirs . '<br />';
            return FALSE;
        }
        $this->_dirNumRows += mysql_num_rows($querySelectDirs);

        //******************************************************************/
        //*       Fill up the current directory's thumbnails content       */
        //******************************************************************/
        $dirThumbs = array();
        while ($l = mysql_fetch_assoc($querySelectDirs)) {
            if (isset($this->e2gSnipCfg['static_tag'])) {
                $l['permalink'] = $this->e2gSnipCfg['e2g_static_instances'] . '_' . $this->e2gSnipCfg['static_tag'];
                $permalink = $this->e2gSnipCfg['e2g_static_instances'] . '_' . $this->e2gSnipCfg['static_tag'];
            } else {
                $l['permalink'] = $this->e2gSnipCfg['e2g_static_instances'] . '_' . $l['cat_id'];
                $permalink = $this->e2gSnipCfg['e2g_static_instances'] . '_' . $l['cat_id'];
            }

            if (isset($this->e2gSnipCfg['tag'])) {
                $l['cat_tag'] = '&amp;tag=' . $this->e2gSnipCfg['static_tag'];
            } else {
                $l['cat_tag'] = '';
            }

            $folderImgInfos = $this->folderImg($l['cat_id'], $this->e2gSnipCfg['gdir']);

            // if there is an empty folder, or invalid content
            if (!$folderImgInfos)
                continue;

            $l['count'] = intval($folderImgInfos['count']);

            // path to subdir's thumbnail
            $getPath = $this->getPath($folderImgInfos['dir_id']);

            $l['w'] = $this->e2gSnipCfg['folder_w'];
            $l['h'] = $this->e2gSnipCfg['folder_h'];
            $thq = $this->e2gSnipCfg['folder_thq'];
            // only for preparation if someone needs the placeholders
            $l['folder_w'] = $this->e2gSnipCfg['folder_w'];
            $l['folder_w'] = $this->e2gSnipCfg['folder_h'];

            $imgShaper = $this->_imgShaper($this->e2gSnipCfg['gdir'], $getPath . $folderImgInfos['filename'], $l['w'], $l['h'], $thq);
            if (!$imgShaper) {
                continue;
            } else {
                $l['src'] = $this->e2gEncode($imgShaper);
            }

            $l['title'] = ( $l['cat_alias'] != '' ? $l['cat_alias'] : $l['cat_name'] );
            $l['title'] = $this->cropName($this->e2gSnipCfg['mbstring'], $this->e2gSnipCfg['charset'], $this->e2gSnipCfg['cat_name_len'], $l['title']);

            if ($this->e2gSnipCfg['use_redirect_link'] === TRUE && !empty($l['cat_redirect_link'])) {
                $l['link'] = $l['cat_redirect_link'];
            } else {
                // making flexible FURL or not
                $l['link'] = $this->modx->makeUrl(
                                $this->modx->documentIdentifier
                                , $this->modx->aliases
                                , 'sid=' . $this->e2gSnipCfg['e2g_static_instances'])
                        . '&amp;gid=' . $l['cat_id']
                        . (isset($this->e2gSnipCfg['static_tag']) ? '&amp;tag=' . $this->e2gSnipCfg['static_tag'] : '')
                        . '#' . $permalink
                ;
            }

            /**
             * invoke plugin for EACH gallery
             */
            // creating the plugin array's content
            $e2gEvtParams = array();
            $l['sid'] = $this->e2gSnipCfg['e2g_static_instances'];
            foreach ($l as $k => $v) {
                $e2gEvtParams[$k] = $v;
            }

            $l['dirpluginprerender'] = $this->_plugin('OnE2GWebDirPrerender', $e2gEvtParams);
            $l['dirpluginrender'] = $this->_plugin('OnE2GWebDirRender', $e2gEvtParams);

            // fill up the dir list with content
            $dirThumbs[] = $this->filler($this->getTpl('dir_tpl'), $l);
        } // while ($l = mysql_fetch_assoc($querySelectDirs))
        mysql_free_result($querySelectDirs);

        return $dirThumbs;
    }

    /**
     * Pagination numbers
     * @return string   formated pagination numbers
     */
    private function _paginationNumbers() {
        $pages = array();
        $pages['totalCount'] = $this->_totalCount;
        $pages['totalPageNum'] = ceil($this->_totalCount / $this->e2gSnipCfg['limit']);
        $indexPage = $this->modx->makeUrl($this->modx->documentIdentifier, $this->modx->aliases, 'sid=' . $this->e2gSnipCfg['e2g_static_instances']);
        $i = 0;
        while ($i * $this->e2gSnipCfg['limit'] < $this->_totalCount) {

            // using &tag parameter
            if (isset($this->e2gSnipCfg['static_tag'])) {
                $permalinkName = $this->e2gSnipCfg['e2g_static_instances'] . '_' . $this->e2gSnipCfg['static_tag'];
                $permalinkName = $this->sanitizedString($this->e2gSnipCfg['e2g_static_instances'] . '_' . $this->e2gSnipCfg['static_tag']);
                $permalinkName = str_replace(',', '', $permalinkName);
                // making flexible FURL or not
                $pagesLink = $indexPage . '&amp;tag=' . $this->e2gSnipCfg['static_tag']
                        . ( isset($this->_sanitizedGets['gid']) ? '&amp;gid=' . $this->_sanitizedGets['gid'] : '' )
                        . '&amp;gpn=' . $i . $this->e2gSnipCfg['customgetparams'] . '#' . $permalinkName;
            }
            // original &gid parameter
            else {
                $permalinkName = $this->e2gSnipCfg['e2g_static_instances'] . '_' . ( isset($this->e2gSnipCfg['static_gid'])
                        && ( $this->_checkGidDescendant((isset($this->_sanitizedGets['gid']) ? $this->_sanitizedGets['gid'] : $this->e2gSnipCfg['gid']), $this->e2gSnipCfg['static_gid']) === TRUE ) ?
                                $this->e2gSnipCfg['gid'] : $this->e2gSnipCfg['static_gid'] );
                $permalinkName = $this->sanitizedString($permalinkName);
                $permalinkName = str_replace(',', '', $permalinkName);
                // making flexible FURL or not
                $pagesLink = $indexPage . ( isset($this->e2gSnipCfg['static_gid'])
                        && ( $this->_checkGidDescendant((isset($this->_sanitizedGets['gid']) ? $this->_sanitizedGets['gid'] : $this->e2gSnipCfg['gid']), $this->e2gSnipCfg['static_gid']) === TRUE ) ?
                                '&amp;gid=' . $this->e2gSnipCfg['gid'] :
                                '&amp;gid=' . $this->e2gSnipCfg['static_gid'] )
                        . ( isset($this->_sanitizedGets['fid']) ? '&amp;fid=' . $this->_sanitizedGets['fid'] : (isset($this->e2gSnipCfg['static_fid']) ? '&amp;fid=' . $this->e2gSnipCfg['static_fid'] : '') )
                        . '&amp;gpn=' . $i . $this->e2gSnipCfg['customgetparams'] . '#' . $permalinkName;
            }

            if ($i === intval($this->e2gSnipCfg['gpn'])) {
                $pages['pages'][$i + 1] = '<b>' . ($i + 1) . '</b> ';
                $pages['currentPage'] = ($i + 1);
            } else {
                $pagesLink = str_replace(' ', '', $pagesLink);
                $pages['pages'][$i + 1] = '<a href="' . $pagesLink . '">' . ($i + 1) . '</a> ';
            }

            if (isset($this->e2gSnipCfg['static_tag'])) {
                $previousLink = $indexPage . '&amp;tag=' . $this->e2gSnipCfg['static_tag']
                        . ( isset($this->_sanitizedGets['gid']) ? '&amp;gid=' . $this->_sanitizedGets['gid'] : '' )
                        . '&amp;gpn=' . ($i - 1) . $this->e2gSnipCfg['customgetparams'] . '#' . $permalinkName;
                $nextLink = $indexPage . '&amp;tag=' . $this->e2gSnipCfg['static_tag']
                        . ( isset($this->_sanitizedGets['gid']) ? '&amp;gid=' . $this->_sanitizedGets['gid'] : '' )
                        . '&amp;gpn=' . ($i + 1) . $this->e2gSnipCfg['customgetparams'] . '#' . $permalinkName;
            } else {
                $previousLink = $indexPage
                        . ( isset($this->e2gSnipCfg['static_gid'])
                        && ( $this->_checkGidDescendant((isset($this->_sanitizedGets['gid']) ? $this->_sanitizedGets['gid'] : $this->e2gSnipCfg['gid']), $this->e2gSnipCfg['static_gid']) === TRUE ) ?
                                '&amp;gid=' . $this->e2gSnipCfg['gid'] :
                                '&amp;gid=' . $this->e2gSnipCfg['static_gid'] )
                        . ( isset($this->_sanitizedGets['fid']) ? '&amp;fid=' . $this->_sanitizedGets['fid'] : (isset($this->e2gSnipCfg['static_fid']) ? '&amp;fid=' . $this->e2gSnipCfg['static_fid'] : '') )
                        . '&amp;gpn=' . ($i - 1) . $this->e2gSnipCfg['customgetparams'] . '#' . $permalinkName;
                $nextLink = $indexPage
                        . ( isset($this->e2gSnipCfg['static_gid'])
                        && ( $this->_checkGidDescendant((isset($this->_sanitizedGets['gid']) ? $this->_sanitizedGets['gid'] : $this->e2gSnipCfg['gid']), $this->e2gSnipCfg['static_gid']) === TRUE ) ?
                                '&amp;gid=' . $this->e2gSnipCfg['gid'] :
                                '&amp;gid=' . $this->e2gSnipCfg['static_gid'] )
                        . ( isset($this->_sanitizedGets['fid']) ? '&amp;fid=' . $this->_sanitizedGets['fid'] : (isset($this->e2gSnipCfg['static_fid']) ? '&amp;fid=' . $this->e2gSnipCfg['static_fid'] : '') )
                        . '&amp;gpn=' . ($i + 1) . $this->e2gSnipCfg['customgetparams'] . '#' . $permalinkName;
            }

            $pages['previousLink'][$i + 1] = $previousLink;
            $pages['nextLink'][$i + 1] = $nextLink;

            $i++;
        }
        $paginationNumbers = $this->_paginationFormat($pages);

        return $paginationNumbers;
    }

    /**
     * Gallery for &fid parameter
     * @return mixed the image's thumbail delivered in template
     */
    public function imgFile() {
        $selectFiles = $this->_fileSqlStatement('*');
        $querySelectFiles = mysql_query($selectFiles);
        if (!$querySelectFiles) {
            echo __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectFiles . '<br />';
            return FALSE;
        }
        $fileNumRows = mysql_num_rows($querySelectFiles);
        if ($fileNumRows === 0) {
            return FALSE;
        }

        // Execute the Javascript library's headers
        $jsLibs = $this->_loadHeaders();
        if ($jsLibs === FALSE) {
            return FALSE;
        }

        $phs = array(
            'sid' => '',
            'wrapper' => '',
            'permalink' => '',
            'content' => '',
            'cat_name' => '',
            'parent_id' => 0,
            'back_tpl' => '',
            'description_tpl' => '',
            'crumbs_tpl' => '',
            'prevupnext_tpl' => '',
            'pagination_tpl' => '',
        );

        // session ID
        $phs['sid'] = $this->e2gSnipCfg['e2g_static_instances'];
        // Gallery's wrapper ID
        $phs['wrapper'] = $this->e2gSnipCfg['e2g_wrapper'];

        $imgThumbs = array();
        while ($l = mysql_fetch_assoc($querySelectFiles)) {
            $thumbPlaceholder = $this->_loadThumbPlaceholders($l);
            if ($thumbPlaceholder === FALSE)
                return FALSE;

            // whether configuration setting is set with or without table, the template will adjust it
            $imgThumbs[] = $this->filler($this->getTpl('thumb_tpl'), $thumbPlaceholder);
        }
        mysql_free_result($querySelectFiles);

        if (strtolower($this->e2gSnipCfg['grid']) === 'table') {
            $countThumbs = count($imgThumbs);
            $i = 0;
            $phs['content'] .= '<table class="' . $this->e2gSnipCfg['grid_class'] . '">';
            foreach ($imgThumbs as $v) {
                if (0 === $i % $this->e2gSnipCfg['colls']) {
                    $phs['content'] .= '<tr>';
                }
                $phs['content'] .= '<td>' . $v . '</td>';
                if ((0 === ($i + 1 + $this->e2gSnipCfg['colls']) % $this->e2gSnipCfg['colls'])
                        || ($i + 1 === $countThumbs)
                ) {
                    $phs['content'] .= '</tr>';
                }
                $i++;
            }
            $phs['content'] .= '</table>';
        } elseif (strtolower($this->e2gSnipCfg['grid']) === 'div') {
            $phs['content'] .= '<div class="' . $this->e2gSnipCfg['grid_class'] . '">';
            foreach ($imgThumbs as $v) {
                $phs['content'] .= $v;
            }
            $phs['content'] .= '</div>';
        } elseif (strtolower($this->e2gSnipCfg['grid']) === 'nowrapper') {
            foreach ($imgThumbs as $v) {
                $phs['content'] .= $v;
            }
        }

        return $this->filler($this->getTpl('tpl'), $phs);
    }

    /**
     * To create a random image usng the &rgid parameter
     * @return mixed the image's thumbail delivered in template
     */
    public function imgRandom() {
        $selectFiles = $this->_fileSqlStatement('*', null, $this->e2gSnipCfg['rgid']);
        $selectFiles .= 'ORDER BY RAND() LIMIT 1';

        $querySelectFiles = mysql_query($selectFiles);
        $fileNumRows = mysql_num_rows($querySelectFiles);
        if ($fileNumRows === 0)
            return NULL;

        // Execute the Javascript library's headers
        $jsLibs = $this->_loadHeaders();
        if ($jsLibs === FALSE) {
            return FALSE;
        }

        $phs = array(
            'sid' => '',
            'wrapper' => '',
            'permalink' => '',
            'content' => '',
            'cat_name' => '',
            'parent_id' => 0,
            'back_tpl' => '',
            'description_tpl' => '',
            'crumbs_tpl' => '',
            'prevupnext_tpl' => '',
            'pagination_tpl' => '',
        );

        // session ID
        $phs['sid'] = $this->e2gSnipCfg['e2g_static_instances'];
        // Gallery's wrapper ID
        $phs['wrapper'] = $this->e2gSnipCfg['e2g_wrapper'];

        $imgThumbs = array();
        while ($l = mysql_fetch_assoc($querySelectFiles)) {
            $thumbPlaceholder = $this->_loadThumbPlaceholders($l);
            if ($thumbPlaceholder === FALSE)
                return FALSE;

            // whether configuration setting is set with or without table, the template will adjust it
            $imgThumbs[] = $this->filler($this->getTpl('thumb_tpl'), $thumbPlaceholder);
        }
        mysql_free_result($querySelectFiles);

        if (strtolower($this->e2gSnipCfg['grid']) === 'table') {
            $countThumbs = count($imgThumbs);
            $i = 0;
            $phs['content'] .= '<table class="' . $this->e2gSnipCfg['grid_class'] . '">';
            foreach ($imgThumbs as $v) {
                if (0 === $i % $this->e2gSnipCfg['colls']) {
                    $phs['content'] .= '<tr>';
                }
                $phs['content'] .= '<td>' . $v . '</td>';
                if ((0 === ($i + 1 + $this->e2gSnipCfg['colls']) % $this->e2gSnipCfg['colls'])
                        || ($i + 1 === $countThumbs)
                ) {
                    $phs['content'] .= '</tr>';
                }
                $i++;
            }
            $phs['content'] .= '</table>';
        } elseif (strtolower($this->e2gSnipCfg['grid']) === 'div') {
            $phs['content'] .= '<div class="' . $this->e2gSnipCfg['grid_class'] . '">';
            foreach ($imgThumbs as $v) {
                $phs['content'] .= $v;
            }
            $phs['content'] .= '</div>';
        } elseif (strtolower($this->e2gSnipCfg['grid']) === 'nowrapper') {
            foreach ($imgThumbs as $v) {
                $phs['content'] .= $v;
            }
        }

        return $this->filler($this->getTpl('tpl'), $phs);
    }

    /**
     * To get and create thumbnails
     * @param  string $gdir             root dir
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
     * @return mixed FALSE/the thumbnail's path
     */
    private function _imgShaper($gdir, $path, $w, $h, $thq, $resizeType=NULL
    , $red=NULL, $green=NULL, $blue=NULL, $createWaterMark = 0) {
        // decoding UTF-8
        $gdir = $this->e2gDecode($gdir);
        $path = $this->e2gDecode($path);
        if (empty($path))
            return FALSE;

        $w = !empty($w) ? $w : $this->e2gSnipCfg['w'];
        $h = !empty($h) ? $h : $this->e2gSnipCfg['h'];
        $thq = !empty($thq) ? $thq : $this->e2gSnipCfg['thq'];
        $resizeType = isset($resizeType) ? $resizeType : $this->e2gSnipCfg['resize_type'];
        $red = isset($red) ? $red : $this->e2gSnipCfg['thbg_red'];
        $green = isset($green) ? $green : $this->e2gSnipCfg['thbg_green'];
        $blue = isset($blue) ? $blue : $this->e2gSnipCfg['thbg_blue'];
        $thumbPath = '_thumbnails/'
                . substr($path, 0, strrpos($path, '.'))
                . '_' . $resizeType
                . '_' . $w . 'x' . $h
                . '_' . $thq
                . '_' . $red . 'x' . $green . 'x' . $blue
                . '.jpg';

        // create cover file
        $thumbDirs = explode('/', dirname($thumbPath));
        $count = count($thumbDirs);
        $xpath = $gdir;

        try {
            $lng = parent::languageSwitch($this->modx->config['manager_language'], E2G_SNIPPET_PATH);
        } catch (Exception $e) {
            echo $e->getMessage();
            return FALSE;
        }

        for ($c = 0; $c < $count; $c++) {
            $xpath .= $thumbDirs[$c] . '/';
            $this->createIndexHtml($xpath, $lng['indexfile']);
        }

        if (!class_exists('E2gThumb')) {
            if (!file_exists(realpath(E2G_SNIPPET_PATH . 'includes/models/e2g.public.thumbnail.class.php'))) {
                echo __LINE__ . ' : File <b>' . E2G_SNIPPET_PATH . 'includes/models/e2g.public.thumbnail.class.php</b> does not exist.';
                return FALSE;
            } else {
                include_once E2G_SNIPPET_PATH . 'includes/models/e2g.public.thumbnail.class.php';
            }
        }

        $imgShaper = new E2gThumb($this->modx, $this->e2gSnipCfg);
        $urlEncoding = $imgShaper->imgShaper($gdir, $path, $w, $h, $thq, $resizeType
                , $red, $green, $blue, $createWaterMark, $thumbPath);
        if ($urlEncoding !== FALSE) {
            return $urlEncoding;
        } else {
            return FALSE;
        }
    }

    /**
     * To insert included files into the page header
     * @return mixed the file inclusion or FALSE return
     */
    private function _loadHeaders() {
        // return empty, not FALSE!
        if ($this->e2gSnipCfg['glib'] === '0') {
            return NULL;
        }

        // Load the library from database.
        $glibs = $this->_loadViewerConfigs($this->e2gSnipCfg['glib']);
        if (!$glibs)
            return FALSE;

        if (!isset($glibs[$this->e2gSnipCfg['glib']])) {
            return FALSE;
        }

        // CSS STYLES
        if (!empty($glibs[$this->e2gSnipCfg['glib']]['headers_css'])
                && $glibs[$this->e2gSnipCfg['glib']]['autoload_css'] === '1'
                && $this->e2gSnipCfg['autoload_css'] != '0'
        ) {
            foreach ($glibs[$this->e2gSnipCfg['glib']]['headers_css'] as $vRegClientCSS) {
                $this->modx->regClientCSS($vRegClientCSS, 'screen');
            }
        }

        // GLOBAL e2g CSS styles
        if ($this->e2gSnipCfg['css'] !== '0' && file_exists(realpath($this->e2gSnipCfg['css']))) {
            $this->modx->regClientCSS($this->e2gSnipCfg['css'], 'screen');
        }

        // JS Libraries
        if (!empty($glibs[$this->e2gSnipCfg['glib']]['headers_js'])
                && $glibs[$this->e2gSnipCfg['glib']]['autoload_js'] === '1'
                && $this->e2gSnipCfg['autoload_js'] != '0'
        ) {
            foreach ($glibs[$this->e2gSnipCfg['glib']]['headers_js'] as $vRegClientJS) {
                $this->modx->regClientStartupScript($vRegClientJS);
            }
        }

        // HTMLBLOCK
        if (!empty($glibs[$this->e2gSnipCfg['glib']]['headers_html'])
                && $glibs[$this->e2gSnipCfg['glib']]['autoload_html'] === '1'
                && $this->e2gSnipCfg['autoload_html'] != '0'
        ) {
            $this->modx->regClientStartupHTMLBlock($glibs[$this->e2gSnipCfg['glib']]['headers_html']);
        }

        // GLOBAL e2g JS styles
        if ($this->e2gSnipCfg['js'] !== '0' && file_exists(realpath($this->e2gSnipCfg['js']))) {
            $this->modx->regClientStartupScript($this->e2gSnipCfg['js']);
        }

        return TRUE;
    }

    /**
     * To generate the display of each of thumbnail pieces from the Javascript libraries
     * @param  mixed $row  the thumbnail's data in an array
     * @return mixed the file inclusion, thumbnail sources, comment's controller
     */
    private function _loadThumbPlaceholders($row) {
        // check the picture existance before continue
        $fileRealPath = realpath($this->e2gDecode($this->e2gSnipCfg['gdir'] . $this->getPath($row['dir_id']) . $row['filename']));
        if (!file_exists($fileRealPath)) {
            return FALSE;
        }

        $row['w'] = $this->e2gSnipCfg['w'];
        $row['h'] = $this->e2gSnipCfg['h'];

        // SLIDESHOW
        $this->modx->setPlaceholder('easy2:show_group', $this->e2gSnipCfg['show_group']);

        ########################################################################

        $glibs = $this->_loadViewerConfigs($this->e2gSnipCfg['glib'], $row['id']);

        $row['glibact'] = '';
        if (isset($this->e2gSnipCfg['landingpage']) || $this->e2gSnipCfg['glib'] === '0') {
            $row['glibact'] = NULL;
        }
        // gallery's javascript library activation
        elseif (isset($glibs[$this->e2gSnipCfg['glib']])) {
            $row['glibact'] = $glibs[$this->e2gSnipCfg['glib']]['glibact'];
        }
        else
            return FALSE;

        $title = trim($row['alias']) != '' ? $row['alias'] : $row['filename'];
        $row['title'] = $this->cropName($this->e2gSnipCfg['mbstring'], $this->e2gSnipCfg['charset'], $this->e2gSnipCfg['name_len'], $title);

        $path = $this->getPath($row['dir_id']);
        $imgShaper = $this->_imgShaper($this->e2gSnipCfg['gdir'], $path . $row['filename'], $row['w'], $row['h'], $this->e2gSnipCfg['thq']);
        if ($imgShaper !== FALSE) {
            $row['src'] = $this->e2gEncode($imgShaper);
        } else {
            $row['src'] = 'assets/modules/easy2/show.easy2gallery.php?w=' . $row['w'] . '&amp;h=' . $row['h'] . '&amp;th=5';
        }
        unset($imgShaper);

        if (isset($this->e2gSnipCfg['landingpage'])) {
            $row['link'] = $this->modx->makeUrl($this->e2gSnipCfg['landingpage']
                            , $this->modx->aliases
                            , 'lp=' . $this->e2gSnipCfg['landingpage'])
                    . '&amp;fid=' . $row['id']
            ;
        } elseif ($this->e2gSnipCfg['use_redirect_link'] === TRUE && !empty($row['redirect_link'])) {
            $row['link'] = $row['redirect_link'];
            $row['glibact'] = '';
        } else {
            if ($this->e2gSnipCfg['img_src'] === 'generated') {
                $row['link'] = 'assets/modules/easy2/show.easy2gallery.php?fid=' . $row['id'];
            } elseif ($this->e2gSnipCfg['img_src'] === 'original') {
                // path to subdir's thumbnail
                $path = $this->getPath($row['dir_id']);
                $row['link'] = $this->e2gSnipCfg['gdir'] . $path . $row['filename'];
            }
        } // if ( isset($this->e2gSnipCfg['landingpage']) )

        if ($row['description'] !== '') {
            $row['description'] = $this->_decodeText($row['description']);
        }

        /**
         * invoke plugin for EACH thumb
         */
        // creating the plugin array's content
        $e2gEvtParams = array();
        $row['sid'] = $this->e2gSnipCfg['e2g_static_instances'];
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
        $checkIgnoredIp = $this->checkIgnoredIp();

        if (intval($this->e2gSnipCfg['ecm']) === 1 && !$checkIgnoredIp) {
            $row['com'] = 'e2gcom' . ($row['comments'] === 0 ? 0 : 1);

            // iframe activation
            if (isset($glibs[$this->e2gSnipCfg['glib']])) {
                $row['comments'] = '<a href="' . E2G_SNIPPET_URL . 'comments.easy2gallery.php?id=' . $row['id'] . '" ' . $glibs[$this->e2gSnipCfg['glib']]['clibact'] . '>' . $row['comments'] . '</a>';
//                $row['comments'] = '<a href="' . E2G_SNIPPET_URL . 'comments.easy2gallery.php?id=' . $row['id'] . '" ' . $glibs[$this->e2gSnipCfg['glib']]['comments'] . '>' . $row['comments'] . '</a>';
//                $row['commentslink'] = E2G_SNIPPET_URL . 'comments.easy2gallery.php?id=' . $row['id'] . '" ' . @rtrim($glibs[$this->e2gSnipCfg['glib']]['comments'], '"');
            }
        } else {
            $row['comments'] = '&nbsp;';
            $row['com'] = 'not_display';
        }

        return $row;
    }

    /**
     * Load the Javascript viewer's into each of images
     * @param   string  $glib   library's name
     * @param   int     $fid    file ID
     * @return  array   the JS configurations
     */
    private function _loadViewerConfigs($glib, $fid=NULL) {
        // SLIDESHOW
        $this->modx->setPlaceholder('easy2:show_group', $this->e2gSnipCfg['show_group']);
        $fid = !empty($fid) ? $fid : $this->e2gSnipCfg['fid'];

        // if &glib=`0`, empty($glib) returns TRUE.
        // http://us2.php.net/empty
        if (empty($glib))
            return FALSE;

        $selectGlibs = 'SELECT * FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_viewers '
                . 'WHERE name=\'' . $glib . '\'';

        $glibs = $this->modx->db->makeArray($this->modx->db->query($selectGlibs));

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
        $glibs[$glib]['headers_html'] = str_replace('[+easy2:show_group+]', $this->e2gSnipCfg['show_group'], $glibs[$glib]['headers_html']);
        $glibs[$glib]['headers_html'] = str_replace('[+easy2:fid+]', $fid, $glibs[$glib]['headers_html']);

        $glibs[$glib]['glibact'] = str_replace('[+easy2:show_group+]', $this->e2gSnipCfg['show_group'], $glibs[$glib]['glibact']);
        $glibs[$glib]['glibact'] = str_replace('[+easy2:fid+]', $fid, $glibs[$glib]['glibact']);

        return $glibs;
    }

    /**
     * Slideshow's controller
     * @return string the slideshow's images
     */
    public function slideshow($slideshow) {
        // gives the index file the shorthand to the modx's API
        $modx = $this->modx;
        /**
         * added the &fid parameter inside the &slideshow, to open a full page of the clicked image
         * into the specified landingpage ID
         */
        if (isset($this->_sanitizedGets['fid'])
                && isset($this->e2gSnipCfg['landingpage'])
                && $this->modx->documentIdentifier !== intval($this->e2gSnipCfg['landingpage'])
        ) {
            // making flexible FURL or not
            $redirectUrl = $this->modx->makeUrl($this->e2gSnipCfg['landingpage']
                            , $this->modx->aliases
                            , 'sid=' . $this->e2gSnipCfg['e2g_static_instances'])
                    . '&amp;lp=' . $this->e2gSnipCfg['landingpage'] . '&amp;fid=' . $this->_sanitizedGets['fid'];
            $this->modx->sendRedirect(htmlspecialchars_decode($redirectUrl));
        } elseif (isset($this->_sanitizedGets['fid']) && !isset($this->e2gSnipCfg['landingpage'])) {
            /**
             * self landingpage
             */
            if (!empty($this->e2gSnipCfg['css'])) {
                $this->modx->regClientCSS($this->e2gSnipCfg['css'], 'screen');
            }
            if (!empty($this->e2gSnipCfg['js'])) {
                $this->modx->regClientStartupScript($this->e2gSnipCfg['js']);
            }
            return $this->landingPage($this->_sanitizedGets['fid']);
        } else {
            /**
             * The DEFAULT display
             */
            // use custom index file if it's been set inside snippet call.
            if (isset($this->e2gSnipCfg['ss_indexfile'])) {
                if (file_exists(realpath($this->e2gSnipCfg['ss_indexfile']))) {
                    $ssDisplay = include($this->e2gSnipCfg['ss_indexfile']);
                } else {
                    $ssDisplay = 'slideshow index file <b>' . $this->e2gSnipCfg['ss_indexfile'] . '</b> is not found.';
                }
            }
            // include the available slideshow from database
            else {
                $selectIndexFile = 'SELECT indexfile FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_slideshows '
                        . 'WHERE name = \'' . $slideshow . '\'';
                $queryIndexFile = mysql_query($selectIndexFile);
                if (!$queryIndexFile) {
                    echo __LINE__ . ' : ' . mysql_error() . '<br />' . $selectIndexFile . '<br />';
                    return FALSE;
                }
                $row = mysql_fetch_row($queryIndexFile);
                $dbIndexFile = $row[0];
                if (empty($dbIndexFile)) {
                    echo __LINE__ . ' : Empty index file in database.';
                    return FALSE;
                } elseif (file_exists(realpath($dbIndexFile))) {
                    $ssDisplay = include($dbIndexFile);
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

        return $this->filler($this->getTpl('slideshow-tpl'), $output);
    }

    private function _getSlideShowParams() {
        $ssParams = array();
        // database selection
        $ssParams['gdir'] = $this->e2gSnipCfg['gdir'];
        $ssParams['sid'] = $this->e2gSnipCfg['e2g_static_instances'];
        $ssParams['gid'] = $this->e2gSnipCfg['gid'];
        $ssParams['fid'] = $this->e2gSnipCfg['fid'];
        $ssParams['rgid'] = $this->e2gSnipCfg['rgid'];
        $ssParams['where_dir'] = $this->e2gSnipCfg['where_dir'];
        $ssParams['where_file'] = $this->e2gSnipCfg['where_file'];
        $ssParams['ss_allowedratio'] = $this->e2gSnipCfg['ss_allowedratio'];
        /**
         * Filtering the output by comparing the images' size ratio
         */
        if ($ssParams['ss_allowedratio'] !== 'all') {
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
        if ($this->e2gSnipCfg['orderby'] === 'random') {
            $ssParams['orderby'] = 'rand()';
            $ssParams['order'] = '';
        } else {
            $ssParams['orderby'] = $this->e2gSnipCfg['orderby'];
            $ssParams['order'] = $this->e2gSnipCfg['order'];
        }
        if ($this->e2gSnipCfg['cat_orderby'] === 'random') {
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
        $ssParams['ss_red'] = $this->e2gSnipCfg['ss_red'];
        $ssParams['ss_green'] = $this->e2gSnipCfg['ss_green'];
        $ssParams['ss_blue'] = $this->e2gSnipCfg['ss_blue'];

        return $ssParams;
    }

    private function _getSlideShowFiles() {
        if ($this->e2gSnipCfg['ss_orderby'] === 'random') {
            $ssOrderBy = 'rand()';
            $ssOrder = '';
        } else {
            $ssOrderBy = $this->e2gSnipCfg['ss_orderby'];
            $ssOrder = $this->e2gSnipCfg['ss_order'];
        }

        /**
         * Filtering the slideshow size ratio
         */
        if ($this->e2gSnipCfg['ss_allowedratio'] !== 'all') {
            // create min-max slideshow width/height ratio
            $ssXpldRatio = explode('-', $this->e2gSnipCfg['ss_allowedratio']);

            $ssMinRatio = trim($ssXpldRatio[0]);
            $ssMinRatio = str_replace(',', '.', $ssMinRatio);
            $ssMinRatio = @explode('.', $ssMinRatio);
            $ssMinRatio = @implode('.', array(intval($ssMinRatio[0]), intval($ssMinRatio[1])));

            $ssMaxRatio = trim($ssXpldRatio[1]);
            $ssMaxRatio = str_replace(',', '.', $ssMaxRatio);
            $ssMaxRatio = @explode('.', $ssMaxRatio);
            $ssMaxRatio = @implode('.', array(intval($ssMaxRatio[0]), intval($ssMaxRatio[1])));
        }

        ob_start();
        $ssFiles = array();
        $errorThumb = 'assets/modules/easy2/show.easy2gallery.php?w=' . $this->e2gSnipCfg['w'] . '&amp;h=' . $this->e2gSnipCfg['h'] . '&amp;th=2';
        $errorImg = 'assets/modules/easy2/show.easy2gallery.php?w=' . $this->e2gSnipCfg['ss_w'] . '&amp;h=' . $this->e2gSnipCfg['ss_h'] . '&amp;th=5';
        $ssCheckDuplicate = array();
        if (!empty($this->e2gSnipCfg['gid']) && $this->modx->documentIdentifier !== intval($this->e2gSnipCfg['landingpage'])) {

            $selectFiles = $this->_fileSqlStatement('*', $this->e2gSnipCfg['ss_allowedratio']);
            $selectFiles .= 'ORDER BY ' . $ssOrderBy . ' ' . $ssOrder . ' ';
            $selectFiles .= ( $this->e2gSnipCfg['ss_limit'] === 'none' ? '' : 'LIMIT ' . ( $this->e2gSnipCfg['gpn'] * $this->e2gSnipCfg['ss_limit'] ) . ', ' . $this->e2gSnipCfg['ss_limit'] );

            $querySelectFiles = mysql_query($selectFiles);
            if (!$querySelectFiles) {
                echo __LINE__ . ' : ' . mysql_error() . '<br />' . $selectFiles . '<br />';
                return FALSE;
            }

            while ($row = mysql_fetch_assoc($querySelectFiles)) {
                $ssRows = $this->_processSlideshowFiles($row);
                if ($ssRows === FALSE)
                    continue;
                foreach ($ssRows as $k => $v) {
                    $ssFiles[$k][] = $v;
                    $ssCheckDuplicate[$v['src']] = $v['src'];
                }
            }
            mysql_free_result($querySelectFiles);
        }

        if (!empty($this->e2gSnipCfg['fid'])) {

            $selectFiles = $this->_fileSqlStatement('*');

            $querySelectFiles = mysql_query($selectFiles);
            if (!$querySelectFiles) {
                echo __LINE__ . ' : ' . mysql_error() . '<br />' . $selectFiles . '<br />';
                return FALSE;
            }

            while ($row = mysql_fetch_assoc($querySelectFiles)) {
                $ssRows = $this->_processSlideshowFiles($row);
                if ($ssRows === FALSE)
                    continue;
                foreach ($ssRows as $k => $v) {
                    if (isset($ssCheckDuplicate[$v['src']])) {
                        continue;
                    }
                    $ssFiles[$k][] = $v;
                    $ssCheckDuplicate[$v['src']] = $v['src'];
                }
            }
            mysql_free_result($querySelectFiles);
        }

        if (!empty($this->e2gSnipCfg['rgid'])) {

            $selectFiles = $this->_fileSqlStatement('*', $this->e2gSnipCfg['ss_allowedratio'], $this->e2gSnipCfg['rgid']);
            $selectFiles .= 'ORDER BY RAND() ';
            $selectFiles .= ( $this->e2gSnipCfg['ss_limit'] === 'none' ? '' : 'LIMIT ' . ( $this->e2gSnipCfg['gpn'] * $this->e2gSnipCfg['ss_limit'] ) . ',' . $this->e2gSnipCfg['ss_limit'] . ' ' );

            $querySelectFiles = mysql_query($selectFiles);
            if (!$querySelectFiles) {
                echo __LINE__ . ' : ' . mysql_error() . '<br />' . $selectFiles . '<br />';
                return FALSE;
            }
            while ($row = mysql_fetch_assoc($querySelectFiles)) {
                $ssRows = $this->_processSlideshowFiles($row);
                if ($ssRows === FALSE)
                    continue;
                foreach ($ssRows as $k => $v) {
                    if (isset($ssCheckDuplicate[$v])) {
                        continue;
                    }
                    $ssFiles[$k][] = $v;
                }
            }
            mysql_free_result($querySelectFiles);
        }

        /**
         * if the counting below = 0 (zero), then should be considered inside
         * the slideshow types, while for some slideshows this doesn't really matter.
         */
        $ssFiles['count'] = count($ssFiles['src']);
        ob_end_clean();

        return $ssFiles;
    }

    /**
     * Process the default slideshow file outputs of all gallery/file/random selections
     * @param   array   $row    the fetch assoc
     * @return  array   the processed outputs
     */
    private function _processSlideshowFiles($row) {
        $path = $this->getPath($row['dir_id']);

        $thumbImg = $this->_imgShaper($this->e2gSnipCfg['gdir'], $path . $row['filename']
                , $this->e2gSnipCfg['w'], $this->e2gSnipCfg['h'], $this->e2gSnipCfg['thq']
                , $this->e2gSnipCfg['resize_type'], $this->e2gSnipCfg['thbg_red']
                , $this->e2gSnipCfg['thbg_green'], $this->e2gSnipCfg['thbg_blue']);
        // thumbnail first...
        $ssFile = array();
        if ($thumbImg !== FALSE) {
            // ... then the slideshow's images
            if ($this->e2gSnipCfg['ss_img_src'] === 'generated') {
                /**
                 * + WATERMARK-ing
                 */
                $ssImg = $this->_imgShaper($this->e2gSnipCfg['gdir'], $path . $row['filename']
                        , $this->e2gSnipCfg['ss_w'], $this->e2gSnipCfg['ss_h'], $this->e2gSnipCfg['ss_thq']
                        , $this->e2gSnipCfg['ss_resize_type'], $this->e2gSnipCfg['ss_red']
                        , $this->e2gSnipCfg['ss_green'], $this->e2gSnipCfg['ss_blue'], $this->e2gSnipCfg['ewm']);
                if ($ssImg !== FALSE) {
                    $ssFile['resizedimg'] = $this->e2gEncode($ssImg);
                } else {
                    return FALSE;
                }
                unset($ssImg);
            } elseif ($this->e2gSnipCfg['ss_img_src'] === 'original') {
                $ssFile['resizedimg'] = $this->e2gSnipCfg['gdir'] . $path . $row['filename'];
            }

            // if the slideshow's images were created successfully
            $ssFile['thumbsrc'] = $this->e2gEncode($thumbImg);
        } else {
            return FALSE;
        }

        foreach ($row as $k => $v) {
            $ssFile[$k] = $v;
        }
        // process the outputs of some specified placeholders
        $ssFile['dirid'] = $row['dir_id']; // fallback for the previous versions
        $ssFile['src'] = $this->e2gEncode($this->e2gSnipCfg['gdir'] . $path . $row['filename']);
        $ssFile['title'] = ($row['alias'] !== '' ? $row['alias'] : $row['filename']);
        $ssFile['name'] = $row['alias'];
        $ssFile['description'] = $this->_decodeText($row['description']);

        return $ssFile;
    }

    /**
     * A landing page to show the image, including information within it.
     * @param  int   $fileId file's ID
     * @return mixed scripts, images, and FALSE return
     */
    public function landingPage($fileId) {
        if (!empty($this->e2gSnipCfg['css'])) {
            $this->modx->regClientCSS($this->e2gSnipCfg['css'], 'screen');
        }
        if (!empty($this->e2gSnipCfg['js'])) {
            $this->modx->regClientStartupScript($this->e2gSnipCfg['js']);
        }

        $select = 'SELECT * FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_files WHERE id = ' . intval($fileId);

        $query = mysql_query($select);
        if (!$query) {
            echo __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $select . '<br />';
            return FALSE;
        }

        $l = array();
        while ($fetch = mysql_fetch_assoc($query)) {
            $l = $fetch;
            $path = $this->getPath($fetch['dir_id']);

            // goldsky -- only to switch between localhost and live site.
            // TODO: need review!
            if ($this->e2gSnipCfg['lp_img_src'] === 'original') {
                $filePath = $this->e2gSnipCfg['gdir'] . $path . $fetch['filename'];
//                if (strpos($_SERVER['DOCUMENT_ROOT'], '/') === (int) 0) {
                if (strtoupper(substr(PHP_OS, 0, 3) !== 'WIN')) {
                    $l['src'] = rawurldecode(str_replace('%2F', '/', rawurlencode($filePath)));
                } else
                    $l['src'] = $filePath;
            }
            elseif ($this->e2gSnipCfg['lp_img_src'] === 'generated') {
                /**
                 * + WATERMARK-ing
                 */
                if (!isset($this->e2gSnipCfg['lp_w']) || !isset($this->e2gSnipCfg['lp_h'])) {
                    $imgSize = @getimagesize($this->e2gSnipCfg['gdir'] . $this->e2gDecode($path . $fetch['filename']));
                    if (!isset($this->e2gSnipCfg['lp_w']))
                        $this->e2gSnipCfg['lp_w'] = $imgSize[0];
                    if (!isset($this->e2gSnipCfg['lp_h']))
                        $this->e2gSnipCfg['lp_h'] = $imgSize[1];
                    $imgSize = array();
                    unset($imgSize);
                }
                $imgShaper = $this->_imgShaper($this->e2gSnipCfg['gdir']
                        , $path . $fetch['filename']
                        , $this->e2gSnipCfg['lp_w']
                        , $this->e2gSnipCfg['lp_h']
                        , $this->e2gSnipCfg['lp_thq']
                        , $this->e2gSnipCfg['lp_resize_type']
                        , $this->e2gSnipCfg['lp_red']
                        , $this->e2gSnipCfg['lp_green']
                        , $this->e2gSnipCfg['lp_blue']
                        , 1);
                if ($imgShaper !== FALSE) {
                    $filePath = $this->e2gEncode($imgShaper);
                } else {
                    $filePath = 'assets/modules/easy2/show.easy2gallery.php?w=' . $this->e2gSnipCfg['lp_w'] . '&amp;h=' . $this->e2gSnipCfg['lp_h'] . '&amp;th=5';
                }
                unset($imgShaper);

//                if (strpos($_SERVER['DOCUMENT_ROOT'], '/') === (int) 0) {
                if (strtoupper(substr(PHP_OS, 0, 3) !== 'WIN')) {
                    $l['src'] = rawurldecode(str_replace('%2F', '/', rawurlencode($filePath)));
                } else
                    $l['src'] = $filePath;
            }

            $l['title'] = $fetch['alias'] != '' ? $fetch['alias'] : $fetch['filename'];
            $l['description'] = $this->_decodeText($fetch['description']);

            /**
             * Comments on the landing page
             */
            // HIDE COMMENTS from Ignored IP Addresses
            $checkIgnoredIp = $this->checkIgnoredIp();

            if ($this->e2gSnipCfg['ecm'] === '1' && !$checkIgnoredIp) {

                $this->modx->regClientCSS($this->e2gSnipCfg['page_tpl_css']);

                $l['com'] = 'e2gcom' . ($l['comments'] === 0 ? 0 : 1); // @todo: check ===
                $l['comments'] = $this->_comments($fileId);
            } else {
                $l['comments'] = '&nbsp;';
                $l['com'] = 'not_display';
            }
        }

        // Gallery's wrapper ID
        $l['wrapper'] = $this->e2gSnipCfg['e2g_wrapper'];

        /**
         * invoke plugin for THE IMAGE
         */
        // feeding additional parameters for the plugin
        $l['fid'] = $fileId;
        $l['landingpage'] = $this->e2gSnipCfg['landingpage'];
        // creating the plugin array's content
        $e2gEvtParams = array();
        foreach ($l as $k => $v) {
            $e2gEvtParams[$k] = $v;
        }

        $l['permalink'] = '<a href="#" name="' . $this->e2gSnipCfg['e2g_static_instances'] . '_' . $fileId . '"></a> ';
        $l['landingpagepluginprerender'] = $this->_plugin('OnE2GWebLandingpagePrerender', $e2gEvtParams);
        $l['landingpagepluginrender'] = $this->_plugin('OnE2GWebLandingpageRender', $e2gEvtParams);

        return $this->filler($this->getTpl('page_tpl'), $l);
    }

    /**
     * Comment function for a page (landingpage or galley)
     * @param  string $fileId File ID of the comment's owner
     * @return mixed  return the comment's page content
     */
    private function _comments($fileId) {
        $cpn = (empty($this->_sanitizedGets['cpn']) || !is_numeric($this->_sanitizedGets['cpn'])) ? 0 : (int) $this->_sanitizedGets['cpn'];
	$fileId = intval($fileId);
	if ($fileId === 0) {
            return FALSE;
	}

        // Get a key from https://www.google.com/recaptcha/admin/create
        $recaptchalib = realpath(E2G_SNIPPET_PATH . 'includes/recaptchalib.php');
        if (!file_exists($recaptchalib)) {
            return FALSE;
        }
        require_once($recaptchalib);

        if (file_exists(realpath(E2G_SNIPPET_PATH . 'includes/langs/' . $this->modx->config['manager_language'] . '.comments.php'))) {
            include_once E2G_SNIPPET_PATH . 'includes/langs/' . $this->modx->config['manager_language'] . '.comments.php';
            $lngCmt = $e2g_lang[$this->modx->config['manager_language']];
        } else {
            include_once E2G_SNIPPET_PATH . 'includes/langs/english.comments.php';
            $lngCmt = $e2g_lang['english'];
        }

        $_P = array();
        $_P['charset'] = $this->modx->config['modx_charset'];

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

        $check = FALSE;
        // INSERT THE COMMENT INTO DATABASE
        if (!empty($_POST['name']) && !empty($_POST['comment'])) {
            $n = htmlspecialchars(trim($_POST['name']), ENT_QUOTES);
            $c = htmlspecialchars(trim($_POST['comment']), ENT_QUOTES);
            $e = htmlspecialchars(trim($_POST['email']), ENT_QUOTES);
            $ip = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];

            if (!$this->checkEmailAddress($e)) {
                $_P['comment_body'] .= '<h2>' . $lngCmt['email_err'] . '</h2>';
            } elseif ($this->e2gSnipCfg['recaptcha'] === '1' && empty($_POST['recaptcha_response_field'])) {
                $_P['comment_body'] .= '<h2>' . $lngCmt['recaptcha_err'] . '</h2>';
            } else {
                $check = TRUE;
            }
            if ($check) {
                if ($this->e2gSnipCfg['recaptcha'] === '1' && $_POST['recaptcha_response_field']) {
                    require_once E2G_SNIPPET_PATH . 'includes/recaptchalib.php';
                    # the response from reCAPTCHA
                    $resp = NULL;
                    # the error code from reCAPTCHA, if any
                    $error = NULL;

                    # was there a reCAPTCHA response?
                    if ($_POST["recaptcha_response_field"]) {
                        $resp = recaptcha_check_answer($this->e2gSnipCfg['recaptcha_key_private'], $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);

                        if (!$resp->is_valid) {
                            # set the error code so that we can display it
                            $error = $resp->error;
                        } else {
                            $comInsert = 'INSERT INTO ' . $this->modx->db->config['table_prefix'] . 'easy2_comments (file_id,author,email,ip_address,comment,date_added) '
                                    . "VALUES($fileId,'$n','$e','$ip','$c', NOW())";
                            if (mysql_query($comInsert)) {
                                mysql_query('UPDATE ' . $this->modx->db->config['table_prefix'] . 'easy2_files SET comments=comments+1 WHERE id=' . $fileId);
                                $_P['comment_body'] .= '<h3>' . $lngCmt['comment_added'] . '</h3>';
                            } else {
                                $_P['comment_body'] .= '<h2>' . $lngCmt['comment_add_err'] . '</h2>';
                            }
                        }
                    }
                } else { // NOT USING reCaptcha
                    $comInsert = 'INSERT INTO ' . $this->modx->db->config['table_prefix'] . 'easy2_comments (file_id,author,email,ip_address,comment,date_added) '
                            . "VALUES($fileId,'$n','$e','$ip','$c', NOW())";
                    if (mysql_query($comInsert)) {
                        mysql_query('UPDATE ' . $this->modx->db->config['table_prefix'] . 'easy2_files SET comments=comments+1 WHERE id=' . $fileId);
                        $_P['comment_body'] .= '<h3>' . $lngCmt['comment_added'] . '</h3>';
                    } else {
                        $_P['comment_body'] .= '<h2>' . $lngCmt['comment_add_err'] . '</h2>';
                    }
                }
            }
        }

        if ($_POST) {
            if (empty($_POST['name']) && empty($_POST['comment'])) {
                $_P['comment_body'] .= '<h2>' . $lngCmt['comment_empty_name_comment'] . '</h2>';
            } elseif (empty($_POST['name'])) {
                $_P['comment_body'] .= '<h2>' . $lngCmt['comment_empty_name'] . '</h2>';
            } elseif (empty($_POST['comment'])) {
                $_P['comment_body'] .= '<h2>' . $lngCmt['comment_empty_comment'] . '</h2>';
            }
        }

        // DISPLAY THE AVAILABLE COMMENTS
        $selectComments = 'SELECT * FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_comments '
                . 'WHERE file_id = ' . $fileId . ' '
                . 'AND STATUS=1 '
                . 'ORDER BY id DESC '
                . 'LIMIT ' . ($cpn * $this->e2gSnipCfg['ecl_page']) . ', ' . $this->e2gSnipCfg['ecl_page'];
        $querySelectComments = mysql_query($selectComments);
        if (!$querySelectComments) {
            echo __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectComments . '<br />';
            return FALSE;
        }

        $rowClassNum = 0;
        while ($l = mysql_fetch_assoc($querySelectComments)) {
            $l['i'] = $rowClassNum % 2;
            $l['name_permalink'] = '<a href="#" name="lpcmtnm' . $l['id'] . '"></a> ';
            $l['name_w_permalink'] = '<a href="'
                    // making flexible FURL or not
                    . $this->modx->makeUrl($this->modx->documentIdentifier
                            , $this->modx->aliases
                            , 'sid=' . $e2gStaticInstances)
                    . '&amp;lp=' . $this->e2gSnipCfg['landingpage'] . '&amp;fid=' . $fileId . '&amp;cpn=' . $cpn . '#lpcmtnm' . $l['id']
                    . '">' . $l['author'] . '</a> ';
            if (!empty($l['email']))
                $l['name_w_mail'] = '<a href="mailto:' . $l['email'] . '">' . $l['author'] . '</a>';
            else
                $l['name_w_mail'] = $l['author'];

            $_P['comment_body'] .= $this->filler($this->getTpl('page_comments_row_tpl'), $l);
            $rowClassNum++;
        }
        mysql_free_result($querySelectComments);

        $_P['pages_permalink'] = '<a href="#" name="lpcmtpg' . $cpn . '"></a>';

        // Comment pages
        $selectCountComments = 'SELECT COUNT(*) FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_comments WHERE file_id = ' . $fileId;
        $querySelectCountComments = mysql_query($selectCountComments);
        if (!$querySelectCountComments) {
            echo __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectCountComments . '<br />';
            return FALSE;
        }

        list($cnt) = mysql_fetch_row($querySelectCountComments);
        mysql_free_result($querySelectCountComments);

        if ($cnt > $this->e2gSnipCfg['ecl_page']) {
            $_P['comment_pages'] = '<p class="pnums">' . $lngCmt['pages'] . ':';
            $commentPageNum = 0;
            while ($commentPageNum * $this->e2gSnipCfg['ecl_page'] < $cnt) {
                if ($commentPageNum === $cpn)
                    $_P['comment_pages'] .= '<b>' . ($commentPageNum + 1) . '</b> ';
                else
                    $_P['comment_pages'] .=
                            '<a href="'
                            // making flexible FURL or not
                            . $this->modx->makeUrl($this->modx->documentIdentifier
                                    , $this->modx->aliases
                                    , 'sid=' . $e2gStaticInstances)
                            . '&amp;lp=' . $this->e2gSnipCfg['landingpage'] . '&amp;fid=' . $fileId . '&amp;cpn=' . $commentPageNum . '#lpcmtpg' . $commentPageNum
                            . '">' . ($commentPageNum + 1) . '</a> ';
                $commentPageNum++;
            }
            $_P['comment_pages'] .= '</p>';
        }

        // COMMENT TEMPLATE
        if ($this->e2gSnipCfg['recaptcha'] === '1') {
            $_P['recaptcha'] = '
                <tr>
                    <td colspan="4">' . $this->_recaptchaForm($this->e2gSnipCfg['recaptcha_key_public'], $error) . '</td>
                </tr>';
        } else {
            $_P['recaptcha'] = '';
        }
        return $this->filler($this->getTpl('page_comments_tpl'), $_P);
    }

    /**
     * Invoking the script with plugin, at any specified places.
     * @param string    $e2gEvtName     event trigger.
     * @param mixed     $e2gEvtParams   parameters array: depends on the event trigger.
     * @return mixed    if TRUE, will return the indexfile. Otherwise this will return FALSE.
     */
    private function _plugin($e2gEvtName, $e2gEvtParams=array()) {
        // if the user set &plugin=`none`
        if ($this->e2gSnipCfg['plugin'] === 'none')
            return NULL;

        // call plugin from the database as default
        if (!isset($this->e2gSnipCfg['plugin'])) {
            return parent::plugin($e2gEvtName, $e2gEvtParams);
        }

        // if the plugins are called from the snippet
        // example: &plugin=`thumb:starrating#Prerender, watermark@custom/index/file.php | gallery:... | landingpage:...`
        // clean up
        $badChars = array('`', ' ');
        $plugin = str_replace($badChars, '', trim($this->e2gSnipCfg['plugin']));

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
            if ($p_selections === 'none')
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
                if ($pluginArea === 'none')
                    return NULL;

                $convertEvtName = '';
                if ($pluginTarget === 'thumb' && $pluginArea === 'prerender')
                    $convertEvtName = 'OnE2GWebThumbPrerender';
                elseif ($pluginTarget === 'thumb' && $pluginArea === 'render')
                    $convertEvtName = 'OnE2GWebThumbRender';
                elseif ($pluginTarget === 'dir' && $pluginArea === 'prerender')
                    $convertEvtName = 'OnE2GWebDirPrerender';
                elseif ($pluginTarget === 'dir' && $pluginArea === 'render')
                    $convertEvtName = 'OnE2GWebDirRender';
                elseif ($pluginTarget === 'gallery' && $pluginArea === 'prerender')
                    $convertEvtName = 'OnE2GWebGalleryPrerender';
                elseif ($pluginTarget === 'gallery' && $pluginArea === 'render')
                    $convertEvtName = 'OnE2GWebGalleryRender';
                elseif ($pluginTarget === 'landingpage' && $pluginArea === 'prerender')
                    $convertEvtName = 'OnE2GWebLandingpagePrerender';
                elseif ($pluginTarget === 'landingpage' && $pluginArea === 'render')
                    $convertEvtName = 'OnE2GWebLandingpageRender';
                else
                    $convertEvtName = '';

                if ($convertEvtName !== $e2gEvtName)
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
                    include realpath($pluginIndexFile);
                    $out = ob_get_contents();
                    ob_end_clean();
                    return $out;
                } // if (empty($pluginIndexFile))
            } // foreach ($xpldTypes as $pluginType)
        } // foreach ($xpldPlugins as $p_category)
    }

    /**
     * To check the valid decendant of the given &gid parameter
     * @param int    $id            single ID to be checked
     * @param string $staticId     comma separated IDs of valid decendants
     * @return bool  TRUE/FALSE
     */
    private function _checkGidDescendant($id, $staticId) {
        // for global variable: '*' (star), always returns TRUE
        if ($staticId === '*')
            return TRUE;
        if (empty($staticId))
            return FALSE;

        $selectDirs = 'SELECT A.cat_id FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_dirs A, '
                . $this->modx->db->config['table_prefix'] . 'easy2_dirs B '
                . 'WHERE B.cat_id IN (' . $staticId . ') '
                . 'AND A.cat_left BETWEEN B.cat_left AND B.cat_right ';
        $querySelectDirs = mysql_query($selectDirs);
        if (!$querySelectDirs) {
            echo __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectDirs . '<br />';
            return FALSE;
        }
        while ($l = mysql_fetch_assoc($querySelectDirs)) {
            $check[$l['cat_id']] = $l['cat_id'];
        }
        mysql_free_result($querySelectDirs);

        $xpldGids = explode(',', $id);
        foreach ($xpldGids as $gid) {
            if (!$check[$gid] && $staticId !== 1) {
                return FALSE;
//                return $this->modx->sendUnauthorizedPage();
            } elseif (!$check[$gid] && ($staticId === '1')) {
                return FALSE;
//                return $this->modx->sendErrorPage();
            }
        }
        return TRUE;
    }

    /**
     * CHECK THE REAL DESCENDANT OF fid ROOT
     * @param int       $parentIds  parent's IDs in an array
     * @param int       $staticId   the original file's ID
     * @return bool     TRUE | FALSE
     */
    private function _checkFidDecendant($parentIds, $staticId) {
        // for global variable: '*' (star), always returns TRUE
        if ($staticId === '*')
            return TRUE;

        $parentIds = @implode(',', $parentIds);
        $selectFiles = 'SELECT f.id FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_files AS f '
                . 'WHERE dir_id IN (' . $parentIds . ') '
        ;
        $querySelectFiles = mysql_query($selectFiles);
        if (!$querySelectFiles) {
            echo __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectFiles . '<br />';
            return FALSE;
        }
        while ($l = mysql_fetch_assoc($querySelectFiles)) {
            $check[$l['id']] = $l['id'];
        }
        mysql_free_result($querySelectFiles);

        $xpldFids = explode(',', $staticId);
        foreach ($xpldFids as $fid) {
            if (!$check[$fid]) {
                return FALSE;
//                return $this->modx->sendErrorPage();
            }
        }
        return TRUE;
    }

    /**
     * CHECK the valid parent IDs of the &tag parameter
     * @param   string  $dirOrFile dir|file
     * @param   string  $tag from &tag parameter
     * @param   int     $id  id of the specified dir/file
     * @return  bool    TRUE | FALSE
     */
    private function _checkTaggedDirIds($tag, $id=1) {
        $getRequest = array();
        $getRequest['tag'] = $this->sanitizedString($_GET['tag']);

        if (!empty($getRequest['tag']) && $getRequest['tag'] !== $tag) {
            return FALSE;
        }

        $selectTaggedDirs = 'SELECT cat_id FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_dirs ';

        $xpldDirTags = @explode(',', $tag);
        $countDirTags = count($xpldDirTags);
        for ($i = 0; $i < $countDirTags; $i++) {
            if ($i === 0)
                $selectTaggedDirs .= 'WHERE LOWER(cat_tag) LIKE \'%' . $xpldDirTags[$i] . '%\' ';
            else
                $selectTaggedDirs .= 'OR LOWER(cat_tag) LIKE \'%' . $xpldDirTags[$i] . '%\' ';
        }

        $excludeDirWebAccess = $this->checkWebAccess('dir');

        if ($excludeDirWebAccess !== FALSE) {
            $selectTaggedDirs .= 'AND cat_id NOT IN (' . $excludeDirWebAccess . ') ';
        }

        $querySelectTaggedDirs = mysql_query($selectTaggedDirs);
        if (!$querySelectTaggedDirs) {
            echo __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectTaggedDirs . '<br />';
            return FALSE;
        }

        $taggedDirs = array();
        while ($l = mysql_fetch_assoc($querySelectTaggedDirs)) {
            $taggedDirs[] = $l['cat_id'];
        }

        mysql_free_result($querySelectTaggedDirs);

        if (empty($taggedDirs)) {
            return FALSE;
        }

        foreach ($taggedDirs as $taggedDir) {
            if ($this->_checkGidDescendant($id, $taggedDir)) {
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * CHECK the valid parent IDs of the &tag parameter
     * @param string $dirOrFile dir|file
     * @param string $tag from &tag parameter
     * @param int    $id  id of the specified dir/file
     * @return bool TRUE | FALSE
     */
    private function _checkTaggedFileIds($tag, $id) {
        $tag = strtolower($tag);

        $selectTaggedFiles = 'SELECT id FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_files ';

        $xpldFileTags = @explode(',', $tag);
        $countFileTags = count($xpldFileTags);
        for ($i = 0; $i < $countFileTags; $i++) {
            if ($i === 0)
                $selectTaggedFiles .= 'WHERE LOWER(tag) LIKE \'%' . $xpldFileTags[$i] . '%\' ';
            else
                $selectTaggedFiles .= 'OR LOWER(tag) LIKE \'%' . $xpldFileTags[$i] . '%\' ';
        }

        $excludeFileWebAccess = $this->checkWebAccess('file');

        if ($excludeFileWebAccess !== FALSE) {
            $selectTaggedFiles .= ' AND id NOT IN (' . $excludeFileWebAccess . ') ';
        }

        $querySelectTaggedFiles = mysql_query($selectTaggedFiles);
        if (!$querySelectTaggedFiles) {
            echo __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectTaggedFiles . '<br />';
            return FALSE;
        }

        $taggedFiles = array();
        while ($l = mysql_fetch_assoc($querySelectTaggedFiles)) {
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

        if (empty($pubkey)) {
            return ("To use reCAPTCHA you must get an API key from
                <a href='https://www.google.com/recaptcha/admin/create'>
                    https://www.google.com/recaptcha/admin/create
                </a>");
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
            theme : \'' . $this->e2gSnipCfg['recaptcha_theme'] . '\'
                ' . ($this->e2gSnipCfg['recaptcha_theme'] === 'custom' ? ',custom_theme_widget: \''
                        . $this->e2gSnipCfg['recaptcha_theme_custom'] . '\'' : '') . '};
            </script>
            <script type="text/javascript" src="' . $server . '/challenge?k=' . $pubkey . $errorpart . '"></script>
            <noscript>
                <iframe src="' . $server . '/noscript?k=' . $pubkey . $errorpart
                . '" height="300" width="500" frameborder="0"></iframe><br/>
                <textarea name="recaptcha_challenge_field" rows="3" cols="40"></textarea>
                <input type="hidden" name="recaptcha_response_field" value="manual_challenge"/>
            </noscript>';
    }

    /**
     * Previous, Up and Next navigation
     * @return  string  Parsed prev-up-next navigation in the prevUpNext_tpl format
     */
    private function _prevUpNext() {
        if ($this->e2gSnipCfg['nav_prevUpNext'] !== '1'
                || $this->e2gSnipCfg['orderby'] === 'rand()'
                || $this->e2gSnipCfg['cat_orderby'] === 'rand()'
                || (!empty($this->e2gSnipCfg['gid']) && strpos(',', $this->e2gSnipCfg['gid']) !== false) // it must be a single gid
                || isset($this->e2gSnipCfg['static_tag'])
        ) {
            return '';
        }

        // initiate placeholders
        $phs = array(
            'prev_cat_link' => '',
            'prev_cat_name' => '',
            'prev_cat_alias' => '',
            'prev_title' => '',
            'prev_cat_symbol' => '',
            'prev_cat_permalink' => '',
            'prev_link' => '',
            'up_cat_link' => '',
            'up_cat_name' => '',
            'up_cat_alias' => '',
            'up_title' => '',
            'up_cat_symbol' => '',
            'up_cat_permalink' => '',
            'up_link' => '',
            'next_cat_link' => '',
            'next_cat_name' => '',
            'next_cat_alias' => '',
            'next_title' => '',
            'next_cat_symbol' => '',
            'next_cat_permalink' => '',
            'next_link' => '',
        );

        $dynamicKey = !empty($this->e2gSnipCfg['static_tag']) ? $this->e2gSnipCfg['tag'] : $this->e2gSnipCfg['gid'];
        $field = $this->e2gSnipCfg['nav_prevUpNextTitle'] === 'cat_alias' ? 'cat_alias' : 'cat_name';
        $check = FALSE;
        // Previous
        if (isset($this->e2gSnipCfg['static_tag'])) {
            $prev = $this->_getSiblingInfo('tag', $dynamicKey, $field, -1);
        } else {
            $prev = $this->_getSiblingInfo(NULL, $dynamicKey, $field, -1);
        }
        if (!empty($prev)
                && $this->_checkGidDescendant($prev['cat_id'], $this->e2gSnipCfg['static_gid'])
        ) {
            $check = TRUE;
            $prev['cat_link'] = $this->modx->makeUrl(
                            $this->modx->documentIdentifier
                            , $this->modx->aliases
                            , 'sid=' . $this->e2gSnipCfg['e2g_static_instances'])
                    . '&amp;gid=' . $prev['cat_id']
            ;
            if (isset($this->e2gSnipCfg['tag'])) {
                $prev['cat_link'] .= '&amp;tag=' . $this->e2gSnipCfg['static_tag'];
            }

            // setup the placeholders
            $phs['prev_cat_link'] = $prev['cat_link'];
            $phs['prev_cat_name'] = $prev['cat_name'];
            $phs['prev_cat_alias'] = $prev['cat_alias'];
            $phs['prev_title'] = !empty($prev[$this->e2gSnipCfg['nav_prevUpNextTitle']]) ?
                    $prev[$this->e2gSnipCfg['nav_prevUpNextTitle']] :
                    $prev['cat_name'];

            $phs['prev_cat_symbol'] = $this->e2gSnipCfg['nav_prevSymbol'];
            if (isset($this->e2gSnipCfg['static_tag'])) {
                $phs['prev_cat_permalink'] = '#' . $this->e2gSnipCfg['e2g_static_instances']
                        . '_' . $this->e2gSnipCfg['static_tag'];
            } else {
                $phs['prev_cat_permalink'] = '#' . $this->e2gSnipCfg['e2g_static_instances']
                        . '_' . $prev['cat_id'];
            }

            // complete link
            $phs['prev_link'] = '<a href="' . $phs['prev_cat_link'] . $phs['prev_cat_permalink'] . '">'
                    . $phs['prev_cat_symbol'] . ' ' . $phs['prev_title']
                    . '</a>';
        }

        // Up
        // if the gallery is the parent ID of the snippet call, disable the up navigation
        if ($this->e2gSnipCfg['static_gid'] === $this->e2gSnipCfg['gid']) {
            $up = array();
        } elseif (isset($this->e2gSnipCfg['static_tag'])) {
            $up = $this->_getParentInfo($dynamicKey, $field, 'tag');
        } else {
            $up = $this->_getParentInfo($dynamicKey, $field);
        }

        if (!empty($up)
                && $this->_checkGidDescendant($up['cat_id'], $this->e2gSnipCfg['static_gid'])
        ) {
            $check = TRUE;
            $up['cat_link'] = $this->modx->makeUrl(
                            $this->modx->documentIdentifier
                            , $this->modx->aliases
                            , 'sid=' . $this->e2gSnipCfg['e2g_static_instances'])
                    . '&amp;gid=' . $up['cat_id']
            ;
            if (isset($this->e2gSnipCfg['tag'])) {
                $up['cat_link'] .= '&amp;tag=' . $this->e2gSnipCfg['static_tag'];
            }

            $phs['up_cat_link'] = $up['cat_link'];
            $phs['up_cat_name'] = $up['cat_name'];
            $phs['up_cat_alias'] = $up['cat_alias'];
            $phs['up_title'] = !empty($up[$this->e2gSnipCfg['nav_prevUpNextTitle']]) ?
                    $up[$this->e2gSnipCfg['nav_prevUpNextTitle']] :
                    $up['cat_name'];
            $phs['up_cat_symbol'] = $this->e2gSnipCfg['nav_upSymbol'];
            if (isset($this->e2gSnipCfg['static_tag'])) {
                $phs['up_cat_permalink'] = '#' . $this->e2gSnipCfg['e2g_static_instances']
                        . '_' . $this->e2gSnipCfg['static_tag'];
            } else {
                $phs['up_cat_permalink'] = '#' . $this->e2gSnipCfg['e2g_static_instances']
                        . '_' . $up['cat_id'];
            }

            // complete link
            $phs['up_link'] = '<a href="' . $phs['up_cat_link'] . $phs['up_cat_permalink'] . '">'
                    . $phs['up_cat_symbol'] . ' ' . $phs['up_title']
                    . '</a>';
        }

        // Next
        if (isset($this->e2gSnipCfg['static_tag'])) {
            $next = $this->_getSiblingInfo('tag', $dynamicKey, $field, 1);
        } else {
            $next = $this->_getSiblingInfo(NULL, $dynamicKey, $field, 1);
        }
        if (!empty($next)
                && $this->_checkGidDescendant($next['cat_id'], $this->e2gSnipCfg['static_gid'])
        ) {
            $check = TRUE;
            $next['cat_link'] = $this->modx->makeUrl(
                            $this->modx->documentIdentifier
                            , $this->modx->aliases
                            , 'sid=' . $this->e2gSnipCfg['e2g_static_instances'])
                    . '&amp;gid=' . $next['cat_id']
            ;
            if (isset($this->e2gSnipCfg['tag'])) {
                $next['cat_link'] .= '&amp;tag=' . $this->e2gSnipCfg['static_tag'];
            }

            $phs['next_cat_link'] = $next['cat_link'];
            $phs['next_cat_name'] = $next['cat_name'];
            $phs['next_cat_alias'] = $next['cat_alias'];
            $phs['next_title'] = !empty($next[$this->e2gSnipCfg['nav_prevUpNextTitle']]) ?
                    $next[$this->e2gSnipCfg['nav_prevUpNextTitle']] :
                    $next['cat_name'];

            $phs['next_cat_symbol'] = $this->e2gSnipCfg['nav_nextSymbol'];
            if (isset($this->e2gSnipCfg['static_tag'])) {
                $phs['next_cat_permalink'] = '#' . $this->e2gSnipCfg['e2g_static_instances']
                        . '_' . $this->e2gSnipCfg['static_tag'];
            } else {
                $phs['next_cat_permalink'] = '#' . $this->e2gSnipCfg['e2g_static_instances']
                        . '_' . $next['cat_id'];
            }

            // complete link
            $phs['next_link'] = '<a href="' . $phs['next_cat_link'] . $phs['next_cat_permalink'] . '">'
                    . $phs['next_title'] . ' ' . $phs['next_cat_symbol']
                    . '</a>';
        }

        if ($check)
            return $this->filler($this->getTpl('prevUpNext_tpl'), $phs);
        else
            return '';
    }

    /**
     * Get parent directory information
     * @param int       $dynamicId  changing ID from $_GET variable
     * @param string    $field      database field
     * @param string    $trigger    catch the 'tag' trigger
     * @return array    parent's info in an array, or FALSE
     */
    private function _getParentInfo($dynamicId, $field = 'cat_id', $trigger=NULL) {
        if (empty($dynamicId))
            return FALSE;
        $selectParent = 'SELECT * FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_dirs ';

        if ($dynamicId !== '*') {
            if ($trigger === 'tag') {
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

        $parent = array();
        while ($row = mysql_fetch_assoc($queryParent)) {
            $parent['cat_id'] = $row['parent_id'];
        }

        if (empty($parent['cat_id'])) {
            return FALSE;
        }
        $parent['cat_name'] = $this->getDirInfo($parent['cat_id'], 'cat_name');
        if ($field !== 'cat_name')
            $parent[$field] = $this->getDirInfo($parent['cat_id'], $field);

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
     * @return array    Sibling's info in an array, or FALSE
     */
    private function _getSiblingInfo($trigger, $dynamicId, $field, $siblingCounter) {
        if (empty($dynamicId)) {
            return FALSE;
        }
        $selectChildren = 'SELECT a.* FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_dirs a '
                . 'WHERE a.parent_id IN ('
                . 'SELECT b.parent_id FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_dirs b '
                . 'WHERE ';

        $excludeDirWebAccess = $this->checkWebAccess('dir');
        if ($excludeDirWebAccess !== FALSE) {
            $selectChildren .= 'b.cat_id NOT IN (' . $excludeDirWebAccess . ') AND ';
        }

        if ($dynamicId !== '*') {
            if ($trigger === 'tag') {
                $selectChildren .= 'b.cat_tag LIKE \'%' . $dynamicId . '%\' AND ';
            } else {
                $selectChildren .= 'b.cat_id IN (' . $dynamicId . ') AND ';
            }
        }

        $selectChildren .= 'b.cat_visible = 1 ) AND a.cat_visible = 1 ';

        if ($excludeDirWebAccess !== FALSE) {
            $selectChildren .= 'AND a.cat_id NOT IN (' . $excludeDirWebAccess . ') ';
        }

        if ($trigger === 'tag' && $dynamicId !== '*') {
            $selectChildren .= 'AND a.cat_tag LIKE \'%' . $dynamicId . '%\' ';
        }

        $selectChildren .= 'AND (SELECT count(F.id) FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_files F '
                . 'WHERE F.dir_id IN '
                . '(SELECT c.cat_id FROM '
                . $this->modx->db->config['table_prefix'] . 'easy2_dirs c, '
                . $this->modx->db->config['table_prefix'] . 'easy2_dirs d '
                . 'WHERE (d.cat_id = a.cat_id '
                . 'AND c.cat_left >= d.cat_left '
                . 'AND c.cat_right <= d.cat_right '
                . 'AND c.cat_level >= d.cat_level '
                . 'AND c.cat_visible = 1)'
                . ')'
                . ')<>0 ';

        $selectChildren .= 'ORDER BY a.' . $this->e2gSnipCfg['cat_orderby'] . ' ' . $this->e2gSnipCfg['cat_order'];

        $queryChildren = mysql_query($selectChildren);
        if (!$queryChildren) {
            echo __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectChildren . '<br />';
            return FALSE;
        }

        $siblings = array();
        while ($row = mysql_fetch_assoc($queryChildren)) {
            $siblings['cat_id'][] = $row['cat_id'];
            $siblings['cat_tag'][] = $row['cat_tag'];
            $siblings['cat_name'][] = $row['cat_name'];
            if ($field !== 'cat_id' && $field !== 'cat_tag' && $field !== 'cat_name')
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
            if ($trigger === 'tag') {
                if ($siblings['cat_id'][$i] === $this->e2gSnipCfg['gid']) {
                    $thesibling['cat_id'] = $siblings['cat_id'][$j];
                    $thesibling['cat_tag'] = $siblings['cat_tag'][$j];
                    $thesibling['cat_name'] = $siblings['cat_name'][$j];
                    if ($field !== 'cat_id' && $field !== 'cat_tag' && $field !== 'cat_name')
                        $thesibling[$field] = $siblings[$field][$j];
                }
            } else {
                if ($siblings['cat_id'][$i] === $dynamicId) {
                    $thesibling['cat_id'] = $siblings['cat_id'][$j];
                    $thesibling['cat_name'] = $siblings['cat_name'][$j];
                    if ($field !== 'cat_id' && $field !== 'cat_tag' && $field !== 'cat_name')
                        $thesibling[$field] = $siblings[$field][$j];
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
     * Strips HTML tags
     * @param   string  $string
     * @param   array   $strippedTags
     */
    private function _stripHTMLTags($string, $strippedTags=array('p', 'div', 'span')) {
        if ($this->e2gSnipCfg['strip_html_tags'] === '0')
            return $string;

        foreach ($strippedTags as $tag) {
            $string = preg_replace('~\<(.*?)' . $tag . '(.*?)\>~', '', $string);
        }
        return $string;
    }

    /**
     * Centralized the SQL statements for directories fetching
     * @param string    $select     SELECT statement
     * @param string    $prefix     field's prefix
     * @return string   The complete SQL's statement with additional parameters
     */
    private function _dirSqlStatement($select, $prefix = NULL) {
        $excludeDirWebAccess = $this->checkWebAccess('dir');

        $prefixDot = '';
        if (isset($prefix))
            $prefixDot = $prefix . '.';

        if (isset($this->e2gSnipCfg['static_tag'])) {
            $dirSqlStatement = 'SELECT ' . $select . ' FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_dirs WHERE ';

            // OPEN the selected tagged folder
            if (isset($this->_sanitizedGets['gid'])
                    && $this->e2gSnipCfg['static_tag'] === $this->e2gSnipCfg['tag']
                    && $this->_checkTaggedDirIds($this->e2gSnipCfg['tag'], $this->_sanitizedGets['gid'])) {
                $dirSqlStatement .= 'parent_id IN (' . $this->_sanitizedGets['gid'] . ') AND ';
            } else {
                // the selected tag of multiple tags on the same page
                if ($this->e2gSnipCfg['static_tag'] === $this->e2gSnipCfg['tag']) {
                    $multipleTags = @explode(',', $this->e2gSnipCfg['tag']);
                }
                // the UNselected tag of multiple tags on the same page
                else {
                    $multipleTags = @explode(',', $this->e2gSnipCfg['static_tag']);
                }

                $countMultipleTags = count($multipleTags);
                for ($i = 0; $i < $countMultipleTags; $i++) {
                    if ($i === 0)
                        $dirSqlStatement .= 'cat_tag LIKE \'%' . $multipleTags[$i] . '%\' ';
                    else
                        $dirSqlStatement .= 'OR cat_tag LIKE \'%' . $multipleTags[$i] . '%\' ';
                }
                $dirSqlStatement .= 'AND ';
            }

            if ($excludeDirWebAccess !== FALSE) {
                $dirSqlStatement .= 'cat_id NOT IN (' . $excludeDirWebAccess . ') AND ';
            }

            $dirSqlStatement .= 'cat_visible=1 ';
        }
        // original &gid parameter
        else {
            $dirSqlStatement = 'SELECT ' . $select . ' FROM ' . $this->modx->db->config['table_prefix']
                    . 'easy2_dirs AS ' . $prefix . ' WHERE ';

            if ($this->e2gSnipCfg['gid'] !== '*') {
                if ($this->_checkGidDescendant((isset($this->_sanitizedGets['gid']) ?
                                        $this->_sanitizedGets['gid'] :
                                        $this->e2gSnipCfg['gid']), $this->e2gSnipCfg['static_gid']) === TRUE) {
                    $dirSqlStatement .= $prefixDot . 'parent_id IN (' . $this->e2gSnipCfg['gid'] . ') ';
                } else {
                    $dirSqlStatement .= $prefixDot . 'parent_id IN (' . $this->e2gSnipCfg['static_gid'] . ') ';
                }
                $dirSqlStatement .= 'AND ';
            }

            if (isset($this->e2gSnipCfg['where_dir'])) {
                $where = $this->_whereClause($this->e2gSnipCfg['where_dir'], $prefix);
                if (!$where) {
                    return FALSE;
                }
                $dirSqlStatement .= $where . ' AND ';
            }

            if ($excludeDirWebAccess !== FALSE) {
                $dirSqlStatement .= $prefixDot . 'cat_id NOT IN (' . $excludeDirWebAccess . ') AND ';
            }

            // ddim -- wrapping children folders
            $dirSqlStatement .=
                    '(SELECT count(*) FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_files F WHERE F.dir_id IN '
                    . '('
                    . 'SELECT A.cat_id FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_dirs A, '
                    . $this->modx->db->config['table_prefix'] . 'easy2_dirs B WHERE '
                    . '('
                    . 'B.cat_id = ' . $prefixDot . 'cat_id '
                    . 'AND A.cat_left >= B.cat_left '
                    . 'AND A.cat_right <= B.cat_right '
                    . 'AND A.cat_level >= B.cat_level '
                    . 'AND A.cat_visible = 1'
                    . ')'
                    . ')'
                    . ')<>0 AND ';
            $dirSqlStatement .= $prefixDot . 'cat_visible=1 ';
        }

        return $dirSqlStatement;
    }

    /**
     * Centralized the SQL statements for files fetching
     * @param string    $select         SELECT statement
     * @param string    $allowedRatio   allowed ratio range
     * @param int       $dirId          gallery ID
     * @return string   The complete SQL's statement with additional parameters
     */
    private function _fileSqlStatement($select, $allowedRatio = NULL, $dirId=NULL) {
        $gid = !empty($dirId) ? $dirId : $this->e2gSnipCfg['gid'];
        $staticGid = !empty($dirId) ? $dirId : $this->e2gSnipCfg['static_gid'];
        $this->e2gSnipCfg['fid'] = $this->e2gSnipCfg['fid'];
        $this->e2gSnipCfg['static_fid'] = $this->e2gSnipCfg['static_fid'];

        if (!empty($allowedRatio) && $allowedRatio !== 'all') {
            /**
             * Filtering the slideshow size ratio
             */
            // create min-max slideshow width/height ratio
            $xpldRatio = explode('-', $allowedRatio);

            $minRatio = trim($xpldRatio[0]);
            $minRatio = str_replace(',', '.', $minRatio);
            $minRatio = @explode('.', $minRatio);
            $minRatio = @implode('.', array(intval($minRatio[0]), intval($minRatio[1])));

            $maxRatio = trim($xpldRatio[1]);
            $maxRatio = str_replace(',', '.', $maxRatio);
            $maxRatio = @explode('.', $maxRatio);
            $maxRatio = @implode('.', array(intval($maxRatio[0]), intval($maxRatio[1])));
        }

        $fileSqlStatement = 'SELECT ' . $select . ' FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_files WHERE ';

        if (isset($this->e2gSnipCfg['static_tag'])) {
            // OPEN the selected tagged folder
            if (isset($this->_sanitizedGets['gid'])
                    && $this->e2gSnipCfg['static_tag'] === $this->e2gSnipCfg['tag']
                    && $this->_checkTaggedDirIds($this->e2gSnipCfg['tag'], $this->_sanitizedGets['gid'])
            ) {
                $fileSqlStatement .= 'dir_id IN (' . $this->_sanitizedGets['gid'] . ') AND ';
            } else {
                // the selected tag of multiple tags on the same page
                if ($this->e2gSnipCfg['static_tag'] === $this->e2gSnipCfg['tag']) {
                    $multipleTags = @explode(',', $this->e2gSnipCfg['tag']);
                }
                // the UNselected tag of multiple tags on the same page
                else {
                    $multipleTags = @explode(',', $this->e2gSnipCfg['static_tag']);
                }
                $countMultipleTags = count($multipleTags);
                for ($i = 0; $i < $countMultipleTags; $i++) {
                    if ($i === 0)
                        $fileSqlStatement .= 'tag LIKE \'%' . $multipleTags[$i] . '%\' ';
                    else
                        $fileSqlStatement .= 'OR tag LIKE \'%' . $multipleTags[$i] . '%\' ';
                }
                $fileSqlStatement .= 'AND ';
            }
        } else {
            if ($gid !== '*') {
                if (!empty($this->e2gSnipCfg['fid'])
                        && $gid === (!empty($staticGid) ? $staticGid : NULL)
                ) {
                    $fileSqlStatement .= 'id IN (' . $this->e2gSnipCfg['fid'] . ') ';
                }
                if (!empty($this->e2gSnipCfg['fid']) && !empty($gid)) {
//                if (!empty($this->e2gSnipCfg['fid']) && !empty($gid) && empty($this->_sanitizedGets['gid'])) {
                    $fileSqlStatement .= 'OR ';
                }
                if (!empty($gid)) {
                    if ($this->_checkGidDescendant((isset($this->_sanitizedGets['gid']) ? $this->_sanitizedGets['gid'] : $gid), $staticGid) === TRUE) {
                        $fileSqlStatement .= 'dir_id IN (' . $gid . ') ';
                    } else {
                        $fileSqlStatement .= 'dir_id IN (' . $staticGid . ') ';
                    }
                }
                $fileSqlStatement .= 'AND ';
            }
            if (isset($this->e2gSnipCfg['where_file'])) {
                $where = $this->_whereClause($this->e2gSnipCfg['where_file']);
                if (!$where) {
                    return FALSE;
                }
                $fileSqlStatement .= $where . ' AND ';
            }
        }

//        $excludeDirWebAccess = $this->checkWebAccess('dir');
//        if ($excludeDirWebAccess !== FALSE) {
//            $dirSqlStatement .= 'dir_id NOT IN (' . $excludeDirWebAccess . ') AND ';
//        }

        $excludeFileWebAccess = $this->checkWebAccess('file');
        if ($excludeFileWebAccess !== FALSE) {
            $fileSqlStatement .= 'id NOT IN (' . $excludeFileWebAccess . ') AND ';
        }

        if (!empty($allowedRatio) && $allowedRatio != 'all') {
            $fileSqlStatement .= 'width/height >=\'' . floatval($minRatio) . '\' AND width/height<=\'' . floatval($maxRatio) . '\' AND ';
        }

        $fileSqlStatement .= 'status = 1 ';

        return $fileSqlStatement;
    }

    /**
     * Formating the pagination
     * @param mixed     $pages  The variable of page contents, number, and links.
     * @return string   The formatted pagination
     */
    private function _paginationFormat($pages) {
        $pagination = '';
        if ($pages['totalPageNum'] > 1) {
            //previous button
            if ($pages['currentPage'] > 1) {
                $pagination.= '<a href="' . $pages['previousLink'][$pages['currentPage']] . '">'
                        . $this->e2gSnipCfg['pagination_text_previous']
                        . '</a>';
            } else {
                $pagination.= '<span class="disabled">' . $this->e2gSnipCfg['pagination_text_previous'] . '</span>';
            }

            // no split
            if ($pages['totalPageNum'] <= ($this->e2gSnipCfg['pagination_spread'] + ($this->e2gSnipCfg['pagination_adjacents'] * 2))) {
                for ($i = 1; $i <= $pages['totalPageNum']; $i++) {
                    if ($i === intval($pages['currentPage']))
                        $pagination.= '<b>' . $i . '</b>';
                    else
                        $pagination.= $pages['pages'][$i];
                }
            } else {
                // start splitting
                if ($pages['currentPage'] < ($this->e2gSnipCfg['pagination_adjacents'] + floor($this->e2gSnipCfg['pagination_spread'] / 2) + 2)) {
                    for ($i = 1; $i < ($this->e2gSnipCfg['pagination_adjacents'] + $this->e2gSnipCfg['pagination_spread'] + 1); $i++) {
                        if ($i === intval($pages['currentPage'])) {
                            $pagination.= '<b>' . $i . '</b>';
                        } else {
                            $pagination.= $pages['pages'][$i];
                        }
                    }
                    $pagination.= $this->e2gSnipCfg['pagination_splitter'];
                    // the last pages
                    for ($i = ($pages['totalPageNum'] - $this->e2gSnipCfg['pagination_adjacents'] + 1); $i <= $pages['totalPageNum']; $i++) {
                        $pagination.= $pages['pages'][$i];
                    }
                } elseif ($pages['currentPage'] >= ($this->e2gSnipCfg['pagination_adjacents'] + floor($this->e2gSnipCfg['pagination_spread'] / 2) + 1) // front
                        && $pages['currentPage'] < ($pages['totalPageNum'] - ($this->e2gSnipCfg['pagination_adjacents'] + ceil($this->e2gSnipCfg['pagination_spread'] / 2) - 1)) // end
                ) {
                    $pagination.= $pages['pages'][1];
                    $pagination.= $pages['pages'][2];
                    $pagination.= $this->e2gSnipCfg['pagination_splitter'];
                    for ($i = ($pages['currentPage'] - floor($this->e2gSnipCfg['pagination_spread'] / 2)); $i <= $pages['currentPage'] + floor($this->e2gSnipCfg['pagination_spread'] / 2); $i++) {
                        if ($i === intval($pages['currentPage'])) {
                            $pagination.= '<b>' . $i . '</b>';
                        } else {
                            $pagination.= $pages['pages'][$i];
                        }
                    }
                    $pagination.= $this->e2gSnipCfg['pagination_splitter'];
                    // the last pages
                    for ($i = ($pages['totalPageNum'] - $this->e2gSnipCfg['pagination_adjacents'] + 1); $i <= $pages['totalPageNum']; $i++) {
                        $pagination.= $pages['pages'][$i];
                    }
                } else {
                    $pagination.= $pages['pages'][1];
                    $pagination.= $pages['pages'][2];
                    $pagination.= $this->e2gSnipCfg['pagination_splitter'];

                    for ($i = $pages['totalPageNum'] - ($this->e2gSnipCfg['pagination_adjacents'] + $this->e2gSnipCfg['pagination_spread'] - 1); $i <= $pages['totalPageNum']; $i++) {
                        if ($i === intval($pages['currentPage'])) {
                            $pagination.= '<b>' . $i . '</b>';
                        } else {
                            $pagination.= $pages['pages'][$i];
                        }
                    }
                }
            }

            //next button
            if ($pages['currentPage'] < $pages['totalPageNum']) {
                $pagination.= '<a href="' . $pages['nextLink'][$pages['currentPage']] . '">'
                        . $this->e2gSnipCfg['pagination_text_next']
                        . '</a>';
            } else {
                $pagination.= '<span class="disabled">' . $this->e2gSnipCfg['pagination_text_next'] . '</span>';
            }
        }
        return $pagination;
    }

    /**
     * Decoding returning text
     * @param   string  $text   string
     * @return  string  decoded string
     */
    private function _decodeText($text) {
        $encodedText = htmlspecialchars_decode($text, ENT_QUOTES);
        $encodedText = html_entity_decode($encodedText, ENT_QUOTES, 'UTF-8');
        $encodedText = $this->_stripHTMLTags($encodedText);
        return $encodedText;
    }

}