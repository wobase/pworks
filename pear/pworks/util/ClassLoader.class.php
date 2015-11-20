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

/**
 * Class loader for runtime
 * @package pworks.mvc.util
 */
class ClassLoader
{
	/**
	 * @param string classFullName, full name for the class, e.g.: pworks.mvc.util.ClassLoader
	 * @param string src, php soruce file path, must be a relative path to include_path evironment variable, or a absolute path
	 * @return Object, instance of specific class.
	 */
	public static function getInstance($classFullName, $src=null)
	{
		$nameParts = explode(".",$classFullName);
		$className = end($nameParts);
		if($src != null){
			$classPath = $src;
		}else{
			$classPath = implode('/',$nameParts);
			$classPath = $classPath.".class.php";
		}

		if(class_exists($className,false)){
			$obj = new $className();
			return $obj;
		}else{
			if(include $classPath)
			{
				if(class_exists($className,false)){
					$obj = new $className();
					return $obj;
				}else{
					$e = new SystemException(SystemException::PHP_ERROR, "Class $className is not defined");
					throw $e;
				}
			}
			else
			{
				$includePath = get_include_path();
				$e = new SystemException(SystemException::PHP_ERROR, "Source File $classPath could not be found in: $includePath");
				throw $e;
			}
		}
	}
	
	public static function isClassExist($classFullName, $src=null){
		$nameParts = explode(".",$classFullName);
		$className = end($nameParts);
		if(class_exists($className,false)){
			return true;
		}
		
		if($src != null){
			$classPath = $src;
		}else{
			$classPath = implode('/',$nameParts);
			$classPath = $classPath.".class.php";
		}

		if(include $classPath)
		{
			if(class_exists($className,false)){
				return true;
			}else{
				$e = new SystemException(SystemException::PHP_ERROR, "Class $className is not defined");
				throw $e;
			}
		}
		else
		{
			$includePath = get_include_path();
			$e = new SystemException(SystemException::PHP_ERROR, "Source File $classPath could not be found in: $includePath");
			throw $e;
		}
	}
}
