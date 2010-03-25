<?php
if (IN_MANAGER_MODE != 'true') die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
$_t = $this->e2gmod_cl['_t']
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title>Easy 2 Gallery <?php echo E2G_VERSION; ?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <link rel="stylesheet" type="text/css" href="media/style/<?php echo $_t; ?>/style.css" />
        <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $lng['charset']; ?>" />
        <script type="text/javascript" src="media/script/tabpane.js"></script>
        <script type="text/javascript">
            function confirmDelete() {
                return (confirm("<?php echo $lng['delete_confirm']; ?>"));
            }
            function confirmDeleteFolder() {
                return (confirm("<?php echo $lng['delete_folder_confirm']; ?>"));
            }
            function addField () {
                var im = document.getElementById("imFields");
                var di = document.createElement("DIV");
                var fi = document.getElementById("firstElt");
                di.innerHTML = '<a href="#" onclick="this.parentNode.parentNode.removeChild(this.parentNode);" style="color:red;text-decoration:none;"><b style="letter-spacing:4px"> &times; <?php echo $lng['remove_field_btn']; ?></b></a>'+fi.innerHTML;
                im.appendChild(di);
                return TRUE;
            }
            function newDir (a) {
                var f;
                f=window.prompt("<?php echo $lng['enter_dirname']; ?>:","");
                if (f) a.href+=f;
                else return false;
            }
            function editDir (a) {
                var f;
                f=window.prompt("<?php echo $lng['enter_new_dirname']; ?>:","");
                if (f) a.href+=f;
                else return false;
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
            function imPreview (imPath) {
                var pElt = this.document.getElementById("pElt");
                pElt.innerHTML = "<img src='<?php echo E2G_MODULE_URL; ?>preview.easy2gallery.php?path="+imPath+"'>";
            }
        </script>
    </head>
    <body>
        <p><?php echo $err.$suc; ?>&nbsp;</p>
        <div class="sectionHeader">Easy 2 Gallery <?php echo E2G_VERSION; ?></div>
        <div class="sectionBody">
            <div class="tab-pane" id="easy2Pane">
                <script type="text/javascript">
                    tpResources = new WebFXTabPane(document.getElementById('easy2Pane'));
                </script>
                <?php include_once 'pane.files.inc.php';?>
                <?php include_once 'pane.imageupload.inc.php';?>
                <?php include_once 'pane.config.inc.php';?>
                <?php include_once 'pane.help.inc.php';?>
            </div>
        </div>
    </body>
</html>
