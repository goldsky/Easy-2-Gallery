jQuery(function($) {
	$("a[rel^='lightbox']").colorbox({photo:true});
	$(".iframe").colorbox({width:400, height:270, iframe:true, opacity:0.3});
});