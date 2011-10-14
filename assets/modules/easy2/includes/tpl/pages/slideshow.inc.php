<?php
if (IN_MANAGER_MODE != 'true')
    die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
?>
<div class="tab-pane" id="tabSlideshowPane">
    <script type="text/javascript">
        tpSlideshow = new WebFXTabPane(document.getElementById('tabSlideshowPane'));
    </script>
    <?php
    /**
     * for edit list
     */
    if (isset($_GET['page']) && $_GET['page'] == 'edit_slideshow') {
    ?>
        <div class="tab-page" id="tabSlideshowEdit">
            <h2 class="tab"><?php echo $this->lng['edit']; ?></h2>
            <script type="text/javascript">
                tpSlideshow.addTabPage( document.getElementById( 'tabSlideshowEdit') );
            </script>
            <form action="<?php echo $this->e2gModCfg['index']; ?>&amp;act=update_slideshow" method="post">
            <?php
            $select_slideshows = 'SELECT * FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_slideshows WHERE id=' . $_GET['ssid'];
            $query_slideshows = mysql_query($select_slideshows);
            if (!$query_slideshows)
                die(__LINE__ . ': ' . mysql_errno() . ' ' . mysql_error() . '<br />' . $select_slideshows);
            else {
                $numrow_slideshows = mysql_num_rows($query_slideshows);
                $row = mysql_fetch_array($query_slideshows);
            ?>
                <p>ID: <?php echo $row['id']; ?></p>
                <input type="hidden" name="slideshow_id" value="<?php echo $row['id']; ?>" />
                <table cellspacing="0" cellpadding="2">
                    <tr>
                        <td><b><?php echo $this->lng['name']; ?>:</b></td>
                        <td><input name="name" type="text" size="75" value="<?php echo $row['name']; ?>" /></td>
                    </tr>
                    <tr>
                        <td valign="top"><b><?php echo $this->lng['description']; ?>:</b></td>
                        <td><input name="description" type="text" size="75" value="<?php echo $row['description']; ?>" /></td>
                    </tr>
                    <tr>
                        <td><b><?php echo $this->lng['index_file']; ?>:</b></td>
                        <td><input name="index_file" type="text" size="75" value="<?php echo $row['indexfile']; ?>" /></td>
                </tr>
<?php } ?>
                <tr>
                    <td></td>
                    <td>
                        <br />
                        <input type="submit" value="<?php echo $this->lng['save']; ?>" /> &nbsp; &nbsp; &nbsp;
                        <input type="button" value="<?php echo $this->lng['cancel']; ?>" onclick="history.go(-1)" /> &nbsp; &nbsp; &nbsp;
                    </td>
                </tr>
            </table>
        </form>
    </div>

    <?php
        }
        /**
         * for current + add list
         */ else {
    ?>
            <div class="tab-page" id="tabSlideshowSettings">
                <h2 class="tab"><?php echo $this->lng['settings']; ?></h2>
                <script type="text/javascript">
                    tpSlideshow.addTabPage( document.getElementById( 'tabSlideshowSettings') );
                </script>
                <p><b><?php echo $this->lng['click_edit']; ?></b></p>
        <form action="<?php echo $this->e2gModCfg['index']; ?>&amp;act=save_slideshow" method="post">
            <ul>
                <?php
                $select_slideshows = 'SELECT * FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_slideshows ';
                $query_slideshows = mysql_query($select_slideshows);
                if (!$query_slideshows)
                    die(__LINE__ . ': ' . mysql_errno() . ' ' . mysql_error() . '<br />' . $select_slideshows);
                else {
                    $numrow_slideshows = mysql_num_rows($query_slideshows);
                    while ($row = mysql_fetch_array($query_slideshows)) {
                ?>
                    <li>
                        <a href="<?php echo $this->e2gModCfg['index'] . '&amp;act=delete_slideshow&amp;ssid=' . $row['id'];?>"
                           onclick="confirm('<?php echo $this->lng['js_delete_slideshow_confirm']; ?>')"
                           title="<?php echo $this->lng['delete']; ?>">
                            <img src="<?php echo MODX_MANAGER_URL; ?>media/style/MODxCarbon/images/icons/delete.gif"
                                 width="16" height="16" border="0" alt="" />
                        </a>
                        <a href="<?php echo $this->e2gModCfg['index'] . '&amp;page=edit_slideshow&amp;ssid=' . $row['id']; ?>"title="edit">
                            <b><?php echo $row['name']; ?></b>
                        </a> (<?php echo $row['id']; ?>) -
                <?php echo htmlspecialchars_decode($row['description']); ?>
                            <br /><i><?php echo $this->lng['index_file']; ?></i>: <?php echo $row['indexfile']; ?>
                        </li>
<?php
                    }
                } ?>
                        </ul>
                    </form>
                </div>
<?php } ?>
            <div class="tab-page" id="tabSlideshowAdd">
                <h2 class="tab"><?php echo $this->lng['add']; ?></h2>
                <script type="text/javascript">
                    tpSlideshow.addTabPage( document.getElementById( 'tabSlideshowAdd') );
                </script>
                <form action="<?php echo $this->e2gModCfg['index']; ?>&amp;act=save_slideshow" method="post">
                    <div id="firstElt">
                        <table cellspacing="0" cellpadding="2">
                            <tr>
                                <td><b><?php echo $this->lng['name']; ?></b></td>
                                <td>: <input name="name[]" type="text" size="75" /></td>
                            </tr>
                            <tr>
                                <td valign="top"><b><?php echo $this->lng['description']; ?></b></td>
                                <td>: <input name="description[]" type="text" size="75" /></td>
                            </tr>
                            <tr>
                                <td><b><?php echo $this->lng['index_file']; ?></b></td>
                                <td>: <input name="index_file[]" type="text" size="75" /></td>
                            </tr>
                        </table>
                    </div>
                    <div id="secondElt"></div>
                    <br />
                    <div style="margin-left: 75px;">
                        <input type="submit" value="<?php echo $this->lng['save']; ?>" /> &nbsp; &nbsp; &nbsp;
                        <input type="button" value="<?php echo $this->lng['btn_field_add_slideshow']; ?>" onclick="addSlideshow(); void(0);" />
            </div>
        </form>
    </div>
</div>