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
 * Validate类测试
 */

namespace tests\thinkphp\library\think;

use think\Config;
use think\Validate;

class validateTest extends \PHPUnit_Framework_TestCase
{

    public function testRule()
    {
        Validate::rule('zip', '/^\d{6}$/');
        Validate::rule([
            'currency' => '/^\d+(\.\d+)?$/',
            'number'   => '/^\d+$/',
            'zip'      => '/^\d{6}$/',
        ]);
    }

    public function testCheck()
    {
        $data = [
            'username'   => 'username',
            'nickname'   => 'nickname',
            'password'   => '123456',
            'repassword' => '123456',
            'mobile'     => '13800000000',
            'email'      => 'abc@abc.com',
            'sex'        => '0',
            'age'        => '20',
            'code'       => '1234',
            'test'       => ['a' => 1, 'b' => 2],
        ];

        $validate = [
            '__pattern__' => [
                'mobile'  => '/^1(?:[358]\d|7[6-8])\d{8}$/',
                'require' => '/.+/',
            ],
            '__all__'     => [
                'code' => function ($value, $data) {
                    return '1234' != $value ? 'code error' : true;
                },
            ],
            'user'        => [
                ['username', [ & $this, 'checkName'], '用户名长度为5到15个字符', 'callback', 'username'],
                ['nickname', 'require', '请填昵称'],
                'password'   => ['[\w-]{6,15}', '密码长度为6到15个字符'],
                'repassword' => ['password', '两次密码不一到致', 'confirm'],
                'mobile'     => ['mobile', '手机号错误'],
                'email'      => ['validate_email', '邮箱格式错误', 'filter'],
                'sex'        => ['0,1', '性别只能为为男或女', 'in'],
                'age'        => ['1,80', '年龄只能在10-80之间', 'between'],
                'test.a'     => ['number', 'a必须是数字'],
                'test.b'     => ['1,3', '不能是1或者3', 'notin'],
                '__option__' => [
                    'scene'           => [
                        'add'  => 'username,nickname,password,repassword,mobile,email,age,code',
                        'edit' => 'nickname,password,repassword,mobile,email,sex,age,code',
                    ],
                    'value_validate'  => 'email',
                    'exists_validate' => 'password,repassword,code',
                ],
            ],
        ];
        Config::set('validate', $validate);
        Validate::check($data, 'user.add');
        $this->assertEquals([], Validate::getError());

        unset($data['password'], $data['repassword']);
        $data['email'] = '';
        Validate::check($data, 'user.edit');
        $this->assertEquals([], Validate::getError());

    }

    public function checkName($value, $field)
    {
        switch ($field) {
            case 'username':
                return !empty($value);
            case 'mobile':
                return 13 == strlen($value);
        }
    }

}
