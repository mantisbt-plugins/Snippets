<?php

# Copyright (c) 2010 - 2012  John Reese
# Copyright (c) 2012 - 2018  MantisBT Team - mantisbt-dev@lists.sourceforge.net
# Licensed under the MIT license

define( 'PLACEHOLDER_USER', '{user}' );
define( 'PLACEHOLDER_REPORTER', '{reporter}' );
define( 'PLACEHOLDER_HANDLER', '{handler}' );
define( 'PLACEHOLDER_PROJECT', '{project}' );

/**
 * Object representing a Snippet (saved block of text).
 */
class Snippet {
	public $id;
	public $user_id;
	public $type;
	public $name;
	public $value;

	/**
	 * Create a new Snippet object with the given details.
	 *
	 * @param int Field type
	 * @param string Short name
	 * @param string Full text value
	 * @param int User ID
	 */
	public function __construct($type, $name, $value, $user_id=0) {
		$this->type = $type;
		$this->name = $name;
		$this->value = $value;
		$this->user_id = $user_id;
	}

	/**
	 * Create or update the database with the object's values.
	 *
	 * @return int Snippet ID if created
	 */
	public function save() {
		$snippet_table = plugin_table("snippet");

		# create
		if ($this->id === null) {
			$query = "INSERT INTO {$snippet_table}
				(
					type,
					name,
					value,
					user_id
				) VALUES (
					".db_param().",
					".db_param().",
					".db_param().",
					".db_param()."
				)";

			db_query($query, array(
				$this->type,
				$this->name,
				$this->value,
				$this->user_id
			));

			$this->id = db_insert_id($snippet_table);

		# update
		} else {
			$query = "UPDATE {$snippet_table} SET
				type=".db_param().",
				name=".db_param().",
				value=".db_param().",
				user_id=".db_param()."
				WHERE id=".db_param();

			db_query($query, array(
				$this->type,
				$this->name,
				$this->value,
				$this->user_id,
				$this->id
			));
		}

		return $this->id;
	}

	/**
	 * Create a copy of the given Snippet with strings cleaned for output.
	 *
	 * @param Snippet|Snippet[] $dirty  Snippet object(s) to process
	 * @param string $target Target format ('view' or 'form'
	 * @param boolean $pattern Replacement patterns
	 *
	 * @return Snippet[] Cleaned snippet objects
	 */
	public static function clean($dirty, $target="view", $pattern=false) {
		if (is_array($dirty)) {
			$cleaned = array();
			foreach ($dirty as $id => $snippet) {
				$cleaned[$id] = self::clean($snippet, $target);
			}
			if (false !== $pattern) {
				$cleaned = self::patterns($cleaned, $pattern);
			}

		} else {
			if ($target == "view") {
				$dirty->name = string_display_line($dirty->name);
				$dirty->value = string_display($dirty->value);
			} elseif ($target == "form") {
				$dirty->name = string_attribute($dirty->name);
				$dirty->value = string_textarea($dirty->value);
			}

			$cleaned = new Snippet(
				$dirty->type,
				$dirty->name,
				$dirty->value,
				$dirty->user_id
			);
			$cleaned->id = $dirty->id;
		}

		return $cleaned;
	}

	/**
	 * Replace placeholder patterns in the snippet values with appropriate
	 * strings before being sent to the client for usage.
	 *
	 * @param Snippet[] $snippets objects to process
	 * @param int $bug_id Reference bug id; if 0, default values will be used
	 *                    (current user / current project)
	 *
	 * @return Snippet[] Updated snippet objects
	 */
	public static function patterns($snippets, $bug_id) {
		$handler = PLACEHOLDER_HANDLER;

		$current_user = auth_get_current_user_id();

		if (is_int($bug_id) && $bug_id > 0) {
			$bug = bug_get($bug_id);
			user_cache_array_rows(array($bug->reporter_id, $bug->handler_id, $current_user));

			$reporter = user_get_username($bug->reporter_id);

			if($bug->handler_id != NO_USER) {
				$handler = user_get_username($bug->handler_id);
			}

			$project = project_get_name($bug->project_id);
			$username = user_get_username($current_user);
		} else {
			$username = user_get_username($current_user);
			$reporter = $username;
			$project = project_get_name(helper_get_current_project());
		}

		foreach ($snippets as $snippet) {
			$snippet->value = str_replace(
				array(PLACEHOLDER_USER, PLACEHOLDER_REPORTER, PLACEHOLDER_HANDLER, PLACEHOLDER_PROJECT),
				array($username, $reporter, $handler, $project),
				$snippet->value);
		}

		return $snippets;
	}

	/**
	 * Load snippets by ID.
	 *
	 * @param mixed Snippet ID (int or array)
	 * @param int User ID
	 *
	 * @return Snippet|Snippet[]
	 */
	public static function load_by_id($id, $user_id) {
		$snippet_table = plugin_table("snippet");

		if (is_array($id)) {
			$ids = array_filter($id, "is_int");

			if (count($ids) < 1) {
				return array();
			}

			$ids = implode(",", $ids);

			$query = "SELECT * FROM {$snippet_table} WHERE id IN ({$ids}) AND user_id=".db_param();
			$result = db_query($query, array($user_id));

			return self::from_db_result($result);

		} else {
			$query = "SELECT * FROM {$snippet_table} WHERE id=".db_param()." AND user_id=".db_param();
			$result = db_query($query, array($id, $user_id));

			$snippets = self::from_db_result($result);
			return $snippets[0];
		}
	}

	/**
	 * Load text objects for a given field type and user id.
	 *
	 * @param int Field type
	 * @param int User ID
	 * @param boolean Include global text objects
	 *
	 * @return Snippet[]
	 */
	public static function load_by_type_user($type, $user_id, $include_global=true) {
		$user_ids = array((int) $user_id);
		if ($include_global) {
			$user_ids[] = 0;
		}
		$user_ids = implode(",", $user_ids);

		$snippet_table = plugin_table("snippet");

		$query = "SELECT * FROM {$snippet_table} WHERE type=".db_param()." AND user_id IN ({$user_ids}) ORDER BY name";
		$result = db_query($query, array($type));

		return self::from_db_result($result);
	}

	/**
	 * Load text objects for a given user id.
	 *
	 * @param int User ID
	 *
	 * @return Snippet[]
	 */
	public static function load_by_user_id($user_id) {
		$snippet_table = plugin_table("snippet");

		$query = "SELECT * FROM {$snippet_table} WHERE user_id=".db_param()." ORDER BY name";
		$result = db_query($query, array($user_id));

		return self::from_db_result($result);
	}

	/**
	 * Delete snippets with the given ID.
	 *
	 * @param mixed $id Snippet ID (int or array)
	 * @param int $user_id
	 */
	public static function delete_by_id($id, $user_id) {
		$snippet_table = plugin_table("snippet");

		if (is_array($id)) {
			$ids = array_filter($id, "is_int");

			if (count($ids) < 1) {
				return;
			}

			$ids = implode(",", $ids);

			$query = "DELETE FROM {$snippet_table} WHERE id IN ({$ids}) AND user_id=".db_param();
			db_query($query, array($user_id));

		} else {
			$query = "DELETE FROM {$snippet_table} WHERE id=".db_param()." AND user_id=".db_param();
			db_query($query, array($id, $user_id));
		}
	}

	/**
	 * Delete all text objects for a given user.
	 *
	 * @param int $user_id User ID
	 */
	public static function delete_by_user_id($user_id) {
		$snippet_table = plugin_table("snippet");
		$query = "DELETE FROM {$snippet_table} WHERE user_id=".db_param();
		db_query($query, array($user_id));
	}

	/**
	 * Convert a database query result to an array of Snippet objects.
	 *
	 * @param IteratorAggregate $result Database query result
	 *
	 * @return Snippet[] objects
	 */
	private static function from_db_result($result) {
		$snippets = array();
		while ($row = db_fetch_array($result)) {
			$snippet = new Snippet(
				(int)$row['type'],
				$row['name'],
				Snippet::replace_legacy_placeholders($row['value']),
				(int)$row['user_id']);
			$snippet->id = (int)$row["id"];

			$snippets[$snippet->id] = $snippet;
		}

		return $snippets;
	}

	/**
	 * Replace legacy placeholders (e.g. %u) with modern ones (e.g. {user}).
	 *
	 * @param string $p_value The snippet to process.
	 *
	 * @return string The processed snippet.
	 */
	private static function replace_legacy_placeholders($p_value) {
		$t_value = $p_value;
		$t_value = str_replace('%u', PLACEHOLDER_USER,     $t_value);
		$t_value = str_replace('%r', PLACEHOLDER_REPORTER, $t_value);
		$t_value = str_replace('%h', PLACEHOLDER_HANDLER,  $t_value);
		$t_value = str_replace('%p', PLACEHOLDER_PROJECT,  $t_value);
		return $t_value;        
	}

	public static function global_url($p_is_global = true) {
		if ($p_is_global) {
			return '&global=true';
		}
		return '';
	}

	/**
	 * Returns an array with names of form fields (text areas) where snippets should be
	 * available for selection.
	 */
	public static function get_configured_field_names() {
		return preg_split("/[,;\s]+/", plugin_config_get("textarea_names", "bugnote_text"));
	}

	/**
	 * Returns an array of ('text area field name' => 'language resource identifier') pairs
	 * that describe available (supported) text areas. Values will be passed to lang_get().
	 */
	public static function get_available_field_names() {
		return array(
			'bugnote_text' => 'bugnote',
			'description' => 'description',
			'steps_to_reproduce' => 'steps_to_reproduce',
			'additional_info' => 'additional_information',
			'body' => 'reminder'
		);
	}
}
