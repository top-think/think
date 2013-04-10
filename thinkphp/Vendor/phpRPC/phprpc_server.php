<?php
/**********************************************************\
|                                                          |
| The implementation of PHPRPC Protocol 3.0                |
|                                                          |
| phprpc_server.php                                        |
|                                                          |
| Release 3.0.1                                            |
| Copyright by Team-PHPRPC                                 |
|                                                          |
| WebSite:  http://www.phprpc.org/                         |
|           http://www.phprpc.net/                         |
|           http://www.phprpc.com/                         |
|           http://sourceforge.net/projects/php-rpc/       |
|                                                          |
| Authors:  Ma Bingyao <andot@ujn.edu.cn>                  |
|                                                          |
| This file may be distributed and/or modified under the   |
| terms of the GNU General Public License (GPL) version    |
| 2.0 as published by the Free Software Foundation and     |
| appearing in the included file LICENSE.                  |
|                                                          |
\**********************************************************/

/* PHPRPC Server for PHP.
 *
 * Copyright: Ma Bingyao <andot@ujn.edu.cn>
 * Version: 3.0
 * LastModified: Apr 12, 2010
 * This library is free.  You can redistribute it and/or modify it under GPL.
 *
/*
 * Interfaces
 *
 * function add($a, $b) {
 *     return $a + $b;
 * }
 * function sub($a, $b) {
 *     return $a - $b;
 * }
 * function inc(&$n) {
 *     return $n++;
 * }
 * include('phprpc_server.php');
 * $server = new PHPRPC_Server();
 * $server->add(array('add', 'sub'));
 * $server->add('inc');
 * $server->setCharset('UTF-8');
 * $server->setDebugMode(true);
 * $server->start();
 *
 */

class PHPRPC_Server {
    var $callback;
    var $charset;
    var $encode;
    var $ref;
    var $encrypt;
    var $enableGZIP;
    var $debug;
    var $keylen;
    var $key;
    var $errno;
    var $errstr;
    var $functions;
    var $cid;
    var $buffer;
    // Private Methods
    function addJsSlashes($str, $flag) {
        if ($flag) {
            $str = addcslashes($str, "\0..\006\010..\012\014..\037\042\047\134\177..\377");
        }
        else {
            $str = addcslashes($str, "\0..\006\010..\012\014..\037\042\047\134\177");
        }
        return str_replace(array(chr(7), chr(11)), array('\007', '\013'), $str);
    }
    function encodeString($str, $flag = true) {
        if ($this->encode) {
            return base64_encode($str);
        }
        else {
            return $this->addJsSlashes($str, $flag);
        }
    }
    function encryptString($str, $level) {
        if ($this->encrypt >= $level) {
            $str = xxtea_encrypt($str, $this->key);
        }
        return $str;
    }
    function decryptString($str, $level) {
        if ($this->encrypt >= $level) {
            $str = xxtea_decrypt($str, $this->key);
        }
        return $str;
    }
    function sendHeader() {
        header("HTTP/1.1 200 OK");
        header("Content-Type: text/plain; charset={$this->charset}");
        header("X-Powered-By: PHPRPC Server/3.0");
        header('P3P: CP="CAO DSP COR CUR ADM DEV TAI PSA PSD IVAi IVDi CONi TELo OTPi OUR DELi SAMi OTRi UNRi PUBi IND PHY ONL UNI PUR FIN COM NAV INT DEM CNT STA POL HEA PRE GOV"');
        header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    }
    function getRequestURL() {
        if (!isset($_SERVER['HTTPS']) ||
            $_SERVER['HTTPS'] == 'off'  ||
            $_SERVER['HTTPS'] == '') {
            $scheme = 'http';
        }
        else {
            $scheme = 'https';
        }
        $host = $_SERVER['SERVER_NAME'];
        $port = $_SERVER['SERVER_PORT'];
        $path = $_SERVER['SCRIPT_NAME'];
        return $scheme . '://' . $host . (($port == 80) ? '' : ':' . $port) . $path;
    }
    function sendURL() {
        if (SID != "") {
            $url = $this->getRequestURL();
            if (count($_GET) > 0) {
                $url .= '?' . strip_tags(SID);
                foreach ($_GET as $key => $value) {
                    if (strpos(strtolower($key), 'phprpc_') !== 0) {
                        $url .= '&' . $key . '=' . urlencode($value);
                    }
                }
            }
            $this->buffer .= "phprpc_url=\"" . $this->encodeString($url) . "\";\r\n";
        }
    }
    function gzip($buffer) {
        $len = strlen($buffer);
        if ($this->enableGZIP && strstr($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip,deflate')) {
            $gzbuffer = gzencode($buffer);
            $gzlen = strlen($gzbuffer);
            if ($len > $gzlen) {
                header("Content-Length: $gzlen");
                header("Content-Encoding: gzip");
                return $gzbuffer;
            }
        }
        header("Content-Length: $len");
        return $buffer;
    }
    function sendCallback() {
        $this->buffer .= $this->callback;
        echo $this->gzip($this->buffer);
        ob_end_flush();
        restore_error_handler();
        if (function_exists('restore_exception_handler')) {
            restore_exception_handler();
        }
        exit();
    }
    function sendFunctions() {
        $this->buffer .= "phprpc_functions=\"" . $this->encodeString(serialize_fix(array_keys($this->functions))) . "\";\r\n";
        $this->sendCallback();
    }
    function sendOutput($output) {
        if ($this->encrypt >= 3) {
            $this->buffer .= "phprpc_output=\"" . $this->encodeString(xxtea_encrypt($output, $this->key)) . "\";\r\n";
        }
        else {
            $this->buffer .= "phprpc_output=\"" . $this->encodeString($output, false) . "\";\r\n";
        }
    }
    function sendError($output = NULL) {
        if (is_null($output)) {
            $output = ob_get_clean();
        }
        $this->buffer .= "phprpc_errno=\"{$this->errno}\";\r\n";
        $this->buffer .= "phprpc_errstr=\"" . $this->encodeString($this->errstr, false) . "\";\r\n";
        $this->sendOutput($output);
        $this->sendCallback();
    }
    function fatalErrorHandler($buffer) {
        if (preg_match('/<b>(.*?) error<\/b>:(.*?)<br/', $buffer, $match)) {
            if ($match[1] == 'Fatal') {
                $errno = E_ERROR;
            }
            else {
                $errno = E_COMPILE_ERROR;
            }
            if ($this->debug) {
                $errstr = preg_replace('/<.*?>/', '', $match[2]);
            }
            else {
                $errstr = preg_replace('/ in <b>.*<\/b>$/', '', $match[2]);
            }

            $buffer = "phprpc_errno=\"{$errno}\";\r\n" .
                      "phprpc_errstr=\"" . $this->encodeString(trim($errstr), false) . "\";\r\n" .
                      "phprpc_output=\"\";\r\n" .
                       $this->callback;
            $buffer = $this->gzip($buffer);
        }
        return $buffer;
    }
    function errorHandler($errno, $errstr, $errfile, $errline) {
        if ($this->debug) {
            $errstr .= " in $errfile on line $errline";
        }
        if (($errno == E_ERROR) or ($errno == E_CORE_ERROR) or
            ($errno == E_COMPILE_ERROR) or ($errno == E_USER_ERROR)) {
            $this->errno = $errno;
            $this->errstr = $errstr;
            $this->sendError();
        }
        else {
            if (($errno == E_NOTICE) or ($errno == E_USER_NOTICE)) {
                if ($this->errno == 0) {
                    $this->errno = $errno;
                    $this->errstr = $errstr;
                }
            }
            else {
                if (($this->errno == 0) or
                    ($this->errno == E_NOTICE) or
                    ($this->errno == E_USER_NOTICE)) {
                    $this->errno = $errno;
                    $this->errstr = $errstr;
                }
            }
        }
        return true;
    }
    function exceptionHandler($exception) {
        $this->errno = $exception->getCode();
        $this->errstr = $exception->getMessage();
        if ($this->debug) {
            $this->errstr .= "\nfile: " . $exception->getFile() .
                             "\nline: " . $exception->getLine() .
                             "\ntrace: " . $exception->getTraceAsString();
        }
        $this->sendError();
    }
    function initErrorHandler() {
        $this->errno = 0;
        $this->errstr = "";
        set_error_handler(array(&$this, 'errorHandler'));
        if (function_exists('set_exception_handler')) {
            set_exception_handler(array(&$this, 'exceptionHandler'));
        }
    }
    function call($function, &$args) {
        if ($this->ref) {
            $arguments = array();
            for ($i = 0; $i < count($args); $i++) {
                $arguments[$i] = &$args[$i];
            }
        }
        else {
            $arguments = $args;
        }
        return call_user_func_array($function, $arguments);
    }
    function getRequest($name) {
        $result = $_REQUEST[$name];
        if (get_magic_quotes_gpc()) {
            $result = stripslashes($result);
        }
        return $result;
    }
    function getBooleanRequest($name) {
        $var = true;
        if (isset($_REQUEST[$name])) {
            $var = strtolower($this->getRequest($name));
            if ($var == "false") {
                $var = false;
            }
        }
        return $var;
    }
    function initEncode() {
        $this->encode = $this->getBooleanRequest('phprpc_encode');
    }
    function initRef() {
        $this->ref = $this->getBooleanRequest('phprpc_ref');
    }
    function initCallback() {
        if (isset($_REQUEST['phprpc_callback'])) {
            $this->callback = base64_decode($this->getRequest('phprpc_callback'));
        }
        else {
            $this->callback = "";
        }
    }
    function initKeylen() {
        if (isset($_REQUEST['phprpc_keylen'])) {
            $this->keylen = (int)$this->getRequest('phprpc_keylen');
        }
        else if (isset($_SESSION[$this->cid])) {
            $session = unserialize(base64_decode($_SESSION[$this->cid]));
            if (isset($session['keylen'])) {
                $this->keylen = $session['keylen'];                
            }
            else {
                $this->keylen = 128;
            }
        }
        else {
            $this->keylen = 128;
        }
    }
    function initClientID() {
        $this->cid = 0;
        if (isset($_REQUEST['phprpc_id'])) {
            $this->cid = $this->getRequest('phprpc_id');
        }
        $this->cid = "phprpc_" . $this->cid;
    }
    function initEncrypt() {
        $this->encrypt = false;
        if (isset($_REQUEST['phprpc_encrypt'])) {
            $this->encrypt = $this->getRequest('phprpc_encrypt');
            if ($this->encrypt === "true") $this->encrypt = true;
            if ($this->encrypt === "false") $this->encrypt = false;
        }
    }
    function initKey() {
        if ($this->encrypt == 0) {
            return;
        }
        else if (isset($_SESSION[$this->cid])) {
            $session = unserialize(base64_decode($_SESSION[$this->cid]));
            if (isset($session['key'])) {
                $this->key = $session['key'];
                require_once('xxtea.php');
                return;
            }
        }
        $this->errno = E_ERROR;
        $this->errstr = "Can't find the key for decryption.";
        $this->encrypt = 0;
        $this->sendError();
    }
    function getArguments() {
        if (isset($_REQUEST['phprpc_args'])) {
            $arguments = unserialize($this->decryptString(base64_decode($this->getRequest('phprpc_args')), 1));
            ksort($arguments);
        }
        else {
            $arguments = array();
        }
        return $arguments;
    }
    function callFunction() {
        $this->initKey();
        $function = strtolower($this->getRequest('phprpc_func'));
        if (array_key_exists($function, $this->functions)) {
            $function = $this->functions[$function];
            $arguments = $this->getArguments();
            $result = $this->encodeString($this->encryptString(serialize_fix($this->call($function, $arguments)), 2));
            $output = ob_get_clean();
            $this->buffer .= "phprpc_result=\"$result\";\r\n";
            if ($this->ref) {
                $arguments = $this->encodeString($this->encryptString(serialize_fix($arguments), 1));
                $this->buffer .= "phprpc_args=\"$arguments\";\r\n";
            }
        }
        else {
            $this->errno = E_ERROR;
            $this->errstr = "Can't find this function $function().";
            $output = ob_get_clean();
        }
        $this->sendError($output);
    }
    function keyExchange() {
        require_once('bigint.php');
        $this->initKeylen();
        if (isset($_SESSION[$this->cid])) {
            $session = unserialize(base64_decode($_SESSION[$this->cid]));
        }
        else {
            $session = array();
        }        
        if ($this->encrypt === true) {
            require_once('dhparams.php');
            $DHParams = new DHParams($this->keylen);
            $this->keylen = $DHParams->getL();
            $encrypt = $DHParams->getDHParams();
            $x = bigint_random($this->keylen - 1, true);
            $session['x'] = bigint_num2dec($x);
            $session['p'] = $encrypt['p'];
            $session['keylen'] = $this->keylen;
            $encrypt['y'] = bigint_num2dec(bigint_powmod(bigint_dec2num($encrypt['g']), $x, bigint_dec2num($encrypt['p'])));
            $this->buffer .= "phprpc_encrypt=\"" . $this->encodeString(serialize_fix($encrypt)) . "\";\r\n";
            if ($this->keylen != 128) {
                $this->buffer .= "phprpc_keylen=\"{$this->keylen}\";\r\n";
            }
            $this->sendURL();
        }
        else {
            $y = bigint_dec2num($this->encrypt);
            $x = bigint_dec2num($session['x']);
            $p = bigint_dec2num($session['p']);
            $key = bigint_powmod($y, $x, $p);
            if ($this->keylen == 128) {
                $key = bigint_num2str($key);
            }
            else {
                $key = pack('H*', md5(bigint_num2dec($key)));
            }
            $session['key'] = str_pad($key, 16, "\0", STR_PAD_LEFT);
        }
        $_SESSION[$this->cid] = base64_encode(serialize($session));
        $this->sendCallback();
    }
    function initSession() {
        @ob_start();
        ob_implicit_flush(0);
        session_start();
    }
    function initOutputBuffer() {
        @ob_start(array(&$this, "fatalErrorHandler"));
        ob_implicit_flush(0);
        $this->buffer = "";
    }
    // Public Methods
    function PHPRPC_Server() {
        require_once('compat.php');
        $this->functions = array();
        $this->charset = 'UTF-8';
        $this->debug = false;
        $this->enableGZIP = false;
    }
    function add($functions, $obj = NULL, $aliases = NULL) {
        if (is_null($functions) || (gettype($functions) != gettype($aliases) && !is_null($aliases))) {
            return false;
        }
        if (is_object($functions)) {
            $obj = $functions;
            $functions = get_class_methods(get_class($obj));
            $aliases = $functions;
        }
        if (is_null($aliases)) {
            $aliases = $functions;
        }
        if (is_string($functions)) {
            if (is_null($obj)) {
                $this->functions[strtolower($aliases)] = $functions;
            }
            else if (is_object($obj)) {
                $this->functions[strtolower($aliases)] = array(&$obj, $functions);
            }
            else if (is_string($obj)) {
                $this->functions[strtolower($aliases)] = array($obj, $functions);
            }
        }
        else {
            if (count($functions) != count($aliases)) {
                return false;
            }
            foreach ($functions as $key => $function) {
                $this->add($function, $obj, $aliases[$key]);
            }
        }
        return true;
    }
    function setCharset($charset) {
        $this->charset = $charset;
    }
    function setDebugMode($debug) {
        $this->debug = $debug;
    }
    function setEnableGZIP($enableGZIP) {
        $this->enableGZIP = $enableGZIP;
    }
    function start() {
        while(ob_get_length() !== false) @ob_end_clean();
        $this->initOutputBuffer();
        $this->sendHeader();
        $this->initErrorHandler();
        $this->initEncode();
        $this->initCallback();
        $this->initRef();
        $this->initClientID();
        $this->initEncrypt();
        if (isset($_REQUEST['phprpc_func'])) {
            $this->callFunction();
        }
        else if ($this->encrypt != false) {
            $this->keyExchange();
        }
        else {
            $this->sendFunctions();
        }
    }
}

PHPRPC_Server::initSession();
?>