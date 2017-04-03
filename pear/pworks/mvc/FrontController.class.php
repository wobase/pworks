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

require_once('pworks/mvc/IConfigHelper.iface.php');
require_once('pworks/mvc/ISystemDataHelper.iface.php');
require_once('pworks/mvc/SysDataType.class.php');
require_once('pworks/util/ClassLoader.class.php');
require_once('pworks/common/cache/ICacheHelper.iface.php');

/**
 * Enter description here...
 *
 * TODO
 * [2014-06-25] 将主类中的 APC Cache抽象为适配器接口
 *
 *
 * ==================DONE========================
 * [2012-01-14] Add parameter element into Filter
 *
 * [2009-05-26] Fix issue for sequnce of excuting filters
 *
 * [2009-05-26] Implement Feature Request 2777528
 * <a href="http://sourceforge.net/tracker/?func=detail&amp;aid=2777528&amp;group_id=214107&amp;atid=1028362">Global Filter</a>
 *
 * [2009-05-26] Enhance Error handling for Result components
 *
 * @example
 * <code>
 * define(__DEBUG__, false);
 *
 * require_once('pworks/mvc/FrontController.class.php');
 * require_once('pworks/mvc/DefaultSystemDataHelper.class.php');
 * require_once('pworks/mvc/CachedConfigHelper.class.php');
 *
 * require_once('pworks/common/cache/impl/SimpleCacheHelper.class.php');
 * require_once('pworks/common/cache/impl/ApcCache.class.php');
 *
 * try{
 *
 * //set config helper
 * $confHelper = new CachedConfigHelper();
 * $confCacheHelper = new SimpleCacheHelper();
 * $confCacheHelper->setGroup('conf_for_appname');
 * $confCacheHelper->setCache(new ApcCache(), 1);
 * $confHelper->setCacheHelper($confCacheHelper);
 * $doConfigCheck = false;
 * $confHelper->init('path/to/config/app.xml', $doConfigCheck);
 * FrontController::$confHelper = $confHelper;
 *
 * //set data input helper
 * FrontController::$dataHelper = new DefaultSystemDataHelper();
 *
 * //set cache helper
 * $appCacheHelper = new SimpleCacheHelper();
 * $appCacheHelper->setGroup('obj_for_appname');
 * $appCacheHelper->setCache(new ApcCache(), 1);
 * FrontController::$cacheHelper = $appCacheHelper;
 *
 *
 * //startup
 * FrontController::start();
 *
 * }catch(SystemException $e){
 * 	if(__DEBUG__){
 *    echo "<h3>SystemException</h3>";
 *    echo "<B>ID:</B>". $e->id;
 *    echo "<br>";
 *    echo "<B>TYPE:</B>". $e->type;
 *    echo "<br>";
 *    echo '<B>MESSAGE:</B><font size="4" color="red">';
 *    echo $e->message;
 *    echo "</font>";
 *    echo "<br>";
 *  }
 * }
 * </code>
 */
class FrontController {
	/**
	 * @var IConfigHelper
	 */
	public static $confHelper;
	/**
	 * @var ISystemDataHelper
	 */
	public static $dataHelper;
	/**
	 * @var ICacheHelper
	 */
	public static $cacheHelper;


	private static $_debug;
	private static $_debugInfo;
	private static $_filterStack;

	private static $_actionStack = array();


	public static function isDebug($debug){
		self::$_debug = $debug;
	}

	public static function getDebugInfo(){
		return self::$_debugInfo;
	}

	/**
	 * @return IConfigHelper
	 */
	public static function getConfHelper(){
		return self::$confHelper;
	}

	/**
	 * @return ISystemDataHelper
	 */
	public static function getDataHelper(){
		return self::$dataHelper;
	}





	/**
	 * ----[#1][2011-12-08][Milo Liu] Start----
	 * Add two new parameters for reusing this fucntion for internal action calls
	 * @param actionId string, default is NULL, it's used to specify an action manually
	 * @param execFilters boolean, default in TRUE, detemind if execute the filters for the action
	 * @param isReturnAction boolean, default FLASE, if this parameter be set as TRUE, the action instance will be returned before rendering the result for the action
	 * ----[#1]End----
	 */
	public static function start($actionId=NULL, $execFilters=TRUE, $isReturnAction=FALSE) {
		//--- #9 -----------------------
		// Added by Milo <cutadra@gmail.com> on Aug. 06, 2016
		// 修复Action循环嵌套问题(#9)
		if(array_key_exists($actionId, self::$_actionStack)){
			self::$_actionStack[$actionId]++;
		}else{
			self::$_actionStack[$actionId] = 1;
		}

		if(self::$_actionStack[$actionId] > 5){
			$errMsg = "Action[id:$actionId] had been called more 5 times in a single"
			. " HTTP Request process, there may exist loop call, please check if there"
			. " exist codes using BaseAction::callAction() or FrontController::start()"
			. " methods.";
			throw new Exception($errMsg, 50903);
			return;
		}
		//--- #9 -----------------------

		//echo __FILE__.','.__LINE__.', =============================================<br>';
		//echo __FILE__.','.__LINE__.', start action:'. $actionId . "<br>";;
		//echo __FILE__.','.__LINE__.', Mem Use:'. ( memory_get_usage() / 1024 / 1024) . "MB<br>";
		if(self::$_debug){
			self::$_debugInfo = array();
		}

		//[2009-05-26] Fix issue for sequnce of excuting filters
		//------------------------------------------------------
		self::$_filterStack = array();


		//----[#1]Start---
		$actionName = '';
		if(NULL != $actionId){
			$actionName = $actionId;
		}else{
			//----[#1]End---
			$actionParam = self::$dataHelper->get('action',SysDataType::REQUEST);
			//$actionName = '';  //----[#1]----
			if($actionParam != NULL){
				if(is_array($actionParam)){
					$actionName = $actionParam[0];
				}else{
					$actionName = $actionParam;
				}
			}
		}//----[#1]----

		//echo __FILE__.','.__LINE__.', Mem Use:'. ( memory_get_usage() / 1024 / 1024) . "MB<br>";

		$actionConf = self::$confHelper->getActionConfig($actionName);
		if($actionConf == NULL){
			$actionName = self::$confHelper->getDefaultAction();
			$actionConf = self::$confHelper->getActionConfig($actionName);
		}
		$action = NULL;
		try{
			$action = ClassLoader::getInstance($actionConf->clzName);
		}catch(SystemException $e){
			$e->message = $e->getMessage() . '[in'.__FILE__.', line:'.__LINE__.' ]';
			throw $e;
		}

		$action->setConfig($actionConf);
		//DebugUtil::dump($action, __FILE__, __LINE__);


		// [2017-04-01] Milo <cutadra@gmail.com>
		// fill get and post data into action
		$action->_http_get = self::$dataHelper->getVar(SysDataType::GET);
		$action->_http_post = self::$dataHelper->getVar(SysDataType::POST);


		//[2009-05-26] Fix issue for sequnce of excuting filters
		//------------------------------------------------------
		//if(!self::executeFilters($actionConf, $action, 'pre')){
		//echo __FILE__.','.__LINE__.', Mem Use:'. ( memory_get_usage() / 1024 / 1024) . "MB<br>";
		if($execFilters){//----[#1]----

			if(!self::executeFiltersBefore($actionConf, $action)){
				return;
			}

		}//----[#1]----
		//echo __FILE__.','.__LINE__.', Mem Use:'. ( memory_get_usage() / 1024 / 1024) . "MB<br>";
		$reqUSec = explode(" ", microtime());
		$action->head['requestTime'] = date("Y-m-d H:i:s.") . substr($reqUSec[0], 2);

		$result = $action->execute();

		$resUSec = explode(" ", microtime());
		$action->head['responseTime'] = date("Y-m-d H:i:s.") . substr($resUSec[0],2);
		//echo __FILE__.','.__LINE__.', Mem Use:'. ( memory_get_usage() / 1024 / 1024) . "MB<br>";
		if($result == null){
			$errMsg = 'No result for Action[id=' . $actionName .']';
			throw new SystemException(SystemException::FRAMEWORK_ERROR,$errMsg);
		}
	//	echo __FILE__.','.__LINE__.', Mem Use:'. ( memory_get_usage() / 1024 / 1024) . "MB<br>";
		//--------------------------------
		//[Jul. 23, 2009][Milo]
		//set result into action
		$action->setResult($result);

		//[2009-05-26] Fix issue for sequnce of excuting filters
		//------------------------------------------------------
		//if(!self::executeFilters($actionConf, $action, 'post')){
	//	echo __FILE__.','.__LINE__.', Mem Use:'. ( memory_get_usage() / 1024 / 1024) . "MB<br>";
		if($execFilters){//----[#1]----
			if(!self::executeFiltersAfter($actionConf, $action)){
				return;
			}
		}//----[#1]----
		//echo __FILE__.','.__LINE__.', Mem Use:'. ( memory_get_usage() / 1024 / 1024) . "MB<br>";

		//----[#1]Start----
		if($isReturnAction){
			return $action;
		}
		//----[#1]End----


		//[2009-05-26] Enhance Error handling for Result components
		//		self::showResult($actionConf->results[$result], $action);
		self::showActionResult($actionConf, $result, $action);
	//	echo __FILE__.','.__LINE__.', Mem Use:'. ( memory_get_usage() / 1024 / 1024) . "MB<br>";
	}


	//----------------------------------------------------------------------
	//[2009-05-26] Fix issue for sequnce of excuting filters
	//
	// remove function excuteFilters, then create three new functions executeFiltersBefore,
	// executeFiltersAfter, and getFilterInstance to instead of it
	//----------------------------------------------------------------------
	/*
	   private static function executeFilters(ActionConfig $conf, IAction &$action, $type){
	   if(is_array($conf->filters)){
	   foreach($conf->filters as $filterId){
	   $filterConf = self::$confHelper->getFilterConfig($filterId);
	   try{
	   ClassLoader::isClassExist($filterConf->clzName);
	   }catch(SystemException $e){
	   $e->message = $e->getMessage() . '[in'.__FILE__.', line:'.__LINE__.' ]';
	   throw $e;
	   }

	   $cacheKeyName = 'filter_instance_' . $filterConf->id;
	   $filter = self::$cacheHelper->fetch($cacheKeyName);
	   if($filter == null){
	   try{
	   $filter = ClassLoader::getInstance($filterConf->clzName);
	   self::$cacheHelper->store($cacheKeyName, $filter);
	   }catch(SystemException $e){
	   $e->message = $e->getMessage() . '[in'.__FILE__.', line:'.__LINE__.' ]';
	   throw $e;
	   }
	   }

	   $result = null;
	   if($type == 'pre'){
	   $result = $filter->before($action);
	   if(self::$_debug){
	   self::$_debugInfo[] = '[PreFilterReslut]'.$result;
	   }
	   }
	   if($type == 'post'){
	   $result = $filter->after($action);
	   if(self::$_debug){
	   self::$_debugInfo[] = '[PostFilterReslut]'.$result;
	   }
	   }

	   if($result != null){
	   self::showResult($filterConf->results[$result], $action);
	   return false;
	   }
	   }
	   }
	   return true;
	   }
	 */

	/**
	 *
	 * @param $conf
	 * @param $action
	 * @return boolean, TRUE for passing through filters, or FALSE for filtered by one filter.
	 */
	private static function executeFiltersBefore(ActionConfig $conf, IAction &$action){
		//[2009-05-26] Implement Feature Request 2777528
		if(is_array(self::$confHelper->getGlobalFilters())){
			foreach(self::$confHelper->getGlobalFilters() as $globalFilterConf){
				if(is_array($globalFilterConf->excludes) && array_key_exists($conf->id, $globalFilterConf->excludes)){
					continue;
				}
				$filter = self::getFilterInstance($globalFilterConf);
				$result = null;
				$result = $filter->before($action);
				if($result != null){
					self::showFilterResult($globalFilterConf, $result, $action);
					return false;
				}
				array_push(self::$_filterStack, array('conf'=> $globalFilterConf, 'obj'=>$filter));
			}
		}

		if(is_array($conf->filters)){
			foreach($conf->filters as $filterId){
				$filterConf = null;
				$filterConf = self::$confHelper->getFilterConfig($filterId);
				if(null == $filterConf){
					$errorMessage = '['.__FILE__.', line:'.__LINE__.' ][Filter: ' . $filterId . '] Filter Config not be found';
					$e = new SystemException(SystemException::FRAMEWORK_ERROR, $errorMessage);
					throw $e;
				}
				$filter = self::getFilterInstance($filterConf);
				$result = null;
				$result = $filter->before($action);
				if($result != null){
					self::showFilterResult($filterConf, $result, $action);
					return false;
				}
				array_push(self::$_filterStack, array('conf' => $filterConf, 'obj'=>$filter));
			}
		}

		return true;
	}


	/**
	 *
	 * @param $conf
	 * @param $action
	 * @return boolean, TRUE for passing through filters, or FALSE for filtered by one filter.
	 */
	private static function executeFiltersAfter(ActionConfig $conf, IAction &$action){
		while( ($filterArray=array_pop(self::$_filterStack)) != null){
			$filterConf = $filterArray['conf'];
			$filter = $filterArray['obj'];
			$result = null;
			$result = $filter->after($action);
			if($result != null){
				self::showFilterResult($filterConf, $result, $action);
				return false;
			}
		}

		return true;
	}


	/**
	 *
	 * @param $filterConf
	 * @return IFilter
	 */
	private static function getFilterInstance(FilterConfig $filterConf){
		try{
			ClassLoader::isClassExist($filterConf->clzName);
		}catch(SystemException $e){
			$e->message ='['.__FILE__.', line:'.__LINE__.' ][Filter: '.$filterConf->id.']'.$e->getMessage();
			throw $e;
		}

		$cacheKeyName = 'filter_instance_' . $filterConf->id;
		$filter = self::$cacheHelper->fetch($cacheKeyName);
		if($filter == null){
			try{
				$filter = ClassLoader::getInstance($filterConf->clzName);
				//[2012-01-14] Add parameter element into Filter
				//----------------------------------------------
				foreach ($filterConf->parameters as $key => $value){
					$filter->$key = $value;
				}

				self::$cacheHelper->store($cacheKeyName, $filter);
			}catch(SystemException $e){
				$e->message ='['.__FILE__.', line:'.__LINE__.' ][Filter: '.$filterConf->id.']'.$e->getMessage();
				throw $e;
			}
		}

		return $filter;
	}

	//--------------------------------END---------------------------------
	//[2009-05-26] Implement Feature Request 2777528
	//[2009-05-26] Fix issue for sequnce of excuting filters
	//--------------------------------------------------------------------


	//------------------------------------------------------------
	//[2009-05-26] Enhance Error handling for Result components
	//Add two functions showFilterResult and showActionResult
	//------------------------------------------------------------
	private static function showFilterResult(FilterConfig $conf, $resultId, IAction &$action){
		if((!is_array($conf->results)) || (!array_key_exists($resultId, $conf->results))) {
			$errMsg = $errorMessage = '['.__FILE__.', line:'.__LINE__.' ][Action: ' .$action->getConfig()->id. '][Result: ' . $resultId . '] Result Config not be found';
			$e = new SystemException(SystemException::FRAMEWORK_ERROR, $errorMessage);
			throw $e;
		}
		try{
			self::showResult($conf->results[$resultId], $action);
		}catch(SystemException $e){
			$e->message = '['.__FILE__.', line:'.__LINE__.' ][Action: ' .$action->getConfig()->id. '][Result: ' . $resultId . '] ' . $e->message;
		}
	}

	private static function showActionResult(ActionConfig $conf, $resultId, IAction &$action){
		if((!is_array($conf->results)) || (!array_key_exists($resultId, $conf->results))) {
			$errMsg = $errorMessage = '['.__FILE__.', line:'.__LINE__.' ][Action: ' .$conf->id. '][Result: ' . $resultId . '] Result Config not be found';
			$e = new SystemException(SystemException::FRAMEWORK_ERROR, $errorMessage);
			throw $e;
		}
		try{
			self::showResult($conf->results[$resultId], $action);
		}catch(SystemException $e){
			$e->message = '['.__FILE__.', line:'.__LINE__.' ][Action: ' .$conf->id. '][Result: ' . $resultId . '] ' . $e->message;
		}
	}

	private static function showResult(ResultConfig $resultConf, IAction &$action){
		$rsTypeConf = self::$confHelper->getResultType($resultConf->type);

		ClassLoader::isClassExist($rsTypeConf->clzName); //throw a SystemException here...

		$rsTypeInsKey = 'rs_type_instance_'. $resultConf->type;
		$rsObj = self::$cacheHelper->fetch($rsTypeInsKey);
		if($rsObj==null){
			$rsObj = ClassLoader::getInstance($rsTypeConf->clzName);  //throw a SystemException here...
			self::$cacheHelper->store($rsTypeInsKey,$rsObj);
		}
		$rsObj->show($action, $resultConf);
	}
	//---------------------------END------------------------------
	//[2009-05-26] Enhance Error handling for Result components
	//Add two functions showFilterResult and showActionResult
	//------------------------------------------------------------
	}
