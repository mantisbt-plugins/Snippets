// Copyright (c) 2010 - 2012  John Reese
// Copyright (c) 2012 - 2017  MantisBT Team - mantisbt-dev@lists.sourceforge.net
// Licensed under the MIT license

jQuery(document).ready(function($) {
	"use strict";

	/**
	 * Return MantisBT XMLHttpRequest URL for given endpoint
	 * @param {string} entrypoint
	 * @returns {string} XMLHttpRequest URL
	 */
	function xhrurl(entrypoint) {
		return "xmlhttprequest.php?entrypoint=plugin_snippets_" + entrypoint;
	}

	// Snippet list behaviors
	$("input.snippets_select_all").change(function(){
		$("input[name='snippet_list[]']").prop("checked", $(this).prop("checked"));
	});

	// Snippet pattern help
	var selector = $(".snippetspatternhelp");
	if (selector.length > 0 ) {
		$.get(xhrurl('pattern_help'))
			.done(function (data) {
				selector.simpletip({
					content: data,
					baseClass: "snippetsTooltip",
					fixed: false,
					offset: [20, 20]
				});
			})
			.fail(function () {
				console.error('Error occured while retrieving Snippets pattern help');
			});
	}

	/**
	 * Primary Snippets functionality.
	 * Use an AJAX request to retrieve the user's available snippets, and
	 * then insert select boxes into the DOM for each supported textarea.
	 */
	function SnippetsInit() {
		/**
		 * Initialize Snippets user interface.
		 * Adds a selection list before each textarea.
		 * @param {object} data - JSON object returned by XHR (see PHPDoc for
		 *                        xmlhttprequest_plugin_snippets_data() for details)
		 * @param {string} data.selector
		 * @param {string} data.label
		 * @param {string} data.default
		 * @param {object} data.snippets - Snippets list
		 */
		function SnippetsUI(data) {
			$(data.selector).each(function() {
				var textarea = $(this);

				if (data.snippets !== null) {
					try {
						// Create Snippets select
						var select = $("<select></select>");
						select.append("<option title='' value=''>" + data.default + "</option>");

						$.each(data.snippets, function(key, snippet) {
							// Escape single quotes
							var value = snippet.value.replace(/'/g, "&#39;");

							select.append(
								"<option value='" + value + "' title='" + value + "'>" + snippet.name + "</option>"
							);
						});

						select.change(function() {
							var text = $(this).val();
							textarea.textrange('replace', text);
							$(this).val("");
						});

						var label = $("<label>" + data.label + " </label>");
						label.append(select);

						textarea.before(label);
						textarea.before('<div class="space-4"></div>');
					} catch(e) {
						console.error('Error occured while generating Snippets UI', e);
					}
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

			var url = xhrurl('data');
			if (bug_id > 0) {
				url += "&bug_id=" + bug_id;
			}

			$.getJSON(url)
				.done(SnippetsUI)
				.fail(function() {
					console.error('Error occured while retrieving Snippets');
				});
		}
	}

	try {
		SnippetsInit();
	} catch(e) {
		alert(e);
	}

});
