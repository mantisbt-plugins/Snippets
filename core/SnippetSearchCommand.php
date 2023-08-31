<?php
# Copyright (c) 2010 - 2012 John Reese
# Copyright (c) MantisBT Team - mantisbt-dev@lists.sourceforge.net
# Licensed under the MIT license

require_once( dirname( __FILE__ ) . '/Snippets.API.php' );

use Mantis\Exceptions\ClientException;

define( 'SNIPPETS_MATCH_TYPE_TITLE', 'title' );
define( 'SNIPPETS_MATCH_TYPE_CONTENT', 'content' );

/**
 * Command to search for snippets.
 *
 * Caller can provide a search string `query` that will be matched for snippets
 * whose title or content contains the search string. Default is no filtering.
 *
 * Caller can provide a limit on number of snippets return. Default is 10.
 */
class SnippetSearchCommand extends Command {
	/**
	 * The current user id
	 *
	 * @var int
	 */
	private $user_id;

	/**
	 * The search text
	 *
	 * @var string
	 */
	private $query;

	/**
	 * The maximum number of results to return.
	 *
	 * @var int
	 */
	private $limit;

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
		$this->query = $this->query( 'query', '' );
		$this->limit = (int)$this->query( 'limit', 10 );

		$this->user_id = auth_get_current_user_id();
	}

	/**
	 * Execute the command.
	 * @return array result
	 */
	protected function process() {
		$t_global_snippets_threshold = plugin_config_get( 'use_global_threshold', null, false, NO_USER );
		$t_use_global = access_has_global_level( $t_global_snippets_threshold );

		$t_snippets_result = array();

		$t_snippets = Snippet::load_by_type_user( 0, $this->user_id, $t_use_global );

		# Include matching snippets up to limit specified
		# - First start with ones where the query matches the title
		# - Then include ones where the query matches the content
		$t_included_snippets = array();
		$t_match_types = array( SNIPPETS_MATCH_TYPE_TITLE, SNIPPETS_MATCH_TYPE_CONTENT );
		foreach( $t_match_types as $t_match ) {
			foreach( $t_snippets as $t_snippet ) {
				if( isset( $t_included_snippets[$t_snippet->id] ) ) {
					continue;
				}

				if( self::match( $t_snippet, $this->query, $t_match ) ) {
					$t_snippets_result[] = array(
						'id'   => $t_snippet->id,
						'name' => $t_snippet->name,
						'text' => $t_snippet->value
					);

					$t_included_snippets[$t_snippet->id] = true;
				}

				if( count( $t_snippets_result ) >= $this->limit ) {
					break 2;
				}
			}	
		}

		$t_results = array(
			'snippets' => $t_snippets_result,
		);

		return $t_results;
	}

	private static function match( $p_snippet, $p_query, $p_match ) {
		if( is_blank( $p_query ) ) {
			return true;
		}

		if( $p_match == SNIPPETS_MATCH_TYPE_TITLE ) {
			if ( stripos( $p_snippet->name, $p_query ) !== false ) {
				return true;
			}
		}

		if( $p_match == SNIPPETS_MATCH_TYPE_CONTENT ) {
			if ( stripos( $p_snippet->value, $p_query ) !== false ) {
				return true;
			}
		}

		return false;
	}
}
