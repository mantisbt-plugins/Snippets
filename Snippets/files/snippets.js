// Copyright 2010 (c) John Reese
// Licensed under the MIT license

$(document).ready(function() {
		try {
			SnippetsInit();
		} catch(e) {
			alert(e);
		}

		// Snippet list behaviors
		$("input.snippets_select_all").change(function(){
				$("input[name='snippet_list[]']").attr("checked", $(this).attr("checked"));
			});
	});

/**
 * Primary Snippets functionality.
 * Use an AJAX request to retrieve the user's available snippets, and
 * then insert select boxes into the DOM for each supported textarea.
 */
function SnippetsInit() {
	var textareas = $("textarea[name='bugnote_text']");

	function SnippetsUI(data) {
		var textarrays = data;

		textareas.each(function(index) {
				var textarea_name = $(this).attr("name");
				var textarea = $(this);
				
				try {

				snippets = textarrays[textarea_name];
				if (snippets != null) {
					label = $("<label>" + textarrays["lang"]["label"] + " </label>");

					select = $("<select></select>");
					select.append($("<option title='' value=''>" + textarrays["lang"]["default"] + "</option>"));

					for (snippetid in snippets) {
						snippet = snippets[snippetid];

						option = $("<option value='" + snippet.value + "' title='" + snippet.value + "'>" + snippet.name + "</option>");
						select.append(option);
					}

					select.change(function() {
							textarea.val(textarea.val() + $(this).val());
							$(this).val("");
						});
					label.append(select);

					$(this).before(label);
					$(this).before("<br/>");

					$(this).parent("td").removeClass("center");
				}

				} catch(e) {
					alert(e);
				}
			});
	}

	if (textareas.length > 0) {
		var bug_id = 0;

		$("form[name='bugnoteadd'] input[name='bug_id']").each(function() {
				bug_id = $(this).val();
			});
		$("form[name='update_bug_form'] input[name='bug_id']").each(function() {
				bug_id = $(this).val();
			});

		xhrurl = "xmlhttprequest.php?entrypoint=plugin_snippets"
		if (bug_id > 0) {
			xhrurl += "&bug_id=" + bug_id;
		}

		xhr = $.getJSON(xhrurl, SnippetsUI);
	}
}

