<?php
$e2g_lang['english'] = array (
        'UTF-8' => 'UTF-8',
        '_thumb_err' => 'Could not create folder &quot;_thumbnails&quot;',
        'add_comment' => 'Add comment',
        'add_field_btn' => 'Upload more',
        'add_file_err' => 'Could not add file',
        'add_to_db' => 'Add to DB',
        'advancehelpcontent' => '&lt;h2&gt;Nested easy2 with MODx API&lt;/h2&gt;
&lt;p&gt;&lt;strong&gt;$customgetparams &lt;/strong&gt;- (with dollar sign) a blank variable handle to add custom &amp;amp;_GET parameter into &lt;strong&gt;pagination &lt;/strong&gt;to be nested with other snippets.&lt;/p&gt;
&lt;p&gt;Example:
&lt;/p&gt;
&lt;blockquote&gt;
&lt;hr /&gt;
&lt;p&gt;$select = &#039;SELECT * FROM `your_table` WHERE id=&#039;.$param1;&lt;br /&gt;$query = mysql_query($select);&lt;br /&gt;$row = mysql_fetch_array($query);&lt;/p&gt;
&lt;p&gt;$easy2pictures = $modx-&amp;gt;runSnippet(&quot;easy2&quot;,array(&lt;br /&gt;&amp;nbsp;&amp;nbsp;&amp;nbsp;&amp;nbsp;&amp;nbsp;&amp;nbsp;&amp;nbsp;&amp;nbsp;&amp;nbsp;&amp;nbsp;&amp;nbsp; &quot;gid&quot;=&amp;gt;$row[&quot;your_Gallery_Id_Field_Inside_Custom_Table&quot;]&lt;br /&gt;&amp;nbsp;&amp;nbsp;&amp;nbsp;&amp;nbsp;&amp;nbsp;&amp;nbsp;&amp;nbsp;&amp;nbsp;&amp;nbsp;&amp;nbsp;&amp;nbsp; ,&quot;customgetparams&quot;=&amp;gt;&#039;&amp;amp;param1=&#039;.$_GET[&#039;param1&#039;].&#039;&amp;amp;param2=&#039;.$_GET[&#039;param2&#039;]&lt;br /&gt;&amp;nbsp;&amp;nbsp;&amp;nbsp;&amp;nbsp;&amp;nbsp;&amp;nbsp;&amp;nbsp;&amp;nbsp;&amp;nbsp;&amp;nbsp;&amp;nbsp; ));&lt;/p&gt;
&lt;p&gt;echo $easy2pictures;
&lt;/p&gt;
&lt;hr /&gt;
&lt;/blockquote&gt;
&lt;p&gt;The pagination hyperlink will be like:&lt;br /&gt;http://your-website/index.php?id=4&lt;strong&gt;&amp;amp;param1&lt;/strong&gt;=string&lt;strong&gt;&amp;amp;param2&lt;/strong&gt;=string&amp;amp;gid=2&amp;amp;gpn=1&lt;/p&gt;
&lt;p&gt;&amp;nbsp;&lt;/p&gt;
&lt;h2&gt;Increase the upload limit&lt;/h2&gt;
&lt;p&gt;PHP only allows 2MB maximum on the file uploading.&lt;br /&gt;This can be increased by putting the .htaccess file into the &lt;b&gt;[modx-root]/manager/&lt;/b&gt; with this content:&lt;/p&gt;
&lt;b&gt;php_value upload_max_filesize 16M&lt;/b&gt;
&lt;p&gt;Or whatever size you need.&lt;/p&gt;',
        'advancehelptitle' => 'Advance',
        'allcomments' => 'All Comments',
        'archive' => 'Archive zip',
        'asc' => 'asc',
        'author' => 'Author',
        'back_to_fmanager' => 'Back',
        'badchars' => 'Invalid characters for file system',
        'cache_clean' => 'Cache is cleared',
        'cache_clean_err' => 'There was an error while trying to clean cache',
        'cancel' => 'Cancel',
        'captcha' => 'Captcha',
        'cat_name_len' => 'max folder name length',
        'cfg_com0' => 'Debug mode.',
        'cfg_com1' => 'Path to folder with &lt;b class=&quot;warning&quot;&gt;trailing slash&lt;/b&gt;, e.g.: assets/easy2gallery/.',
        'cfg_com10' => 'Graphic library',
        'cfg_com10a' => 'Thumbnail&#039;s grid.',
        'cfg_com10b' => 'CSS class name for the grid.',
        'cfg_com11' => 'Comments per page',
        'cfg_com12' => 'type of watermark, text or image',
        'cfg_com13' => 'text of watermark or path to',
        'cfg_com14' => 'horisontal position of watermark',
        'cfg_com15' => 'vertical position of watermark',
        'cfg_com16' => 'Sorting method',
        'cfg_com17' => 'Chunk name or path to tpl file',
        'cfg_com17a' => 'chunk name or path to template file of a single landing page',
        'cfg_com18' => 'Max name length',
        'cfg_com19' => 'chunk name or path to file, &lt;b class=&quot;warning&quot;&gt;relative to comments.easy2gallery.php&lt;/b&gt;',
        'cfg_com1a' => 'The script&#039;s encoding.',
        'cfg_com2' => 'Max width in pixels, larger images will be resized automatically.&lt;br&gt; &lt;b&gt;0 - no limit&lt;/b&gt;.',
        'cfg_com20' => 'Max folder name length',
        'cfg_com21' => 'Folder sorting method',
        'cfg_com3' => 'Max height in pixels, larger images will be resized automatically.&lt;br&gt; &lt;b&gt;0 - no limit&lt;/b&gt;.',
        'cfg_com4' => 'Level of jpeg comression from 0 to 100%.&lt;br&gt;&lt;b class=&quot;warning&quot;&gt;Only for images larger than limits&lt;/b&gt;.',
        'cfg_com4a' => 'Set this option &#039;Yes&#039; if the change should also apply to ALL existing images. Click the &#039;Synchronize&#039; button to run the process.',
        'cfg_com5' => 'Thumbnail width, px.',
        'cfg_com6' => 'Thumbnail height, px',
        'cfg_com6a' => 'Inner: cropped | Shrink: shrink | Resize: proportional autofit',
        'cfg_com6b' => 'White is 255 255 255, black is 0 0 0. It&#039;s only resized thumbnail that contains margin gap with default thumb size.',
        'cfg_com7' => 'Level of jpeg compression of thumbnails , from 0 to 100%.',
        'cfg_com8' => 'Columns',
        'cfg_com9' => 'Thumbnails per page',
        'cfg_e2g_currentcrumb_class' => 'Current crumb&#039;s class',
        'cfg_e2gback_class' => 'Back button&#039;s class',
        'cfg_e2gpnums_class' => 'Pagination&#039;s class',
        'char_limitation' => 'Please use &lt;b&gt;common latin letters&lt;/b&gt; for the filenames inside the ZIP contents, or the file system will find errors while uploading.&lt;br /&gt;UTF-8 letter will be ignored or translated to the closest character.',
        'charset' => 'UTF-8',
        'chmod_err' => 'Unable to change the permission',
        'classname' => 'Class name',
        'clean_cache' => 'Clear cache',
        'colls' => 'columns',
        'comment_add_err' => 'There was an error while trying to add comment',
        'comment_added' => 'Comment is added',
        'comments' => 'Comments',
        'comments_cnt' => 'comments count',
        'comments_row' => 'Comments row',
        'commentsmgr' => 'Comments Manager',
        'config' => 'Config',
        'copy' => 'Copy',
        'couldnotdeletedirectory' => 'Could not delete directory',
        'couldnotdeletefile' => 'Could not delete file',
        'couldnotmovedirectory' => 'Could not move directory',
        'couldnotmovefile' => 'Could not move file',
        'create_dir' => 'Create folder',
        'css' => 'CSS',
        'date' => 'Date',
        'date_added' => 'date',
        'db_err' => 'DB error',
        'delete' => 'Delete',
        'desc' => 'desc',
        'description' => 'Description',
        'dir' => 'folder',
        'dir_delete' => 'folder deleted',
        'dir_delete_err' => 'Could not delete folder',
        'dir_delete_fdb' => 'folder removed only from DB',
        'dir_delete_fhdd' => 'folder removed only from hd',
        'dir_edd_err' => 'There was an error while trying to create a folder',
        'dir_edded' => 'Folder created',
        'dir_move_err' => 'Could not move folder',
        'dir_move_suc' => 'Moved folder',
        'dir_moved' => 'folder moved',
        'dir_moved_fdb' => 'folder updated only in DB',
        'dir_moved_fhdd' => 'folder moved only in HD',
        'directory_create_err' => 'Could not create folder',
        'directory_created' => 'folder created',
        'dirs_delete_err' => 'Could not delete folders',
        'dirs_deleted' => 'folders deleted',
        'dirs_deleted_fdb' => 'folders removed from DB',
        'dirs_deleted_fhdd' => 'folders removed from HD',
        'dirs_move_err' => 'Could not move folders',
        'dirs_move_suc' => 'Moved folders',
        'dirs_moved' => 'folders moved',
        'dirs_moved_fdb' => 'folders updated in DB',
        'dirs_moved_fhdd' => 'folders moved in HD',
        'dirs_uploaded' => 'directories uploaded',
        'download' => 'Download',
        'dpath_err' => 'path or id of folder is undefined',
        'e2g_debug' => 'Debug option',
        'e2g_encode' => 'Encoding',
        'ecl' => 'Comments per page',
        'edit' => 'Edit',
        'editing' => 'Editing info',
        'empty' => 'empty',
        'empty_name_comment' => 'You must enter name and comment',
        'enter_dirname' => 'Enter new folder name',
        'enter_new_alias' => 'Folder&#039;s title',
        'enter_new_dirname' => 'Rename folder',
        'file' => 'File',
        'file2' => 'files',
        'file_added' => 'File added',
        'file_delete' => 'file deleted',
        'file_delete_err' => 'Could not delete file',
        'file_delete_fdb' => 'file removed only from DB',
        'file_delete_fhdd' => 'file removed only from HD',
        'filename' => 'filename',
        'files_delete_err' => 'Could not delete files',
        'files_deleted' => 'files deleted',
        'files_deleted_fdb' => 'files removed from DB',
        'files_deleted_fhdd' => 'files removed from HD',
        'files_uploaded' => 'files uploaded',
        'filetosamedirectory_err' => 'Could not move file into the same folder',
        'fpath_err' => 'path or id of image is undefined',
        'gallery' => 'Gallery',
        'glib' => 'JS Library',
        'go' => 'Go',
        'gotofolder' => 'Go to folder',
        'grid' => 'Grid',
        'grid_class' => 'Grid&#039;s class',
        'h' => 'Height',
        'help' => 'Help',
        'hidden' => 'Hidden',
        'hiddencomments' => 'Hidden Comments',
        'id_err' => 'wrong id',
        'ignore' => 'Ignore',
        'ignored_ip' => 'Ignored IP Address',
        'image' => 'image',
        'indexfile' => '&lt;h2&gt;Unauthorized access&lt;/h2&gt;You&#039;re not allowed to access file folder',
        'info' => 'Info',
        'inner' => 'inner',
        'install' => 'Install',
        'invisible' => 'Invisible',
        'ip_ignored_err' => 'Could not add IP to ignore list.',
        'ip_ignored_suc' => 'This IP is now ignored.',
        'ip_unignored_err' => 'Could not release IP from ignore list.',
        'ip_unignored_suc' => 'This IP is now unignored.',
        'ipaddress' => 'IP Address',
        'js_delete_confirm' => 'Are you sure you want to delete this file?\n\nClick ok to confirm.',
        'js_delete_folder_confirm' => 'Are you sure you want to delete this folder?\nAll child files and folders will be removed as well.\n\nClick ok to confirm.',
        'js_ignore_ip_address_confirm' => 'Are you sure want to ignore this IP Address?\nAll comments from this IP address will be hidden as well.\n\nClick ok to confirm.',
        'js_unignore_ip_address_confirm' => 'Are you sure want to unignore this IP Address?\nAll comments from this IP address will be shown as well.\n\nClick ok to confirm.',
        'landing_page' => 'Landing page tpl',
        'langupdated' => 'Language file is updated.',
        'last_modified' => 'editing date',
        'limit' => 'Pictures per page',
        'manager' => 'Files',
        'memoryusage' => 'memory usage',
        'modified' => 'Modified',
        'moreinfocontent' => '&lt;p&gt;&lt;a href=&quot;http://e2g.info/documentation.htm&quot; target=&quot;_blank&quot;&gt;&lt;b&gt;Documentation&lt;/b&gt;&lt;/a&gt;&lt;/p&gt;
&lt;p&gt;&lt;b&gt;&lt;a href=&quot;http://e2g.info/&quot; target=&quot;_blank&quot;&gt;Easy 2 Gallery official site&lt;/a&gt;&lt;/b&gt;&lt;/p&gt;
&lt;p&gt;&lt;b&gt;&lt;a href=&quot;http://wiki.modxcms.com/index.php/Easy2gallery&quot; target=&quot;_blank&quot;&gt;Easy 2 Gallery WIKI&lt;/a&gt;&lt;/b&gt;&lt;/p&gt;',
        'moreinfotitle' => 'More information',
        'move' => 'Move',
        'movetofolder' => 'Move to folder',
        'name' => 'Name',
        'name_len' => 'max thumbnail name length',
        'newimgcfg' => 'Images settings',
        'no' => 'no',
        'none' => 'none',
        'off' => 'Off',
        'oldimgcfg' => 'Resize existing images',
        'on' => 'On',
        'options' => 'Actions',
        'order' => 'Thumbs order by',
        'order2' => 'Folders order by',
        'paramhelpcontent' => '&lt;h2&gt;Basic call: [!easy2!]&lt;/h2&gt;
&lt;p&gt;Will show all directories and images under assets/gallery folder&lt;/p&gt;
&lt;h2&gt;Main parameters&lt;/h2&gt;
&lt;p&gt;You can have multiple IDs&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;gid&lt;/strong&gt; - id of gallery, from which e2g snippet starts&lt;br /&gt;
default 1 - root.&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;fid &lt;/strong&gt;- id of image for spesific image display&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;rgid &lt;/strong&gt;- gallery id for random thumbnail output&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;tags&lt;/strong&gt; - image&#039;s/folder&#039;s tags (this parameter will ignore the &amp;amp;gid parameter, but can accompanied by &amp;amp;showonly). Options: word/string.&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;showonly&lt;/strong&gt; - images | folders, to show only those spesific type&lt;/p&gt;
&lt;p&gt;Example call: &lt;strong&gt;[!easy2?fid=`2,3`!]&lt;/strong&gt;&lt;br /&gt;Will show images ID# 2 &lt;strong&gt;and &lt;/strong&gt;3&lt;/p&gt;
&lt;p&gt;Example call: &lt;strong&gt;[!easy2?gid=`20,57` &amp;amp;showonly=`images`!]&lt;/strong&gt;&lt;br /&gt;Will show &lt;strong&gt;only &lt;/strong&gt;images from folder ID 20 &lt;strong&gt;and &lt;/strong&gt;57 (excluding directory&#039;s thumbnails).&lt;/p&gt;
&lt;p&gt;Example call: &lt;strong&gt;[!easy2?rgid=`102,138`!]&lt;/strong&gt;&lt;br /&gt;Will show random image from folder ID 102 &lt;strong&gt;and &lt;/strong&gt;138, inside &lt;strong&gt;one &lt;/strong&gt;thumbnail.&lt;/p&gt;
&lt;p&gt;Example call: &lt;strong&gt;[!easy2?tags=`dogs, puppies` &amp;amp;showonly=`images`!]&lt;/strong&gt;&lt;br /&gt;Will show all images (only) which have dogs OR puppies tag.&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;If you want to apply changes to added images you should clean cache.&lt;/strong&gt;&lt;/p&gt;
&lt;h2&gt;Thumbnail&lt;/h2&gt;
&lt;p&gt;&lt;strong&gt; Folder _thumbnails in root of gallery.&lt;/strong&gt;&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;w&lt;/strong&gt; - thumbnail width, px.&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;h&lt;/strong&gt; - thumbnail height, px.&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;thq&lt;/strong&gt; - Level of jpeg-compression. 0 to 100%. (100 - max)&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;name_len&lt;/strong&gt; - Max length of thumbnail name
&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;cat_name_len&lt;/strong&gt; - Max length of folder name
&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;colls&lt;/strong&gt; - images per column&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;limit&lt;/strong&gt; - images per page.&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;gpn&lt;/strong&gt; - start page number.&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;orderby&lt;/strong&gt; - Sort by: date_added, last_modified, comments, filename, name, random.&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;order&lt;/strong&gt; - Sort ASCENDING or DESCENDING, ASC / DESC correspondingly.&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;cat_orderby&lt;/strong&gt; - Field by which folders will be sorted out: cat_id, cat_name, random.&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;cat_order&lt;/strong&gt; - Sort folders ASCENDING or DESCENDING, ASC / DESC correspondingly&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;resize_type&lt;/strong&gt; - type of thumbnail resizing: &#039;inner&#039; (cropped) | &#039;resize&#039; (autofit) | &#039;shrink&#039; (shrink)&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;thbg_red&lt;/strong&gt; - thumbnail background color: RED in RGB&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;thbg_green&lt;/strong&gt; - thumbnail background color: GREEN in RGB&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;thbg_blue&lt;/strong&gt; - thumbnail background color: BLUE in RGB&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;grid &lt;/strong&gt;- options of thumbnail&#039;s grid arrangement, &#039;css&#039; or &#039;table&#039;
&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;show_group&lt;/strong&gt; - slideshow group name&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;landingpage&lt;/strong&gt; - set the slideshow&#039;s/thumbnail&#039;s landing page.&lt;/p&gt;
&lt;ul&gt;
&lt;li&gt;for the slideshow: if
there is an &#039;open image&#039; pop up, but there are no &amp;amp;landingpage is
set up, then the image will be opened directly inside the page,
replacing the slideshow box.&lt;/li&gt;
&lt;li&gt;for the thumbnail, it will open the javascript iframe pop up.&lt;/li&gt;
&lt;li&gt;At the landing page, the snippet should be called again with its own docID:&lt;br /&gt;[ ! easy2? &amp;amp;landingpage=`__own_ID__` ! ]&lt;/li&gt;
&lt;li&gt;options: document ID. &lt;/li&gt;
&lt;/ul&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;gal_desc=`1`&lt;/strong&gt; (on) to see the Gallery&#039;s information (title &amp;amp; description) above the gallery template.&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;showonly &lt;/strong&gt;- &#039;images&#039; | &#039;folders&#039; under the &amp;amp;gid parameter&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;css &lt;/strong&gt;- path to the customized CSS file&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;js&lt;/strong&gt; - path to the customized javascript file&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;grid_class &lt;/strong&gt;- CSS classname for the gallery grid&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;e2g_currentcrumb_class &lt;/strong&gt;- CSS classname for the current crumb&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;e2gback_class&lt;/strong&gt;&lt;strong&gt; &lt;/strong&gt;- CSS classname for the back button&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;e2gpnums_class&lt;/strong&gt;&lt;strong&gt; &lt;/strong&gt;- CSS classname for the pagination&lt;/p&gt;
&lt;h2&gt;Comments&lt;/h2&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;ecm&lt;/strong&gt; - comments on / off, 1 / 0 correspondingly.&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;ecl&lt;/strong&gt; - Comments per page.&lt;/p&gt;
&lt;h2&gt; Breadcrumbs parameters &lt;/h2&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;crumbs_separator&lt;/strong&gt; - Separator. default is &#039;/&#039;&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;crumbs_showHome&lt;/strong&gt; - This toggles the root crumb to be added to the beginning of the trail. default is 0&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;crumbs_showAsLinks&lt;/strong&gt; - If you want breadcrumbs to be text and not links, set to 0. default is 1&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;crumbs_showCurrent&lt;/strong&gt; - Include the current page at the end of the trail. default is 1&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;crumbs_showPrevious &lt;/strong&gt;- Enabling the previous crumb path from the original &amp;amp;gid call. default is 0&lt;/p&gt;
&lt;div id=&quot;_mcePaste&quot; style=&quot;overflow: hidden; position: absolute; left: -10000px; top: 171px; width: 1px; height: 1px;&quot;&gt;
&lt;h2&gt;Basic call: [!easy2!]&lt;/h2&gt;
&lt;p&gt;Will show all directories and images under assets/gallery folder&lt;/p&gt;
&lt;h2&gt;Main parameters&lt;/h2&gt;
&lt;p&gt;You can have multiple IDs&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;gid&lt;/strong&gt; - id of gallery, from which e2g snippet starts&lt;br /&gt;
default 1 - root.&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;fid &lt;/strong&gt;- id of image for spesific image display&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;rgid &lt;/strong&gt;- gallery id for random thumbnail output&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;showonly&lt;/strong&gt; - images | folders, to show only those spesific type&lt;/p&gt;
&lt;p&gt;Example call: [!easy2?fid=`2,3`!]&lt;br /&gt;Will show images ID# 2 &lt;strong&gt;and &lt;/strong&gt;3&lt;/p&gt;
&lt;p&gt;Example call: [!easy2?gid=`20,57` &amp;amp;showonly=`images`!]&lt;br /&gt;Will show &lt;strong&gt;only &lt;/strong&gt;images from folder ID 20 &lt;strong&gt;and &lt;/strong&gt;57 (excluding directory&#039;s thumbnails).&lt;/p&gt;
&lt;p&gt;Example call: [!easy2?rgid=`102,138`!]&lt;br /&gt;Will show random image from folder ID 102 &lt;strong&gt;and &lt;/strong&gt;138, inside &lt;strong&gt;one &lt;/strong&gt;thumbnail.&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;If you want to apply changes to added images you should clean cache.&lt;/strong&gt;&lt;/p&gt;
&lt;h2&gt;Thumbnail&lt;/h2&gt;
&lt;p&gt;&lt;strong&gt; Folder _thumbnails in root of gallery.&lt;/strong&gt;&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;w&lt;/strong&gt; - thumbnail width, px.&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;h&lt;/strong&gt; - thumbnail height, px.&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;thq&lt;/strong&gt; - Level of jpeg-compression. 0 to 100%. (100 - max)&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;name_len&lt;/strong&gt; - Max length of thumbnail name
&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;cat_name_len&lt;/strong&gt; - Max length of folder name
&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;colls&lt;/strong&gt; - images per column&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;limit&lt;/strong&gt; - images per page.&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;gpn&lt;/strong&gt; - start page number.&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;orderby&lt;/strong&gt; - Sort by: date_added, last_modified, comments, filename, name, random.&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;order&lt;/strong&gt; - Sort ASCENDING or DESCENDING, ASC / DESC correspondingly.&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;cat_orderby&lt;/strong&gt; - Field by which folders will be sorted out: cat_id, cat_name, random.&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;cat_order&lt;/strong&gt; - Sort folders ASCENDING or DESCENDING, ASC / DESC correspondingly&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;resize_type&lt;/strong&gt; - type of thumbnail resizing: &#039;inner&#039; (cropped) | &#039;resize&#039; (autofit) | &#039;shrink&#039; (shrink)&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;thbg_red&lt;/strong&gt; - thumbnail background color: RED in RGB&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;thbg_green&lt;/strong&gt; - thumbnail background color: GREEN in RGB&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;thbg_blue&lt;/strong&gt; - thumbnail background color: BLUE in RGB&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;grid &lt;/strong&gt;- options of thumbnail&#039;s grid arrangement, &#039;css&#039; or &#039;table&#039;
&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;show_group&lt;/strong&gt; - slideshow group name&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;landingpage&lt;/strong&gt; - set the slideshow&#039;s/thumbnail&#039;s landing page.&lt;/p&gt;
&lt;ul&gt;
&lt;li&gt;for the slideshow: if
there is an &#039;open image&#039; pop up, but there are no &amp;amp;landingpage is
set up, then the image will be opened directly inside the page,
replacing the slideshow box.&lt;/li&gt;
&lt;li&gt;for the thumbnail, it will open the javascript iframe pop up.&lt;/li&gt;
&lt;li&gt;At the landing page, the snippet should be called again with its own docID:&lt;br /&gt;[ ! easy2? &amp;amp;landingpage=`__own_ID__` ! ]&lt;/li&gt;
&lt;li&gt;options: document ID. &lt;/li&gt;
&lt;/ul&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;gal_desc=`1`&lt;/strong&gt; (on) to see the Gallery&#039;s information (title &amp;amp; description) above the gallery template.&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;showonly &lt;/strong&gt;- &#039;images&#039; | &#039;folders&#039; under the &amp;amp;gid parameter&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;css &lt;/strong&gt;- path to the customized CSS file&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;js&lt;/strong&gt; - path to the customized javascript file&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;grid_class &lt;/strong&gt;- CSS classname for the gallery grid&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;e2g_currentcrumb_class &lt;/strong&gt;- CSS classname for the current crumb&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;e2gback_class&lt;/strong&gt;&lt;strong&gt; &lt;/strong&gt;- CSS classname for the back button&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;e2gpnums_class&lt;/strong&gt;&lt;strong&gt; &lt;/strong&gt;- CSS classname for the pagination&lt;/p&gt;
&lt;h2&gt;Comments&lt;/h2&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;ecm&lt;/strong&gt; - comments on / off, 1 / 0 correspondingly.&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;ecl&lt;/strong&gt; - Comments per page.&lt;/p&gt;
&lt;h2&gt; Breadcrumbs parameters &lt;/h2&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;crumbs_separator&lt;/strong&gt; - Separator. default is &#039;/&#039;&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;crumbs_showHome&lt;/strong&gt; - This toggles the root crumb to be added to the beginning of the trail. default is 0&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;crumbs_showAsLinks&lt;/strong&gt; - If you want breadcrumbs to be text and not links, set to 0. default is 1&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;crumbs_showCurrent&lt;/strong&gt; - Include the current page at the end of the trail. default is 1&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;crumbs_showPrevious &lt;/strong&gt;- Enabling the previous crumb path from the original &amp;amp;gid call. default is 0&lt;/p&gt;
&lt;/div&gt;',
        'paramhelptitle' => 'Parameters',
        'path' => 'Path',
        'pleaseselectobject' => 'Please select any directory or image',
        'pleaseselectparent' => 'Please select the new directory destination',
        'pluginshelpcontent' => '&lt;h2&gt;Plugins parameters&lt;/h2&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;plugins=`thumb: _pluginname1_ , pluginname2 | gallery: _pluginname3_ , _pluginname4_@custom/index/file.php | landingpage: _pluginname5_`&lt;/strong&gt;&lt;/p&gt;
&lt;p&gt;Originally, plugins only make a new layer above the thumbnail.&lt;br /&gt;But in the displaying process, each of plugin gets all thumbnails&#039; information from the database by their IDs.&lt;/p&gt;
&lt;p&gt;All IDs of files are identified by &lt;strong&gt;fid_&lt;/strong&gt;&lt;em&gt;[file_id]&lt;br /&gt;&lt;/em&gt;All IDs of directories are identified by &lt;strong&gt;gid_&lt;/strong&gt;&lt;em&gt;[gallery_id]&lt;br /&gt;&lt;/em&gt;ID for the landingpage is identified by &lt;strong&gt;fid_&lt;/strong&gt;&lt;em&gt;[file_id]&lt;/em&gt;&lt;/p&gt;
&lt;p&gt;The plugins target the specified objects: &lt;/p&gt;
&lt;ul&gt;
&lt;li&gt;thumb (for image thumbnail), &lt;/li&gt;
&lt;li&gt;gallery (for directory thumbnail), &lt;/li&gt;
&lt;li&gt;and landingpage (for the image in the landing page).&lt;/li&gt;
&lt;/ul&gt;
&lt;p&gt;If the snippet calls more than one target, the pipe symbol ( | ) is used as delimiter of each of target.&lt;/p&gt;
&lt;p&gt;The target call is followed by plugin&#039;s name after a colon, which are separated by comma if there are more than one plugin applied.&lt;/p&gt;
&lt;p&gt;The plugin&#039;s name is followed by a path to a customized index file after an @ (ampersand) sign, if there is any.&lt;/p&gt;',
        'pluginshelptitle' => 'Plugins',
        'pos1' => 'left',
        'pos2' => 'center',
        'pos3' => 'right',
        'pos4' => 'top',
        'pos5' => 'center',
        'pos6' => 'bottom',
        'random' => 'random',
        'refresh' => 'Update',
        'remove_field_btn' => 'Remove',
        'ren_file_err' => 'Could not rename file',
        'renamefile' => 'Change the filename',
        'resize' => 'resize',
        'resize_type' => 'Thumbnail Resize',
        'restore_err' => 'There was an error while trying to restore',
        'restore_suc' => 'Gallery&#039;s names restored',
        'rez_file_err' => 'Could not resize file',
        'save' => 'Save',
        'search' => 'Search',
        'send_btn' => 'Post',
        'shrink' => 'shrink',
        'size' => 'Size',
        'slideshowshelpcontent' => '&lt;h2&gt;Slideshow parameters&lt;/h2&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;slideshow&lt;/strong&gt; - slideshow types&lt;br /&gt;Default options:&lt;/p&gt;
&lt;ul&gt;
&lt;li&gt;simple&lt;/li&gt;
&lt;li&gt;galleriffic&lt;/li&gt;
&lt;li&gt;smoothgallery&lt;/li&gt;
&lt;li&gt;galleryview&lt;/li&gt;
&lt;li&gt;contentflow&lt;/li&gt;
&lt;/ul&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;ss_config&lt;/strong&gt; - custom configuration/options for each of slideshow types.&lt;br /&gt; The implementation is up to the developer to use it inside the slideshow&#039;s index file.&lt;/p&gt;
&lt;p&gt;Default options: &lt;/p&gt;
&lt;ul&gt;
&lt;li&gt;galleriffic 
&lt;ul&gt;
&lt;li&gt;example-1&lt;/li&gt;
&lt;li&gt;example-2&lt;/li&gt;
&lt;li&gt;example-3&lt;/li&gt;
&lt;li&gt;example-5&lt;/li&gt;
&lt;/ul&gt;
&lt;/li&gt;
&lt;li&gt;smoothgallery 
&lt;ul&gt;
&lt;li&gt;fullgallery&lt;/li&gt;
&lt;li&gt;galleryset&lt;/li&gt;
&lt;li&gt;timedgallery&lt;/li&gt;
&lt;li&gt;simpletimedslideshow&lt;/li&gt;
&lt;li&gt;simpleshowcaseslideshow&lt;/li&gt;
&lt;li&gt;timedimageswitchers&lt;/li&gt;
&lt;li&gt;slidingtransition&lt;/li&gt;
&lt;li&gt;horcontinuous&lt;/li&gt;
&lt;li&gt;vercontinuous&lt;/li&gt;
&lt;li&gt;zoom&lt;/li&gt;
&lt;/ul&gt;
&lt;/li&gt;
&lt;li&gt;galleryview 
&lt;ul&gt;
&lt;li&gt;gallerylight&lt;br /&gt;[!easy2? &amp;amp;slideshow=`galleryview`
        &amp;amp;ss_config=`gallerylight` &amp;amp;w=`100` &amp;amp;h=`100` &amp;amp;ss_w=`600`
    &amp;amp;ss_h=`400` !]&lt;/li&gt;
&lt;li&gt;gallerydark&lt;br /&gt;[! easy2? &amp;amp;slideshow=`galleryview`
        &amp;amp;ss_config=`gallerydark` &amp;amp;w=`30` &amp;amp;h=`30` &amp;amp;ss_w=`600`
    &amp;amp;ss_h=`400` !]&lt;/li&gt;
&lt;li&gt;topfilmstrip&lt;br /&gt;[! easy2? &amp;amp;slideshow=`galleryview`
        &amp;amp;ss_config=`topfilmstrip` &amp;amp;w=`100` &amp;amp;h=`38` &amp;amp;ss_w=`700`
    &amp;amp;ss_h=`400` !]&lt;/li&gt;
&lt;li&gt;polaroid&lt;br /&gt;[! easy2? &amp;amp;slideshow=`galleryview`
        &amp;amp;ss_config=`polaroid` &amp;amp;w=`114` &amp;amp;h=`110` &amp;amp;ss_w=`469`
    &amp;amp;ss_h=`452` !]&lt;/li&gt;
&lt;li&gt;filmstrip&lt;br /&gt;[! easy2? &amp;amp;slideshow=`galleryview`
    &amp;amp;ss_config=`filmstrip` &amp;amp;w=`100` &amp;amp;h=`100` !]&lt;/li&gt;
&lt;li&gt;panel&lt;br /&gt;[! easy2? &amp;amp;slideshow=`galleryview`
    &amp;amp;ss_config=`panel` &amp;amp;ss_w=`600` &amp;amp;ss_h=`300` !]&lt;/li&gt;
&lt;/ul&gt;
&lt;/li&gt;
&lt;li&gt;contentflow 
&lt;ul&gt;
&lt;li&gt;default&lt;/li&gt;
&lt;/ul&gt;
&lt;/li&gt;
&lt;/ul&gt;
&lt;p&gt;The typical snippet call of using one of these slideshows is&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;[!easy2?slideshow=`smoothgallery` &amp;amp;ss_config=`fullgallery`!]&lt;/strong&gt;&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&lt;br /&gt;&lt;/strong&gt;&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;ss_indexfile&lt;/strong&gt;&lt;br /&gt; Easy 2 Gallery 1.4.0 calls the slideshow file from &lt;strong&gt;assets/modules/easy2/slideshows/&#039;.$slideshow.&#039;/&#039;.$slideshow.&#039;.php&lt;/strong&gt;&lt;br /&gt; You can create that file elsewhere, and use this parameter to go to that file instead.&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;ss_w&lt;/strong&gt; - slideshow width box, default = 400 (in px)&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;ss_h&lt;/strong&gt; - slideshow height box, default = 300 (in px)&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;ss_bg&lt;/strong&gt; - box background color, default = white&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;ss_allowedratio&lt;/strong&gt;
- allowed width/height ratio of image dimension inside slideshow box,
default 0.75*(&amp;amp;ss_w/&amp;amp;ss_h) - 1.25*(&amp;amp;ss_w/&amp;amp;ss_h).&lt;br /&gt;     Options:&lt;/p&gt;
&lt;ul&gt;
&lt;li&gt;&#039;minimumfloatnumber-maximumfloatnumber&#039;, eg: `1.0-2.0`&lt;/li&gt;
&lt;li&gt;&#039;none&#039; - disable this ratio, all landscape/portrait images will be shown&lt;/li&gt;
&lt;/ul&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;ss_limit&lt;/strong&gt; - to set how many images the slide show should retrieve from the gallery ID.&lt;br /&gt; More images mean longer page loading!&lt;br /&gt; Options&amp;nbsp;: int | &#039;none&#039;&lt;br /&gt; Default&amp;nbsp;: (int)6 &lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;ss_css&lt;/strong&gt; - set the slideshow&#039;s CSS path. &lt;/p&gt;
&lt;p&gt;&lt;strong&gt;&amp;amp;ss_js&lt;/strong&gt; - set the slideshow&#039;s JS path. &lt;/p&gt;
&lt;p&gt;&amp;nbsp;&lt;/p&gt;
&lt;hr /&gt;
&lt;p&gt;
Example call: &lt;strong&gt;[!easy2? &amp;amp;slideshow=`simple`!]&lt;/strong&gt;&lt;br /&gt;Will show slideshow with all images under root folder.
&lt;/p&gt;
&lt;p&gt;Example call: &lt;strong&gt;[!easy2? &amp;amp;slideshow=`simple` &amp;amp;gid=`2,4` &amp;amp;ss_w=`600` &amp;amp;ss_h=`200` &amp;amp;ss_allowedratio=`1.0-2.0`!]&lt;/strong&gt;&lt;br /&gt;
Will show slideshow with all images under folder ID 2 and 4, with box
dimension 600x200px, with image ratio allowance (width/height) is
between 1.0 to 2.0 (get the limited landscape images).&lt;/p&gt;
&lt;p&gt;Images height will be adjusted automatically.&lt;/p&gt;
&lt;p&gt;&amp;nbsp;&lt;/p&gt;
&lt;hr /&gt;
&lt;p&gt;&amp;nbsp;&lt;/p&gt;
&lt;h2&gt;Create New Slideshow&lt;/h2&gt;
&lt;p&gt;&lt;a target=&quot;_blank&quot; href=&quot;http://modxcms.com/forums/index.php/topic,49266.msg290825.html#msg290825&quot;&gt;http://modxcms.com/forums/index.php/topic,49266.msg290825.html#msg290825&lt;/a&gt;&lt;/p&gt;',
        'slideshowshelptitle' => 'Slideshows',
        'synchro' => 'Synchronize',
        'synchro_err' => 'There was an error while trying to synchronize',
        'synchro_suc' => 'Gallery synchronized',
        'table' => 'table',
        'tags' => 'Tags',
        'text' => 'text',
        'thbg_rgb' => 'Thumbnail background color',
        'thq' => 'Compression level',
        'thumb' => 'thumbnail',
        'thumbcnt' => 'Display settings',
        'thumbscfg' => 'Thumbnails settings',
        'tofolder' => 'to folder',
        'tpl' => 'Templates',
        'tplhelpcontent' => '&lt;h2&gt; Templates parameters &lt;/h2&gt;
&lt;p&gt;&lt;strong&gt;glib&lt;/strong&gt; - javascript library&lt;br /&gt;
Default: highslide
&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;css&lt;/strong&gt; - CSS style&lt;br /&gt;
chunk name or path to file&lt;br /&gt;
Default: assets/modules/easy2/templates/default/style.css
&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;tpl&lt;/strong&gt; - gallery template&lt;br /&gt;
chunk name or path to file&lt;br /&gt;
Default: assets/modules/easy2/templates/gallery.htm&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;dir_tpl&lt;/strong&gt; - folder template&lt;br /&gt;
chunk name or path to file.&lt;br /&gt;
Default: assets/modules/easy2/templates/directory.htm
&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;thumb_tpl&lt;/strong&gt; - thumbnail template.&lt;br /&gt;
chunk name or path to file.&lt;br /&gt;
Default: assets/modules/easy2/templates/thumbnail.htm
&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;rand_tpl&lt;/strong&gt; - random pic template&lt;br /&gt;
chunk name or path to file.&lt;br /&gt;
Default: assets/modules/easy2/templates/random_thumbnail.htm
&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;page_tpl&lt;/strong&gt; - landing page template&lt;br /&gt;
chunk name or path to file.&lt;br /&gt;
Default: assets/modules/easy2/templates/page.htm&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;comments_tpl&lt;/strong&gt; - comments template.&lt;br /&gt;
chunk name or path to file, relative to file comments.easy2gallery.php.&lt;br /&gt;
Default: assets/modules/easy2/templates/comments.htm
&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;comments_row_tpl&lt;/strong&gt; - comments row template.&lt;br /&gt;
chunk name or path to file, relative to file comments.easy2gallery.php.&lt;br /&gt;
Default: assets/modules/easy2/templates/comments_row.htm
&lt;/p&gt;
&lt;p&gt;&amp;nbsp;&lt;/p&gt;
&lt;h2&gt; Placeholders description &lt;/h2&gt;
&lt;h3&gt; gallery &lt;/h3&gt;
&lt;p&gt;&lt;strong&gt;[+easy2:permalink+]&lt;/strong&gt; - permalink to the gallery grid&lt;br /&gt;&lt;strong&gt;[+easy2:cat_name+]&lt;/strong&gt; - current folder name&lt;br /&gt;
&lt;strong&gt;[+easy2:back+]&lt;/strong&gt; - link back to parent&lt;br /&gt;
&lt;strong&gt;[+easy2:content+]&lt;/strong&gt; - content&lt;br /&gt;
&lt;strong&gt;[+easy2:pages+]&lt;/strong&gt; - pagination&lt;/p&gt;
&lt;h3&gt; Folders &lt;/h3&gt;
&lt;p&gt;&lt;strong&gt;[+easy2:cat_name+]&lt;/strong&gt; - folder name&lt;br /&gt;
&lt;strong&gt;[+easy2:cat_id+]&lt;/strong&gt; - folder id&lt;br /&gt;
&lt;strong&gt;[+easy2:parent_id+]&lt;/strong&gt; - parent folder id&lt;br /&gt;
&lt;strong&gt;[+easy2:cat_level+]&lt;/strong&gt; - level&lt;br /&gt;
&lt;strong&gt;[+easy2:count+]&lt;/strong&gt; - number of files (new)
&lt;/p&gt;
&lt;p&gt;and all thumbnail placeholders
&lt;/p&gt;
&lt;h3&gt; Thumbnails &lt;/h3&gt;
&lt;p&gt;&lt;strong&gt;[+easy2:src+]&lt;/strong&gt; - path to thumbnail&lt;br /&gt;
&lt;strong&gt;[+easy2:w+]&lt;/strong&gt; - thumb&#039;s width&lt;br /&gt;
&lt;strong&gt;[+easy2:h+]&lt;/strong&gt; - thumb&#039;s height&lt;br /&gt;
&lt;strong&gt;[+easy2:id+]&lt;/strong&gt; - id of image&lt;br /&gt;
&lt;strong&gt;[+easy2:name+]&lt;/strong&gt; - image name (if &amp;gt; name_len, that lenght = name_len-2)&lt;br /&gt;
&lt;strong&gt;[+easy2:title+]&lt;/strong&gt; - title (full name)&lt;br /&gt;
&lt;strong&gt;[+easy2:description+]&lt;/strong&gt; - image description&lt;br /&gt;
&lt;strong&gt;[+easy2:filename+]&lt;/strong&gt; - image filename&lt;br /&gt;
&lt;strong&gt;[+easy2:size+]&lt;/strong&gt; - size of file (bytes)&lt;br /&gt;
&lt;strong&gt;[+easy2:comments+]&lt;/strong&gt; - comments total for image&lt;br /&gt;
&lt;strong&gt;[+easy2:date_added+]&lt;/strong&gt; - date added&lt;br /&gt;
&lt;strong&gt;[+easy2:last_modified+]&lt;/strong&gt; - last modified date&lt;br /&gt;
&lt;strong&gt;[+easy2:dir_id+]&lt;/strong&gt; - folder id&lt;/p&gt;
&lt;h3&gt; Page&lt;/h3&gt;
&lt;p&gt;for landing page
&lt;/p&gt;
&lt;p&gt;
&lt;strong&gt;[+easy2:src+]&lt;/strong&gt; - path to thumbnail&lt;br /&gt;
&lt;strong&gt;[+easy2:name+]&lt;/strong&gt; - image name (if &amp;gt; name_len, that lenght = name_len-2)&lt;br /&gt;
&lt;strong&gt;[+easy2:title+]&lt;/strong&gt; - title (if there is no alias, it&#039;ll use name)&lt;br /&gt;
&lt;strong&gt;[+easy2:description+]&lt;/strong&gt; - image description&lt;/p&gt;
&lt;h3&gt; Comments (row) &lt;/h3&gt;
&lt;p&gt;&lt;strong&gt;[+easy2:id+]&lt;/strong&gt; - comment id&lt;br /&gt;
&lt;strong&gt;[+easy2:file_id+]&lt;/strong&gt; - file id&lt;br /&gt;
&lt;strong&gt;[+easy2:author+]&lt;/strong&gt; - author&#039;s name&lt;br /&gt;
&lt;strong&gt;[+easy2:email+]&lt;/strong&gt; - author&#039;s email&lt;br /&gt;
&lt;strong&gt;[+easy2:name_w_mail+]&lt;/strong&gt; - if email is set &quot;&amp;lt;a href=&quot;mailto:[+easy2:email+]&quot;&amp;gt;[+easy2:author+]&amp;lt;/a&amp;gt;&quot;, else &quot;[+easy2:author+]&quot;&lt;br /&gt;
&lt;strong&gt;[+easy2:comment+]&lt;/strong&gt; - comment&lt;br /&gt;
&lt;strong&gt;[+easy2:date_added+]&lt;/strong&gt; - comment date&lt;br /&gt;
&lt;strong&gt;[+easy2:last_modified+]&lt;/strong&gt; - comment last modified date&lt;/p&gt;
&lt;h3&gt; Comments (page) &lt;/h3&gt;
&lt;p&gt;
&lt;strong&gt;[+easy2:title+]&lt;/strong&gt; - pagetitle (from includes/langs/*.comments.php)&lt;br /&gt;
&lt;strong&gt;[+easy2:body+]&lt;/strong&gt; - comments&lt;br /&gt;
&lt;strong&gt;[+easy2:pages+]&lt;/strong&gt; - pagination links&lt;br /&gt;
+ language specific settings includes/langs/*.comments.php&lt;/p&gt;',
        'tplhelptitle' => 'Templates',
        'translation' => 'translation',
        'type' => 'type',
        'type_err' => 'Restricted type of file',
        'uim_preview' => 'Preview',
        'uim_preview_err' => 'Preview&lt;br /&gt;not available',
        'unignore' => 'Unignore',
        'update_err' => 'changes not saved',
        'updated' => 'saved',
        'upload' => 'Image Upload',
        'upload_btn' => 'Upload',
        'upload_dir' => 'Upload image in folder',
        'upload_err' => 'Could not upload file',
        'userecomment' => 'Comment',
        'useremail' => 'Email',
        'username' => 'Name',
        'valid_extensions' => 'Valid extensions are',
        'w' => 'Width',
        'withselected' => 'With selected',
        'wm' => 'Watermarks',
        'wmpos1' => 'Horizontal position',
        'wmpos2' => 'Vertical position',
        'wmt' => 'text/path',
        'yes' => 'yes',
        'zip_foldername' => 'Zip&#039;s name will be used as the new folder name.&lt;br /&gt;
            Make sure there is no other same folder&#039;s name inside current directory, or the uploading will overwrite the existing folder.&lt;br /&gt;
            This name will also be used as the URL (web address).',
        'zip_select_none' => 'Please select any directory or file to be downloaded',
);
?>