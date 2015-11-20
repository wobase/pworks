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

require_once('pworks/mvc/IAction.iface.php');

class BlankAction implements IAction {
	protected $_errors;
	protected $_warnings;
	protected $_data;
	protected $_voBind;
	protected $_config;

	//------------------------------------------
	//[Jul. 23, 2009][Milo]
	//Add property "result" and related functions
	protected $____result____;

	public function setResult($result){
		$this->____result____ = $result;
	}

	public function getResult(){
		return $this->____result____;
	}

	public function setData($key, $value){
		$this->_data[$key] = $value;
	}

	public function getData(){
		return $this->_data; 
	}

	public function getErrors() {

	}

	public function getWarnings() {

	}

	public function getInfos(){}

	public function addError($fieldId, $content, $details=array()){	}

	public function addWarning($fieldId, $content, $details=array()){ }
	public function addInfo($fieldId, $content, $details=array()){ }

	public function setConfig(ActionConfig $config){
		$this->_config = $config;
	}

	public function getConfig(){
		return $this->_config;
	}

	public function execute(){
		return 'succ';
	}    
}

