<?php

# Copyright (c) 2010 - 2012  John Reese
# Copyright (c) 2012 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
# Licensed under the MIT license

class SnippetsPlugin extends MantisPlugin {
	public static $_version = "1.0.0";

	public function register() {
		$this->name = plugin_lang_get("name");
		$this->description = plugin_lang_get("description");
		$this->page = "config_page";

		$this->version = self::$_version;

		$this->requires = array(
			"MantisCore" => "1.3",
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
		if (access_has_global_level(plugin_config_get("edit_own_threshold"))) {
			$page = plugin_page("snippet_list");
			$label = plugin_lang_get("list_title");

			return "<a href=\"{$page}\">{$label}</a>";
		}
	}

	public function menu_manage($event, $user_id) {
		if (access_has_global_level(plugin_config_get("edit_global_threshold"))) {
			$page = plugin_page("snippet_list") . Snippet::global_url();
			$label = plugin_lang_get("list_global_title");

			return '<a href="' . string_html_specialchars( $page ) . '">' . $label . '</a>';
		}
	}

	public function resources($event) {
		return '<script src="' . plugin_file("simpletip.js") . '"></script>
			<script src="' . plugin_file("jquery-textrange.js") . '"></script>
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