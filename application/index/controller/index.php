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

namespace index\controller;

use think\View;
use org\Slog;

class Index
{

    public function index()
    {
        Slog::log('调试');
        $view = new View();
        return $view->fetch();
    }

    public function hello($name = '')
    {
        echo 'hello,' . $name;
    }

}
