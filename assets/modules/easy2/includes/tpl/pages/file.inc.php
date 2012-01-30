<?php

if (IN_MANAGER_MODE != 'true')
    die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

/**
 * PAGE ACTION
 */
$page = empty($this->sanitizedGets['page']) ? '' : $this->sanitizedGets['page'];
switch ($page) {
    case 'create_dir':
        include_once E2G_MODULE_PATH . 'includes/tpl/pages/file.create_dir.inc.php';
        break;

    case 'edit_dir' :
        if (empty($this->sanitizedGets['dir_id']) || !is_numeric($this->sanitizedGets['dir_id'])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['id_err'];
            header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
            exit();
        }
        include_once E2G_MODULE_PATH . 'includes/tpl/pages/file.edit_dir.inc.php';
        break;

    case 'edit_file':
        if (empty($this->sanitizedGets['file_id']) || !is_numeric($this->sanitizedGets['file_id'])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $id['id_err'];

            header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
            exit();
        }
        include_once E2G_MODULE_PATH . 'includes/tpl/pages/file.edit_file.inc.php';
        break;

    case 'comments':
        if (empty($this->sanitizedGets['file_id']) || !is_numeric($this->sanitizedGets['file_id'])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $id['id_err'];

            header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
            exit();
        }
        include_once E2G_MODULE_PATH . 'includes/tpl/pages/file.comments.inc.php';
        break;

    case 'openexplorer':
        if (isset($_POST['newparent']))
            header('Location: ' . html_entity_decode($this->e2gModCfg['index'] . '&amp;pid=' . $_POST['newparent']));
        exit();
        break;

    default:
        if (isset($this->sanitizedGets['tag']))
            include_once E2G_MODULE_PATH . 'includes/tpl/pages/file.tag.inc.php';
        else
            include_once E2G_MODULE_PATH . 'includes/tpl/pages/file.default.inc.php';
        break;
}
