<?php
if ($_GET['langfile']) {
    $xpldfilename = @explode('.', $_GET['langfile']);
    $filefirstname = $xpldfilename[0];
    $filelastname = ltrim($_GET['langfile'],$filefirstname);
}

// prepare the english file
if (!file_exists(E2G_MODULE_PATH . 'includes/langs/english'.$filelastname)) {
    $_SESSION['easy2err'][] = 'No english file which is referred to this language file.';
}
else {
    include (E2G_MODULE_PATH . 'includes/langs/english'.$filelastname);
    if (isset($lngi)) {
        $langarrays = $lngi;
    }
    else {
        $langarrays = $e2g_lang['english'];
    }
    foreach ($langarrays as $key_eng => $val_eng) {
        $engs[$key_eng]['key'] = $key_eng;
        $engs[$key_eng]['value'] = $val_eng;
    }

    // prepare another language file
    include (E2G_MODULE_PATH . 'includes/langs/'.$_GET['langfile']);
    if (isset($lngi)) {
        $langs = $lngi;
    } else {
        $langs = $e2g_lang[$filefirstname];
    }
    foreach ($langs as $key_other => $val_other) {
        $others[$key_other]['key'] = $key_other;
        $others[$key_other]['value'] = $val_other;
    }

    ?>
<h2 style="text-align: right;"><?php echo $_GET['langfile'];?></h2>
<hr />
<form action="<?php echo $index.'&act=save_lang&lang='.$filefirstname.'&langfile='.$_GET['langfile']; ?>" name="edit_lang" method="post">
    <div>
        <input type="submit" name="" value="Save"  />
        <input type="reset" name="" value="Reset" />
        <input type="button" name="" value="Cancel" onclick="javascript:document.location.href='<?php echo $index; ?>'" />
    </div>
    <table width="100%" style="background-color:#eee;">
        <tr>
            <td width="50%">
                <p style="font-size: large; font-weight: bold;"><?php echo $lng['credit_lang_file'];?></p>
                <table style="border:1px dotted #000;" cellpadding="2" cellspacing="0" width="100%">
                    <tr style="border:1px dotted #000;">
                        <th style="border:1px dotted #000;">Keys</th>
                        <th style="border:1px dotted #000;">English</th>
                        <th style="border:1px dotted #000;width:98%;"><?php echo $this->_e2g_encode($filefirstname,'ucfirst') ; ?></th>
                    </tr>
                        <?php
                        foreach ($engs as $eng) {
                            if ($eng['key'] == 'credit_lang_file_author'
                                    || $eng['key'] == 'credit_lang_file_date'
                                    || $eng['key'] == 'credit_lang_file_version'
                            ) {
                                ?>
                    <tr>
                        <td style="border:1px dotted #000;" valign="top" nowrap="nowrap"><?php echo $eng['key'];?></td>
                        <td style="border:1px dotted #000;" valign="top" nowrap="nowrap"><?php echo $eng['value'];?></td>
                        <td style="border:1px dotted #000;" valign="top">
                                        <?php
                                        echo '<input type="text" size="" style="width:98%;" name="'.$others[$eng['key']]['key'].'"
                    value="'.$others[$eng['key']]['value'].'" />';
                                        ?>
                        </td>
                    </tr>
                                <?php
                            }
                            unset($engs['credit_lang_file_author'],$engs['credit_lang_file_date'],$engs['credit_lang_file_version']);
                        }
                        ?>
                </table>
            </td>
            <td width="50%" valign="top">
                <p style="font-size: large; font-weight: bold;">
                    <a href="http://php.net/manual/en/function.setlocale.php" target="_blank">setlocale(category,locale)</a>
                </p>
                <table style="border:1px dotted #000;" cellpadding="2" cellspacing="0" width="100%">
                    <tr style="border:1px dotted #000;">
                        <th style="border:1px dotted #000;">Keys</th>
                        <th style="border:1px dotted #000;">English</th>
                        <th style="border:1px dotted #000;width:98%;"><?php echo $this->_e2g_encode($filefirstname,'ucfirst') ; ?></th>
                    </tr>
                        <?php
                        foreach ($engs as $eng) {
                            if ($eng['key'] == 'setlocale') {
                                ?>
                    <tr>
                        <td style="border:1px dotted #000;" valign="top"><?php echo $eng['key'];?></td>
                        <td style="border:1px dotted #000;" valign="top" nowrap="nowrap"><?php echo $eng['value'];?></td>
                        <td style="border:1px dotted #000;" valign="top">
                                        <?php
                                        echo '<input type="text" size="" style="width:98%;" name="'.$others[$eng['key']]['key'].'"
                    value="'.$others[$eng['key']]['value'].'" />';
                                        ?>
                        </td>
                    </tr>
                                <?php
                            }
                            unset($engs['setlocale']);
                        }
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
                <th style="border:1px dotted #000;" width="40%"><?php echo $this->_e2g_encode($filefirstname,'ucfirst') ; ?></th>
            </tr>
                <?php
                foreach ($engs as $eng) {
                    if ($eng['key'] != 'setlocale') {
                        // to separate the javascript text
                        $jsclass = (strpos($eng['key'],'js_')!==false ? 'background-color:#FFC;': '');
                        ?>
            <tr style="border:1px dotted #000;">
                <td style="border:1px dotted #000;<?php echo $jsclass;?>" valign="top"><?php echo $eng['key'];?></td>
                <td style="border:1px dotted #000;<?php echo $jsclass;?>" valign="top">
                                <?php
                                echo htmlspecialchars_decode($eng['value'], ENT_QUOTES);
                                ?>
                </td>
                <td style="border:1px dotted #000;<?php echo $jsclass;?>" valign="top">
                                <?php
                                if (strlen($eng['value'])<80) {
                                    echo '<input type="text" size="60" name="'.$others[$eng['key']]['key'].'"
                                value="'.$others[$eng['key']]['value'].'" />';
                                } else {
                                    $textareaheight = (strlen($eng['value'])/5);
                                    echo '<textarea rows="" cols="" style="width : 94%;height: '.$textareaheight.'px;"'
                                            . ( strpos($eng['key'],'js_')===false ? ' class="mceEditor"' : '' )
                                            .' name="'.$others[$eng['key']]['key'].'" >';
                                    echo $others[$eng['key']]['value'];
                                    echo '</textarea>';
                                }
                                ?>

                </td>
            </tr>
                        <?php
                    }
                }
                ?>
        </table>
    </div>
    <div>
        <input type="submit" name="" value="Save"  />
        <input type="reset" name="" value="Reset" />
        <input type="button" name="" value="Cancel" onclick="javascript:document.location.href='<?php echo $index; ?>'" />
    </div>
</form>

    <?php

}