<?php

# Copyright (c) 2010 - 2012  John Reese
# Copyright (c) 2012 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
# Licensed under the MIT license

access_ensure_global_level(config_get("manage_plugin_threshold"));

html_page_top();
print_manage_menu();
?>

<br/>
<form action="<?php echo plugin_page("config") ?>" method="post">
<?php echo form_security_field("plugin_Snippets_config") ?>
<table class="width60" align="center">

<tr>
<td class="form-title" colspan="2"><?php echo plugin_lang_get("config_title") ?></td>
</tr>

<tr <?php echo helper_alternate_class() ?>>
<td class="category"><?php echo plugin_lang_get( 'edit_global_threshold' ) ?></td>
<td><select name="edit_global_threshold"><?php print_enum_string_option_list( 'access_levels', plugin_config_get( 'edit_global_threshold' ) ) ?></select></td>
</tr>

<tr <?php echo helper_alternate_class() ?>>
<td class="category"><?php echo plugin_lang_get( 'use_global_threshold' ) ?></td>
<td><select name="use_global_threshold"><?php print_enum_string_option_list( 'access_levels', plugin_config_get( 'use_global_threshold' ) ) ?></select></td>
</tr>

<tr <?php echo helper_alternate_class() ?>>
<td class="category"><?php echo plugin_lang_get( 'edit_own_threshold' ) ?></td>
<td><select name="edit_own_threshold"><?php print_enum_string_option_list( 'access_levels', plugin_config_get( 'edit_own_threshold' ) ) ?></select></td>
</tr>

<tr <?php echo helper_alternate_class() ?>>
<td class="category"><?php echo plugin_lang_get( 'textarea_names' ) ?></td>
<td>
<?php
  $configuredNames = Snippet::get_configured_field_names();
  $availableNames = Snippet::get_available_field_names();

  foreach( $availableNames as $name => $lang_get_param ) {
    echo '<div><label><input type="checkbox" name="textarea_names[]" value="', $name, '" ';
    check_checked( in_array($name, $configuredNames) );
    echo '/>', lang_get( $lang_get_param ), "</label></div>\n";
  }
?>
</td>
</tr>

<tr>
<td class="center" colspan="2"><input type="submit" value="<?php echo plugin_lang_get("action_update") ?>"/></td>
</tr>

</table>
</form>

<?php
html_page_bottom();
