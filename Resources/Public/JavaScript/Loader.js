// reload javascripts every frequency seconds
var frequency = 3600,
	timestamp = Math.round(new Date().getTime() / 1000 / frequency),
	loadJs = function(file){
		document.write('<sc' + 'ript type="text/javascript" src="../typo3conf/ext/contentstage/Resources/Public/JavaScript/' + file + '?' + timestamp + '"></script>');
	};

loadJs('jquery-1.8.3.min.js');
document.write('<sc' + 'ript type="text/javascript">$j=jQuery.noConflict();</script>');
loadJs('jquery.treeview.js');
loadJs('jquery.tmpl.min.js');
loadJs('iLightbox.js');
loadJs('Main.js');
loadJs('Compare.js');
