<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi.cn@gmail.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace org\transform\driver;

class Json
{
    public function encode($data)
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    public function decode($data, $assoc = true)
    {
        return json_decode($data, $assoc);
    }
}
