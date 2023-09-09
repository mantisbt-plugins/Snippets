<?php
# Copyright (c) 2010 - 2012 John Reese
# Copyright (c) MantisBT Team - mantisbt-dev@lists.sourceforge.net
# Licensed under the MIT license

require_once( dirname( __FILE__ ) . '/Snippets.API.php' );

use Mantis\Exceptions\ClientException;

/**
 * Command to update a snippet.
 */
class SnippetUpdateCommand extends Command {
	/**
	 * The snippet id.
	 *
	 * @var int
	 */
	private $snippet_id;

	/**
	 * The snippet object
	 *
	 * @var Snippet
	 */
	private $snippet;

	/**
	 * The snippet name
	 *
	 * @var string
	 */
	private $name;

	/**
	 * The snippet text
	 *
	 * @var string
	 */
	private $text;

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
		$this->name = $this->payload( 'name', '' );
		$this->text = $this->payload( 'text', '' );

		if( is_blank( $this->name ) ) {
			throw new ClientException(
				'Snippet name not specified',
				ERROR_EMPTY_FIELD,
				array( 'name' ) );
		}

		if( is_blank( $this->text ) ) {
			throw new ClientException(
				'Snippet text not specified',
				ERROR_EMPTY_FIELD,
				array( 'text' ) );
		}

		$this->snippet = Snippet::load_by_id( $this->snippet_id, /* user_id */ null );
		if( !$this->snippet ) {
			# TODO: ideally we should have a generic ENTITY_NOT_FOUND error to trigger 404 http status code
			# this error will trigger 500 http status code for now, it should trigge 404.
			# low priority since this is not used by the UI.
			throw new ClientException(
			 	"Snippet '" . $this->snippet_id . "' does not exist.",
			 	ERROR_GENERIC,
			 	array( $this->snippet_id )
			);
		}

		$t_global = $this->snippet->user_id == NO_USER;

		if( $t_global ) {
			access_ensure_global_level( plugin_config_get( 'edit_global_threshold' ) );
		} else {
			access_ensure_global_level( plugin_config_get( 'edit_own_threshold' ) );
			$t_current_user_id = auth_get_current_user_id();

			# users should only be able to delete their own snippets
			if( $this->snippet->user_id != $t_current_user_id ) {
				access_denied();
			}
		}
	}

	/**
	 * Execute the command.
	 * @return array result
	 */
	protected function process() {
		$this->snippet->name = $this->name;
		$this->snippet->value = $this->text;
		$this->snippet->save();

		$t_results = array(
			'snippets' => array(
				array(
					'id' => $this->snippet->id,
					'name' => $this->snippet->name,
					'text' => $this->snippet->value
				)
			)
		);

		return $t_results;
	}
}
