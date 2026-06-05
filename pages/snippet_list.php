<?php

# Copyright (c) 2010 - 2012  Amethyst Reese
# Copyright (c) 2012 - 2021  MantisBT Team - mantisbt-dev@lists.sourceforge.net
# Licensed under the MIT license

$t_page_name = basename( __FILE__, '.php' );
$f_global = gpc_get_bool( "global", false );

if( $f_global ) {
	access_ensure_global_level( plugin_config_get( "edit_global_threshold" ) );
	$t_admin = access_has_global_level( config_get( "manage_plugin_threshold" ) );
	$t_user_id = 0;
	$t_current_page = $t_page_name . '&amp;global';
} else {
	access_ensure_global_level( plugin_config_get( "edit_own_threshold" ) );
	$t_admin = false;
	$t_user_id = auth_get_current_user_id();
	# This is a hack to trick the HTML API which relies on strpos to determine
	# the active tab, to only highlight the "My Snippets" tab and not the
	# "Global Snippets" one when the former is active
	$t_current_page = $t_page_name . '"';
}

$t_snippets = Snippet::load_by_user_id( $t_user_id );
$t_page_title = plugin_lang_get( $f_global ? "list_global_title" : "list_title" );

layout_page_header( $t_page_title );
layout_page_begin();

print_account_menu( $t_current_page );

$t_form_security_field = form_security_field( "plugin_Snippets_list_action" );
?>
	<div class="col-md-12 col-xs-12">

		<div class="space-10"></div>

		<div class="form-container">
			<form action="<?php echo plugin_page( "snippet_list_action" ) ?>"
				  method="post">
				<div class="widget-box widget-color-blue2">
					<div class="widget-header widget-header-small">
						<h4 class="widget-title lighter">
							<i class="ace-icon fa fa-file-o"></i>
							<?php echo $t_page_title ?>
						</h4>
						<?php echo $t_form_security_field; ?>
<?php
	if( $f_global ) {
?>
							<input type="hidden" name="global" value="true"/>
<?php
	}
?>
					</div>

					<div class="widget-body">
						<div class="widget-main no-padding">
							<div class="table-responsive">
								<div class="widget-toolbox padding-8 clearfix">
<?php
	# Jump to Create Snippet section
	print_link_button( "#create_snippet",
		plugin_lang_get( 'create_goto' ),
		'btn-sm'
	);

	if( $t_admin ) {
		echo '&nbsp;';
		print_link_button(
			plugin_page( 'config_page' ) . '&return_page=' . $t_page_name,
			plugin_lang_get( 'config' ),
			'btn-sm'
		);
	}

	# Sort order (only for personal Snippets)
	if( !$f_global ) {
?>
									<div class="pull-right">
										<form action="<?php echo plugin_page( "snippet_list_action" ) ?>">
											<label for="sort_order" class="padding-right-8">
												<?php echo plugin_lang_get( 'sort_order' ) ?>
											</label>
											<?php
											$t_current = plugin_config_get( 'sort_order' );
											SnippetsPlugin::print_sort_options_list( $t_current );
											echo $t_form_security_field;
											?>
											<button type="submit" name="action" value="sort_order"
											        class="btn btn-sm btn-white btn-round btn-primary">
												<?php echo plugin_lang_get( 'action_update' ); ?>
											</button>
										</form>
									</div>
<?php
	}
?>
								</div>

								<table class="table table-striped table-bordered table-condensed table-hover">
									<thead>
									<tr>
										<th class="width-5"></th>
										<th><?php echo plugin_lang_get( "list_name" ) ?></th>
										<th><?php echo plugin_lang_get( "list_value" ) ?></th>
									</tr>
									</thead>
									<tbody>
<?php
	foreach( Snippet::clean( $t_snippets ) as $t_snippet ): {
?>
										<tr>
											<td class="center">
												<!--suppress HtmlFormInputWithoutLabel -->
												<input type="checkbox"
													   class="ace"
													   name="snippet_list[]"
													   value="<?php echo $t_snippet->id ?>"
												/>
												<span class="lbl"></span>
											</td>
											<td><?php echo $t_snippet->name ?></td>
											<td><?php echo $t_snippet->value ?></td>
										</tr>
<?php
	} endforeach
?>
									</tbody>
								</table>
							</div>
						</div>

						<div class="widget-toolbox no-padding clearfix ">
							<table id="snippets-list-footer" class="table">
								<tr>
									<td class="center width-5">
										<input class="ace snippets_select_all"
											   type="checkbox"
											   title="<?php echo plugin_lang_get( 'action_select_all' ); ?>"
										/>
										<span class="lbl"></span>
									</td>
									<td>
										<button type="submit" name="action"
												value="edit"
												class="btn btn-primary btn-white btn-sm btn-round">
											<?php echo plugin_lang_get( "action_edit" ) ?>
										</button>
										<button type="submit" name="action"
												value="delete"
												class="btn btn-primary btn-white btn-sm btn-round">
											<?php echo plugin_lang_get( "action_delete" ) ?>
										</button>
									</td>
								</tr>
							</table>
						</div>
					</div>
				</div>
			</form>
		</div>

		<div class="space-10"></div>

		<div class="form-container">
			<a id="create_snippet"></a>

			<form action="<?php echo plugin_page( "snippet_create" ) ?>"
				  method="post">
				<?php echo form_security_field( "plugin_snippets_create" ) ?>
<?php
	if( $f_global ) {
?>
				<input type="hidden" name="global" value="true"/>
<?php
	}
?>
				<div class="widget-box widget-color-blue2">
					<div class="widget-header widget-header-small">
						<h4 class="widget-title lighter">
							<i class="ace-icon fa fa-file-o"></i>
							<?php echo plugin_lang_get( $f_global ? "create_global_title" : "create_title" ) ?>
						</h4>
					</div>

					<div class="widget-body">
						<div class="widget-main no-padding">
							<div class="table-responsive">
								<table class="table table-bordered table-condensed table-striped">
									<tr>
										<td class="category">
											<label for="name">
												<?php echo plugin_lang_get( "create_name" ) ?>
											</label>
										</td>
										<td>
											<input type="text" id="name" name="name"
												   size="40" maxlength="<?php echo SnippetsPlugin::DB_FIELD_SIZE_NAME ?>"
											/>
										</td>
									</tr>

									<tr>
										<td class="category">
											<label for="value">
												<?php echo plugin_lang_get( "create_value" ) ?>
											</label>
										</td>
										<td class="snippetspatternhelp">
											<textarea id="value" name="value"
													  cols="80"
													  rows="6"></textarea>
										</td>
									</tr>
								</table>
							</div>
						</div>
						<div class="widget-toolbox padding-8 clearfix">
							<input type="submit"
								   class="btn btn-primary btn-white btn-round"
								   value="<?php echo plugin_lang_get( 'action_create' ) ?>"
							/>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>

<?php
layout_page_end();

