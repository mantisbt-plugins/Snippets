<?php

# Copyright 2010 (c) John Reese
# Licensed under the MIT license

function xmlhttprequest_plugin_snippets() {
	plugin_push_current("Snippets");

	# load snippets available to the user
	$user_id = auth_get_current_user_id();
	$snippets = Snippet::load_by_type_user(0, $user_id);

	$data_array = array(
		"lang" => array(
			"label" => plugin_lang_get("select_label"),
			"default" => plugin_lang_get("select_default"),
		),
		"bugnote_text" => array(),
	);

	# arrange the available snippets into the data array
	foreach($snippets as $snippet) {
		$snippet = Snippet::clean($snippet);
		$data_array["bugnote_text"][$snippet->id] = $snippet;
	}

	$json = json_encode($data_array);
	file_put_contents("/tmp/snippets", print_r($json, true));
	echo $json;

	plugin_pop_current();
}

/**
 * Object representing a saved block of text.
 */
class Snippet {
	public $id;
	public $user_id;
	public $type;
	public $name;
	public $value;

	/**
	 * Create a new text object with the given details.
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
	 * @return int Text ID if created
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

			db_query_bound($query, array(
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

			db_query_bound($query, array(
				$this->type,
				$this->name,
				$this->value,
				$this->user_id,
				$this->id
			));
		}
	}

	/**
	 * Create a copy of the given object with strings cleaned for output.
	 *
	 * @param object Snippet object
	 * @return object Cleaned snippet object
	 */
	public static function clean($dirty, $target="view") {
		if (is_array($dirty)) {
			$cleaned = array();
			foreach($dirty as $id => $snippet) {
				$cleaned[$id] = self::clean($snippet, $target);
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
	 * Load snippets by ID.
	 *
	 * @param mixed Snippet ID (int or array)
	 * @param int User ID
	 * @return mixed Snippet(s)
	 */
	public static function load_by_id($id, $user_id) {
		$snippet_table = plugin_table("snippet");

		if (is_array($id)) {
			$ids = array_filter($id, "is_int");
			$ids = implode(",", $ids);

			$query = "SELECT * FROM {$snippet_table} WHERE id IN ({$ids}) AND user_id=".db_param();
			$result = db_query_bound($query, array($user_id));

			return self::from_db_result($result);

		} else {
			$query = "SELECT * FROM {$snippet_table} WHERE id=".db_param()." AND user_id=".db_param();
			$result = db_query_bound($query, array($id, $user_id));

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
	 * @return array Text objects
	 */
	public static function load_by_type_user($type, $user_id, $include_global=true) {
		$user_ids = array((int) $user_id);
		if ($include_global) {
			$user_ids[] = 0;
		}
		$user_ids = implode(",", $user_ids);

		$snippet_table = plugin_table("snippet");

		$query = "SELECT * FROM {$snippet_table} WHERE type=".db_param()." AND user_id IN ({$user_ids}) ORDER BY name";
		$result = db_query_bound($query, array($type));

		return self::from_db_result($result);
	}

	/**
	 * Load text objects for a given user id.
	 *
	 * @param int User ID
	 * @return array Text objects
	 */
	public static function load_by_user_id($user_id) {
		$snippet_table = plugin_table("snippet");

		$query = "SELECT * FROM {$snippet_table} WHERE user_id=".db_param()." ORDER BY name";
		$result = db_query_bound($query, array($user_id));

		return self::from_db_result($result);
	}

	/**
	 * Delete snippets with the given ID.
	 *
	 * @param mixed Snippet ID (int or array)
	 */
	public static function delete_by_id($id, $user_id) {
		$snippet_table = plugin_table("snippet");

		if (is_array($id)) {
			$ids = array_filter($id, "is_int");
			$ids = implode(",", $ids);

			$query = "DELETE FROM {$snippet_table} WHERE id IN ({$ids}) AND user_id=".db_param();
			db_query_bound($query, array($user_id));

		} else {
			$query = "DELETE FROM {$snippet_table} WHERE id=".db_param()." AND user_id=".db_param();
			db_query_bound($query, array($id, $user_id));
		}
	}

	/**
	 * Delete all text objects for a given user.
	 *
	 * @param int User ID
	 */
	public static function delete_by_user_id($user_id) {
		$snippet_table = plugin_table("snippet");
		$query = "DELETE FROM {$snippet_table} WHERE user_id=".db_param();
		db_query_bound($query, array($user_id));
	}

	/**
	 * Convert a database query result to an array of text objects.
	 *
	 * @param object Database query result
	 * @return array Text objects
	 */
	private static function from_db_result($result) {
		$snippets = array();
		while ($row = db_fetch_array($result)) {
			$snippet = new Snippet($row["type"], $row["name"], $row["value"], $row["user_id"]);
			$snippet->id = $row["id"];

			$snippets[$row["id"]] = $snippet;
		}

		return $snippets;
	}
}

