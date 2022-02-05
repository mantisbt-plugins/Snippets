<?php

# Copyright (c) 2010 - 2012  John Reese
# Copyright (c) 2012 - 2021  MantisBT Team - mantisbt-dev@lists.sourceforge.net
# Licensed under the MIT license

form_security_validate( "plugin_Snippets_list_action" );

$global = gpc_get_bool( "global" );

if( $global ) {
	access_ensure_global_level( plugin_config_get( "edit_global_threshold" ) );
	$user_id = 0;
}
else {
	access_ensure_global_level( plugin_config_get( "edit_own_threshold" ) );
	$user_id = auth_get_current_user_id();
}

$action = gpc_get_string( "action" );
$snippet_list = gpc_get_int_array( "snippet_list", array() );

$t_redirect_page = plugin_page( "snippet_list", true ) . Snippet::global_url( $global );

if( count( $snippet_list ) < 1 ) {
	form_security_purge( "plugin_Snippets_list_action" );
	helper_ensure_confirmed(
		plugin_lang_get( 'action_nothing_to_do' ),
		lang_get( 'ok' )
	);
	print_header_redirect( $t_redirect_page );
}

$snippets = Snippet::load_by_id( $snippet_list, $user_id );
$single = count( $snippets ) == 1;

### DELETE
if( $action == "delete" ) {
	$snippet_names = array_column( Snippet::clean( $snippets ), 'name' );
	helper_ensure_confirmed(
		plugin_lang_get( "action_delete_confirm" )
		. "<br>" . implode( ", ", $snippet_names ),
		plugin_lang_get( "action_delete" )
	);
	Snippet::delete_by_id( array_keys( $snippets ), $user_id );

	form_security_purge( "plugin_Snippets_list_action" );
	print_successful_redirect( $t_redirect_page );

### EDIT
} elseif( $action == "edit" ) {
	$snippets = Snippet::clean( $snippets, Snippet::TARGET_FORM );
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
	if( $global ) {
?>
			<input type="hidden" name="global" value="true"/>
<?php
	}
?>

			<div class="widget-box widget-color-blue2">
				<div class="widget-header widget-header-small">
					<h4 class="widget-title lighter">
						<i class="ace-icon fa fa-file-o"></i>
						<?php echo plugin_lang_get( $global ? 'edit_global_title' : 'edit_title' ) ?>
					</h4>
				</div>
				<div class="widget-body">
					<div class="widget-main no-padding table-responsive">
						<table class="table table-bordered table-condensed table-striped">
<?php
	foreach( $snippets as $snippet ) {
?>
							<tr>
<?php
		# Hide checkbox when operating on a single Snippet
		if( !$single ) {
?>
								<td class="category center"
									rowspan="2">
									<!--suppress HtmlFormInputWithoutLabel -->
									<input type="checkbox"
										   name="snippet_list[]"
										   class="ace"
										   value="<?php echo $snippet->id ?>"
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
		if( $single ) {
?>
									<input type="hidden"
										   name="snippet_list[]"
										   value="<?php echo $snippet->id ?>"
										   checked="checked"
									/>
<?php
		}
		echo plugin_lang_get( "edit_name" );
		$t_textarea = 'value_' . $snippet->id;
?>
								</th>
								<td>
									<!--suppress HtmlFormInputWithoutLabel -->
									<input type="text"
										   name="name_<?php echo $snippet->id ?>"
										   size="40"
										   value="<?php echo $snippet->name ?>"
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
											  rows="6"><?php echo $snippet->value ?></textarea>
								</td>
							</tr>

<?php
		# Add a spacer if processing more than one Snippet
		if( !$single ) {
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
} elseif( $action == "update" ) {
	foreach( $snippets as $snippet_id => $snippet ) {
		$new_name = gpc_get_string( "name_$snippet_id" );
		$new_value = gpc_get_string( "value_$snippet_id" );

		if( $snippet->name != $new_name || $snippet->value != $new_value ) {
			if( !is_blank( $new_name ) ) {
				$snippet->name = $new_name;
			}
			if( !is_blank( $new_value ) ) {
				$snippet->value = $new_value;
			}

			$snippet->save();
		}
	}

	form_security_purge( "plugin_Snippets_list_action" );
	print_successful_redirect( $t_redirect_page );
}
