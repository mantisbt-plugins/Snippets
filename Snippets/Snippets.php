<?php

# Copyright 2010 (c) John Reese
# Licensed under the MIT license

class SnippetsPlugin extends MantisPlugin {

	public function register() {
		$this->name = plugin_lang_get("name");
		$this->description = plugin_lang_get("description");

		$this->version = plugin_lang_get("version");
		$this->requires = array(
			"MantisCore" => "1.2.0",
			"jQuery" => "1.3",
		);

		$this->author = "John Reese";
		$this->contact = "jreese@leetcode.net";
		$this->url = "http://leetcode.net";
	}

	public function config() {
		return array(
			"edit_global_threshold" => ADMINISTRATOR,
		);
	}

	public function errors() {
		return array(
			"name_empty" => plugin_lang_get("error_name_empty"),
			"value_empty" => plugin_lang_get("error_value_empty"),
		);
	}

	public function hooks() {
		return array(
			"EVENT_MENU_ACCOUNT" => "menu_account",
			"EVENT_MENU_MANAGE" => "menu_manage",

			"EVENT_LAYOUT_RESOURCES" => "resources",
		);
	}

	public function init() {
		require_once("Snippets.API.php");
	}

	public function menu_account($event, $user_id) {
		$page = plugin_page("snippet_list");
		$label = plugin_lang_get("list_title");

		return "<a href=\"{$page}\">{$label}</a>";
	}

	public function menu_manage($event, $user_id) {
		$page = plugin_page("snippet_list") . "&global=true";
		$label = plugin_lang_get("list_global_title");

		return "<a href=\"{$page}\">{$label}</a>";
	}

	public function resources($event) {
		return '<script src="' . plugin_file("simpletip.js") . '"></script>
			<script src="' . plugin_file("caret.js") . '"></script>
			<script src="' . plugin_file("snippets.js") . '"></script>
			<link rel="stylesheet" type="text/css" href="' . plugin_file("snippets.css") . '"/>';
	}

	public function schema() {
		return array(
			# 2010-03-18
			array("CreateTableSQL", array(plugin_table("snippet"), "
				id			I		NOTNULL UNSIGNED AUTOINCREMENT PRIMARY,
				user_id		I		NOTNULL UNSIGNED,
				type		I		NOTNULL UNSIGNED,
				name		C(128)	NOTNULL,
				value		XL		NOTNULL
				")),
		);
	}
}

