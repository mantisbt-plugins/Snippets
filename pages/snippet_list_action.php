<?php

# Copyright (c) 2010 - 2012  John Reese
# Copyright (c) 2012 - 2017  MantisBT Team - mantisbt-dev@lists.sourceforge.net
# Licensed under the MIT license

form_security_validate("plugin_Snippets_list_action");

$global = gpc_get_bool("global", false);

if ($global) {
	access_ensure_global_level(plugin_config_get("edit_global_threshold"));
	$user_id = 0;
} else {
	access_ensure_global_level(plugin_config_get("edit_own_threshold"));
	$user_id = auth_get_current_user_id();
}

$action = gpc_get_string("action");

$snippet_list = gpc_get_int_array("snippet_list", array());

if (count($snippet_list) < 1) {
	form_security_purge("plugin_Snippets_list_action");
	print_header_redirect(plugin_page("snippet_list", true) . Snippet::global_url($global));
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
	print_successful_redirect(plugin_page("snippet_list", true) . Snippet::global_url($global));

### EDIT
} elseif ($action == "edit") {
	$snippets = Snippet::clean($snippets, "form");
	layout_page_header();
	layout_page_begin();
?>

<br/>

<div id="snippet-div" class="form-container">
	<form action="<?php echo plugin_page( 'snippet_list_action' ) ?>" method="post">
		<?php echo form_security_field("plugin_Snippets_list_action") ?>
		<?php if ($global): ?><input type="hidden" name="global" value="true"/><?php endif ?>
		<input type="hidden" name="action" value="update"/>

<div class="widget-box widget-color-blue2">
	<div class="widget-header widget-header-small">
		<h4 class="widget-title lighter">
			<i class="ace-icon fa fa-user"></i>
			<?php echo plugin_lang_get( $global ? 'edit_global_title' : 'edit_title' ) ?>
		</h4>
	</div>
	<div class="widget-body">
		<div class="widget-main no-padding">
			<div class="table-responsive">
				<table class="table table-bordered table-condensed table-striped">

		<fieldset>

<?php
	$first = true;
	$single = count( $snippets ) == 1;

	foreach( $snippets as $snippet ) {
		if ( !$first ) {
?>
<tr class="spacer"><td></td></tr>
<?php
		}
?>

<tr>
<?php
		# Hide checkbox when operating on a single Snippet
		if( !$single ) {
?>
<td class="center" rowspan="2"><input type="checkbox" name="snippet_list[]" value="<?php echo $snippet->id ?>" checked="checked"/></td>
<?php
		}
?>
<td class="category">
<?php
		# Add hidden field with Snippet id
		if( $single ) {
?>
<input type="hidden" name="snippet_list[]" value="<?php echo $snippet->id ?>" checked="checked"/>
<?php
		}
		echo plugin_lang_get("edit_name")
?>
</td>
<td><input type="text" name="name_<?php echo $snippet->id ?>" size="40" value="<?php echo $snippet->name ?>"/></td>
</tr>

<tr>
<td class="category"><?php echo plugin_lang_get("edit_value") ?></td>
<td class="snippetspatternhelp"><textarea name="value_<?php echo $snippet->id ?>" cols="80" rows="6"><?php echo $snippet->value ?></textarea></td>
</tr>

<?php
		$first = false;
	}
?>

<tfoot>
<tr>
<td class="center" colspan="3"><input type="submit" value="<?php echo plugin_lang_get("action_edit") ?>"/></td>
</tr>
</tfoot>

	</fieldset>
</table>
</form>
</div>

<?php
	layout_page_end();

### UPDATE
} elseif ($action == "update") {
	foreach($snippets as $snippet_id => $snippet) {
		$new_name = gpc_get_string("name_{$snippet_id}");
		$new_value = gpc_get_string("value_{$snippet_id}");

		if ($snippet->name != $new_name
			|| $snippet->value != $new_value)
		{
			if (!is_blank($new_name)) {
				$snippet->name = $new_name;
			}
			if (!is_blank($new_value)) {
				$snippet->value = $new_value;
			}

			$snippet->save();
		}
	}

	form_security_purge("plugin_Snippets_list_action");
	print_successful_redirect(plugin_page("snippet_list", true) . Snippet::global_url($global));

}
