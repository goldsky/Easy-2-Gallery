<?php
if (IN_MANAGER_MODE != 'true') die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
?>
<h2> Templates parameters </h2>
<p><strong>glib</strong> - javascript library<br />
Default: highslide
</p>
<p><strong>css</strong> - CSS style<br />
chunk name or path to file<br />
Default: assets/modules/easy2/templates/style.css
</p>
<p><strong>tpl</strong> - gallery template<br />
chunk name or path to file<br />
Default: assets/modules/easy2/templates/gallery.htm
</p>
<p><strong>dir_tpl</strong> - folder template<br />
chunk name or path to file.<br />
Default: assets/modules/easy2/templates/directory.htm
</p>
<p><strong>thumb_tpl</strong> - thumbnail template.<br />
chunk name or path to file.<br />
Default: assets/modules/easy2/templates/thumbnail.htm
</p>
<p><strong>rand_tpl</strong> - random pic template<br />
chunk name or path to file.<br />
Default: assets/modules/easy2/templates/random_thumbnail.htm
</p>
<p><strong>comments_tpl</strong> - comments template.<br />
chunk name or path to file, relative to file comments.easy2gallery.php.<br />
Default: assets/modules/easy2/templates/comments.htm
</p>
<p><strong>comments_row_tpl</strong> - comments row template.<br />
chunk name or path to file, relative to file comments.easy2gallery.php.<br />
Default: assets/modules/easy2/templates/comments_row.htm
</p>
<p>&nbsp;</p>
<h2> Placeholders description </h2>
<h3> gallery </h3>
<p><strong>[+easy2:cat_name+]</strong> - current folder name<br />
<strong>[+easy2:back+]</strong> - link back to parent<br />
<strong>[+easy2:content+]</strong> - content<br />
<strong>[+easy2:pages+]</strong> - pagination</p>
<h3> Folders </h3>
<p><strong>[+easy2:cat_name+]</strong> - folder name<br />
<strong>[+easy2:cat_id+]</strong> - folder id<br />
<strong>[+easy2:parent_id+]</strong> - parent folder id<br />
<strong>[+easy2:cat_level+]</strong> - level<br />
<strong>[+easy2:count+]</strong> - number of files (new)
</p>
<p>and all thumbnail placeholders
</p>
<h3> Thumbnails </h3>
<p><strong>[+easy2:src+]</strong> - path to thumbnail<br />
<strong>[+easy2:w+]</strong> - thumb's width<br />
<strong>[+easy2:h+]</strong> - thumb's height<br />
<strong>[+easy2:id+]</strong> - id of image<br />
<strong>[+easy2:name+]</strong> - image name (if &gt; name_len, that lenght = name_len-2)<br />
<strong>[+easy2:title+]</strong> - title (full name)<br />
<strong>[+easy2:description+]</strong> - image description<br />
<strong>[+easy2:filename+]</strong> - image filename<br />
<strong>[+easy2:size+]</strong> - size of file (bytes)<br />
<strong>[+easy2:comments+]</strong> - comments total for image<br />
<strong>[+easy2:date_added+]</strong> - date added<br />
<strong>[+easy2:last_modified+]</strong> - last modified date<br />
<strong>[+easy2:dir_id+]</strong> - folder id</p>
<h3> Comments (row) </h3>
<p><strong>[+easy2:id+]</strong> - comment id<br />
<strong>[+easy2:file_id+]</strong> - file id<br />
<strong>[+easy2:author+]</strong> - author's name<br />
<strong>[+easy2:email+]</strong> - author's email<br />
<strong>[+easy2:name_w_mail+]</strong> - if email is set "&lt;a href="mailto:[+easy2:email+]"&gt;[+easy2:author+]&lt;/a&gt;", else "[+easy2:author+]"<br />
<strong>[+easy2:comment+]</strong> - comment<br />
<strong>[+easy2:date_added+]</strong> - comment date<br />
<strong>[+easy2:last_modified+]</strong> - comment last modified date</p>
<h3> Comments (page) </h3>
<p>
<strong>[+easy2:title+]</strong> - pagetitle (from langs/*.comments.php)<br />
<strong>[+easy2:body+]</strong> - comments<br />
<strong>[+easy2:pages+]</strong> - pagination links<br />
+ language specific settings langs/*.comments.php</p>