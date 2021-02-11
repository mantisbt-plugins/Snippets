<?php
# Copyright (c) 2021 Damien Regad
# Licensed under the MIT license

/**
 * UpdateFunction to delete orphaned Snippets.
 *
 * Plugin versions < 2.3.0 did not delete a Users' Snippets when their account
 * was deleted, resulting in orphaned records in the snippet table.
 *
 * @return int 2 if success
 *
 * @noinspection PhpUnused
 */
function install_delete_orphans() {
	$t_snippets_table = plugin_table( 'snippet' );

	$t_query = "DELETE FROM $t_snippets_table
		WHERE NOT EXISTS (SELECT 1 FROM {user} AS u WHERE u.id = user_id)
		AND user_id <> 0";

	if( db_query( $t_query ) === false ) {
		return false;
	}

	return 2; // Success
}
