<?php

// harden it
if (!@require_once('../../../../../manager/includes/protect.inc.php'))
    die('Go away!');

// initialize the variables prior to grabbing the config file
$database_type = "";
$database_server = "";
$database_user = "";
$database_password = "";
$dbase = "";
$table_prefix = "";
$base_url = "";
$base_path = "";

// MODx config
if (!@require_once '../../../../../manager/includes/config.inc.php')
    die('Unable to include the MODx\'s config file');

mysql_connect($database_server, $database_user, $database_password) or die('MySQL connect error');
mysql_select_db(str_replace('`', '', $dbase));
@mysql_query("{$database_connection_method} {$database_connection_charset}");

// e2g's configs
$q = mysql_query('SELECT * FROM ' . $table_prefix . 'easy2_configs');
if (!$q)
    die(__FILE__ . ': MySQL query error for configs');
else {
    while ($row = mysql_fetch_array($q)) {
        $e2g[$row['cfg_key']] = $row['cfg_val'];
    }
}

// initiate a new document parser
include ('../../../../../manager/includes/document.parser.class.inc.php');
$modx = new DocumentParser;
$modx->getSettings();

// Easy 2 Gallery module path
define('E2G_MODULE_PATH', MODX_BASE_PATH . 'assets/modules/easy2/');
// Easy 2 Gallery module URL
define('E2G_MODULE_URL', MODX_SITE_URL . '../../');

require_once E2G_MODULE_PATH . 'includes/utf8/utf8.php';

// initiate e2g's public module
include ('../configs/params.module.easy2gallery.php');
include ('../models/e2g.public.class.php'); //extending
include ('../models/e2g.module.class.php');

// LANGUAGE
if (file_exists(realpath('../langs/' . $modx->config['manager_language'] . '.inc.php'))) {
    include '../langs/' . $modx->config['manager_language'] . '.inc.php';

    // if there is a blank language parameter, english will fill it as the default.
    foreach ($e2g_lang[$modx->config['manager_language']] as $olk => $olv) {
        $oldLangKey[$olk] = $olk; // other languages
        $oldLangVal[$olk] = $olv;
    }

    include '../langs/english.inc.php';
    foreach ($e2g_lang['english'] as $enk => $env) {
        if (!isset($oldLangKey[$enk])) {
            $e2g_lang[$modx->config['manager_language']][$enk] = $env;
        }
    }

    $lng = $e2g_lang[$modx->config['manager_language']];
} else {
    include '../langs/english.inc.php';
    $lng = $e2g_lang['english'];
}

$e2gMod = new E2gMod($modx, $e2gModCfg, $e2g, $lng);

$getRequests = $e2gMod->sanitizedGets($_GET);
if (empty($getRequests)) {
    die('Request is empty');
}

$index = $e2gModCfg['index'];
$index = str_replace('assets/modules/easy2/includes/controllers/', '', $index);

$rootDir = '../../../../../' . $e2g['dir'];
$pidPath = $e2gMod->getPath($getRequests['pid']);
$gdir = $e2g['dir'] . $getRequests['path'];

if ($e2gMod->synchro('../../../../../' . $getRequests['path'], $getRequests['pid'])) {
    echo '<div class="success" style="padding-left: 10px;">' . __LINE__ . ' : ' . $lng['synchro_suc'] . '</div>';
} else {
    echo '<div class="warning" style="padding-left: 10px;">' . __LINE__ . ' : ' . $lng['synchro_err'] . '</div>';
}

exit();