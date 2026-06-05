<?php

# Copyright (c) 2010 - 2012  Amethyst Reese
# Copyright (c) 2012 - 2021  MantisBT Team - mantisbt-dev@lists.sourceforge.net
# Licensed under the MIT license

/** @noinspection PhpUnhandledExceptionInspection */

use Mantis\Exceptions\ClientException;

form_security_validate( "plugin_Snippets_list_action" );

$f_global = gpc_get_bool( "global" );

if( $f_global ) {
	access_ensure_global_level( plugin_config_get( "edit_global_threshold" ) );
	$t_user_id = 0;
}
else {
	access_ensure_global_level( plugin_config_get( "edit_own_threshold" ) );
	$t_user_id = auth_get_current_user_id();
}

$t_redirect_page = plugin_page( "snippet_list", true ) . Snippet::global_url( $f_global );

$f_action = gpc_get_string( "action" );
if( $f_action == 'sort_order' ) {
	if( $f_global ) {
		throw new ClientException(
			"Must use plugin config page to update global sort order",
			ERROR_PLUGIN_GENERIC
		);
	}

	$t_sort_order = gpc_get_int( 'sort_order' );
	if( !array_key_exists( $t_sort_order, SnippetsPlugin::get_sort_options() ) ) {
		throw new ClientException(
			"Invalid sort option",
			ERROR_INVALID_FIELD_VALUE,
			[ 'sort_option' ]
		);
	}

	# Set user's sort order, delete if equal to default
	$t_default_value = plugin_config_get( 'sort_order',
			SnippetsPlugin::SORT_ALPHA,
			false,
			ALL_USERS,
			ALL_PROJECTS
	);
	if( $t_sort_order != $t_default_value ) {
		plugin_config_set( 'sort_order', $t_sort_order, $t_user_id );
	}
	else {
		plugin_config_delete( 'sort_order', $t_user_id );
	}

	form_security_purge( 'plugin_Snippets_list_action' );
	print_header_redirect( $t_redirect_page );
}

$t_snippets_list = gpc_get_int_array( "snippet_list", array() );
$t_snippets = Snippet::load_by_id( $t_snippets_list, $t_user_id );
if( empty( $t_snippets ) ) {
	form_security_purge( "plugin_Snippets_list_action" );
	helper_ensure_confirmed(
		plugin_lang_get( 'action_nothing_to_do' ),
		lang_get( 'ok' )
	);
	print_header_redirect( $t_redirect_page );
}
$t_single = count( $t_snippets ) == 1;

### DELETE
if( $f_action == 'delete' ) {
	$t_snippet_names = array_column( Snippet::clean( $t_snippets ), 'name' );
	helper_ensure_confirmed(
		plugin_lang_get( "action_delete_confirm" )
		. "<br>" . implode( ", ", $t_snippet_names ),
		plugin_lang_get( "action_delete" )
	);

	$t_ids = array_keys( $t_snippets );
	foreach( $t_ids as $t_id ) {
		$t_data = array(
			'query' => array(
				'id' => $t_id,
			)
		);

		$t_command = new SnippetDeleteCommand( $t_data );
		$t_command->execute();
	}

	form_security_purge( "plugin_Snippets_list_action" );
	print_header_redirect( $t_redirect_page );

### EDIT
} elseif( $f_action == "edit" ) {
	$t_snippets = Snippet::clean( $t_snippets, Snippet::TARGET_FORM );
	layout_page_header();
	layout_page_begin();

	$t_page_name = basename( __FILE__, '.php' );
	print_account_menu( $t_page_name );
?>

<div class="col-md-12 col-xs-12">
	<div class="space-10"></div>

	<div id="snippet-div" class="form-container">
		<form action="<?php echo plugin_page( 'snippet_list_action' ) ?>"
			  method="post">
			<?php echo form_security_field( "plugin_Snippets_list_action" ) ?>
			<input type="hidden" name="action" value="update"/>
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
						<?php echo plugin_lang_get( $f_global ? 'edit_global_title' : 'edit_title' ) ?>
					</h4>
				</div>
				<div class="widget-body">
					<div class="widget-main no-padding table-responsive">
						<table class="table table-bordered table-condensed table-striped">
<?php
	foreach( $t_snippets as $t_snippet ) {
?>
							<tr>
<?php
		# Hide checkbox when operating on a single Snippet
		if( !$t_single ) {
?>
								<td class="category center"
									rowspan="2">
									<!--suppress HtmlFormInputWithoutLabel -->
									<input type="checkbox"
										   name="snippet_list[]"
										   class="ace"
										   value="<?php echo $t_snippet->id ?>"
										   checked="checked"
									/>
									<span class="lbl"></span>
								</td>
<?php
		}
?>
								<th>
<?php
		# Add hidden field with Snippet id
		if( $t_single ) {
?>
									<input type="hidden"
										   name="snippet_list[]"
										   value="<?php echo $t_snippet->id ?>"
										   checked="checked"
									/>
<?php
		}
		echo plugin_lang_get( "edit_name" );
		$t_textarea = 'value_' . $t_snippet->id;
?>
								</th>
								<td>
									<!--suppress HtmlFormInputWithoutLabel -->
									<input type="text" name="name_<?php echo $t_snippet->id ?>"
										   size="40" maxlength="<?php echo SnippetsPlugin::DB_FIELD_SIZE_NAME ?>"
										   value="<?php echo $t_snippet->name ?>"
									/>
								</td>
							</tr>

							<tr>
								<th>
									<label for="<?php echo $t_textarea; ?>">
										<?php echo plugin_lang_get( "edit_value"
										) ?>
									</label>
								</th>
								<td class="snippetspatternhelp">
									<textarea id="<?php echo $t_textarea; ?>"
											  name="<?php echo $t_textarea; ?>"
											  cols="80"
											  rows="6"><?php echo $t_snippet->value ?></textarea>
								</td>
							</tr>

<?php
		# Add a spacer if processing more than one Snippet
		if( !$t_single ) {
?>
							<tr class="spacer"><td></td></tr>
							<tr></tr>
<?php
		}
	} # foreach
?>

						</table>
					</div>

					<div class="widget-toolbox padding-8 clearfix">
						<button type="submit"
								class="btn btn-primary btn-white btn-round">
							<?php echo plugin_lang_get( "action_update" ) ?>
						</button>
					</div>
				</div>
			</div>
		</form>
	</div>

</div>

<?php
	layout_page_end();

### UPDATE
} elseif( $f_action == 'update' ) {
	foreach( $t_snippets as $t_snippet_id => $t_snippet ) {
		$t_new_name = gpc_get_string( "name_$t_snippet_id" );
		$t_new_value = gpc_get_string( "value_$t_snippet_id" );

		$t_data = array(
			'query' => array(
				'id' => $t_snippet_id,
			),
			'payload' => array(
				'name' => $t_new_name,
				'text' => $t_new_value,
			)
		);

		$t_command = new SnippetUpdateCommand( $t_data );
		$t_command->execute();
	}

	form_security_purge( "plugin_Snippets_list_action" );
	print_header_redirect( $t_redirect_page );
}
