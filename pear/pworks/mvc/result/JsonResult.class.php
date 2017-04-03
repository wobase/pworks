<?php
/*
 * Copyright 2011 - 2015 Milo Liu<cutadra@gmail.com>.
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
require_once('pworks/mvc/IResult.iface.php');


/**
 * [2015-12-10][Milo Liu] Added json and utf8 header
 * [2011-12-08][Milo Liu] Add for standard JSON data format
 */
class JsonResult implements IResult {
	public function show(IAction &$action, ResultConfig $config) {

        $result = array();

        // Added by Milo on 1st Apil, 2017
        // added a fixed head member
        if(isset($action->head)){
            $result['head'] = $action->head;
        }

        // head  part
		$result['head']['status'] = $action->getStatus();

var_dump($action->getErrors());

		foreach($action->getErrors() as $errMsgs){
			foreach($errMsgs as $errMsg){
				$result['head']['errors'][] = array(
					"code" => $errMsg->ownerId,
					"message" => $errMsg->content);
			}
		}

		foreach($action->getWarnings() as $warnMsgs){
			foreach($warnMsgs as $warnMsg){
				$result['head']['warnings'][] = array(
					"code" => $warnMsg->ownerId,
					"message" => $warnMsg->content);
			}
		}

		foreach($action->getInfos() as $infoMsgs){
			foreach($infoMsgs as $infoMsg){
				$result['head']['infos'][] = array(
					"code" => $infoMsg->ownerId,
					"message" => $infoMsg->content);
			}
		}
                // body part

		$result['body'] = $action->getData();

		header('Content-type: application/json');
		header('Content-type: text/html; charset=utf-8');

		echo json_encode($result);
	}
}
