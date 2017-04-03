<?php
require_once('pworks/mvc/FrontController.class.php');
require_once('pworks/mvc/DefaultSystemDataHelper.class.php');
require_once('pworks/mvc/CachedConfigHelper.class.php');
require_once('pworks/common/cache/impl/SimpleCacheHelper.class.php');
require_once('pworks/common/cache/impl/ArrayCache.class.php');

class FrontControllerTest extends PHPUnit_Framework_TestCase{

  public function testLoopActionCall(){


    $dataHelper = new DefaultSystemDataHelper();
    $dataHelper->defaultType = SysDataType::REQUEST;
    $dataHelper->set('actionId','restful');
    $dataHelper->set('method', 'GET');
    $dataHelper->set('url', '/ticket');


    $configFile = dirname(__FILE__) . '/9_action_loop_call.xml';

    FrontController::$confHelper = CachedConfigHelper::getInstance()
                                  ->setCacheHelper(
                                    SimpleCacheHelper::getInstance()
                                    ->setGroup('cfg')
                                    ->setCache(new ArrayCache(), 1))
                                  ->init($configFile, false);
    FrontController::$dataHelper = $dataHelper;
    FrontController::$cacheHelper = SimpleCacheHelper::getInstance()
                                    ->setGroup('app')
                                    ->setCache(new ArrayCache(), 1);



    try{
        FrontController::start();
    }catch(Exception $actual){
        $errMsg = "Action[id:restful] had been called more 5 times in a single"
			. " HTTP Request process, there may exist loop call, please check if there"
			. " exist codes using BaseAction::callAction() or FrontController::start()"
			. " methods.";
        $expected = new Exception($errMsg, 50903);

        $this->assertEquals($expected, $actual);
        //print_r($actual);
    }
  }

}
