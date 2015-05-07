<?php
/**********************************************************\
|                                                          |
| The implementation of PHPRPC Protocol 3.0                |
|                                                          |
| dhparams.php                                             |
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

/* Diffie-Hellman Parameters for PHPRPC.
 *
 * Copyright: Ma Bingyao <andot@ujn.edu.cn>
 * Version: 1.2
 * LastModified: Apr 12, 2010
 * This library is free.  You can redistribute it and/or modify it under GPL.
 */
class DHParams {
    var $len;
    var $dhParams;
    function getNearest($n, $a) {
        $j = 0;
        $m = abs($a[0] - $n);
        for ($i = 1; $i < count($a); $i++) {
            $t = abs($a[$i] - $n);
            if ($m > $t) {
                $m = $t;
                $j = $i;
            }
        }
        return $a[$j];
    }
    function DHParams($len = 128) {
        if (extension_loaded('gmp')) {
            $a = array(96, 128, 160, 192, 256, 512, 768, 1024, 1536, 2048, 3072, 4096);
        }
        else if (extension_loaded('big_int')) {
            $a = array(96, 128, 160, 192, 256, 512, 768, 1024, 1536);
        }
        else if (extension_loaded('bcmath')) {
            $a = array(96, 128, 160, 192, 256, 512);
        }
        else {
            $a = array(96, 128, 160);
        }
        $this->len = $this->getNearest($len, $a);
        $dhParams = unserialize(file_get_contents("dhparams/{$this->len}.dhp", true));
        $this->dhParams = $dhParams[mt_rand(0, count($dhParams) - 1)];
    }
    function getL() {
        return $this->len;
    }
    function getP() {
        return $this->dhParams['p'];
    }
    function getG() {
        return $this->dhParams['g'];
    }
    function getDHParams() {
        return $this->dhParams;
    }
}
?>