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

require_once('pworks/message/Message.class.php'); 

class Messager{
    private $defaultErrorTemplate = '<br/><font color="red">%0%</font>';
    private $defaultWarningTemplate = '<br/><font color="#ff8040">%0%</font>';
    private $defaultInfoTemplate = '<br/><font color="#0066cc">%0%</font>';
    
    private $errorTempalet;
    private $warningTemplate;
    private $infoTemplate;
    
    private $_errors;
    private $_warnings;
    private $_infos;
    
    public function __construct(){
        $this->errorTempalet = $this->defaultErrorTemplate;
        $this->warningTemplate = $this->defaultWarningTemplate;
        $this->infoTemplate = $this->defaultInfoTemplate;
        $this->_errors = array();
        $this->_warnings = array();
        $this->_infos = array();
        
    }
    
    public function setErrorTemplate($content){
        $this->errorTempalet = $content;
    }
    
    public function setWarningTemplate($content){
        $this->warningTemplate = $content;
    }
    
    public function setInfoTemplate($content){
        $this->infoTemplate = $content;
    }
    
    public function addErrors($errors){
//DebugUtil::dump($errors, __FILE__, __LINE__);
       $this->_errors = $errors; 
//DebugUtil::dump($this->_errors, __FILE__, __LINE__);
    }
    
    public function addWarnings($warnings){
        $this->_warnings = $warnings; 
    }
    
    public function addInfos($infos){
        $this->_infos = $infos; 
    }
        
    public function showError($id){
//DebugUtil::dump($this->_errors, __FILE__, __LINE__);        
        if(is_array($this->_errors) && array_key_exists($id, $this->_errors)){
            $fieldErrors = $this->_errors[$id];
//DebugUtil::dump($fieldErrors, __FILE__, __LINE__);             
            foreach($fieldErrors as $error){
                echo(str_replace('%0%', $error->content, $this->errorTempalet));
            }
        }
    }
    
    public function showWarning($id){
        if(is_array($this->_warnings) && array_key_exists($id, $this->_warnings)){
            foreach($this->_warnings[$id] as $warning){
                echo(str_replace('%0%', $warning->content, $this->warningTemplate));
            }
        }
    }
    
    public function showInfo($id){
        if(is_array($this->_infos) && array_key_exists($id, $this->_infos)){
            foreach($this->_infos[$id] as $info){
                echo(str_replace('%0%', $info->content, $this->infoTemplate));
            }
        }
    }
}

