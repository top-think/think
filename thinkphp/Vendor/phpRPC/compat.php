<?php
/**********************************************************\
|                                                          |
| The implementation of PHPRPC Protocol 3.0                |
|                                                          |
| compat.php                                               |
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

/* Provides missing functionality for older versions of PHP.
 *
 * Copyright: Ma Bingyao <andot@ujn.edu.cn>
 * Version: 1.5
 * LastModified: Apr 12, 2010
 * This library is free.  You can redistribute it and/or modify it under GPL.
 */

require_once("phprpc_date.php");

if (!function_exists('file_get_contents')) {
    function file_get_contents($filename, $incpath = false, $resource_context = null) {
        if (false === $fh = fopen($filename, 'rb', $incpath)) {
            user_error('file_get_contents() failed to open stream: No such file or directory',
                E_USER_WARNING);
            return false;
        }
        clearstatcache();
        if ($fsize = @filesize($filename)) {
            $data = fread($fh, $fsize);
        }
        else {
            $data = '';
            while (!feof($fh)) {
                $data .= fread($fh, 8192);
            }
        }
        fclose($fh);
        return $data;
    }
}

if (!function_exists('ob_get_clean')) {
    function ob_get_clean() {
        $contents = ob_get_contents();
        if ($contents !== false) ob_end_clean();
        return $contents;
    }
}

/**
3 more bugs found and fixed:
1. failed to work when the gz contained a filename - FIXED
2. failed to work on 64-bit architecture (checksum) - FIXED
3. failed to work when the gz contained a comment - cannot verify.
Returns some errors (not all!) and filename.
*/
function gzdecode($data, &$filename = '', &$error = '', $maxlength = null) {
    $len = strlen($data);
    if ($len < 18 || strcmp(substr($data, 0, 2), "\x1f\x8b")) {
        $error = "Not in GZIP format.";
        return null;  // Not GZIP format (See RFC 1952)
    }
    $method = ord(substr($data, 2, 1));  // Compression method
    $flags  = ord(substr($data, 3, 1));  // Flags
    if ($flags & 31 != $flags) {
        $error = "Reserved bits not allowed.";
        return null;
    }
    // NOTE: $mtime may be negative (PHP integer limitations)
    $mtime = unpack("V", substr($data, 4, 4));
    $mtime = $mtime[1];
    $xfl   = substr($data, 8, 1);
    $os    = substr($data, 8, 1);
    $headerlen = 10;
    $extralen  = 0;
    $extra     = "";
    if ($flags & 4) {
        // 2-byte length prefixed EXTRA data in header
        if ($len - $headerlen - 2 < 8) {
            return false;  // invalid
        }
        $extralen = unpack("v", substr($data, 8, 2));
        $extralen = $extralen[1];
        if ($len - $headerlen - 2 - $extralen < 8) {
            return false;  // invalid
        }
        $extra = substr($data, 10, $extralen);
        $headerlen += 2 + $extralen;
    }
    $filenamelen = 0;
    $filename = "";
    if ($flags & 8) {
        // C-style string
        if ($len - $headerlen - 1 < 8) {
            return false; // invalid
        }
        $filenamelen = strpos(substr($data, $headerlen), chr(0));
        if ($filenamelen === false || $len - $headerlen - $filenamelen - 1 < 8) {
            return false; // invalid
        }
        $filename = substr($data, $headerlen, $filenamelen);
        $headerlen += $filenamelen + 1;
    }
    $commentlen = 0;
    $comment = "";
    if ($flags & 16) {
        // C-style string COMMENT data in header
        if ($len - $headerlen - 1 < 8) {
            return false;    // invalid
        }
        $commentlen = strpos(substr($data, $headerlen), chr(0));
        if ($commentlen === false || $len - $headerlen - $commentlen - 1 < 8) {
            return false;    // Invalid header format
        }
        $comment = substr($data, $headerlen, $commentlen);
        $headerlen += $commentlen + 1;
    }
    $headercrc = "";
    if ($flags & 2) {
        // 2-bytes (lowest order) of CRC32 on header present
        if ($len - $headerlen - 2 < 8) {
            return false;    // invalid
        }
        $calccrc = crc32(substr($data, 0, $headerlen)) & 0xffff;
        $headercrc = unpack("v", substr($data, $headerlen, 2));
        $headercrc = $headercrc[1];
        if ($headercrc != $calccrc) {
            $error = "Header checksum failed.";
            return false;    // Bad header CRC
        }
        $headerlen += 2;
    }
    // GZIP FOOTER
    $datacrc = unpack("V", substr($data, -8, 4));
    $datacrc = sprintf('%u', $datacrc[1] & 0xFFFFFFFF);
    $isize = unpack("V", substr($data, -4));
    $isize = $isize[1];
    // decompression:
    $bodylen = $len - $headerlen - 8;
    if ($bodylen < 1) {
        // IMPLEMENTATION BUG!
        return null;
    }
    $body = substr($data, $headerlen, $bodylen);
    $data = "";
    if ($bodylen > 0) {
        switch ($method) {
        case 8:
            // Currently the only supported compression method:
            $data = gzinflate($body, $maxlength);
            break;
        default:
            $error = "Unknown compression method.";
            return false;
        }
    }  // zero-byte body content is allowed
    // Verifiy CRC32
    $crc   = sprintf("%u", crc32($data));
    $crcOK = $crc == $datacrc;
    $lenOK = $isize == strlen($data);
    if (!$lenOK || !$crcOK) {
        $error = ( $lenOK ? '' : 'Length check FAILED. ') . ( $crcOK ? '' : 'Checksum FAILED.');
        return false;
    }
    return $data;
}

if (version_compare(phpversion(), "5", "<")) {
    function serialize_fix($v) {
        return str_replace('O:11:"phprpc_date":7:{', 'O:11:"PHPRPC_Date":7:{', serialize($v));
    }
}
else {
    function serialize_fix($v) {
        return serialize($v);
    }
}

function declare_empty_class($classname) {
    static $callback = null;
    $classname = preg_replace('/[^a-zA-Z0-9\_]/', '', $classname);
    if ($callback===null) {
        $callback = $classname;
        return;
    }
    if ($callback) {
        call_user_func($callback, $classname);
    }
    if (!class_exists($classname)) {
        if (version_compare(phpversion(), "5", "<")) {
            eval('class ' . $classname . ' { }');
        }
        else {
            eval('
    class ' . $classname . ' {
        private function __get($name) {
            $vars = (array)$this;
            $protected_name = "\0*\0$name";
            $private_name = "\0'.$classname.'\0$name";
            if (array_key_exists($name, $vars)) {
                return $this->$name;
            }
            else if (array_key_exists($protected_name, $vars)) {
                return $vars[$protected_name];
            }
            else if (array_key_exists($private_name, $vars)) {
                return $vars[$private_name];
            }
            else {
                $keys = array_keys($vars);
                $keys = array_values(preg_grep("/^\\\\x00.*?\\\\x00".$name."$/", $keys));
                if (isset($keys[0])) {
                    return $vars[$keys[0]];
                }
                else {
                    return NULL;
                }
            }
        }
    }');
        }
    }
}
declare_empty_class(ini_get('unserialize_callback_func'));
ini_set('unserialize_callback_func', 'declare_empty_class');
?>