<?php
/*
 * Copyright 2011 - 2016 Milo Liu<cutadra@gmail.com>.
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

require_once('pworks/mvc/action/BaseAction.class.php');

/**
* Added by Milo<cutadra@gmail.com> on Aug. 3rd, 2016-08-03
* 配合 (wobase #7) 需求中的完整 Restful 支持, 添加入口的路由处理器,
* 用于将Restful URL 导向至具体的Action处理, 并将结果以定义好的JSON
* 格式返回给调用方
*
* 要正确获取method值, 需要在apache中添加rewrite rule如下:
* {{{#!ini
* # {htdoc}/.htaccess
* Options +FollowSymLinks
* RewriteEngine On
* RewriteBase /
*
* RewriteRule ^(\w+)\.json$ index\.php\?action=restful&url=$1&method=%{REQUEST_METHOD} [QSA,L]
* }}}
*
* 与原有的重写规则唯一的区别, 就是添加了 method=%{REQUEST_METHOD} 这一段自动从服务器获取
* http method的代码, 在Nignx服务器, 对应的变更 为 $request_method.
*
* @see pworks.mvc.result.RestfulJsonResult
*
*/
class RestRouterAction extends BaseAction{
  public $url;
  public $method;
  public $content_type;

  // [2017-04-01] Milo <cutadra@gmail.com>
  // Added new member, head, it will be used to store
  // the following common head information for restful response.
  // - requestUrl       full url without get parameters
  // - requestMethod    HTTP Method
  // - requestTime      date('Y-m-d H:i:s.v')
  // - responseTime     date('Y-m-d H:i:s.v')
  // - requestParameters all parameter in get post and rest path
  public $head = array();

  public function execute(){

//var_dump($this->url);
//var_dump($this->method);
//var_dump($this->content_type);
//var_dump($this->_http_get);
//var_dump($this->_http_post);



    $method = strtolower(trim($this->method));

    $this->head['requestMethod'] = $this->method;
    $this->head['requestUrl'] = $this->url;


    $appConfig = FrontController::getConfHelper()->getApp();

#var_dump($appConfig);

    //print_r($this);exit;

    //print_r($appConfig);exit;
    $restConfigs = $appConfig->rest[$method];
    //print_r($restConfigs);
    $rs = $this->matchAction($restConfigs, $this->url);

//var_dump($rs);

    if( null === $rs){
      $this->addError('405', 'action:'. __CLASS__);
      $this->addError('405', 'url:'. $this->url);
      $this->addError('405', 'method:'. $method);
      $this->__status = '405';
      return 'succ';
    }

    $actionId = $rs['action']->id;
    $param = $rs['param'];

    if('application/json' == strtolower(trim($this->content_type))) {
      $jsonData = json_decode($this->_http_post , true);

//var_dump($jsonData);

      if( NULL === $jsonData){
        $this->addError('405', 'Invalid JSON String in the POST body: ' . $this->_http_post);
        $this->__status = '406';
        return 'succ';
      }else{
        foreach($jsonData as $postJsonField => $postJsonValue ){
          $param[$postJsonField] = $postJsonValue;
        }
      }
    }

    $this->head['requestParameters'] = $param;
    //$this->head['requestParameters']['get'] = $this->_http_get;
    //$this->head['requestParameters']['post'] = $this->_http_post;

//var_dump($param);
//var_dump($actionId);
    $restAction = $this->callAction($actionId, $param);
//var_dump($restAction);

    $this->_data = $restAction->getData();
    $this->_errors = $restAction->getErrors();
    $this->_warnings = $restAction->getWarnings();
    $this->_infos = $restAction->getInfos();
    $this->__status = $restAction->getResult();



    return 'succ';
  }

  /**
   * 匹配Restful URL对应的Action, 并且, 如果地址中包含参数, 则解释出参数值
   * @param  RestConfig[] $restConfig 已经通过method过滤后的RestConfig数组
   * @param  String $url  实际请求的URL地址
   * @return array(
   *         'action' =>  actionId,
   *         'param' => array( key => value )) for 成功匹配
   *       OR
   *       	 NULL 没有找到对应的API定义
   */
  public function matchAction($restConfigs, $url){
    $url = trim($url);

    foreach($restConfigs as $urlPattern => $restCfg){
      $matchs = null;
      $fullPattern = '#^'.$urlPattern.'$#';
      preg_match($fullPattern, $url , $matches);
      if(is_array($matches)){
        $right = false;
        $param = array();
        foreach($matches as $key => $value){
          if($key === 0 && $value === $url){
            $right = true;
          }
          if(!is_int($key)){
            $param[$key] = $value;
          }
        }

        if($right){
          return array('action' => $restCfg->action, 'param' => $param);
        }
      }
    }

    return null;
  }

  public function getStatus(){
    return $this->__status;
  }
}
