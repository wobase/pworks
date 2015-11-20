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

class Logs{
	/**
	 * 
	 *
	 * @var ILog[]
	 */
	public $_logs;
	
	public function start($headLines, $hasReport=false){
		foreach($this->_logs as $log){
			$log->hasReport = $hasReport;
			foreach($headLines as $line){
				$log->writeLine($line);
			}
			$log->start();
		}
	}

	public function addEntity(LogEntity $entity){
		foreach($this->_logs as $log){
			$log->addEntity($entity);
		}	
	}

	public function end(){
		foreach($this->_logs as $log){
			$log->end();
		}
	}

	public function report(){
		foreach($this->_logs as $log){
			$rpts = $log->getReport();
			$log->writeLine('---STATISTIC---');
			foreach($rpts as $key => $value){
				$log->writeLine($key . ': ' . $value);
			}
		}
	}
	
	public function close(){
	foreach($this->_logs as $log){
			$log->close();
		}
	}
}

