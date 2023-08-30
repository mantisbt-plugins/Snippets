<?php
# Copyright (c) 2010 - 2012 John Reese
# Copyright (c) MantisBT Team - mantisbt-dev@lists.sourceforge.net
# Licensed under the MIT license

require_once( dirname( __FILE__ ) . '/../Snippets.API.php' );

use Mantis\Exceptions\ClientException;

/**
 * Command to delete a snippet.
 */
class SnippetDeleteCommand extends Command {
	/**
	 * The snippet id.
	 *
	 * @var int
	 */
	private $snippet_id;

	/**
	 * The snippet owner user id.
	 *
	 * @var int
	 */
	private $owner_id;

	/**
	 * Constructor
	 *
	 * @param array $p_data The command data.
	 */
	function __construct( array $p_data ) {
		parent::__construct( $p_data );
	}

	/**
	 * Validate the data.
	 *
	 * @return void
	 * @throws ClientException
	 */
	protected function validate() {
		$this->snippet_id = (int)$this->query( 'id' );
		if( !$this->snippet_id ) {
			throw new ClientException(
				'Snippet id not specified',
				ERROR_EMPTY_FIELD,
				array( 'id' ) );
		}

		$t_snippet = Snippet::load_by_id( $this->snippet_id, /* user_id */ null );
		if( !$t_snippet ) {
			# TODO: ideally we should have a generic ENTITY_NOT_FOUND error to trigger 404 http status code
			# this error will trigger 500 http status code for now, it should trigge 404.
			# low priority since this is not used by the UI.
			throw new ClientException(
			 	"Snippet '" . $this->snippet_id . "' does not exist.",
			 	ERROR_GENERIC,
			 	array( $this->snippet_id )
			);
		}

		$t_global = $t_snippet->user_id == NO_USER;

		if( $t_global ) {
			access_ensure_global_level( plugin_config_get( 'edit_global_threshold' ) );
			$this->owner_id = NO_USER;
		} else {
			access_ensure_global_level( plugin_config_get( 'edit_own_threshold' ) );
			$t_current_user_id = auth_get_current_user_id();
			$this->owner_id = $t_current_user_id;

			# users should only be able to delete their own snippets
			if( $t_snippet->user_id != $t_current_user_id ) {
				access_denied();
			}
		}
	}

	/**
	 * Execute the command.
	 *
	 * @return void
	 */
	protected function process() {
		Snippet::delete_by_id( array( $this->snippet_id ), $this->owner_id );
	}
}
