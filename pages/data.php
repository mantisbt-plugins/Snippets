<?php
# Copyright (c) 2010 - 2012  Amethyst Reese
# Copyright (c) 2012 - 2021  MantisBT Team - mantisbt-dev@lists.sourceforge.net
# Licensed under the MIT license

header('Content-Type: application/json');

# Set the reference Bug Id for placeholders replacements
$t_bug_id = gpc_get_int('bug_id', 0);

# Load snippets available to the user
$t_use_global = access_has_global_level(plugin_config_get('use_global_threshold'));
$t_user_id = -1;
if (access_has_global_level(plugin_config_get('edit_own_threshold'))) {
	$t_user_id = auth_get_current_user_id();
}
$t_snippets = Snippet::load_by_type_user(Snippet::TYPE_STANDARD, $t_user_id, $t_use_global);
$t_snippets = Snippet::clean($t_snippets, Snippet::TARGET_FORM, $t_bug_id);

# Split names of textareas found in 'textarea_names' option, and
# make an array of "textarea[name='FIELD_NAME']" strings
$t_selectors = array_map(
	function($name) {
		return "textarea[name='$name']";
	},
	Snippet::get_configured_field_names()
);

$t_data = array(
	# return configured jQuery selectors for textareas in "selector" field
	'selector' => implode(',', $t_selectors),
	'label' => plugin_lang_get('select_label'),
	'default' => plugin_lang_get('select_default'),
	'snippets' => array_values($t_snippets),
);

echo json_encode($t_data);
