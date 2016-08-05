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

require_once('pworks/mvc/AppConfig.class.php');
require_once('pworks/mvc/ActionConfig.class.php');
require_once('pworks/mvc/FilterConfig.class.php');
require_once('pworks/mvc/ResultTypeConfig.class.php');
require_once('pworks/mvc/ResultConfig.class.php');
require_once('pworks/mvc/RestConfig.class.php');


/**
 * [2014-12-09] Upgraded by Milo Liu <cutadra@gmail.com>
 *  简化action的配置，现在可以将action节点简化为以下的方式：
 *  <action id="GetDailyData"/>
 *  此简化方式等价于
 *  <action id="GetDailyData" class="action.GetDailyDataAction">
 *     <result id="succ"/>
 *  </action>
 *  其中：
 *  - 默认的包名为 action
 *  - 默认的完整类名为id值加上 Action
 *  - 自动添加一个id为succ的result设置
 * ----------------------------------------------------------------------
 *
 *
 * [2012-01-14] Add parameter element into Filter
 * [2009-05-26] Implement Feature Request 2777528 <a href="http://sourceforge.net/tracker/?func=detail&amp;aid=2777528&amp;group_id=214107&amp;atid=1028362">Global Filter</a>
 *
 * xml config sources:
 * <pre>
 * <application id="myApp" default-action="login">
 * 	<globals>
 * 		<global name="dao-config-file" value="dao.xml"/>
 * 		<global name="app-root" value="path/to/app/root"/>
 * 	</globals>
 * 	<resultTypes>
 * 		<resultType id="php" class="pworks.mvc.result.PhpResult" />
 * 		<resultType id="rosetta" class="pworks.mvc.result.RosettaResult" />
 * 	</resultTypes>
 * 	<filters>
 * 		<filter id="dao" class="pworks.mvc.filter.DaoInitFilter"/>
 * 		<filter id="vo"	class="pworks.mvc.filter.VoFillingFilter"/>
 * 		<filter id="auth"	class="myApp.action.AuthFilter">
 * 			<result id="failed" type="php" src="login.php"/>
 * 		</filter>
 *
 *      <!-- [2009-05-26] Implement Feature Request 2777528 -->
 * 		<filter id="glbDict" class="myApp.filter.GlobalDictFilter" type="global">
 * 			<exclude id="index"/>
 * 			<exclude id="login"/>
 * 			<exclude id="logout"/>
 * 		</filter>
 *
 * 		<!-- [2012-01-14] Add parameter element info Filter -->
 * 		<filter id="globalMessage" class="common.filter.GlobalMessageFilter" type="global">
 * 			<parameter key="langPackPath" value="message/messages.txt"/>
 * 			<parameter key="format" value="text/csv"/>
 * 			<!-- format could be xml, php/array, php/class, etc. -->
 * 		</filter>
 *
 * 	</filters>
 * 	<actions>
 * 		<action id="login" class="pworks.mvc.BlankAction">
 * 			<result id="succ" type="php" src="login.php"/>
 * 		</action>
 * 		<action id="loginSubmit" class="myApp.action.LoginSubmitAction">
 * 			<filter id="dao"/>
 * 			<filter id="vo"/>
 * 			<result id="succ" type="php" src="main.php"/>
 * 			<result id="failed" type="php" src="login.php"/>
 * 		</action>
 * 		<action id="addUser" class="pworks.mvc.BlankAction">
 * 			<filter id="auth">
 * 				<param name="userType" value="admin"/>
 * 			</filter>
 * 			<result id="succ" type="php" src="admin/user/new_user_form.php"/>
 * 		</action>
 *
 * 		<action id="addUserSubmit" class="myApp.action.AddUserSubmitAction">
 * 			<filter id="auth">
 * 			<!-- ================================================= -->
 * 			<!-- this feature is not implemented in current version-->
 * 			<!--	<param name="userType" value="admin"/> -->
 * 			<!-- ================================================= -->
 * 			</filter>
 * 			<filter id="dao"/>
 * 			<filter id="vo"/>
 * 			<result id="succ" type="php" src="admin/user/new_user_view.php"/>
 * 			<result id="failed" type="php" src="admin/user/new_user_form.php"/>
 * 		</action>
 *
 * 		<!-- ================================================= -->
 * 		<!-- Aug. 03, 2016 添加对Restful API的完整支持 -->
 * 		<--
 * 		1. 当不指定class属性时, 自动由id属性值添加后缀Action做为class属性的值, 如下面的配置中
 * 		id="ticket.Search", 则class的值自动设置为ticket.SearchAction, 最终
 * 		-->
 * 		<action id="ticket.Search" type="rest" method="get" url="/ticket">
 * 			<result id="succ" type="json"/>
 * 		</action>
 * 		<!-- ================================================= -->
 *
 * 	</actions>
 * </application>
 */
class AppXMLCfgLoader {

	private $AppConfig;

	public function load($filename) {
		$dom = new DOMDocument();
		$dom->load($filename);

		$appNode = $dom->getElementsByTagName('application')->item(0);
		$this->AppConfig = new AppConfig();
		$this->AppConfig->id = $appNode->getAttribute('id');
		$this->AppConfig->defaultAction = $appNode->getAttribute('default-action');

		$xpath = new DOMXPath($dom);
		$globalNodes = $xpath->query('//application/globals/global');
		foreach ($globalNodes as $gNode){
			$key = $gNode->getAttribute('name');
			$value = $gNode->getAttribute('value');
			$this->AppConfig->globals[$key] = $value;
		}

		$resultTypeNodes = $xpath->query('//application/resultTypes/resultType');
		foreach ($resultTypeNodes as $rsTpNode){
			$rsType = new ResultTypeConfig();
			$rsType->id = $rsTpNode->getAttribute('id');
			$rsType->clzName = $rsTpNode->getAttribute('class');
			$this->AppConfig->resultTypes[$rsType->id] = $rsType;
		}



		$filterNodes = $xpath->query('//application/filters/filter');
		foreach ($filterNodes as $fltNode){
			$filter = new FilterConfig();
			$filter->id = $fltNode->getAttribute('id');
			$filter->clzName = $fltNode->getAttribute('class');

			//[2009-05-26] Implement Feature Request 2777528
			//----------------------------------------------
			$filter->type = $fltNode->getAttribute('type')?$fltNode->getAttribute('type'):FilterConfig::TYPE_DEFAULT;

			$filterResultNodes = $xpath->query('//application/filters/filter[@id="'.$filter->id.'"]/result');
			foreach ($filterResultNodes as $filterResultNode){
				$result = new ResultConfig();
				$result->id = $filterResultNode->getAttribute('id');
				$result->type = $filterResultNode->getAttribute('type');
				$result->src = $filterResultNode->getAttribute('src');
				$filter->results[$result->id] = $result;
			}

			//[2009-05-26] Implement Feature Request 2777528
			//----------------------------------------------
			$filterExcludeNodes = $xpath->query('//application/filters/filter[@id="'.$filter->id.'"]/exclude');
			foreach($filterExcludeNodes as $filterExcludeNode){
				$exActionId = $filterExcludeNode->getAttribute('id');
				$filter->excludes[$exActionId] = $exActionId;
			}

			//[2012-01-14] Add parameter element into Filter
			//----------------------------------------------
			$filterParamNodes = $xpath->query('//application/filters/filter[@id="'.$filter->id.'"]/parameter');

			foreach($filterParamNodes as $filterParamNode){
				$key = $filterParamNode->getAttribute('key');
				$value = $filterParamNode->getAttribute('value');
				$filter->parameters[$key] = $value;
			}


			$this->AppConfig->filters[$filter->id] = $filter;

			//[2009-05-26] Implement Feature Request 2777528
			//----------------------------------------------
			if(FilterConfig::TYPE_GLOBAL == $filter->type){
				$this->AppConfig->globalFilters[$filter->id] = $filter;
			}
		}


		$actionNodes = $xpath->query('//application/actions/action');
		foreach ($actionNodes as $actionNode){
			$action = new ActionConfig();
            $action->id = $actionNode->getAttribute('id');

            // ----------------------------------------------
            // [2014-12-09] By Milo Liu <cutadra@gmail.com>
            // 添加对简化action设置的支持
            if($actionNode->hasAttribute('class')){
                $action->clzName = $actionNode->getAttribute('class');
            }else{
                //如果没有设置class属性，则根据id来自动补全类设置
                // 1. package为action
                // 2. 完成类名为id值加上Action后缀
                $action->clzName = 'action.' . $action->id . 'Action';
            }
            //------------------------------------------------

						/**
						 * ================= #7 ===========================
						 * [Aug. 03, 2016] Milo Liu <cutadra@gmail.com>
						 * 添加Restful API配置属性支持
						 * 1. type
						 * 2. method
						 * 3. url
						 */
						/* -------
						   1. type
						   -------
						   i. type 的可选值为 controller 和 rest
						   ii. action 的 type 属性如果不指定或者不在指定的取值范围内, 则默认为'controller'
						*/
						$actionTypeOptions = array('controller','rest');
						$actionType = 'controller';
						if($actionNode->hasAttribute('type')){
						 	$actionType = strtolower($actionNode->getAttribute('type'));
							if(!in_array($actionType, $actionTypeOptions)){
								$actionType = 'controller'; // 强制设置为默认值
								//TODO: 此处需要添加日志, 说明type属性在强制转换前后的值变化情况
							}
					 	}
						$action->type = $actionType;

						/* ---------
						   2. method
						   ---------
						   i. method 的可选值为 get, post, put 和 delete
						   ii. method 属性如果不指定或者不在指定的取值范围内, 则默认设置为'get'
						*/
						$actionMethodOptions = array('get','post', 'put', 'delete');
						$actionMethod = 'get';
						if($actionNode->hasAttribute('method')){
						 	$actionMethod = strtolower($actionNode->getAttribute('method'));
							if(!in_array($actionMethod, $actionMethodOptions)){
								$actionMethod = 'get'; // 强制设置为默认值
								//TODO: 此处需要添加日志, 说明method属性在强制转换前后的值变化情况
							}
					 	}
						$action->method= $actionMethod;

						/* ------
							 3. url
							 ------
							 i. 如果type值为 controller, url值不做验证
							 i. 如果type值为 rest, 则对URL进行如下验证:
							   a) 不可为空
								 b) 只能包含正斜杠(/), 英文字母[a-zA-Z], 数字[0-9], 冒号(:), 下划线(_)
								 和连字符(-)
								 c) 冒号如果存在, 必须紧跟在斜杠之后, 冒号后必须至少存在一个有效变更名,
								 并且, 在下一个斜杠出现前, 不得再出现另一个冒号
								 d) 将冒号表示的变量, 注册到全局的 $this->appConfig->restApiMapping
								 之中, 结构如下:
								array(
								  $action->method => array(
								  	'url_pattern' => array(
											'pattern' => 'url_pattern',
											'raw_url' => $action->url,
											'url_variable' => array( 0 => 'var1' , 1=> 'var2' )
											'action' => $action->id
										),
										...
									)
								)
						*/
						//echo __FILE__ . ':' . __LINE__ . ': type: '. $action->type . "\n";

						if('rest' == $action->type){
							//i. 非空验证
							//echo __FILE__ . ':' . __LINE__ . ': rest block ' .  "\n";
							//echo __FILE__ . ':' . __LINE__ . ': Has URL? ' . $actionNode->hasAttribute('url').  "\n";

							if(! $actionNode->hasAttribute('url')){
								//echo __FILE__ . ':' . __LINE__ . ': No url block ' .  "\n";
								$errorMsg = '[Config File:'.$filename.'][Action ID:'.$action->id.'][HTTP Method:'.$action->method.']';
								throw new Exception($errorMsg.'rest类型的action, 其url设定不可为空!', 50701);
								return;
							}

							$actionUrl = trim($actionNode->getAttribute('url'));
							if(empty($actionUrl)){
								$errorMsg = '[Config File:'.$filename.'][Action ID:'.$action->id.'][HTTP Method:'.$action->method.'][URL: NULL]';
								throw new Exception($errorMsg.'rest类型的action, 其url设定不可为空!', 50701);
								return;
							}

							// ii. 的效性验证
							$urlPattern = '#^/[:_a-zA-Z][-_0-9a-zA-Z]*(/[:_a-zA-Z][-_0-9a-zA-Z]*)*$#';
							$matches = null;
							preg_match_all($urlPattern, $actionUrl, $matches, PREG_PATTERN_ORDER);

							//print_r($actionUrl);
							//echo "\n";
							//print_r($matches[0][0]);
							//echo "\n";


							if( !is_array($matches)
							||  !isset($matches[0]) || !isset($matches[0][0])
							|| $actionUrl !== $matches[0][0]) {
								//print_r($actionUrl);exit;
								$errorMsg = '[Config File:'.$filename.'][Action ID:'.$action->id.'][HTTP Method:'.$action->method.'][URL:'.$actionUrl.']';
								throw new Exception($errorMsg.'URL不符合格式要求, 必须以/开头, 只能包含正斜杠(/), 英文字母[a-zA-Z], 数字[0-9], 冒号(:), 下划线(_)!', 50702);
								return;
							}
							$action->url = $actionUrl;

							/// iii. 剥离URL中的变量名, 并生成最终的动态表达式
              $urlVarPattern = '#/:([_a-zA-Z][-_0-9a-zA-Z]*)/?#';
							$varMatches = null;
							$apiUrlPattern = $action->url;
							preg_match_all($urlVarPattern, $action->url, $varMatches, PREG_PATTERN_ORDER);
							if( count($varMatches)> 1 ){
								foreach($varMatches[1] as $urlVarName){
									$varPattern = "(?P<$urlVarName>[-_0-9a-zA-Z]+)";
									$apiUrlPattern = str_replace(":$urlVarName", $varPattern, $apiUrlPattern);
								}
							}

							if(array_key_exists($apiUrlPattern, $this->AppConfig->rest[$action->method])){
								$errorMsg = '[Config File:'.$filename.'][Action ID:'.$action->id.'][HTTP Method:'.$action->method.'][URL:'.$actionUrl.']';
								throw new Exception($errorMsg.'重复定义的URL及Method, 请检查配置文件, 修正或者清理配置信息!', 50703);
								return;
							}

							$restApiConfig = new RestConfig();
							$restApiConfig->pattern = $apiUrlPattern;
							$restApiConfig->url = $action->url;
							$restApiConfig->method = $action->method;
							$restApiConfig->action = $action;
							$this->AppConfig->rest[$restApiConfig->method][$restApiConfig->pattern] = $restApiConfig;
						}

						/* ================= #7 =========================== */

			$actionFilterNodes = $xpath->query('//application/actions/action[@id="'.$action->id.'"]/filter');
			foreach ($actionFilterNodes as $actionFilterNode){
				$action->filters[]=$actionFilterNode->getAttribute('id');
			}

			$actionResultNodes = $xpath->query('//application/actions/action[@id="'.$action->id.'"]/result');
            //DebugUtil::dump($actionResultNodes->item(0), __FILE__, __LINE__);


			// ----------------------------------------------
            // [2014-12-09] By Milo Liu <cutadra@gmail.com>
            // 添加对简化action设置的支持
            // 如果没有设置result元素，则自动添加一个id值为succ的result配置
            if(0 == sizeof($actionResultNodes) ){
                $result = new ResultConfig();
                $result->id = 'succ';
                $action->results['succ'] = $result;
            }
            // ----------------------------------------------

            foreach ($actionResultNodes as $actionRsNode){
				$result = new ResultConfig();
				$result->id = $actionRsNode->getAttribute('id');
				$result->type = $actionRsNode->getAttribute('type');
				$result->src = $actionRsNode->getAttribute('src');
				$resultParams = $actionRsNode->getElementsByTagName('param');
				$result->params = array();
				foreach($resultParams as $rsParam){
					$result->params[$rsParam->getAttribute('name')] = $rsParam->getAttribute('value');
				}
				$action->results[$result->id] = $result;
			}

			$this->AppConfig->actions[$action->id]	= $action;
		}
	}

	public function getAppConfig(){
		return $this->AppConfig;
	}
}
