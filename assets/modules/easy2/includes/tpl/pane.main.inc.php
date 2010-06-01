<?php
header('content-type: text/html; charset=utf-8');
if (IN_MANAGER_MODE != 'true') die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
$_t = $this->e2gmod_cfg['_t'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
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
                di.innerHTML = '<a href="#" onclick="this.parentNode.parentNode.removeChild(this.parentNode);" style="color:red;text-decoration:none;"><b style="letter-spacing:4px"> &times; <?php echo $lng['remove_field_btn']; ?></b></a>'+fi.innerHTML;
                im.appendChild(di);
                return TRUE;
            }
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
                    imBox.innerHTML = '<img src="'+imSrc+'" width="'+w+'" height="'+h+'" />';
                }
                return TRUE;
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
                        window.location.href= '<?php echo $index; ?>&pid='+ document.forms["topmenu"].newparent.options[index].value;
                    }
                }
                if (i=='1b') {
                    document.forms["topmenu"].action=
                    "<?php echo $index; ?>&pid=<?php echo $_POST['newparent']; ?>&page=openexplorer";
                    document.forms["topmenu"].submit();
                }
                if (i==2) {
                    document.forms["list"].action=
                    "<?php echo $index.'&act=delete_checked&pid='.$parent_id.(!empty($cpath)?'&path='.$cpath:'');?>";
                    document.forms["list"].submit();
                }
                if (i==3) {
                    document.forms["list"].action=
                    "<?php echo $index.'&act=download_checked&pid='.$parent_id.(!empty($cpath)?'&path='.$cpath:'');?>";
                    document.forms["list"].submit();
                }
                if (i==4) {
                    document.forms["list"].action=
                    "<?php echo $index.'&act=move_checked&pid='.$parent_id.(!empty($cpath)?'&path='.$cpath:'');?>";
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
            function imPreview (imPath) {
                var pElt = this.document.getElementById("pElt");
                pElt.innerHTML = "<img src='<?php echo E2G_MODULE_URL; ?>preview.easy2gallery.php?path="+imPath+"'>";
            }
            function imPreview2 (imPath) {
                var pElt = this.document.getElementById("pElt2");
                pElt.innerHTML = "<img src='<?php echo E2G_MODULE_URL; ?>preview.easy2gallery.php?path="+imPath+"'>";
            }
            function imPreview3 (imPath) {
                var pElt = this.document.getElementById("pElt3");
                pElt.innerHTML = "<img src='<?php echo E2G_MODULE_URL; ?>preview.easy2gallery.php?path="+imPath+"'>";
            }
            function imPreview4 (imPath) {
                var pElt = this.document.getElementById("pElt4");
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
        <script type="text/javascript" src="<?php echo MODX_BASE_URL;?>assets/plugins/<?php echo $e2g['tinymcefolder'];?>/tiny_mce.js"></script>
        <script type="text/javascript">
        tinyMCE.init({
            mode : "textareas",
            theme : "advanced",
            editor_selector : "mceAdvanced",
            theme_advanced_toolbar_location : "top",
            content_css : "<?php echo E2G_MODULE_URL; ?>includes/tpl/e2g_mod.css"
        });
        </script>
    </head>
    <body>
<?php
        $suc = $err = '';
        if (count($_SESSION['easy2err']) > 0) {
            $err = '<p class="warning" style="padding-left: 10px;">'.implode('<br />', $_SESSION['easy2err']).'</p>';
            $_SESSION['easy2err'] = array();
        }
        if (count($_SESSION['easy2suc']) > 0) {
            $suc = '<p class="success" style="padding-left: 10px;">'.implode('<br />', $_SESSION['easy2suc']).'</p>';
            $_SESSION['easy2suc'] = array();
        }
?>
        <p><?php echo $err.$suc; ?></p>
        <div class="sectionHeader">Easy 2 Gallery <?php echo E2G_VERSION; ?></div>
        <div class="sectionBody">
            <div class="tab-pane" id="easy2Pane">
                <script type="text/javascript">
                    tpResources = new WebFXTabPane(document.getElementById('easy2Pane'));
                </script>
                <?php include_once 'pane.files.inc.php';?>
                <?php include_once 'pane.imageupload.inc.php';?>
                <?php include_once 'pane.comments.inc.php';?>
                <?php include_once 'pane.config.inc.php';?>
                <?php include_once 'pane.help.inc.php';?>
            </div>
        </div>
    </body>
</html>
