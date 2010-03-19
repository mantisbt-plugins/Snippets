<?php

# Copyright 2010 (c) John Reese
# Licensed under the MIT license

html_page_top();
?>

<br/>
<form action="<?php echo plugin_page("snippet_create") ?>" method="post">
<?php echo form_security_field("plugin_snippets_create") ?>
<table class="width60" align="center">

<tr>
<td class="form-title" colspan="2">Create Snippet</td>
</tr>

<tr <?php echo helper_alternate_class() ?>>
<td class="category">Short Name</td>
<td><input name="name"/></td>
</tr>

<tr <?php echo helper_alternate_class() ?>>
<td class="category">Full Text</td>
<td><textarea name="value" cols="60" rows="6"></textarea></td>
</tr>

<tr>
<td class="center" colspan="2"><input type="submit"/></td>
</tr>

</table>
</form>

<?php
html_page_bottom();

