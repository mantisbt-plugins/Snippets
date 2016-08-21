// Copyright (c) 2010 - 2012  John Reese
// Copyright (c) 2012 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
// Licensed under the MIT license

jQuery(document).ready(function($) {
		var SnippetsLangArray = null;

		/**
		 * Handle retrieving and storing language strings from the server.
		 */
		function SnippetsLang(str) {
			if (SnippetsLangArray == null) {
				xhr = $.ajax({
					async: false,
					dataType: "json",
					url: "xmlhttprequest.php?entrypoint=plugin_snippets_text",
					success: function(data) {SnippetsLangArray = data;}
					});
			}

			return SnippetsLangArray[str];
		}

		// Snippet list behaviors
		$("input.snippets_select_all").change(function(){
				$("input[name='snippet_list[]']").prop("checked", $(this).prop("checked"));
			});

		// Snippet pattern help
		$(".snippetspatternhelp").each(function() {$(this).simpletip({
				content: SnippetsLang("pattern_help"),
				baseClass: "snippetsTooltip",
				fixed: false,
				offset: [20, 20],
				});
			});

		/**
		 * Primary Snippets functionality.
		 * Use an AJAX request to retrieve the user's available snippets, and
		 * then insert select boxes into the DOM for each supported textarea.
		 */
		function SnippetsInit() {
			function SnippetsUI(data) {
				var textarrays = data;

				$(data.selector).each(function(index) {
						var textarea_name = $(this).attr("name");
						var textarea = $(this);

						try {

						snippets = textarrays["texts"];
						if (snippets != null) {
							label = $("<label>" + SnippetsLang("label") + " </label>");

							select = $("<select></select>");
							select.append($("<option title='' value=''>" + SnippetsLang("default") + "</option>"));

							for (snippetid in snippets) {
								snippet = snippets[snippetid];
								// Escape single quotes
								value = snippet.value.replace(/'/g, "&#39;" );

								option = $("<option value='" + value + "' title='" + value + "'>" + snippet.name + "</option>");
								select.append(option);
							}

							select.change(function() {
									text = $(this).val();
									textarea.textrange('replace', text);
									$(this).val("");
								});
							label.append(select);

							$(this).before(label);
							$(this).before('<div class="space-4"></div>');

							$(this).parent("td").removeClass("center");
						}

						} catch(e) {
							alert(e);
						}
					});
			}

			//if we have any textareas then fetch snippets
			if ($("textarea").length > 0) {
				var bug_id = 0;

				$("form[name='bugnoteadd'] input[name='bug_id']").each(function() {
						bug_id = $(this).val();
					});
				$("form[action='bug_update.php'] input[name='bug_id']").each(function() {
						bug_id = $(this).val();
					});

				xhrurl = "xmlhttprequest.php?entrypoint=plugin_snippets"
				if (bug_id > 0) {
					xhrurl += "&bug_id=" + bug_id;
				}

				xhr = $.getJSON(xhrurl, SnippetsUI);
			}
		}

		try {
			SnippetsInit();
		} catch(e) {
			alert(e);
		}

	});
