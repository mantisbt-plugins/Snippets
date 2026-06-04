<?php

# Copyright (c) 2010 - 2012  Amethyst Reese
# Copyright (c) 2012 - 2021  MantisBT Team - mantisbt-dev@lists.sourceforge.net
# Licensed under the MIT license

use Mantis\Exceptions\ClientException;

/**
 * Object representing a Snippet (saved block of text).
 */
class Snippet
{
	/**
	 * Snippet types.
	 */
	const TYPE_STANDARD = 0;

	/**
	 * Target formats.
	 */
	const TARGET_VIEW = 'view';
	const TARGET_FORM = 'form';

	/**
	 * Placeholder types.
	 */
	const PLACEHOLDER_USER = '{user}';
	const PLACEHOLDER_REPORTER = '{reporter}';
	const PLACEHOLDER_HANDLER = '{handler}';
	const PLACEHOLDER_PROJECT = '{project}';

	/**
	 * @var int|null Snippet id
	 */
	public ?int $id = null;

	/**
	 * @var int User id
	 */
	public int $user_id;

	/**
	 * @var int Snippet type, see TYPE_* constants
	 */
	public int $type;

	/**
	 * @var string Snippet name
	 */
	public string $name;

	/**
	 * @var string Snippet value
	 */
	public string $value;

	/**
	 * Create a new Snippet object with the given details.
	 *
	 * @param int    $p_type    Field type
	 * @param string $p_name    Short name
	 * @param string $p_value   Full text value
	 * @param int    $p_user_id User ID
	 */
	public function __construct( $p_type, $p_name, $p_value, $p_user_id = 0 ) {
		$this->type = $p_type;
		$this->name = $p_name;
		$this->value = $p_value;
		$this->user_id = $p_user_id;
	}

	/**
	 * Create a copy of the given Snippet with strings cleaned for output.
	 *
	 * @param Snippet|Snippet[] $p_dirty  Snippet object(s) to process
	 * @param string            $p_target Target format (VIEW or FORM)
	 * @param int               $p_bug_id Reference Bug Id for pattern replacements
	 *
	 * @return Snippet[] Cleaned snippet objects
	 * @throws ClientException
	 */
	public static function clean( $p_dirty, $p_target = self::TARGET_VIEW, $p_bug_id = 0 ) {
		if( is_array( $p_dirty ) ) {
			$t_cleaned = array_map(
				function( $p_snippet ) use ( $p_target ) {
					return self::clean( $p_snippet, $p_target );
				},
				$p_dirty
			);
			if( $p_bug_id ) {
				$t_cleaned = self::patterns( $t_cleaned, $p_bug_id );
			}

		}
		else {
			switch( $p_target ) {
				case self::TARGET_FORM:
					$p_dirty->name = string_attribute( $p_dirty->name );
					$p_dirty->value = string_textarea( $p_dirty->value );
					break;
				case self::TARGET_VIEW:
				default:
					$p_dirty->name = string_display_line( $p_dirty->name );
					$p_dirty->value = string_display( $p_dirty->value );
					break;
			}

			$t_cleaned = new Snippet(
				$p_dirty->type,
				$p_dirty->name,
				$p_dirty->value,
				$p_dirty->user_id
			);
			$t_cleaned->id = $p_dirty->id;
		}

		return $t_cleaned;
	}

	/**
	 * Replace placeholder patterns in the snippet values with appropriate
	 * strings before being sent to the client for usage.
	 *
	 * @param Snippet[] $p_snippets objects to process
	 * @param int       $p_bug_id   Reference bug id; if 0, default values will
	 *                            be used
	 *                            (current user / current project)
	 *
	 * @return Snippet[] Updated snippet objects
	 * @throws ClientException
	 */
	public static function patterns( $p_snippets, $p_bug_id ) {
		$t_handler = null;
		$t_current_user = auth_get_current_user_id();

		if( is_int( $p_bug_id ) && $p_bug_id > 0 ) {
			$t_bug = bug_get( $p_bug_id );
			user_cache_array_rows( array(
				$t_bug->reporter_id,
				$t_bug->handler_id,
				$t_current_user,
			) );

			$t_reporter = user_get_username( $t_bug->reporter_id );

			if( $t_bug->handler_id != NO_USER ) {
				$t_handler = user_get_username( $t_bug->handler_id );
			}

			$t_project = project_get_name( $t_bug->project_id );
			$t_username = user_get_username( $t_current_user );
		}
		else {
			$t_username = user_get_username( $t_current_user );
			$t_reporter = $t_username;
			$t_project = project_get_name( helper_get_current_project() );
		}

		if( !$t_handler ) {
			$t_handler = plugin_lang_get( 'no_handler' );
		}

		foreach( $p_snippets as $t_snippet ) {
			$t_snippet->value = str_replace(
				array(
					self::PLACEHOLDER_USER,
					self::PLACEHOLDER_REPORTER,
					self::PLACEHOLDER_HANDLER,
					self::PLACEHOLDER_PROJECT,
				),
				array( $t_username, $t_reporter, $t_handler, $t_project ),
				$t_snippet->value
			);
		}

		return $p_snippets;
	}

	/**
	 * Load snippets by ID.
	 *
	 * @param int|array $p_id      Snippet ID (int or array)
	 * @param int|null  $p_user_id User ID or null if not to be included in the query
	 *
	 * @return Snippet|Snippet[] Snippet array with elements or empty array
	 *                           Snippet if single id is provided and found.
	 */
	public static function load_by_id( $p_id, $p_user_id ) {
		$t_snippet_table = plugin_table( "snippet" );

		if( is_array( $p_id ) ) {
			$t_ids = array_filter( $p_id, "is_int" );

			if( count( $t_ids ) < 1 ) {
				return array();
			}

			$t_ids = implode( ",", $t_ids );
			$t_params = array();
			$t_query = "SELECT * FROM $t_snippet_table WHERE id IN ($t_ids)";

			if( !is_null( $p_user_id ) ) {
				$t_query .= " AND user_id=" . db_param();
				$t_params[] = $p_user_id;
			}

			$t_result = db_query( $t_query, $t_params );

			return self::from_db_result( $t_result );
		} else {
			$t_params = array( $p_id );
			$t_query = "SELECT * FROM $t_snippet_table WHERE id=" . db_param();

			if( !is_null( $p_user_id ) ) {
				$t_query .= " AND user_id=" . db_param();
				$t_params[] = $p_user_id;
			}

			$t_result = db_query( $t_query, $t_params );

			$t_snippets = self::from_db_result( $t_result );
			return empty( $t_snippets ) ? [] : $t_snippets[$p_id];
		}
	}

	/**
	 * Convert a database query result to an array of Snippet objects.
	 *
	 * @param ADORecordSet $p_result Database query result
	 *
	 * @return Snippet[] objects
	 */
	private static function from_db_result( $p_result ) {
		$t_snippets = array();
		while( $t_row = db_fetch_array( $p_result ) ) {
			$t_snippet = new Snippet(
				(int)$t_row['type'],
				$t_row['name'],
				Snippet::replace_legacy_placeholders( $t_row['value'] ),
				(int)$t_row['user_id']
			);
			$t_snippet->id = (int)$t_row["id"];

			$t_snippets[$t_snippet->id] = $t_snippet;
		}

		return $t_snippets;
	}

	/**
	 * Replace legacy placeholders (e.g. %u) with modern ones (e.g. {user}).
	 *
	 * @param string $p_value The snippet to process.
	 *
	 * @return string The processed snippet.
	 * @noinspection PhpUnnecessaryLocalVariableInspection
	 */
	private static function replace_legacy_placeholders( $p_value ) {
		$t_value = $p_value;
		$t_value = str_replace( '%u', self::PLACEHOLDER_USER, $t_value );
		$t_value = str_replace( '%r', self::PLACEHOLDER_REPORTER, $t_value );
		$t_value = str_replace( '%h', self::PLACEHOLDER_HANDLER, $t_value );
		$t_value = str_replace( '%p', self::PLACEHOLDER_PROJECT, $t_value );
		return $t_value;
	}

	/**
	 * Load text objects for a given field type and user id.
	 *
	 * @param int  $p_type           Field type
	 * @param int  $p_user_id        User ID
	 * @param bool $p_include_global Include global text objects
	 *
	 * @return Snippet[]
	 */
	public static function load_by_type_user(
		$p_type,
		$p_user_id,
		$p_include_global = true
	) {
		$t_user_ids = array( (int)$p_user_id );
		if( $p_include_global ) {
			$t_user_ids[] = 0;
		}

		$t_snippet_table = plugin_table( "snippet" );

		$t_query = new DbQuery();
		$t_query->sql( "SELECT * FROM $t_snippet_table"
			. ' WHERE type = ' . $t_query->param( $p_type )
			. ' AND ' . $t_query->sql_in( 'user_id', $t_user_ids )
		);

		# Sort order
		switch( plugin_config_get( 'sort_order', SnippetsPlugin::SORT_ALPHA ) ) {
			case SnippetsPlugin::SORT_GLOBAL_FIRST:
				$t_query->append_sql( " ORDER BY user_id ASC, name ASC" );
				break;
			case SnippetsPlugin::SORT_PERSONAL_FIRST:
				$t_query->append_sql( " ORDER BY user_id DESC, name ASC" );
				break;
			case SnippetsPlugin::SORT_ALPHA:
			default:
				$t_query->append_sql( " ORDER BY name ASC" );
				break;
		}

		$t_result = $t_query->execute();

		return self::from_db_result( $t_result );
	}

	/**
	 * Load text objects for a given user id.
	 *
	 * @param int $p_user_id User ID
	 *
	 * @return Snippet[]
	 */
	public static function load_by_user_id( $p_user_id ) {
		$t_snippet_table = plugin_table( "snippet" );

		$t_query = "SELECT * FROM $t_snippet_table WHERE user_id=" . db_param() . " ORDER BY name";
		$t_result = db_query( $t_query, array( $p_user_id ) );

		return self::from_db_result( $t_result );
	}

	/**
	 * Delete snippets with the given ID.
	 *
	 * @param mixed $p_id Snippet ID (int or array)
	 * @param int   $p_user_id
	 */
	public static function delete_by_id( $p_id, $p_user_id ) {
		$t_snippet_table = plugin_table( "snippet" );

		if( is_array( $p_id ) ) {
			$t_ids = array_filter( $p_id, "is_int" );

			if( count( $t_ids ) < 1 ) {
				return;
			}

			$t_ids = implode( ",", $t_ids );

			$t_query = "DELETE FROM $t_snippet_table WHERE id IN ($t_ids) AND user_id=" . db_param();
			db_query( $t_query, array( $p_user_id ) );

		}
		else {
			$t_query = "DELETE FROM $t_snippet_table WHERE id=" . db_param() . " AND user_id=" . db_param();
			db_query( $t_query, array( $p_id, $p_user_id ) );
		}
	}

	/**
	 * Delete all text objects for a given user.
	 *
	 * @param int $p_user_id User ID
	 */
	public static function delete_by_user_id( $p_user_id ) {
		$t_snippet_table = plugin_table( "snippet" );
		$t_query = "DELETE FROM $t_snippet_table WHERE user_id=" . db_param();
		db_query( $t_query, array( $p_user_id ) );
	}

	public static function global_url( $p_is_global = true ) {
		if( $p_is_global ) {
			return '&global=true';
		}
		return '';
	}

	/**
	 * Returns an array with names of form fields (text areas) where snippets
	 * should be available for selection.
	 */
	public static function get_configured_field_names() {
		return preg_split( "/[,;\s]+/",
			plugin_config_get( "textarea_names", "bugnote_text" )
		);
	}

	/**
	 * Returns an array of ('text area field name' => 'language resource
	 * identifier') pairs that describe available (supported) text areas.
	 * Values will be passed to lang_get().
	 */
	public static function get_available_field_names() {
		return array(
			'bugnote_text' => 'bugnote',
			'description' => 'description',
			'steps_to_reproduce' => 'steps_to_reproduce',
			'additional_info' => 'additional_information',
			'body' => 'reminder',
		);
	}

	/**
	 * Create or update the database with the object's values.
	 *
	 * @return int Snippet ID if created
	 */
	public function save() {
		$t_snippet_table = plugin_table( "snippet" );

		# create
		if( $this->id === null ) {
			$t_query = "INSERT INTO $t_snippet_table
				(
					type,
					name,
					value,
					user_id
				) VALUES (
					" . db_param() . ",
					" . db_param() . ",
					" . db_param() . ",
					" . db_param() . "
				)";

			db_query( $t_query, array(
				$this->type,
				$this->name,
				$this->value,
				$this->user_id,
			) );

			$this->id = db_insert_id( $t_snippet_table );

			# update
		}
		else {
			$t_query = "UPDATE $t_snippet_table SET
				type=" . db_param() . ",
				name=" . db_param() . ",
				value=" . db_param() . ",
				user_id=" . db_param() . "
				WHERE id=" . db_param();

			db_query( $t_query, array(
				$this->type,
				$this->name,
				$this->value,
				$this->user_id,
				$this->id,
			) );
		}

		return $this->id;
	}
}
