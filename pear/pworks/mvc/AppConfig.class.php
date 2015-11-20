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
 * Root Node of the pwork application configuration
 * 
 * the attributes of this root node are:
 * - id
 * - defaultAction
 * 
 * the children nodes are:
 * - actions
 * - filters
 * - resultTypes
 * - globals
 */
class AppConfig {

	public $id;

	public $defaultAction;

        /**
         *
         * @var array<ActionConfig>
         */
	public $actions;

        /**
         *
         * @var array<FilterConfig>
         */
	public $filters;

        /**
         *
         * @var array<ResultTypeConfig>
         */
	public $resultTypes;

        /**
         *
         * @var array< key => value >
         */
	public $globals;
	
	//[2009-05-26] Implement Feature Request 2777528
	//----------------------------------------------
	public $globalFilters;


	public function toXml(){
		$outXml = "<application id=\"{$this->id}\" default-action=\"{$this->defaultAction}\">";

		$outXml .= '<globals>';
		foreach($this->globals as $key => $value){
			$outXml .= "<global name=\"$key\" value=\"$value\"/>";
		}
		$outXml .= '</globals>';


		$outXml .= '<resultTypes>';
		foreach($this->resultTypes as $rsType){
			$outXml .= $rsType->toXml();	
		}
		$outXml .= '</resultTypes>';


		$outXml .= '<filters>';
		foreach($this->filters as $filter){
			$outXml .= $filter->toXml();	
		}
		$outXml .= '</filters>';


		$outXml .= '<actions>';
		foreach($this->actions as $action){
			$outXml .= $action->toXml();	
		}
		$outXml .= '</actions>';

		$outXml .= "</application>";
		return $outXml;
	}
}

