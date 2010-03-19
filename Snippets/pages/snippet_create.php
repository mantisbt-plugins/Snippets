<?php

# Copyright 2010 (c) John Reese
# Licensed under the MIT license

form_security_validate("plugin_snippets_create");

$user_id = gpc_get_int("user_id", -1);
if ($user_id == -1) {
	$user_id = auth_get_current_user_id();
} else {
	access_ensure_global_level(plugin_config_get("global_text_threshold"));
	$user_id = 0;
}

$name = gpc_get_string("name");
$value = gpc_get_string("value");

$snippet = new Snippet(0, $name, $value, $user_id);
$snippet->save();

print_successful_redirect(plugin_page("account_snippets", true));

