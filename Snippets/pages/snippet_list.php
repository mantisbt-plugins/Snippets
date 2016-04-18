<?php

# Copyright (c) 2010 - 2012  John Reese
# Copyright (c) 2012 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
# Licensed under the MIT license

$global = gpc_get_bool( "global", false );

if( $global ) {
	access_ensure_global_level( plugin_config_get( "edit_global_threshold" ) );
	$admin = access_has_global_level( config_get( "manage_plugin_threshold" ) );
	$user_id = 0;
} else {
	access_ensure_global_level( plugin_config_get( "edit_own_threshold" ) );
	$user_id = auth_get_current_user_id();
}

$snippets = Snippet::load_by_user_id( $user_id );

layout_page_header();

layout_page_begin();

if( $global ) {
	print_manage_menu();
} else {
	print_account_menu();
}
?>
<div class="col-md-12 col-xs-12">

	<div class="space-10"></div>

	<div class="form-container">
		<form action="<?php echo plugin_page( "snippet_list_action" ) ?>" method="post">
		<div class="widget-box widget-color-blue2">
			<div class="widget-header widget-header-small">
				<h4 class="widget-title lighter">
					<i class="ace-icon fa fa-file-o"></i>
					<?php echo plugin_lang_get( $global ? "list_global_title" : "list_title" ) ?>
				</h4>
				<?php echo form_security_field( "plugin_Snippets_list_action" ) ?>

<?php
	if( $global ) {
?>
				<input type="hidden" name="global" value="true"/>
<?php
		if( $admin ) {
            print_button( plugin_page( "config_page" ), plugin_lang_get( "config" ) );
		}
	}
?>
			</div>

			<div class="widget-body">
				<div class="widget-main no-padding">
					<div class="table-responsive">

						<table class="table table-striped table-bordered table-condensed table-hover">
							<thead>
								<tr>
								<th width="5%"> </td>
								<th><?php echo plugin_lang_get( "list_name" ) ?></th>
								<th><?php echo plugin_lang_get( "list_value" ) ?></th>
								</tr>
							</thead>
							<tbody>
							<?php foreach( Snippet::clean( $snippets ) as $snippet ): ?>
								<tr>
									<td class="center">
										<input type="checkbox" class="ace"name="snippet_list[]" value="<?php echo $snippet->id ?>"/><span class="lbl"></span>
									</td>
									<td><?php echo $snippet->name ?></td>
									<td><?php echo $snippet->value ?></td>
								</tr>
							<?php endforeach ?>
							</tbody>
						</table>
					</div>
				</div>
				<div class="widget-toolbox padding-8 clearfix">
					<input class="ace" type="checkbox"/><span class="lbl"></span>
					<select class="snippets_select_action" name="action">
						<option value="edit"><?php echo plugin_lang_get( "action_edit" ) ?></option>
						<option value="delete"><?php echo plugin_lang_get( "action_delete" ) ?></option>
					</select>
					<input class="btn btn-primary btn-white btn-sm btn-round" type="submit" value="<?php echo plugin_lang_get( "action_go" ) ?>"/>
				</div>
			</div>
		</div>
		</form>
	</div>

	<div class="space-10"></div>

	<div class="form-container">

		<form action="<?php echo plugin_page( "snippet_create" ) ?>" method="post">
			<?php echo form_security_field( "plugin_snippets_create" ) ?>
			<?php if( $global ): ?><input type="hidden" name="global" value="true"/><?php endif ?>

		<div class="widget-box widget-color-blue2">
			<div class="widget-header widget-header-small">
				<h4 class="widget-title lighter">
					<i class="ace-icon fa fa-file-o"></i>
					<?php echo plugin_lang_get( $global ? "create_global_title" : "create_title" ) ?>
				</h4>
			</div>

			<div class="widget-body">
				<div class="widget-main no-padding">
					<div class="table-responsive">
						<table class="table table-bordered table-condensed table-striped">
							<tr>
								<td class="category"><?php echo plugin_lang_get( "create_name" ) ?></td>
								<td><input name="name"/></td>
							</tr>

							<tr>
								<td class="category"><?php echo plugin_lang_get( "create_value" ) ?></td>
								<td class="snippetspatternhelp"><textarea name="value" cols="80" rows="6"></textarea></td>
							</tr>
						</table>
					</div>
				</div>
				<div class="widget-toolbox padding-8 clearfix">
					<input type="submit" class="btn btn-primary btn-white btn-round" value="<?php echo plugin_lang_get( 'action_create' ) ?>"/>
				</div>
			</div>
		</div>
		</form>
	</div>
</div>
<?php
layout_page_end();

