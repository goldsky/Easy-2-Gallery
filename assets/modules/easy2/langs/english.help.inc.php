<?php
if (IN_MANAGER_MODE != 'true') die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
?>
<p>Use this snippet call: [!easy2!]</p>

<p>Here's a list of <b>main parameters</b> </p>

<p><b>gid</b> - id of gallery, from which e2g snippet starts<br />
default 1 - root.</p>

<p><b>w</b> - thumbnail width, px.</p>

<p><b>h</b> - thumbnail height, px.</p>

<p><b>If you want to apply changes to added images you should clean cache.<br /> Folder _thumbnails in root of gallery.</b></p>

<p><b>thq</b> - Level of jpeg-compression. 0 to 100%. (100 - max)</p>

<p><b>colls</b> - images per column</p>

<p><b>limit</b> - images per page.</p>
<p><b>gpn</b> - start page number.</p>
<p><b>ecm</b> - comments on / off, 1 / 0 correspondingly.</p>
<p><b>ecl</b> - Comments per page.</p>
<p><b>orderby</b> - Sort by: date_added, last_modified, comments, filename, name, random.</p>
<p><b>order</b> - Sort ASCENDING or DESCENDING, ASC / DESC correspondingly.</p>

<p>All parameters can be combined.<br />
Example call: [!easy2?gid=7 &w=200 &h=180 &thq=90!]</p>