<?php

# Copyright (c) 2010 - 2012  John Reese
# Copyright (c) 2012 - 2016  MantisBT Team - mantisbt-dev@lists.sourceforge.net
# Licensed under the MIT license

$global = gpc_get_bool("global", false);

if ($global) {
	access_ensure_global_level(plugin_config_get("edit_global_threshold"));
	$admin = access_has_global_level(config_get("manage_plugin_threshold"));
	$user_id = 0;
} else {
	access_ensure_global_level(plugin_config_get("edit_own_threshold"));
	$user_id = auth_get_current_user_id();
}

$snippets = Snippet::load_by_user_id($user_id);

html_page_top();

if ($global) print_manage_menu();
?>

<div class="form-container">
<form action="<?php echo plugin_page("snippet_list_action") ?>" method="post">
<fieldset>

<legend><span><?php
	echo plugin_lang_get($global ? "list_global_title" : "list_title");
?></span></legend>

<?php echo form_security_field("plugin_Snippets_list_action") ?>

<?php
	if ($global) {
?>
			<input type="hidden" name="global" value="true"/>
<?php
		if ($admin) {
?>
			<div class="floatright"><?php
				print_bracket_link(plugin_page("config_page"), plugin_lang_get("config"));
			?></div>
<?php
		}
	} else {
		print_account_menu();
	}
?>

<table>

<thead>
	<tr class="row-category">
		<th width="5%"> </td>
		<th><?php echo plugin_lang_get("list_name") ?></th>
		<th><?php echo plugin_lang_get("list_value") ?></th>
	</tr>
</thead>

<tbody>
<?php foreach(Snippet::clean($snippets) as $snippet): ?>
	<tr>
		<td class="center">
			<input type="checkbox" name="snippet_list[]" value="<?php echo $snippet->id ?>"/>
		</td>
		<td><?php echo $snippet->name ?></td>
		<td><?php echo $snippet->value ?></td>
	</tr>
<?php endforeach ?>
</tbody>

<tfoot>
	<tr>
		<td class="center">
			<input class="snippets_select_all" type="checkbox"/>
		</td>
		<td colspan="2">
			<select class="snippets_select_action" name="action">
				<option value="edit"><?php echo plugin_lang_get("action_edit") ?></option>
				<option value="delete"><?php echo plugin_lang_get("action_delete") ?></option>
			</select>
			<input class="snippets_select_submit" type="submit" value="<?php echo plugin_lang_get("action_go") ?>"/>
		</td>
	</tr>
</tfoot>

</table>
</fieldset>
</form>
</div>


<div class="form-container">

<form action="<?php echo plugin_page("snippet_create") ?>" method="post">
<?php echo form_security_field("plugin_snippets_create") ?>
<?php if ($global): ?><input type="hidden" name="global" value="true"/><?php endif ?>

<table>

<tr>
<td class="form-title" colspan="2"><?php echo plugin_lang_get($global ? "create_global_title" : "create_title") ?></td>
</tr>

<tr class="row-1">
<td class="category"><?php echo plugin_lang_get("create_name") ?></td>
<td><input name="name"/></td>
</tr>

<tr class="row-2">
<td class="category"><?php echo plugin_lang_get("create_value") ?></td>
<td class="snippetspatternhelp"><textarea name="value" cols="80" rows="6"></textarea></td>
</tr>

</table>

<div class="button-submit center">
	<input type="submit" value="<?php echo plugin_lang_get("action_create") ?>"/>
</div>

</form>
</div>

<?php
html_page_bottom();

