<?php

# Copyright 2010 (c) John Reese
# Licensed under the MIT license

$global = gpc_get_bool("global", false);

if ($global) {
	access_ensure_global_level(plugin_config_get("edit_global_threshold"));
	$user_id = 0;
} else {
	$user_id = auth_get_current_user_id();
}

$snippets = Snippet::load_by_user_id($user_id);

html_page_top();

if ($global) print_manage_menu();
?>

<br/>
<form action="<?php echo plugin_page("snippet_list_action"), $global ? "&global=true" : "" ?>" method="post">
<?php echo form_security_field("plugin_Snippets_list_action") ?>
<table class="width75" align="center">

<tr>
<td class="form-title" colspan="2"><?php echo plugin_lang_get($global ? "list_global_title" : "list_title") ?></td>
<td class="right"><?php if (!$global) print_account_menu() ?></td>
</tr>

<tr class="row-category">
<td> </td>
<td><?php echo plugin_lang_get("list_name") ?></td>
<td><?php echo plugin_lang_get("list_value") ?></td>
</tr>

<?php foreach(Snippet::clean($snippets) as $snippet): ?>
<tr <?php echo helper_alternate_class() ?>>
<td class="center"><input type="checkbox" name="snippet_list[]" value="<?php echo $snippet->id ?>"/></td>
<td><?php echo $snippet->name ?></td>
<td><?php echo $snippet->value ?></td>
</tr>

<?php endforeach ?>

<tr>
<td class="center"><input class="snippets_select_all" type="checkbox"/></td>
<td colspan="2">
	<select class="snippets_select_action" name="action">
		<option value="edit"><?php echo plugin_lang_get("action_edit") ?></option>
		<option value="delete"><?php echo plugin_lang_get("action_delete") ?></option>
	</select>
	<input class="snippets_select_submit" type="submit" value="<?php echo plugin_lang_get("action_go") ?>"/>
</td>
</tr>

</table>
</form>

<br/>
<form action="<?php echo plugin_page("snippet_create") ?>" method="post">
<?php echo form_security_field("plugin_snippets_create") ?>
<table class="width75" align="center">

<tr>
<td class="form-title" colspan="2">Create Snippet</td>
</tr>

<tr <?php echo helper_alternate_class() ?>>
<td class="category">Short Name</td>
<td><input name="name"/></td>
</tr>

<tr <?php echo helper_alternate_class() ?>>
<td class="category">Full Text</td>
<td><textarea name="value" cols="80" rows="6"></textarea></td>
</tr>

<tr>
<td class="center" colspan="2"><input type="submit"/></td>
</tr>

</table>
</form>

<?php
html_page_bottom();

