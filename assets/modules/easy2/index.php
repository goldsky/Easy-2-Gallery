<?php

/**
 * EASY 2 GALLERY
 * Gallery Module for MODx Evolution
 * @author Cx2 <inteldesign@mail.ru>
 * @author Temus <temus3@gmail.com>
 * @author goldsky <goldsky@modx-id.com>
 */
if (IN_MANAGER_MODE != 'true')
    die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

// Easy 2 Gallery version
if (!defined('E2G_VERSION') || 'E2G_VERSION' !== '1.4.2') {
    define('E2G_VERSION', '1.4.2');
}

// Easy 2 Gallery module path
if (!defined('E2G_MODULE_PATH')) {
    define('E2G_MODULE_PATH', MODX_BASE_PATH . 'assets/modules/easy2/');
}
// Easy 2 Gallery module URL
if (!defined('E2G_MODULE_URL')) {
    define('E2G_MODULE_URL', MODX_SITE_URL . 'assets/modules/easy2/');
}

require_once E2G_MODULE_PATH . 'includes/utf8/utf8.php';

// LANGUAGE
if (file_exists(realpath(E2G_MODULE_PATH . 'includes/langs/' . $modx->config['manager_language'] . '.inc.php'))) {
    include E2G_MODULE_PATH . 'includes/langs/' . $modx->config['manager_language'] . '.inc.php';

    // if there is a blank language parameter, english will fill it as the default.
    foreach ($e2g_lang[$modx->config['manager_language']] as $olk => $olv) {
        $oldLangKey[$olk] = $olk; // other languages
        $oldLangVal[$olk] = $olv;
    }

    include E2G_MODULE_PATH . 'includes/langs/english.inc.php';
    foreach ($e2g_lang['english'] as $enk => $env) {
        if (!isset($oldLangKey[$enk])) {
            $e2g_lang[$modx->config['manager_language']][$enk] = $env;
        }
    }

    $lng = $e2g_lang[$modx->config['manager_language']];
} else {
    include E2G_MODULE_PATH . 'includes/langs/english.inc.php';
    $lng = $e2g_lang['english'];
}

// ALERTS / ERRORS
if (!isset($_SESSION['easy2err']))
    $_SESSION['easy2err'] = array();
if (!isset($_SESSION['easy2suc']))
    $_SESSION['easy2suc'] = array();

$_SESSION['saveE2gSettings'] = false;
$countConfigs = array();
/**
 * Create a smooth conversion between file based config to database base
 */
// CONFIGURATIONS from the previous version installation
if (file_exists(realpath(E2G_MODULE_PATH . 'includes/configs/config.easy2gallery.php'))) {
    require_once E2G_MODULE_PATH . 'includes/configs/config.easy2gallery.php';
    foreach ($e2g as $ck => $cv) {
        $configsKey[$ck] = $ck;
        $configsVal[$ck] = $cv;
    }
    $countConfigs['oldConfigFile'] = count($e2g);
}

// CONFIGURATIONS
if (!isset($e2g)) {
    $upgradeCheck = 'SHOW TABLES LIKE \'' . $modx->db->config['table_prefix'] . 'easy2_configs\' ';
    $upgradeCheckValue = $modx->db->getValue($modx->db->query($upgradeCheck));
    if (!empty($upgradeCheckValue)) {
        $configsQuery = $modx->db->select('*', $modx->db->config['table_prefix'] . 'easy2_configs');
        if ($configsQuery) {
            while ($row = mysql_fetch_array($configsQuery)) {
                $configsKey[$row['cfg_key']] = $row['cfg_key'];
                $e2g[$row['cfg_key']] = $row['cfg_val'];
            }
        }
    }
    $countConfigs['oldConfigDb'] = count($e2g);
}

// the default config will replace any blank value of config's.
if (file_exists(realpath(E2G_MODULE_PATH . 'includes/configs/default.config.easy2gallery.php'))) {
    require_once E2G_MODULE_PATH . 'includes/configs/default.config.easy2gallery.php';
    foreach ($e2gDefault as $dk => $dv) {
        if (!isset($configsKey[$dk])) {
            $e2g[$dk] = $dv;
        }
    }
    $countConfigs['defaultConfigs'] = count($e2gDefault);
    $e2gDefault = array();
    unset($e2gDefault);
}

if (isset($countConfigs['oldConfigFile']) && $countConfigs['oldConfigFile'] < $countConfigs['defaultConfigs']
        || isset($countConfigs['oldConfigDb']) && $countConfigs['oldConfigDb'] < $countConfigs['defaultConfigs']
        || !isset($countConfigs['oldConfigFile']) && !isset($countConfigs['oldConfigDb'])
) {
    $_SESSION['saveE2gSettings'] = true;
}

// CHECKING THE root and _thumbnails FOLDERs
if (!is_dir(MODX_BASE_PATH . $e2g['dir'])) {
    // INSTALL
    if (is_dir(E2G_MODULE_PATH . 'install')) {
        require_once E2G_MODULE_PATH . 'install/index.php';
        exit();
    } else {
        $_SESSION['easy2err'][] = '<b style="color:red">' . $lng['dir'] . ' &quot;' . $e2g['dir'] . '&quot; ' . $lng['empty'] . '</b>';
//    exit;
    }
} elseif (!is_dir(MODX_BASE_PATH . $e2g['dir'] . '_thumbnails')) {
    if (mkdir(MODX_BASE_PATH . $e2g['dir'] . '_thumbnails')) {
        @chmod(MODX_BASE_PATH . $e2g['dir'] . '_thumbnails', 0755);
    } else {
        $_SESSION['easy2err'][] = '<b style="color:red">' . $lng['_thumb_err'] . '</b>';
        exit;
    }
}

// Easy 2 Gallery module path
if (!defined('E2G_GALLERY_PATH')) {
    define('E2G_GALLERY_PATH', MODX_BASE_PATH . $e2g['dir']);
}
// Easy 2 Gallery module URL
if (!defined('E2G_GALLERY_URL')) {
    define('E2G_GALLERY_URL', MODX_SITE_URL . $e2g['dir']);
}

include 'includes/configs/params.module.easy2gallery.php';

// INSTALL
if (is_dir(E2G_MODULE_PATH . 'install')) {
    return require_once E2G_MODULE_PATH . 'install/index.php';
}

$pageConfigFile = realpath(E2G_MODULE_PATH . 'includes/configs/config.pages.easy2gallery.php');
if (empty($pageConfigFile) || !file_exists($pageConfigFile)) {
    return __LINE__ . ' : ' . $lng['config_file_err_missing'];
    return FALSE;
} else {
    require $pageConfigFile;
    $e2gPages = $e2gModCfg['e2gPages'];
}

/**
 * EXECUTE MODULE
 */
$e2gPubClassFile = E2G_MODULE_PATH . 'includes/models/e2g.public.class.php';
if (!class_exists('E2gPub') && file_exists(realpath($e2gPubClassFile))) {
    include $e2gPubClassFile;
} else {
    $output = 'Missing $e2gPubClassFile';
}

$e2gModClassFile = E2G_MODULE_PATH . 'includes/models/e2g.module.class.php';
if (!class_exists('E2gMod') && file_exists(realpath($e2gModClassFile))) {
    include $e2gModClassFile;
} else {
    $output = 'Missing $e2gModClassFile';
}

if (class_exists('E2gPub') && class_exists('E2gMod')) {
    $e2gModule = new E2gMod($modx, $e2gModCfg, $e2g, $lng);

    if ($_SESSION['saveE2gSettings']) {
        $e2gModule->saveE2gSettings($e2g);
        $_SESSION['saveE2gSettings'] = false;
    }

    $output = $e2gModule->explore();
}

return $output;