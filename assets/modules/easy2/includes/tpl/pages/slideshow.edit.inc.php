<?php
if (IN_MANAGER_MODE != 'true')
    die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

if (!isset($this->sanitizedGets['page']) || $this->sanitizedGets['page'] !== 'edit_slideshow' && $this->sanitizedGets['page'] !== 'duplicate_slideshow') {
    die("Die off!");
}

$select_slideshows = 'SELECT * FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_slideshows WHERE id=' . $this->sanitizedGets['ssid'];
$query_slideshows = mysql_query($select_slideshows);
if (!$query_slideshows)
    die(__LINE__ . ': ' . mysql_errno() . ' ' . mysql_error() . '<br />' . $select_slideshows);
else {
    $numrow_slideshows = mysql_num_rows($query_slideshows);
    $row = mysql_fetch_assoc($query_slideshows);
}

$slideshow_id = $this->sanitizedGets['page'] === 'duplicate_slideshow' ? '' : $row['id'];
if (!empty($slideshow_id)) {
    echo '<p>ID: ' . $row['id'] . '</p>';
}
?>

<form action="<?php
echo $this->e2gModCfg['index'];
if ($this->sanitizedGets['page'] === 'duplicate_slideshow') {
    echo '&amp;act=save_slideshow';
} elseif ($this->sanitizedGets['page'] === 'edit_slideshow') {
    echo '&amp;act=update_slideshow';
}
?>" method="post">
    <input type="hidden" name="slideshow_id" value="<?php echo $slideshow_id; ?>" />
    <table cellspacing="0" cellpadding="2">
        <tr>
            <td><b><?php echo $this->lng['name']; ?>:</b></td>
            <td><input name="name" type="text" size="75" value="<?php
$name = $this->sanitizedGets['page'] === 'duplicate_slideshow' ? 'Duplicate of ' . $row['name'] : $row['name'];
echo $name;
?>" /></td>
        </tr>
        <tr>
            <td valign="top"><b><?php echo $this->lng['description']; ?>:</b></td>
            <td><input name="description" type="text" size="75" value="<?php echo $row['description']; ?>" /></td>
        </tr>
        <tr>
            <td><b><?php echo $this->lng['index_file']; ?>:</b></td>
            <td><input name="index_file" type="text" size="75" value="<?php echo $row['indexfile']; ?>" /></td>
        </tr>

        <tr>
            <td></td>
            <td>
                <br />
                <input type="submit" value="<?php echo $this->lng['save']; ?>" /> &nbsp; &nbsp; &nbsp;
                <input type="button" value="<?php echo $this->lng['cancel']; ?>" onclick="history.go(-1)" /> &nbsp; &nbsp; &nbsp;
            </td>
        </tr>
    </table>
</form>