// Copyright 2010 (c) John Reese
// Licensed under the MIT license

$(document).ready(function() {
	var textareas = $("textarea[name='bugnote_text']");

	function SavedTextInit(data) {
		var textarrays = data;

		textareas.each(function(index) {
				var name = $(this).attr("name");
				var textarea = $(this);
				
				try {

				texts = textarrays[name];
				if (texts != null) {
					label = $("<label>" + textarrays["lang"]["label"] + " </label>");

					select = $("<select></select>");
					select.append($("<option title=''>" + textarrays["lang"]["default"] + "</option>"));

					for (textshort in texts) {
						option = $("<option title='" + texts[textshort] + "'>" + textshort + "</option>");
						select.append(option);
					}

					select.change(function() {
							textarea.html($(this).attr("title"));
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
		xhr = $.getJSON("xmlhttprequest.php?entrypoint=plugin_savedtext", SavedTextInit);
	}

});

