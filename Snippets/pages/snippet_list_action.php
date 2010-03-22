<?php

# Copyright 2010 (c) John Reese
# Licensed under the MIT license

form_security_validate("plugin_Snippets_list_action");

$global = gpc_get_bool("global", false);

if ($global) {
	access_ensure_global_level(plugin_config_get("edit_global_threshold"));
	$user_id = 0;
} else {
	$user_id = auth_get_current_user_id();
}

$action = gpc_get_string("action");

$snippet_list = gpc_get_int_array("snippet_list", array());

if (count($snippet_list) < 1) {
	form_security_purge("plugin_Snippets_list_action");
	print_header_redirect(plugin_page("snippet_list", true) . ($global ? "&global=true" : ""));
}

$snippets = Snippet::load_by_id($snippet_list, $user_id);

function array_object_properties($arr, $prop) {
	$props = array();
	foreach($arr as $key => $obj) {
		$props[$key] = $obj->$prop;
	}
	return $props;
}

### DELETE
if ($action == "delete") {
	$snippet_names = array_object_properties(Snippet::clean($snippets), "name");
	helper_ensure_confirmed(plugin_lang_get("action_delete_confirm") . "<br/>" . implode(", ", $snippet_names), plugin_lang_get("action_delete"));
	Snippet::delete_by_id(array_keys($snippets), $user_id);

	form_security_purge("plugin_Snippets_list_action");
	print_successful_redirect(plugin_page("snippet_list", true) . ($global ? "&global=true" : ""));

### EDIT
} elseif ($action == "edit") {
	$snippets = Snippet::clean($snippets, "form");
	html_page_top();
?>

<br/>
<form action="<?php echo plugin_page("snippet_list_action") ?>" method="post">
<?php echo form_security_field("plugin_Snippets_list_action") ?>
<?php if ($global): ?><input type="hidden" name="global" value="true"/><?php endif ?>
<input type="hidden" name="action" value="update"/>
<table class="width75" align="center">

<tr>
<td class="form-title" colspan="3"><?php echo plugin_lang_get($global ? "edit_global_title" : "edit_title") ?></td>
</tr>

<?php $first = true; foreach ($snippets as $snippet): ?>
<?php if (!$first): ?><tr class="spacer"><td></td></tr><?php endif ?>

<tr <?php echo helper_alternate_class() ?>>
<td class="center" rowspan="2"><input type="checkbox" name="snippet_list[]" value="<?php echo $snippet->id ?>" checked="checked"/></td>
<td class="category"><?php echo plugin_lang_get("edit_name") ?></td>
<td><input name="name_<?php echo $snippet->id ?>" value="<?php echo $snippet->name ?>"/></td>
</tr>

<tr <?php echo helper_alternate_class() ?>>
<td class="category"><?php echo plugin_lang_get("edit_value") ?></td>
<td><textarea name="value_<?php echo $snippet->id ?>" cols="80" rows="6"><?php echo $snippet->value ?></textarea></td>
</tr>

<?php $first = false; endforeach ?>

<tr>
<td><input type="checkbox" class="snippets_select_all" checked="checked"/></td>
<td class="center" colspan="2"><input type="submit" value="<?php echo plugin_lang_get("action_edit") ?>"/></td>
</tr>

</table>
</form>

<?php
	html_page_bottom();

### UPDATE
} elseif ($action == "update") {
	foreach($snippets as $snippet_id => $snippet) {
		$new_name = gpc_get_string("name_{$snippet_id}");
		$new_value = gpc_get_string("value_{$snippet_id}");

		if ($snippet->name != $new_name
			|| $snippet->value != $new_value)
		{
			$snippet->name = $new_name;
			$snippet->value = $new_value;

			$snippet->save();
		}
	}

	form_security_purge("plugin_Snippets_list_action");
	print_successful_redirect(plugin_page("snippet_list", true) . ($global ? "&global=true" : ""));

}


