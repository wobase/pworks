<?php
date_default_timezone_set("Asia/Shanghai");

error_reporting(E_ALL & ~E_NOTICE);

require_once('config.inc.php');

define('APP_ROOT', dirname(__FILE__));


set_include_path(get_include_path()
. PATH_SEPARATOR . '/usr/lib/php/pear'
. PATH_SEPARATOR . APP_ROOT
);


define('PWORKS_CONFIG_FILE_PATH', APP_ROOT . '/pworks.inc.php');


error_log(var_export($_REQUEST, true));

require_once('pworks/pworks.php');
