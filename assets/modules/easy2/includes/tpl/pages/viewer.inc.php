<?php
if (IN_MANAGER_MODE != 'true')
    die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
?>
<div class="tab-pane" id="tabThumbViewerPane">
    <script type="text/javascript">
        tpThumbViewer = new WebFXTabPane(document.getElementById('tabThumbViewerPane'));
    </script>
    <?php
    /**
     * for edit list
     */
    if (isset($this->sanitizedGets['page'])) {
        ?>
        <div class="tab-page" id="tabThumbViewerEdit">
            <h2 class="tab"><?php echo $this->lng['edit']; ?></h2>
            <script type="text/javascript">
                tpThumbViewer.addTabPage( document.getElementById( 'tabThumbViewerEdit') );
            </script>
            <?php
            include_once (dirname(__FILE__) . DIRECTORY_SEPARATOR . "viewer.edit.inc.php");
            ?>
        </div>
        <?php
    } // if (isset($this->sanitizedGets['page']) && $this->sanitizedGets['page'] == 'edit_viewer')
    /**
     * for current
     */ else {
        ?>
        <div class="tab-page" id="tabThumbViewerSettings">
            <h2 class="tab"><?php echo $this->lng['settings']; ?></h2>
            <script type="text/javascript">
                tpThumbViewer.addTabPage( document.getElementById( 'tabThumbViewerSettings') );
            </script>
            <p><b><?php echo $this->lng['click_edit']; ?></b></p>
            <?php
            $select_viewers = 'SELECT * FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_viewers ';
            $query_viewers = mysql_query($select_viewers);
            if (!$query_viewers)
                die(__LINE__ . ' : ' . mysql_error() . '<br />' . $select_viewers);
            else {
                $numrow_viewers = mysql_num_rows($query_viewers);
                ?>
                <ul>
                    <?php while ($row = mysql_fetch_assoc($query_viewers)) {
                        ?>
                        <li>
                            <a href="<?php echo $this->e2gModCfg['index'] . '&amp;page=duplicate_viewer&amp;viewer_id=' . $row['id']; ?>"
                               onclick="return confirm('<?php echo $this->lng['js_duplicate_viewer_confirm']; ?>')"
                               title="<?php echo $this->lng['copy']; ?>">
                                <img src="<?php echo MODX_MANAGER_URL; ?>media/style/MODxCarbon/images/icons/copy.gif"
                                     width="16" height="16" border="0" alt="" />
                            </a>
                            <a href="<?php echo $this->e2gModCfg['index'] . '&amp;act=delete_viewer&amp;viewer_id=' . $row['id']; ?>"
                               onclick="return confirm('<?php echo $this->lng['js_delete_viewer_confirm']; ?>')"
                               title="<?php echo $this->lng['duplicate']; ?>">
                                <img src="<?php echo MODX_MANAGER_URL; ?>media/style/MODxCarbon/images/icons/delete.gif"
                                     width="16" height="16" border="0" alt="" />
                            </a>
                            <?php echo ($row['disabled'] == 1 ? '<span class="disabled">' : ''); ?>
                            <a href="<?php echo $this->e2gModCfg['index'] . '&amp;page=edit_viewer&amp;viewer_id=' . $row['id']; ?>" title="edit"><b><?php echo $row['name']; ?></b></a>
                            <?php echo ($row['disabled'] == 1 ? '</span>' : ''); ?>
                            (<?php echo $row['id']; ?>) - <?php echo htmlspecialchars_decode($row['description']); ?>
                        </li>
                    <?php } ?>
                </ul>
            <?php } ?>
        </div>
    <?php } ?>


    <div class="tab-page" id="tabThumbViewerAdd">
        <h2 class="tab"><?php echo $this->lng['add']; ?></h2>
        <script type="text/javascript">
            tpThumbViewer.addTabPage( document.getElementById( 'tabThumbViewerAdd') );
        </script>
        <form action="<?php echo $this->e2gModCfg['index']; ?>&amp;act=save_viewer" method="post">
            <table cellspacing="0" cellpadding="2">
                <tr>
                    <td><b><?php echo $this->lng['name']; ?></b></td>
                    <td valign="top">:</td>
                    <td> <input name="name" type="text" size="75" /> *) <?php echo $this->lng['required']; ?></td>
                </tr>
                <tr>
                    <td colspan="2">&nbsp;</td>
                    <td><?php echo $this->lng['viewer_desc_name']; ?></td>
                </tr>
                <tr>
                    <td><b><?php echo $this->lng['alias']; ?></b></td>
                    <td valign="top">:</td>
                    <td> <input name="alias" type="text" size="75" /> *) <?php echo $this->lng['required']; ?></td>
                </tr>
                <tr>
                    <td colspan="2">&nbsp;</td>
                    <td><?php echo $this->lng['viewer_desc_alias']; ?></td>
                </tr>
                <tr>
                    <td valign="top"><b><?php echo $this->lng['description']; ?></b></td>
                    <td valign="top">:</td>
                    <td> <textarea cols="" rows="3" name="description" style="width: 450px;"></textarea></td>
                </tr>
                <tr>
                    <td><b><?php echo $this->lng['viewer_disabled']; ?></b></td>
                    <td valign="top">:</td>
                    <td><input name="disabled" type="checkbox" value="1" /></td>
                </tr>
                <tr>
                    <td valign="top"><b>CSS <?php echo $this->lng['viewer_headers']; ?></b></td>
                    <td valign="top">:</td>
                    <td> <textarea cols="" rows="3" name="headers_css" style="width: 450px;"></textarea></td>
                </tr>
                <tr>
                    <td colspan="2">&nbsp;</td>
                    <td><?php echo htmlspecialchars_decode($this->lng['viewer_desc_headers']); ?></td>
                </tr>
                <tr>
                    <td colspan="2">&nbsp;</td>
                    <td> <input type="checkbox" value="1" name="autoload_css" /> <?php echo $this->lng['viewer_autoload']; ?></td>
                </tr>
                <tr>
                    <td colspan="2">&nbsp;</td>
                    <td><?php echo htmlspecialchars_decode($this->lng['viewer_desc_autoload']); ?></td>
                </tr>
                <tr>
                    <td valign="top"><b>JavaScript <?php echo $this->lng['viewer_headers']; ?></b></td>
                    <td valign="top">:</td>
                    <td> <textarea cols="" rows="3" name="headers_js" style="width: 450px;"></textarea></td>
                </tr>
                <tr>
                    <td colspan="2">&nbsp;</td>
                    <td><?php echo htmlspecialchars_decode($this->lng['viewer_desc_headers']); ?></td>
                </tr>
                <tr>
                    <td colspan="2">&nbsp;</td>
                    <td> <input type="checkbox" value="1" name="autoload_js" /> <?php echo $this->lng['viewer_autoload']; ?></td>
                </tr>
                <tr>
                    <td colspan="2">&nbsp;</td>
                    <td><?php echo htmlspecialchars_decode($this->lng['viewer_desc_autoload']); ?></td>
                </tr>
                <tr>
                    <td valign="top"><b>HTML block <?php echo $this->lng['viewer_headers']; ?></b></td>
                    <td valign="top">:</td>
                    <td> <textarea cols="" rows="3" name="headers_html" style="width: 450px;"></textarea></td>
                </tr>
                <tr>
                    <td colspan="2">&nbsp;</td>
                    <td><?php echo htmlspecialchars_decode($this->lng['viewer_desc_html_headers']); ?></td>
                </tr>
                <tr>
                    <td colspan="2">&nbsp;</td>
                    <td> <input type="checkbox" value="1" name="autoload_html" /> <?php echo $this->lng['viewer_autoload']; ?></td>
                </tr>
                <tr>
                    <td colspan="2">&nbsp;</td>
                    <td><?php echo htmlspecialchars_decode($this->lng['viewer_desc_autoload']); ?></td>
                </tr>
                <tr>
                    <td valign="top"><b><?php echo $this->lng['viewer_action_glib']; ?></b></td>
                    <td valign="top">:</td>
                    <td> <textarea cols="" rows="3" name="glibact" style="width: 450px;"></textarea></td>
                </tr>
                <tr>
                    <td colspan="2">&nbsp;</td>
                    <td><?php echo htmlspecialchars_decode($this->lng['viewer_action_desc_glib']); ?></td>
                </tr>
                <tr>
                    <td valign="top"><b><?php echo $this->lng['viewer_action_clib']; ?></b></td>
                    <td valign="top">:</td>
                    <td> <textarea cols="" rows="3" name="clibact" style="width: 450px;"></textarea></td>
                </tr>
                <tr>
                    <td colspan="2">&nbsp;</td>
                    <td><?php echo htmlspecialchars_decode($this->lng['viewer_action_desc_clib']); ?></td>
                </tr>
                <tr>
                    <td colspan="2">&nbsp;</td>
                    <td>
                        <br />
                        <input type="submit" value="<?php echo $this->lng['save']; ?>" /> &nbsp; &nbsp;
                        <input type="reset" value="<?php echo $this->lng['reset']; ?>" /> &nbsp; &nbsp;
                    </td>
                </tr>
            </table>
        </form>
    </div>
</div>