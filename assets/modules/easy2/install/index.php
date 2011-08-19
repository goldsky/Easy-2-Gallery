<?php
// SYSTEM VARS
$debug = 0;                             // MODx's debug variable
$_t = $modx->config['manager_theme'];   // MODx's manager theme
$_a = (int) $_GET['a'];                 // MODx's action ID
$_i = (int) $_GET['id'];                // MODx's module ID
$index = 'index.php?a=' . $_a . '&id=' . $_i . (!empty($e2g['mod_id']) ? '&amp;e2g_id=' . $e2g['mod_id'] : '');

$installClassFile = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'install.class.php';
require_once $installClassFile;

$install = new install($modx, $e2g);
$lngi = $install->loadLanguage();
$_SESSION['installE2g'] = TRUE;
#################################################################################
#################################################################################
#################################################################################

if (isset($_GET['p']) && $_GET['p'] == 'del_inst_dir') {
//    deleteAll(MODX_BASE_PATH . 'assets/modules/easy2/install/');
    $install->deleteAll(dirname(__FILE__));
    header('Location: ' . $index);
    exit();
} elseif (!empty($_POST)) {
    $install->initInstall($_POST, $index);
} else {
    // SNIPPET
    if (empty($e2g['snippet_id']) || $e2g['snippet_id'] == '') {
        $select = mysql_query('SELECT id FROM ' . $modx->db->config['table_prefix'] . 'site_snippets WHERE name =\'easy2\'');
        if (mysql_num_rows($select) > 0)
            $snippetId = mysql_result($select, 0, 0);
        mysql_free_result($select);
    } else {
        $snippetId = $e2g['snippet_id'];
    }

    // PLUGIN
    if (empty($e2g['plugin_id']) || $e2g['plugin_id'] == '') {
        $select = mysql_query('SELECT id FROM ' . $modx->db->config['table_prefix'] . 'site_plugins WHERE name=\'easy2\'');
        if (mysql_num_rows($select) > 0)
            $pluginId = mysql_result($select, 0, 0);
        mysql_free_result($select);
    } else {
        $pluginId = $e2g['plugin_id'];
    }
    ?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html>
        <head>
            <title>Easy 2 Gallery <?php echo E2G_VERSION; ?> installation</title>
            <link rel="stylesheet" type="text/css"
                  href="media/style/<?php echo $_t; ?>/style.css" />
            <script type="text/javascript" src="media/script/tabpane.js"></script>
        </head>
        <body>
            <p>&nbsp;</p>
            <div class="sectionHeader">Easy 2 Gallery <?php echo E2G_VERSION; ?> Installation</div>
            <div class="sectionBody">
                <div class="tab-pane" id="easy2Pane"><script type="text/javascript">
                    tpResources = new WebFXTabPane(document.getElementById("easy2Pane"));
                    </script>
                    <div class="tab-page" id="install">
                        <h2 class="tab"><?php echo $lngi['install']; ?></h2>
                        <script type="text/javascript">
                            tpResources.addTabPage(document.getElementById("install"));
                        </script>
                        <?php
                        if (count($_SESSION['easy2err']) > 0 || count($_SESSION['easy2suc']) > 0) {
                            $suc = $err = '';
                            if (count($_SESSION['easy2err']) > 0) {
                                $err = '<p class="warning">' . implode('<br />', $_SESSION['easy2err']) . '</p>';
                                $_SESSION['easy2err'] = array();
                                $err .= '<br /><br /><a href="#" onclick="document.location.href=\'' . $index . '\'"><b>' . $lngi['back'] . '</b></a>';
                            }
                            if (count($_SESSION['easy2suc']) > 0) {
                                $suc = '<p class="success">' . implode('<br />', $_SESSION['easy2suc']) . '</p>';
                                $_SESSION['easy2suc'] = array();
                            }
                            echo $suc . $err;
                        } else {
                            ?> <br />
                            <form method="post" action="">
                                <table cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td><b><?php echo $lngi['path']; ?> :</b>&nbsp;</td>
                                        <td><input name="path" type="text" style="width: 100%" size="50" value="<?php echo $e2g['dir']; ?>" />
                                            <input type="hidden" name="mod_id"
                                                   value="<?php echo (!empty($e2g['mod_id']) ? $e2g['mod_id'] : $_GET['id']); ?>" />
                                            <input type="hidden" name="plugin_id" value="<?php echo $pluginId; ?>" />
                                            <input type="hidden" name="snippet_id" value="<?php echo $snippetId; ?>" /></td>
                                    </tr>
                                </table>
                                <div><?php echo htmlspecialchars_decode($lngi['comment1'], ENT_QUOTES); ?></div>
                                <div style="border: 1px solid #ccc; padding: 10px; background-color: #EFEFEF;">
                                    <img src="<?php echo MODX_BASE_URL; ?>manager/media/style/MODxCarbon/images/icons/error.png" alt="" style="float: left; margin-right: 10px;" />
                                    <?php echo htmlspecialchars_decode($lngi['comment'], ENT_QUOTES); ?>
                                </div>
                                <div style="color: green; font-weight: bold; font-size: 1.5em;"><?php echo htmlspecialchars_decode($lngi['system_check']); ?> :</div>

                                <?php
                                $iconOk = '<img src="' . MODX_BASE_URL . 'assets/modules/easy2/includes/tpl/icons/action_check.png" alt="" /> ';
                                $iconBad = '<img src="' . MODX_BASE_URL . 'assets/modules/easy2/includes/tpl/icons/action_delete.png" alt="" /> ';
                                $disabled = '';
                                echo '<ul>';

                                echo '<li>';

                                $getChr = mysql_query("SHOW CHARACTER SETS");
                                // get collation
                                $getCol = mysql_query("SHOW COLLATION");
                                $showVars = $modx->db->makeArray($modx->db->query("SHOW VARIABLES"));
                                foreach ($showVars as $v) {
                                    $mysqlVars[$v['Variable_name']] = $v['Value'];
                                }
                                $databaseCollation = $mysqlVars['collation_database'];

                                if (@mysql_num_rows($getCol) > 0) {
                                    $output = "\n" . 'Database collation <select id="database_collation" name="database_collation">' . "\n";
                                    while ($row = mysql_fetch_assoc($getCol)) {
                                        $cola[$row['Collation']] = $row;
                                    }
                                    asort($cola);
                                    foreach ($cola as $k => $v) {
                                        $collation = htmlentities($k);
                                        $selected = ( $collation == $databaseCollation ? ' selected' : '' );
                                        $output .= '<option value="' . $collation . '"' . $selected . '>' . $collation . '</option>' . "\n";
                                    }
                                    $output .= '</select>' . "\n";
                                }
                                echo $output;
                                echo '</li>';

                                // PHP version
                                if (version_compare(PHP_VERSION, '5.2.0', '<')) {
                                    $disabled = 'disabled="disabled"';
                                    echo '<li>';
                                    echo $iconBad . 'PHP version ' . PHP_VERSION . ' (Min: 5.2.0)';
                                    echo '</li>';
                                } else {
                                    echo '<li>';
                                    echo $iconOk . 'PHP version ' . PHP_VERSION;
                                    echo '</li>';
                                }

                                if (get_magic_quotes_gpc()) {
                                    echo '<li>';
                                    echo $iconBad . 'PHP magic_quotes_gpc()=ON. Try to disable it from .htaccess or php.ini';
                                    echo '</li>';
                                } else {
                                    echo '<li>';
                                    echo $iconOk . 'PHP magic_quotes_gpc()=OFF';
                                    echo '</li>';
                                }

                                // PHP Multibyte String
                                if (function_exists('mb_get_info') && is_array(mb_get_info())) {
                                    echo '<li>';
                                    echo $iconOk . 'PHP Multibyte String enabled';
                                    echo '</li>';
                                } else {
                                    $disabled = 'disabled="disabled"';
                                    echo '<li>';
                                    echo $iconBad . 'PHP Multibyte String disabled';
                                    echo '</li>';
                                }

                                // PHP Zipclass
                                if (class_exists('ZipArchive')) {
                                    echo '<li>';
                                    echo $iconOk . 'PHP ZipArchive';
                                    echo '</li>';
                                } else {
                                    echo '<li>';
                                    echo $iconBad . 'PHP ZipArchive';
                                    echo '</li>';
                                }

                                // Easy 2 javascript library folders
                                if (is_dir('../assets/libs')) {
                                    echo '<li>';
                                    echo $iconOk . 'assets/libs';
                                    echo '</li>';
                                } else {
                                    $disabled = 'disabled="disabled"';
                                    echo '<li>';
                                    echo $iconBad . 'assets/libs';
                                    echo '</li>';
                                }

                                $style = '';
                                if ($disabled == 'disabled="disabled"')
                                    $style = 'style="color:gray;"';

                                echo '</ul>';
                                ?>
                                <input type="submit" <?php echo $style; ?> value="<?php echo htmlspecialchars_decode($lngi['ok']); ?>" <?php echo $disabled; ?> />
                            </form>
                            <?php
                        }
                        ?></div>
                </div>
            </div>
        </body>
    </html>
    <?php
}
return;