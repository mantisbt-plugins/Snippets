<?php
# Copyright (c) 2010 - 2012  Amethyst Reese
# Copyright (c) 2012 - 2021  MantisBT Team - mantisbt-dev@lists.sourceforge.net
# Licensed under the MIT license

header('Content-Type: application/json');

$t_help = array(
	'title' => plugin_lang_get('pattern_title'),
	'text'  => plugin_lang_get('pattern_help'),
);

echo json_encode($t_help);
