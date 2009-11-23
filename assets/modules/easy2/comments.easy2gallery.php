<?php

/******************************************************************************
*
*  EASY 2 GALLERY BY Cx2 <inteldesign@mail.ru>
*  VERSION 1.3
*
******************************************************************************/

error_reporting(0);
if (empty($_GET['id']) || !is_numeric($_GET['id'])) {
    die('<h2 style="color:red">ID Error!</h2>');
}

include './config.easy2gallery.php';
if ($e2g['ecm'] == 0) {
    die('<h2 style="color:red">Comments disabled!</h2>');
}

$cpn = (empty($_GET['cpn']) || !is_numeric($_GET['cpn'])) ? 0 : (int) $_GET['cpn'];


require '../../../manager/includes/config.inc.php';

startCMSSession();

mysql_connect($database_server, $database_user, $database_password)
or die('<h2 style="color:red">MySQL connect error!</h2>');
mysql_select_db(str_replace('`', '', $dbase));
@mysql_query("{$database_connection_method} {$database_connection_charset}"); 


$id = (int) $_GET['id'];

$res = mysql_query('SELECT * FROM '.$table_prefix."system_settings");
while ($row = mysql_fetch_assoc($res)) $settings[$row['setting_name']] = $row['setting_value'];

if (file_exists('./langs/'.$settings['manager_language'].'.comments.php')) {
    include './langs/'.$settings['manager_language'].'.comments.php';
} else {
    include './langs/english.comments.php';
}

$_P['charset']=$settings['modx_charset'];

// ƒŒ¡¿¬À≈Õ»≈  ŒÃÃ≈Õ“¿–»ﬂ

if (!empty($_POST['name']) && !empty($_POST['comment'])) {
    $n = htmlspecialchars(trim($_POST['name']), ENT_QUOTES);
    $c = htmlspecialchars(trim($_POST['comment']), ENT_QUOTES);
    $e = htmlspecialchars(trim($_POST['email']), ENT_QUOTES);

    if(!empty($e2g['captcha']) && ((trim($_POST['vericode'])=='') || (isset($_SESSION['veriword']) && $_SESSION['veriword'] != $_POST['vericode']))) {
        $_P['body'] .= '<h2>'.$lng['captcha_err'].'</h2>';
    }

    elseif (!empty($n) && !empty($c)) {
        if (mysql_query('INSERT INTO '.$table_prefix.'easy2_comments (file_id,author,email,comment,date_added) '
                      . "VALUES($id,'$n','$e','$c', NOW())")) {

            mysql_query('UPDATE '.$table_prefix.'easy2_files SET comments=comments+1 WHERE id='.$id);
            $_P['body'] .= '<h3>'.$lng['comment_added'].'</h3>';

        } else {
            $_P['body'] .= '<h2>'.$lng['comment_add_err'].'</h2>';
        }
    } else {
        $_P['body'] .= '<h2>'.$lng['empty_name_comment'].'</h2>';
    }
}


// ÿ¿¡ÀŒÕ —“–Œ »

if (file_exists($e2g['comments_row_tpl'])) {
    $row_tpl = file_get_contents($e2g['comments_row_tpl']);
} elseif ( !($row_tpl = get_chunk($e2g['comments_row_tpl'])) ) {
    die ('Comments row template not found!');
}


$res = mysql_query('SELECT * FROM '.$table_prefix.'easy2_comments WHERE file_id = '.$id.' ORDER BY id DESC LIMIT '.($cpn*$e2g['ecl']).', '.$e2g['ecl']);
$i = 0;
while($l = mysql_fetch_array($res, MYSQL_ASSOC)) {

    $l['i'] = $i%2;

    if (!empty($l['email'])) $l['name_w_mail'] = '<a href="mailto:'.$l['email'].'">'.$l['author'].'</a>';
    else $l['name_w_mail'] = $l['author'];

    $_P['body'] .= filler($row_tpl, $l);
    $i++;
}

// COUNT PAGES
$res = mysql_query('SELECT COUNT(*) FROM '.$table_prefix.'easy2_comments WHERE file_id = '.$id);
list($cnt) = mysql_fetch_row($res);
if ($cnt > $e2g['ecl']) {
    $_P['pages'] = '<p class="pnums">'.$lng['pages'].':';
    $i = 0;
    while ($i*$e2g['ecl'] < $cnt) {
        if ($i == $cpn) $_P['pages'] .= '<b>'.($i+1).'</b> ';
        else $_P['pages'] .= '<a href="?id='.$id.'&cpn='.$i.'">'.($i+1).'</a> ';
        $i++;
    }
    $_P['pages'] .= '</p>';
}

mysql_close();

// ÿ¿¡ÀŒÕ —“–¿Õ»÷€

if (file_exists($e2g['comments_tpl'])) {
    $tpl = file_get_contents($e2g['comments_tpl']);
} elseif ( !($tpl = get_chunk($e2g['comments_tpl'])) ) {
    die ('Comments template not found!');
}

if(!empty($e2g['captcha'])){
    $seed=rand();
    $_SESSION['veriword'] = md5($seed);
    $siteurl = str_replace("assets/modules/easy2/", "", $site_url);
    $_P['captcha'] = '<tr><td>'.$_P['code'].'</td><td><input type="text" name="vericode" /></td><td colspan="2" class="captcha_cell"><img src="'.$siteurl.'manager/includes/veriword.php?rand='.$seed.'" alt="" /><td></tr>';
}
else {
    $_P['captcha'] ='';
}

echo filler ($tpl, $_P);

function filler ($tpl, $data, $prefix = '[+easy2:', $suffix = '+]') {
     foreach($data as $k => $v) {
         $tpl = str_replace($prefix.(string)$k.$suffix, (string)$v, $tpl);
     }
     return $tpl;
}

function get_chunk ($name) {
    global $table_prefix;

    $res = mysql_query('SELECT * FROM '.$table_prefix."site_htmlsnippets WHERE name='".mysql_escape_string($name)."'");
    if (mysql_num_rows($res) > 0) {
        $row = mysql_fetch_array($res, MYSQL_ASSOC);
        return $row['snippet'];
    } else {
        return false;
    }
}


?>