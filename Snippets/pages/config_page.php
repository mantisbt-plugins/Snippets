<?php

# Copyright (c) 2010 - 2012  John Reese
# Copyright (c) 2012 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
# Licensed under the MIT license

access_ensure_global_level(config_get("manage_plugin_threshold"));

layout_page_header();

layout_page_begin();
print_manage_menu();
?>

<div class="col-md-12 col-xs-12">
<div class="space-10"></div>

<div class="form-container">
<form action="<?php echo plugin_page("config") ?>" method="post">
<?php echo form_security_field("plugin_Snippets_config") ?>
<div class="widget-box widget-color-blue2">
<div class="widget-header widget-header-small">
	<h4 class="widget-title lighter">
		<i class="ace-icon fa fa-file-o"></i>
		<?php echo plugin_lang_get( 'config_title' ) ?>
	</h4>
</div>

<div class="widget-body">
	<div class="widget-main no-padding">
		<div class="table-responsive">
			<table class="table table-bordered table-condensed table-striped">
	<tr>
		<td class="category"><?php echo plugin_lang_get( 'edit_global_threshold' ) ?></td>
		<td><select name="edit_global_threshold"><?php
			print_enum_string_option_list( 'access_levels', plugin_config_get( 'edit_global_threshold' ) );
		?></select></td>
	</tr>

	<tr>
		<td class="category"><?php echo plugin_lang_get( 'use_global_threshold' ) ?></td>
		<td><select name="use_global_threshold"><?php
			print_enum_string_option_list( 'access_levels', plugin_config_get( 'use_global_threshold' ) );
		?></select></td>
	</tr>

	<tr>
		<td class="category"><?php echo plugin_lang_get( 'edit_own_threshold' ) ?></td>
		<td><select name="edit_own_threshold"><?php
			print_enum_string_option_list( 'access_levels', plugin_config_get( 'edit_own_threshold' ) );
		?></select></td>
	</tr>


	<tr>
		<td class="category"><?php echo plugin_lang_get( 'textarea_names' ) ?></td>
		<td>
<?php
	$configuredNames = Snippet::get_configured_field_names();
	$availableNames = Snippet::get_available_field_names();

	foreach( $availableNames as $name => $lang_get_param ) {
		echo '<div><label><input type="checkbox" class="ace"name="textarea_names[]" value="', $name, '" ';
		check_checked( in_array( $name, $configuredNames ) );
		echo '/><span class="lbl">', lang_get( $lang_get_param ), "</span></label></div>\n";
	}
?>
		</td>
	</tr>
</table>
</div>
</div>

<div class="widget-toolbox padding-8 clearfix">
	<input type="submit" class="btn btn-primary btn-white btn-round" value="<?php echo plugin_lang_get('action_update') ?>"/>
</div>
</div>

</div>
</form>
</div>
</div>

<?php
layout_page_end();
