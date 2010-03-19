// Copyright 2010 (c) John Reese
// Licensed under the MIT license

$(document).ready(function() {
	var textareas = $("textarea[name='bugnote_text']");

	function SnippetsInit(data) {
		var textarrays = data;

		textareas.each(function(index) {
				var textarea_name = $(this).attr("name");
				var textarea = $(this);
				
				try {

				snippets = textarrays[textarea_name];
				if (snippets != null) {
					label = $("<label>" + textarrays["lang"]["label"] + " </label>");

					select = $("<select></select>");
					select.append($("<option title=''>" + textarrays["lang"]["default"] + "</option>"));

					for (snippetid in snippets) {
						snippet = snippets[snippetid];

						option = $("<option value='" + snippet.value + "' title='" + snippet.value + "'>" + snippet.name + "</option>");
						select.append(option);
					}

					select.change(function() {
							textarea.html($(this).val());
						});
					label.append(select);

					$(this).before(label);
					$(this).before("<br/>");
				}

				} catch(e) {
					alert(e);
				}
			});
	}

	if (textareas.length > 0) {
		xhr = $.getJSON("xmlhttprequest.php?entrypoint=plugin_snippets", SnippetsInit);
	}

});

