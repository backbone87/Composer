;(function($, undef) {
window.addEvent("domready", function() {
	var container = $("ccc_check"),
		submit = container.getElements("input[type=submit]"),
		inputs = container.getElements("input.ccc_check_confirm"),
		checkConfirm;
	
	(checkConfirm = function() {
		submit.set("disabled", inputs.every(function(input) { return input.get("checked"); }) ? undef : "disabled");
	})();
	
	inputs.length && container.addEvent("change", checkConfirm);
});
})(document.id);