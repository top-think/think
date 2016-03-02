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
 * 模型类测试
 */

namespace tests\thinkphp\library\think;

use think\Model;

class modelTest extends \PHPUnit_Framework_TestCase
{
    public function getConfig()
    {
        $config = [
            'type' => 'mysql',
            'database' => 'test',
            'username' => 'root',
            'password' => '',
        ];
        return $config;
    }

    public function testValidate()
    {
        $model = new Model('', $this->getConfig());
        $data = [
            'username' => 'username',
            'nickname' => 'nickname',
            'password' => '123456',
            'repassword' => '123456',
            'mobile' => '13800000000',
            'email' => 'abc@abc.com',
            'sex' => '0',
            'age' => '20',
            'code' => '1234',
        ];

        $validate = [
            '__pattern__' => [
                'mobile' => '/^1(?:[358]\d|7[6-8])\d{8}$/',
                'require' => '/.+/',
            ],
            '__all__' => [
                'code' => function($value, $data) {return '1234' != $value ? 'code error' : true;},
            ],
            'user' => [
                ['username', [&$this, 'checkName'], '用户名长度为5到15个字符', 'callback', 'username'],
                ['nickname', 'require', '请填昵称'],
                'password' => ['[\w-]{6,15}', '密码长度为6到15个字符'],
                'repassword' => ['password', '两次密码不一到致', 'confirm'],
                'mobile' => ['mobile', '手机号错误'],
                'email' => ['validate_email', '邮箱格式错误', 'filter'],
                'sex' => ['0,1', '性别只能为为男或女', 'in'],
                'age' => ['1,80', '年龄只能在10-80之间', 'between'],
                '__option__' => [
                    'scene' => [
                        'add' => 'username,nickname,password,repassword,mobile,email,age,code',
                        'edit' => 'nickname,password,repassword,mobile,email,sex,age,code',
                    ],
                    'value_validate' => 'email',
                    'exists_validate' => 'password,repassword,code',
                ],
            ],
        ];
        \think\Config::set('validate', $validate);
        $result = $model->validate('user.add')->create($data);
        $this->assertEquals('', $model->getError());

        unset($data['password'], $data['repassword']);
        $data['email'] = '';
        $result = $model->validate('user.edit')->create($data);
        $this->assertEquals('', $model->getError());

        // 测试带.和*的键名
        $data = [
            'code' => '',
            'name' => ['a' => '', 'b' => ''],
            'sku' => [
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
        $test = [
            'code' => function($value, $data) {return empty($value) ? ['code' => 'not empty'] : true;},
            'name.*' => ['/.+/', 'not empty'],
            'sku.*.*.price' => ['/\d+/', 'mast int'],
            '__option__' => [
                'patch' => true,
            ],
        ];
        $result = $model->validate($test)->create($data);
        $msg = [
            'code' => 'not empty',
            'name.a' => 'not empty',
            'name.b' => 'not empty',
            'sku.0.0.price' => 'mast int',
            'sku.0.1.price' => 'mast int',
        ];
        $this->assertEquals($msg, $model->getError());
    }

    public function checkName($value, $field) {
        switch($field) {
            case 'username':
                return !empty($value);
            case 'mobile':
                return 13 == strlen($value);
        }
    }

    public function testFill()
    {
        $model = new Model('', $this->getConfig());
        $data = [
            'username' => '',
            'nickname' => 'nickname',
            'phone' => ' 123456',
            'hobby' => ['1', '2'],
            'cityid' => '1',
        ];
        $auto = [
            'user' => [
                '__option__' => [
                    'value_fill' => ['username', 'password', 'phone'],
                    'exists_fill' => 'nickname',
                ],
                'username' => ['strtolower', 'callback'],
                'password' => ['md5', 'callback'],
                'nickname' => [[&$this, 'fillName'], 'callback', 'cn_'],
                'phone' => function($value, $data) {return trim($value);},
                'hobby' => ['', 'serialize'],
                'cityid' => ['1', 'ignore'] ,
                'address' => ['address'],
                'integral' => 0,
                ['reg_time', 'time', 'callback'],
                ['login_time', function($value, $data) {return $data['reg_time'];}],
            ],
        ];
        \think\Config::set('auto', $auto);
        $result = $model->auto('user')->create($data);
        $data['nickname'] = 'cn_nickname';
        $data['phone'] = '123456';
        $data['hobby'] = serialize($data['hobby']);
        $data['address'] = 'address';
        $data['integral'] = 0;
        $data['reg_time'] = time();
        $data['login_time'] = $data['reg_time'];
        unset($data['cityid']);
        $this->assertEquals($data, $result);

        // 测试带.和*的键名
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
        $test = [
            'name.*' => 'name',
            'goods.*.*.price' => 100,
        ];
        $result = $model->auto($test)->create($data);
        $data['name']['a'] = $data['name']['b'] = 'name';
        $data['goods'][0][0]['price'] = 100;
        $data['goods'][0][1]['price'] = 100;
        $this->assertEquals($data, $result);
    }

    public function fillName($value, $prefix) {
        return $prefix . trim($value);
    }
}
