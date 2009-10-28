<?php


if (file_exists('../assets/modules/easy2/install/langs/'.$modx->config['manager_language'].'.inc.php')) {
    include '../assets/modules/easy2/install/langs/'.$modx->config['manager_language'].'.inc.php';
} else {
    include '../assets/modules/easy2/install/langs/russian.inc.php';
}


if (isset($_GET['p']) && $_GET['p'] == 'del_inst_dir') {

    delete_all ('../assets/modules/easy2/install/');
    header('Location: '.$index);
    exit();

} elseif (!empty($_POST)) {

    $ms = array('suc' => array(), 'err' => array());


    // DIRS
    $_POST['path'] = str_replace('../', '', $_POST['path']);
    if (empty($_POST['path'])) {
        $_SESSION['easy2err'][] = $lngi['empty_dir'];
        chref($index);
    }

    // CHECK/CREATE DIRS
    $_POST['path'] = preg_replace('/^\/?(.+)$/', '\\1', $_POST['path']);
    $dirs = explode('/', substr($_POST['path'], 0, -1));
    $npath = '..';
    foreach ($dirs as $dir) {
        $npath .= '/'.$dir;
        if (is_dir($npath) || empty($dir)) continue;

        if(!mkdir($npath, 0777)) {
            $_SESSION['easy2err'][] = $lngi['create_dir_err'].' "'.$npath."'";
            chref($index);
        }
    }

    $_SESSION['easy2dir'] = substr($npath, 3).'/';
    $_SESSION['easy2suc'][] = $lngi['dir_created'];




    // CHECK/CREATE TABLES

    // mysql_list_fields()

    // GET All Tables

    $res = mysql_list_tables(str_replace('`', '', $GLOBALS['dbase']));
    $tab = array();
    while ($row = mysql_fetch_row($res)) {
        $tab[$row[0]] = $row[0];
    }

    // easy2_dirs CHECK
    if (isset($tab['easy2_dirs'])) {
        if (!mysql_query('RENAME TABLE easy2_dirs TO '.$GLOBALS['table_prefix'].'easy2_dirs')) {
            $_SESSION['easy2err'][] = $lngi['table'].' easy2_dirs '.$lngi['rename_err'].'<br />'.mysql_error();
            chref($index);
        }
    }


    // easy2_dirs CREATE

    if (mysql_query('CREATE TABLE IF NOT EXISTS '.$GLOBALS['table_prefix'].'easy2_dirs (
parent_id int(10) unsigned NOT NULL default \'0\',
cat_id int(10) unsigned NOT NULL auto_increment,
cat_left int(10) unsigned NOT NULL default \'0\',
cat_right int(10) unsigned NOT NULL default \'0\',
cat_level int(10) unsigned NOT NULL default \'0\',
cat_name varchar(255) NOT NULL default \'\',
cat_visible tinyint(4) NOT NULL default \'1\',
PRIMARY KEY  (cat_id),
KEY cat_left (cat_left)
) TYPE=MyISAM')) {
        $_SESSION['easy2suc'][] = $lngi['table'].' '.$GLOBALS['table_prefix'].'easy2_dirs '.$lngi['created'];
    } else {
        $_SESSION['easy2err'][] = $lngi['table'].' '.$GLOBALS['table_prefix'].'easy2_dirs '.$lngi['create_err'].'<br />'.mysql_error();
        chref($index);
    }

    $res = mysql_query('SELECT cat_right FROM '.$GLOBALS['table_prefix'].'easy2_dirs WHERE cat_id=1');
    if (mysql_num_rows($res) == 0) {

        if (mysql_query('INSERT INTO '.$GLOBALS['table_prefix']."easy2_dirs VALUES (0,1,1,2,0,'Easy 2',1)")) {
            $_SESSION['easy2suc'][] = $lngi['data'].' '.$GLOBALS['table_prefix'].'easy2_dirs '.$lngi['added'];
        } else {
            $_SESSION['easy2err'][] = $lngi['data'].' '.$GLOBALS['table_prefix'].'easy2_dirs '.$lngi['add_err'].'<br />'.mysql_error();
            chref($index);
        }

    }

    //list($r) = mysql_fetch_row($res);



    // easy2_comments CHECK
    if (isset($tab['easy2_comments'])) {
        if (!mysql_query('RENAME TABLE easy2_comments TO '.$GLOBALS['table_prefix'].'easy2_comments')) {
            $_SESSION['easy2err'][] = $lngi['table'].' '.$GLOBALS['table_prefix'].'easy2_comments '.$lngi['rename_err'].'<br />'.mysql_error();
            chref($index);
        }
    }


    // easy2_comments CREATE

    if (mysql_query('CREATE TABLE IF NOT EXISTS '.$GLOBALS['table_prefix'].'easy2_comments (
id int(10) unsigned NOT NULL auto_increment,
file_id int(10) unsigned NOT NULL default \'0\',
author varchar(64) NOT NULL default \'\',
email varchar(64) NOT NULL default \'\',
comment text NOT NULL,
date_added datetime NOT NULL default \'0000-00-00 00:00:00\',
last_modified datetime default NULL,
status tinyint(3) unsigned NOT NULL default \'1\',
PRIMARY KEY  (id),
KEY file_id (file_id)
) TYPE=MyISAM')) {
        $_SESSION['easy2suc'][] = $lngi['table'].' '.$GLOBALS['table_prefix'].'easy2_comments '.$lngi['created'];
    } else {
        $_SESSION['easy2err'][] = $lngi['table'].' '.$GLOBALS['table_prefix'].'easy2_comments '.$lngi['create_err'].'<br />'.mysql_error();
        chref($index);
    }


    // easy2_comments CHECK
    if (isset($tab['easy2_files'])) {
        if (!mysql_query('RENAME TABLE easy2_files TO '.$GLOBALS['table_prefix'].'easy2_files')) {
            $_SESSION['easy2err'][] = $lngi['table'].' '.$GLOBALS['table_prefix'].'easy2_files '.$lngi['rename_err'].'<br />'.mysql_error();
            chref($index);
        }
    }


    // easy2_comments CREATE

    if (mysql_query('CREATE TABLE IF NOT EXISTS '.$GLOBALS['table_prefix'].'easy2_files (
id int(10) unsigned NOT NULL auto_increment,
dir_id int(10) unsigned NOT NULL default \'0\',
filename varchar(255) NOT NULL default \'\',
size varchar(32) NOT NULL default \'\',
name varchar(255) NOT NULL default \'\',
description text NOT NULL,
date_added datetime NOT NULL default \'0000-00-00 00:00:00\',
last_modified datetime default NULL,
comments int(10) unsigned NOT NULL default \'0\',
status tinyint(3) unsigned NOT NULL default \'1\',
PRIMARY KEY  (id)
) TYPE=MyISAM')) {
        $_SESSION['easy2suc'][] = $lngi['table'].' '.$GLOBALS['table_prefix'].'easy2_files '.$lngi['created'];
    } else {
        $_SESSION['easy2err'][] = $lngi['table'].' '.$GLOBALS['table_prefix'].'easy2_files '.$lngi['create_err'].'<br />'.mysql_error();
        chref($index);
    }


    // MODULE

    $mod = file_get_contents('../assets/modules/easy2/install/module.easy2gallery.php');
    $res = mysql_query('UPDATE '.$GLOBALS['table_prefix'].'site_modules SET modulecode = \''.mysql_escape_string($mod).'\' WHERE name =\'easy2\'');
    if ($res) {
        $_SESSION['easy2suc'][] = $lngi['mod_updated'];
    } else {
        $_SESSION['easy2err'][] = $lngi['mod_update_err'].'<br />'.mysql_error();
        chref($index);
    }

    // SNIPPET

    $snippet = file_get_contents('../assets/modules/easy2/install/snippet.easy2gallery.php');


    $res = mysql_query('SELECT id FROM '.$GLOBALS['table_prefix'].'site_snippets WHERE name =\'easy2\'');
    if (mysql_num_rows($res) > 0) {
        $sql = 'UPDATE '.$GLOBALS['table_prefix'].'site_snippets SET snippet=\''.mysql_escape_string($snippet).'\' WHERE name =\'easy2\' LIMIT 1';
        if (mysql_query($sql)) {
            $_SESSION['easy2suc'][] = $lngi['snippet_updated'];
        } else {
            $_SESSION['easy2err'][] = $lngi['snippet_update_err'];
            chref($index);
        }
    } else {
        $sql = "INSERT INTO ".$GLOBALS['table_prefix']."site_snippets "
."(name, description, snippet, moduleguid, locked, properties, category) "
."VALUES('easy2', 'Easy 2 Gallery', '".mysql_escape_string($snippet)."', '', '1','', '0');";

        if (mysql_query($sql)) {
            $_SESSION['easy2suc'][] = $lngi['snippet_added'];
        } else {
            $_SESSION['easy2err'][] = $lngi['snippet_add_err'];
            chref($index);
        }
    }


    $_SESSION['easy2suc']['success'] = '<br /><br /><br />'.$lngi['success']
    .'<br /><br /><input type="button" value="'.$lngi['del_inst_dir'].'" onclick="document.location.href=\''.$index.'&p=del_inst_dir\'">';


    // SAVE DIR

    if (empty($e2g)) {
        require '../assets/modules/easy2/config.easy2gallery.php';
    }

    $e2g['dir'] = $_SESSION['easy2dir'];
    $c = "<?php\r\n\$e2g = array (\r\n";
    foreach($e2g as $k => $v) {
        $c .= "'$k' => ".(is_numeric($v)?$v:"'$v'").",\r\n";
    }
    $c .= ");\r\n?>";

    $f = fopen('../assets/modules/easy2/config.easy2gallery.php', 'w');
    fwrite($f, $c);
    fclose($f);

    unset($_SESSION['easy2dir']);


    chref($index);

} else {
    $content = '<br />
<form method="post">
<table cellspacing="0" cellpadding="0">
<tr>
<td width="50"><b>'.$lngi['path'].':</b></td>
<td><input name="path" type="text" style="width:100%" value="assets/gallery/"></td>
</tr>
</table>
'.$lngi['comment1'].'
<p><br />'.$lngi['comment'].'</p><br />
<input type="submit" value="'.$lngi['ok'].'">
</form>';

}


$suc = $err = '';
if (count($_SESSION['easy2err']) > 0) {
    $err = '<p class="warning">'.implode('<br />', $_SESSION['easy2err']).'</p>';
    $_SESSION['easy2err'] = array();
    $err .= '<br /><br /><a href="#" onclick="document.location.href=\''.$index.'\'"><b>'.$lngi['back'].'</b></a>';
}
if (count($_SESSION['easy2suc']) > 0) {
    $suc = '<p class="success">'.implode('<br />', $_SESSION['easy2suc']).'</p>';
    $_SESSION['easy2suc'] = array();
}

if (!empty($suc) || !empty($err)) $content = $suc.$err;



$out = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Easy 2 install</title>
<link rel="stylesheet" type="text/css" href="media/style/' . $_t . '/style.css" />
<script type="text/javascript" src="media/script/tabpane.js"></script>
</head>
<body>
<div class="sectionHeader">Easy 2 Gallery</div>

<div class="sectionBody">
 <div class="tab-pane" id="easy2Pane">
<script type="text/javascript">
 tpResources = new WebFXTabPane(document.getElementById("easy2Pane"));
</script>

  <div class="tab-page" id="install">
   <h2 class="tab">'.$lng['install'].'</h2>
<script type="text/javascript">
 tpResources.addTabPage(document.getElementById("install"));
</script>
   '.$content.'</div>

 </div>
</div>

</body>
</html>';

echo $out;


function chref ($href) {
    $_SESSION['easy2ms'] = $ms;
    header('Location: '.$href);
    exit();
}
?>