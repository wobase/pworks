<?php
require_once('pworks/mvc/AppXMLCfgLoader.class.php');
require_once('pworks/mvc/ActionConfig.class.php');
require_once('pworks/mvc/ResultConfig.class.php');
require_once('pworks/mvc/RestConfig.class.php');


class ActionConfigTest extends PHPUnit_Framework_TestCase{

  /**
  * 普通固定URL
  * @group enhancement_7
  */
  public function testStaticRestUrl(){
    $configFile = dirname(__FILE__) . '/7_rest_normal.xml';

    $expected = new ActionConfig();
    $expected->id = "ticket.Search";
    $expected->clzName = "action.ticket.SearchAction";
    $expected->type = "rest";
    $expected->method = "get";
    $expected->url = "/ticket";

    $result = new ResultConfig();
    $result->id = "succ";
    $result->type = "json";
    //$result->src = '';
    //$result->params = array();

    $expected->results["succ"] = $result;

    $expectedRest = new RestConfig();
    $expectedRest->pattern = '/ticket';
    $expectedRest->url = '/ticket';
    $expectedRest->method = 'get';
    $expectedRest->action = $expected;

    $fixture = new AppXMLCfgLoader();
    $fixture->load($configFile);
    $configs = $fixture->getAppConfig();

    //$this->assertArrayHasKey('ticket.Search', $configs->actions);

    $actual = $configs->actions['ticket.Search'];

    //$this->assertEquals($expected, $actual);

    $this->assertArrayHasKey('/ticket', $configs->rest['get']);

    $actualRest = $configs->rest['get']['/ticket'];

    $this->assertEquals($expectedRest, $actualRest);

    //echo "Excepted\n==========\n";
    //print_r($expectedRest);
    //echo "Actual\n==========\n";
    //($actualRest);
  }

  /**
  *  URL中包含参数
  *   @group enhancement_7
  */
  public function testUrlWithVariables(){
    $configFile = dirname(__FILE__) . '/7_url_with_param.xml';

    $excepted = new RestConfig();
    $excepted->url = '/ticket/id/:ticketId';
    $excepted->pattern = '/ticket/id/(?P<ticketId>[-_0-9a-zA-Z]+)';
    $excepted->method = 'get';

    $action = new ActionConfig();
    $action->id = 'ticket.GetDetails';
    $action->clzName = 'action.ticket.GetDetailsAction';
    $action->type = 'rest';
    $action->method = 'get';
    $action->url = '/ticket/id/:ticketId';

    $result = new ResultConfig();
    $result->id = 'succ';
    $result->type = 'json';

    $action->results[$result->id] = $result;

    $excepted->action = $action;

    $fixture = new AppXMLCfgLoader();
    $fixture->load($configFile);
    $configs = $fixture->getAppConfig();

    //print_r($configs->rest['get']);

    $this->assertArrayHasKey($excepted->pattern, $configs->rest['get']);
    $this->assertEquals($excepted, $configs->rest['get'][$excepted->pattern]);
  }


  /**
   * url为空容错测试
   * @group enhancement_7
   */
  public function testNullUrl(){
    $configFile = dirname(__FILE__) . '/7_null_url.xml';

    $errorMsg = '[Config File:'.$configFile.'][Action ID:ticket.Null][HTTP Method:get]';
    $expected =  new Exception($errorMsg.'rest类型的action, 其url设定不可为空!', 50701);

    $fixture = new AppXMLCfgLoader();
    try{
      $fixture->load($configFile);
      //$this->assertFalse(true, "预期的异常没有抛出!");
    }catch(Exception $actual){

      $this->assertEquals($expected, $actual);
      //print_r($actual);
    }
  }

  /**
   * url地址格式错误测试用例
   * @group enhancement_7
   */
  public function testInvalidUrl(){
    $configFile = dirname(__FILE__) . '/7_invalid_url.xml';

    $errorMsg = '[Config File:'.$configFile.'][Action ID:ticket.Invalid][HTTP Method:get][URL:this/is/an/invalid/url/!@#%/:dfdf:00-_)(/]';
    $expected =  new Exception($errorMsg.'URL不符合格式要求, 必须以/开头, 只能包含正斜杠(/), 英文字母[a-zA-Z], 数字[0-9], 冒号(:), 下划线(_)!', 50702);

    $fixture = new AppXMLCfgLoader();
    try{
      $fixture->load($configFile);
      //$this->assertFalse(true, "预期的异常没有抛出!");
    }catch(Exception $actual){

      $this->assertEquals($expected, $actual);
      //print_r($actual);
    }
  }

  /**
  * RESTFul API地址重复容错测试
  *  @group enhancement_7
  */
  public function testDuplicateUrl(){
    $configFile = dirname(__FILE__) . '/7_duplicate_rest_url.xml';

    $errorMsg = '[Config File:'.$configFile.'][Action ID:ticket.Duplicated][HTTP Method:get][URL:/ticket/id/:ticketId]';
    $expected =  new Exception($errorMsg.'重复定义的URL及Method, 请检查配置文件, 修正或者清理配置信息!', 50703);

    $fixture = new AppXMLCfgLoader();
    try{
      $fixture->load($configFile);
      //$this->assertFalse(true, "预期的异常没有抛出!");
    }catch(Exception $actual){

      $this->assertEquals($expected, $actual);
      //print_r($actual);
    }
  }


  /**
   * TODO: Null Action ID check
   * @group bug_9 cases
   */
  public function testNullActionId(){
    $configFile = dirname(__FILE__) . '/9_null_action_id.xml';

    $errorMsg = 'The "id" attribute is required of the "action" node, '
    . 'please check this issue in the file: ' . $configFile
    . ', line: 8';

    $expected =  new Exception($errorMsg, 50901);

    $fixture = new AppXMLCfgLoader();
    try{
      $fixture->load($configFile);
    }catch(Exception $actual){
      $this->assertEquals($expected, $actual);
      //print_r($actual);
  }
}

   /**
    * TODO: Duplicate Action ID check
    * @group bug_9 cases
    */
  public function testDuplicateActionId(){
    $configFile = dirname(__FILE__) . '/9_duplicate_action_id.xml';

    $errorMsg = 'A duplicated id: ticket.Search'
							.  ' is found, at line 9'
							.  ' in the config file: : ' . $configFile
							. ', please rename or remove the action node!';

    $expected =  new Exception($errorMsg, 50902);

    $fixture = new AppXMLCfgLoader();
    try{
      $fixture->load($configFile);
    }catch(Exception $actual){
      $this->assertEquals($expected, $actual);
      //print_r($actual);
    }
  }
}
