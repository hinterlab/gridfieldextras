<?php

define('GRIDFIELDEXTRAS_DIR', 'silverstripe-gridfieldextras');

$dir = basename(dirname(__FILE__));
if($dir != "silverstripe-gridfieldextras") {
    user_error('Directory name must be "silverstripe-gridfieldextras" (currently "'.$dir.'")',E_USER_ERROR);
}
