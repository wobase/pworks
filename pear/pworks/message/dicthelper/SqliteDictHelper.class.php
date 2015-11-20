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

require_once ('pworks/message/IDictHelper.iface.php');
require_once ('pworks/message/LangPackConstant.class.php');

class SqliteDictHelper implements IDictHelper {
	
	const DSN = 'sqlite:';
	
	const DB_INIT_SCRIPTS = <<<SQL
CREATE TABLE IF NOT EXISTS lang_pack (
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	system_name VARCHAR(50) DEFAULT 'default',
	module_name VARCHAR(50) DEFAULT 'default',
	lang_code VARCHAR(15) DEFAULT 'EN',
	label TEXT NOT NULL,
	content TEXT,
	lastupdate DATETIME DEFAULT CURRENT_TIMESTAMP,
	UNIQUE(system_name, lang_code, module_name, label) 
)
SQL;
	
	private $depend_extension = array ('pdo_sqlite', 'sqlite3', 'PDO' );
	
	private $connection;
	
	public function init($url, $options) {
		$this->connection = new PDO ( self::DSN . $url );
		$this->connection->exec ( self::DB_INIT_SCRIPTS );
	
	}
	
	public function checkExt() {
		foreach ( $this->depend_extension as $ext ) {
			if (! extension_loaded ( $ext )) {
				//TODO add log information here...
				return false;
			}
		}
		return true;
	}
	
	public function getLabels($module = null, $system = null, $language = null) {
		return $this->_query ( null, null, null, $module, $system, $language );
	}
	
	public function getLabel($label, $module, $system, $language) {
		return $this->_query ( null, $label, null, $module, $system, $language );
	}
	
	public function setLabel($label, $content, $module, $system, $language) {
		$labels = $this->_query ( null, $label, null, $module, $system, $language );
		
		if (count ( $labels ) > 0) {
			foreach ( $labels as $row ) {
				
				$this->_update ( $row ['id'], $content );
			}
		
		} else {
			
			$this->_insert ( $label, $content, $module, $system, $language );
		}
	}
	
	public function search($label_pattern, $content_pattern, $module, $system, $language) {
		$label_pattern = is_null ( $label_pattern ) ? null : $label_pattern . '%';
		$content_pattern = is_null ( $content_pattern ) ? null : $content_pattern . '%';
		return $this->_query ( null, $label_pattern, $content_pattern, $module, $system, $language );
	}
	
	public function remove($id) {
		$bind_array = array ();
		$bind_array [':id'] = $id;
		$sql_statement = 'DELETE FROM lang_pack WHERE id = :id';
		$statement = $this->connection->prepare ( $sql_statement );
		$statement->execute ( $bind_array );
		return $statement->rowCount ();
	}
	
	public function find($id) {
		return $this->_query ( $id, null, null, null, null, null );
	}
	
	public function cloneLangPack($from_lang, $to_lang, $system = null, $module = null, $write_mode = LangPackConstant::MODE_WRITE_OVERRIDE) {
		if (is_null ( $from_lang )) {
			throw new Exception ( 'Please specify source language!' );
		
		//TODO add log information here...
		}
		
		if (is_null ( $to_lang )) {
			throw new Exception ( 'Please specify destination language!' );
		
		//TODO add log information here...
		}
		
		if ($to_lang == $from_lang) {
			throw new Exception ( 'The source language is same to destination language, operation is cancelled!' );
		
		//TODO add log information here...
		}
		
		if (LangPackConstant::MODE_WRITE_OVERRIDE == $write_mode) {
			$bind_array = array ();
			$bind_array [':lang'] = $to_lang;
			$sql_statement = 'DELETE FROM lang_pack WHERE lang_code = :lang';
			$statement = $this->connection->prepare ( $sql_statement );
			$statement->execute ( $bind_array );
		
		//TODO add log information here...
		}
		
		$bind_array = array ();
		$sql_statement = 'INSERT INTO lang_pack(label, content, module_name, system_name, lang_code)';
		$sql_statement .= ' SELECT label, content, module_name, system_name, :to_lang FROM lang_pack ';
		$bind_array [':to_lang'] = $to_lang;
		$sql_statement .= ' WHERE lang_code = :from_lang ';
		$bind_array [':from_lang'] = $from_lang;
		
		if (! is_null ( $system )) {
			$sql_statement .= ' AND system_name = :system ';
			$bind_array [':system'] = $system;
		}
		
		if (! is_null ( $module )) {
			$sql_statement .= ' AND module_name = :module ';
			$bind_array [':module'] = $module;
		}
		$statement = $this->connection->prepare ( $sql_statement );
		$statement->execute ( $bind_array );
		return $statement->rowCount ();
	}
	
	public function exportXml($system, $language) {
		$labels = $this->_query ( null, null, null, null, $system, $language, 'system_name, lang_code, module_name' );
		$xml = '';
		$system = '';
		$systemCloseTag = '';
		$lang = '';
		$langCloseTag = '';
		$module = '';
		$moduleCloseTag = '';
		foreach ( $labels as $row ) {
			if ($system != $row ['system_name']) {
				//close previous system node
				$xml .= $systemCloseTag;
				
				//start new system node
				$system = $row ['system_name'];
				$xml .= '<!-- SYSTEM ' . $system . ' START --><system name="' . $system . '">';
				$systemCloseTag = '</system>' . '<!-- SYSTEM ' . $system . ' END -->';
			}
			
			if ($lang != $row ['lang_name']) {
				//close previous lang node
				$xml .= $langCloseTag;
				
				//start new lang node
				$lang = $row ['lang_code'];
				$xml .= '<!-- LANGUAGE ' . $lang . ' START --><lang code="' . $lang . '">';
				$langCloseTag = '</lang>' . '<!-- LANGUAGE ' . $lang . ' END -->';
			}
			
			if ($module != $row ['module_name']) {
				//close previous module node
				$xml .= $moduleCloseTag;
				
				//start new module node
				$module = $row ['module_name'];
				$xml .= '<!-- MODULE ' . $module . ' START --><module name="' . $module . '">';
				$moduleCloseTag = '</module>' . '<!-- MODULE ' . $module . ' END -->';
			}
			
			$xml .= '<label id="' . $row ['label'] . '">' . '<![CDATA[' . $row ['content'] . ']]>';
		}
		
		$xml .= $moduleCloseTag;
		$xml .= $langCloseTag;
		$xml .= $systemCloseTag;
		return $xml;
	}
	
	public function importXml($xmlContent, $write_mode = LangPackConstant::MODE_WRITE_OVERRIDE) {
		$updateCount = 0;
		$insertCount = 0;
		$ignoreCount = 0;
		$dom = DOMDocument::loadXML ( $xmlContent );
		$xpath = new DOMXPath ( $dom );
		//system node
		$system = '';
		$findPathPart = array ();
		$findPath = '//system';
		$systemNodes = $xpath->query ( $findPath );
		foreach ( $systemNodes as $sysNode ) {
			$system = $sysNode->getAttribute ( 'name' );
			
			//language node
			$lang = '';
			$findPathPart ['s'] = 'system[@name="' . $system . '"]';
			$findPath = '//' . implode ( '/', $findPathPart ) . '/lang';
			$langNodes = $xpath->query ( $findPath );
			foreach ( $langNodes as $langNode ) {
				$lang = $langNode->getAttribute ( 'code' );
				
				//module name
				$module = '';
				$findPathPart ['l'] = 'lang[@code="' . $lang . '"]';
				$findPath = '//' . implode ( '/', $findPathPart ) . '/module';
				$modNodes = $xpath->query ( $findPath );
				foreach ( $modNodes as $modNode ) {
					$module = $modNode->getAttribute ( 'name' );
					
					//label and content
					$findPathPart ['m'] = 'module[@name="' . $module . '"]';
					$findPath = '//' . implode ( '/', $findPathPart ) . '/label';
					$labelNodes = $xpath->query ( $findPath );
					foreach ( $labelNodes as $labNode ) {
						$label = $content = '';
						$label = $labNode->getAttribute ( 'id' );
						$content = $labNode->textContent;
						$existLabRows = $this->_query ( null, $label, null, $module, $system, $lang );
						if (count ( $existLabRows ) > 0) {
							if (LangPackConstant::MODE_WRITE_OVERRIDE == $write_mode) {
								$this->_update ( $existLabRows [0] ['id'], $content );
								$updateCount ++;
							} else {
								$ignoreCount ++;
							
		//TODO add duplication warning message here ...
							}
						} else {
							$this->_insert ( $label, $content, $module, $system, $lang );
							$insertCount ++;
						}
					}
				}
			}
		}
		return array ('inserted' => $insertCount, 'updated' => $updateCount, 'ignored' => $ignoreCount );
	}
	
	private function _update($id, $content) {
		$bind_array = array ();
		$bind_array [':id'] = $id;
		$bind_array [':content'] = $content;
		$sql_statement = 'UPDATE lang_pack SET content = :content WHERE id = :id';
		$statement = $this->connection->prepare ( $sql_statement );
		$statement->execute ( $bind_array );
	}
	
	private function _insert($label, $content, $module, $system, $language) {
		$bind_array = array ();
		$bind_array [':label'] = $label;
		$bind_array [':content'] = $content;
		$bind_array [':module'] = $module;
		$bind_array [':system'] = $system;
		$bind_array [':language'] = $language;
		
		//check uniqe (system_name, lang_code, module_name, label)
		if (count ( $this->_query ( null, $label, null, $module, $system, $language ) ) > 0) {
			throw new Exception ( 'The label: ' . "[$system][$language][$module] $label had already been inserted!" );
		}
		
		$sql_statement = 'INSERT INTO lang_pack(label, content, module_name, system_name, lang_code) VALUES(:label, :content, :module, :system, :language)';
		$statement = $this->connection->prepare ( $sql_statement );
		$statement->execute ( $bind_array );
		return $statement->rowCount ();
	}
	
	private function _query($id, $label, $content, $module, $system, $language, $order = null) {
		$bind_array = array ();
		$sql_statement = 'SELECT * FROM lang_pack WHERE 1<2 ';
		
		if (! is_null ( $id )) {
			$sql_statement .= ' AND id = :id ';
			$bind_array [':id'] = array ($id, PDO::PARAM_INT );
		}
		
		if (! is_null ( $label )) {
			$sql_statement .= ' AND label LIKE :label ';
			$bind_array [':label'] = array ($label, PDO::PARAM_STR );
		}
		if (! is_null ( $content )) {
			$sql_statement .= ' AND content LIKE :content ';
			$bind_array [':content'] = array ($content, PDO::PARAM_STR );
		}
		if (! is_null ( $module )) {
			$sql_statement .= ' AND module_name = :module ';
			$bind_array [':module'] = array ($module, PDO::PARAM_STR );
		}
		if (! is_null ( $system )) {
			$sql_statement .= ' AND system_name = :system ';
			$bind_array [':system'] = array ($system, PDO::PARAM_STR );
		}
		if (! is_null ( $language )) {
			$sql_statement .= ' AND lang_code = :language ';
			$bind_array [':language'] = array ($language, PDO::PARAM_STR );
		}
		
		if (! is_null ( $order )) {
			$sql_statement .= ' ORDER BY ' . $order;
		}
		
		$statement = $this->connection->prepare ( $sql_statement, array (PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY ) );
		foreach ( $bind_array as $key => $value ) {
			$statement->bindParam ( $key, $value [0], $value [1] );
		}
		$statement->execute ();
		
		//$statement->debugDumpParams();
		

		return $statement->fetchAll ( PDO::FETCH_ASSOC );
	}
	
	public function getArray($label, $module, $system, $language) {
		throw new Exception ( "getArray has not been implement yet!" );
	}
}
