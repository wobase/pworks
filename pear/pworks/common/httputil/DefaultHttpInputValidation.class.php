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

require_once('pworks/common/httputil/IHttpInputValidation.iface.php');
require_once('pworks/common/httputil/HttpConst.class.php');

class DefaultHttpInputValidation implements IHttpInputValidation{
    public static function getDate($dataType, $name, $filter = NULL, $option= NULL){
        $retVal = null;
        switch($dataType){
            case HttpConst::TYPE_GET :{
                global $_GET;
                if(array_key_exists($name, $_GET)){
                    $retVal = $_GET[$name];
                }
                break;
            }
            case HttpConst::TYPE_POST :{
                global $_POST;
                if(array_key_exists($name, $_POST)){
                    $retVal = $_POST[$name];
                }
                break;
            }
            case HttpConst::TYPE_REQUEST :{
                global $_REQUEST;
                if(array_key_exists($name, $_REQUEST)){
                    $retVal = $_REQUEST[$name];
                }
                break;
            }
            case HttpConst::TYPE_SERVER :{
                global $_SERVER;
                if(array_key_exists($name, $_SERVER)){
                    $retVal = $_SERVER[$name];
                }
                break;
            }
            case HttpConst::TYPE_ENV :{
                if(getenv($name)){
                    $retVal = getenv($name);
                }
                break;
            }
        }
        if($retVal == null || $filter == null){
            return $retVal;
        }

        switch($filter){
            case HttpConst::FILTER_EMAIL :
                return self::getEmail($retVal, $option);

            case HttpConst::FILTER_URL :
                return self::getUrl($retVal, $option);

            case HttpConst::FILTER_HTML :
                return self::getHtml($retVal, $option);

            case HttpConst::FILTER_NUMBER :
				$number_flag = false;
				if (is_array($retVal))
				{
					foreach($retVal as $single)
					{
						$number_check = self::getNumber($single, $option);
						if ($number_check != 0 && empty($number_check))
						{
							$number_flag = true;	
							break;
						}
					}
					if($number_flag) {return null; }
					else{ return $retVal;}
				}
				else
				{
					return self::getNumber($retVal, $option);
				}
            case HttpConst::FILTER_POSITIVE_NUMBER :
                return self::getPositiveNumber($retVal, $option);
            case HttpConst::FILTER_STRING_NUMBER :
                return self::getStringNumber($retVal, $option);
            case HttpConst::FILTER_DATETIME:
                return self::getDatetime($retVal, $option);
            case HttpConst::FILTER_DATE:
                return self::getDateCheck($retVal, $option);
        }
        return $retVal;
    }

    public static function getEmail($value, $option=null){
        if(preg_match('/^[a-zA-Z0-9._-]+@[a-zA-Z0-9-]+(\.[a-zA-Z.]{2,5})+$/i', $value)){
            return $value;
        }
        else{
            return null;
        }
    }

    public static function getUrl($value, $option=null){
        if(preg_match('/^(http:\/\/)?([^\/]+)/i', $value)){
            return urlencode($value);
        }
        else{
            return null;
        }
    }

    public static function getHtml($value, $option=null){
        $quoteStyle = ENT_QUOTES;
        $charset = 'UTF-8';
        if(is_array($option)){

            if(array_key_exists(HttpConst::OPTION_QUOTE_STYLE,$option)){
                $quoteStyle = $option[HttpConst::OPTION_QUOTE_STYLE];
            }

            if(array_key_exists(HttpConst::OPTION_CHARTSET,$option)){
                $charset = $option[HttpConst::OPTION_CHARTSET];
            }
        }
        return htmlentities($value,$quoteStyle, $charset);
    }

    public static function getNumber($value, $option=null){
        if(is_numeric($value)){
            return $value;
        }
        else{
            return null;
        }
    }

   public static function getPositiveNumber($value, $option=null){
        if(preg_match('/^[1-9]\d*$/', $value)){
            return $value;
        }
        else{
            return null;
        }
    }

   public static function getStringNumber($value, $option=null){
        #if(preg_match("/^[a-zA-Z]\w*$/", $value)){
        if(preg_match('/^\w*$/', $value)){
            return $value;
        }
        else{
            return null;
        }
    }

   public static function getDatetime($value, $option=null){
	   if (is_array($value)) $value = implode(" ",$value).":00";
        if(preg_match('/^((((1[6-9]|[2-9]\d)\d{2})-(0?[13578]|1[02])-(0?[1-9]|[12]\d|3[01]))|(((1[6-9]|[2-9]\d)\d{2})-(0?[13456789]|1[012])-(0?[1-9]|[12]\d|30))|(((1[6-9]|[2-9]\d)\d{2})-0?2-(0?[1-9]|1\d|2[0-8]))|(((1[6-9]|[2-9]\d)(0[48]|[2468][048]|[13579][26])|((16|[2468][048]|[3579][26])00))-0?2-29-)) (20|21|22|23|[0-1]?\d):[0-5]?\d:[0-5]?\d$/', $value)){
            return $value;
        }
        else{
            return null;
        }
    }

   public static function getDateCheck($value, $option=null){
	   $value = substr($value,0,10);
        if(preg_match('/^((((1[6-9]|[2-9]\d)\d{2})-(0?[13578]|1[02])-(0?[1-9]|[12]\d|3[01]))|(((1[6-9]|[2-9]\d)\d{2})-(0?[13456789]|1[012])-(0?[1-9]|[12]\d|30))|(((1[6-9]|[2-9]\d)\d{2})-0?2-(0?[1-9]|1\d|2[0-8]))|(((1[6-9]|[2-9]\d)(0[48]|[2468][048]|[13579][26])|((16|[2468][048]|[3579][26])00))-0?2-29-))$/',$value)){
            return $value;
        }
        else{
            return null;
        }
    }
}

