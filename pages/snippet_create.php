<?php

# Copyright (c) 2010 - 2012  John Reese
# Copyright (c) 2012 - 2017  MantisBT Team - mantisbt-dev@lists.sourceforge.net
# Licensed under the MIT license

form_security_validate("plugin_snippets_create");

$global = gpc_get_bool("global", false);
if ($global) {
	access_ensure_global_level(plugin_config_get("edit_global_threshold"));
	$user_id = 0;
} else {
	access_ensure_global_level(plugin_config_get("edit_own_threshold"));
	$user_id = auth_get_current_user_id();
}

$name = gpc_get_string("name");
$value = gpc_get_string("value");

if (is_blank($name)) {
	plugin_error("name_empty");
}
if (is_blank($value)) {
	plugin_error("value_empty");
}

$snippet = new Snippet(0, $name, $value, $user_id);
$snippet->save();

form_security_purge("plugin_snippets_create");
print_successful_redirect(plugin_page("snippet_list", true) . Snippet::global_url($global));

