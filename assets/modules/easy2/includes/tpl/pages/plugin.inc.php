<?php
if (IN_MANAGER_MODE != 'true')
    die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
?>
<div class="sectionBody"><?php echo htmlspecialchars_decode($this->lng['plugins_desc']); ?></div>
<div class="tab-pane" id="tabPluginPane">
    <script type="text/javascript">
        tpPlugin = new WebFXTabPane(document.getElementById('tabPluginPane'));
    </script>
    <?php
    /**
     * for edit list
     */
    if (isset($this->sanitizedGets['page']) && $this->sanitizedGets['page'] == 'edit_plugin') {
        ?>
        <div class="tab-page" id="tabPluginEdit">
            <h2 class="tab"><?php echo $this->lng['edit']; ?></h2>
            <script type="text/javascript">
                tpPlugin.addTabPage( document.getElementById( 'tabPluginEdit') );
            </script>
            <form action="<?php echo $this->e2gModCfg['index']; ?>&amp;act=update_plugin" method="post">
                <?php
                $select_plugins = 'SELECT * FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_plugins WHERE id=' . $this->sanitizedGets['ssid'];
                $query_plugins = mysql_query($select_plugins);
                if (!$query_plugins)
                    die(__LINE__ . ': ' . mysql_errno() . ' ' . mysql_error() . '<br />' . $select_plugins);
                else {
                    $numrow_plugins = mysql_num_rows($query_plugins);
                    $row = mysql_fetch_assoc($query_plugins);
                    $events = @explode(',', $row['events']);
                    ?>
                    <p>ID: <?php echo $row['id']; ?></p>
                    <input type="hidden" name="plugin_id" value="<?php echo $row['id']; ?>" />
                    <table cellspacing="0" cellpadding="2">
                        <tr>
                            <td><b><?php echo $this->lng['name']; ?></b></td>
                            <td valign="top">:</td>
                            <td> <input name="name" type="text" size="75" value="<?php echo $row['name']; ?>" /></td>
                        </tr>
                        <tr>
                            <td valign="top"><b><?php echo $this->lng['description']; ?></b></td>
                            <td valign="top">:</td>
                            <td> <input name="description" type="text" size="75" value="<?php echo $row['description']; ?>" /></td>
                        </tr>
                        <tr>
                            <td><b><?php echo $this->lng['plugin_disabled']; ?></b></td>
                            <td valign="top">:</td>
                            <td><input name="disabled" type="checkbox" value="1" <?php echo ($row['disabled'] == 1 ? 'checked="checked" ' : ''); ?>/></td>
                        </tr>
                        <tr>
                            <td><b><?php echo $this->lng['index_file']; ?></b></td>
                            <td valign="top">:</td>
                            <td> <input name="index_file" type="text" size="75" value="<?php echo $row['indexfile']; ?>" /></td>
                        </tr>
                        <tr>
                            <td valign="top"><b><?php echo $this->lng['events_system']; ?></b></td>
                            <td valign="top">:</td>
                            <td><ul>
                                    <li>Snippet <span style="color:green;font-style: italic;">(runtime)</span>:
                                        <table border="0">
                                            <tbody>
                                                <tr>
                                                    <td><input type="checkbox" name="events[]" value="<?php echo $this->_getEventNum('OnE2GWebThumbPrerender'); ?>" <?php echo ( in_array($this->_getEventNum('OnE2GWebThumbPrerender'), $events) ? 'checked="checked" ' : ''); ?>/> OnE2GWebThumbPrerender <span style="color:green;font-style: italic;">(thumb:pluginName#prerender)</span></td>
                                                    <td><input type="checkbox" name="events[]" value="<?php echo $this->_getEventNum('OnE2GWebThumbRender'); ?>" <?php echo ( in_array($this->_getEventNum('OnE2GWebThumbRender'), $events) ? 'checked="checked" ' : ''); ?>/> OnE2GWebThumbRender <span style="color:green;font-style: italic;">(thumb:pluginName#render)</span></td>
                                                </tr>
                                                <tr>
                                                    <td><input type="checkbox" name="events[]" value="<?php echo $this->_getEventNum('OnE2GWebDirPrerender'); ?>" <?php echo ( in_array($this->_getEventNum('OnE2GWebDirPrerender'), $events) ? 'checked="checked" ' : ''); ?>/> OnE2GWebDirPrerender <span style="color:green;font-style: italic;">(dir:pluginName#prerender)</span></td>
                                                    <td><input type="checkbox" name="events[]" value="<?php echo $this->_getEventNum('OnE2GWebDirRender'); ?>" <?php echo ( in_array($this->_getEventNum('OnE2GWebDirRender'), $events) ? 'checked="checked" ' : ''); ?>/> OnE2GWebDirRender <span style="color:green;font-style: italic;">(dir:pluginName#render)</span></td>
                                                </tr>
                                                <tr>
                                                    <td><input type="checkbox" name="events[]" value="<?php echo $this->_getEventNum('OnE2GWebGalleryPrerender'); ?>" <?php echo ( in_array($this->_getEventNum('OnE2GWebGalleryPrerender'), $events) ? 'checked="checked" ' : ''); ?>/> OnE2GWebGalleryPrerender <span style="color:green;font-style: italic;">(gallery:pluginName#prerender)</span></td>
                                                    <td><input type="checkbox" name="events[]" value="<?php echo $this->_getEventNum('OnE2GWebGalleryRender'); ?>" <?php echo ( in_array($this->_getEventNum('OnE2GWebGalleryRender'), $events) ? 'checked="checked" ' : ''); ?>/> OnE2GWebGalleryRender <span style="color:green;font-style: italic;">(gallery:pluginName#render)</span></td>
                                                </tr>
                                                <tr>
                                                    <td><input type="checkbox" name="events[]" value="<?php echo $this->_getEventNum('OnE2GWebLandingpagePrerender'); ?>" <?php echo ( in_array($this->_getEventNum('OnE2GWebLandingpagePrerender'), $events) ? 'checked="checked" ' : ''); ?>/> OnE2GWebLandingpagePrerender <span style="color:green;font-style: italic;">(landingpage:pluginName#prerender)</span></td>
                                                    <td><input type="checkbox" name="events[]" value="<?php echo $this->_getEventNum('OnE2GWebLandingpageRender'); ?>" <?php echo ( in_array($this->_getEventNum('OnE2GWebLandingpagePrerender'), $events) ? 'checked="checked" ' : ''); ?>/> OnE2GWebLandingpageRender <span style="color:green;font-style: italic;">(landingpage:pluginName#render)</span></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </li>
                                    <li>Module :
                                        <table border="0">
                                            <tbody>
                                                <tr>
                                                    <td colspan="2" style="font-weight:bold;border-top: 1px dotted #ccc;"><?php echo $this->lng['header']; ?></td>
                                                </tr>
                                                <tr>
                                                    <td><input type="checkbox" name="events[]" value="<?php echo $this->_getEventNum('OnE2GModHeadScript'); ?>" <?php echo ( in_array($this->_getEventNum('OnE2GModHeadScript'), $events) ? 'checked="checked" ' : ''); ?>/> OnE2GModHeadScript</td>
                                                    <td><input type="checkbox" name="events[]" value="<?php echo $this->_getEventNum('OnE2GModHeadCSSScript'); ?>" <?php echo ( in_array($this->_getEventNum('OnE2GModHeadCSSScript'), $events) ? 'checked="checked" ' : ''); ?>/> OnE2GModHeadCSSScript</td>
                                                </tr>
                                                <tr>
                                                    <td><input type="checkbox" name="events[]" value="<?php echo $this->_getEventNum('OnE2GModHeadJSScript'); ?>" <?php echo ( in_array($this->_getEventNum('OnE2GModHeadJSScript'), $events) ? 'checked="checked" ' : ''); ?>/> OnE2GModHeadJSScript</td>
                                                    <td>&nbsp;</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2" style="font-weight:bold;border-top: 1px dotted #ccc;"><?php echo $this->lng['dashboard']; ?></td>
                                                </tr>
                                                <tr>
                                                    <td><input type="checkbox" name="events[]" value="<?php echo $this->_getEventNum('OnE2GDashboardPrerender'); ?>" <?php echo ( in_array($this->_getEventNum('OnE2GDashboardPrerender'), $events) ? 'checked="checked" ' : ''); ?>/> OnE2GDashboardPrerender</td>
                                                    <td><input type="checkbox" name="events[]" value="<?php echo $this->_getEventNum('OnE2GDashboardRender'); ?>" <?php echo ( in_array($this->_getEventNum('OnE2GDashboardRender'), $events) ? 'checked="checked" ' : ''); ?>/> OnE2GDashboardRender</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2" style="font-weight:bold;border-top: 1px dotted #ccc;"><?php echo $this->lng['mgr_files']; ?></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2" style="font-weight:bold;"><?php echo $this->lng['dir']; ?></td>
                                                </tr>
                                                <tr>
                                                    <td><input type="checkbox" name="events[]" value="<?php echo $this->_getEventNum('OnE2GFolderCreateFormPrerender'); ?>" <?php echo ( in_array($this->_getEventNum('OnE2GFolderCreateFormPrerender'), $events) ? 'checked="checked" ' : ''); ?>/> OnE2GFolderCreateFormPrerender</td>
                                                    <td><input type="checkbox" name="events[]" value="<?php echo $this->_getEventNum('OnE2GFolderCreateFormRender'); ?>" <?php echo ( in_array($this->_getEventNum('OnE2GFolderCreateFormRender'), $events) ? 'checked="checked" ' : ''); ?>/> OnE2GFolderCreateFormRender</td>
                                                </tr>
                                                <tr>
                                                    <td><input type="checkbox" name="events[]" value="<?php echo $this->_getEventNum('OnE2GFolderCreateFormSave'); ?>" <?php echo ( in_array($this->_getEventNum('OnE2GFolderCreateFormSave'), $events) ? 'checked="checked" ' : ''); ?>/> OnE2GFolderCreateFormSave</td>
                                                    <td><input type="checkbox" name="events[]" value="<?php echo $this->_getEventNum('OnE2GFolderAdd'); ?>" <?php echo ( in_array($this->_getEventNum('OnE2GFolderAdd'), $events) ? 'checked="checked" ' : ''); ?>/> OnE2GFolderAdd</td>
                                                </tr>
                                                <tr>
                                                    <td><input type="checkbox" name="events[]" value="<?php echo $this->_getEventNum('OnE2GFolderEditFormPrerender'); ?>" <?php echo ( in_array($this->_getEventNum('OnE2GFolderEditFormPrerender'), $events) ? 'checked="checked" ' : ''); ?>/> OnE2GFolderEditFormPrerender</td>
                                                    <td><input type="checkbox" name="events[]" value="<?php echo $this->_getEventNum('OnE2GFolderEditFormRender'); ?>" <?php echo ( in_array($this->_getEventNum('OnE2GFolderEditFormRender'), $events) ? 'checked="checked" ' : ''); ?>/> OnE2GFolderEditFormRender</td>
                                                </tr>
                                                <tr>
                                                    <td><input type="checkbox" name="events[]" value="<?php echo $this->_getEventNum('OnE2GFolderEditFormSave'); ?>" <?php echo ( in_array($this->_getEventNum('OnE2GFolderEditFormSave'), $events) ? 'checked="checked" ' : ''); ?>/> OnE2GFolderEditFormSave</td>
                                                    <td><input type="checkbox" name="events[]" value="<?php echo $this->_getEventNum('OnE2GFolderDelete'); ?>" <?php echo ( in_array($this->_getEventNum('OnE2GFolderDelete'), $events) ? 'checked="checked" ' : ''); ?>/> OnE2GFolderDelete</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2" style="font-weight:bold;"><?php echo $this->lng['file']; ?></td>
                                                </tr>
                                                <tr>
                                                    <td><input type="checkbox" name="events[]" value="<?php echo $this->_getEventNum('OnE2GFileUploadFormPrerender'); ?>" <?php echo ( in_array($this->_getEventNum('OnE2GFileUploadFormPrerender'), $events) ? 'checked="checked" ' : ''); ?>/> OnE2GFileUploadFormPrerender</td>
                                                    <td><input type="checkbox" name="events[]" value="<?php echo $this->_getEventNum('OnE2GFileUploadFormRender'); ?>" <?php echo ( in_array($this->_getEventNum('OnE2GFileUploadFormRender'), $events) ? 'checked="checked" ' : ''); ?>/> OnE2GFileUploadFormRender</td>
                                                </tr>
                                                <tr>
                                                    <td><input type="checkbox" name="events[]" value="<?php echo $this->_getEventNum('OnE2GFileUpload'); ?>" <?php echo ( in_array($this->_getEventNum('OnE2GFileUpload'), $events) ? 'checked="checked" ' : ''); ?>/> OnE2GFileUpload</td>
                                                    <td><input type="checkbox" name="events[]" value="<?php echo $this->_getEventNum('OnE2GFileAdd'); ?>" <?php echo ( in_array($this->_getEventNum('OnE2GFileAdd'), $events) ? 'checked="checked" ' : ''); ?>/> OnE2GFileAdd</td>
                                                </tr>
                                                <tr>
                                                    <td><input type="checkbox" name="events[]" value="<?php echo $this->_getEventNum('OnE2GFileEditFormPrerender'); ?>" <?php echo ( in_array($this->_getEventNum('OnE2GFileEditFormPrerender'), $events) ? 'checked="checked" ' : ''); ?>/> OnE2GFileEditFormPrerender</td>
                                                    <td><input type="checkbox" name="events[]" value="<?php echo $this->_getEventNum('OnE2GFileEditFormRender'); ?>" <?php echo ( in_array($this->_getEventNum('OnE2GFileEditFormRender'), $events) ? 'checked="checked" ' : ''); ?>/> OnE2GFileEditFormRender</td>
                                                </tr>
                                                <tr>
                                                    <td><input type="checkbox" name="events[]" value="<?php echo $this->_getEventNum('OnE2GFileEditFormSave'); ?>" <?php echo ( in_array($this->_getEventNum('OnE2GFileEditFormSave'), $events) ? 'checked="checked" ' : ''); ?>/> OnE2GFileEditFormSave</td>
                                                    <td><input type="checkbox" name="events[]" value="<?php echo $this->_getEventNum('OnE2GFileDelete'); ?>" <?php echo ( in_array($this->_getEventNum('OnE2GFileDelete'), $events) ? 'checked="checked" ' : ''); ?>/> OnE2GFileDelete</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2" style="font-weight:bold;"><?php echo $this->lng['zip']; ?></td>
                                                </tr>
                                                <tr>
                                                    <td><input type="checkbox" name="events[]" value="<?php echo $this->_getEventNum('OnE2GZipUploadFormPrerender'); ?>" <?php echo ( in_array($this->_getEventNum('OnE2GZipUploadFormPrerender'), $events) ? 'checked="checked" ' : ''); ?>/> OnE2GZipUploadFormPrerender</td>
                                                    <td><input type="checkbox" name="events[]" value="<?php echo $this->_getEventNum('OnE2GZipUploadFormRender'); ?>" <?php echo ( in_array($this->_getEventNum('OnE2GZipUploadFormRender'), $events) ? 'checked="checked" ' : ''); ?>/> OnE2GZipUploadFormRender</td>
                                                </tr>
                                                <tr>
                                                    <td><input type="checkbox" name="events[]" value="<?php echo $this->_getEventNum('OnE2GZipUpload'); ?>" <?php echo ( in_array($this->_getEventNum('OnE2GZipUpload'), $events) ? 'checked="checked" ' : ''); ?>/> OnE2GZipUpload</td>
                                                    <td>&nbsp;</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </li>
                                </ul>
                                <br />
                                <input type="submit" value="<?php echo $this->lng['save']; ?>" /> &nbsp; &nbsp;
                                <input type="reset" value="<?php echo $this->lng['reset']; ?>" /> &nbsp; &nbsp;
                                <input type="button" value="<?php echo $this->lng['cancel']; ?>" onclick="history.go(-1)" /> &nbsp; &nbsp;
                            </td>
                        </tr>
                    </table>
                <?php } ?>
            </form>
        </div>
        <?php
    } // if (isset($this->sanitizedGets['page']) && $this->sanitizedGets['page'] == 'edit_plugin')
    /**
     * for current
     */ else {
        ?>
        <div class="tab-page" id="tabPluginSettings">
            <h2 class="tab"><?php echo $this->lng['settings']; ?></h2>
            <script type="text/javascript">
                tpPlugin.addTabPage( document.getElementById( 'tabPluginSettings') );
            </script>
            <p><b><?php echo $this->lng['click_edit']; ?></b></p>
    <!--                    <form action="<?php echo $this->e2gModCfg['index']; ?>&amp;act=save_plugin" method="post">-->
            <ul>
                <?php
                $select_plugins = 'SELECT * FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_plugins ';
                $query_plugins = mysql_query($select_plugins);
                if (!$query_plugins)
                    die(__LINE__ . ': ' . mysql_errno() . ' ' . mysql_error() . '<br />' . $select_plugins);
                else {
                    $numrow_plugins = mysql_num_rows($query_plugins);
                    // include the event's names
                    $eventConfigFile = realpath(E2G_MODULE_PATH . 'includes/configs/config.events.easy2gallery.php');
                    if (empty($eventConfigFile) || !file_exists($eventConfigFile)) {
                        die(__FILE__ . ', ' . __LINE__ . ': missing config.events.easy2gallery.php file');
                    }
                    include $eventConfigFile;
                    while ($row = mysql_fetch_assoc($query_plugins)) {
                        ?>
                        <li>
                            <a href="<?php echo $this->e2gModCfg['index'] . '&amp;act=delete_plugin&amp;plugin_id=' . $row['id'];?>"
                               onclick="confirm('<?php echo $this->lng['js_delete_plugin_confirm']; ?>')"
                               title="<?php echo $this->lng['delete']; ?>">
                                <img src="<?php echo MODX_MANAGER_URL; ?>media/style/MODxCarbon/images/icons/delete.gif"
                                     width="16" height="16" border="0" alt="" />
                            </a>
                            <?php echo ($row['disabled'] == 1 ? '<span class="disabled">' : ''); ?>
                            <a href="<?php echo $this->e2gModCfg['index'] . '&amp;page=edit_plugin&amp;ssid=' . $row['id']; ?>"title="edit"><b><?php echo $row['name']; ?></b></a>
            <?php echo ($row['disabled'] == 1 ? '</span>' : ''); ?>
                            (<?php echo $row['id']; ?>) - <?php echo htmlspecialchars_decode($row['description']); ?>
                            <br /><i><?php echo $this->lng['index_file']; ?></i>: <?php echo $row['indexfile']; ?>
                            <br /><i><?php echo $this->lng['events_system']; ?></i>:
                            <?php
                            $event_names = array();
                            $events = @explode(',', $row['events']);
                            foreach ($events as $v) {
                                $v = trim($v);
                                $event_names[] = $e2gEvents[$v];
                            }
                            echo @implode(', ', $event_names);
                            ?>
                        </li>
                        <?php
                    }
                }
                ?>
            </ul>
            <!--            </form>-->
        </div><!--tabPluginSettings-->
<?php } ?>
    <div class="tab-page" id="tabPluginAdd">
        <h2 class="tab"><?php echo $this->lng['add']; ?></h2>
        <script type="text/javascript">
            tpPlugin.addTabPage( document.getElementById( 'tabPluginAdd') );
        </script>

        <form action="<?php echo $this->e2gModCfg['index']; ?>&amp;act=save_plugin" method="post">
            <table cellspacing="0" cellpadding="2">
                <tr>
                    <td><b><?php echo $this->lng['name']; ?></b></td>
                    <td valign="top">:</td>
                    <td> <input name="name" type="text" size="75" /></td>
                </tr>
                <tr>
                    <td valign="top"><b><?php echo $this->lng['description']; ?></b></td>
                    <td valign="top">:</td>
                    <td> <input name="description" type="text" size="75" /></td>
                </tr>
                <tr>
                    <td><b><?php echo $this->lng['plugin_disabled']; ?></b></td>
                    <td valign="top">:</td>
                    <td><input name="disabled" type="checkbox" size="75" /></td>
                </tr>
                <tr>
                    <td><b><?php echo $this->lng['index_file']; ?></b></td>
                    <td valign="top">:</td>
                    <td> <input name="index_file" type="text" size="75" /></td>
                </tr>
                <tr>
                    <td valign="top"><b><?php echo $this->lng['events_system']; ?></b></td>
                    <td valign="top">:</td>
                    <td><ul>
                            <li>Snippet <span style="color:green;font-style: italic;">(runtime)</span>:
                                <table border="0">
                                    <tbody>
                                        <tr>
                                            <td><input type="checkbox" name="events[]" value="<?php echo $this->_getEventNum('OnE2GWebThumbPrerender'); ?>" /> OnE2GWebThumbPrerender <span style="color:green;font-style: italic;">(thumb:pluginName#prerender)</span></td>
                                            <td><input type="checkbox" name="events[]" value="<?php echo $this->_getEventNum('OnE2GWebThumbRender'); ?>" /> OnE2GWebThumbRender <span style="color:green;font-style: italic;">(thumb:pluginName#render)</span></td>
                                        </tr>
                                        <tr>
                                            <td><input type="checkbox" name="events[]" value="<?php echo $this->_getEventNum('OnE2GWebDirPrerender'); ?>" /> OnE2GWebDirPrerender <span style="color:green;font-style: italic;">(dir:pluginName#prerender)</span></td>
                                            <td><input type="checkbox" name="events[]" value="<?php echo $this->_getEventNum('OnE2GWebDirRender'); ?>" /> OnE2GWebDirRender <span style="color:green;font-style: italic;">(dir:pluginName#render)</span></td>
                                        </tr>
                                        <tr>
                                            <td><input type="checkbox" name="events[]" value="<?php echo $this->_getEventNum('OnE2GWebGalleryPrerender'); ?>" /> OnE2GWebGalleryPrerender <span style="color:green;font-style: italic;">(gallery:pluginName#prerender)</span></td>
                                            <td><input type="checkbox" name="events[]" value="<?php echo $this->_getEventNum('OnE2GWebGalleryRender'); ?>" /> OnE2GWebGalleryRender <span style="color:green;font-style: italic;">(gallery:pluginName#render)</span></td>
                                        </tr>
                                        <tr>
                                            <td><input type="checkbox" name="events[]" value="<?php echo $this->_getEventNum('OnE2GWebLandingpagePrerender'); ?>" /> OnE2GWebLandingpagePrerender <span style="color:green;font-style: italic;">(landingpage:pluginName#prerender)</span></td>
                                            <td><input type="checkbox" name="events[]" value="<?php echo $this->_getEventNum('OnE2GWebLandingpageRender'); ?>" /> OnE2GWebLandingpageRender <span style="color:green;font-style: italic;">(landingpage:pluginName#render)</span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </li>
                            <li>Module :
                                <table border="0">
                                    <tbody>
                                        <tr>
                                            <td colspan="2" style="font-weight:bold;border-top: 1px dotted #ccc;"><?php echo $this->lng['header']; ?></td>
                                        </tr>
                                        <tr>
                                            <td><input type="checkbox" name="events[]" value="<?php echo $this->_getEventNum('OnE2GModHeadScript'); ?>" /> OnE2GModHeadScript</td>
                                            <td><input type="checkbox" name="events[]" value="<?php echo $this->_getEventNum('OnE2GModHeadCSSScript'); ?>" /> OnE2GModHeadCSSScript</td>
                                        </tr>
                                        <tr>
                                            <td><input type="checkbox" name="events[]" value="<?php echo $this->_getEventNum('OnE2GModHeadJSScript'); ?>" /> OnE2GModHeadJSScript</td>
                                            <td>&nbsp;</td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" style="font-weight:bold;border-top: 1px dotted #ccc;"><?php echo $this->lng['dashboard']; ?></td>
                                        </tr>
                                        <tr>
                                            <td><input type="checkbox" name="events[]" value="<?php echo $this->_getEventNum('OnE2GDashboardPrerender'); ?>" /> OnE2GDashboardPrerender</td>
                                            <td><input type="checkbox" name="events[]" value="<?php echo $this->_getEventNum('OnE2GDashboardRender'); ?>" /> OnE2GDashboardRender</td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" style="font-weight:bold;border-top: 1px dotted #ccc;"><?php echo $this->lng['mgr_files']; ?></td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" style="font-weight:bold;"><?php echo $this->lng['dir']; ?></td>
                                        </tr>
                                        <tr>
                                            <td><input type="checkbox" name="events[]" value="<?php echo $this->_getEventNum('OnE2GFolderCreateFormPrerender'); ?>" /> OnE2GFolderCreateFormPrerender</td>
                                            <td><input type="checkbox" name="events[]" value="<?php echo $this->_getEventNum('OnE2GFolderCreateFormRender'); ?>" /> OnE2GFolderCreateFormRender</td>
                                        </tr>
                                        <tr>
                                            <td><input type="checkbox" name="events[]" value="<?php echo $this->_getEventNum('OnE2GFolderCreateFormSave'); ?>" /> OnE2GFolderCreateFormSave</td>
                                            <td><input type="checkbox" name="events[]" value="<?php echo $this->_getEventNum('OnE2GFolderAdd'); ?>" /> OnE2GFolderAdd</td>
                                        </tr>
                                        <tr>
                                            <td><input type="checkbox" name="events[]" value="<?php echo $this->_getEventNum('OnE2GFolderEditFormPrerender'); ?>" /> OnE2GFolderEditFormPrerender</td>
                                            <td><input type="checkbox" name="events[]" value="<?php echo $this->_getEventNum('OnE2GFolderEditFormRender'); ?>" /> OnE2GFolderEditFormRender</td>
                                        </tr>
                                        <tr>
                                            <td><input type="checkbox" name="events[]" value="<?php echo $this->_getEventNum('OnE2GFolderEditFormSave'); ?>" /> OnE2GFolderEditFormSave</td>
                                            <td><input type="checkbox" name="events[]" value="<?php echo $this->_getEventNum('OnE2GFolderDelete'); ?>" /> OnE2GFolderDelete</td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" style="font-weight:bold;"><?php echo $this->lng['file']; ?></td>
                                        </tr>
                                        <tr>
                                            <td><input type="checkbox" name="events[]" value="<?php echo $this->_getEventNum('OnE2GFileUploadFormPrerender'); ?>" /> OnE2GFileUploadFormPrerender</td>
                                            <td><input type="checkbox" name="events[]" value="<?php echo $this->_getEventNum('OnE2GFileUploadFormRender'); ?>" /> OnE2GFileUploadFormRender</td>
                                        </tr>
                                        <tr>
                                            <td><input type="checkbox" name="events[]" value="<?php echo $this->_getEventNum('OnE2GFileUpload'); ?>" /> OnE2GFileUpload</td>
                                            <td><input type="checkbox" name="events[]" value="<?php echo $this->_getEventNum('OnE2GFileAdd'); ?>" /> OnE2GFileAdd</td>
                                        </tr>
                                        <tr>
                                            <td><input type="checkbox" name="events[]" value="<?php echo $this->_getEventNum('OnE2GFileEditFormPrerender'); ?>" /> OnE2GFileEditFormPrerender</td>
                                            <td><input type="checkbox" name="events[]" value="<?php echo $this->_getEventNum('OnE2GFileEditFormRender'); ?>" /> OnE2GFileEditFormRender</td>
                                        </tr>
                                        <tr>
                                            <td><input type="checkbox" name="events[]" value="<?php echo $this->_getEventNum('OnE2GFileEditFormSave'); ?>" /> OnE2GFileEditFormSave</td>
                                            <td><input type="checkbox" name="events[]" value="<?php echo $this->_getEventNum('OnE2GFileDelete'); ?>" /> OnE2GFileDelete</td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" style="font-weight:bold;"><?php echo $this->lng['zip']; ?></td>
                                        </tr>
                                        <tr>
                                            <td><input type="checkbox" name="events[]" value="<?php echo $this->_getEventNum('OnE2GZipUploadFormPrerender'); ?>" /> OnE2GZipUploadFormPrerender</td>
                                            <td><input type="checkbox" name="events[]" value="<?php echo $this->_getEventNum('OnE2GZipUploadFormRender'); ?>" /> OnE2GZipUploadFormRender</td>
                                        </tr>
                                        <tr>
                                            <td><input type="checkbox" name="events[]" value="<?php echo $this->_getEventNum('OnE2GZipUpload'); ?>" /> OnE2GZipUpload</td>
                                            <td>&nbsp;</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </li>
                        </ul>
                        <br />
                        <input type="submit" value="<?php echo $this->lng['save']; ?>" /> &nbsp; &nbsp;
                        <input type="reset" value="<?php echo $this->lng['reset']; ?>" /> &nbsp; &nbsp;
                    </td>
                </tr>
            </table>
        </form>
    </div><!--tabPluginAdd-->
</div>