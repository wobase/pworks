<?php
/*
 * Copyright 2008 - 2015 Milo Liu<cutadra@gmail.com>.
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

require_once('pworks/common/cache/ICacheHelper.iface.php');
require_once('pworks/mvc/IConfigHelper.iface.php');
#require_once('pworks/mvc/tool/ConfChecker.class.php');
require_once('pworks/mvc/AppXMLCfgLoader.class.php');

/**
 * 使用指定的缓存助手类, 将解释过的应用的配置数据, 放入缓存进行读写
 */
class CachedConfigHelper implements IConfigHelper {

    /**
     * @var ICacheHelper
     */
    private $cacheHelper;
    private $appConf;

    public static function getInstance(){
      return new CachedConfigHelper();
    }

    public function setCacheHelper(ICacheHelper $cacheHelper) {
        $this->cacheHelper = $cacheHelper;
        return $this;
    }

    // TODO: $doCheck参数, 用于检查配置文件的一置性和有效性
    // 最初设计这一功能的原因是, 在线性的配置文件解释过程中, 一部分的配置内容需
    // 要其它还没有解释到, 或者不确定是否已经解释完整, 因此需要在所有的配置解释
    // 完成之后, 才能单独执行一些配置检查任务.
    //
    // 由于检查任务与配置的解释任务不在同一个代码段内维护, 造成检查工具与解释工
    // 具之间的逻辑实现容易出现集偏差和遗漏, 长期维护工作无法持续.
    //
    // 后续, 会设计一种内部的消息机制处理配置文件的有效性检查, 将解释过程与检查
    // 过程合并, 保持逻辑的高内聚性.
    // see #8
    public function init($filename, $doCheck=false) {

        if(!$this->cacheHelper->fetch('app_conf_init_flag')){
            //Check xml file first

            //Loading...
            $loader = new AppXMLCfgLoader();
            $loader->load($filename);

            $this->appConf = $loader->getAppConfig();

            //check configuration
            #if($doCheck){
               # ConfChecker::check($this);
            #}

            $this->cacheHelper->store('app_conf_init_flag', true);
            $this->cacheHelper->store('app_conf', $this->appConf);
        }
        else{
            $this->appConf = $this->cacheHelper->fetch('app_conf');
        }
        return $this;
    }

    public function getDefaultAction(){
        return $this->appConf->defaultAction;
    }

    public function getActionConfig($name) {
        if(is_array($this->appConf->actions) && array_key_exists($name, $this->appConf->actions))
        {
            return $this->appConf->actions[$name];
        }
        else{
            return null;
        }
    }

    public function getFilterConfig($name) {
        if(is_array($this->appConf->filters) && array_key_exists($name, $this->appConf->filters))
        {
            return $this->appConf->filters[$name];
        }
        else{
            return null;
        }
    }

    public function getGlobal($name) {
        if(is_array($this->appConf->globals) && array_key_exists($name, $this->appConf->globals))
        {
            return $this->appConf->globals[$name];
        }
        else{
            return null;
        }
    }

    public function getResultType($name) {
        if(is_array($this->appConf->resultTypes) && array_key_exists($name, $this->appConf->resultTypes))
        {
            return $this->appConf->resultTypes[$name];
        }
        else{
            return null;
        }
    }

    public function getApp(){
        return $this->appConf;
    }

   	//[2009-05-26] Implement Feature Request 2777528
	//----------------------------------------------
    public function getGlobalFilters(){
    	return $this->appConf->globalFilters;
    }
}
