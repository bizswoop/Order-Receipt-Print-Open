jQuery(function($) {
	/* global _ZPRINT_TEMPLATES_ */
	var $form = $("#location_form");

	var templatesFormats = _ZPRINT_TEMPLATES_.formats;
	var templatesSettings = _ZPRINT_TEMPLATES_.settings;

	$("#size").on("change", function(e) {
		var currentSize = e.target.value;
		var dataClasses = $.map($(e.target).find("option"), function(el) {
			return "sz-" + el.value;
		}).join(" ");
		$form.removeClass(dataClasses);
		$form.addClass("sz-" + currentSize);
	}).trigger("change");

	$("#template").on("change", function(e) {
		var currentTemplate = e.target.value;
		var templateSettings = templatesSettings[currentTemplate];
		var dataClasses = $.map($(e.target).find("option"), function(el) {
			return "tmpl-" + el.value;
		}).join(" ");
		$form.removeClass(dataClasses);
		$form.addClass("tmpl-" + currentTemplate);

		$("#formatBox").attr("hidden", "hidden");
		if (templatesFormats[currentTemplate]) {
			$("#formatBox").removeAttr("hidden");
		}

		$("#format option").attr("hidden", "hidden");
		$("#format option").each(function() {
			if (templatesFormats[currentTemplate] && templatesFormats[currentTemplate].indexOf($(this).val()) >= 0)
				$(this).removeAttr("hidden");
		});

		$("#custom_options").attr("hidden", "hidden");
		$("#custom_options .content").html("");
		var custom = $("#template-" + currentTemplate);
		if (custom.length > 0) {
			$("#custom_options").removeAttr("hidden");
			$("#custom_options .content").html(custom.html());
		}

		$(".templateSetting").attr("hidden", "hidden");
		$.each(templateSettings, function(group, labels) {
			$.each(labels, function(label, value) {
				if (value) $(".templateSetting[data-group=" + group + "][data-label=" + label + "]").removeAttr("hidden");
			});
		});
	}).trigger("change");

	$("#format").on("change", function(e) {
		if (e.target.value === "plain") {
			$("#symbolsLengthContainer, #printSymbolsDebugContainer").removeAttr("hidden");
			$("#symbolsLengthContainer input, #printSymbolsDebugContainer input").removeAttr("disabled");

			$("#fontContainer").attr("hidden", "hidden");
			$("#fontContainer input").attr("disabled", "disabled");
		} else {
			$("#symbolsLengthContainer, #printSymbolsDebugContainer").attr("hidden", "hidden");
			$("#symbolsLengthContainer input, #printSymbolsDebugContainer input").attr("disabled", "disabled");

			$("#fontContainer").removeAttr("hidden");
			$("#fontContainer input").removeAttr("disabled");
		}
	}).trigger("change");

	$("#width").on("change", function(e) {
		if (e.target.value > 0) {
			$("#height_field").removeAttr("hidden");
		} else if (e.target.value === 0) {
			$("#height_field").attr("hidden", "hidden");
		} else {
			$("#width").attr("value", 0);
		}
	}).trigger("change");

	$("#custom_margins").on("change", function() {
		if ($(this).is(":checked")) {
			$("#margins").removeAttr("hidden");
			$("#top_margin, #right_margin, #bottom_margin, #left_margin").removeAttr("disabled");
		} else {
			$("#margins").attr("hidden", "hidden");
			$("#top_margin, #right_margin, #bottom_margin, #left_margin").attr("disabled", "disabled");
		}
		$("#top_margin, #right_margin, #bottom_margin, #left_margin").trigger("change");
	}).trigger("change");

	$("#height, #top_margin, #right_margin, #bottom_margin, #left_margin, #symbolsLength").on("change", function(e) {
		if (e.target.value < 0 || e.target.value === "") {
			$(this).attr("value", 0);
		}
	});
});
