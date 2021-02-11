<?php

# Copyright (c) 2010 - 2012  John Reese
# Copyright (c) 2012 - 2018  MantisBT Team - mantisbt-dev@lists.sourceforge.net
# Licensed under the MIT license

$t_page_name = basename( __FILE__, '.php' );
$global = gpc_get_bool( "global", false );

if( $global ) {
	access_ensure_global_level( plugin_config_get( "edit_global_threshold" ) );
	$admin = access_has_global_level( config_get( "manage_plugin_threshold" ) );
	$user_id = 0;
	$t_current_page = $t_page_name . '&amp;global';
} else {
	access_ensure_global_level( plugin_config_get( "edit_own_threshold" ) );
	$user_id = auth_get_current_user_id();
	# This is a hack to trick the HTML API which relies on strpos to determine
	# the active tab, to only highlight the "My Snippets" tab and not the
	# "Global Snippets" one when the former is active
	$t_current_page = $t_page_name . '"';
}

$snippets = Snippet::load_by_user_id( $user_id );
$page_title = plugin_lang_get( $global ? "list_global_title" : "list_title" ) ;

layout_page_header($page_title);
layout_page_begin();

print_account_menu( $t_current_page );
?>
<div class="col-md-12 col-xs-12">

	<div class="space-10"></div>

	<div class="form-container">
		<form action="<?php echo plugin_page( "snippet_list_action" ) ?>" method="post">
		<div class="widget-box widget-color-blue2">
			<div class="widget-header widget-header-small">
				<h4 class="widget-title lighter">
					<i class="ace-icon fa fa-file-o"></i>
					<?php echo $page_title ?>
				</h4>
				<?php echo form_security_field( "plugin_Snippets_list_action" ) ?>
			</div>

			<div class="widget-body">
				<div class="widget-main no-padding">
					<div class="table-responsive">

<?php
	if( $global ) {
?>
						<input type="hidden" name="global" value="true"/>
<?php
		if( $admin ) {
?>
						<div class="widget-toolbox padding-8 clearfix">
<?php
			print_link_button(
				plugin_page( 'config_page' ) . '&return_page='. $t_page_name,
				plugin_lang_get( 'config' ), 'btn-xs'
			);
?>
						</div>
<?php
		}
	}
?>

						<table class="table table-striped table-bordered table-condensed table-hover">
							<thead>
								<tr>
								<th width="5%"></th>
								<th><?php echo plugin_lang_get( "list_name" ) ?></th>
								<th><?php echo plugin_lang_get( "list_value" ) ?></th>
								</tr>
							</thead>
							<tbody>
							<?php foreach( Snippet::clean( $snippets ) as $snippet ): ?>
								<tr>
									<td class="center">
										<input type="checkbox" class="ace" name="snippet_list[]" value="<?php echo $snippet->id ?>"/><span class="lbl"></span>
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
					<input class="ace snippets_select_all" type="checkbox"/><span class="lbl"></span>
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
								<td><input type="text" name="name" size="40" /></td>
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

