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
 * 
 * [2009-05-26] Implement Feature Request 2777528 
 * <a href="http://sourceforge.net/tracker/?func=detail&amp;aid=2777528&amp;group_id=214107&amp;atid=1028362">Global Filter</a>
 * <filter id="glbFilter" class="" type="global">
 * 	<exclude id="excludeActionId1"/>
 *  <exclude id="excludeActionId2"/>
 * </filter>
 * @author Milo Liu
 */
class FilterConfig {
	
	//[2009-05-26] Implement Feature Request 2777528
	//----------------------------------------------
	const TYPE_GLOBAL = 'global';
	const TYPE_DEFAULT = 'default';
	
	public $id;

	public $clzName;

	public $results;
	
	//[2009-05-26] Implement Feature Request 2777528
	//----------------------------------------------
	/**
	 * Type of a filter, includes:
	 * <ol>
	 * 	<li> GLOBAL - global filters, would be called for all actions</li>
	 *  <li> DEFAULT - default filters, would be called for the action that include the filter</li>
	 * </ol>
	 * @var string
	 */
	public $type = FilterConfig::TYPE_DEFAULT;
	
	/**
	 * A list of action id, which related actions would ignore this global filter
	 * @var array
	 */
	public $excludes = array();	

	//[2012-01-14] Add parameter element into Filter
	public $parameters = array();
	
	public function toXml(){
		$outXml = "<filter id=\"".$this->id."\" class=\"".$this->clzName."\" type=\"".$this->type."\">";

		foreach($this->results as $result){
			$outXml .= $result->toXml();
		}

		foreach($this->excludes as $actionId){
			$outXml .= "<exclude id=\"$actionId\"/>";
		}
		
		foreach ($this->parameters as $key => $value){
			$outXml .= "<parameter key=\"$key\" value=\"$value\"/>";
		}
		
		$outXml .= "</filter>";
		return $outXml;
	}
}