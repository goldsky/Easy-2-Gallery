
/******************************************************************************
*
*  EASY 2 GALLERY BY Cx2 <inteldesign@mail.ru>
*  VERSION 1.35
*
******************************************************************************/


require './assets/modules/easy2/config.easy2gallery.php';

// GALLERY ID
$gid = (!empty($gid) && is_numeric($gid)) ? $gid : 1;
$gid = (!empty($_GET['gid']) && is_numeric($_GET['gid'])) ? (int) $_GET['gid'] : $gid;
$rgid = (!empty($rgid) && is_numeric($rgid)) ? $rgid : 0;

// FILE ID
$fid = (!empty($fid) && is_numeric($fid)) ? $fid : 0;
$fid = (!empty($_GET['fid']) && is_numeric($_GET['fid'])) ? (int) $_GET['fid'] : $fid;

// WIDTH
$w = (!empty($w) && is_numeric($w)) ? (int) $w : $e2g['w'];

// HEIGHT
$h = (!empty($h) && is_numeric($h)) ? (int) $h : $e2g['h'];

// JPEG QUALITY
$thq = (!empty($thq) && $thq<=100 && $thq>=0) ? (int) $thq : $e2g['thq'];

// NAME LENGHT
$name_len = (!empty($name_len) && is_numeric($name_len)) ? (int) $name_len : $e2g['name_len'];

// DIRECTORY NAME LENGHT
$cat_name_len = (!empty($cat_name_len) && is_numeric($cat_name_len)) ? (int) $cat_name_len : $e2g['cat_name_len'];

// COLLS
$colls = (!empty($colls) && is_numeric($colls)) ? (int) $colls : $e2g['colls'];

// NO TABLES
$notables = (isset($notables) && is_numeric($notables)) ? $notables : (isset($e2g['notables']) ? $e2g['notables'] : 0);
$notables = $fid ? 1 : $notables;

// LIMIT
$limit = (!empty($limit) && is_numeric($limit)) ? (int) $limit : $e2g['limit'];

// GLIB
$glib = (!empty($glib)) ? $glib : $e2g['glib'];

// COMMENTS
$ecm = (isset($ecm) && is_numeric($ecm)) ? $ecm : $e2g['ecm'];

// PAGE NUMBER
$gpn = (!empty($gpn) && is_numeric($gpn)) ? $gpn : 0;
$gpn = (!empty($_GET['gpn']) && is_numeric($_GET['gpn'])) ? (int) $_GET['gpn'] : $gpn;

// ORDER BY
$orderby = (!empty($orderby)) ? $orderby : $e2g['orderby'];
$orderby = preg_replace('/[^_a-z]/i', '', $orderby);
$cat_orderby = (!empty($cat_orderby)) ? $cat_orderby : $e2g['cat_orderby'];
$cat_orderby = preg_replace('/[^_a-z]/i', '', $cat_orderby);

// ORDER
$order = (!empty($order)) ? $order : $e2g['order'];
$order = preg_replace('/[^a-z]/i', '', $order);
$cat_order = (!empty($cat_order)) ? $cat_order : $e2g['cat_order'];
$cat_order = preg_replace('/[^a-z]/i', '', $cat_order);

// GALLERY CSS
$css = (!empty($css)) ? str_replace('../', '', $css) : $e2g['css'];

// GALLERY TEMPLATE
$tpl = (!empty($tpl)) ? str_replace('../', '', $tpl) : $e2g['tpl'];

// DIR TEMPLATE
$dir_tpl = (!empty($dir_tpl)) ? str_replace('../', '', $dir_tpl) : $e2g['dir_tpl'];

// THUMB TEMPLATE
$thumb_tpl = (!empty($thumb_tpl)) ? str_replace('../', '', $thumb_tpl) : $e2g['thumb_tpl'];

// THUMB RAND TEMPLATE
$rand_tpl = (!empty($rand_tpl)) ? str_replace('../', '', $rand_tpl) : $e2g['rand_tpl'];

//SLIDESHOW GROUP
$show_group = isset($show_group) ? $show_group : 'Gallery'.$gid;

// CRUMBS
$crumbs_separator = isset($crumbs_separator) ? $crumbs_separator : ' / ';
$crumbs_showHome = isset($crumbs_showHome) ? $crumbs_showHome : 0;
$crumbs_showAsLinks = isset($crumbs_showAsLinks) ? $crumbs_showAsLinks : 1;
$crumbs_showCurrent = isset($crumbs_showCurrent) ? $crumbs_showCurrent : 1;

//mbstring
$charset = $modx->config['modx_charset'];
$mbstring = function_exists('mb_strlen') && function_exists('mb_substr');

#################### FUNCTIONS ####################

if (!function_exists('get_thumb')) {
function get_thumb ($gdir, $path, $w = 150, $h = 150, $thq=80, $resize_type = 'inner') {
    global $modx;

    if (empty($path)) return false;

    $thumb_path = '_thumbnails/'.substr($path, 0, strrpos($path, '.')).'_'.$w.'x'.$h.'.jpg';

    if (!file_exists($gdir.$thumb_path) && file_exists($gdir.$path)) {

        $i = getimagesize($gdir.$path);
        if ($i[2] == 1) $im = imagecreatefromgif ($gdir.$path);
        elseif ($i[2] == 2) $im = imagecreatefromjpeg ($gdir.$path);
        elseif ($i[2] == 3) $im = imagecreatefrompng ($gdir.$path);
        else return false;

        if ($i[0]/$w > 2 || $i[1]/$h > 2) {
            $tmp_w = $w*2;
            $tmp_h = round($i[1] * ($tmp_w/$i[0]));

            $temp = imagecreatetruecolor ($tmp_w, $tmp_h);
            imagecopyresized ($temp, $im, 0, 0, 0, 0, $tmp_w, $tmp_h, $i[0], $i[1]);

            $i[0] = $tmp_w;
            $i[1] = $tmp_h;

            imagedestroy($im);
            $im = $temp;
        }



        if ($resize_type == 'inner') {
            // 'inner' - trim to default dimensions

            // Shifts
            $x = $y = 0;

            // Dimensions
            $w2 = $w;
            $h2 = $h;

            if ($i[0] > $i[1]) {
                $w2 = round($i[0] * $h / $i[1]);
                if ($w2 > $w) $x = ($w2 - $w)/2 * (-1);
            } else {
                $h2 = round($i[1] * $w / $i[0]);
                if ($h2 > $h) $y = ($h2 - $h)/2 * (-1);
            }

            $pic = imagecreatetruecolor($w, $h);
            $bgc = imagecolorallocate($pic, 255, 255, 255);
            imagefill($pic, 0, 0, $bgc);
            imagecopyresampled($pic, $im, $x, $y, 0, 0, $w2, $h2, $i[0], $i[1]);

        } else {
            // 'resize' - proportionally reduce to default dimensions

            if ($i[0] > $i[1]) $h = round($i[1] * $w / $i[0]);
            else $w = round($i[0] * $h / $i[1]);

            $pic = imagecreatetruecolor($w, $h);
            $bgc = imagecolorallocate($pic, 255, 255, 255);
            imagefill($pic, 0, 0, $bgc);
            imagecopyresampled($pic, $im, 0, 0, 0, 0, $w, $h, $i[0], $i[1]);
        }


        // Make dirs
        $dirs = explode('/', $path);
        $npath = $gdir.'_thumbnails';
        for ($c = 0; $c < count($dirs) - 1; $c++) {
            $npath .= '/'.$dirs[$c];
            if (is_dir($npath)) continue;
            if(!mkdir($npath)) return false;
            @chmod($npath, 0755);
        }


        imagejpeg($pic, $gdir.$thumb_path, $thq);
        @chmod($gdir.$thumb_path, 0644);

        imagedestroy($pic);
        imagedestroy($im);
    }

    return $gdir.$thumb_path;
}
}

if (!function_exists('get_path')) {
function get_path ($id) {
    global $modx;

    $result = array();

    $res = mysql_query('SELECT A.cat_id, A.cat_name FROM '.$modx->db->config['table_prefix'].'easy2_dirs A, '.$modx->db->config['table_prefix'].'easy2_dirs B WHERE B.cat_id='.$id.' AND B.cat_left BETWEEN A.cat_left AND A.cat_right ORDER BY A.cat_left');
    while ($l = mysql_fetch_row($res)) {
        $result[$l[0]] = $l[1];
    }

    if (empty($result)) return null;

    return $result;
}
}

if (!function_exists('filler')) { 
function filler ($tpl, $data, $prefix = '[+easy2:', $suffix = '+]') {
     foreach($data as $k => $v) {
         $tpl = str_replace($prefix.(string)$k.$suffix, (string)$v, $tpl);
     }
     return $tpl;
}
}

if (!function_exists('get_folder_img')) {
function get_folder_img ($gid) {
    global $modx;
    $res = mysql_query('SELECT DISTINCT F.* FROM '.$modx->db->config['table_prefix'].'easy2_dirs A, '. $modx->db->config['table_prefix'].'easy2_dirs B, '. $modx->db->config['table_prefix'].'easy2_files F '
. 'WHERE (B.cat_id='.$gid.' AND A.cat_left >= B.cat_left AND A.cat_right <= B.cat_right AND A.cat_level > B.cat_level AND A.cat_visible = 1 AND F.dir_id = A.cat_id) OR F.dir_id = '.$gid.' ORDER BY A.cat_level ASC, F.id DESC');
    $result = mysql_fetch_array($res, MYSQL_ASSOC);
    if ($result) $result['count'] = mysql_num_rows($res);
	mysql_free_result($res);
    return $result;
}
}

#################### CODE ####################

// RANDOM IMAGE

if ($orderby == 'random' && $limit == 1) {

    if (file_exists($rand_tpl)) {
        $rand_tpl = file_get_contents($rand_tpl);
    } elseif (!empty($modx->chunkCache[$rand_tpl])) {
        $rand_tpl = $modx->chunkCache[$rand_tpl];
    } else {
        return 'Template not found!';
    }

    $res = mysql_query('SELECT * FROM '.$modx->db->config['table_prefix'].'easy2_files '
    . 'WHERE status = 1 '
    . ($rgid? 'AND dir_id='.$rgid.' ' : '')
    . 'ORDER BY RAND() LIMIT 1');

    $num_rows = mysql_num_rows($res);
    if (!$num_rows) return;

    $l = mysql_fetch_array($res, MYSQL_ASSOC);
    $pos = strrpos($l['filename'], '.');
    $ext = substr($l['filename'], $pos);

    $l['title'] = $l['name'];
    if ($l['name'] == '') $l['name'] = '&nbsp;';
    elseif ($mbstring){
        if (mb_strlen($l['name'], $charset) > $name_len) $l['name'] = mb_substr($l['name'], 0, $name_len-1, $charset).'...';
    }
    elseif (strlen($l['name']) > $name_len) $l['name'] = substr($l['name'], 0, $name_len-1).'...';

    $l['w'] = $w;
    $l['h'] = $h;

    $path = get_path($l['dir_id']);
    if (count($path) > 1) {
        unset($path[1]);
        $path = implode('/', array_keys($path)).'/';
    } else {
        $path = '';
    }
    $l['src'] = get_thumb($e2g['dir'], $path.'/'.$l['id'].$ext, $w, $h, $thq);


    return filler($rand_tpl, $l);
}

// PATHS

$_e2g = array('content'=>'','pages'=>'','parent_id'=>0,'back'=>'');

$path = get_path($gid);
$_e2g['cat_name'] = is_array($path) ? end($path) : '';
$crumbs='';
if (count($path) > 1) {
    end($path);
    prev($path);
    $_e2g['parent_id'] = key($path);
    $_e2g['parent_name'] = $path[$_e2g['parent_id']];

    //crumbs
    $cnt=0;
    foreach ($path as $k=>$v) {
        $cnt++;
        if ($cnt==1 && !$crumbs_showHome) {continue;}
        if ($cnt==count($path) && !$crumbs_showCurrent) {continue;}
        if ($cnt!=count($path)) $crumbs .= $crumbs_separator.($crumbs_showAsLinks ? '<a href="[~[*id*]~]&gid='.$k.'">'.$v.'</a>' : $v);
        else $crumbs .= $crumbs_separator.'<span class="e2g_currentCrumb">'.$v.'</span>';
    }
    $crumbs = substr_replace($crumbs,'',0,strlen($crumbs_separator));

    unset($path[1]);
    $path = implode('/', array_keys($path)).'/';
} else {
    $path = '';
}

// CSS STYLES

if (file_exists($css)) {
    $modx->regClientCSS($modx->config['base_url'].$css,'screen');
} 

if ($glib == 'fancybox') {
   $modx->regClientCSS($modx->config['base_url'].'assets/libs/fancybox/fancybox.css','screen');
   $modx->regClientStartupScript('http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js');
   $modx->regClientStartupScript($modx->config['base_url'].'assets/libs/fancybox/fancybox.js');
}
if ($glib == 'floatbox') {
    $modx->regClientCSS($modx->config['base_url'].'assets/libs/floatbox/floatbox.css','screen');
    $modx->regClientStartupScript($modx->config['base_url'].'assets/libs/floatbox/floatbox.js');
}
if ($glib == 'highslide') {
    $modx->regClientCSS($modx->config['base_url'].'assets/libs/highslide/highslide.css','screen');
    $modx->regClientStartupScript($modx->config['base_url'].'assets/libs/highslide/highslide.js');
    $modx->regClientStartupScript($modx->config['base_url'].'assets/libs/highslide/highslide-settings.js');
}
if ($glib == 'shadowbox') {
    $modx->regClientCSS($modx->config['base_url'].'assets/libs/shadowbox/shadowbox.css','screen');
    $modx->regClientStartupScript($modx->config['base_url'].'assets/libs/shadowbox/shadowbox.js');
    $modx->regClientStartupScript($modx->config['base_url'].'assets/libs/shadowbox/shadowbox-settings.js');
}
if ($glib == 'slimbox') {
   $modx->regClientCSS($modx->config['base_url'].'assets/libs/slimbox/css/slimbox.css','screen');
   $modx->regClientStartupScript('http://ajax.googleapis.com/ajax/libs/mootools/1.2.2/mootools-yui-compressed.js');
   $modx->regClientStartupScript($modx->config['base_url'].'assets/libs/slimbox/js/slimbox.js');
}
if ($glib == 'slimbox2') {
   $modx->regClientCSS($modx->config['base_url'].'assets/libs/slimbox/css/slimbox.css','screen');
   $modx->regClientStartupScript('http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js');
   $modx->regClientStartupScript($modx->config['base_url'].'assets/libs/slimbox/js/slimbox2.js');
}
if ($glib == 'lightwindow') {
   $modx->regClientCSS($modx->config['base_url'].'assets/libs/lightwindow/css/lightwindow.css','screen');
   $modx->regClientStartupScript('http://ajax.googleapis.com/ajax/libs/prototype/1.6.0.3/prototype.js');
   $modx->regClientStartupScript('http://ajax.googleapis.com/ajax/libs/scriptaculous/1.8.2/scriptaculous.js?load=effects');
   $modx->regClientStartupScript($modx->config['base_url'].'assets/libs/lightwindow/js/lightwindow.js');
}
if ($glib == 'colorbox') {
   $modx->regClientCSS($modx->config['base_url'].'assets/libs/colorbox/colorbox.css','screen');
   $modx->regClientStartupScript('http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js');
   $modx->regClientStartupScript($modx->config['base_url'].'assets/libs/colorbox/jquery.colorbox-min.js');
}

if (!$fid) {

$i = 0;

// SUBDIRS & THUMBS FOR SUBDIRS
$res = mysql_query('SELECT A.* '
. 'FROM '.$modx->db->config['table_prefix'].'easy2_dirs A, '
. $modx->db->config['table_prefix'].'easy2_dirs B '
. 'WHERE B.cat_id='.$gid.' '
. 'AND A.cat_left >= B.cat_left '
. 'AND A.cat_right <= B.cat_right '
. 'AND A.cat_level = B.cat_level + 1 '
. 'AND A.cat_visible = 1 '
. 'GROUP BY A.cat_id '
. 'ORDER BY  '. ( $cat_orderby == 'random' ? 'RAND() ' : 'A.'.$cat_orderby.' '.$cat_order));

$num_rows = mysql_num_rows($res);
$_e2g['content'] = $num_rows ? ($notables ? '<div class="e2g">':'<table class="e2g"><tr>') : '';

// DIRECTORY TEMPLATE

if (file_exists($dir_tpl)) {
    $tpl_dir = file_get_contents($dir_tpl);
} elseif (!empty($modx->chunkCache[$dir_tpl])) {
    $tpl_dir = $modx->chunkCache[$dir_tpl];
} else {
    return 'Directory template '.$dir_tpl.' not found!';
}

while ($l = mysql_fetch_array($res, MYSQL_ASSOC)) {

    // search image for subdir
    $l1=get_folder_img($l['cat_id']);
    if (!$l1) continue;
    $l['count'] = $l1['count'];

    //path to thumb 
    $path1=get_path($l1['dir_id']);
    if (count($path1) > 1) {
        unset($path1[1]);
        $path1 = implode('/', array_keys($path1)).'/';
    } else {
        $path1 = '';
    }

    if (($i>0) && ($i % $colls == 0) && !$notables) $_e2g['content'] .= '</tr><tr>';

    $pos = strrpos($l1['filename'], '.');
    $ext = substr($l1['filename'], $pos);

    $l['title'] = $l['cat_name'];
    if ($l['cat_name'] == '') $l['cat_name'] = '&nbsp;';
    elseif ($mbstring){
        if (mb_strlen($l['cat_name'], $charset) > $cat_name_len) $l['cat_name'] = mb_substr($l['cat_name'], 0, $cat_name_len-1, $charset).'...';
    }
    elseif (strlen($l['cat_name']) > $cat_name_len) $l['cat_name'] = substr($l['cat_name'], 0, $cat_name_len-1).'...';

    $l['w'] = $w;
    $l['h'] = $h;
    $l['src'] = get_thumb($e2g['dir'], $path1.$l1['id'].$ext, $w, $h, $thq);

    $_e2g['content'] .= $notables ? filler($tpl_dir, $l) : '<td>'.filler($tpl_dir, $l).'</td>';
    $i++;

}

$_e2g['content'] .= $num_rows ? ($notables ? '</div>' : '</tr></table>') : '';

} // if (!$fid)

// THUMBS FOR CURRENT DIR
$res = mysql_query('SELECT * FROM '.$modx->db->config['table_prefix'].'easy2_files '
. ($fid? 'WHERE id='.$fid.' ' : 'WHERE dir_id='.$gid.' ')
. 'AND status = 1 '
. 'ORDER BY '. ( $orderby == 'random' ? 'RAND() ' : $orderby.' '.$order.' ')
. 'LIMIT '.($gpn * $limit).', '.$limit);

$i = 0;
$num_rows = mysql_num_rows($res);
$_e2g['content'] .= $num_rows ? ($notables ? '<div class="e2g">':'<table class="e2g"><tr>') : '';

// THUMBNAIL TEMPLATE
if (file_exists($thumb_tpl)) {
    $tpl_thumb = file_get_contents($thumb_tpl);
} elseif (!empty($modx->chunkCache[$thumb_tpl])) {
    $tpl_thumb = $modx->chunkCache[$thumb_tpl];
} else {
    return 'Thumbnail template not found!';
}

while ($l = mysql_fetch_array($res, MYSQL_ASSOC)) {

    //path to single thumb
    if ($fid) {
        $path=get_path($l['dir_id']);
        if (count($path) > 1) {
            unset($path[1]);
            $path = implode('/', array_keys($path)).'/';
        } else {
            $path = '';
        }
    }

    if (($i>0) && ($i % $colls == 0) && !$notables) $_e2g['content'] .= '</tr><tr>';

    $pos = strrpos($l['filename'], '.');
    $ext = substr($l['filename'], $pos);

    $l['title'] = $l['name'];

    if ($l['name'] == '') $l['name'] = '&nbsp;';
    elseif ($mbstring){
        if (mb_strlen($l['name'], $charset) > $name_len) $l['name'] = mb_substr($l['name'], 0, $name_len-1, $charset).'...';
    }
    elseif (strlen($l['name']) > $name_len) $l['name'] = substr($l['name'], 0, $name_len-1).'...';

    $l['w'] = $w;
    $l['h'] = $h;
    $l['src'] = get_thumb($e2g['dir'], $path.$l['id'].$ext, $w, $h, $thq);

// gallery activation
if ($glib == 'highslide') {$l['glibact']='class="highslide" onclick="return hs.expand(this, {slideshowGroup: \'mygroup\'})"';}
if ($glib == 'slimbox' || $glib == 'slimbox2' || $glib == 'fancybox' || $glib == 'colorbox') {$l['glibact']='rel="lightbox['.$show_group.']"';}
if ($glib == 'shadowbox') {$l['glibact']='rel="shadowbox['.$show_group.'];player=img"';}
if ($glib == 'floatbox') {$l['glibact']='class="floatbox" rev="doSlideshow:true group:'.$show_group.'"';}
if ($glib == 'lightwindow') {$l['glibact']='class="lightwindow" rel="Gallery['.$show_group.']" params="lightwindow_type=image"';}

    if ($ecm == 1) {
        $l['com'] = 'e2gcom'.($l['comments']==0?0:1);

// iframe activation
if ($glib == 'highslide') {$l['comments'] = '<a href="assets/modules/easy2/comments.easy2gallery.php?id='.$l['id'].'" onclick="return hs.htmlExpand(this, { objectType: \'iframe\'} )">'.$l['comments'].'</a>';}
if ($glib == 'slimbox' || $glib == 'slimbox2') {
   $modx->regClientCSS($modx->config['base_url'].'assets/libs/highslide/highslide.css','screen');
   $modx->regClientStartupScript($modx->config['base_url'].'assets/libs/highslide/highslide-iframe.js');
   $l['comments'] = '<a href="assets/modules/easy2/comments.easy2gallery.php?id='.$l['id'].'" onclick="return hs.htmlExpand(this, { objectType: \'iframe\'} )">'.$l['comments'].'</a>';
}
if ($glib == 'fancybox' || $glib == 'colorbox') {$l['comments'] = '<a href="assets/modules/easy2/comments.easy2gallery.php?id='.$l['id'].'" class="iframe">'.$l['comments'].'</a>';}
if ($glib == 'shadowbox') {$l['comments'] = '<a href="assets/modules/easy2/comments.easy2gallery.php?id='.$l['id'].'" rel="shadowbox;width=400;height=250;player=iframe">'.$l['comments'].'</a>';}
if ($glib == 'floatbox') {$l['comments'] = '<a href="assets/modules/easy2/comments.easy2gallery.php?id='.$l['id'].'" rel="floatbox" rev="type:iframe width:400 height:250 enableDragResize:true controlPos:tr innerBorder:0">'.$l['comments'].'</a>';}
if ($glib == 'lightwindow') {$l['comments'] = '<a href="assets/modules/easy2/comments.easy2gallery.php?id='.$l['id'].'" class="lightwindow" params="lightwindow_type=external,lightwindow_width=400,lightwindow_height=250">'.$l['comments'].'</a>';}

    } else {
        $l['comments'] = '';
        $l['com'] = 'not_display';
    }

	$_e2g['content'] .= $notables ? filler($tpl_thumb, $l) : '<td>'.filler($tpl_thumb, $l).'</td>';

    $i++;
}

$_e2g['content'] .= $num_rows ? ($notables ? '</div>' : '</tr></table>') : '';

if ($fid) return $_e2g['content'];

// PAGES LINKS
$res = mysql_query('SELECT COUNT(*) FROM '.$modx->db->config['table_prefix'].'easy2_files WHERE dir_id='.$gid);
list($cnt) = mysql_fetch_row($res);
if ($cnt > $limit) {
    $_e2g['pages'] = '<div class="e2gpnums">';
    $i = 0;
    while ($i*$limit < $cnt) {
        if ($i == $gpn) $_e2g['pages'] .= '<b>'.($i+1).'</b> ';
        else $_e2g['pages'] .= '<a href="[~[*id*]~]&gid='.$gid.'&gpn='.$i.'">'.($i+1).'</a> ';
        $i++;
    }

    $_e2g['pages'] .= '</div>';
}


// BACK BUTTON
if ($_e2g['parent_id'] > 0) {
    $_e2g['back'] = '<p class="e2gback">&#8249;&#8249; <a href="[~[*id*]~]&gid='.$_e2g['parent_id'].'">'.$_e2g['parent_name'].'</a></p>';
}

// CRUMBS
$_e2g['crumbs']=$crumbs;

// GALLERY TEMPLATE
if (file_exists($tpl)) {
    $gal_tpl = file_get_contents($tpl);
} elseif (!empty($modx->chunkCache[$tpl])) {
    $gal_tpl = $modx->chunkCache[$tpl];
} else {
    return 'Gallery template not found!';
}

return filler($gal_tpl, $_e2g);
