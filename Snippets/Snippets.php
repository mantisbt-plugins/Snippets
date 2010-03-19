<?php

# Copyright 2010 (c) John Reese
# Licensed under the MIT license

class SnippetsPlugin extends MantisPlugin {

	public function register() {
		$this->name = plugin_lang_get("name");
		$this->description = plugin_lang_get("description");

		$this->version = "0.1";
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
			"global_text_threshold" => ADMINISTRATOR,
		);
	}

	public function hooks() {
		return array(
			"EVENT_MENU_ACCOUNT" => "menu_account",

			"EVENT_BUGNOTE_ADD_FORM" => "bugnote_add_form",
		);
	}

	public function init() {
		require_once("Snippets.API.php");
	}

	public function menu_account($event, $user_id) {
		$page = plugin_page("account_snippets");
		$label = plugin_lang_get("name");

		return "<a href=\"{$page}\">{$label}</a>";
	}

	public function bugnote_add_form($event, $bug_id) {
		echo '<script src="', plugin_file("snippets.js"), '"></script>';
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

