<?php
/*
 * Copyright 2011 - 2015 Milo Liu<cutadra@gmail.com>. 
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

require_once('pworks/mvc/action/BaseAction.class.php');

/**
* [2011-12-08][Milo Liu] This class provides the simple mapping rules between RESTful URLs and Actions
* 
*/
class RESTfulEntryAction extends BaseAction{

	public $url;
	
	private $__status;
	
	public function getStatus(){
		return $this->__status;
	}
	
	public function execute(){
		$this->debug(__LINE__, $this->url);

		if(0 == strlen(trim($this->url))){
			$this->addError('action', '404');
			$this->__status = '404';
			return 'succ';
		} 
		
		$urlParts = explode('/',trim($this->url));
		
		$paraLen = count($urlParts);
		
		$actionId = $urlParts[0];

		$parameter = array();
		if(2 == $paraLen){
			$parameter['id'] = $urlParts[1];
		}
		
		if( 2 < $paraLen){
			$paraLen--;
			$i = 1;
			while($i< $paraLen){
				$name=$urlParts[$i++];
				$value=$urlParts[$i++];
				$parameter[$name] = $value;
			}
		}

		$restAction = $this->callAction($actionId, $parameter);
	

		$this->_data = $restAction->getData();
		$this->_errors = $restAction->getErrors();
		$this->_warnings = $restAction->getWarnings();
		$this->_infos = $restAction->getInfos();
		$this->__status = $restAction->getResult();

		return 'succ';
	}

	private function debug($line, $var) {
		DebugUtil::dump ( $var, __FILE__, $line );
	}
}
