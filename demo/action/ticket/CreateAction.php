<?php
require_once('pworks/mvc/action/BaseAction.class.php');

class CreateAction extends BaseAction{

    public $a;
    public $b;

    public function execute(){

        usleep(675000);

	      // your logic


        // -------------
        // data in head
        //---------------
        // the error will be added into head.errors array
        // $this->addError('errorCode', 'errorMessage');
        // the head.status will be 'fail'
        // return 'fail';


        //-----------------
        // data in the body
        // ----------------
        // $this->setData('field_under_body', $varName);

        // --------------------
        // the head.status will be 'succ'
        return 'succ';
    }
}
