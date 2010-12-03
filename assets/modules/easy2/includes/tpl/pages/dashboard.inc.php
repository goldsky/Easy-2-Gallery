<?php
if (IN_MANAGER_MODE != 'true')
    die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

$icon_ok = '<img src="' . MODX_BASE_URL . 'assets/modules/easy2/includes/tpl/icons/action_check.png" alt="OK" /> ';
$icon_bad = '<img src="' . MODX_BASE_URL . 'assets/modules/easy2/includes/tpl/icons/action_delete.png" alt="Not OK" /> ';

echo $this->_plugin('OnE2GDashboardPrerender');
?>
<div class="dashboardContent">
    <table style="width: 100%;">
        <tr>
            <td class="tdLeft">
                <div class="curveBox">
                    <div class="h2title"><?php echo $lng['info_gallery']; ?></div>
                    <table>
                        <tr>
                            <td><?php echo $lng['folders']; ?></td>
                            <td>:</td>
                            <td>
                                <?php
                                echo $modx->db->getValue($modx->db->query('SELECT COUNT(cat_id) FROM ' . $modx->db->config['table_prefix'] . 'easy2_dirs '));
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td><?php echo $lng['folders_hidden']; ?></td>
                            <td>:</td>
                            <td>
                                <?php
                                echo $modx->db->getValue($modx->db->query('SELECT COUNT(cat_id) FROM ' . $modx->db->config['table_prefix'] . 'easy2_dirs WHERE cat_visible=\'0\''));
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td><?php echo $lng['files']; ?></td>
                            <td>:</td>
                            <td>
                                <?php
                                echo $modx->db->getValue($modx->db->query('SELECT COUNT(id) FROM ' . $modx->db->config['table_prefix'] . 'easy2_files '));
                                ?>
                                (
                                <?php
                                $fileSizeArray = $modx->db->makeArray($modx->db->query('SELECT size FROM ' . $modx->db->config['table_prefix'] . 'easy2_files '));
                                $capacity = (int) 0;
                                foreach ($fileSizeArray as $k => $v) {
                                    $capacity += (int) $v['size'];
                                }
                                if ($capacity < 1024)
                                    echo $capacity . ' bytes';
                                elseif ($capacity < 1048576)
                                    echo round($capacity / 1024, 2) . ' ' . $lng['kilobytes'];
                                else
                                    echo round($capacity / 1048576, 2) . ' ' . $lng['megabytes'];
                                ?>
                                )
                            </td>
                        </tr>
                        <tr>
                            <td><?php echo $lng['files_hidden']; ?></td>
                            <td>:</td>
                            <td>
                                <?php
                                echo $modx->db->getValue($modx->db->query('SELECT COUNT(id) FROM ' . $modx->db->config['table_prefix'] . 'easy2_files WHERE status=\'0\''));
                                ?>
                                (
                                <?php
                                $fileSizeArray = $modx->db->makeArray($modx->db->query('SELECT size FROM ' . $modx->db->config['table_prefix'] . 'easy2_files WHERE status=\'0\''));
                                $capacity = (int) 0;
                                foreach ($fileSizeArray as $k => $v) {
                                    $capacity += (int) $v['size'];
                                }
                                if ($capacity < 1024)
                                    echo $capacity . " bytes";
                                elseif ($capacity < 1048576)
                                    echo round($capacity / 1024, 2) . ' ' . $lng['kilobytes'];
                                else
                                    echo round($capacity / 1048576, 2) . ' ' . $lng['megabytes'];
                                ?>
                                )
                            </td>
                        </tr>
                    </table>
                </div>
                <!--div class="curveBox">
                    <div class="h2title">Last uploads</div>
                </div-->
                <div class="curveBox">
                    <div class="h2title"><?php echo $lng['comments']; ?></div>
                    <table>
                        <tr>
                            <td><?php echo $lng['comments']; ?></td>
                            <td>:</td>
                            <td>
                                <?php
                                echo $modx->db->getValue($modx->db->query('SELECT COUNT(id) FROM ' . $modx->db->config['table_prefix'] . 'easy2_comments '));
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td><?php echo $lng['comments_unapproved']; ?></td>
                            <td>:</td>
                            <td>
                                <?php
                                echo $modx->db->getValue($modx->db->query(
                                                'SELECT COUNT(id) FROM ' . $modx->db->config['table_prefix'] . 'easy2_comments '
                                                . 'WHERE `approved`=\'0\''
                                ));
                                ?>
                            </td>
                        </tr>
                    </table>
                </div>
                <!--div class="curveBox">
                    <div class="h2title">Statistics</div>
                    <table>
                        <tr>
                            <td>Top 10 hits</td>
                            <td>:</td>
                            <td>link</td>
                        </tr>
                        <tr>
                            <td>Top 10 rating</td>
                            <td>:</td>
                            <td>link</td>
                        </tr>
                        <tr>
                            <td>10 Most Commented</td>
                            <td>:</td>
                            <td>link</td>
                        </tr>
                    </table>
                </div-->
            </td>
            <td class="tdRight">
                <!--div class="curveBox">
                    <div class="h2title">News</div>
                </div-->
                <div class="curveBox">
                    <div class="h2title"><?php echo $lng['info_system']; ?></div>
                    <table>
                        <tr>
                            <td><?php echo $lng['server']; ?></td>
                            <td>:</td>
                            <td style="font-weight: bold;"><?php echo getenv('SERVER_SOFTWARE'); ?></td>
                        </tr>
                        <tr>
                            <td><?php echo $lng['magic_quote']; ?></td>
                            <td>:</td>
                            <td style="font-weight: bold;">
                                <?php
                                // PHP magic_quotes_gpc()
                                if (get_magic_quotes_gpc ()) {
                                    echo $lng['on'] . $icon_bad . ' ' . $lng['magic_quote_disabling'];
                                } else {
                                    echo $lng['off'] . $icon_ok;
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td><?php echo $lng['multibyte_string']; ?></td>
                            <td>:</td>
                            <td style="font-weight: bold;">
                                <?php
                                if (function_exists('mb_get_info') && is_array(mb_get_info())) {
                                    echo $lng['enabled'] . ' ' . $icon_ok;
                                } else {
                                    echo $lng['disabled'] . ' ' . $icon_bad;
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td>set_time_limit</td>
                            <td>:</td>
                            <td style="font-weight: bold;">
                                <?php
                                if (function_exists('set_time_limit')) {
                                    echo $lng['enabled'] . ' ' . $icon_ok;
                                } else {
                                    echo $lng['disabled'] . ' ' . $icon_bad;
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td>ZipArchive</td>
                            <td>:</td>
                            <td style="font-weight: bold;">
                                <?php
                                if (class_exists('ZipArchive')) {
                                    echo $lng['enabled'] . ' ' . $icon_ok;
                                } else {
                                    echo $lng['disabled'] . ' ' . $icon_bad;
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td><?php echo $lng['upload_max']; ?></td>
                            <td>:</td>
                            <td style="font-weight: bold;"><?php echo ini_get('upload_max_filesize'); ?></td>
                        </tr>
                        <tr>
                            <td>MySQL</td>
                            <td>:</td>
                            <td style="font-weight: bold;"><?php echo $modx->db->getVersion(); ?></td>
                        </tr>
                        <tr>
                            <td><?php echo $lng['database']; ?></td>
                            <td>:</td>
                            <td style="font-weight: bold;"><?php echo str_replace('`', '', $modx->db->config['dbase']); ?></td>
                        </tr>
                        <tr>
                            <td><?php echo $lng['gallery_folder']; ?></td>
                            <td>:</td>
                            <td style="font-weight: bold;"><?php echo $gdir; ?></td>
                        </tr>
                    </table>
                </div>
                <div class="curveBox">
                    <div class="h2title"><?php echo $lng['utilities']; ?></div>
                    <table width="100%">
                        <tr>
                            <td width="50%">
                                <div class="dashboardButton">
                                    <a href="<?php echo $index; ?>&amp;act=clean_cache">
                                        <span><?php echo $lng['clean_cache']; ?></span>
                                    </a>
                                </div>
                            </td>
                            <!--td width="50%">
                                <div class="dashboardButton">
                                    <a href="<?php //echo $index;    ?>&amp;act=reset_all_hit">
                                        <span>Reset all hit counters</span>
                                    </a>
                                </div>
                            </td-->
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>
    <div class="clear">&nbsp;</div>
</div>
<?php echo $this->_plugin('OnE2GDashboardRender'); ?>