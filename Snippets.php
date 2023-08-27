<?php
# Copyright (c) 2010 - 2012  John Reese
# Copyright (c) 2012 - 2021  MantisBT Team - mantisbt-dev@lists.sourceforge.net
# Licensed under the MIT license

class SnippetsPlugin extends MantisPlugin
{
	const VERSION = '2.4.1';

	public function register() {
		$this->name = plugin_lang_get( "name" );
		$this->description = plugin_lang_get( "description" );
		$this->page = "config_page";

		$this->version = self::VERSION;

		$this->requires = array(
			"MantisCore" => "2.3.0",
		);

		$this->author = "John Reese and MantisBT Team";
		$this->contact = "mantisbt-dev@lists.sourceforge.net";
		$this->url = "https://github.com/mantisbt-plugins/snippets";
	}

	public function config() {
		return array(
			"edit_global_threshold" => ADMINISTRATOR,
			"use_global_threshold" => REPORTER,
			"edit_own_threshold" => REPORTER,
			"textarea_names" => "bugnote_text",
		);
	}

	public function errors() {
		return array(
			"name_empty" => plugin_lang_get( "error_name_empty" ),
			"value_empty" => plugin_lang_get( "error_value_empty" ),
		);
	}

	public function hooks() {
		return array(
			"EVENT_MENU_ACCOUNT" => "menu_account",
			"EVENT_MENU_MANAGE" => "menu_manage",

			"EVENT_LAYOUT_RESOURCES" => "resources",

			"EVENT_MANAGE_USER_DELETE" => "user_delete",

			'EVENT_REST_API_ROUTES' => 'routes',
		);
	}

	public function init() {
		require_once( dirname( __FILE__ ) . '/core/Snippets.API.php' );
		require_once( dirname( __FILE__ ) . '/core/commands/SnippetGetCommand.php' );
		require_once( dirname( __FILE__ ) . '/core/commands/SnippetAddCommand.php' );
		require_once( dirname( __FILE__ ) . '/core/commands/SnippetUpdateCommand.php' );
		require_once( dirname( __FILE__ ) . '/core/commands/SnippetDeleteCommand.php' );
		require_once( dirname( __FILE__ ) . '/core/commands/SnippetSearchCommand.php' );
	}

	/**
	 * Hook for EVENT_MENU_ACCOUNT.
	 *
	 * Adds "My Snippets" and "Global Snippets" menu items.
	 *
	 * @return array
	 *
	 * @noinspection PhpUnused
	 */
	public function menu_account() {
		$t_return = array();

		if( access_has_global_level( plugin_config_get( "edit_own_threshold" )
		) ) {
			$page = plugin_page( "snippet_list" );
			$label = plugin_lang_get( "list_title" );

			$t_return[] = "<a href=\"$page\">$label</a>";
		}

		$t_menu_item = $this->menu_manage();
		if( $t_menu_item ) {
			$t_return[] = $t_menu_item;
		}

		return $t_return;
	}

	/**
	 * Hook for EVENT_MENU_MANAGE.
	 *
	 * Adds "Global Snippets" menu item.
	 *
	 * @return string
	 */
	public function menu_manage() {
		if( access_has_global_level( plugin_config_get( "edit_global_threshold"	) ) ) {
			$page = plugin_page( "snippet_list" ) . Snippet::global_url();
			$label = plugin_lang_get( "list_global_title" );

			return '<a href="' . string_html_specialchars( $page ) . '">' . $label . '</a>';
		}
		return '';
	}

	/**
	 * Hook for EVENT_LAYOUT_RESOURCES.
	 *
	 * Adds "Global Snippets" menu item.
	 *
	 * @return string
	 */
	public function resources() {
		return '
			<script src="' . plugin_file( "jquery-textrange.js" ) . '"></script>
			<script src="' . plugin_file( "jquery.qtip.min.js" ) . '"></script>
			<script src="' . plugin_file( "snippets.js" ) . '"></script>
			<link rel="stylesheet" type="text/css" href="' . plugin_file( "jquery.qtip.min.css" ) . '"/>
			<link rel="stylesheet" type="text/css" href="' . plugin_file( "snippets.css" ) . '"/>';
	}

	/**
	 * Hook for EVENT_MANAGE_USER_DELETE.
	 *
	 * When deleting a user's account, cleanup their Snippets.
	 *
	 * @param string $event
	 * @param int    $user_id
	 *
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function user_delete( $event, $user_id ) {
		Snippet::delete_by_user_id( $user_id );
	}

	/**
	 * Hook for EVENT_REST_API_ROUTES.
	 *
	 * Add the RESTful routes handled by this plugin.
	 *
	 * @param string $p_event_name The event name
	 * @param array  $p_event_args The event arguments
	 * @return void
	 *
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function routes( $p_event_name, $p_event_args ) {
		$t_app = $p_event_args['app'];
		$t_plugin = $this;
		$t_app->group(
			plugin_route_group(),
			function() use ( $t_app, $t_plugin ) {
				$t_app->get( '/help', [ $t_plugin, 'route_help' ] );

				$t_app->get( '/data', [ $t_plugin, 'route_data' ] );
				$t_app->get( '/data/{bug_id}', [ $t_plugin, 'route_data' ] );

				$t_app->get( '/snippets', [ $t_plugin, 'snippet_get' ] );
				$t_app->post( '/snippets', [ $t_plugin, 'snippet_add' ] );
				$t_app->put( '/snippets/{snippet_id}', [ $t_plugin, 'snippet_update' ] );
				$t_app->delete( '/snippets/{snippet_id}', [ $t_plugin, 'snippet_delete' ] );
				$t_app->get( '/snippets/search', [ $t_plugin, 'search' ] );
			}
		);
	}

	public function schema() {
		return array(
			# 2010-03-18
			0 => array( "CreateTableSQL", array( plugin_table( "snippet" ), "
				id			I		NOTNULL UNSIGNED AUTOINCREMENT PRIMARY,
				user_id		I		NOTNULL UNSIGNED,
				type		I		NOTNULL UNSIGNED,
				name		C(128)	NOTNULL,
				value		XL		NOTNULL
				")),

			# 2.3.0
			1 => array( "UpdateFunction", "delete_orphans" ),
		);
	}

	public function upgrade( $p_schema ) {
		if( $p_schema == 1 ) {
			require_once( dirname( __FILE__ ) . '/install_functions.php' );
		}
		return true;
	}

	/**
	 * REST API for adding a snippet.
	 *
	 * @param Slim\Http\Request  $p_request
	 * @param Slim\Http\Response $p_response
	 * @param array              $p_args
	 * @return Slim\Http\Response
	 */
	public function snippet_add( $p_request, $p_response, $p_args ) {
		plugin_push_current( $this->basename );

		$t_data = array(
			'payload' => $p_request->getParsedBody()
		);

		$t_command = new SnippetAddCommand( $t_data );
		$t_result = $t_command->execute();

		plugin_pop_current();

		return $p_response
			->withStatus( HTTP_STATUS_CREATED )
			->withJson( $t_result );
	}

	/**
	 * REST API for updating a snippet.
	 *
	 * @param Slim\Http\Request  $p_request
	 * @param Slim\Http\Response $p_response
	 * @param array              $p_args
	 * @return Slim\Http\Response
	 */
	public function snippet_update( $p_request, $p_response, $p_args ) {
		plugin_push_current( $this->basename );

		$t_data = array(
			'query' => array( 'id' => isset( $p_args['snippet_id'] ) ? (int)$p_args['snippet_id'] : 0 ),
			'payload' => $p_request->getParsedBody()
		);

		$t_command = new SnippetUpdateCommand( $t_data );
		$t_result = $t_command->execute();

		plugin_pop_current();

		return $p_response
			->withStatus( HTTP_STATUS_CREATED )
			->withJson( $t_result );
	}

	/**
	 * REST API for deleting a snippet by id.
	 *
	 * @param Slim\Http\Request  $p_request
	 * @param Slim\Http\Response $p_response
	 * @param array              $p_args
	 * @return Slim\Http\Response
	 */
	public function snippet_delete( $p_request, $p_response, $p_args ) {
		plugin_push_current( $this->basename );

		$t_data = array(
			'query' => array(
				'id' => isset( $p_args['snippet_id'] ) ? (int)$p_args['snippet_id'] : 0
			)
		);

		$t_command = new SnippetDeleteCommand( $t_data );
		$t_command->execute();

		plugin_pop_current();

		return $p_response
			->withStatus( HTTP_STATUS_NO_CONTENT );
	}

	/**
	 * REST API for getting global or user specific snippets
	 *
	 * @param Slim\Http\Request  $p_request
	 * @param Slim\Http\Response $p_response
	 * @param array              $p_args
	 * @return Slim\Http\Response
	 */
	public function snippet_get( $p_request, $p_response, $p_args ) {
		plugin_push_current( $this->basename );

		$t_query = array();

		$t_global = $p_request->getParam( 'global' );
		if( !is_null( $t_global ) ) {
			$t_query['global'] = $t_global;
		}

		$t_data = array(
			'query' => $t_query
		);

		$t_command = new SnippetGetCommand( $t_data );
		$t_result = $t_command->execute();

		plugin_pop_current();

		return $p_response
			->withStatus( HTTP_STATUS_SUCCESS )
			->withJson( $t_result );
	}

	/**
	 * REST API for searching accessible snippets.
	 *
	 * Caller can provide a search string `query` that will be matched for snippets
	 * whose title or content contains the search string. Default is no filtering.
	 *
	 * Caller can provide a limit on number of snippets return. Default is 10.
	 *
	 * @param Slim\Http\Request  $p_request
	 * @param Slim\Http\Response $p_response
	 * @param array              $p_args
	 * @return Slim\Http\Response
	 */
	public function search( $p_request, $p_response, $p_args ) {
		plugin_push_current( $this->basename );

		$t_query_data = array(
			'query' => $p_request->getParam( 'query' )
		);

		$t_limit = $p_request->getParam( 'limit' );
		if( $t_limit ) {
			$t_query_data['limit'] = (int)$t_limit;
		}

		$t_data = array(
			'query' => $t_query_data
		);

		$t_command = new SnippetSearchCommand( $t_data );
		$t_result = $t_command->execute();

		plugin_pop_current();

		return $p_response
			->withStatus( HTTP_STATUS_SUCCESS )
			->withJson( $t_result );
	}

	/**
	 * RESTful route for Snippets Pattern Help (tooltip).
	 *
	 * Returned JSON structure
	 *   - {string} title
	 *   - {string} text
	 *
	 * @param Slim\Http\Request  $request
	 * @param Slim\Http\Response $response
	 * @param array              $args
	 *
	 * @return Slim\Http\Response
	 *
	 * @noinspection PhpUnused, PhpUnusedParameterInspection
	 */
	public function route_help( $request, $response, $args ) {
		plugin_push_current( $this->basename );

		$t_help = array(
			'title' => plugin_lang_get( 'pattern_title' ),
			'text' => plugin_lang_get( 'pattern_help' ),
		);

		plugin_pop_current();

		return $response
			->withStatus( HTTP_STATUS_SUCCESS )
			->withJson( $t_help );
	}

	/**
	 * RESTful route for Snippets data.
	 *
	 * Returned JSON structure:
	 * - {string}     version  - Plugin version
	 * - {string}     selector - Configured jQuery selector for textareas
	 * - {string}     label    - Language string for Snippets select's label
	 * - {string}     default  - Language string for Snippets select's default
	 * option
	 * - {null|array} snippets - List of snippets, with following structure:
	 *   - {int}      id
	 *   - {int}      user_id
	 *   - {int}      type
	 *   - {string}   name     - Snippet title
	 *   - {string}   value    - Snippet text
	 *
	 * @param Slim\Http\Request  $request
	 * @param Slim\Http\Response $response
	 * @param array              $args [bug_id = Bug Id for patterns
	 *                                 replacement]
	 *
	 * @return Slim\Http\Response
	 *
	 * @noinspection PhpUnused, PhpUnusedParameterInspection
	 */
	public function route_data( $request, $response, $args ) {
		plugin_push_current( $this->basename );

		# Set the reference Bug Id for placeholders replacements
		if( isset( $args['bug_id'] ) ) {
			$t_bug_id = (int)$args['bug_id'];
		} else {
			$t_bug_id = 0;
		}

		# Load snippets available to the user
		$t_use_global = access_has_global_level( plugin_config_get( 'use_global_threshold' ) );
		$t_user_id = -1;
		if( access_has_global_level( plugin_config_get( 'edit_own_threshold' ) ) ) {
			$t_user_id = auth_get_current_user_id();
		}
		$t_snippets = Snippet::load_by_type_user( 0, $t_user_id, $t_use_global );
		$t_snippets = Snippet::clean( $t_snippets, Snippet::TARGET_FORM, $t_bug_id );

		# Split names of textareas found in 'textarea_names' option, and
		# make an array of "textarea[name='FIELD_NAME']" strings
		$t_selectors = array_map(
			function( $name ) {
				return "textarea[name='$name']";
			},
			Snippet::get_configured_field_names()
		);

		$t_data = array(
			# return configured jQuery selectors for textareas in "selector" field
			'selector' => implode( ',', $t_selectors ),
			'label' => plugin_lang_get( 'select_label' ),
			'default' => plugin_lang_get( 'select_default' ),
			'snippets' => array_values( $t_snippets ),
		);

		plugin_pop_current();

		return $response
			->withStatus( HTTP_STATUS_SUCCESS )
			->withJson( $t_data );
	}
}
