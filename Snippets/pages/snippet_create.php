<?php

# Copyright 2010 (c) John Reese
# Licensed under the MIT license

form_security_validate("plugin_snippets_create");

$global = gpc_get_bool("global", false);
if ($global) {
	access_ensure_global_level(plugin_config_get("edit_global_threshold"));
	$user_id = 0;
} else {
	$user_id = auth_get_current_user_id();
}

$name = gpc_get_string("name");
$value = gpc_get_string("value");

$snippet = new Snippet(0, $name, $value, $user_id);
$snippet->save();

form_security_purge("plugin_snippets_create");
print_successful_redirect(plugin_page("snippet_list", true) . $global ? "&global=true" : "");

