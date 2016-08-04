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

  public function execute(){

    $appConfig = FrontController::getConfHelper()->getApp();
    $restConfigs = $appConfig->rest[$method];
    $rs = $this->matchAction($restConfigs, $url);
    if( null === $rs){
      $this->addError('action', '405');
      $this->__status = '405';
      return 'succ';
    }

    $actionId = $rs['action'];
    $param = $rs['param'];

    $restAction = $this->callAction($actionId, $param);

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
          return array('action' => $restCfg->action->id, 'param' => $param);
        }
      }
    }

    return null;
  }
}
