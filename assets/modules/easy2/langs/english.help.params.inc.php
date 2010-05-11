<?php
if (IN_MANAGER_MODE != 'true') die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
?>
<p>Basic call: [!easy2!]<br />Will show all directories and images under assets/gallery folder</p>
<h2>Main parameters</h2>
<p>You can have multiple IDs</p>
<p><strong>gid</strong> - id of gallery, from which e2g snippet starts<br />
default 1 - root.</p>
<p><strong>fid </strong>- id of image for spesific image display</p>
<p><strong>rgid </strong>- gallery id for random thumbnail output</p>
<p><strong>showonly</strong> - images | folders, to show only those spesific type</p>
<p>Example call: [!easy2?fid=`2,3`!]<br />Will show images ID# 2 <strong>and </strong>3</p>
<p>Example call: [!easy2?gid=`20,57` &amp;showonly=`images`!]<br />Will show <strong>only </strong>images from folder ID 20 <strong>and </strong>57 (excluding directory's thumbnails).</p>
<p>Example call: [!easy2?rgid=`102,138`!]<br />Will show random image from folder ID 102 <strong>and </strong>138, inside <strong>one </strong>thumbnail.</p>
<p><strong>If you want to apply changes to added images you should clean cache.</strong></p>
<h2>Thumbnail</h2>
<p><strong> Folder _thumbnails in root of gallery.</strong></p>
<p><strong>w</strong> - thumbnail width, px.</p>
<p><strong>h</strong> - thumbnail height, px.</p>
<p><strong>thq</strong> - Level of jpeg-compression. 0 to 100%. (100 - max)</p>
<p><strong>name_len</strong> - Max length of thumbnail name
</p>
<p><strong>cat_name_len</strong> - Max length of folder name
</p>
<p><strong>colls</strong> - images per column</p>
<p><strong>limit</strong> - images per page.</p>
<p><strong>gpn</strong> - start page number.</p>
<p><strong>orderby</strong> - Sort by: date_added, last_modified, comments, filename, name, random.</p>
<p><strong>order</strong> - Sort ASCENDING or DESCENDING, ASC / DESC correspondingly.</p>
<p><strong>cat_orderby</strong> - Field by which folders will be sorted out: cat_id, cat_name, random.</p>
<p><strong>cat_order</strong> - Sort folders ASCENDING or DESCENDING, ASC и DESC correspondingly</p>
<p><strong>notables</strong> - use &lt;div&gt; instead &lt;table&gt;
</p>
<p><strong>show_group</strong> - slideshow group name
</p>
<h2>Comments</h2>
<p><strong>ecm</strong> - comments on / off, 1 / 0 correspondingly.</p>
<p><strong>ecl</strong> - Comments per page.</p>
<h2> Breadcrumbs parameters </h2>
<p><strong>crumbs_separator</strong> - Separator. default is '/'</p>
<p><strong>crumbs_showHome</strong> - This toggles the root crumb to be added to the beginning of the trail. default is 0</p>
<p><strong>crumbs_showAsLinks</strong> - If you want breadcrumbs to be text and not links, set to 0. default is 1</p>
<p><strong>crumbs_showCurrent</strong> - Include the current page at the end of the trail. default is 1</p>
<h2>Slideshow parameters</h2>
<p><strong>slideshow</strong> - slideshow types<br />Default options:</p>
<ul>
<li>simple</li>
<li>galleriffic</li>
<li>smoothgallery</li>
<li>galleryview</li>
<li>contentflow</li>
</ul>
<p><strong>ss_config</strong> - custom configuration/options for each of slideshow types.<br />
The implementation is up to the developer to use it inside the slideshow's index file.</p>
<p>Default options:
</p>
<ul>
<li>galleriffic
<ul>
<li>example-1</li>
<li>example-2</li>
<li>example-3</li>
<li>example-5</li>
</ul>
</li>
<li>smoothgallery
<ul>
<li>fullgallery</li>
<li>galleryset</li>
<li>timedgallery</li>
<li>simpletimedslideshow</li>
<li>simpleshowcaseslideshow</li>
<li>timedimageswitchers</li>
<li>slidingtransition</li>
<li>horcontinuous</li>
<li>vercontinuous</li>
<li>zoom</li>
</ul>
</li>
<li>galleryview
<ul>
<li>gallerylight</li>
<li>gallerydark</li>
<li>topfilmstrip</li>
<li>polaroid</li>
<li>filmstrip</li>
<li>panel</li>
</ul>
</li>
<li>contentflow
<ul>
<li>default</li>
</ul>
</li>
</ul>
<p><strong>ss_indexfile</strong><br />
Easy 2 Gallery 1.4.0 calls the slideshow file from E2G_SNIPPET_PATH.'includes/slideshow/'.$slideshow.'/'.$slideshow.'.php'.<br />
You can create that file elsewhere, and use this parameter to go to that file instead.</p>
<p><strong>ss_w</strong> - slideshow width box, default = 400 (in px)</p>
<p><strong>ss_h</strong> - slideshow height box, default = 300 (in px)</p>
<p><strong>ss_bg</strong> - box background color, default = white</p>
<p><strong>ss_allowedratio</strong> - allowed width/height ratio of image dimension inside slideshow box, default 0.75*(&amp;ss_w/&amp;ss_h) - 1.25*(&amp;ss_w/&amp;ss_h).<br />
    Options:</p>
<ul>
<li>'minimumfloatnumber-maximumfloatnumber', eg: `1.0-2.0`</li>
<li>'none' - disable this ratio, all landscape/portrait images will be shown</li>
</ul>
<p><strong>ss_limit</strong> - to set how many images the slide show should retrieve from the gallery ID.<br />
More images mean longer page loading!<br />
Options&nbsp;: int | 'none'<br />
Default&nbsp;: (int)6
</p>
<p><strong>ss_css</strong> - set the slideshow's CSS path.
</p>
<p><strong>ss_js</strong> - set the slideshow's JS path.
</p>
<p><strong>landingpage</strong> - set the slideshow's landing page.</p>
<ul>
<li>If there is an 'open image' pop up, but there are no &amp;landingpage is set up, then the image will be opened directly inside the page, replacing the slideshow box.</li>
<li>At the landing page, the snippet should be called again with its own docID:<br />[ ! easy2? &amp;landingpage=`__own_ID__` ! ]</li>
<li>options: document ID.
</li>
</ul>
<p>Example call: [!easy2? &amp;slideshow=`simple`!]<br />Will show slideshow with all images under root folder.</p>
<p>Example call: [!easy2? &amp;slideshow=`simple` &amp;gid=`2,4` &amp;ss_w=`600` &amp;ss_h=`200` &amp;ss_allowedratio=`1.0-2.0`!]<br />
Will show slideshow with all images under folder ID 2 and 4, with box dimension 600x200px, with image ratio allowance (width/height) is between 1.0 to 2.0 (get the limited landscape images).</p>
<p>Images height will be adjusted automatically.</p>
<hr />
<h2>Nested easy2 with MODx API</h2>
<p><strong>customgetparams </strong>- to add custom &amp;_GET parameter into <strong>pagination </strong>to be nested with other snippets.</p>
<p>Example:</p>
<p>$select = 'SELECT * FROM `your_table` WHERE id='.$param1;<br />$query = mysql_query($select);<br />$row = mysql_fetch_array($query);</p>
<p>$easy2pictures = $modx-&gt;runSnippet("easy2",array(<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; "gid"=&gt;$row["yourGalleryIdFieldInsideAnotherTable"]<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ,"customgetparams"=&gt;'&amp;param1='.$_GET['param1'].'&amp;param2='.$_GET['param2']<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ));</p>
<p>echo $easy2pictures;</p>
<p>The pagination hyperlink will be like:<br />http://your-website/index.php?id=4<strong>&amp;param1</strong>=string<strong>&amp;param2</strong>=string&amp;gid=2&amp;gpn=1</p>