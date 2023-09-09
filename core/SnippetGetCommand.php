<?php
# Copyright (c) 2010 - 2012 Amethyst Reese
# Copyright (c) MantisBT Team - mantisbt-dev@lists.sourceforge.net
# Licensed under the MIT license

require_once( dirname( __FILE__ ) . '/Snippets.API.php' );

use Mantis\Exceptions\ClientException;

/**
 * Command to get global snippets or snippets for a user.
 *
 * The intent of this APIs is for getting the list of user snippets or global
 * snippets for scenarios like managing such lists. For consumption of snippets
 * available to a user scenarios, use the Search API with no filter or a specific
 * text filter.
 */
class SnippetGetCommand extends Command {
	/**
	 * The owner id
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
		$t_global = (int)$this->query( 'global', 0 );

		if( $t_global ) {
			$t_global_snippets_threshold = plugin_config_get( 'use_global_threshold', null, false, NO_USER );
			if( !access_has_global_level( $t_global_snippets_threshold ) ) {
				throw new ClientException(
					'User does not have access to global snippets.',
					ERROR_ACCESS_DENIED );
			}

			$this->owner_id = NO_USER;
		} else {
			$this->owner_id = auth_get_current_user_id();
		}
	}

	/**
	 * Execute the command.
	 *
	 * @return array result
	 */
	protected function process() {
		$t_snippets_result = array();

		# global is always false, because we will explicitly include the global user id (NO_USER)
		# or the current user id, as appropriate
		$t_snippets = Snippet::load_by_type_user( 0, $this->owner_id, /* global */ false );

		foreach( $t_snippets as $t_snippet ) {
			$t_snippets_result[] = array(
				'id'   => $t_snippet->id,
				'name' => $t_snippet->name,
				'text' => $t_snippet->value
			);
		}

		$t_results = array(
			'snippets' => $t_snippets_result,
		);

		return $t_results;
	}
}
