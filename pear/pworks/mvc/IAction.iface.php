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

/**
 * action 接口定义, 约定了action的基本形为特征
 */
interface IAction {
    
        /**
         * 主业务逻辑封装于此方法中
         */
	public function execute();
        
        /**
         * action处理结束后, 所有的结果, 会写入至$data成员中, 并通过下面的方法进行访问
         */
	public function setData($key,$value);
	public function getData();
        
        /**
         * 一组分级的系统信息读写方法, 用于传递应用的系统信息
         */
	public function addError($fieldId, $content, $details=array());
	public function addWarning($fieldId, $content, $details=array());
	public function addInfo($fieldId, $content, $details=array());
	
	public function getErrors();
	public function getWarnings();
	public function getInfos();
	
	
	/**
         * 方便过滤器获取action配置信息
         * 或者在进行action的嵌套时, 调整action的配置信息
	 * @return ActionConfig 
	 */
        public function setConfig(ActionConfig $config);
	public function getConfig();

	/**
	 * [Jul. 23, 2009][Milo]
	 * 为过滤器以及视图模块提供对result对象的操作接口
	 */
	public function setResult($result);
	public function getResult();
}