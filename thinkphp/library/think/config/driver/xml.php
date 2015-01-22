<?php
// +----------------------------------------------------------------------
// | TOPThink [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://topthink.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace Think\Config\Driver;

class Xml {
    public function parse($config){
        if(is_file($config)) {
            $content    =   simplexml_load_file($config);
        }else{
            $content    =   simplexml_load_string($config);
        }
        $result =   (array)$content;
        foreach($result as $key=>$val){
            if(is_object($val)) {
                $result[$key]   =   (array)$val;
            }
        }
        return $result;
    }
}
