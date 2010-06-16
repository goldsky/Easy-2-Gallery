<?php

/**
 * EASY 2 GALLERY
 * Gallery Snippet Comments for Easy 2 Gallery Module for MODx Evolution
 * @author Cx2 <inteldesign@mail.ru>
 * @author Temus
 * @author goldsky <goldsky@modx-id.com>
 * @version 1.4.0
 */

error_reporting(0);
if (empty($_GET['id']) || !is_numeric($_GET['id'])) {
    die('<h2 style="color:red">ID Error!</h2>');
}

include 'includes/configs/config.easy2gallery.php';
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

if (file_exists('includes/langs/'.$settings['manager_language'].'.comments.php')) {
    include 'includes/langs/'.$settings['manager_language'].'.comments.php';
    $lng=$e2g_lang[$settings['manager_language']];
} else {
    include 'includes/langs/english.comments.php';
    $lng=$e2g_lang['english'];
}

$_P['charset']=$settings['modx_charset'];

// output from language file
$_P['title']=$lng['title'];
$_P['comment_add']=$lng['comment_add'];
$_P['name']=$lng['name'];
$_P['email']=$lng['email'];
$_P['usercomment']=$lng['usercomment'];
$_P['send_btn']=$lng['send_btn'];
$_P['comment_body']='';
$_P['comment_pages']='';
$_P['code']=$lng['code'];

// INSERT THE COMMENT INTO DATABASE

if (!empty($_POST['name']) && !empty($_POST['comment'])) {
    $n = htmlspecialchars(trim($_POST['name']), ENT_QUOTES);
    $c = htmlspecialchars(trim($_POST['comment']), ENT_QUOTES);
    $e = htmlspecialchars(trim($_POST['email']), ENT_QUOTES);
    $ip = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];

    if(check_email_address($e) == FALSE) {
        $_P['comment_body'] .= '<h2>'.$lng['email_err'].'</h2>';
    }
    elseif(!empty($e2g['captcha']) && ((trim($_POST['vericode'])=='') || (isset($_SESSION['veriword']) && $_SESSION['veriword'] != $_POST['vericode']))) {
        $_P['comment_body'] .= '<h2>'.$lng['captcha_err'].'</h2>';
    }

    elseif (!empty($n) && !empty($c)) {
        if (mysql_query('INSERT INTO '.$table_prefix.'easy2_comments (file_id,author,email,ip_address,comment,date_added) '
        . "VALUES($id,'$n','$e','$ip','$c', NOW())")) {

            mysql_query('UPDATE '.$table_prefix.'easy2_files SET comments=comments+1 WHERE id='.$id);
            $_P['comment_body'] .= '<h3>'.$lng['comment_added'].'</h3>';

        } else {
            $_P['comment_body'] .= '<h2>'.$lng['comment_add_err'].'</h2>';
        }
    }
}
else {
    $_P['comment_body'] .= '<h2>'.$lng['empty_name_comment'].'</h2>';
}

// COMMENT ROW TEMPLATE

if (file_exists($e2g['comments_row_tpl'])) {
    $row_tpl = file_get_contents($e2g['comments_row_tpl']);
} elseif ( !($row_tpl = get_chunk($e2g['comments_row_tpl'])) ) {
    die ('Comments row template not found!');
}

$res = mysql_query('SELECT * FROM '.$table_prefix.'easy2_comments WHERE file_id = '.$id.' AND STATUS=1 ORDER BY id DESC LIMIT '.($cpn*$e2g['ecl']).', '.$e2g['ecl']);
$i = 0;
while($l = mysql_fetch_array($res, MYSQL_ASSOC)) {

    $l['i'] = $i%2;

    if (!empty($l['email'])) $l['name_w_mail'] = '<a href="mailto:'.$l['email'].'">'.$l['author'].'</a>';
    else $l['name_w_mail'] = $l['author'];

    $_P['comment_body'] .= filler($row_tpl, $l);
    $i++;
}

// COUNT PAGES
$res = mysql_query('SELECT COUNT(*) FROM '.$table_prefix.'easy2_comments WHERE file_id = '.$id);
list($cnt) = mysql_fetch_row($res);
if ($cnt > $e2g['ecl']) {
    $_P['comment_pages'] = '<p class="pnums">'.$lng['pages'].':';
    $i = 0;
    while ($i*$e2g['ecl'] < $cnt) {
        if ($i == $cpn) $_P['comment_pages'] .= '<b>'.($i+1).'</b> ';
        else $_P['comment_pages'] .= '<a href="?id='.$id.'&cpn='.$i.'">'.($i+1).'</a> ';
        $i++;
    }
    $_P['comment_pages'] .= '</p>';
}

mysql_close();

// COMMENT TEMPLATE

if (file_exists($e2g['comments_tpl'])) {
    $tpl = file_get_contents($e2g['comments_tpl']);
} elseif ( !($tpl = get_chunk($e2g['comments_tpl'])) ) {
    die ('Comments template not found!');
}

if(!empty($e2g['captcha'])) {
    $seed=rand();
    $_SESSION['veriword'] = md5($seed);
    $siteurl = str_replace("assets/modules/easy2/", "", $site_url);
    $_P['captcha'] = '<tr><td>'.$_P['code'].'</td><td><input type="text" name="vericode" /></td><td colspan="2" class="captcha_cell"><img src="'.$siteurl.'manager/includes/veriword.php?rand='.$seed.'" alt="" /><td></tr>';
}
else {
    $_P['captcha'] ='';
}
header('Content-Type: text/html; charset='.$_P['charset']);
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

function check_email_address($email) {
    if (!preg_match("/^([a-zA-Z0-9])+([\.a-zA-Z0-9_-])*@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-]+)*\.([a-zA-Z]{2,6})$/", $email)) {
        return false;
    }
    return true;
}
?>