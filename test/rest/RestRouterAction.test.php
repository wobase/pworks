<?php
require_once('pworks/mvc/action/RestRouterAction.class.php');

require_once('pworks/mvc/AppXMLCfgLoader.class.php');


class RestRouterActionTest extends PHPUnit_Framework_TestCase{

/**
 * Smoking Case
 * @group enhancement_7
 */
  public function testMatchActionNormal(){
    $configFile = dirname(__FILE__) . '/7_RestApi.xml';
    $loader = new AppXMLCfgLoader();
    $loader->load($configFile);
    $configs = $loader->getAppConfig();
    $restConfigs = $configs->rest;

    // Request following urls with get, post, put, and delete
    $requestUrls = array(
      '/ticket/id/123',
      '/ticket/id/123/comment/id/13',
      '/ticket/id/123/comment',
      '/ticket',
      '/id/123/comment/456',
      'ticket/id/123'
    );


    $expected = array();
    // '/ticket/id/123',
    $expected[] = array('action' => 'ticket.GetDetails', 'param' => array('ticketId'=> 123)); //get
    $expected[] = 'null'; //post
    $expected[] = array('action' => 'ticket.Update', 'param' => array('ticketId'=> 123)); // put
    $expected[] = array('action' => 'ticket.Delete', 'param' => array('ticketId'=> 123)); // delete

    //'/ticket/id/123/comment/id/13',
    $expected[] = 'null'; //get
    $expected[] = 'null'; //post
    $expected[] = array('action' => 'ticket.comment.Update', 'param' => array('ticketId'=> 123, 'commentId' => 13)); // put
    $expected[] = array('action' => 'ticket.comment.Delete', 'param' => array('ticketId'=> 123, 'commentId' => 13)); // delete

    // '/ticket/id/123/comment',
    $expected[] = array('action' => 'ticket.comment.Search', 'param' => array('ticketId'=> 123)); // get
    $expected[] = array('action' => 'ticket.comment.Create', 'param' => array('ticketId'=> 123)); // post
    $expected[] = 'null'; //put
    $expected[] = 'null'; //delete

    // '/ticket',
    $expected[] = array('action' => 'ticket.Search', 'param' => array()); // get
    $expected[] = array('action' => 'ticket.Create', 'param' => array()); // post
    $expected[] = 'null'; //put
    $expected[] = 'null'; //delete

    // '/id/123/comment/456',
    $expected[] = 'null'; //get
    $expected[] = 'null'; //post
    $expected[] = 'null'; //put
    $expected[] = 'null'; //delete

    // 'ticket/id/123'
    $expected[] = 'null'; //get
    $expected[] = 'null'; //post
    $expected[] = 'null'; //put
    $expected[] = 'null'; //delete

    $methods = array('get', 'post', 'put', 'delete');

    $fixture = new RestRouterAction();

    $actuals = array();
    foreach($requestUrls as $url){
      foreach($methods as $method){
        $rs = $fixture->matchAction($restConfigs[$method], $url);
        if(null === $rs){
          $actuals[] = 'null';
        }else{
          $actuals[] = array('action' => $rs['action']->id, 'param'=>$rs['param'] );
        }
      }
    }

    $this->assertEquals($expected, $actuals);
  }

}
