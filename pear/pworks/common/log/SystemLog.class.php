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

require_once('pworks/common/log/LogEntity.class.php');
require_once('pworks/common/log/ILog.iface.php');

class SystemLog implements ILog {

	private $__defaultTemplate ='[%WHEN%][%WHO%]- %REQUEST% - %RESULT% - %NOTE%';

	private $logTemplate;

	private $_messageType = 0;

	private $_logFile;

	protected $startTime;
	protected $endTime;
	protected $succCount;
	protected $failCount;

	protected $hasReport=false;

	public function hasReport($flag=false){
		$this->hasReport = $flag;
	}

	public function setLogFile($fileName){
		if(strlen(trim($fileName)) && is_file($fileName)){
			$this->_logFile = $fileName;
			$this->_messageType = 3;
		}
		else{
			$this->_logFile = '';
			$this->_messageType = 0;
		}
	}

	public function setTemplate($template){
		$this->logTemplate = $template;
	}

	public function getTemplate(){
		return (strlen(trim($this->logTemplate))>0)?($this->logTemplate):($this->__defaultTemplate);
	}

	/**
	 * Enter description here...
	 *
	 * @param string[] $headLines
	 */
	public function start() {
		if($this->hasReport){
			$this->startTime = time();
			$this->succCount = 0;
			$this->failCount = 0;
		}
	}


	public function addEntity(LogEntity $entity) {
		$this->count($entity);
		$logString = $this->getTemplate();
		$logString = str_replace('%WHEN%',$entity->when, $logString);
		$logString = str_replace('%WHO%',$entity->who, $logString);
		$logString = str_replace('%REQUEST%',$entity->request, $logString);
		
		$result = 'OPERATION_FAILED';
		
		if(TRUE === $entity->result){
			$result = 'OPERATION_SUCCESSFULLY';
		}
		
		$logString = str_replace('%RESULT%',$result, $logString);
		$logString = str_replace('%NOTE%',$entity->note, $logString);
		$logString .= "\n";
	  
		if(strlen(trim($logString))>0){
			if(3 == $this->_messageType){
				error_log($logString, 3, $this->_logFile);
			}
			else{
				error_log($logString, 0);
			}
		}
	}


	protected function count(LogEntity $entity){
		if($this->hasReport){
			if($entity->result){
				$this->succCount++;
			}else{
				$this->failCount++;
			}
		}
	}

	public function end() {
		if($this->hasReport){
			$this->endTime = time();
		}
	}


	public function getReport(){
		$report = array();
		$report['start'] = $this->startTime;
		$report['end'] = $this->endTime;
		$report['succ'] = $this->succCount;
		$report['fail'] = $this->failCount;
		return $report;
	}


	/*
	 * Removed, because it had been implemented in Logs
	 */
	//	public function report(){
	//		$reportInfo = $this->getReport();
	//		$this->writeLine("");
	//		$this->writeLine("Summary Report");
	//		$this->writeLine("====================");
	//		$this->writeLine("     Start: " . $this->startTime);
	//		$this->writeLine("       End: " . $this->endTime);
	//		$this->writeLine(" Total Log: " . $this->succCount + $this->failCount);
	//		$this->writeLine("Successful: " . $this->succCount);
	//		$this->writeLine("      Fail: " . $this->failCount );
	//	}
	//
}
