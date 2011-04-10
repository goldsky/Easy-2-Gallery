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
$docParserClassFile = realpath('../../../../../manager/includes/document.parser.class.inc.php');
if (empty($docParserClassFile) || !file_exists($docParserClassFile)) {
    die(__FILE__ . ': Missing doc parser class file.');
}
include ($docParserClassFile);
$modx = new DocumentParser;
$modx->getSettings();

// Easy 2 Gallery module path
define('E2G_MODULE_PATH', MODX_BASE_PATH . 'assets/modules/easy2/');
// Easy 2 Gallery module URL
define('E2G_MODULE_URL', MODX_SITE_URL . '../../');

// initiate e2g's public module
$modParamFile = realpath('../configs/params.module.easy2gallery.php');
if (empty ($modParamFile) || !file_exists($modParamFile)) {
    die(__FILE__ . ': Missing module\'s params file.');
}
include ($modParamFile);
$pubClassFile = realpath('../models/e2g.public.class.php');
if (empty ($pubClassFile) || !file_exists($pubClassFile)) {
    die(__FILE__ . ': Missing public class file.');
}
include ($pubClassFile); //extending
$modClassFile = realpath('../models/e2g.module.class.php');
if (empty ($modClassFile) || !file_exists($modClassFile)) {
    die(__FILE__ . ': Missing module class file.');
}
include ($modClassFile);

// LANGUAGE
$lng = E2gPub::languageSwitch();
if (!is_array($lng)) {
    die($lng); // FALSE returned.
}

$e2gMod = new E2gMod($modx, $e2gModCfg, $e2g, $lng);

$getRequests = $e2gMod->sanitizedGets($_GET);
if (empty($getRequests)) {
    die('Request is empty');
}

echo $e2gMod->countFiles($getRequests['path']);
exit;