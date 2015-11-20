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

require_once('pworks/common/exception/SystemException.class.php');

class FileUtil{

    /**
     * Make a new directory, if needed, create all non-existent forefather directories.
     *
     * @param string $path
     */
    public static function newPath($path){
        if(is_file($path)){
            throw new SystemException(SystemException::OS_ERROR, "Can not create directory $path, it is an existent file");
        }

        if(is_dir($path)){
            return;
        }

        $parentPath = dirname($path);
        if(!is_dir($parentPath)){
            self::newPath($parentPath);
        }
        mkdir($path);
    }

    /**
     * Make a new empty file, if needed, create all non-existent forefather directories.
     *
     * @param string $path
     */
    public static function newFile($path){
        if(is_dir($path)){
            throw new SystemException(SystemException::OS_ERROR, "Can not create file $path, it is an existent directory");
        }

        if(is_file($path)){
            return;
        }

        $parentPath = dirname($path);
        if(!is_dir($parentPath)){
            self::newPath($parentPath);
        }
        
        $handle = fopen($path,'w');
        fflush($handle);
        fclose($handle);
    }


    /**
     * Delete subtree(all directories and files)
     *
     * @param string $path
     *
     */
    public static function deleteTree($path){
        
    }
}
