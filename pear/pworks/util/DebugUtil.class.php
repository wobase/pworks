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

class DebugUtil{

    const GRADE_START= '<table border="1"><tr><td>';
    const GRADE_END= '</td></tr></table>';

    public static $enable = false;

    public static function dump(&$var, $file, $line, $format='HTML'){
        if(self::$enable){
            if(strtoupper($format) == 'TEXT'){
                self::textDump($var, $file, $line);
            }else{
                self::htmlDump($var, $file, $line);
            }
        }
    }

    public static function point($file, $line, $format='HTML'){
        if(self::$enable){
            if(strtoupper($format) == 'TEXT'){
                self::textPoint($file, $line);
            }else{
                self::htmlPoint($file, $line);
            }
        }
    }

    private static function htmlPoint($file, $line){
        if(self::$enable){
            echo "<p>[$file][$line]</p>";
        }
    }

    private static function textPoint($file, $line){
        if(self::$enable){
            echo "\n[$file][$line]\n\n";
        }
    }

    private static function htmlDump(&$var, $file, $line){
        if(self::$enable){
            echo "<h3>[$file][$line]</h3>";
            echo "<pre>";
            var_dump($var);
            echo "</pre>";
        }
    }

    private static function textDump(&$var, $file, $line){
        if(self::$enable){
            echo "\n[$file][$line]\n";
            var_dump($var);

            echo "\n\n";
        }
    }
}
