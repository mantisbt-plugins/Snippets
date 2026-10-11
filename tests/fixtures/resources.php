<?php
# Minimal MantisBT boundary for exercising the plugin's resource output.
class MantisPlugin {}
define( 'ADMINISTRATOR', 90 );
define( 'REPORTER', 25 );

function plugin_config_get( $p_option, $p_default = null, $p_global = false ) {
	if( $p_option !== 'rest_api_path' || !$p_global ) {
		throw new RuntimeException( 'The REST path must use server-wide configuration.' );
	}
	return $GLOBALS['argv'][1] ?? (new SnippetsPlugin())->config()['rest_api_path'];
}

function plugin_file( $p_file ) {
	return 'plugin_file.php?file=Snippets/' . $p_file;
}

function string_html_specialchars( $p_value ) {
	return htmlspecialchars( $p_value, ENT_QUOTES, 'UTF-8' );
}

require dirname( __DIR__, 2 ) . '/Snippets.php';
echo (new SnippetsPlugin())->resources();
