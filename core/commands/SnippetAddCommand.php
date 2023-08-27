<?php
# Copyright (c) 2010 - 2012 John Reese
# Copyright (c) MantisBT Team - mantisbt-dev@lists.sourceforge.net
# Licensed under the MIT license

require_once( dirname( __FILE__ ) . '/../Snippets.API.php' );

use Mantis\Exceptions\ClientException;

/**
 * Command to add snippets.
 */
class SnippetAddCommand extends Command {
	/**
	 * The snippet owner user id.
	 *
	 * @var int
	 */
	private $owner_id;

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
		$this->name = $this->payload( 'name', '' );
		$this->text = $this->payload( 'text', '' );
		$t_global = (bool)$this->payload( 'global', true );

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

		if( $t_global ) {
			access_ensure_global_level( plugin_config_get( 'edit_global_threshold' ) );
			$this->owner_id = NO_USER;
		} else {
			access_ensure_global_level( plugin_config_get( 'edit_own_threshold' ) );
			$this->owner_id = auth_get_current_user_id();
		}
	}

	/**
	 * Execute the command.
	 * @return array result
	 */
	protected function process() {
		$t_snippet = new Snippet( /* type */ 0, $this->name, $this->text, $this->owner_id );
		$t_snippet->save();

		$t_results = array(
			'snippets' => array(
				array(
					'id' => $t_snippet->id,
					'name' => $t_snippet->name,
					'text' => $t_snippet->value
				)
			)
		);

		return $t_results;
	}
}
