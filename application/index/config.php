<?php
// +----------------------------------------------------------------------
// | TOPThink [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://topthink.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// $Id$

return array(
    'url_route'         =>  true,
        'require_module'    =>  false,
            'require_controll'    =>  true,
            'default_layer'     =>  'action',
            'url_html_suffix'       =>  '.html',
            'url_model' =>  1,
            'base_url'=>'/test/index.php',
    'url_route_rules'   =>   array('a'=>'index/test?b=3','news/:id\d'=>array('index/test','status=1')),
    'pathinfo_depr'     =>  '/',

    'DB_TYPE'=>'mysql',
    'DB_HOST'=>'localhost',
    'DB_NAME'=>'examples',
    'DB_USER'=>'root',
    'DB_PWD'=>'',
    'DB_PORT'=>'3306',
    'DB_PREFIX'=>'think_',
);