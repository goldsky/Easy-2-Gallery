<?php
// disabled for:
// http://modxcms.com/forums/index.php/topic,23177.msg308887.html#msg308887
// header('content-type: text/html;' . $this->lng['charset']);
// http://modxcms.com/forums/index.php/topic,23177.msg309172.html#msg309172
// $setlocale = @explode(',', trim(trim($this->lng['setlocale'], "setlocale("), ')'));
// call_user_func_array('setlocale', array(constant(trim($setlocale[0])), trim($setlocale[1])));

if (IN_MANAGER_MODE != 'true')
    die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

$filtered = isset($this->sanitizedGets['filter']) ? '&amp;filter=' . $this->sanitizedGets['filter'] : '';

foreach ($this->e2gModCfg['e2gPages'] as $v) {
    $e2gPage[$v['e2gpg']] = $v;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title>Easy 2 Gallery <?php echo E2G_VERSION; ?> | <?php echo $e2gPage[$this->e2gModCfg['e2gpg']]['lng']; ?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $this->lng['charset']; ?>" />
        <link rel="stylesheet" type="text/css" href="media/style/<?php echo $this->e2gModCfg['_t']; ?>/style.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo E2G_MODULE_URL; ?>includes/tpl/css/e2g_mod.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo MODX_SITE_URL; ?>assets/libs/viewers/highslide/highslide.css" />
        <?php echo $this->plugin('OnE2GModHeadCSSScript'); ?>

        <script type="text/javascript" src="media/script/tabpane.js"></script>
        <script type="text/javascript" src="<?php echo E2G_MODULE_URL; ?>includes/tpl/js/e2g_mod.js"></script>
        <script type="text/javascript">
            //<![CDATA[
            function confirmDelete() {
                return (confirm("<?php echo $this->lng['js_delete_confirm']; ?>"));
            }

            function confirmDeleteFolder() {
                return (confirm("<?php echo $this->lng['js_delete_folder_confirm']; ?>"));
            }

            function ignoreIPAddress() {
                return (confirm("<?php echo $this->lng['js_ignore_ip_address_confirm']; ?>"));
            }

            function unignoreIPAddress() {
                return (confirm("<?php echo $this->lng['js_unignore_ip_address_confirm']; ?>"));
            }

            function addField () {
                var im = document.getElementById("imFields");
                var di = document.createElement("DIV");
                var fi = document.getElementById("firstElt");
                di.innerHTML = '<a href="javascript:;" onclick="this.parentNode.parentNode.removeChild(this.parentNode);" style="color:red;text-decoration:none;"><b style="letter-spacing:4px"> &times; <?php echo $this->lng['remove']; ?><\/b><\/a>'+fi.innerHTML;
                im.appendChild(di);
                return true;
            }

            function addSlideshow () {
                var sl = document.getElementById("secondElt");
                var di = document.createElement("DIV");
                var fi = document.getElementById("firstElt");
                di.innerHTML = '<a href="javascript:;" onclick="this.parentNode.parentNode.removeChild(this.parentNode);" style="color:red;text-decoration:none;"><b style="letter-spacing:4px"> &times; <?php echo $this->lng['remove']; ?><\/b><\/a>'+fi.innerHTML;
                sl.appendChild(di);
                return true;
            }

            function addPlugin () {
                var pl = document.getElementById("secondElt");
                var di = document.createElement("DIV");
                var fi = document.getElementById("firstElt");
                di.innerHTML = '<a href="javascript:;" onclick="this.parentNode.parentNode.removeChild(this.parentNode);" style="color:red;text-decoration:none;"><b style="letter-spacing:4px"> &times; <?php echo $this->lng['remove']; ?><\/b><\/a>'+fi.innerHTML;
                pl.appendChild(di);
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
                    var ratio = w / h;
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
                    imBox.innerHTML = '<?php echo $this->lng['uim_preview_err']; ?>';
                } else {
                    imBox.innerHTML = '<img src="'+imSrc+'" width="'+w+'" height="'+h+'" alt="" />';
                }
                return true;
            }

            function submitform(i) {
                if (i==1) {
                    var index=document.forms["topmenu"].newparent.selectedIndex;
                    if (document.forms["topmenu"].newparent.options[index].value != "") {
                        window.location.href= '<?php echo html_entity_decode($this->e2gModCfg['index']); ?>&pid='+ document.forms["topmenu"].newparent.options[index].value;
                    }
                }
                if (i=='1b') {
                    document.forms["topmenu"].action=
                        "<?php echo html_entity_decode($this->e2gModCfg['index']); ?>&pid=<?php echo $_POST['newparent']; ?>&page=openexplorer";
                    document.forms["topmenu"].submit();
                }
                if (i==2) {
                    var index=document.forms["topmenu"].opentag.selectedIndex;
                    if (document.forms["topmenu"].opentag.options[index].value != "") {
                        window.location.href= '<?php echo html_entity_decode($this->e2gModCfg['index']); ?>&tag='+ document.forms["topmenu"].opentag.options[index].value;
                    }
                }
                if (i==3) {
                    document.forms["list"].action=
                        "<?php echo html_entity_decode($this->e2gModCfg['index']) . '&act=show_checked&pid=' . $this->e2gModCfg['parent_id'] . (!empty($cpath) ? '&path=' . $cpath : ''); ?>";
                    document.forms["list"].submit();
                }
                if (i==4) {
                    document.forms["list"].action=
                        "<?php echo html_entity_decode($this->e2gModCfg['index']) . '&act=hide_checked&pid=' . $this->e2gModCfg['parent_id'] . (!empty($cpath) ? '&path=' . $cpath : ''); ?>";
                    document.forms["list"].submit();
                }
                if (i==5) {
                    document.forms["list"].action=
                        "<?php echo html_entity_decode($this->e2gModCfg['index']) . '&act=delete_checked&pid=' . $this->e2gModCfg['parent_id'] . (!empty($cpath) ? '&path=' . $cpath : ''); ?>";
                    document.forms["list"].submit();
                }
                if (i==6) {
                    document.forms["list"].action=
                        "<?php echo html_entity_decode($this->e2gModCfg['index']) . '&act=download_checked&pid=' . $this->e2gModCfg['parent_id'] . (!empty($cpath) ? '&path=' . $cpath : ''); ?>";
                    document.forms["list"].submit();
                }
                if (i==7) {
                    document.forms["list"].action=
                        "<?php echo html_entity_decode($this->e2gModCfg['index']) . '&act=move_checked&pid=' . $this->e2gModCfg['parent_id'] . (!empty($cpath) ? '&path=' . $cpath : ''); ?>";
                    document.forms["list"].submit();
                }
                if (i==8) {
                    document.forms["list"].action=
                        "<?php echo html_entity_decode($this->e2gModCfg['index']) . '&act=tag_add_checked&pid=' . $this->e2gModCfg['parent_id'] . (!empty($cpath) ? '&path=' . $cpath : ''); ?>";
                    document.forms["list"].submit();
                }
                if (i==9) {
                    document.forms["list"].action=
                        "<?php echo html_entity_decode($this->e2gModCfg['index']) . '&act=tag_remove_checked&pid=' . $this->e2gModCfg['parent_id'] . (!empty($cpath) ? '&path=' . $cpath : ''); ?>";
                    document.forms["list"].submit();
                }
            }

            function submitcomment(i) {
                if (i==1) {
                    document.forms["listComments"].action=
                        "<?php echo html_entity_decode($this->e2gModCfg['index']) . '&act=com_list_actions'; ?>";
                    document.forms["listComments"].submit();
                }
                if (i==2) {
                    document.forms["listHiddenComments"].action=
                        "<?php echo html_entity_decode($this->e2gModCfg['index']) . '&act=com_list_actions'; ?>";
                    document.forms["listHiddenComments"].submit();
                }
                if (i==3) {
                    document.forms["fileComments"].action=
                        "<?php echo html_entity_decode($this->e2gModCfg['index']) . '&act=com_list_actions'; ?>";
                    document.forms["fileComments"].submit();
                }
            }

            function savecomment(i) {
                if (i==1) {
                    document.forms["listComments"].action=
                        "<?php echo html_entity_decode($this->e2gModCfg['index'] . $filtered) . '&act=com_save'; ?>";
                    document.forms["listComments"].submit();
                }
                if (i==2) {
                    document.forms["listHiddenComments"].action=
                        "<?php echo html_entity_decode($this->e2gModCfg['index']) . '&act=com_save'; ?>";
                    document.forms["listHiddenComments"].submit();
                }
                if (i==3) {
                    document.forms["fileComments"].action=
                        "<?php echo html_entity_decode($this->e2gModCfg['index']) . '&page=comments&file_id=' . $this->sanitizedGets['file_id'] . '&pid=' . $this->sanitizedGets['pid'] . '&act=com_save'; ?>";
                    document.forms["fileComments"].submit();
                }
            }

            function xhrRequest() {
                var xhr;
                try {
                    xhr = new XMLHttpRequest();
                } catch(e) {
                    var xmlHttpVersion = new Array( "MSXML2.xhr.6.0",
                    "MSXML2.XMLHTTP.5.0",
                    "MSXML2.XMLHTTP.4.0",
                    "MSXML2.XMLHTTP.3.0",
                    "MSXML2.XMLHTTP",
                    "Microsoft.XMLHTTP"
                );
                    for (var i=0; i=xmlHttpVersion.length && !xhr; i++) {
                        try {
                            xhr = new ActiveXObject(xmlHttpVersion[i]);
                        } catch (e) {}
                    }
                }
                if (!xhr)
                    alert("Error creating the XMLHttpRequestObject.");
                else
                    return xhr;
            }

            function countFiles(path, pid) {
                var xhr = xhrRequest();
                var container = document.getElementById("countfiles_"+pid);
                var oldcontainer = document.getElementById("countfileslink_"+pid);
                var url = "<?php echo E2G_MODULE_URL; ?>includes/controllers/module.countfiles.php?";
                url += "&path="+path;
                url += "&pid="+pid;
                if (xhr && container) {
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState == 4 && xhr.status == 200) {
                            container.innerHTML = xhr.responseText;
                            container.removeChild(oldcontainer);
                        } else {
                            container.innerHTML = " <img src=\"<?php echo E2G_MODULE_URL; ?>includes\/tpl\/icons\/preloader10x10.gif\" \/>";
                        }
                    }
                    xhr.open("GET", url, true);
                    xhr.send(null);
                } else {
                    alert ("Your browser does not support HTTP Request!");
                }
            }

            function synchro(path, pid, uid) {
                var xhr = xhrRequest();
                var report = document.getElementById("report");
                var container = document.getElementById("thumbnail");
                if (!container) {
                    var container = document.getElementById("grid");
                }
                var url = "<?php echo E2G_MODULE_URL; ?>includes/controllers/module.synchro.php?";
                url += "a=<?php echo $this->sanitizedGets['a']; ?>&id=<?php echo $this->sanitizedGets['id']; ?>&e2gpg=<?php echo $this->sanitizedGets['e2gpg']; ?>&path="+path;
                url += "<?php echo isset($this->sanitizedGets['tag']) ? '&tag=' . $this->sanitizedGets['tag'] : ''; ?>";
                url += "&pid="+pid;
                url += "&uid="+uid;
                if (xhr && container) {
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState == 4 && xhr.status == 200) {
                            window.location.href='<?php echo htmlspecialchars_decode($this->e2gModCfg['index']); ?>&act=synchro';
                            report.innerHTML = xhr.responseText;
                        } else {
                            container.innerHTML = "<img src=\"<?php echo E2G_MODULE_URL; ?>includes\/tpl\/icons\/preloader-file.gif\" \/>";
                        }
                    }
                    xhr.open("GET", url, true);
                    xhr.send(null);
                } else {
                    alert ("Your browser does not support HTTP Request!");
                }
            }

            function viewDefaultThumbnails (path, pid, procFile) {
                var xhr = xhrRequest();
                var container = document.getElementById("thumbnail");
                if (procFile=='rescanhd')
                    var url = "<?php echo E2G_MODULE_URL; ?>includes/controllers/module.gallery.rescan.thumb.php?";
                else
                    var url = "<?php echo E2G_MODULE_URL; ?>includes/controllers/module.gallery.default.thumb.php?";
                url += "a=<?php echo $this->sanitizedGets['a']; ?>&id=<?php echo $this->sanitizedGets['id']; ?>&e2gpg=<?php echo $this->sanitizedGets['e2gpg']; ?>&path="+path;
                url += "&pid="+pid;
                if (xhr && container) {
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState == 4 && xhr.status == 200) {
                            container.innerHTML = xhr.responseText;
                        } else {
                            container.innerHTML = "<img src=\"<?php echo E2G_MODULE_URL; ?>includes\/tpl\/icons\/preloader-file.gif\" \/>";
                        }
                    }
                    xhr.open("GET", url, true);
                    xhr.send(null);
                } else {
                    alert ("Your browser does not support HTTP Request!");
                }
            }

            function viewDefaultGrid (path, pid, procFile) {
                var xhr = xhrRequest();
                var container = document.getElementById("grid");
                if (procFile=='rescanhd')
                    var url = "<?php echo E2G_MODULE_URL; ?>includes/controllers/module.gallery.rescan.grid.php?";
                else
                    var url = "<?php echo E2G_MODULE_URL; ?>includes/controllers/module.gallery.default.grid.php?";
                url += "a=<?php echo $this->sanitizedGets['a']; ?>&id=<?php echo $this->sanitizedGets['id']; ?>&e2gpg=<?php echo $this->sanitizedGets['e2gpg']; ?>&path="+path;
                url += "&pid="+pid;
                if (xhr && container) {
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState == 4 && xhr.status == 200) {
                            container.innerHTML = xhr.responseText;
                        } else {
                            container.innerHTML = "<img src=\"<?php echo E2G_MODULE_URL; ?>includes\/tpl\/icons\/preloader-file.gif\" \/>";
                        }
                    }
                    xhr.open("GET", url, true);
                    xhr.send(null);
                } else {
                    alert ("Your browser does not support HTTP Request!");
                }
            }

            function viewTagThumbnails (path, tag) {
                var xhr = xhrRequest();
                var container = document.getElementById("thumbnail");
                var url = "<?php echo E2G_MODULE_URL; ?>includes/controllers/module.gallery.tag.thumb.php?";
                url += "a=<?php echo $this->sanitizedGets['a']; ?>&id=<?php echo $this->sanitizedGets['id']; ?>&e2gpg=<?php echo $this->sanitizedGets['e2gpg']; ?>";
                url += "&path="+path+"&tag="+tag;
                if (xhr && container) {
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState == 4 && xhr.status == 200) {
                            container.innerHTML = xhr.responseText;
                        } else {
                            container.innerHTML = "<img src=\"<?php echo E2G_MODULE_URL; ?>includes\/tpl\/icons\/preloader-file.gif\" \/>";
                        }
                    }
                    xhr.open("GET", url, true);
                    xhr.send(null);
                } else {
                    alert ("Your browser does not support HTTP Request!");
                }
            }

            function viewTagGrid (path, tag) {
                var xhr = xhrRequest();
                var container = document.getElementById("list");
                var url = "<?php echo E2G_MODULE_URL; ?>includes/controllers/module.gallery.tag.grid.php?";
                url += "a=<?php echo $this->sanitizedGets['a']; ?>&id=<?php echo $this->sanitizedGets['id']; ?>&e2gpg=<?php echo $this->sanitizedGets['e2gpg']; ?>";
                url += "&path="+path+"&tag="+tag;
                if (xhr && container) {
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState == 4 && xhr.status == 200) {
                            container.innerHTML = xhr.responseText;
                        } else {
                            container.innerHTML = "<img src=\"<?php echo E2G_MODULE_URL; ?>includes\/tpl\/icons\/preloader-file.gif\" \/>";
                        }
                    }
                    xhr.open("GET", url, true);
                    xhr.send(null);
                } else {
                    alert ("Your browser does not support HTTP Request!");
                }
            }

            // default page preview
            function imPreview (imPath, i) {
                var pElt = this.document.getElementById("rowPreview_"+i);
                if(pElt.style.display == "block") {
                    pElt.style.display = "none";
                } else {
                    pElt.style.display = "block";
                    pElt.innerHTML = "<a href=\"<?php echo MODX_SITE_URL; ?>"+imPath+"\" class=\"highslide\" onclick=\"return hs.expand(this, { objectType: 'ajax'})\"><img src='<?php echo E2G_MODULE_URL; ?>preview.easy2gallery.php?path="+imPath+"' alt=\"\"><\/a>";
                }
            }

            // comment preview
            function imPreview2 (imPath, i) {
                var pElt = this.document.getElementById("rowPreview2_"+i);
                if(pElt.style.display == "block") {
                    pElt.style.display = "none";
                } else {
                    pElt.style.display = "block";
                    pElt.innerHTML = "<a href=\"<?php echo MODX_SITE_URL; ?>"+imPath+"\" class=\"highslide\" onclick=\"return hs.expand(this)\"><img src='<?php echo E2G_MODULE_URL; ?>preview.easy2gallery.php?path="+imPath+"' alt=\"\"><\/a>";
                }
            }

            // ignored comment preview
            function imPreview3 (imPath, i) {
                var pElt = this.document.getElementById("rowPreview3_"+i);
                if(pElt.style.display == "block") {
                    pElt.style.display = "none";
                } else {
                    pElt.style.display = "block";
                    pElt.innerHTML = "<a href=\"<?php echo MODX_SITE_URL; ?>"+imPath+"\" class=\"highslide\" onclick=\"return hs.expand(this)\"><img src='<?php echo E2G_MODULE_URL; ?>preview.easy2gallery.php?path="+imPath+"' alt=\"\"><\/a>";
                }
            }

            // file edit page preview
            function imPreview4 (imPath) {
                var pElt = this.document.getElementById("pElt4");
                if(pElt.style.display == "block") {
                    pElt.style.display = "none";
                } else {
                    pElt.style.display = "block";
                    pElt.innerHTML = "<a href=\"<?php echo MODX_SITE_URL; ?>"+imPath+"\" class=\"highslide\" onclick=\"return hs.expand(this)\"><img src='<?php echo E2G_MODULE_URL; ?>preview.easy2gallery.php?path="+imPath+"' alt=\"\"><\/a>";
                }
            }

            // file comment page preview
            function imPreview5 (imPath) {
                var pElt = this.document.getElementById("pElt5");
                if(pElt.style.display == "block") {
                    pElt.style.display = "none";
                } else {
                    pElt.style.display = "block";
                    pElt.innerHTML = "<a href=\"<?php echo MODX_SITE_URL; ?>"+imPath+"\" class=\"highslide\" onclick=\"return hs.expand(this)\"><img src='<?php echo E2G_MODULE_URL; ?>preview.easy2gallery.php?path="+imPath+"' alt=\"\"><\/a>";
                }
            }

            // tag page preview
            function imPreview6 (imPath, i) {
                var pElt = this.document.getElementById("rowPreview6_"+i);
                if(pElt.style.display == "block") {
                    pElt.style.display = "none";
                } else {
                    pElt.style.display = "block";
                    pElt.innerHTML = "<img src='<?php echo E2G_MODULE_URL; ?>preview.easy2gallery.php?path="+imPath+"' alt=\"\">";
                }
            }
            //]]>
        </script>
        <script type="text/javascript" src="<?php echo MODX_SITE_URL . $this->e2g['tinymcefolder']; ?>/tiny_mce.js"></script>
        <script type="text/javascript">
            tinyMCE.init({
                mode : "textareas",
                theme : "advanced",
                editor_selector : "mceEditor",
                theme_advanced_toolbar_location : "top",
                theme_advanced_toolbar_align : "left",
                theme_advanced_statusbar_location : "bottom",
                theme_advanced_resizing : true,
                content_css : "<?php echo E2G_MODULE_URL; ?>includes/tpl/css/e2g_mod.css"
            });
        </script>
        <script type="text/javascript" src="<?php echo MODX_SITE_URL; ?>assets/libs/viewers/highslide/highslide-full.js"></script>
        <script type="text/javascript">
            hs.graphicsDir = '<?php echo MODX_SITE_URL; ?>assets/libs/viewers/highslide/graphics/';
            hs.showCredits = false;
            hs.outlineType = "rounded-white";
            hs.allowSizeReduction = false;
            hs.captionEval = "this.a.title";
            hs.align = "center";
            hs.blockRightClick = true;
            hs.dimmingOpacity = 0.75;
        </script>
        <?php echo $this->plugin('OnE2GModHeadJSScript'); ?>

        <?php echo $this->plugin('OnE2GModHeadScript'); ?>

    </head>
    <body>
        <?php
        $suc = $err = '';
        $count_suc = count($_SESSION['easy2suc']);
        $count_err = count($_SESSION['easy2err']);
        if ($count_err > 0) {
            $err = '<div class="warning" style="padding-left: 10px;">' . implode('<br />', $_SESSION['easy2err']) . '</div>';
            $_SESSION['easy2err'] = array();
        }
        if ($count_suc > 0) {
            $suc = '<div class="success" style="padding-left: 10px;">' . implode('<br />', $_SESSION['easy2suc']) . '</div>';
            $_SESSION['easy2suc'] = array();
        }
        if (($count_suc + $count_err) == 0)
            $suc = '&nbsp;';

        $this->_checkConfigCompletion();

        if (empty($_SESSION['e2gMgr']))
            $this->_loadE2gMgrSessions();

        $userPermissions = $_SESSION['e2gMgr']['permissions'];
        $userRole = $_SESSION['e2gMgr']['role'];
        $userPermissionsArray = @explode(',', $userPermissions);
        ?>
        <div id="report"><?php echo $err . $suc; ?></div>
        <div class="sectionHeader">
            <span>Easy 2 Gallery <?php echo E2G_VERSION; ?></span>
            <span class="navigation">
                <span class="navigationTitle">Menu</span>
                <span class="navigationTree">
                    <?php
                    foreach ($this->e2gModCfg['e2gPages'] as $k => $v) {
                        // $userRole == '1' is a Supreme Administrator role
                        if ($userRole == '1'
                                || in_array($v['access'], $userPermissionsArray)
                                || $v['title'] == 'dashboard'
                        ) {
                    ?>
                            <span class="navigationBranch">
                        <?php if ($this->e2gModCfg['e2gpg'] != $v['e2gpg']) {
                        ?>
                                <a href="<?php echo $v['link']; ?>">
                            <?php } ?>
                            <span class="navItem<?php echo (($this->e2gModCfg['e2gpg'] == $v['e2gpg']) ? ' active' : ''); ?>">
                                <span class="navTitle"><?php echo $v['lng']; ?></span>
                                <span class="navIcon"><?php echo $v['icon']; ?></span>
                            </span>
                            <?php if ($this->e2gModCfg['e2gpg'] != $v['e2gpg']) {
                            ?>
                            </a>
                        <?php } ?>
                        </span>
                    <?php
                        }
                    }
                    ?>
                </span>
            </span>
        </div>
        <div class="sectionBody">
            <div class="tab-pane" id="easy2Pane">
                <script type="text/javascript">
                    var tpEasy2 = new WebFXTabPane(document.getElementById('easy2Pane'));
                </script>
                <?php
                    // $userRole == '1' is a Supreme Administrator role
                    if ($userRole == '1'
                            || in_array($e2gPage[$this->e2gModCfg['e2gpg']]['access'], $userPermissionsArray)
                            || $e2gPage[$this->e2gModCfg['e2gpg']]['title'] == 'dashboard'
                    ) {
                ?>
                        <div class="pageTitle"><span><?php echo $e2gPage[$this->e2gModCfg['e2gpg']]['lng']; ?></span></div>
                <?php
                    }
                ?>
                    <div style="clear:both;"></div>
                <?php
                    // $userRole == '1' is a Supreme Administrator role
                    if ($userRole == '1'
                            || in_array($e2gPage[$this->e2gModCfg['e2gpg']]['access'], $userPermissionsArray)
                            || $e2gPage[$this->e2gModCfg['e2gpg']]['title'] == 'dashboard'
                    ) {
                        include_once $e2gPage[$this->e2gModCfg['e2gpg']]['file'];
                    }
                ?>
                </div>
            </div>
            <span class="e2g_grayed"><?php echo $this->_echoMemoryUsage(); ?></span>
        <p>&nbsp;</p>
    </body>
</html>