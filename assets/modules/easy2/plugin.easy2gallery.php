<?php
global $e;
$e = &$modx->Event;

switch ($e->name) {
    case 'OnWebPageInit':
        $_SESSION['e2g_instances'] = 1;
        break;
    case 'OnWebPageComplete':
        $_SESSION['e2g_instances'] = 0;
        break;
    default :
        break;
}