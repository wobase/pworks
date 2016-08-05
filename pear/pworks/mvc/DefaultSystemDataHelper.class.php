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

require_once('pworks/mvc/ISystemDataHelper.iface.php');
require_once('pworks/mvc/SysDataType.class.php');

/**
 * 一个支持HTTP协议的默认数据处理助手, 通过数据助手, 可以使得action的实现与具体的网络协议无关
 */
class DefaultSystemDataHelper implements ISystemDataHelper {

	//[2011-12-08][Milo Liu] Add default type for get and set functions
	public $defaultType = SysDataType::REQUEST;

	//[2011-12-08][Milo Liu] Add implement for the new interface added in ISystemDataHelper
	public function set($name, $value, $type=NULL){

		if(NULL==$type) $type = $this->defaultType;

		switch($type){
			case SysDataType::GET :
				global $_GET;
				$_GET[$name]=$value;
				break;

			case SysDataType::POST :
				global $_POST;
				$_POST[$name]=$value;
				break;

			case SysDataType::REQUEST :
				global $_REQUEST;
				$_REQUEST[$name]=$value;
				break;

			case SysDataType::SERVER :
				global $_SERVER;
				$_SERVER[$name]=$value;
				break;

			case SysDataType::ENV :
				putenv("$name=$value");
				break;

			case SysDataType::COOKIE :
				global $_COOKIE;
				$_COOKIE[$name]=$value;
				break;
		}
	}


	//[2011-12-08][Milo Liu] add default value for type parameter
	public function get($name,$type=NULL) {

		//[2011-12-08][Milo Liu] add default value for type parameter
		if(NULL==$type) $type = $this->defaultType;


		$retVal = null;
		switch($type){
			case SysDataType::GET :
				global $_GET;
				if(array_key_exists($name, $_GET)){
					$retVal = $_GET[$name];
				}
				break;

			case SysDataType::POST :
				global $_POST;
				if(array_key_exists($name, $_POST)){
					$retVal = $_POST[$name];
				}
				break;

			case SysDataType::REQUEST :
				global $_REQUEST;
				if(array_key_exists($name, $_REQUEST)){
					$retVal = $_REQUEST[$name];
				}
				break;

			case SysDataType::SERVER :
				global $_SERVER;
				if(array_key_exists($name, $_SERVER)){
					$retVal = $_SERVER[$name];
				}
				break;

			case SysDataType::ENV :
				if(getenv($name)){
					$retVal = getenv($name);
				}
				break;

			case SysDataType::COOKIE :
				global $_COOKIE;
				if(array_key_exists($name, $_COOKIE)){
					$retVal = $_COOKIE[$name];
				}
				break;

		}
		return $retVal;
	}
}
