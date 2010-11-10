<?php

if (IN_MANAGER_MODE != 'true')
    die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

/**
 * PAGE ACTION
 */
$page = empty($_GET['page']) ? '' : $_GET['page'];
switch ($page) {
    case 'create_dir':
        include_once E2G_MODULE_PATH . 'includes/tpl/pages/file.create_dir.inc.php';
        break;

    case 'edit_dir' :
        if (empty($_GET['dir_id']) || !is_numeric($_GET['dir_id'])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['id_err'];
            header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
            exit();
        }
        include_once E2G_MODULE_PATH . 'includes/tpl/pages/file.edit_dir.inc.php';
        break;

    case 'edit_file':
        if (empty($_GET['file_id']) || !is_numeric($_GET['file_id'])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $id['id_err'];

            header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
            exit();
        }
        include_once E2G_MODULE_PATH . 'includes/tpl/pages/file.edit_file.inc.php';
        break;

    case 'comments':
        if (empty($_GET['file_id']) || !is_numeric($_GET['file_id'])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $id['id_err'];

            header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
            exit();
        }
        include_once E2G_MODULE_PATH . 'includes/tpl/pages/file.comments.inc.php';
        break;

    case 'openexplorer':
        if (isset($_POST['newparent']))
            header('Location: ' . html_entity_decode($index . '&amp;pid=' . $_POST['newparent']));
        exit();
        break;

    default:
        if (isset($_GET['tag']))
            include_once E2G_MODULE_PATH . 'includes/tpl/pages/file.tag.inc.php';
        else
            include_once E2G_MODULE_PATH . 'includes/tpl/pages/file.default.inc.php';
        break;
}
