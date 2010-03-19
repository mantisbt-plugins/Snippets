<?php

# Copyright 2010 (c) John Reese
# Licensed under the MIT license

function xmlhttprequest_plugin_snippets() {
	plugin_push_current("Snippets");

	echo json_encode(array(
		"lang" => array(
			"label" => plugin_lang_get("select_label"),
			"default" => plugin_lang_get("select_default"),
		),
		"bugnote_text" => array(
			"foo" => "foobar",
		),
	));

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
	 * Delete a single text object with the given ID.
	 *
	 * @param int Text ID
	 */
	public static function delete_by_id($id) {
		$snippet_table = plugin_table("snippet");
		$query = "DELETE FROM {$snippet_table} WHERE id=".db_param();
		db_query_bound($query, array($id));
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

