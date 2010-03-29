<?php
if (IN_MANAGER_MODE != 'true') die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

$paramhelpcontent = (isset($paramhelpcontent) ? $paramhelpcontent : '' );
if (file_exists( E2G_MODULE_PATH . 'langs/'.$modx->config['manager_language'].'.help.params.inc.php')) {
    $easyhelpfile = file( E2G_MODULE_PATH . 'langs/'.$modx->config['manager_language'].'.help.params.inc.php' );
} else {
    $easyhelpfile = file_get_contents( E2G_MODULE_PATH . 'langs/english.help.params.inc.php' );
}
$numLines = count($easyhelpfile);
// process each line but skip the first 3 lines.
for ($i = 3; $i < $numLines; $i++) {
    $paramhelpcontent .= trim($easyhelpfile[$i]);
}

$tplhelpcontent = (isset($tplhelpcontent) ? $tplhelpcontent : '' );
if (file_exists( E2G_MODULE_PATH . 'langs/'.$modx->config['manager_language'].'.help.tpl.inc.php')) {
    $tplhelpfile = file( E2G_MODULE_PATH . 'langs/'.$modx->config['manager_language'].'.help.tpl.inc.php' );
} else {
    $tplhelpfile = file_get_contents( E2G_MODULE_PATH . 'langs/english.help.tpl.inc.php' );
}
$numLines = count($tplhelpfile);
// process each line but skip the first 3 lines.
for ($i = 3; $i < $numLines; $i++) {
    $tplhelpcontent .= trim($tplhelpfile[$i]);
}

$lng = array (
        'charset' => 'ISO-8859-1',
//        'charset' => 'UTF-8',
        'create_dir' => 'Create folder',
        'manager' => 'Files',
        'upload' => 'Image Upload',
        'help' => 'Help',
        'install' => 'Install',
        'upload_dir' => 'Upload image in folder',
        'file' => 'File',
        'file2' => 'files',
        'name' => 'Name',
        'description' => 'Description',
        'upload_btn' => 'Upload',
        'add_field_btn' => 'Upload more',
        'remove_field_btn' => 'Remove',
        'delete_confirm' => 'Are you sure you want to delete this file?\n\nClick ok to confirm.',
        'delete_folder_confirm' => 'Are you sure you want to delete this folder?\nAll child files and folders will be removed as well.\n\nClick ok to confirm.',
        'enter_dirname' => 'Enter new folder name',
        'enter_new_dirname' => 'Rename folder',

        'debug' => 'Debug option',
        'dir' => 'folder',
        'tinymcefolder' => 'TinyMCE folder',
        'thumb' => 'thumbnail',
        'empty' => 'empty',
        'valid_extensions' => 'Valid extensions are',
        'files_uploaded' => 'files uploaded',
        'directory_created' => 'folder created',
        'files_deleted' => 'files deleted',
        'files_deleted_fdb' => 'files removed from DB',
        'files_deleted_fhdd' => 'files removed from HD',
        'dirs_deleted' => 'folders deleted',
        'dirs_deleted_fdb' => 'folders removed from DB',
        'dirs_deleted_fhdd' => 'folders removed from HD',
        'dir_delete' => 'folder deleted',
        'dir_delete_fdb' => 'folder removed only from DB',
        'dir_delete_fhdd' => 'folder removed only from hd',
        'file_delete' => 'file deleted',
        'file_delete_fdb' => 'file removed only from DB',
        'file_delete_fhdd' => 'file removed only from HD',

        'dir_edded' => 'Folder created',
        'dir_edd_err' => 'There was an error while trying to create a folder',

        'synchro' => 'Synchronize',
        'synchro_suc' => 'Gallery synchronized',
        'synchro_err' => 'There was an error while trying to synchronize',

        'indexfile' => '<h2>Unauthorized access</h2>You\'re not allowed to access file folder',

        'restore_suc' => 'Gallery\'s names restored',
        'restore_err' => 'There was an error while trying to restore',

        'archive' => 'Archive zip',

        'commentsmgr' => 'Comments Manager',
        'allcomments' => 'All Comments',
        'comments' => 'Comments',
        'author' => 'Author',
        'date' => 'Date',
        'ipaddress' => 'IP Address',
        'ignoredipaddr' => 'Ignored IP Address',
        'modified' => 'Modified',
        'delete' => 'Delete',
        'refresh' => 'Update',
        'info' => 'Info',
        'size' => 'Size',
        'options' => 'Actions',
        'edit' => 'Edit',
        'editing' => 'Editing info',
        'add_to_db' => 'Add to DB',
        'path' => 'Path',
        'updated' => 'saved',
        'back_to_fmanager' => 'Back',
        'save' => 'Save',
        'cancel' => 'Cancel',
        'clean_cache' => 'Clear cache',
        'cache_clean' => 'Cache is cleared',
        'cache_clean_err' => 'There was an error while trying to clean cache',

        'config' => 'Config',

        'newimgcfg' => 'Images settings',
        'thumbscfg' => 'Thumbnails settings',
        'thumbcnt' => 'Display settings',
        'w' => 'Width',
        'h' => 'Height',
        'thq' => 'Compression level',
        'resize_type' => 'Thumbnail Resize',
        'inner' => 'inner',
        'shrink' => 'shrink',
        'resize' => 'resize',
        'thbg_rgb' => 'Thumbnail background color',

        'name_len' => 'max thumbnail name length',
        'cat_name_len' => 'max folder name length',
        'colls' => 'columns',
        'notables' => 'Grid',
        'limit' => 'Pictures per page',
        'glib' => 'JS Library',
        'ecl' => 'Comments per page',
        'tpl' => 'Templates',
        'css' => 'CSS',
        'gallery' => 'Gallery',
        'comments_row' => 'Comments row',
        'wm' => 'Watermarks',
        'type' => 'type',
        'text' => 'text',
        'image' => 'image',
        'wmt' => 'text/path',
        'wmpos1' => 'Horizontal position',
        'wmpos2' => 'Vertical position',
        'pos1' => 'left',
        'pos2' => 'center',
        'pos3' => 'right',
        'pos4' => 'top',
        'pos5' => 'center',
        'pos6' => 'bottom',
        'order' => 'Thumbs order by',
        'order2' => 'Folders order by',
        'date_added' => 'date',
        'filename' => 'filename',
        'last_modified' => 'editing date',
        'comments_cnt' => 'comments count',
        'random' => 'random',
        'captcha' => 'Captcha',

        'asc' => 'asc',
        'desc' => 'desc',

        'on' => 'On',
        'off' => 'Off',

        'cfg_com0' => 'Debug mode.',
        'cfg_com1' => 'Path to folder with <b class="warning">trailing slash</b>, e.g.: assets/easy2gallery/.',
        'cfg_com1a' => 'TinyMCE folder, e.g.: tinymce3241',
        'cfg_com2' => 'Max width in pixels, larger images will be resized automatically.<br> <b>0 - no limit</b>.',
        'cfg_com3' => 'Max height in pixels, larger images will be resized automatically.<br> <b>0 - no limit</b>.',
        'cfg_com4' => 'Level of jpeg comression from 0 to 100%.<br><b class="warning">Only for images larger than limits</b>.',
        'cfg_com5' => 'Thumbnail width, px.',
        'cfg_com6' => 'Thumbnail height, px',
        'cfg_com6a' => 'Inner: cropped | Shrink: shrink | Resize: proportional autofit',
        'cfg_com6b' => 'White is 255 255 255, black is 0 0 0. It\'s only resized thumbnail that contains margin gap with default thumb size.',
        'cfg_com7' => 'Level of jpeg compression of thumbnails , from 0 to 100%.',
        'cfg_com8' => 'Columns',
        'cfg_com9' => 'Thumbnails per page',
        'cfg_com10' => 'Graphic library',
        'cfg_com10a' => 'Using table for positioning or not',
        'cfg_com11' => 'Comments per page',
        'cfg_com12' => 'type of watermark, text or image',
        'cfg_com13' => 'text of watermark or path to',
        'cfg_com14' => 'horisontal position of watermark',
        'cfg_com15' => 'vertical position of watermark',
        'cfg_com16' => 'Sorting method',
        'cfg_com17' => 'Chunk name or path to tpl file',
        'cfg_com18' => 'Max name length',
        'cfg_com19' => 'chunk name or path to file, <b class="warning">relative to comments.easy2gallery.php</b>',
        'cfg_com20' => 'Max folder name length',
        'cfg_com21' => 'Folder sorting method',

        'add_comment' => 'Add comment',
        'username' => 'Name',
        'useremail' => 'Email',
        'userecomment' => 'Comment',
        'send_btn' => 'Post',
        'empty_name_comment' => 'You must enter name and comment',
        'comment_added' => 'Comment is added',
        'comment_add_err' => 'There was an error while trying to add comment',

        'file_added' => 'File added',


        '_thumb_err' => 'Can\'t create folder &quot;_thumbnails&quot;',
        'upload_err' => 'Can\'t upload file',
        'add_file_err' => 'Can\'t add file',
        'ren_file_err' => 'Can\'t rename file',
        'type_err' => 'Restricted type of file',
        'db_err' => 'DB error',
        'directory_create_err' => 'Can\'t create folder',
        'files_delete_err' => 'Can\'t delete files',
        'dirs_delete_err' => 'Can\'t delete folders',
        'dir_delete_err' => 'Can\'t delete folder',
        'dpath_err' => 'path or id of folder is undefined',
        'fpath_err' => 'path or id of image is undefined',
        'file_delete_err' => 'Can\'t delete file',
        'id_err' => 'wrong id',
        'update_err' => 'changes not saved',
        'renamefile' => 'Change the filename',

        'uim_preview' => 'Preview',
        'uim_preview_err' => 'Preview<br />not available',


        'paramhelptitle' => 'Parameters',
        'tplhelptitle' => 'Templates',
        'moreinfotitle' => 'More information',

        'paramhelpcontent' => $paramhelpcontent,
        'tplhelpcontent' => $tplhelpcontent,
        'moreinfocontent' => '
<p><a href="http://e2g.info/documentation.htm" target="_blank"><b>Documentation</b></a></p>
<p><b><a href="http://e2g.info/" target="_blank">Easy 2 Gallery official site</a></b></p>
<p><b><a href="http://wiki.modxcms.com/index.php/Easy2gallery" target="_blank">Easy 2 Gallery WIKI</a></b></p>

'
);

?>