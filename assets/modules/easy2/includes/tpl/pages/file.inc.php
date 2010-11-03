<?php

if (IN_MANAGER_MODE != 'true')
    die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

if ($_GET['page'] == 'create_dir')
    include_once E2G_MODULE_PATH . 'includes/tpl/pages/file.create_dir.inc.php';
elseif ($_GET['page'] == 'edit_dir')
    include_once E2G_MODULE_PATH . 'includes/tpl/pages/file.edit_dir.inc.php';
elseif ($_GET['page'] == 'edit_file')
    include_once E2G_MODULE_PATH . 'includes/tpl/pages/file.edit_file.inc.php';
elseif ($_GET['page'] == 'comments')
    include_once E2G_MODULE_PATH . 'includes/tpl/pages/file.comments.inc.php';
elseif (isset($_GET['tag']))
    include_once E2G_MODULE_PATH . 'includes/tpl/pages/file.tag.inc.php';
// default page
else
    include_once E2G_MODULE_PATH . 'includes/tpl/pages/file.default.inc.php';