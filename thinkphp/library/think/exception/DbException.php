<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://zjzit.cn>
// +----------------------------------------------------------------------

namespace think\exception;

use think\Exception;

/**
 * Database相关异常处理类
 */
class DbException extends Exception 
{
    public function __construct($message, $code, $db, $config)
    {
        $this->message  = $message;
        $this->code     = $code;

        $error = explode(':', $db->getError());
        $this->setData('Database Status', [
            'Error Code'    => $error[0],
            'Error Message' => $error[1],
            'Error SQL'     => $db->getLastSql()
        ]);

        $this->setData('Database Config', $config);
    }
}
