<?php

define('GRIDFIELDEXTRAS_DIR', 'gridfieldextras');

$dir = basename(dirname(__FILE__));
if($dir != "gridfieldextras") {
	user_error('Directory name must be "gridfieldextras" (currently "'.$dir.'")',E_USER_ERROR);
}