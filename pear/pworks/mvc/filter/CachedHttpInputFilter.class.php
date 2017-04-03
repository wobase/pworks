<?php
/*
 * Copyright 2009 - 2015 Milo Liu<cutadra@gmail.com>.
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

require_once('pworks/mvc/IFilter.iface.php');
require_once('pworks/common/cache/impl/ApcuCache.class.php');
require_once('pworks/common/httputil/DefaultHttpInputValidation.class.php');
require_once('pworks/common/httputil/HttpConst.class.php');
require_once('pworks/common/exception/SystemException.class.php');
/**
 * This filter is using to do auto-filling, auto-filtering data from HTTP into Action
 *
 * When a Action want framework to do auto-filling/auto-filtering, Its properties must be documented by following tags
 * <ul>
 * <li>filter , the value will be one of:
 * 	<ul>
 *   <li>string </li>
 * 	 <li>number </li>
 * 	 <li>url </li>
 *   <li>email </li>
 *   <li>html </li>
 *   <li>class, the property is a object which CLASS name specifid by tag var</li>
 *  </ul>
 *  Default value is string
 * </li>
 * <li>var = {Class Name}, this tag is used for 'class'</li>
 * </ul>
 */
class CachedHttpInputFilter implements IFilter{


	/**
	 * [2012-01-14] Allow application to customize the messages
	 * e.g.:
	 * set this property as "conf/message.php", and then create the APP_ROOT/conf/message.php with following content:
	 * <code>
	 * <?php
	 * $messages[CachedHttpInputFilter::ERROR_ISNULL] = '%s is required';
	 * $messages[CachedHttpInputFilter::ERROR_NUMBER] = 'The value for %s is not a valid number';
	 * $messages[CachedHttpInputFilter::ERROR_DATETIME] = 'The value for %s is not a valid date and time';
	 * $messages[CachedHttpInputFilter::ERROR_DATE] = 'The value for %s is not a valid date';
	 * $messages[CachedHttpInputFilter::ERROR_POSITIVE_NUMBER] = 'The value for %s is not a valid positive number';
	 * $messages[CachedHttpInputFilter::ERROR_STRING_NUMBER] = 'The value for %s is not a valid string';
	 * $messages[CachedHttpInputFilter::ERROR_MAX_LENGTH] = 'The value for %s is too long';
	 * $messages[CachedHttpInputFilter::ERROR_EMAIL] = 'The value for %s is not a valid email address';
	 * $messages[CachedHttpInputFilter::ERROR_URL] = 'The value for %s is not a valid URL';
	 * $messages[CachedHttpInputFilter::ERROR_HTML] = 'The value for %s is not a valid html content';
	 * </code>
	 */
	public $messageFilePath;
	public $useMessagePack=false; // this property is used for version compatible
	public $errorMessages = array();

    const CACHE_PREFIX = 'input_filter_';
    const PATTERN = '/[\s\t]*\*[\s]*@([\w]+)[\s]+(.+)/';
    const TAG_FILTER = 'filter';
    const TAG_VAR = 'var';
    const TAG_MAX_LENGTH = 'maxLength';
    const TAG_FLAG = 'flag';
    const TAG_CHARSET = 'charset';
    const TAG_ISNULL = 'isnull';
    const TAG_LABEL = 'label';
    const FILTER_STRING = 'string';
    const FILTER_DATETIME = 'datetime';
    const FILTER_DATE = 'date';
    const FILTER_NUMBER = 'number';
    const FILTER_POSITIVE_NUMBER = 'positive_number';
    const FILTER_STRING_NUMBER = 'string_number';
    const FILTER_EMAIL = 'email';
    const FILTER_URL = 'url';
    const FILTER_HTML = 'html';
    const FILTER_CLASS = 'class';

    const PARENT_TYPE_ACTION = 'action';
    const PARENT_TYPE_CLASS = 'class';
    const LABEL_DEFAULT  = '此项';
    /*
    const ERROR_ISNULL = '请填写%s!';
    const ERROR_NUMBER = '请填写数值!';
    const ERROR_EMAIL = '请填写email!';
    const ERROR_URL = '请填写url!';
    const ERROR_HTML = '请填写HTML!';
     */
    const ERROR_ISNULL = '0001';
    const ERROR_NUMBER = '0008';
    const ERROR_DATETIME = '0049';
    const ERROR_DATE = '0005';
    const ERROR_POSITIVE_NUMBER = '0023';
    const ERROR_STRING_NUMBER = '0024';
    const ERROR_MAX_LENGTH = '0048';
    const ERROR_EMAIL = '0003';
    const ERROR_URL = '0004';
    const ERROR_HTML = '0050';


    private $maxRecursionLevel = 4;

    private $recursionLevel = 0;

    /**
     * @var ApcCache
     */
    private $cache;

    //private $errors;

    /**
     * @var IAction
     */
    private $action;

    //[2012-01-14] Allow application to customize the messages
    private function genMsgContent($error,$label){
    	if($this->useMessagePack){
    		return array($error, sprintf($this->errorMessages[$error], $label));
    	}else{
    		return array($error, $label);
    	}
    }

    public function before(IAction &$action){

    	//[2012-01-14] Allow application to customize the messages
    	if($this->useMessagePack){
    		$error_messages = array();
    		global $error_messages;
    		require_once($this->messageFilePath);
    		$this->errorMessages = $error_messages;
    		unset($error_messages);
    	}


        $this->cache = new ApcuCache();
        $this->recursionLevel = 0;

        $filters = $this->getActionFilterTypes($action);

        $this->action = &$action;
        $this->filterData($action, $filters);
        return null;
    }

    public function after(IAction &$action){
        return null;
    }
    /**
     * Filter and fill data and add error message while filtering failed
     *
     * @param Object $object
     * @param Filter[] $filters
     * @param String $varPrefix
     */
    public function filterData(&$object, $filters, $varPrefix=''){
        foreach($object as $property => $value){
            $filter = $filters[$property];
            $fieldName = $varPrefix . $property;
            if(self::FILTER_CLASS == $filter->filter){
                $clzName = $filter->clzName;
                $obj = new $clzName;
                $this->filterData($obj, $filter->filterTypes , $fieldName.'_');
                $object->$property = $obj;
            }else{
                $getData = DefaultHttpInputValidation::getDate(HttpConst::TYPE_REQUEST, $fieldName);
                $origData = is_string($getData) ? ($getData) : $getData;
                //$origData = trim(DefaultHttpInputValidation::getDate(HttpConst::TYPE_REQUEST, $fieldName));
                if(empty($origData)){
                    if(!$filter->isnull){
                        //$this->action->addError($property, sprintf(self::ERROR_ISNULL, $filter->label));
                        $this->action->addError($property, $this->genMsgContent(self::ERROR_ISNULL, $filter->label));
                    }
                    if(strlen($origData) > 0)
                    {
                        $object->$property = $origData;
                    }
                    continue;
                }
                elseif (is_array($origData))
                {
                    $null_flag = false;
                    if (!$filter->isnull)
                    {
                        if ($filter->filter == self::FILTER_NUMBER)
                        {
                            if(array_sum($origData) == 0)
                            {
                                $this->action->addError($property, $this->genMsgContent(self::ERROR_ISNULL, $filter->label));
                                $null_flag = true;
                            }
                        }
                        else
                        {
                            foreach ($origData as $single_null)
                            {
                                if (empty($single_null))
                                {
                                    $this->action->addError($property, $this->genMsgContent(self::ERROR_ISNULL, $filter->label));
                                    $null_flag = true;
                                    $object->$property = $origData;
                                    break;
                                }
                            }
                        }
                    }
                    else
                    {
                        //when filter datetime include of two part
                        if ($filter->filter == self::FILTER_DATETIME)
                        {
                            $null_arr = array();
                            foreach ($origData as $single_null)
                            {
                                if ($single_null == '')
                                {
                                    $null_arr[] = 1;
                                }
                                else
                                {
                                    $null_arr[] = 0;
                                }
                            }
                            //two part all is null
                            if (array_sum($null_arr) == 2){
                                $null_flag = true;
                            }
                            elseif (array_sum($null_arr) == 1){
                                $this->action->addError($property, $this->genMsgContent(self::ERROR_ISNULL, $filter->label));
                                $null_flag = true;
                                $object->$property = $origData;
                            }
                        }
                    }
                    if ($null_flag) continue;
                }
                $object->$property = $origData;
                if ($filter->maxLength != 0)
                {
                    $fillData = DefaultHttpInputValidation::getDate(HttpConst::TYPE_REQUEST, $fieldName);
                    if (is_array($fillData))
                    {
                        $max_length_flag = false;
                        foreach ($fillData as $single)
                        {
                            if (mb_strlen($single, 'UTF-8') > $filter->maxLength)
                            {
                                $this->action->addError($property, $this->genMsgContent(self::ERROR_MAX_LENGTH, $filter->label));
                                $max_length_flag = true;
                                break;
                            }
                        }
                        if ($max_length_flag) continue;
                    }
                    elseif (mb_strlen($fillData,'UTF-8') > $filter->maxLength)
                    {
                        $this->action->addError($property, $this->genMsgContent(self::ERROR_MAX_LENGTH, $filter->label));
                        continue;
                    }
                }
                switch($filter->filter){
                case self::FILTER_DATE:
                    $fillData = DefaultHttpInputValidation::getDate(HttpConst::TYPE_REQUEST, $fieldName, HttpConst::FILTER_DATE);
                    if(!$fillData){
                        //$this->action->addError($property, self::ERROR_NUMBER);
                        $this->action->addError($property, $this->genMsgContent(self::ERROR_DATE, $filter->label));
                    }
                    else{
                        $object->$property = $fillData;
                    }
                    break;
                case self::FILTER_DATETIME:
                    $fillData = DefaultHttpInputValidation::getDate(HttpConst::TYPE_REQUEST, $fieldName, HttpConst::FILTER_DATETIME);
                    if(!$fillData){
                        //$this->action->addError($property, self::ERROR_NUMBER);
                        $this->action->addError($property, $this->genMsgContent(self::ERROR_DATETIME, $filter->label));
                    }
                    else{
                        $object->$property = is_array($fillData) ? implode(' ', $fillData) : $fillData;
                    }
                    break;
                case self::FILTER_NUMBER:
                    $fillData = DefaultHttpInputValidation::getDate(HttpConst::TYPE_REQUEST, $fieldName, HttpConst::FILTER_NUMBER);
                    if(!$fillData){
                        //$this->action->addError($property, self::ERROR_NUMBER);
                        $this->action->addError($property, $this->genMsgContent(self::ERROR_NUMBER, $filter->label));
                    }
                    else{
                        $object->$property = $fillData;
                    }
                    break;
                case self::FILTER_POSITIVE_NUMBER:
                    $fillData = DefaultHttpInputValidation::getDate(HttpConst::TYPE_REQUEST, $fieldName, HttpConst::FILTER_POSITIVE_NUMBER);
                    if(!$fillData){
                        //$this->action->addError($property, self::ERROR_NUMBER);
                        $this->action->addError($property, $this->genMsgContent(self::ERROR_POSITIVE_NUMBER, $filter->label));
                    }
                    else{
                        $object->$property = $fillData;
                    }
                    break;
                case self::FILTER_EMAIL:
                    $fillData = DefaultHttpInputValidation::getDate(HttpConst::TYPE_REQUEST, $fieldName, HttpConst::FILTER_EMAIL);
                    if(!$fillData){
                        //$this->action->addError($property, self::ERROR_EMAIL);
                        $this->action->addError($property, $this->genMsgContent(self::ERROR_EMAIL, $filter->label));
                    }
                    else{
                        $object->$property = $fillData;
                    }
                    break;
                case self::FILTER_URL:
                    $fillData = DefaultHttpInputValidation::getDate(HttpConst::TYPE_REQUEST, $fieldName, HttpConst::FILTER_URL);
                    if(!$fillData){
                        //$this->action->addError($property, self::ERROR_URL);
                        $this->action->addError($property, $this->genMsgContent(self::ERROR_URL, $filter->label));
                    }
                    else{
                        $object->$property = $fillData;
                    }
                    break;
                case self::FILTER_HTML:
                    $fillData = DefaultHttpInputValidation::getDate(HttpConst::TYPE_REQUEST, $fieldName, HttpConst::FILTER_HTML);
                    if(!$fillData){
                        //$this->action->addError($property, self::ERROR_HTML);
                        $this->action->addError($property, $this->genMsgContent(self::ERROR_HTML, $filter->label));
                    }
                    else{
                        $object->$property = $fillData;
                    }
                    break;
                case self::FILTER_STRING_NUMBER:
                    $fillData = DefaultHttpInputValidation::getDate(HttpConst::TYPE_REQUEST, $fieldName, HttpConst::FILTER_STRING_NUMBER);
                    if(!$fillData){
                        $this->action->addError($property, $this->genMsgContent(self::ERROR_STRING_NUMBER, $filter->label));
                    }
                    else{
                        $object->$property = $fillData;
                    }
                    break;
                case self::FILTER_STRING:
                    default:
                        break;
                }
            }
        }
    }


    public function getActionFilterTypes(&$action){
        $key = self::CACHE_PREFIX . 'action_' .  $action->getConfig()->clzName;
        $filerTypes = array();

        global $__USE_JSON_FILTER__;
        if($__USE_JSON_FILTER__){
        	require_once 'filters.inc';
        	$filters = json_decode($__filters_json);
        	return $filters[$key];
        }


        if($this->cache->fetch($key)===FALSE){
            $ref = new ReflectionObject($action);
            $this->recursionLevel = 0;
            $filerTypes = $this->parseDoc($ref, self::PARENT_TYPE_ACTION);

            global $__DUMP_DOC_TAGS__;
            if($__DUMP_DOC_TAGS__){
            $json_arr[$key] = $filerTypes;
            	echo json_encode($json_arr);
            }

            $this->cache->store($key, new ArrayObject($filerTypes));
        }
        else{
            $filerTypes = $this->cache->fetch($key);
        }
        return $filerTypes;
    }

    public function getClassFilterTypes($clzName){
        $key = self::CACHE_PREFIX . 'class_' . $clzName;
        $filerTypes = array();
        if($this->cache->fetch($key)===FALSE){
            $ref = new ReflectionClass($clzName);
            $filerTypes = $this->parseDoc($ref, self::PARENT_TYPE_CLASS);
            $this->cache->store($key,new ArrayObject($filerTypes));
        }
        else{
            $filerTypes = $this->cache->fetch($key);
        }
        return $filerTypes;
    }
    public function parseDoc(Reflector $ref, $parentType){
        //$ref = new ReflectionObject($action);

        $filterTypes = array();

        $properties = $ref->getProperties();
        foreach($properties as $property){
            if($property->isPublic()){
                $filterType = new FilterType();
                $filterType->parentClz = $ref->getName();
                $filterType->parentType = $parentType;
                $filterType->name = $property->getName();
                $filterType->filter = self::FILTER_STRING;
                $filterType->flag = '';
                $filterType->maxLength = 0;
                $filterType->isnull = true;
                $filterType->label = self::LABEL_DEFAULT;
                $docTags = $this->fetchDocTags($property->getDocComment());
                foreach($docTags  as $tag => $value){
                    if(self::TAG_FILTER == $tag){
                        $filterType->filter = $value;
                    }

                    if(self::TAG_VAR == $tag){
                        $filterType->clzName = $value;
                    }

                    if(self::TAG_FLAG == $tag){
                        $filterType->flag = $value;
                    }

                    if(self::TAG_CHARSET == $tag){
                        $filterType->charset = $value;
                    }

                    if(self::TAG_ISNULL == $tag){
                        $filterType->isnull = ('false' == strtolower($value))?false:true;
                    }

                    if(self::TAG_LABEL == $tag){
                        $filterType->label = $value;
                    }

                    if(self::TAG_MAX_LENGTH == $tag){
                        $filterType->maxLength = $value;
                    }
                }

                if($filterType->filter == self::FILTER_CLASS){
                    if($this->recursionLevel++ < $this->maxRecursionLevel){
                        $filterType->filterTypes = $this->getClassFilterTypes($filterType->clzName);
                        $this->recursionLevel--;
                    }else{
                        throw new SystemException(SystemException::RUNTIME_ERROR,'Out of limit(' . $this->maxRecursionLevel . ') of recursion level.');
                    }
                }
                $filterTypes[$property->getName()] = $filterType;
            }
        }
        return $filterTypes;
    }


    public function fetchDocTags($docComment){
        $tags = array();
        $lines = explode("\n",$docComment);
        foreach($lines as $line){
            preg_match(self::PATTERN, trim($line), $reg);
            //	print_r($reg);
            //	echo "\n";
            if(count($reg)== 3){
                $tags[trim($reg[1])] = trim($reg[2]);
            }
        }
        return $tags;
    }
    public function addError($fieldId, $content, $details=array()){
        $message = new Message();
        $message->type = Message::TYPE_ERROR;
        $message->ownerId = $fieldId;
        $message->content = $content;
        $message->details = $details;
        $this->_errors[$fieldId][] = $message;
    }
}

class FilterType{
    public $parentClz;
    public $parentType;
    public $name;
    public $isnull;
    public $label;
    public $filter;
    public $flag;
    public $charset;
    public $clzName;
    public $filterTypes;
    public $maxLength;
}
