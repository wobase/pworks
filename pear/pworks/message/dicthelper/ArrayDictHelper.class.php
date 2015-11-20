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

require_once('pworks/message/IDictHelper.iface.php');
require_once('pworks/message/LangPackConstant.class.php');

/**
 * use php source as the language dictionaries
 * the php source must just include an array named $lang
 * the structure of the array will be:
 * array(
 *   'system_name' => array(
 *      'language_code' => array(
 *         'module_name' => array(
 *            'label' => 'content',
 *            'error' => 'error message',
 *            'title' => '<h1>Page Title</h1>'
 *      )
 *   )
 * )
 */
class ArrayDictHelper implements IDictHelper{

	const PARAM_BASE_PATH = 'base';
	const PARAM_NAME_PATTERN = 'name-pattern';

	const DEFAULT_DICT_FILE = 'lang_dict.php';

	public $_dictCache;

	private $_loadedDict;

	private $_dictFileParts= array();

	const NAME_PATTERN = '/[^\{\}]*(\{[^\{\}]{3,4}\})[^\{\}]*/';

	const PN_LANG = '{lang}';
	const PN_SYS = '{sys}';
	const PN_MOD = '{mod}';


	private $_name_pattern;
	private $_base_path;

	/**
	 * @param url, string, unused in this adapter
	 * @param options, array(
	 *  	'base-path' => $basepath,
	 * 		'name-pattern' => $name_pattern
	 * )
	 * following keywords could be used in name-pattern:
	 * - {lang} mapping to parameter $language in all methods
	 * - {sys} mapping to parameter $system in all methods
	 * - {mod} mapping to parameter $module in all methods
	 */
	public function init($url, $options){
		$this->_base_path = isset($options[self::PARAM_BASE_PATH])?$options[self::PARAM_BASE_PATH]:'/';
		$this->_name_pattern =isset($options[self::PARAM_NAME_PATTERN])?$options[self::PARAM_NAME_PATTERN]:(self::DEFAULT_DICT_FILE);

		preg_match_all(self::NAME_PATTERN, $this->_name_pattern, $matchs);
		if(count($matchs[0]) > 0){
			$this->_dictFileParts = array_combine($matchs[1], $matchs[0]);
		}
		$this->_dictCache = array();
		$this->_loadedDict = array();
	}

	private function _loadLabel($label, $module, $system, $language){
		if(count($this->_dictFileParts)==0){
			$dict_file_path = $this->_base_path.'/'.$this->_name_pattern;
			$this->addDict($dict_file_path);
		}
		else{
			$dict_path_parts = $this->_dictFileParts;
			if(!array_key_exists($system, $this->_dictCache)
				|| !is_array($this->_dictCache[$system]) ||  !array_key_exists($language, $this->_dictCache[$system])
				|| !is_array($this->_dictCache[$system][$language]) ||  !array_key_exists($module, $this->_dictCache[$system][$language])
			){
				if(array_key_exists(self::PN_LANG, $dict_path_parts)) {
					$dict_path_parts[self::PN_LANG] = str_replace(self::PN_LANG, $language,$dict_path_parts[self::PN_LANG]);
				}
				if(array_key_exists(self::PN_SYS, $dict_path_parts)) {
					$dict_path_parts[self::PN_SYS] = str_replace(self::PN_SYS, $system,$dict_path_parts[self::PN_SYS]);
				}
				if(array_key_exists(self::PN_MOD, $dict_path_parts)) {
					$dict_path_parts[self::PN_MOD] = str_replace(self::PN_MOD, $module,$dict_path_parts[self::PN_MOD]);
				}
				$dict_file_path = $this->_base_path. '/' . implode('', $dict_path_parts);  
				$this->addDict($dict_file_path);
			}
		}

		if(array_key_exists($system, $this->_dictCache)
			&& is_array($this->_dictCache[$system]) && array_key_exists($language, $this->_dictCache[$system])
			&& is_array($this->_dictCache[$system][$language]) &&  array_key_exists($module, $this->_dictCache[$system][$language])
		){
			return $this->_dictCache[$system][$language][$module][$label];
		}else{
			return null;
		}
	}

	private function addDict($path){
		if(!in_array($path, $this->_loadedDict)){
			if(!is_file($path)){
				throw new Exception($path .' is not a real file, please check if it is existing!', LangPackConstant::ERROR_OS_FILE_NOT_FOUND);
			}
			$lang = NULL;
			include($path);
			if(!isset($lang) || !is_array($lang)){
				throw new Exception('Please define the language array with name $lang', LangPackConstant::ERROR_PHP_NON_EXISTENT_VAR );
			}
			$this->_dictCache = array_merge_recursive($this->_dictCache, $lang);
			$this->_loadedDict[] = $path;
			unset($lang);
		}
	}

	public function getLabels($module, $system, $language){
		//TODO implement ...	
	}

	public function getLabel($label, $module, $system, $language){
		return $this->_loadLabel($label,$module, $system, $language);
	}

	public function getArray($label, $module, $system, $language){
		$options = $this->_loadLabel($label,$module, $system, $language);
		if(is_array($options)){
			return $options;
		}else{
			return null;
		}
	}


	public function setLabel($label, $content, $module, $system, $language){
		$this->_dictCache[$system][$language][$module][$label] = $content;
	}

	public function search($label_pattern, $content_pattern, $module, $system, $language){
		throw new Exception('Array Dictionary Helper does not support "search" method', LangPackConstant::ERROR_PHP_UNSUPPORT_METHOD);
	}

	public function remove($id){
		throw new Exception('Array Dictionary Helper does not support "remove" method', LangPackConstant::ERROR_PHP_UNSUPPORT_METHOD);
	}

	public function find($id){
		throw new Exception('Array Dictionary Helper does not support "find" method', LangPackConstant::ERROR_PHP_UNSUPPORT_METHOD);
	}

	public function cloneLangPack($from_lang, $to_lang, $system, $module, $write_mode){
		throw new Exception('Array Dictionary Helper does not support "cloneLangPack" method', LangPackConstant::ERROR_PHP_UNSUPPORT_METHOD);
	}

	public function exportXml($system, $language){
		throw new Exception('Array Dictionary Helper does not support "exportXml" method', LangPackConstant::ERROR_PHP_UNSUPPORT_METHOD);
	}

	public function importXml($xmlContent, $write_mode){
		throw new Exception('Array Dictionary Helper does not support "importXml" method', LangPackConstant::ERROR_PHP_UNSUPPORT_METHOD);
	}
}

//$test_string = 'dict/{lang}/{sys}_{mod}.dict.php';
//$test_string = 'dict/xxlang/sys_mod.dict.lang.php';
//$reg_pattern = '/[^\{\}]*(\{[^\{\}]{3,4}\})[^\{\}]*/';
//preg_match_all($reg_pattern, $test_string, $matchs);
//print_r($matchs);
//
//$ff = array_combine($matchs[1], $matchs[0]);
//
//print_r($ff);
//
//echo implode('', $ff);
