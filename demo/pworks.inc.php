<?php
# ##################################
# 下面的常量必须在应的启动脚本中定义
# 1. APP_ROOT
#  用于指定应用系统的根目录，例如：
# define('APP_ROOT', '/var/www');
#
# 2. PWORK_CONFIG_FILE_PATH
#  用于完全替代当前文件中的设置的文件， 例如：
#  define('PWORK_CONFIG_FILE_PATH', APP_ROOT . '/pworks.inc.php');
#
# #################################

define('APP_NAME', 'yun-api');

//For develop mode
define('CONFIG_CACHE_SETTING', 'array');

//For production mode
# define('CONFIG_CACHE_SETTING', 'apc, array');

define('PWORKS_XML_CONFIG', APP_ROOT . '/pworks.xml');

//For production mode
# define('PWORKS_CONFIG_SYNTAX_CHECK', false);
//For develop mode
define('PWORKS_CONFIG_SYNTAX_CHECK', false);


//For develop mode
define('APP_CACHE_SETTING', 'array');

//For production mode
# define('APP_CACHE_SETTING', 'apc, array');
