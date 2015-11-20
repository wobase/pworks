<?php
/*
 * Copyright 2014 - 2015 Milo Liu<cutadra@gmail.com>. 
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without 
 * modification, are permitted provided that the following conditions are met:
 *    1. Redistributions of source code must retain the above copyright notice, 
 *       this list of conditions and the following disclaimer.
 * 
 *    2. Redistributions in binary form must reproduce the above copyright 
 *       notice, this list of conditions and the following disclaimer in the 
 *       documentation and/or other materials provided with the distribution.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDER AND CONTRIBUTORS "AS IS" 
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE 
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE 
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE 
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR 
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF 
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS 
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN 
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) 
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE 
 * POSSIBILITY OF SUCH DAMAGE.
 * 
 * The views and conclusions contained in the software and documentation are 
 * those of the authors and should not be interpreted as representing official 
 * policies, either expressed or implied, of the copyright holder.
 */

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

define('APP_NAME', 'home');

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
