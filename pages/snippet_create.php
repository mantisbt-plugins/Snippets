<?php
# Copyright (c) 2010 - 2012  Amethyst Reese
# Copyright (c) 2012 - 2021  MantisBT Team - mantisbt-dev@lists.sourceforge.net
# Licensed under the MIT license

form_security_validate( 'plugin_snippets_create' );

$f_name = gpc_get_string( 'name' );
$f_text = gpc_get_string( 'value' );
$f_global = gpc_get_bool( 'global', false );

$t_data = array(
	'payload' => array(
		'name' => $f_name,
		'text' => $f_text,
		'global' => $f_global
	)
);

$t_command = new SnippetAddCommand( $t_data );
$t_command->execute();

form_security_purge( 'plugin_snippets_create' );
print_header_redirect(
	plugin_page( 'snippet_list', true ) . Snippet::global_url( $f_global )
);
