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
require_once('pworks/message/Messager.class.php');

//[2011-12-8][Milo Liu] Add a new public function for calling another action
require_once('pworks/mvc/FrontController.class.php');

abstract class BaseAction implements IAction {
	protected $_errors;
	protected $_warnings;
	protected $_infos;
	protected $_data;
	//protected $_voBind;
	protected $_config;

	// [2017-04-01] Milo <cutadra@gmail.com>
	// added fields for http request parameters
	public $_http_get;
	public $_http_post;


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

	public function fillData($dataType){
		foreach($this as $key => $value) {
			$this->$key = FrontController::getDataHelper()->get($key, $dataType);
		}
	}

	public function setData($key, $value){
		$this->_data[$key] = $value;
	}

	public function getData(){
		return $this->_data;
	}

	public function removeData($key)
	{
		unset($this->_data[$key]);
	}

	public function emptyData()
	{
		$data = $this->getData();
		foreach ($data as $key=>$value)
		{
			$this->removeData($key);
		}
	}

	public function getErrors() {
		return $this->_errors;
	}

	public function getWarnings() {
		return $this->_warnings;
	}

	public function getInfos(){
		return $this->_infos;
	}

	public function addError($fieldId, $content, $details=array()){
		$message = new Message();
		$message->type = Message::TYPE_ERROR;
		$message->ownerId = $fieldId;
		$message->content = $content;
		$message->details = $details;
		$this->_errors[$fieldId][] = $message;
	}

	public function addWarning($fieldId, $content, $details=array()){
		$message = new Message();
		$message->type = Message::TYPE_WARNING;
		$message->ownerId = $fieldId;
		$message->content = $content;
		$message->details = $details;
		$this->_warnings[$fieldId][] = $message;
	}

	public function addInfo($fieldId, $content, $details=array()){
		$message = new Message();
		$message->type = Message::TYPE_NORMAL;
		$message->ownerId = $fieldId;
		$message->content = $content;
		$message->details = $details;
		$this->_infos[$fieldId][] = $message;
	}

	public function setConfig(ActionConfig $config){
		$this->_config = $config;
	}

	public function getConfig(){
		return $this->_config;
	}


	/**
	 * [2011-12-8][Milo Liu] Add a new public function for calling another action
	 */
	public function callAction($actionId, $parameters,  $includeFilter=TRUE){

		foreach($parameters as $name => $value){
			FrontController::$dataHelper->set($name, $value);
		}

		try{
			return FrontController::start($actionId, $includeFilter, TRUE);
		}catch(SystemException $e){
			$e->message = $e->getMessage() . '[in'.__FILE__.', line:'.__LINE__.' ]';
			throw $e;
		}
	}
}
