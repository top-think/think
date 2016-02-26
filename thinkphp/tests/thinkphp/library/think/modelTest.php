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

/**
 * 模板测试
 * @author    Haotong Lin <lofanmi@gmail.com>
 */

namespace tests\thinkphp\library\think;

use think\Model;

class templateTest extends \PHPUnit_Framework_TestCase
{
    public function testValidate()
    {
        $model = new Model();
        $data = [
            'name' => ['a' => 'a', 'b' => 'b'],
            'goods' => [
                0 => [
                    0 => [
                        'item' => 'item',
                        'price' => '100',
                    ],
                    1 => [
                        'item' => 'item2',
                        'price' => '100',
                    ]
                ]
            ]
        ];

        $validate = [
            'name.*' => function($value, $data) {return empty($value) ? 'not empty' : true;},
            'goods.*.*.price' => ['/\d+/', 'mast int'],
        ];
        $result = $model->validate($validate)->create($data);
        $this->assertEquals('', $model->getError());
    }

    public function testFill()
    {
        $model = new Model();
        $data = [
            'name' => ['a' => 'a', 'b' => 'b'],
            'goods' => [
                0 => [
                    0 => [
                        'item' => 'item',
                        'price' => '',
                    ],
                    1 => [
                        'item' => 'item2',
                        'price' => '',
                    ]
                ]
            ]
        ];

        $auto = [
            'name.*' => 'name',
            'goods.*.*.price' => 100,
        ];
        $result = $model->auto($auto)->create($data);
        $data['name']['a'] = $data['name']['b'] = 'name';
        $data['goods'][0][0]['price'] = 100;
        $data['goods'][0][1]['price'] = 100;
        $this->assertEquals($data, $result);
    }
}
