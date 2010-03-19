<?php

# Copyright 2010 (c) John Reese
# Licensed under the MIT license

class SavedTextPlugin extends MantisPlugin {

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
			"EVENT_BUGNOTE_ADD_FORM" => "bugnote_add_form",
		);
	}

	public function init() {
		require_once("SavedText.API.php");
	}

	public function bugnote_add_form($event, $bug_id) {
		echo '<script src="', plugin_file("select.js"), '"></script>';
	}

	public function schema() {
		return array(
			# 2010-03-18
			array("CreateTableSQL", array(plugin_table("item"), "
				id			I		NOTNULL UNSIGNED AUTOINCREMENT PRIMARY,
				user_id		I		NOTNULL UNSIGNED,
				type		I		NOTNULL UNSIGNED,
				name		C(128)	NOTNULL,
				value		XL		NOTNULL
				")),
		);
	}
}

