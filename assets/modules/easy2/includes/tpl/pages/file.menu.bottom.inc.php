<?php
if (IN_MANAGER_MODE != 'true') die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

if (!isset($_GET['path'])) {
?>
<div id="e2g_bottommenu">
    <ul class="actionButtons">
        <li style="float:left;">
            <b><?php echo $lng['withselected']; ?>: </b>
            <select id="fileActions" name="fileActions">
                <option value="">&nbsp;</option>
                <option value="show"><?php echo $lng['show']; ?></option>
                <option value="hide"><?php echo $lng['hide']; ?></option>
                <option value="delete"><?php echo $lng['delete']; ?></option>
                <?php if(class_exists('ZipArchive')) { ?>
                <option value="download"><?php echo $lng['download']; ?></option>
                    <?php } ?>
                <option value="tag"><?php echo $lng['tag']; ?></option>
                <option value="move"><?php echo $lng['move']; ?></option>
            </select>
            <!--a href="javascript:void(0)" onclick="submitfileform(1)">
                <img src="<?php echo E2G_MODULE_URL; ?>includes/tpl/icons/action_go.gif" alt="" />
                <span><?php echo $lng['go']; ?></span>
            </a-->
        </li>
        <li id="showActions" style="display: none;">
            <a name="show" href="javascript:;" onclick="submitform(3)">
                <img src="<?php echo  E2G_MODULE_URL; ?>includes/tpl/icons/eye_opened.png" alt="" /> <?php echo $lng['show']; ?>
            </a>
        </li>
        <li id="hideActions" style="display: none;">
            <a name="hide" href="javascript:;" onclick="submitform(4)">
                <img src="<?php echo  E2G_MODULE_URL; ?>includes/tpl/icons/eye_closed.png" alt="" /> <?php echo $lng['hide']; ?>
            </a>
        </li>
        <li id="deleteActions" style="display: none;">
            <a name="delete" href="javascript:;" onclick="submitform(5)" style="font-weight:bold;color:red">
                <img src="<?php echo  MODX_MANAGER_URL ; ?>media/style/MODxCarbon/images/icons/delete.png" alt="" /> <?php echo $lng['delete']; ?>
            </a>
        </li>
        <?php if(class_exists('ZipArchive')) { ?>
        <li id="downloadActions" style="display: none;">
            <a name="download" href="javascript:;" onclick="submitform(6)">
                <img src="<?php echo E2G_MODULE_URL; ?>includes/tpl/icons/page_white_compressed.png" alt="" /> <?php echo $lng['download']; ?>
            </a>
        </li><?php } ?>
        <li id="tagActions" style="display: none;">
            <?php echo $lng['tag'];?>:
            <input name="tag_input" type="text" size="20" />&nbsp;
            <a name="tag_add" href="javascript:;" onclick="submitform(8)" title="Add Tag">
                <img src="<?php echo  MODX_MANAGER_URL ; ?>media/style/MODxCarbon/images/icons/add.png" alt="" /> <?php echo $lng['add']; ?>
            </a>&nbsp;
            <a name="tag_del" href="javascript:;" onclick="submitform(9)" title="Remove Tag">
                <img src="<?php echo  MODX_MANAGER_URL ; ?>media/style/MODxCarbon/images/icons/delete.png" alt="" /> <?php echo $lng['remove']; ?>
            </a>
        </li>
        <li id="moveActions" style="display: none;">
            <span><?php echo $lng['movetofolder']; ?> :</span>
            <select name="newparent">
                <option value="">&nbsp;</option>
                <?php echo $this->_folderOptions(0); ?>
            </select>
            <span><?php echo $lng['and']; ?></span>
            <select name="gotofolder">
                <option value="gothere"><?php echo $lng['go_there']; ?></option>
                <option value="stayhere"><?php echo $lng['stay_here']; ?></option>
            </select>
            <a name="move" href="javascript:;" onclick="submitform(7)">
                <img src="<?php echo  MODX_MANAGER_URL ; ?>media/style/MODxCarbon/images/icons/sort.png" alt="" />
                <span><?php echo $lng['go']; ?></span>
            </a>
        </li>
    </ul>
</div>
<?php
}