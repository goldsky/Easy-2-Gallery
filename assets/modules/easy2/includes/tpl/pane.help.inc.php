<?php
if (IN_MANAGER_MODE != 'true') die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
?>
<div class="tab-page" id="tabHelp">
    <span class="tab"><?php echo $lng['help']; ?></span>
    <script type="text/javascript">
        tpResources.addTabPage( document.getElementById( 'tabHelp' ) );
    </script>
    <div class="tab-pane" id="tabHelpPane">
        <script type="text/javascript">
            tpResources2 = new WebFXTabPane( document.getElementById( 'tabHelpPane' ) );
        </script>
        <div class="tab-page" id="tabHelpParameters">
            <h2 class="tab"><?php echo $lng['paramhelptitle']; ?></h2>
            <script type="text/javascript">
                tpResources2.addTabPage( document.getElementById( 'tabHelpParameters' ) );
            </script>
            <?php echo htmlspecialchars_decode($lng['paramhelpcontent'], ENT_QUOTES); ?>
        </div>
        <div class="tab-page" id="tabHelpTemplates">
            <h2 class="tab"><?php echo $lng['tplhelptitle']; ?></h2>
            <script type="text/javascript">
                tpResources2.addTabPage( document.getElementById( 'tabHelpTemplates' ) );
            </script>
            <?php echo htmlspecialchars_decode($lng['tplhelpcontent'], ENT_QUOTES); ?>
        </div>
        <div class="tab-page" id="tabHelpTranslation">
            <h2 class="tab"><?php echo ucfirst($lng['translation']); ?></h2>
            <script type="text/javascript">
                tpResources2.addTabPage( document.getElementById( 'tabHelpTranslation' ) );
            </script>
            <table>
                <tr>
                    <td>
                        <p><b>Edit translation (compares to English):</b></p>
                        <ul>
                            <?php
                            $langdir= E2G_MODULE_PATH .'includes/langs/';
                            if (is_dir($langdir)) {
                                if ($dh = opendir($langdir)) {
                                    while (($file = readdir($dh)) !== false) {
                                        $ext = end(@explode('.', $file));
                                        if ($ext!='php') continue;
//                                echo '<li><a href="'.$index.'&page=edit_lang&lang='.$xpldfilename[0].'" >'.$file.'</a></li>';
                                        echo '<li><a href="'.$index.'&page=edit_lang&langfile='.$file.'" >'.$file.'</a></li>';
                                    }
                                    closedir($dh);
                                }
                            }

                            ?>
                        </ul>
                    </td>
                    <td valign="top" style="border-left: 1px dotted #000; padding-left: 5px;">
                        <p><b>Notes:</b></p>
                        <ul>
                            <li>Javascript's notification is marked with "js_" key or <span style="background-color: #FFC;">yellow background</span>.
                                Thus, the tag for a new line is <b>"\n"</b>, not "&lt;br /&gt;"</li>
                            <li>All input will be filtered by <b>htmlspecialchars($string,ENT_QUOTES)</b></li>
                            <li>To add a new language file, simply create a blank file with the specified suffix names:
                                <ul>
                                    <li>[language] <b>.comments.php</b> <i>(for comment box)</i></li>
                                    <li>[language] <b>.inc.php</b> <i>(for global settings)</i></li>
                                    <li>[language] <b>.inst.inc.php</b> <i>(for installation)</i></li>
                                </ul>
                                inside the <b>includes/langs/</b> folder, then start making the translation from this form.
                            </li>
                        </ul>
                    </td>
                </tr>
            </table>

            <p>&nbsp;</p>
            <?php if ($_GET['page']=='edit_lang') {
                include_once E2G_MODULE_PATH . 'includes/tpl/page.edit_lang.inc.php';
            }?>
            <p>&nbsp;</p>
        </div>
        <div class="tab-page" id="tabHelpInfo">
            <h2 class="tab"><?php echo $lng['moreinfotitle']; ?></h2>
            <script type="text/javascript">
                tpResources2.addTabPage( document.getElementById( 'tabHelpInfo' ) );
            </script>
            <?php echo htmlspecialchars_decode($lng['moreinfocontent'], ENT_QUOTES); ?>
        </div>
    </div>
</div>