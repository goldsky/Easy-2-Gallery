<?php

/**
 * EASY 2 GALLERY
 * Gallery Snippet Comments for Easy 2 Gallery Module for MODx Evolution
 * @author Cx2 <inteldesign@mail.ru>
 * @author Temus <temus3@gmail.com>
 * @author goldsky <goldsky@fastmail.fm>
 * @version 1.4.0
 */
error_reporting(0);
if (empty($_GET['id']) || !is_numeric($_GET['id'])) {
    die('<h2 style="color:red">ID Error!</h2>');
}

$cpn = (empty($_GET['cpn']) || !is_numeric($_GET['cpn'])) ? 0 : (int) $_GET['cpn'];

$modxMgrConfigFile = realpath('../../../manager/includes/config.inc.php');
if (empty($modxMgrConfigFile) || !file_exists($modxMgrConfigFile)) {
    die(__FILE__ . ', ' . __LINE__ . ': missing config file');
}
require $modxMgrConfigFile;

startCMSSession();

mysql_connect($database_server, $database_user, $database_password)
        or die('<h2 style="color:red">MySQL connect error!</h2>');
mysql_select_db(str_replace('`', '', $dbase));
@mysql_query("{$database_connection_method} {$database_connection_charset}");

// e2g's configs
$e2g_res = mysql_query('SELECT * FROM ' . $table_prefix . 'easy2_configs');
if (!$e2g_res) {
    sh_err('MySQL query error for configs');
    die;
} else {
    while ($row = mysql_fetch_assoc($e2g_res)) {
        $e2g[$row['cfg_key']] = $row['cfg_val'];
    }
}

if ($e2g['ecm'] == 0) {
    die('<h2 style="color:red">Comments disabled!</h2>');
}

$id = (int) $_GET['id'];

$res = mysql_query('SELECT * FROM ' . $table_prefix . "system_settings");
while ($row = mysql_fetch_assoc($res))
    $settings[$row['setting_name']] = $row['setting_value'];

$lngCommentFile = realpath('includes/langs/' . $settings['manager_language'] . '.comments.php');
if (!empty($lngCommentFile) && file_exists($lngCommentFile)) {
    include $lngCommentFile;
    $lng = $e2g_lang[$settings['manager_language']];
} else {
    include realpath('includes/langs/english.comments.php');
    $lng = $e2g_lang['english'];
}

$_P['charset'] = $settings['modx_charset'];

// output from language file
$_P['title'] = $lng['title'];
$_P['comment_add'] = $lng['comment_add'];
$_P['name'] = $lng['name'];
$_P['email'] = $lng['email'];
$_P['usercomment'] = $lng['usercomment'];
$_P['send_btn'] = $lng['send_btn'];
$_P['comment_body'] = '';
$_P['comment_pages'] = '';
$_P['code'] = $lng['code'];
$_P['waitforapproval'] = $lng['waitforapproval'];

// INSERT THE COMMENT INTO DATABASE
if (!empty($_POST['name']) && !empty($_POST['comment'])) {
    $n = htmlspecialchars(trim($_POST['name']), ENT_QUOTES);
    $c = htmlspecialchars(trim($_POST['comment']), ENT_QUOTES);
    $e = htmlspecialchars(trim($_POST['email']), ENT_QUOTES);
    $ip = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];

    if (checkEmailAddress($e) == FALSE) {
        $_P['comment_body'] .= '<h2>' . $lng['email_err'] . '</h2>';
    } elseif ($e2g['recaptcha'] == 1 && (trim($_POST['recaptcha_response_field']) == '')) {
        $_P['comment_body'] .= '<h2>' . $lng['recaptcha_err'] . '</h2>';
    }

    //captcha
    if ($e2g['recaptcha'] == 1 && $_POST['recaptcha_response_field']) {
        require_once 'includes/recaptchalib.php';
        # the response from reCAPTCHA
        $resp = null;
        # the error code from reCAPTCHA, if any
        $error = null;

        # was there a reCAPTCHA response?
        if ($_POST["recaptcha_response_field"]) {
            $privatekey = $e2g['recaptcha_key_private'];
            $resp = recaptcha_check_answer($privatekey,
                            $_SERVER["REMOTE_ADDR"],
                            $_POST["recaptcha_challenge_field"],
                            $_POST["recaptcha_response_field"]);

            if (!$resp->is_valid) {
                # set the error code so that we can display it
                $error = $resp->error;
            } else {
                $com_insert = 'INSERT INTO ' . $table_prefix . 'easy2_comments '
                        . '(file_id,author,email,ip_address,comment,date_added) '
                        . "VALUES($id,'$n','$e','$ip','$c', NOW())";
                if (mysql_query($com_insert)) {
                    mysql_query('UPDATE ' . $table_prefix . 'easy2_files SET comments=comments+1 WHERE id=' . $id);
                    $_P['comment_body'] .= '<h3>' . $lng['comment_added'] . '</h3>';
                } else {
                    $_P['comment_body'] .= '<h2>' . $lng['comment_add_err'] . '</h2>';
                }
            }
        }
    }
    // NOT USING reCaptcha
    else {
        $com_insert = 'INSERT INTO ' . $table_prefix . 'easy2_comments '
                . '(file_id,author,email,ip_address,comment,date_added) '
                . "VALUES($id,'$n','$e','$ip','$c', NOW())";
        if (mysql_query($com_insert)) {
            mysql_query('UPDATE ' . $table_prefix . 'easy2_files SET comments=comments+1 WHERE id=' . $id);
            $_P['comment_body'] .= '<h3>' . $lng['comment_added'] . '</h3>';
        } else {
            $_P['comment_body'] .= '<h2>' . $lng['comment_add_err'] . '</h2>';
        }
    }
}

if ($_POST && empty($_POST['name']) && empty($_POST['comment'])) {
    $_P['comment_body'] .= '<h2>' . $lng['empty_name_comment'] . '</h2>';
}


// COMMENT ROW TEMPLATE

if (file_exists(realpath($e2g['comments_row_tpl']))) {
    $row_tpl = file_get_contents($e2g['comments_row_tpl']);
} elseif (!($row_tpl = getChunk($e2g['comments_row_tpl']))) {
    die('Comments row template not found!');
}

$res = mysql_query('SELECT * FROM ' . $table_prefix . 'easy2_comments '
                . 'WHERE file_id = ' . $id . ' '
                . 'AND STATUS=1 ORDER BY id DESC '
                . 'LIMIT ' . ($cpn * $e2g['ecl']) . ', ' . $e2g['ecl']);
$i = 0;
while ($l = mysql_fetch_assoc($res)) {

    $l['i'] = $i % 2;

    $l['name_w_mail'] = '<a href="mailto:' . $l['email'] . '">' . $l['author'] . '</a>';

    $_P['comment_body'] .= filler($row_tpl, $l);
    $i++;
}

// COUNT PAGES
$res = mysql_query('SELECT COUNT(*) FROM ' . $table_prefix . 'easy2_comments WHERE file_id = ' . $id);
list($cnt) = mysql_fetch_row($res);
if ($cnt > $e2g['ecl']) {
    $_P['comment_pages'] = '<p class="pnums">' . $lng['pages'] . ':';
    $i = 0;
    while ($i * $e2g['ecl'] < $cnt) {
        if ($i == $cpn)
            $_P['comment_pages'] .= '<b>' . ($i + 1) . '</b> ';
        else
            $_P['comment_pages'] .= '<a href="?id=' . $id . '&cpn=' . $i . '">' . ($i + 1) . '</a> ';
        $i++;
    }
    $_P['comment_pages'] .= '</p>';
}

mysql_close();

// COMMENT TEMPLATE

if (file_exists(realpath($e2g['comments_tpl']))) {
    $tpl = file_get_contents($e2g['comments_tpl']);
} elseif (!($tpl = getChunk($e2g['comments_tpl']))) {
    die('Comments template not found!');
}

if ($e2g['recaptcha'] == 1) {
    $publickey = $e2g['recaptcha_key_public'];
    $_P['recaptcha'] = '
                <tr>
                    <td colspan="4">' . recaptchaForm($e2g, $publickey, $error) . '</td>
                </tr>';
} else {
    $_P['recaptcha'] = '';
}

header('Content-Type: text/html; charset=' . $_P['charset']);
echo filler($tpl, $_P);

/**
 * Gets the challenge HTML (javascript and non-javascript version).
 * This is called from the browser, and the resulting reCAPTCHA HTML widget
 * is embedded within the HTML form it was called from.
 * @param string $pubkey A public key for reCAPTCHA
 * @param string $error The error given by reCAPTCHA (optional, default is null)
 * @param boolean $use_ssl Should the request be made over ssl? (optional, default is false)

 * @return string - The HTML to be embedded on the user's form.
 */
function recaptchaForm($e2g, $pubkey, $error = null, $use_ssl = false) {
    require_once 'includes/recaptchalib.php';

    $theme = $e2g['recaptcha_theme'];
    $theme_custom = $e2g['recaptcha_theme_custom'];

    if ($pubkey == null || $pubkey == '') {
        die("To use reCAPTCHA you must get an API key from <a href='https://www.google.com/recaptcha/admin/create' target=\"_blank\">https://www.google.com/recaptcha/admin/create</a>");
    }

    if ($use_ssl) {
        $server = RECAPTCHA_API_SECURE_SERVER;
    } else {
        $server = RECAPTCHA_API_SERVER;
    }

    $errorpart = "";
    if ($error) {
        $errorpart = "&amp;error=" . $error;
    }
    return '
        <script type="text/javascript">
        var RecaptchaOptions = {
        theme : \'' . $theme . '\'
            ' . ($theme == 'custom' ? ',custom_theme_widget: \'' . $theme_custom . '\'' : '') . '};
        </script>
        <script type="text/javascript" src="' . $server . '/challenge?k=' . $pubkey . $errorpart . '"></script>
        <noscript>
            <iframe src="' . $server . '/noscript?k=' . $pubkey . $errorpart . '" height="300" width="500" frameborder="0"></iframe><br/>
            <textarea name="recaptcha_challenge_field" rows="3" cols="40"></textarea>
            <input type="hidden" name="recaptcha_response_field" value="manual_challenge"/>
        </noscript>';
}

function filler($tpl, $data, $prefix = '[+easy2:', $suffix = '+]') {
    foreach ($data as $k => $v) {
        $tpl = str_replace($prefix . (string) $k . $suffix, (string) $v, $tpl);
    }
    return $tpl;
}

function getChunk($name) {
    global $table_prefix;

    $res = mysql_query('SELECT * FROM ' . $table_prefix . "site_htmlsnippets WHERE name='" . mysql_real_escape_string($name) . "'");
    if (mysql_num_rows($res) > 0) {
        $row = mysql_fetch_assoc($res);
        return $row['snippet'];
    } else {
        return false;
    }
}

function checkEmailAddress($email) {
    if (!preg_match("/^([a-zA-Z0-9])+([\.a-zA-Z0-9_-])*@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-]+)*\.([a-zA-Z]{2,6})$/", $email)) {
        return false;
    }
    return true;
}

?>