// Copyright (c) 2010 - 2012  John Reese
// Copyright (c) 2012 - 2021  MantisBT Team - mantisbt-dev@lists.sourceforge.net
// Licensed under the MIT license

jQuery(function($) {
	"use strict";

	/**
	 * Return MantisBT REST API URL for given endpoint
	 * @param {string} endpoint
	 * @returns {string} REST API URL
	 */
	function rest_api(endpoint) {
		// Using the full URL (through index.php) to avoid issues on sites
		// where URL rewriting is not working (#31)
		return "api/rest/index.php/plugins/Snippets/" + endpoint;
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
		 * @param {object} data - JSON object returned by REST API (see PHPDoc
		 *                        for Snippets::route_data() for details)
		 * @param {string} data.selector
		 * @param {string} data.label
		 * @param {string} data.default
		 * @param {object} data.snippets - Snippets list
		 */
		function SnippetsUI(data) {
			$(data.selector).each(function() {
				const textarea = $(this);

				// Only display snippets selector if there are any
				if (Array.isArray(data.snippets) && data.snippets.length > 0) {
					try {
						// Create Snippets select
						const select = $("<select></select>");

						// Set the Tab index equal to the associated textareas
						select.attr('tabindex', textarea.attr('tabindex'));

						select.append("<option title='' value=''>" + data.default + "</option>");

						$.each(data.snippets, function(key, snippet) {
							// Escape single quotes
							const value = snippet.value.replace(/'/g, "&#39;");

							select.append(
								"<option value='" + value + "' title='" + value + "'>" + snippet.name + "</option>"
							);
						});

						select.on('change', function() {
							const text = $(this).val();
							textarea.textrange('replace', text);
							$(this).val("");
						});

						const label = $("<label>" + data.label + " </label>");
						label.append(select);

						textarea.before(label);
						textarea.before('<div class="space-4"></div>');
					} catch(e) {
						console.error('Error occurred while generating Snippets UI', e);
					}
				}
			});
		}

		// If we have any textareas (excluding those in the plugin's own
		// edit pages) then fetch Snippets
		if ($("textarea").not(".snippetspatternhelp textarea").length > 0) {
			// Retrieve the bug id from the known forms where we know
			// Snippets-supported textareas exist.
			const known_forms = ['bug_update.php', 'bugnote_add.php', 'bug_reminder.php'];
			let selector = '';
			known_forms.forEach(function (value) {
				selector += "form[action='" + value + "'], ";
			});
			selector = selector.replace(/, $/, '');
			const bug_id = $('input[name="bug_id"]', selector).val();

			let url = rest_api('data');
			if (bug_id > 0) {
				url += "/" + bug_id;
			}

			$.getJSON(url)
				.done(SnippetsUI)
				.fail(function() {
					console.error('Error occurred while retrieving Snippets');
				});
		}
	}

	/**
	 * Initialize Placeholder help tooltip for given object
	 * @param {object} domObject
	 * @param {object} data - JSON object returned by XHR
	 */
	function AddTooltip(domObject, data) {
		domObject.qtip({
			content: {
				text: data.text,
				title: data.title,
				button: true
			},
			position: {
				target: domObject.children('textarea'),
				my: 'bottom right',
				at: 'top right',
				viewport: $(window),
				adjust: {
					method: 'flip'
				}
			},
			hide: {
				fixed: true
			}
		});
	}


	try {
		SnippetsInit();
	} catch(e) {
		alert(e);
	}

	// Snippet list behaviors
	$("input.snippets_select_all").on('change', function() {
		$("input[name='snippet_list[]']").prop("checked", $(this).prop("checked"));
	});

	// Snippet pattern help
	const selector = $(".snippetspatternhelp");
	if (selector.length > 0 ) {
		$.get(rest_api('help'))
			.done(function (data) {
				selector.each(function() {
					AddTooltip($(this), data);
				});
			})
			.fail(function () {
				console.error('Error occurred while retrieving Snippets pattern help');
			});
	}

});
