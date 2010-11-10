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
    if (isset($_GET['page']) && $_GET['page'] == 'edit_viewer') {
    ?>
        <div class="tab-page" id="tabThumbViewerEdit">
            <h2 class="tab"><?php echo $lng['edit']; ?></h2>
            <script type="text/javascript">
                tpThumbViewer.addTabPage( document.getElementById( 'tabThumbViewerEdit') );
            </script>
            <form action="<?php echo $index; ?>&amp;act=update_viewer" method="post">
            <?php
            $select_viewers = 'SELECT * FROM ' . $modx->db->config['table_prefix'] . 'easy2_viewers WHERE id=' . $_GET['ssid'];
            $query_viewers = mysql_query($select_viewers);
            if (!$query_viewers)
                die(__LINE__ . ': ' . mysql_errno() . ' ' . mysql_error() . '<br />' . $select_viewers);
            else {
                $numrow_viewers = mysql_num_rows($query_viewers);
                $row = mysql_fetch_array($query_viewers);
            ?>
                <p>ID: <?php echo $row['id']; ?></p>
                <input type="hidden" name="viewer_id" value="<?php echo $row['id']; ?>" />
                <table cellspacing="0" cellpadding="2">
                    <tr>
                        <td><b><?php echo $lng['name']; ?></b></td>
                        <td valign="top">:</td>
                        <td> <input name="name" type="text" size="75" value="<?php echo $row['name']; ?>" /> *) <?php echo $lng['required']; ?></td>
                    </tr>
                    <tr>
                        <td colspan="2">&nbsp;</td>
                        <td><?php echo $lng['viewer_desc_name']; ?></td>
                    </tr>
                    <tr>
                        <td><b><?php echo $lng['alias']; ?></b></td>
                        <td valign="top">:</td>
                        <td> <input name="alias" type="text" size="75" value="<?php echo $row['alias']; ?>" /> *) <?php echo $lng['required']; ?></td>
                    </tr>
                    <tr>
                        <td colspan="2">&nbsp;</td>
                        <td><?php echo $lng['viewer_desc_alias']; ?></td>
                    </tr>
                    <tr>
                        <td valign="top"><b><?php echo $lng['description']; ?></b></td>
                        <td valign="top">:</td>
                        <td> <textarea cols="" rows="3" name="description" style="width: 450px;"><?php echo $row['description']; ?></textarea></td>
                    </tr>
                    <tr>
                        <td><b><?php echo $lng['viewer_disabled']; ?></b></td>
                        <td valign="top">:</td>
                        <td><input name="disabled" type="checkbox" value="1" <?php echo ($row['disabled'] == 1 ? 'checked="checked" ' : ''); ?>/></td>
                    </tr>
                    <tr>
                        <td valign="top"><b>CSS <?php echo $lng['viewer_headers']; ?></b></td>
                        <td valign="top">:</td>
                        <td> <textarea cols="" rows="3" name="headers_css" style="width: 450px;"><?php echo $row['headers_css']; ?></textarea></td>
                    </tr>
                    <tr>
                        <td colspan="2">&nbsp;</td>
                        <td><?php echo htmlspecialchars_decode($lng['viewer_desc_headers']); ?></td>
                    </tr>
                    <tr>
                        <td colspan="2">&nbsp;</td>
                        <td> <input type="checkbox" value="1" name="autoload_css" <?php echo ($row['autoload_css'] == 1 ? 'checked="checked" ' : ''); ?>/> <?php echo $lng['viewer_autoload']; ?></td>
                    </tr>
                    <tr>
                        <td colspan="2">&nbsp;</td>
                        <td><?php echo htmlspecialchars_decode($lng['viewer_desc_autoload']); ?></td>
                    </tr>
                    <tr>
                        <td valign="top"><b>JavaScript <?php echo $lng['viewer_headers']; ?></b></td>
                        <td valign="top">:</td>
                        <td> <textarea cols="" rows="3" name="headers_js" style="width: 450px;"><?php echo $row['headers_js']; ?></textarea></td>
                    </tr>
                    <tr>
                        <td colspan="2">&nbsp;</td>
                        <td><?php echo htmlspecialchars_decode($lng['viewer_desc_headers']); ?></td>
                    </tr>
                    <tr>
                        <td colspan="2">&nbsp;</td>
                        <td> <input type="checkbox" value="1" name="autoload_js" <?php echo ($row['autoload_js'] == 1 ? 'checked="checked" ' : ''); ?>/> <?php echo $lng['viewer_autoload']; ?></td>
                    </tr>
                    <tr>
                        <td colspan="2">&nbsp;</td>
                        <td><?php echo htmlspecialchars_decode($lng['viewer_desc_autoload']); ?></td>
                    </tr>
                    <tr>
                        <td valign="top"><b>HTML block <?php echo $lng['viewer_headers']; ?></b></td>
                        <td valign="top">:</td>
                        <td> <textarea cols="" rows="3" name="headers_html" style="width: 450px;"><?php echo $row['headers_html']; ?></textarea></td>
                    </tr>
                    <tr>
                        <td colspan="2">&nbsp;</td>
                        <td><?php echo htmlspecialchars_decode($lng['viewer_desc_html_headers']); ?></td>
                    </tr>
                    <tr>
                        <td colspan="2">&nbsp;</td>
                        <td> <input type="checkbox" value="1" name="autoload_html" <?php echo ($row['autoload_html'] == 1 ? 'checked="checked" ' : ''); ?>/> <?php echo $lng['viewer_autoload']; ?></td>
                    </tr>
                    <tr>
                        <td colspan="2">&nbsp;</td>
                        <td><?php echo htmlspecialchars_decode($lng['viewer_desc_autoload']); ?></td>
                    </tr>
                    <tr>
                        <td valign="top"><b><?php echo $lng['viewer_action_glib']; ?></b></td>
                        <td valign="top">:</td>
                        <td> <textarea cols="" rows="3" name="glibact" style="width: 450px;"><?php echo $row['glibact']; ?></textarea></td>
                    </tr>
                    <tr>
                        <td colspan="2">&nbsp;</td>
                        <td><?php echo htmlspecialchars_decode($lng['viewer_action_desc_glib']); ?></td>
                    </tr>
                    <tr>
                        <td valign="top"><b><?php echo $lng['viewer_action_clib']; ?></b></td>
                        <td valign="top">:</td>
                        <td> <textarea cols="" rows="3" name="clibact" style="width: 450px;"><?php echo $row['clibact']; ?></textarea></td>
                    </tr>
                    <tr>
                        <td colspan="2">&nbsp;</td>
                        <td><?php echo htmlspecialchars_decode($lng['viewer_action_desc_clib']); ?></td>
                    </tr>
                    <tr>
                        <td colspan="2">&nbsp;</td>
                        <td>
                            <br />
                            <input type="submit" value="<?php echo $lng['save']; ?>" /> &nbsp; &nbsp;
                            <input type="reset" value="<?php echo $lng['reset']; ?>" /> &nbsp; &nbsp;
                            <input type="button" value="<?php echo $lng['cancel']; ?>" onclick="document.location.href='<?php echo $index; ?>'"/> &nbsp; &nbsp;
                        </td>
                    </tr>
                </table>
            <?php } ?>
        </form>
    </div>
    <?php
        } // if (isset($_GET['page']) && $_GET['page'] == 'edit_viewer')
        /**
         * for current
         */ else {
    ?>
            <div class="tab-page" id="tabThumbViewerSettings">
                <h2 class="tab"><?php echo $lng['settings']; ?></h2>
                <script type="text/javascript">
                    tpThumbViewer.addTabPage( document.getElementById( 'tabThumbViewerSettings') );
                </script>
                <p><b><?php echo $lng['click_edit']; ?></b></p>
        <?php
            $select_viewers = 'SELECT * FROM ' . $modx->db->config['table_prefix'] . 'easy2_viewers ';
            $query_viewers = mysql_query($select_viewers);
            if (!$query_viewers)
                die(__LINE__ . ' : ' . mysql_error() . '<br />' . $select_viewers);
            else {
                $numrow_viewers = mysql_num_rows($query_viewers);
        ?>
                <ul>
            <?php while ($row = mysql_fetch_array($query_viewers)) {
            ?>
                    <li>
                <?php echo ($row['disabled'] == 1 ? '<span class="disabled">' : ''); ?>
                    <a href="<?php echo $index . '&amp;page=edit_viewer&amp;ssid=' . $row['id']; ?>"title="edit"><b><?php echo $row['name']; ?></b></a>
                <?php echo ($row['disabled'] == 1 ? '</span>' : ''); ?>
                    (<?php echo $row['id']; ?>) - <?php echo htmlspecialchars_decode($row['description']); ?>
                </li>
            <?php } ?>
            </ul>
        <?php } ?>
        </div>
    <?php } ?>


        <div class="tab-page" id="tabThumbViewerAdd">
            <h2 class="tab"><?php echo $lng['add']; ?></h2>
            <script type="text/javascript">
                tpThumbViewer.addTabPage( document.getElementById( 'tabThumbViewerAdd') );
            </script>
            <form action="<?php echo $index; ?>&amp;act=save_viewer" method="post">
                <table cellspacing="0" cellpadding="2">
                    <tr>
                        <td><b><?php echo $lng['name']; ?></b></td>
                        <td valign="top">:</td>
                        <td> <input name="name" type="text" size="75" /> *) <?php echo $lng['required']; ?></td>
                    </tr>
                    <tr>
                        <td colspan="2">&nbsp;</td>
                        <td><?php echo $lng['viewer_desc_name']; ?></td>
                    </tr>
                    <tr>
                        <td><b><?php echo $lng['alias']; ?></b></td>
                        <td valign="top">:</td>
                        <td> <input name="alias" type="text" size="75" /> *) <?php echo $lng['required']; ?></td>
                    </tr>
                    <tr>
                        <td colspan="2">&nbsp;</td>
                        <td><?php echo $lng['viewer_desc_alias']; ?></td>
                    </tr>
                    <tr>
                        <td valign="top"><b><?php echo $lng['description']; ?></b></td>
                        <td valign="top">:</td>
                        <td> <textarea cols="" rows="3" name="description" style="width: 450px;"></textarea></td>
                    </tr>
                    <tr>
                        <td><b><?php echo $lng['viewer_disabled']; ?></b></td>
                        <td valign="top">:</td>
                        <td><input name="disabled" type="checkbox" value="1" /></td>
                    </tr>
                    <tr>
                        <td valign="top"><b>CSS <?php echo $lng['viewer_headers']; ?></b></td>
                        <td valign="top">:</td>
                        <td> <textarea cols="" rows="3" name="headers_css" style="width: 450px;"></textarea></td>
                    </tr>
                    <tr>
                        <td colspan="2">&nbsp;</td>
                        <td><?php echo htmlspecialchars_decode($lng['viewer_desc_headers']); ?></td>
                    </tr>
                    <tr>
                        <td colspan="2">&nbsp;</td>
                        <td> <input type="checkbox" value="1" name="autoload_css" /> <?php echo $lng['viewer_autoload']; ?></td>
                    </tr>
                    <tr>
                        <td colspan="2">&nbsp;</td>
                        <td><?php echo htmlspecialchars_decode($lng['viewer_desc_autoload']); ?></td>
                    </tr>
                    <tr>
                        <td valign="top"><b>JavaScript <?php echo $lng['viewer_headers']; ?></b></td>
                        <td valign="top">:</td>
                        <td> <textarea cols="" rows="3" name="headers_js" style="width: 450px;"></textarea></td>
                    </tr>
                    <tr>
                        <td colspan="2">&nbsp;</td>
                        <td><?php echo htmlspecialchars_decode($lng['viewer_desc_headers']); ?></td>
                    </tr>
                    <tr>
                        <td colspan="2">&nbsp;</td>
                        <td> <input type="checkbox" value="1" name="autoload_js" /> <?php echo $lng['viewer_autoload']; ?></td>
                    </tr>
                    <tr>
                        <td colspan="2">&nbsp;</td>
                        <td><?php echo htmlspecialchars_decode($lng['viewer_desc_autoload']); ?></td>
                    </tr>
                    <tr>
                        <td valign="top"><b>HTML block <?php echo $lng['viewer_headers']; ?></b></td>
                        <td valign="top">:</td>
                        <td> <textarea cols="" rows="3" name="headers_html" style="width: 450px;"></textarea></td>
                    </tr>
                    <tr>
                        <td colspan="2">&nbsp;</td>
                        <td><?php echo htmlspecialchars_decode($lng['viewer_desc_html_headers']); ?></td>
                    </tr>
                    <tr>
                        <td colspan="2">&nbsp;</td>
                        <td> <input type="checkbox" value="1" name="autoload_html" /> <?php echo $lng['viewer_autoload']; ?></td>
                    </tr>
                    <tr>
                        <td colspan="2">&nbsp;</td>
                        <td><?php echo htmlspecialchars_decode($lng['viewer_desc_autoload']); ?></td>
                    </tr>
                    <tr>
                        <td valign="top"><b><?php echo $lng['viewer_action_glib']; ?></b></td>
                        <td valign="top">:</td>
                        <td> <textarea cols="" rows="3" name="glibact" style="width: 450px;"></textarea></td>
                    </tr>
                    <tr>
                        <td colspan="2">&nbsp;</td>
                        <td><?php echo htmlspecialchars_decode($lng['viewer_action_desc_glib']); ?></td>
                    </tr>
                    <tr>
                        <td valign="top"><b><?php echo $lng['viewer_action_clib']; ?></b></td>
                        <td valign="top">:</td>
                        <td> <textarea cols="" rows="3" name="clibact" style="width: 450px;"></textarea></td>
                    </tr>
                    <tr>
                        <td colspan="2">&nbsp;</td>
                        <td><?php echo htmlspecialchars_decode($lng['viewer_action_desc_clib']); ?></td>
                    </tr>
                    <tr>
                        <td colspan="2">&nbsp;</td>
                        <td>
                            <br />
                            <input type="submit" value="<?php echo $lng['save']; ?>" /> &nbsp; &nbsp;
                            <input type="reset" value="<?php echo $lng['reset']; ?>" /> &nbsp; &nbsp;
                    </td>
                </tr>
            </table>
        </form>
    </div>
</div>