<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

Route::get('/', function () {
    return 'hello,ThinkPHP5!';
});

Route::group('hello', function () {
    Route::get(':id', 'index/hello');
    Route::post(':name', 'index/hello');
}, [], ['id' => '\d+', 'name' => '\w+']);

return [

];
