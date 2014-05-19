<?php

# Copyright (c) 2010 - 2012  John Reese
# Copyright (c) 2012 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
# Licensed under the MIT license

form_security_validate("plugin_Snippets_config");
access_ensure_global_level(config_get("manage_plugin_threshold"));

function maybe_set_option( $name, $value ) {
	if ( $value != plugin_config_get( $name ) ) {
		plugin_config_set( $name, $value );
	}
}

maybe_set_option("edit_global_threshold", gpc_get_int("edit_global_threshold"));
maybe_set_option("use_global_threshold", gpc_get_int("use_global_threshold"));
maybe_set_option("edit_own_threshold", gpc_get_int("edit_own_threshold"));

form_security_purge("plugin_Snippets_config");
print_successful_redirect(plugin_page("config_page", true));

