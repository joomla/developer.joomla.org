window.addEvent('domready', function() {
	$$('.hasTip').each(function(el) {
		var title = el.get('title');
		if (title) {
			var parts = title.split('::', 2);
			el.store('tip:title', parts[0]);
			el.store('tip:text', parts[1]);
		}
	});
	var JTooltips = new Tips($$('.hasTip'), {
		maxTitleChars : 50,
		fixed : false
	});
});

window.addEvent('domready', function() {
	var myGraph = new mooBarGraph({
		container: $('myGraph'),
		data: graphData,
		width: 1000,
		height: 1000
	});
});


window.addEvent('domready', function(){
var nativeColorUi = false;
if (Browser.opera && (Browser.version >= 11.5)) {
	nativeColorUi = true;
}
$$('.input-colorpicker').each(function(item){
	if (nativeColorUi) {
		item.type = 'color';
	} else {
		new MooRainbow(item, {
			id: item.id,
			imgPath: '" . JURI::root(true) . "/media/system/images/mooRainbow/',
			onComplete: function(color) {
				this.element.value = color.hex;
			},
			startColor: item.value.hexToRgb(true) ? item.value.hexToRgb(true) : [0, 0, 0]
		});
	}
});
});

window.addEvent("domready", function(){ keepAlive.periodical(3600000); });

(function($){
	$(document).ready(function() {
		// Handler for .ready() called.
		var tab = $('<li class=" active"><a href="#page-site" data-toggle="tab">Site</a></li>');
		$('#myTabTabs').append(tab);
	});
})(jQuery);

(function ($){
	$(document).ready(function (){
		var barchart = new $.JQPLOTBarchart('chart2','chart2');
	});
})(jQuery);