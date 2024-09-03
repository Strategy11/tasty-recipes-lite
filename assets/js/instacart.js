(function (d, s, id) {
	var js, fjs = d.getElementsByTagName(s)[0];
	if (d.getElementById(id)) { return; }
	js = d.createElement(s);
	js.id = id;
	js.src = "https://widgets.instacart.com/widget-bundle-v2.js";
	js.async = true;
	js.dataset.source_origin = "affiliate_hub";
	fjs.parentNode.insertBefore(js, fjs);
}) (document, "script", "standard-instacart-widget-v1");
