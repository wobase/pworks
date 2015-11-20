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

require_once('pworks/common/log/ILog.iface.php');

abstract class AbsStreamLog implements ILog{
	
	protected $defaultTemplate='[%WHEN%][%WHO%] %REQUEST% - %RESULT% - %NOTE%';
	
	protected $startTime;
	protected $endTime;
	protected $succCount;
	protected $failCount;
	
	public $template;
	
	/**
	 * if counting time, result
	 * @var boolean
	 */
	protected $hasReport=false;

	public function hasReport($flag=false){
		$this->hasReport = $flag;
	}

	protected function getTemplate(){
		return (strlen(trim($this->template))>0)?($this->template):($this->defaultTemplate);
	}
    
    abstract public function open($url);
    abstract public function close();
    
	public function start(){
		if($this->hasReport){
			$this->startTime = time();
			$this->succCount = 0;
			$this->failCount = 0;
		}
	}
	
	public function end(){
		if($this->hasReport){
			$this->endTime = time();
		}
	}

	public function addEntity(LogEntity $entity){
		$this->count($entity);
		$line = $this->getTemplate();
		$line = str_replace('%WHEN%', $entity->when, $line);
		$line = str_replace('%WHO%', $entity->who, $line);
		$line = str_replace('%REQUEST%', $entity->request, $line);

		$result = 'OPERATION_FAILED';

		if(TRUE === $entity->result){
			$result = 'OPERATION_SUCCESSFULLY';
		}

		$line = str_replace('%RESULT%', $result, $line);
		$line = str_replace('%NOTE%', $entity->note, $line);
		$this->writeLine($line);
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
	abstract protected function writeLine($line);
}
