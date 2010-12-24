<?php
if (IN_MANAGER_MODE != 'true')
    die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
?>
<form action="<?php echo $index; ?>&amp;act=save_config" method="post">
    <input type="submit" value="<?php echo $lng['save']; ?>" /> &nbsp; &nbsp; &nbsp;
    <input name="clean_cache" type="checkbox" value="1" style="border:0" /> <?php echo $lng['clean_cache']; ?>
    <br /><br />
    <div class="tab-pane" id="tabConfigPane">
        <script type="text/javascript">
            tpConfig = new WebFXTabPane(document.getElementById('tabConfigPane'));
        </script>
        <div class="tab-page" id="tabGeneralSettings">
            <h2 class="tab"><?php echo $lng['general']; ?></h2>
            <script type="text/javascript">
                tpConfig.addTabPage( document.getElementById( 'tabGeneralSettings') );
            </script>
            <table cellspacing="0" cellpadding="2" width="100%">
                <tr class="gridAltItem">
                    <td width="140"><b><?php echo $lng['path']; ?>:</b></td>
                    <td><input name="dir" type="text" value="<?php echo $e2g['dir']; ?>" /></td>
                </tr>
                <tr>
                    <td colspan="2"><?php echo htmlspecialchars_decode($lng['dir_cfg_desc']); ?></td>
                </tr>
                <tr class="gridAltItem">
                    <td><b><?php echo $lng['e2g_encode']; ?>:</b></td>
                    <td>
                        <select name="e2g_encode">
                            <?php
                            include (E2G_MODULE_PATH . 'includes/configs/config.encode.easy2gallery.php');
                            foreach ($e2gEncodes as $e2gEncode) {
                            ?>
                                <option value="<?php echo $e2gEncode['value']; ?>"<?php echo ($e2g['e2g_encode'] == $e2gEncode['value'] ? ' selected="selected"' : ''); ?>><?php echo $e2gEncode['lng']; ?></option>
                            <?php } ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><?php echo htmlspecialchars_decode($lng['e2g_encode_cfg_desc']); ?></td>
                </tr>
                <tr>
                    <td colspan="2"><br /><b class="success" style="font-size:120%"><?php echo $lng['mod_options']; ?></b></td>
                </tr>
                <tr class="gridAltItem">
                    <td><b><?php echo $lng['e2g_debug']; ?>:</b></td>
                    <td>
                        <input type="radio" name="e2g_debug" value="0" <?php echo ($e2g['e2g_debug'] == '0' ? 'checked="checked"' : ''); ?> /> <?php echo $lng['no']; ?>
                        <input type="radio" name="e2g_debug" value="1" <?php echo ($e2g['e2g_debug'] == '1' ? 'checked="checked"' : ''); ?> /> <?php echo $lng['yes']; ?>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><?php echo htmlspecialchars_decode($lng['e2g_debug_cfg_desc']); ?></td>
                </tr>
                <tr class="gridAltItem">
                    <td><b><?php echo $lng['mod_view']; ?>:</b></td>
                    <td>
                        <input type="radio" name="mod_view" value="list" <?php echo ($e2g['mod_view'] == 'list' ? 'checked="checked"' : ''); ?> /> <?php echo $lng['list']; ?>
                        <input type="radio" name="mod_view" value="thumbnails" <?php echo ($e2g['mod_view'] == 'thumbnails' ? 'checked="checked"' : ''); ?> /> <?php echo $lng['thumbnails']; ?>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><?php echo htmlspecialchars_decode($lng['mod_view_cfg_desc']); ?></td>
                </tr>
                <tr class="gridAltItem">
                    <td><b><?php echo $lng['mod_date_format']; ?>:</b></td>
                    <td>
                        <input name="mod_date_format" size="30" type="text" value="<?php echo $e2g['mod_date_format']; ?>" />
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><?php echo htmlspecialchars_decode($lng['mod_date_format_cfg_desc']); ?></td>
                </tr>
                <tr class="gridAltItem">
                    <td><b><?php echo $lng['tinymce_path']; ?>:</b></td>
                    <td>
                        <input name="tinymcefolder" size="50" type="text" value="<?php echo $e2g['tinymcefolder']; ?>" />
                        /tiny_mce.js
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><?php echo htmlspecialchars_decode($lng['tinymce_path_desc']); ?></td>
                </tr>
                <tr>
                    <td colspan="2"><br /><b class="success" style="font-size:120%"><?php echo $lng['snip_options']; ?></b></td>
                </tr>
                <tr class="gridAltItem">
                    <td><b><?php echo $lng['e2g_wrapper_cfg']; ?>:</b></td>
                    <td>
                        <input name="e2g_wrapper" size="30" type="text" value="<?php echo $e2g['e2g_wrapper']; ?>" />
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><?php echo htmlspecialchars_decode($lng['e2g_wrapper_cfg_desc']); ?></td>
                </tr>
                <tr class="gridAltItem">
                    <td><b><?php echo $lng['strip_html_tags_cfg']; ?>:</b></td>
                    <td>
                        <input type="radio" name="strip_html_tags" value="0" <?php echo ($e2g['strip_html_tags'] == '0' ? 'checked="checked"' : ''); ?> /> <?php echo $lng['no']; ?>
                        <input type="radio" name="strip_html_tags" value="1" <?php echo ($e2g['strip_html_tags'] == '1' ? 'checked="checked"' : ''); ?> /> <?php echo $lng['yes'] . ' (' . $lng['recommended'] . ')'; ?>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><?php echo htmlspecialchars_decode($lng['strip_html_tags_cfg_desc']); ?></td>
                </tr>
                <!--tr class="gridAltItem">
                    <td><b><?php echo $lng['ie6_allow_cfg']; ?>:</b></td>
                    <td>
                        <input type="radio" name="ie6_allow" value="0" <?php echo ($e2g['ie6_allow'] == '0' ? 'checked="checked"' : ''); ?> /> <?php echo $lng['no']; ?>
                        <input type="radio" name="ie6_allow" value="1" <?php echo ($e2g['ie6_allow'] == '1' ? 'checked="checked"' : ''); ?> /> <?php echo $lng['yes'] . ' (' . $lng['recommended'] . ')'; ?>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><?php echo htmlspecialchars_decode($lng['ie6_allow_cfg_desc']); ?></td>
                </tr-->
            </table>
        </div>
        <div class="tab-page" id="tabImagesSettings">
            <h2 class="tab"><?php echo $lng['settings_img']; ?></h2>
            <script type="text/javascript">
                tpConfig.addTabPage( document.getElementById( 'tabImagesSettings') );
            </script>
            <table cellspacing="0" cellpadding="2" width="100%">
                <tr class="gridAltItem">
                    <td width="180"><b><?php echo $lng['img_src_cfg']; ?>:</b></td>
                    <td><span style="color:green;">&amp;img_src=</span>
                        <select name="img_src">
                            <option value="original"<?php echo ($e2g['img_src'] == 'original' ? ' selected="selected"' : ''); ?>>original (<?php echo $lng['original']; ?>)</option>
                            <option value="generated"<?php echo ($e2g['img_src'] == 'generated' ? ' selected="selected"' : ''); ?>>generated (<?php echo $lng['generated']; ?>)</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><?php echo htmlspecialchars_decode($lng['img_src_cfg_desc']); ?></td>
                </tr>
                <tr class="gridAltItem">
                    <td><b><?php echo $lng['w']; ?>:</b></td>
                    <td><input name="maxw" type="text" value="<?php echo $e2g['maxw']; ?>" size="4" /></td>
                </tr>
                <tr>
                    <td colspan="2"><?php echo htmlspecialchars_decode($lng['w_cfg_desc']); ?></td>
                </tr>
                <tr class="gridAltItem">
                    <td><b><?php echo $lng['h']; ?>:</b></td>
                    <td><input name="maxh" type="text" value="<?php echo $e2g['maxh']; ?>" size="4" /></td>
                </tr>
                <tr>
                    <td colspan="2"><?php echo htmlspecialchars_decode($lng['h_cfg_desc']); ?></td>
                </tr>
                <tr class="gridAltItem">
                    <td><b><?php echo $lng['img_orientation_follow']; ?>:</b></td>
                    <td>
                        <input type="radio" name="resize_orientated_img" value="0"<?php echo ($e2g['resize_orientated_img'] == '0' ? 'checked="checked"' : ''); ?> /> <?php echo $lng['no']; ?>
                        <input type="radio" name="resize_orientated_img" value="1"<?php echo ($e2g['resize_orientated_img'] == '1' ? 'checked="checked"' : ''); ?> /> <?php echo $lng['yes']; ?>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><?php echo htmlspecialchars_decode($lng['img_orientation_follow_cfg_desc']); ?></td>
                </tr>
                <tr class="gridAltItem">
                    <td><b><?php echo $lng['thq']; ?>:</b></td>
                    <td><input name="maxthq" type="text" value="<?php echo $e2g['maxthq']; ?>" size="3" /></td>
                </tr>
                <tr>
                    <td colspan="2"><?php echo htmlspecialchars_decode($lng['thq_cfg_desc']); ?></td>
                </tr>
                <tr class="gridAltItem">
                    <td><b><?php echo $lng['img_old_resize']; ?>:</b></td>
                    <td>
                        <input type="radio" name="resize_old_img" value="0" <?php echo ($e2g['resize_old_img'] == '0' ? 'checked="checked"' : ''); ?> /> <?php echo $lng['no']; ?>
                        <input type="radio" name="resize_old_img" value="1" <?php echo ($e2g['resize_old_img'] == '1' ? 'checked="checked"' : ''); ?> /> <?php echo $lng['yes']; ?>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><?php echo htmlspecialchars_decode($lng['img_old_resize_cfg_desc']); ?></td>
                </tr>
            </table>
        </div>
        <div class="tab-page" id="tabThumbnailSettings">
            <h2 class="tab"><?php echo $lng['settings_thumb']; ?></h2>
            <script type="text/javascript">
                tpConfig.addTabPage( document.getElementById( 'tabThumbnailSettings') );
            </script>

            <div class="tab-pane" id="tabThumbnailConfigPane">
                <script type="text/javascript">
                    tpThumbnailConfig = new WebFXTabPane(document.getElementById('tabThumbnailConfigPane'));
                </script>
                <div class="tab-page" id="tabModOptionSettings">
                    <h2 class="tab"><?php echo $lng['mod_options']; ?></h2>
                    <script type="text/javascript">
                        tpThumbnailConfig.addTabPage( document.getElementById( 'tabModOptionSettings') );
                    </script>

                    <table cellspacing="0" cellpadding="2" width="100%">
                        <tr class="gridAltItem">
                            <td width="180"><b><?php echo $lng['w']; ?>:</b></td>
                            <td><input name="mod_w" type="text" value="<?php echo $e2g['mod_w']; ?>" size="4" /></td>
                        </tr>
                        <tr>
                            <td colspan="2"><?php echo htmlspecialchars_decode($lng['w_thumb_cfg_desc']); ?></td>
                        </tr>
                        <tr class="gridAltItem">
                            <td><b><?php echo $lng['h']; ?>:</b></td>
                            <td><input name="mod_h" type="text" value="<?php echo $e2g['mod_h']; ?>" size="4" /></td>
                        </tr>
                        <tr>
                            <td colspan="2"><?php echo htmlspecialchars_decode($lng['h_thumb_cfg_desc']); ?></td>
                        </tr>
                        <tr class="gridAltItem">
                            <td><b><?php echo $lng['thq']; ?>:</b></td>
                            <td><input name="mod_thq" type="text" value="<?php echo $e2g['mod_thq']; ?>" size="3" /></td>
                        </tr>
                        <tr>
                            <td colspan="2"><?php echo htmlspecialchars_decode($lng['thq_cfg_desc']); ?></td>
                        </tr>
                        <tr class="gridAltItem">
                            <td><b><?php echo $lng['resize_type']; ?>:</b></td>
                            <td>
                                <select name="mod_resize_type">
                                    <option value="inner"<?php echo ($e2g['mod_resize_type'] == 'inner' ? ' selected="selected"' : ''); ?>><?php echo $lng['inner']; ?></option>
                                    <option value="shrink"<?php echo ($e2g['mod_resize_type'] == 'shrink' ? ' selected="selected"' : ''); ?>><?php echo $lng['shrink']; ?></option>
                                    <option value="resize"<?php echo ($e2g['mod_resize_type'] == 'resize' ? ' selected="selected"' : ''); ?>><?php echo $lng['resize']; ?></option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2"><?php echo htmlspecialchars_decode($lng['resize_type_cfg_desc']); ?></td>
                        </tr>
                        <tr class="gridAltItem">
                            <td><b><?php echo $lng['thbg_rgb']; ?>:</b></td>
                            <td>
                                R: <input name="mod_thbg_red" type="text" value="<?php echo $e2g['mod_thbg_red']; ?>" size="3" />
                                G: <input name="mod_thbg_green" type="text" value="<?php echo $e2g['mod_thbg_green']; ?>" size="3" />
                                B: <input name="mod_thbg_blue" type="text" value="<?php echo $e2g['mod_thbg_blue']; ?>" size="3" />
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2"><?php echo htmlspecialchars_decode($lng['thbg_rgb_cfg_desc']); ?></td>
                        </tr>
                    </table>
                </div>
                <div class="tab-page" id="tabSnipOptionSettings">
                    <h2 class="tab"><?php echo $lng['snip_options']; ?></h2>
                    <script type="text/javascript">
                        tpThumbnailConfig.addTabPage( document.getElementById( 'tabSnipOptionSettings') );
                    </script>
                    <table cellspacing="0" cellpadding="2" width="100%">
                        <tr class="gridAltItem">
                            <td width="180"><b><?php echo $lng['w']; ?>:</b></td>
                            <td><span style="color:green;">&amp;w=</span> <input name="w" type="text" value="<?php echo $e2g['w']; ?>" size="4" /></td>
                        </tr>
                        <tr>
                            <td colspan="2"><?php echo htmlspecialchars_decode($lng['w_thumb_cfg_desc']); ?></td>
                        </tr>
                        <tr class="gridAltItem">
                            <td><b><?php echo $lng['h']; ?>:</b></td>
                            <td><span style="color:green;">&amp;h=</span> <input name="h" type="text" value="<?php echo $e2g['h']; ?>" size="4" /></td>
                        </tr>
                        <tr>
                            <td colspan="2"><?php echo htmlspecialchars_decode($lng['h_thumb_cfg_desc']); ?></td>
                        </tr>
                        <tr class="gridAltItem">
                            <td><b><?php echo $lng['thq']; ?>:</b></td>
                            <td><span style="color:green;">&amp;thq=</span> <input name="thq" type="text" value="<?php echo $e2g['thq']; ?>" size="3" /></td>
                        </tr>
                        <tr>
                            <td colspan="2"><?php echo htmlspecialchars_decode($lng['thq_cfg_desc']); ?></td>
                        </tr>
                        <tr class="gridAltItem">
                            <td><b><?php echo $lng['resize_type']; ?>:</b></td>
                            <td><span style="color:green;">&amp;resize_type=</span>
                                <select name="resize_type">
                                    <option value="inner"<?php echo ($e2g['resize_type'] == 'inner' ? ' selected="selected"' : ''); ?>><?php echo $lng['inner']; ?></option>
                                    <option value="shrink"<?php echo ($e2g['resize_type'] == 'shrink' ? ' selected="selected"' : ''); ?>><?php echo $lng['shrink']; ?></option>
                                    <option value="resize"<?php echo ($e2g['resize_type'] == 'resize' ? ' selected="selected"' : ''); ?>><?php echo $lng['resize']; ?></option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2"><?php echo htmlspecialchars_decode($lng['resize_type_cfg_desc']); ?>
                                <img src="<?php echo E2G_MODULE_URL; ?>includes/tpl/img/e2g_resize_proportions.png" alt="e2g_resize_proportions.png" />
                            </td>
                        </tr>
                        <tr class="gridAltItem">
                            <td><b><?php echo $lng['thbg_rgb']; ?>:</b></td>
                            <td>
                                R: <span style="color:green;">&amp;thbg_red=</span> <input name="thbg_red" type="text" value="<?php echo $e2g['thbg_red']; ?>" size="3" />
                                G: <span style="color:green;">&amp;thbg_green=</span> <input name="thbg_green" type="text" value="<?php echo $e2g['thbg_green']; ?>" size="3" />
                                B: <span style="color:green;">&amp;thbg_blue=</span> <input name="thbg_blue" type="text" value="<?php echo $e2g['thbg_blue']; ?>" size="3" />
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2"><?php echo htmlspecialchars_decode($lng['thbg_rgb_cfg_desc']); ?></td>
                        </tr>
                        <tr class="gridAltItem">
                            <td><b><?php echo $lng['glib']; ?>:</b></td>
                            <td><span style="color:green;">&amp;glib=</span> <select name="glib">
                                    <?php
                                    $selectGlibs = 'SELECT * FROM ' . $modx->db->config['table_prefix'] . 'easy2_viewers ';

                                    $glibs = $modx->db->makeArray($modx->db->query($selectGlibs));

                                    foreach ($glibs as $k => $v) {
                                        $jsLibs[$v['name']] = $v;
                                    }
                                    $glibs = array();
                                    unset($glibs);
                                    foreach ($jsLibs as $k => $v) {
                                        echo '<option value="' . $k . '"' . (($e2g['glib'] == $k) ? ' selected="selected"' : '') . '>' . $k . ' (' . $v['alias'] . ')</option>';
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2"><?php echo htmlspecialchars_decode($lng['glib_cfg_desc']); ?></td>
                        </tr>
                        <tr class="gridAltItem">
                            <td><b><?php echo $lng['grid']; ?>:</b></td>
                            <td><span style="color:green;">&amp;grid=</span> <input type="radio" name="grid" value="css" <?php echo ($e2g['grid'] == 'css' ? 'checked="checked"' : ''); ?> /> <?php echo $lng['css']; ?>
                                <input type="radio" name="grid" value="table" <?php echo ($e2g['grid'] == 'table' ? 'checked="checked"' : ''); ?> /> <?php echo $lng['table']; ?> <br />
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2"><?php echo htmlspecialchars_decode($lng['grid_cfg_desc']); ?></td>
                        </tr>
                    </table>
                </div>
                <div class="tab-page" id="tabDisplayOptionSettings">
                    <h2 class="tab"><?php echo $lng['settings_display']; ?></h2>
                    <script type="text/javascript">
                        tpThumbnailConfig.addTabPage( document.getElementById( 'tabDisplayOptionSettings') );
                    </script>
                    <table cellspacing="0" cellpadding="2" width="100%">
                        <tr class="gridAltItem">
                            <td width="180"><b><?php echo $lng['name_len']; ?>:</b></td>
                            <td><span style="color:green;">&amp;name_len=</span> <input name="name_len" type="text" value="<?php echo $e2g['name_len']; ?>" size="4" /></td>
                        </tr>
                        <tr>
                            <td colspan="2"><?php echo htmlspecialchars_decode($lng['name_len_cfg_desc']); ?></td>
                        </tr>
                        <tr class="gridAltItem">
                            <td><b><?php echo $lng['cat_name_len']; ?>:</b></td>
                            <td><span style="color:green;">&amp;cat_name_len=</span> <input name="cat_name_len" type="text" value="<?php echo $e2g['cat_name_len']; ?>" size="4" /></td>
                        </tr>
                        <tr>
                            <td colspan="2"><?php echo htmlspecialchars_decode($lng['cat_name_len_cfg_desc']); ?></td>
                        </tr>
                        <tr class="gridAltItem">
                            <td><b><?php echo $lng['colls']; ?>:</b></td>
                            <td><span style="color:green;">&amp;colls=</span> <input name="colls" type="text" value="<?php echo $e2g['colls']; ?>" size="4" /></td>
                        </tr>
                        <tr>
                            <td colspan="2"><?php echo htmlspecialchars_decode($lng['colls_cfg_desc']); ?></td>
                        </tr>
                        <tr class="gridAltItem">
                            <td><b><?php echo $lng['limit']; ?>:</b></td>
                            <td><span style="color:green;">&amp;limit=</span> <input name="limit" type="text" value="<?php echo $e2g['limit']; ?>" size="4" /></td>
                        </tr>
                        <tr>
                            <td colspan="2"><?php echo htmlspecialchars_decode($lng['limit_cfg_desc']); ?></td>
                        </tr>
                        <tr class="gridAltItem">
                            <td><b><?php echo $lng['order']; ?>:</b></td>
                            <td><span style="color:green;">&amp;orderby=</span>
                                <select size="1" name="orderby">
                                    <option value="date_added"<?php echo ($e2g['orderby'] == 'date_added' ? ' selected="selected"' : ''); ?>>date_added (<?php echo $lng['date_added']; ?>)</option>
                                    <option value="last_modified"<?php echo ($e2g['orderby'] == 'last_modified' ? ' selected="selected"' : ''); ?>>last_modified (<?php echo $lng['last_modified']; ?>)</option>
                                    <option value="comments"<?php echo ($e2g['orderby'] == 'comments' ? ' selected="selected"' : ''); ?>>comments (<?php echo $lng['comments_cnt']; ?>)</option>
                                    <option value="filename"<?php echo ($e2g['orderby'] == 'filename' ? ' selected="selected"' : ''); ?>>filename (<?php echo $lng['filename']; ?>)</option>
                                    <option value="alias"<?php echo ($e2g['orderby'] == 'alias' ? ' selected="selected"' : ''); ?>>alias (<?php echo $lng['alias']; ?>)</option>
                                    <option value="random"<?php echo ($e2g['orderby'] == 'random' ? ' selected="selected"' : ''); ?>>random (<?php echo $lng['random']; ?>)</option>
                                </select>
                                <span style="color:green;">&amp;order=</span>
                                <select size="1" name="order">
                                    <option value="ASC"<?php echo ($e2g['order'] == 'ASC' ? ' selected="selected"' : ''); ?>>ASC (<?php echo $lng['asc']; ?>)</option>
                                    <option value="DESC"<?php echo ($e2g['order'] == 'DESC' ? ' selected="selected"' : ''); ?>>DESC (<?php echo $lng['desc']; ?>)</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2"><?php echo htmlspecialchars_decode($lng['order_cfg_desc']); ?></td>
                        </tr>
                        <tr class="gridAltItem">
                            <td><b><?php echo $lng['order2']; ?>:</b></td>
                            <td><span style="color:green;">&amp;cat_orderby=</span>
                                <select size="1" name="cat_orderby">
                                    <option value="cat_id"<?php echo ($e2g['cat_orderby'] == 'cat_id' ? ' selected="selected"' : ''); ?>>cat_id (<?php echo $lng['cat_id']; ?>)</option>
                                    <option value="cat_name"<?php echo ($e2g['cat_orderby'] == 'cat_name' ? ' selected="selected"' : ''); ?>>cat_name (<?php echo $lng['dir_name']; ?>)</option>
                                    <option value="cat_alias"<?php echo ($e2g['cat_orderby'] == 'cat_alias' ? ' selected="selected"' : ''); ?>>cat_alias (<?php echo $lng['alias']; ?>)</option>
                                    <option value="random"<?php echo ($e2g['cat_orderby'] == 'random' ? ' selected="selected"' : ''); ?>>random (<?php echo $lng['random']; ?>)</option>
                                </select>
                                <span style="color:green;">&amp;cat_order=</span>
                                <select size="1" name="cat_order">
                                    <option value="ASC"<?php echo ($e2g['cat_order'] == 'ASC' ? ' selected="selected"' : ''); ?>>ASC (<?php echo $lng['asc']; ?>)</option>
                                    <option value="DESC"<?php echo ($e2g['cat_order'] == 'DESC' ? ' selected="selected"' : ''); ?>>DESC (<?php echo $lng['desc']; ?>)</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2"><?php echo htmlspecialchars_decode($lng['order2_cfg_desc']); ?></td>
                        </tr>
                        <tr class="gridAltItem">
                            <td><b><?php echo $lng['cat_thumb_order_cfg']; ?>:</b></td>
                            <td><span style="color:green;">&amp;cat_thumb_orderby=</span>
                                <select size="1" name="cat_thumb_orderby">
                                    <option value="date_added"<?php echo ($e2g['cat_thumb_orderby'] == 'date_added' ? ' selected="selected"' : ''); ?>>date_added (<?php echo $lng['date_added']; ?>)</option>
                                    <option value="last_modified"<?php echo ($e2g['cat_thumb_orderby'] == 'last_modified' ? ' selected="selected"' : ''); ?>>last_modified (<?php echo $lng['last_modified']; ?>)</option>
                                    <option value="comments"<?php echo ($e2g['cat_thumb_orderby'] == 'comments' ? ' selected="selected"' : ''); ?>>comments (<?php echo $lng['comments_cnt']; ?>)</option>
                                    <option value="filename"<?php echo ($e2g['cat_thumb_orderby'] == 'filename' ? ' selected="selected"' : ''); ?>>filename (<?php echo $lng['filename']; ?>)</option>
                                    <option value="name"<?php echo ($e2g['cat_thumb_orderby'] == 'name' ? ' selected="selected"' : ''); ?>>name (<?php echo $lng['name']; ?>)</option>
                                    <option value="random"<?php echo ($e2g['cat_thumb_orderby'] == 'random' ? ' selected="selected"' : ''); ?>>random (<?php echo $lng['random']; ?>)</option>
                                </select>
                                <span style="color:green;">&amp;cat_thumb_order=</span>
                                <select size="1" name="cat_thumb_order">
                                    <option value="ASC"<?php echo ($e2g['cat_thumb_order'] == 'ASC' ? ' selected="selected"' : ''); ?>>ASC (<?php echo $lng['asc']; ?>)</option>
                                    <option value="DESC"<?php echo ($e2g['cat_thumb_order'] == 'DESC' ? ' selected="selected"' : ''); ?>>DESC (<?php echo $lng['desc']; ?>)</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2"><?php echo htmlspecialchars_decode($lng['cat_thumb_order_cfg_desc']); ?></td>
                        </tr>
                    </table>
                </div>
                <div class="tab-page" id="tabCrumbOptionSettings">
                    <h2 class="tab"><?php echo $lng['settings_crumbs']; ?></h2>
                    <script type="text/javascript">
                        tpThumbnailConfig.addTabPage( document.getElementById( 'tabCrumbOptionSettings') );
                    </script>
                    <table cellspacing="0" cellpadding="2" width="100%">
                        <tr class="gridAltItem">
                            <td width="180"><b><?php echo $lng['enable']; ?>:</b></td>
                            <td>
                                <span style="color:green;">&amp;crumbs= 0 | 1</span>
                                <input name="crumbs" type="radio" value="0"<?php echo ($e2g['crumbs'] == 0 ? ' checked="checked"' : ''); ?> style="border:0" />
                                <b><?php echo $lng['off']; ?></b>
                                <input name="crumbs" type="radio" value="1"<?php echo ($e2g['crumbs'] == 1 ? ' checked="checked"' : ''); ?> style="border:0" />
                                <b><?php echo $lng['on']; ?></b>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2"><?php echo htmlspecialchars_decode($lng['enable']); ?></td>
                        </tr>
                        <tr class="gridAltItem">
                            <td><b><?php echo $lng['use']; ?>:</b></td>
                            <td><span style="color:green;">&amp;crumbs_use=</span>
                                <select size="1" name="crumbs_use">
                                    <option value="cat_name"<?php echo ($e2g['crumbs_use'] == 'cat_name' ? ' selected="selected"' : ''); ?>>foldername (<?php echo $lng['dir_name']; ?>)</option>
                                    <option value="cat_alias"<?php echo ($e2g['crumbs_use'] == 'cat_alias' ? ' selected="selected"' : ''); ?>>alias (<?php echo $lng['alias'] . ' / ' . $lng['title']; ?>)</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2"><?php echo htmlspecialchars_decode($lng['crumbs_usage_cfg_desc']); ?></td>
                        </tr>
                        <tr class="gridAltItem">
                            <td><b><?php echo $lng['crumbs_separator_cfg']; ?>:</b></td>
                            <td><span style="color:green;">&amp;crumbs_separator=</span> <input name="crumbs_separator" type="text" value="<?php echo $e2g['crumbs_separator']; ?>" size="4" /></td>
                        </tr>
                        <tr>
                            <td colspan="2"><?php echo htmlspecialchars_decode($lng['crumbs_separator_cfg_desc']); ?></td>
                        </tr>
                        <tr class="gridAltItem">
                            <td><b><?php echo $lng['crumbs_showAsLinks_cfg']; ?>:</b></td>
                            <td>
                                <span style="color:green;">&amp;crumbs_showAsLinks= 0 | 1</span>
                                <input name="crumbs_showAsLinks" type="radio" value="0"<?php echo ($e2g['crumbs_showAsLinks'] == 0 ? ' checked="checked"' : ''); ?> style="border:0" />
                                <b><?php echo $lng['off']; ?></b>
                                <input name="crumbs_showAsLinks" type="radio" value="1"<?php echo ($e2g['crumbs_showAsLinks'] == 1 ? ' checked="checked"' : ''); ?> style="border:0" />
                                <b><?php echo $lng['on']; ?></b>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2"><?php echo htmlspecialchars_decode($lng['crumbs_showAsLinks_cfg_desc']); ?></td>
                        </tr>
                        <tr class="gridAltItem">
                            <td><b><?php echo $lng['crumbs_showHome_cfg']; ?>:</b></td>
                            <td>
                                <span style="color:green;">&amp;crumbs_showHome= 0 | 1</span>
                                <input name="crumbs_showHome" type="radio" value="0"<?php echo ($e2g['crumbs_showHome'] == 0 ? ' checked="checked"' : ''); ?> style="border:0" />
                                <b><?php echo $lng['off']; ?></b>
                                <input name="crumbs_showHome" type="radio" value="1"<?php echo ($e2g['crumbs_showHome'] == 1 ? ' checked="checked"' : ''); ?> style="border:0" />
                                <b><?php echo $lng['on']; ?></b>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2"><?php echo htmlspecialchars_decode($lng['crumbs_showHome_cfg_desc']); ?></td>
                        </tr>
                        <tr class="gridAltItem">
                            <td><b><?php echo $lng['crumbs_showCurrent_cfg']; ?>:</b></td>
                            <td>
                                <span style="color:green;">&amp;crumbs_showCurrent= 0 | 1</span>
                                <input name="crumbs_showCurrent" type="radio" value="0"<?php echo ($e2g['crumbs_showCurrent'] == 0 ? ' checked="checked"' : ''); ?> style="border:0" />
                                <b><?php echo $lng['off']; ?></b>
                                <input name="crumbs_showCurrent" type="radio" value="1"<?php echo ($e2g['crumbs_showCurrent'] == 1 ? ' checked="checked"' : ''); ?> style="border:0" />
                                <b><?php echo $lng['on']; ?></b>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2"><?php echo htmlspecialchars_decode($lng['crumbs_showCurrent_cfg_desc']); ?></td>
                        </tr>
                        <tr class="gridAltItem">
                            <td><b><?php echo $lng['crumbs_showPrevious_cfg']; ?>:</b></td>
                            <td>
                                <span style="color:green;">&amp;crumbs_showPrevious= 0 | 1</span>
                                <input name="crumbs_showPrevious" type="radio" value="0"<?php echo ($e2g['crumbs_showPrevious'] == 0 ? ' checked="checked"' : ''); ?> style="border:0" />
                                <b><?php echo $lng['off']; ?></b>
                                <input name="crumbs_showPrevious" type="radio" value="1"<?php echo ($e2g['crumbs_showPrevious'] == 1 ? ' checked="checked"' : ''); ?> style="border:0" />
                                <b><?php echo $lng['on']; ?></b>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2"><?php echo htmlspecialchars_decode($lng['crumbs_showPrevious_cfg_desc']); ?></td>
                        </tr>
                    </table>
                </div>
                <div class="tab-page" id="tabPrevUpNextOptionSettings">
                    <h2 class="tab"><?php echo $lng['settings_nav_prevUpNext']; ?></h2>
                    <script type="text/javascript">
                        tpThumbnailConfig.addTabPage( document.getElementById( 'tabPrevUpNextOptionSettings') );
                    </script>
                    <table cellspacing="0" cellpadding="2" width="100%">
                        <tr class="gridAltItem">
                            <td width="180"><b><?php echo $lng['enable']; ?>:</b></td>
                            <td>
                                <span style="color:green;">&amp;nav_prevUpNext= 0 | 1</span>
                                <input name="nav_prevUpNext" type="radio" value="0"<?php echo ($e2g['nav_prevUpNext'] == 0 ? ' checked="checked"' : ''); ?> style="border:0" />
                                <b><?php echo $lng['off']; ?></b>
                                <input name="nav_prevUpNext" type="radio" value="1"<?php echo ($e2g['nav_prevUpNext'] == 1 ? ' checked="checked"' : ''); ?> style="border:0" />
                                <b><?php echo $lng['on']; ?></b>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2"><?php echo htmlspecialchars_decode($lng['enable']); ?></td>
                        </tr>
                        <tr class="gridAltItem">
                            <td><b><?php echo $lng['nav_prevSymbol_cfg']; ?>:</b></td>
                            <td>
                                <span style="color:green;">&amp;nav_prevSymbol=</span> <input name="nav_prevSymbol" type="text" value="<?php echo $e2g['nav_prevSymbol']; ?>" size="70" />
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2"><?php echo htmlspecialchars_decode($lng['nav_prevSymbol_cfg_desc']); ?></td>
                        </tr>
                        <tr class="gridAltItem">
                            <td><b><?php echo $lng['nav_upSymbol_cfg']; ?>:</b></td>
                            <td>
                                <span style="color:green;">&amp;nav_upSymbol=</span> <input name="nav_upSymbol" type="text" value="<?php echo $e2g['nav_upSymbol']; ?>" size="70" />
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2"><?php echo htmlspecialchars_decode($lng['nav_upSymbol_cfg_desc']); ?></td>
                        </tr>
                        <tr class="gridAltItem">
                            <td><b><?php echo $lng['nav_nextSymbol_cfg']; ?>:</b></td>
                            <td>
                                <span style="color:green;">&amp;nav_nextSymbol=</span> <input name="nav_nextSymbol" type="text" value="<?php echo $e2g['nav_nextSymbol']; ?>" size="70" />
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2"><?php echo htmlspecialchars_decode($lng['nav_nextSymbol_cfg_desc']); ?></td>
                        </tr>
                    </table>
                </div>
                <div class="tab-page" id="tabPaginationOptionSettings">
                    <h2 class="tab"><?php echo $lng['settings_pagination']; ?></h2>
                    <script type="text/javascript">
                        tpThumbnailConfig.addTabPage( document.getElementById( 'tabPaginationOptionSettings') );
                    </script>
                    <table cellspacing="0" cellpadding="2" width="100%">
                        <tr class="gridAltItem">
                            <td width="180"><b><?php echo $lng['enable']; ?>:</b></td>
                            <td>
                                <span style="color:green;">&amp;pagination= 0 | 1</span>
                                <input name="pagination" type="radio" value="0"<?php echo ($e2g['pagination'] == 0 ? ' checked="checked"' : ''); ?> style="border:0" />
                                <b><?php echo $lng['off']; ?></b>
                                <input name="pagination" type="radio" value="1"<?php echo ($e2g['pagination'] == 1 ? ' checked="checked"' : ''); ?> style="border:0" />
                                <b><?php echo $lng['on']; ?></b>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2"><?php echo htmlspecialchars_decode($lng['enable']); ?></td>
                        </tr>
                        <tr class="gridAltItem">
                            <td><b><?php echo $lng['pagination_adjacents_cfg']; ?>:</b></td>
                            <td>
                                <input name="pagination_adjacents" size="30" type="text" value="<?php echo $e2g['pagination_adjacents']; ?>" />
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2"><?php echo htmlspecialchars_decode($lng['pagination_adjacents_cfg_desc']); ?></td>
                        </tr>
                        <tr class="gridAltItem">
                            <td><b><?php echo $lng['pagination_spread_cfg']; ?>:</b></td>
                            <td>
                                <input name="pagination_spread" size="30" type="text" value="<?php echo $e2g['pagination_spread']; ?>" />
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2"><?php echo htmlspecialchars_decode($lng['pagination_spread_cfg_desc']); ?></td>
                        </tr>
                        <tr class="gridAltItem">
                            <td><b><?php echo $lng['pagination_text_previous_cfg']; ?>:</b></td>
                            <td>
                                <input name="pagination_text_previous" size="30" type="text" value="<?php echo $e2g['pagination_text_previous']; ?>" />
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2"><?php echo htmlspecialchars_decode($lng['pagination_text_previous_cfg_desc']); ?></td>
                        </tr>
                        <tr class="gridAltItem">
                            <td><b><?php echo $lng['pagination_text_next_cfg']; ?>:</b></td>
                            <td>
                                <input name="pagination_text_next" size="30" type="text" value="<?php echo $e2g['pagination_text_next']; ?>" />
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2"><?php echo htmlspecialchars_decode($lng['pagination_text_next_cfg_desc']); ?></td>
                        </tr>
                        <tr class="gridAltItem">
                            <td><b><?php echo $lng['pagination_splitter_cfg']; ?>:</b></td>
                            <td>
                                <input name="pagination_splitter" size="30" type="text" value="<?php echo $e2g['pagination_splitter']; ?>" />
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2"><?php echo htmlspecialchars_decode($lng['pagination_splitter_cfg_desc']); ?></td>
                        </tr>



                    </table>
                </div>
            </div>
        </div>
        <div class="tab-page" id="tabSlideshowSettings">
            <h2 class="tab"><?php echo $lng['settings_slideshow']; ?></h2>
            <script type="text/javascript">
                tpConfig.addTabPage( document.getElementById( 'tabSlideshowSettings') );
            </script>
            <table cellspacing="0" cellpadding="2" width="100%">
                <tr class="gridAltItem">
                    <td width="180"><b><?php echo $lng['ss_img_src_cfg']; ?>:</b></td>
                    <td><span style="color:green;">&amp;ss_img_src=</span>
                        <select name="ss_img_src">
                            <option value="original"<?php echo ($e2g['ss_img_src'] == 'original' ? ' selected="selected"' : ''); ?>><?php echo $lng['original']; ?></option>
                            <option value="generated"<?php echo ($e2g['ss_img_src'] == 'generated' ? ' selected="selected"' : ''); ?>><?php echo $lng['generated']; ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><?php echo htmlspecialchars_decode($lng['ss_img_src_cfg_desc']); ?></td>
                </tr>
                <tr class="gridAltItem">
                    <td><b><?php echo $lng['ss_order_cfg']; ?>:</b></td>
                    <td><span style="color:green;">&amp;ss_orderby=</span>
                        <select size="1" name="ss_orderby">
                            <option value="date_added"<?php echo ($e2g['ss_orderby'] == 'date_added' ? ' selected="selected"' : ''); ?>>date_added (<?php echo $lng['date_added']; ?>)</option>
                            <option value="last_modified"<?php echo ($e2g['ss_orderby'] == 'last_modified' ? ' selected="selected"' : ''); ?>>last_modified (<?php echo $lng['last_modified']; ?>)</option>
                            <option value="comments"<?php echo ($e2g['ss_orderby'] == 'comments' ? ' selected="selected"' : ''); ?>>comments (<?php echo $lng['comments_cnt']; ?>)</option>
                            <option value="filename"<?php echo ($e2g['ss_orderby'] == 'filename' ? ' selected="selected"' : ''); ?>>filename (<?php echo $lng['filename']; ?>)</option>
                            <option value="name"<?php echo ($e2g['ss_orderby'] == 'name' ? ' selected="selected"' : ''); ?>>name (<?php echo $lng['name']; ?>)</option>
                            <option value="random"<?php echo ($e2g['ss_orderby'] == 'random' ? ' selected="selected"' : ''); ?>>random (<?php echo $lng['random']; ?>)</option>
                        </select>
                        <span style="color:green;">&amp;ss_order=</span>
                        <select size="1" name="ss_order">
                            <option value="ASC"<?php echo ($e2g['ss_order'] == 'ASC' ? ' selected="selected"' : ''); ?>>ASC (<?php echo $lng['asc']; ?>)</option>
                            <option value="DESC"<?php echo ($e2g['ss_order'] == 'DESC' ? ' selected="selected"' : ''); ?>>DESC (<?php echo $lng['desc']; ?>)</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><?php echo htmlspecialchars_decode($lng['order_cfg_desc']); ?></td>
                </tr>
                <tr class="gridAltItem">
                    <td><b><?php echo $lng['ss_limit_cfg']; ?>:</b></td>
                    <td><span style="color:green;">&amp;ss_limit=</span> <input name="ss_limit" type="text" value="<?php echo $e2g['ss_limit']; ?>" size="4" /></td>
                </tr>
                <tr>
                    <td colspan="2"><?php echo htmlspecialchars_decode($lng['ss_limit_cfg_desc']); ?></td>
                </tr>


                <tr class="gridAltItem">
                    <td><b><?php echo $lng['ss_w_cfg']; ?>:</b></td>
                    <td><span style="color:green;">&amp;ss_w=</span> <input name="ss_w" type="text" value="<?php echo $e2g['ss_w']; ?>" size="4" />
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><?php echo htmlspecialchars_decode($lng['ss_w_cfg_desc']); ?></td>
                </tr>
                <tr class="gridAltItem">
                    <td><b><?php echo $lng['ss_h_cfg']; ?>:</b></td>
                    <td><span style="color:green;">&amp;ss_h=</span> <input name="ss_h" type="text" value="<?php echo $e2g['ss_h']; ?>" size="4" />
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><?php echo htmlspecialchars_decode($lng['ss_h_cfg_desc']); ?></td>
                </tr>
                <tr class="gridAltItem">
                    <td><b><?php echo $lng['ss_thq_cfg']; ?>:</b></td>
                    <td><span style="color:green;">&amp;ss_thq=</span> <input name="ss_thq" type="text" value="<?php echo $e2g['ss_thq']; ?>" size="4" />
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><?php echo htmlspecialchars_decode($lng['ss_thq_cfg_desc']); ?></td>
                </tr>
                <tr class="gridAltItem">
                    <td><b><?php echo $lng['ss_resize_type_cfg']; ?>:</b></td>
                    <td><span style="color:green;">&amp;ss_resize_type=</span>
                        <select name="ss_resize_type">
                            <option value="inner"<?php echo ($e2g['ss_resize_type'] == 'inner' ? ' selected="selected"' : ''); ?>><?php echo $lng['inner']; ?></option>
                            <option value="shrink"<?php echo ($e2g['ss_resize_type'] == 'shrink' ? ' selected="selected"' : ''); ?>><?php echo $lng['shrink']; ?></option>
                            <option value="resize"<?php echo ($e2g['ss_resize_type'] == 'resize' ? ' selected="selected"' : ''); ?>><?php echo $lng['resize']; ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><?php echo htmlspecialchars_decode($lng['ss_resize_type_cfg_desc']); ?></td>
                </tr>
                <tr class="gridAltItem">
                    <td><b><?php echo $lng['ss_bg_cfg']; ?>:</b></td>
                    <td><span style="color:green;">&amp;ss_bg=</span> <input name="ss_bg" type="text" value="<?php echo $e2g['ss_bg']; ?>" size="10" />
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><?php echo htmlspecialchars_decode($lng['ss_bg_cfg_desc']); ?></td>
                </tr>
                <tr class="gridAltItem">
                    <td><b><?php echo $lng['ss_rgb_cfg']; ?>:</b></td>
                    <td>
                        R: <span style="color:green;">&amp;ss_red=</span> <input name="ss_red" type="text" value="<?php echo $e2g['ss_red']; ?>" size="3" />
                        G: <span style="color:green;">&amp;ss_green=</span> <input name="ss_green" type="text" value="<?php echo $e2g['ss_green']; ?>" size="3" />
                        B: <span style="color:green;">&amp;ss_blue=</span> <input name="ss_blue" type="text" value="<?php echo $e2g['ss_blue']; ?>" size="3" />
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><?php echo htmlspecialchars_decode($lng['ss_rgb_cfg_desc']); ?></td>
                </tr>
                <tr class="gridAltItem">
                    <td><b><?php echo $lng['ss_allowedratio_cfg']; ?>:</b></td>
                    <td><span style="color:green;">&amp;ss_allowedratio=</span> <input name="ss_allowedratio" type="text" value="<?php echo $e2g['ss_allowedratio']; ?>" size="10" />
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><?php echo htmlspecialchars_decode($lng['ss_allowedratio_cfg_desc']); ?></td>
                </tr>
            </table>
        </div>
        <div class="tab-page" id="tabTemplatesSettings">
            <h2 class="tab"><?php echo $lng['tpl']; ?></h2>
            <script type="text/javascript">
                tpConfig.addTabPage( document.getElementById( 'tabTemplatesSettings') );
            </script>
            <table cellspacing="0" cellpadding="2" width="100%"><tr class="gridAltItem">
                    <td width="180"><b><?php echo $lng['gallery']; ?>:</b></td>
                    <td><span style="color:green;">&amp;tpl=</span> <input name="tpl" type="text" value="<?php echo $e2g['tpl']; ?>" size="70" /></td>
                </tr>
                <tr>
                    <td colspan="2"><?php echo htmlspecialchars_decode($lng['tpl_path_cfg_desc']); ?></td>
                </tr>
                <tr class="gridAltItem">
                    <td><b><?php echo $lng['dir']; ?>:</b></td>
                    <td><span style="color:green;">&amp;dir_tpl=</span> <input name="dir_tpl" type="text" value="<?php echo $e2g['dir_tpl']; ?>" size="70" /></td>
                </tr>
                <tr>
                    <td colspan="2"><?php echo htmlspecialchars_decode($lng['tpl_path_cfg_desc']); ?></td>
                </tr>
                <tr class="gridAltItem">
                    <td><b><?php echo $lng['thumb']; ?>:</b></td>
                    <td><span style="color:green;">&amp;thumb_tpl=</span> <input name="thumb_tpl" type="text" value="<?php echo $e2g['thumb_tpl']; ?>" size="70" /></td>
                </tr>
                <tr>
                    <td colspan="2"><?php echo htmlspecialchars_decode($lng['tpl_path_cfg_desc']); ?></td>
                </tr>
                <tr class="gridAltItem">
                    <td><b><?php echo $lng['thumb']; ?> RAND:</b></td>
                    <td><span style="color:green;">&amp;rand_tpl=</span> <input name="rand_tpl" type="text" value="<?php echo $e2g['rand_tpl']; ?>" size="70" /></td>
                </tr>
                <tr>
                    <td colspan="2"><?php echo htmlspecialchars_decode($lng['tpl_path_cfg_desc']); ?></td>
                </tr>
                <tr class="gridAltItem">
                    <td><b><?php echo $lng['comments']; ?>:</b></td>
                    <td><span style="color:green;">&amp;comments_tpl=</span> <input name="comments_tpl" type="text" value="<?php echo $e2g['comments_tpl']; ?>" size="70" /></td>
                </tr>
                <tr>
                    <td colspan="2"><?php echo htmlspecialchars_decode($lng['tpl_comments_cfg_desc']); ?></td>
                </tr>
                <tr class="gridAltItem">
                    <td><b><?php echo $lng['comments_row']; ?>:</b></td>
                    <td><span style="color:green;">&amp;comments_row_tpl=</span> <input name="comments_row_tpl" type="text" value="<?php echo $e2g['comments_row_tpl']; ?>" size="70" /></td>
                </tr>
                <tr>
                    <td colspan="2"><?php echo htmlspecialchars_decode($lng['tpl_comments_cfg_desc']); ?></td>
                </tr>
                <tr class="gridAltItem">
                    <td><b><?php echo $lng['tpl_lp']; ?>:</b></td>
                    <td><span style="color:green;">&amp;page_tpl=</span> <input name="page_tpl" type="text" value="<?php echo $e2g['page_tpl']; ?>" size="70" /></td>
                </tr>
                <tr>
                    <td colspan="2"><?php echo htmlspecialchars_decode($lng['tpl_lp_path_cfg_desc']); ?></td>
                </tr>
                <tr class="gridAltItem">
                    <td><b><?php echo $lng['tpl_lp_cmt']; ?>:</b></td>
                    <td><span style="color:green;">&amp;page_comments_tpl=</span> <input name="page_comments_tpl" type="text" value="<?php echo $e2g['page_comments_tpl']; ?>" size="70" /></td>
                </tr>
                <tr>
                    <td colspan="2"><?php echo htmlspecialchars_decode($lng['tpl_lp_cmt_path_cfg_desc']); ?></td>
                </tr>
                <tr class="gridAltItem">
                    <td><b><?php echo $lng['tpl_lp_cmt_row']; ?>:</b></td>
                    <td><span style="color:green;">&amp;page_comments_row_tpl=</span> <input name="page_comments_row_tpl" type="text" value="<?php echo $e2g['page_comments_row_tpl']; ?>" size="70" /></td>
                </tr>
                <tr>
                    <td colspan="2"><?php echo htmlspecialchars_decode($lng['tpl_lp_cmt_path_cfg_desc']); ?></td>
                </tr>
                <tr class="gridAltItem">
                    <td><b><?php echo $lng['tpl_slideshow']; ?>:</b></td>
                    <td><span style="color:green;">&amp;slideshow_tpl=</span> <input name="ss_tpl" type="text" value="<?php echo $e2g['ss_tpl']; ?>" size="70" /></td>
                </tr>
                <tr>
                    <td colspan="2"><?php echo htmlspecialchars_decode($lng['tpl_path_cfg_desc']); ?></td>
                </tr>
                <tr class="gridAltItem">
                    <td><b><?php echo $lng['tpl_jsdisabled']; ?>:</b></td>
                    <td><input name="jsdisabled_tpl" type="text" value="<?php echo $e2g['jsdisabled_tpl']; ?>" size="70" /></td>
                </tr>
                <tr>
                    <td colspan="2"><?php echo htmlspecialchars_decode($lng['tpl_path_cfg_desc']); ?></td>
                </tr>
                <tr>
                    <td colspan="2"><br /><b class="success" style="font-size:120%"><?php echo $lng['css']; ?></b></td>
                </tr>
                <tr class="gridAltItem">
                    <td width="180"><b><?php echo $lng['css']; ?>:</b></td>
                    <td><span style="color:green;">&amp;css=</span> <input name="css" type="text" value="<?php echo $e2g['css']; ?>" size="70" /></td>
                </tr>
                <tr>
                    <td colspan="2"><?php echo htmlspecialchars_decode($lng['tpl_css_path_cfg_desc']); ?></td>
                </tr>
                <tr class="gridAltItem">
                    <td><b><?php echo $lng['tpl_lp_css']; ?>:</b></td>
                    <td><span style="color:green;">&amp;page_tpl_css=</span> <input name="page_tpl_css" type="text" value="<?php echo $e2g['page_tpl_css']; ?>" size="70" /></td>
                </tr>
                <tr>
                    <td colspan="2"><?php echo htmlspecialchars_decode($lng['tpl_css_path_cfg_desc']); ?></td>
                </tr>
                <tr class="gridAltItem">
                    <td><b><?php echo $lng['grid_class']; ?>:</b></td>
                    <td><span style="color:green;">&amp;grid_class=</span> <input name="grid_class" type="text" value="<?php echo $e2g['grid_class']; ?>" size="20" /></td>
                </tr>
                <tr>
                    <td colspan="2"><?php echo htmlspecialchars_decode($lng['grid_class_cfg_desc']); ?></td>
                </tr>
                <tr class="gridAltItem">
                    <td><b><?php echo $lng['cfg_crumbs_classCurrent']; ?>:</b></td>
                    <td><span style="color:green;">&amp;crumbs_classCurrent=</span> <input name="crumbs_classCurrent" type="text" value="<?php echo $e2g['crumbs_classCurrent']; ?>" size="20" /></td>
                </tr>
                <tr>
                    <td colspan="2"><?php echo $lng['classname']; ?></td>
                </tr>


                <!--Deprecated-->
                <tr class="gridAltItem">
                    <td><b><?php echo $lng['cfg_back_class']; ?>:</b></td>
                    <td><span style="color:green;">&amp;back_class=</span> <input name="back_class" type="text" value="<?php echo $e2g['back_class']; ?>" size="20" /></td>
                </tr>
                <tr>
                    <td colspan="2"><?php echo $lng['classname']; ?> (Deprecated. Please, specified this directly on the template, instead.)</td>
                </tr>
                <tr class="gridAltItem">
                    <td><b><?php echo $lng['cfg_pagenum_class']; ?>:</b></td>
                    <td><span style="color:green;">&amp;pagenum_class=</span> <input name="pagenum_class" type="text" value="<?php echo $e2g['pagenum_class']; ?>" size="20" /></td>
                </tr>
                <tr>
                    <td colspan="2"><?php echo $lng['classname']; ?> (Deprecated. Please, specified this directly on the template, instead.)</td>
                </tr>
                <!--Deprecated-->


            </table>

        </div>
        <div class="tab-page" id="tabWatermarks">
            <h2 class="tab"><?php echo $lng['watermarks']; ?></h2>
            <script type="text/javascript">
                tpConfig.addTabPage( document.getElementById( 'tabWatermarks') );
            </script>
            <table cellspacing="0" cellpadding="2" width="100%">
                <tr>
                    <td colspan="2"><span style="color:green;">&amp;ewm= 0 | 1</span>
                        <input name="ewm" type="radio" value="0"<?php echo ($e2g['ewm'] == 0 ? ' checked="checked"' : ''); ?> style="border:0" />
                        <b><?php echo $lng['off']; ?></b>
                        <input name="ewm" type="radio" value="1"<?php echo ($e2g['ewm'] == 1 ? ' checked="checked"' : ''); ?> style="border:0" />
                        <b><?php echo $lng['on']; ?></b>
                    </td>
                </tr>
                <tr class="gridAltItem">
                    <td width="150"><b><?php echo $lng['type']; ?>:</b></td>
                    <td><span style="color:green;">&amp;wmtype=</span>
                        <select size="1" name="wmtype">
                            <option value="text"<?php echo ($e2g['wmtype'] == 'text' ? ' selected="selected"' : ''); ?>>text (<?php echo $lng['text']; ?>)</option>
                            <option value="image"<?php echo ($e2g['wmtype'] == 'image' ? ' selected="selected"' : ''); ?>>image (<?php echo $lng['image']; ?>)</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><?php echo htmlspecialchars_decode($lng['watermark_type_cfg_desc']); ?></td>
                </tr>
                <tr class="gridAltItem">
                    <td><b><?php echo $lng['watermark_textpath']; ?>:</b></td>
                    <td><span style="color:green;">&amp;wmt=</span> <input size="50" name="wmt" type="text" value="<?php echo $e2g['wmt']; ?>" /></td>
                </tr>
                <tr>
                    <td colspan="2"><?php echo htmlspecialchars_decode($lng['watermark_text_path_cfg_desc']); ?></td>
                </tr>
                <tr class="gridAltItem">
                    <td><b><?php echo $lng['watermark_horpos']; ?>:</b></td>
                    <td><span style="color:green;">&amp;wmpos1=</span>
                        <select size="1" name="wmpos1">
                            <option value="1"<?php echo ($e2g['wmpos1'] == 1 ? ' selected="selected"' : ''); ?>>1 (<?php echo $lng['left']; ?>)</option>
                            <option value="2"<?php echo ($e2g['wmpos1'] == 2 ? ' selected="selected"' : ''); ?>>2 (<?php echo $lng['center']; ?>)</option>
                            <option value="3"<?php echo ($e2g['wmpos1'] == 3 ? ' selected="selected"' : ''); ?>>3 (<?php echo $lng['right']; ?>)</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><?php echo htmlspecialchars_decode($lng['watermark_horpos_cfg_desc']); ?></td>
                </tr>
                <tr class="gridAltItem">
                    <td><b><?php echo $lng['watermark_verpos']; ?>:</b></td>
                    <td><span style="color:green;">&amp;wmpos2=</span>
                        <select size="1" name="wmpos2">
                            <option value="1"<?php echo ($e2g['wmpos2'] == 1 ? ' selected="selected"' : ''); ?>>1 (<?php echo $lng['top']; ?>)</option>
                            <option value="2"<?php echo ($e2g['wmpos2'] == 2 ? ' selected="selected"' : ''); ?>>2 (<?php echo $lng['center']; ?>)</option>
                            <option value="3"<?php echo ($e2g['wmpos2'] == 3 ? ' selected="selected"' : ''); ?>>3 (<?php echo $lng['bottom']; ?>)</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><?php echo htmlspecialchars_decode($lng['watermark_verpos_cfg_desc']); ?></td>
                </tr>
            </table>
        </div>
        <div class="tab-page" id="tabComments">
            <h2 class="tab"><?php echo $lng['comments']; ?></h2>
            <script type="text/javascript">
                tpConfig.addTabPage( document.getElementById( 'tabComments') );
            </script>

            <table cellspacing="0" cellpadding="2" width="100%">
                <tr>
                    <td colspan="2"><span style="color:green;">&amp;ecm= 0 | 1</span>
                        <input name="ecm" type="radio" value="0"<?php echo ($e2g['ecm'] == 0 ? ' checked="checked"' : ''); ?> style="border:0" />
                        <b><?php echo $lng['off']; ?></b>
                        <input name="ecm" type="radio" value="1"<?php echo ($e2g['ecm'] == 1 ? ' checked="checked"' : ''); ?> style="border:0" />
                        <b><?php echo $lng['on']; ?></b>
                    </td>
                </tr>
                <tr class="gridAltItem">
                    <td width="250"><b><?php echo $lng['ecl']; ?>:</b></td>
                    <td><span style="color:green;">&amp;ecl=</span> <input name="ecl" type="text" value="<?php echo $e2g['ecl']; ?>" size="3" /></td>
                </tr>
                <tr>
                    <td colspan="2"><?php echo htmlspecialchars_decode($lng['ecl_cfg_desc']); ?></td>
                </tr>
                <tr class="gridAltItem">
                    <td><b><?php echo $lng['ecl_page']; ?>:</b></td>
                    <td><span style="color:green;">&amp;ecl_page=</span> <input name="ecl_page" type="text" value="<?php echo $e2g['ecl_page']; ?>" size="3" /></td>
                </tr>
                <tr>
                    <td colspan="2"><?php echo htmlspecialchars_decode($lng['ecl_cfg_desc']); ?></td>
                </tr>
                <tr>
                    <td colspan="2"><br /><b class="success" style="font-size:120%"><?php echo $lng['settings_recaptcha']; ?></b></td>
                </tr>
                <tr>
                    <td colspan="2"><span style="color:green;">&amp;recaptcha= 0 | 1</span>
                        <input name="recaptcha" type="radio" value="0"<?php echo ($e2g['recaptcha'] == 0 ? ' checked="checked"' : ''); ?> style="border:0" />
                        <b><?php echo $lng['off']; ?></b>
                        <input name="recaptcha" type="radio" value="1"<?php echo ($e2g['recaptcha'] == 1 ? ' checked="checked"' : ''); ?> style="border:0" />
                        <b><?php echo $lng['on']; ?></b>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><?php echo htmlspecialchars_decode($lng['captcha_desc']); ?></td>
                </tr>
                <tr class="gridAltItem">
                    <td><b><?php echo $lng['recaptcha_key_public']; ?>:</b></td>
                    <td><input name="recaptcha_key_public" type="text" value="<?php echo $e2g['recaptcha_key_public']; ?>" size="70" /></td>
                </tr>
                <tr>
                    <td colspan="2"><?php echo htmlspecialchars_decode($lng['recaptcha_key_public_desc']); ?></td>
                </tr>
                <tr class="gridAltItem">
                    <td><b><?php echo $lng['recaptcha_key_private']; ?>:</b></td>
                    <td><input name="recaptcha_key_private" type="text" value="<?php echo $e2g['recaptcha_key_private']; ?>" size="70" /></td>
                </tr>
                <tr>
                    <td colspan="2"><?php echo htmlspecialchars_decode($lng['recaptcha_key_private_desc']); ?></td>
                </tr>
                <tr class="gridAltItem">
                    <td><b><?php echo $lng['theme']; ?>:</b></td>
                    <td>
                        <select name="recaptcha_theme">
                            <option value="red"<?php echo ($e2g['recaptcha_theme'] == 'red' ? ' selected="selected"' : ''); ?>><?php echo $lng['red']; ?></option>
                            <option value="white"<?php echo ($e2g['recaptcha_theme'] == 'white' ? ' selected="selected"' : ''); ?>><?php echo $lng['white']; ?></option>
                            <option value="blackglass"<?php echo ($e2g['recaptcha_theme'] == 'blackglass' ? ' selected="selected"' : ''); ?>><?php echo $lng['blackglass']; ?></option>
                            <option value="clean"<?php echo ($e2g['recaptcha_theme'] == 'clean' ? ' selected="selected"' : ''); ?>><?php echo $lng['clean']; ?></option>
                            <option value="custom"<?php echo ($e2g['recaptcha_theme'] == 'custom' ? ' selected="selected"' : ''); ?>><?php echo $lng['custom']; ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><?php echo htmlspecialchars_decode($lng['theme_recaptcha_desc']); ?></td>
                </tr>
                <tr class="gridAltItem">
                    <td><b><?php echo $lng['theme_custom']; ?>:</b></td>
                    <td><input name="recaptcha_theme_custom" type="text" value="<?php echo $e2g['recaptcha_theme_custom']; ?>" size="70" /></td>
                </tr>
                <tr>
                    <td colspan="2"><?php echo htmlspecialchars_decode($lng['theme_custom_recaptcha_desc']); ?></td>
                </tr>
            </table>
        </div>
    </div>
</form>