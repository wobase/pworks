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


if(defined('PWORKS_CONFIG_FILE_PATH')) {
    require_once(PWORKS_CONFIG_FILE_PATH);
}
else{
    require_once('pworks/pworks.inc.default.php');
}

require_once('pworks/mvc/FrontController.class.php');
require_once('pworks/mvc/DefaultSystemDataHelper.class.php');
require_once('pworks/mvc/CachedConfigHelper.class.php');
require_once('pworks/common/cache/impl/SimpleCacheHelper.class.php');
require_once('pworks/common/cache/impl/ArrayCache.class.php');
require_once('pworks/common/cache/impl/ApcCache.class.php');
require_once('pworks/util/DebugUtil.class.php');

// 调试开关
$debug = (isset($_REQUEST['__debug']) && 'true' == strtolower($_REQUEST['__debug']))? true : false;
DebugUtil::$enable = $debug;

// 缓存清除开关
$refCache = (isset($_REQUEST['__clean_cache']) && 'true' == strtolower($_REQUEST['__clean_cache']))? true : false;
if($refCache){
    apc_clear_cache('user');
}

$_configCacheGroupName = APP_NAME . '-configs';
$_appCacheGroupName = APP_NAME . '-application-objects';

//缓存别名和级别映射
$_cacheClassMapping = array();
$_cacheClassMapping['apc'] = array('ApcCache', 1);
$_cacheClassMapping['array'] = array('ArrayCache', 2);
// JsonCache will be implemented in next release
//$_cacheClassMapping['json'] = array('JsonCache', 3);

$_fileCachePath = APP_ROOT . '/cache';


try{
    // 配置信息缓存设置
    $configCacheHelper = new SimpleCacheHelper();
    $configCacheHelper->setGroup($_configCacheGroupName);
    $configCaches = explode(',', CONFIG_CACHE_SETTING );
    foreach($configCaches as $cacheName){
        $cacheClz = $_cacheClassMapping[$cacheName][0];
        $cacheLv =  $_cacheClassMapping[$cacheName][1];
        $configCacheHelper->setCache(new $cacheClz, $cacheLv);
    }


    // 装载应用级的配置文件 通常为 APP_ROOT/pworks.xml
    $confHelper = new CachedConfigHelper();
    $confHelper->setCacheHelper($configCacheHelper);

    $confHelper->init(PWORKS_XML_CONFIG, PWORKS_CONFIG_SYNTAX_CHECK);
    FrontController::$confHelper = $confHelper;

    // 设置HTTP相关协议的数据处理助手类
    FrontController::$dataHelper = new DefaultSystemDataHelper();

    // 设置应用对象的缓存
    $appCacheHelper = new SimpleCacheHelper();
    $appCacheHelper->setGroup($_appCacheGroupName);
    $appCaches = explode(',', APP_CACHE_SETTING );
    foreach($configCaches as $cacheName){
        $cacheClz = $_cacheClassMapping[$cacheName][0];
        $cacheLv =  $_cacheClassMapping[$cacheName][1];
        $appCacheHelper->setCache(new $cacheClz, $cacheLv);
    }
    FrontController::$cacheHelper = $appCacheHelper;

    //启动
    FrontController::start();


}catch (SystemException $e){
    if($debug){
        echo "<h3>SystemException</h3>";
        echo "<B>ID:</B>". $e->id;
        echo "<br>";
        echo "<B>TYPE:</B>". $e->type;
        echo "<br>";
        echo '<B>MESSAGE:</B><font size="4" color="red">';
        echo $e->message;
        echo "</font>";
        echo "<br>";
    }
}
