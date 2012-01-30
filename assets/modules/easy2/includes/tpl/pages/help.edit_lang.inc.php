<?php
if ($this->sanitizedGets['langfile']) {
    $xpldFileName = @explode('.', $this->sanitizedGets['langfile']);
    $fileFirstName = $xpldFileName[0];
    $fileLastName = ltrim($this->sanitizedGets['langfile'], $fileFirstName);
}

// prepare the english file
if (!file_exists(realpath(E2G_MODULE_PATH . 'includes/langs/english' . $fileLastName))) {
    $_SESSION['easy2err'][] = 'No english file which is referred to this language file.';
} else {
    include (E2G_MODULE_PATH . 'includes/langs/english' . $fileLastName);
    if (isset($lngi)) {
        $langArrays = $lngi;
    } else {
        $langArrays = $e2g_lang['english'];
    }
    foreach ($langArrays as $keyEng => $valEng) {
        $engs[$keyEng]['key'] = $keyEng;
        $engs[$keyEng]['value'] = $valEng;
    }

    // prepare another language file
    include (E2G_MODULE_PATH . 'includes/langs/' . $this->sanitizedGets['langfile']);
    if (isset($lngi)) {
        $langs = $lngi;
    } else {
        $langs = $e2g_lang[$fileFirstName];
    }
    foreach ($langs as $keyOther => $valOther) {
        $others[$keyOther]['key'] = $keyOther;
        $others[$keyOther]['value'] = $valOther;
    }

?>

    <h2 style="text-align: right;"><?php echo $this->sanitizedGets['langfile']; ?></h2>
    <hr />
    <form action="<?php echo $this->e2gModCfg['index']; ?>&amp;act=save_lang" name="edit_lang" method="post">
        <input type="hidden" name="lang" value="<?php echo $fileFirstName; ?>" />
        <input type="hidden" name="langfile" value="<?php echo $this->sanitizedGets['langfile']; ?>" />
        
        <div>
            <input type="submit" name="submit" value="Save"  />
            <input type="reset" name="reset" value="Reset" />
            <input type="button" name="cancel" value="Cancel" onclick="document.location.href='<?php echo $this->e2gModCfg['index']; ?>'" />
        </div>
        <table width="100%" style="background-color:#eee;">
            <tr>
                <td width="50%">
                    <table cellpadding="2" cellspacing="0" width="100%">
                        <tr>
                            <td style="font-size: large; font-weight: bold;" nowrap="nowrap"><?php echo $this->lng['credit_lang_file']; ?></td>
                            <td style="font-size: large; font-weight: bold;" width="100%">
                                <input type="text" size="" style="width:98%;" name="<?php echo $engs['credit_lang_file']['key']; ?>"
                                       value="<?php echo $others['credit_lang_file']['value']; ?>" />
                            </td>
                        </tr>
                    </table>
                    <table style="border:1px dotted #000;" cellpadding="2" cellspacing="0" width="100%">
                        <tr style="border:1px dotted #000;">
                            <th style="border:1px dotted #000;">Keys</th>
                            <th style="border:1px dotted #000;">English</th>
                            <th style="border:1px dotted #000;width:98%;"><?php echo $this->e2gEncode($fileFirstName, 'ucfirst'); ?></th>
                        </tr>
                    <?php
                    foreach ($engs as $eng) {
                        if ($eng['key'] == 'credit_lang_file_author'
                                || $eng['key'] == 'credit_lang_file_date'
                                || $eng['key'] == 'credit_lang_file_version'
                        ) {
                    ?>
                            <tr>
                                <td style="border:1px dotted #000;" valign="top" nowrap="nowrap"><?php echo $eng['key']; ?></td>
                                <td style="border:1px dotted #000;" valign="top" nowrap="nowrap"><?php echo $eng['value']; ?></td>
                                <td style="border:1px dotted #000;" valign="top">
                                    <input type="text" size="" style="width:98%;" name="<?php echo $engs[$eng['key']]['key']; ?>"
                                           value="<?php echo $others[$eng['key']]['value']; ?>" />
                                </td>
                            </tr>
                    <?php
                        } // if ($eng['key'] == 'credit_lang_file_author' ...
                    } // foreach ($engs as $eng)
                    ?>
                </table>
            </td>
        </tr>
    </table>
    <p>&nbsp;</p>
    <div style="display:block;float: none; ">
        <table style="border:1px dotted #000;" cellpadding="2" cellspacing="0">
            <tr style="border:1px dotted #000;">
                <th style="border:1px dotted #000;" width="20%">Keys</th>
                <th style="border:1px dotted #000;" width="40%">English</th>
                <th style="border:1px dotted #000;" width="40%"><?php echo $this->e2gEncode($fileFirstName, 'ucfirst'); ?></th>
            </tr>
            <?php
                    foreach ($engs as $eng) {
                        if ($eng['key'] != 'credit_lang_file'
                                && $eng['key'] != 'credit_lang_file_author'
                                && $eng['key'] != 'credit_lang_file_date'
                                && $eng['key'] != 'credit_lang_file_version'
                        ) {
                            // to separate the javascript text
                            $jsClass = (strpos($eng['key'], 'js_') !== false ? 'background-color:#FFC;' : '');
            ?>
                            <tr style="border:1px dotted #000;">
                                <td style="border:1px dotted #000;<?php echo $jsClass; ?>" valign="top"><?php echo $eng['key']; ?></td>
                                <td style="border:1px dotted #000;<?php echo $jsClass; ?>" valign="top">
                    <?php
                            echo htmlspecialchars_decode($eng['value'], ENT_QUOTES);
                    ?>
                        </td>
                        <td style="border:1px dotted #000;<?php echo $jsClass; ?>" valign="top">
                    <?php
                            if (strlen($eng['value']) < 60) {
                                echo '<input type="text" size="80" name="' . $engs[$eng['key']]['key'] . '"
                                value="' . $others[$eng['key']]['value'] . '" />';
                            } else {
                                $textAreaHeight = (strlen($eng['value']) / 5);
                                echo '<textarea rows="2" cols="" style="width : 94%;'
                                . ( $textAreaHeight < 50 ? '"' : ' height: ' . $textAreaHeight . 'px;"' )
                                . ( strpos($eng['key'], 'js_') === false ? ' class="mceEditor"' : '' )
                                . ' name="' . $engs[$eng['key']]['key'] . '" >';
                                echo $others[$eng['key']]['value'];
                                echo '</textarea>';
                            }
                    ?>
                        </td>
                    </tr>
            <?php
                        } // if ($eng['key'] != 'setlocale' ...
                    } // foreach ($engs as $eng)
            ?>
                </table>
            </div>
            <div>
                <input type="submit" name="submit" value="Save"  />
                <input type="reset" name="reset" value="Reset" />
                <input type="button" name="cancel" value="Cancel" onclick="document.location.href='<?php echo $this->e2gModCfg['index']; ?>'" />
            </div>
        </form>

<?php
                }