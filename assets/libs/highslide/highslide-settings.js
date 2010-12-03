hs.graphicsDir = "assets/libs/highslide/graphics/";
hs.showCredits = false;
hs.outlineType = "rounded-white";
hs.allowSizeReduction = false;
hs.outlineWhileAnimating = true;
hs.captionEval = "this.a.title";
hs.align = "center";
// Add the controlbar
if (hs.addSlideshow) hs.addSlideshow({
	slideshowGroup: 'mygroup',
	interval: 5000,
	repeat: false,
	useControls: true,
	fixedControls: 'fit',
	overlayOptions: {
		opacity: .6,
		position: 'bottom center',
		hideOnMouseOut: true
	}
});