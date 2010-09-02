<?php
// disabled for:
// http://modxcms.com/forums/index.php/topic,23177.msg308887.html#msg308887
// header('content-type: text/html;' . $lng['charset']);
// http://modxcms.com/forums/index.php/topic,23177.msg309172.html#msg309172
// $setlocale = @explode(',', trim(trim($lng['setlocale'], "setlocale("), ')'));
// call_user_func_array('setlocale', array(constant(trim($setlocale[0])), trim($setlocale[1])));

if (IN_MANAGER_MODE != 'true')
    die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
$_t = $this->e2gmod_cfg['_t'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title>Easy 2 Gallery <?php echo E2G_VERSION; ?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $lng['charset']; ?>" />
        <link rel="stylesheet" type="text/css" href="media/style/<?php echo $_t; ?>/style.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo E2G_MODULE_URL; ?>includes/tpl/e2g_mod.css" />
        <script type="text/javascript" src="media/script/tabpane.js"></script>
        <script type="text/javascript">
            function confirmDelete() {
                return (confirm("<?php echo $lng['js_delete_confirm']; ?>"));
            }
            function confirmDeleteFolder() {
                return (confirm("<?php echo $lng['js_delete_folder_confirm']; ?>"));
            }
            function ignoreIPAddress() {
                return (confirm("<?php echo $lng['js_ignore_ip_address_confirm']; ?>"));
            }
            function unignoreIPAddress() {
                return (confirm("<?php echo $lng['js_unignore_ip_address_confirm']; ?>"));
            }
            function addField () {
                var im = document.getElementById("imFields");
                var di = document.createElement("DIV");
                var fi = document.getElementById("firstElt");
                di.innerHTML = '<a href="javascript:;" onclick="this.parentNode.parentNode.removeChild(this.parentNode);" style="color:red;text-decoration:none;"><b style="letter-spacing:4px"> &times; <?php echo $lng['remove']; ?><\/b><\/a>'+fi.innerHTML;
                im.appendChild(di);
                return true;
            }
            // for download preview
            function uimPreview (imSrc) {
                if (!document.images) return FALSE;
                var im = new Image();
                im.src = imSrc;
                var w = im.width;
                var h = im.height;
                if ( w > 161 || h > 161 ) {
                    ratio = w / h;
                    if ( ratio <= 1 ) {
                        h = 161;
                        w = Math.round(161*ratio);
                    } else {
                        w = 161;
                        h = Math.round(161/ratio);
                    }
                }
                var imBox = this.document.getElementById("imBox");
                if ( w == 0 || h == 0 ) {
                    imBox.innerHTML = '<?php echo $lng['uim_preview_err']; ?>';
                } else {
                    imBox.innerHTML = '<img src="'+imSrc+'" width="'+w+'" height="'+h+'" alt="" />';
                }
                return true;
            }
            function selectAll (check_var) {
                for (var i=0; i<document.forms["list"].elements.length; i++) {
                    var e=document.forms["list"].elements[i];
                    if (e.type == "checkbox") e.checked = check_var;
                }
            }
            function submitform(i) {
                if (i==1) {
                    var index=document.forms["topmenu"].newparent.selectedIndex;
                    if (document.forms["topmenu"].newparent.options[index].value != "") {
                        window.location.href= '<?php echo html_entity_decode($index); ?>&pid='+ document.forms["topmenu"].newparent.options[index].value;
                    }
                }
                if (i=='1b') {
                    document.forms["topmenu"].action=
                        "<?php echo html_entity_decode($index); ?>&pid=<?php echo $_POST['newparent']; ?>&page=openexplorer";
                    document.forms["topmenu"].submit();
                }
                if (i==2) {
                    var index=document.forms["topmenu"].opentag.selectedIndex;
                    if (document.forms["topmenu"].opentag.options[index].value != "") {
                        window.location.href= '<?php echo html_entity_decode($index); ?>&page=tag&tag='+ document.forms["topmenu"].opentag.options[index].value;
                    }
                }
                if (i==3) {
                    document.forms["list"].action=
                        "<?php echo html_entity_decode($index) . '&act=delete_checked&pid=' . $parent_id . (!empty($cpath) ? '&path=' . $cpath : ''); ?>";
                    document.forms["list"].submit();
                }
                if (i==4) {
                    document.forms["list"].action=
                        "<?php echo html_entity_decode($index) . '&act=download_checked&pid=' . $parent_id . (!empty($cpath) ? '&path=' . $cpath : ''); ?>";
                    document.forms["list"].submit();
                }
                if (i==5) {
                    document.forms["list"].action=
                        "<?php echo html_entity_decode($index) . '&act=move_checked&pid=' . $parent_id . (!empty($cpath) ? '&path=' . $cpath : ''); ?>";
                    document.forms["list"].submit();
                }
                if (i==6) {
                    document.forms["list"].action=
                        "<?php echo html_entity_decode($index) . '&act=tag_add_checked&pid=' . $parent_id . (!empty($cpath) ? '&path=' . $cpath : ''); ?>";
                    document.forms["list"].submit();
                }
                if (i==7) {
                    document.forms["list"].action=
                        "<?php echo html_entity_decode($index) . '&act=tag_remove_checked&pid=' . $parent_id . (!empty($cpath) ? '&path=' . $cpath : ''); ?>";
                    document.forms["list"].submit();
                }
            }
            function selectAllComments (check_var) {
                for (var i=0; i<document.forms["listComments"].elements.length; i++) {
                    var e=document.forms["listComments"].elements[i];
                    if (e.type == "checkbox") e.checked = check_var;
                }
            }
            function selectAllIgnoreIPs (check_var) {
                for (var i=0; i<document.forms["listIgnoreIPs"].elements.length; i++) {
                    var e=document.forms["listIgnoreIPs"].elements[i];
                    if (e.type == "checkbox") e.checked = check_var;
                }
            }
            function selectAllHiddenComments (check_var) {
                for (var i=0; i<document.forms["listHiddenComments"].elements.length; i++) {
                    var e=document.forms["listHiddenComments"].elements[i];
                    if (e.type == "checkbox") e.checked = check_var;
                }
            }

            function showAllImages() {
                var ele2 = document.getElementsByClassName("imPreview");
                var ele1 = document.getElementById("toggleText");
                var text = document.getElementById("displayText");
                if(ele1.style.display == "block") {
                    ele1.style.display = "none";
                    text.innerHTML = "<span style=\"float: left;width: 1.2em;\">+</span> Show all images";
                    ele2.style.display = "none";
                    for (var i=0; i<document.forms["list"].elements.length; i++) {
                        
                    }
                }
                else {
                    ele1.style.display = "block";
                    text.innerHTML = "<span style=\"float: left;width: 1.2em;\">-</span> Hide all images";
                    ele2.style.display = "block";
                }
            }
            
            // default page preview
            function imPreview (imPath, i) {
                var pElt = this.document.getElementById("rowPreview_"+i);
                if(pElt.style.display == "block") {
                    pElt.style.display = "none";
                }
                else {
                    pElt.style.display = "block";
                }
                pElt.innerHTML = "<img src='<?php echo E2G_MODULE_URL; ?>preview.easy2gallery.php?path="+imPath+"'>";
            }
            // comment preview
            function imPreview2 (imPath, i) {
                var pElt = this.document.getElementById("rowPreview2_"+i);
                if(pElt.style.display == "block") {
                    pElt.style.display = "none";
                }
                else pElt.style.display = "block";
                pElt.innerHTML = "<img src='<?php echo E2G_MODULE_URL; ?>preview.easy2gallery.php?path="+imPath+"'>";
            }
            // ignored comment preview
            function imPreview3 (imPath, i) {
                var pElt = this.document.getElementById("rowPreview3_"+i);
                if(pElt.style.display == "block") {
                    pElt.style.display = "none";
                }
                else pElt.style.display = "block";
                pElt.innerHTML = "<img src='<?php echo E2G_MODULE_URL; ?>preview.easy2gallery.php?path="+imPath+"'>";
            }
            // file edit page preview
            function imPreview4 (imPath) {
                var pElt = this.document.getElementById("pElt4");
                if(pElt.style.display == "block") {
                    pElt.style.display = "none";
                }
                else pElt.style.display = "block";
                pElt.innerHTML = "<img src='<?php echo E2G_MODULE_URL; ?>preview.easy2gallery.php?path="+imPath+"'>";
            }
            // file comment page preview
            function imPreview5 (imPath) {
                var pElt = this.document.getElementById("pElt5");
                pElt.innerHTML = "<img src='<?php echo E2G_MODULE_URL; ?>preview.easy2gallery.php?path="+imPath+"'>";
            }
            // tag page preview
            function imPreview6 (imPath, i) {
                var pElt = this.document.getElementById("rowPreview6_"+i);
                if(pElt.style.display == "block") {
                    pElt.style.display = "none";
                }
                else pElt.style.display = "block";
                pElt.innerHTML = "<img src='<?php echo E2G_MODULE_URL; ?>preview.easy2gallery.php?path="+imPath+"'>";
            }

            var tpResources;
            function showTab( sName ) {
                if (typeof tpResources != "undefined" ) {
                    switch ( sName ) {
                        case "file":
                            tpResources.setSelectedIndex( 0 );
                            break;
                        case "commentsmgr":
                            tpResources.setSelectedIndex( 1 );
                            break;
                        case "imageupload":
                            tpResources.setSelectedIndex( 2 );
                            break;
                        case "config":
                            tpResources.setSelectedIndex( 3 );
                            break;
                        case "help":
                            tpResources.setSelectedIndex( 4 );
                            break;
                    }
                }
            }
        </script>
        <script type="text/javascript" src="<?php echo MODX_BASE_URL . $e2g['tinymcefolder']; ?>/tiny_mce.js"></script>
        <script type="text/javascript">
            tinyMCE.init({
                mode : "textareas",
                theme : "advanced",
                editor_selector : "mceEditor",
                theme_advanced_toolbar_location : "top",
                theme_advanced_toolbar_align : "left",
                theme_advanced_statusbar_location : "bottom",
                theme_advanced_resizing : true,
                content_css : "<?php echo E2G_MODULE_URL; ?>includes/tpl/e2g_mod.css"
            });
        </script>
    </head>
    <body>
        <?php
        $suc = $err = '';
        if (count($_SESSION['easy2err']) > 0) {
            $err = '<p class="warning" style="padding-left: 10px;">' . implode('<br />', $_SESSION['easy2err']) . '</p>';
            $_SESSION['easy2err'] = array();
        }
        if (count($_SESSION['easy2suc']) > 0) {
            $suc = '<p class="success" style="padding-left: 10px;">' . implode('<br />', $_SESSION['easy2suc']) . '</p>';
            $_SESSION['easy2suc'] = array();
        }
        ?>
        <p><?php echo $err . $suc; ?></p>
        <div class="sectionHeader">Easy 2 Gallery <?php echo E2G_VERSION; ?></div>
        <div class="sectionBody">
            <div class="tab-pane" id="easy2Pane">
                <script type="text/javascript">
                    tpResources = new WebFXTabPane(document.getElementById('easy2Pane'));
                </script>
                <?php include_once 'pane.files.inc.php'; ?>
                <?php include_once 'pane.imageupload.inc.php'; ?>
                <?php include_once 'pane.comments.inc.php'; ?>
                <?php include_once 'pane.config.inc.php'; ?>
                <?php include_once 'pane.help.inc.php'; ?>
            </div>
        </div>
    </body>
</html>
