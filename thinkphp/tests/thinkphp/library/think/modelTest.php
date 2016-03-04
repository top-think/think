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
            'connection' => [
                'type'     => 'mysql',
                'database' => 'test',
                'username' => 'root',
                'password' => '',
            ],
            'prefix' => 'tp_',
        ];
        return $config;
    }

    public function testValidate()
    {
        $model = new Model('', $this->getConfig());
        $data  = [
            'username'   => 'username',
            'nickname'   => 'nickname',
            'password'   => '123456',
            'repassword' => '123456',
            'mobile'     => '13800000000',
            'email'      => 'abc@abc.com',
            'sex'        => '0',
            'age'        => '20',
            'code'       => '1234',
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
                ['username', [&$this, 'checkName'], '用户名长度为5到15个字符', 'callback', 'username'],
                ['nickname', 'require', '请填昵称'],
                'password'   => ['[\w-]{6,15}', '密码长度为6到15个字符'],
                'repassword' => ['password', '两次密码不一到致', 'confirm'],
                'mobile'     => ['mobile', '手机号错误'],
                'email'      => ['validate_email', '邮箱格式错误', 'filter'],
                'sex'        => ['0,1', '性别只能为为男或女', 'in'],
                'age'        => ['1,80', '年龄只能在10-80之间', 'between'],
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
        \think\Config::set('validate', $validate);
        $result = $model->validate('user.add')->create($data);
        $this->assertEquals('', $model->getError());

        unset($data['password'], $data['repassword']);
        $data['email'] = '';
        $result        = $model->validate('user.edit')->create($data);
        $this->assertEquals('', $model->getError());

        // 测试带.和*的键名
        $data   = [
            'code' => '',
            'name' => ['a' => '', 'b' => ''],
            'sku'  => [
                0 => [
                    0 => [
                        'item'  => 'item',
                        'price' => '',
                    ],
                    1 => [
                        'item'  => 'item2',
                        'price' => '',
                    ],
                ],
            ],
        ];
        $test   = [
            'code'          => function ($value, $data) {
                return empty($value) ? ['code' => 'not empty'] : true;
            },
            'name.*'        => ['/.+/', 'not empty'],
            'sku.*.*.price' => ['/\d+/', 'mast int'],
            '__option__'    => [
                'patch' => true,
            ],
        ];
        $result = $model->validate($test)->create($data);
        $msg    = [
            'code'          => 'not empty',
            'name.a'        => 'not empty',
            'name.b'        => 'not empty',
            'sku.0.0.price' => 'mast int',
            'sku.0.1.price' => 'mast int',
        ];
        $this->assertEquals($msg, $model->getError());
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

    public function testFill()
    {
        $model = new Model('', $this->getConfig());
        $data  = [
            'username' => '',
            'nickname' => 'nickname',
            'phone'    => ' 123456',
            'hobby'    => ['1', '2'],
            'cityid'   => '1',
        ];
        $auto  = [
            'user' => [
                '__option__' => [
                    'value_fill'  => ['username', 'password', 'phone'],
                    'exists_fill' => 'nickname',
                ],
                'username'   => ['strtolower', 'callback'],
                'password'   => ['md5', 'callback'],
                'nickname'   => [[&$this, 'fillName'], 'callback', 'cn_'],
                'phone'      => function ($value, $data) {
                    return trim($value);
                },
                'hobby'      => ['', 'serialize'],
                'cityid'     => ['1', 'ignore'],
                'address'    => ['address'],
                'integral'   => 0,
                ['reg_time', 'time', 'callback'],
                ['login_time', function ($value, $data) {
                    return $data['reg_time'];
                }],
            ],
        ];
        \think\Config::set('auto', $auto);
        $result             = $model->auto('user')->create($data);
        $data['nickname']   = 'cn_nickname';
        $data['phone']      = '123456';
        $data['hobby']      = serialize($data['hobby']);
        $data['address']    = 'address';
        $data['integral']   = 0;
        $data['reg_time']   = time();
        $data['login_time'] = $data['reg_time'];
        unset($data['cityid']);
        $this->assertEquals($data, $result);

        // 测试带.和*的键名
        $data                         = [
            'name'  => ['a' => 'a', 'b' => 'b'],
            'goods' => [
                0 => [
                    0 => [
                        'item'  => 'item',
                        'price' => '',
                    ],
                    1 => [
                        'item'  => 'item2',
                        'price' => '',
                    ],
                ],
            ],
        ];
        $test                         = [
            'name.*'          => 'name',
            'goods.*.*.price' => 100,
        ];
        $result                       = $model->auto($test)->create($data);
        $data['name']['a']            = $data['name']['b'] = 'name';
        $data['goods'][0][0]['price'] = 100;
        $data['goods'][0][1]['price'] = 100;
        $this->assertEquals($data, $result);
    }

    public function fillName($value, $prefix)
    {
        return $prefix . trim($value);
    }

    public function testExecute()
    {
        $sql = <<<EOF
DROP TABLE IF EXISTS `tp_user`;
CREATE TABLE `tp_user` (
  `id` int(10) unsigned NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `username` char(40) NOT NULL DEFAULT '' COMMENT '用户名',
  `password` char(40) NOT NULL DEFAULT '' COMMENT '密码',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间'
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='会员表';

DROP TABLE IF EXISTS `tp_order`;
CREATE TABLE `tp_order` (
  `id` int(10) unsigned NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `sn` char(20) NOT NULL DEFAULT '' COMMENT '订单号',
  `amount` decimal(10,2) unsigned NOT NULL DEFAULT '0' COMMENT '金额',
  `freight_fee` decimal(10,2) unsigned NOT NULL DEFAULT '0' COMMENT '运费',
  `address_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '地址id',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间'
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='订单表';

DROP TABLE IF EXISTS `tp_user_address`;
CREATE TABLE `tp_user_address` (
  `id` int(10) unsigned NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `consignee` varchar(60) NOT NULL DEFAULT '' COMMENT '收货人',
  `area_info` varchar(50) NOT NULL DEFAULT '' COMMENT '地区信息',
  `city_id` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '城市id',
  `area_id` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '地区id',
  `address` varchar(120) NOT NULL DEFAULT '' COMMENT '地址',
  `tel` varchar(60) NOT NULL DEFAULT '' COMMENT '电话',
  `mobile` varchar(60) NOT NULL DEFAULT '' COMMENT '手机',
  `isdefault` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否默认'
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='地址表';

DROP TABLE IF EXISTS `tp_role_user`;
CREATE TABLE `tp_role_user` (
  `role_id` smallint(5) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
EOF;

        $model = new Model('', $this->getConfig());
        $model->execute($sql);
    }

    public function testAdd()
    {
        $config = $this->getConfig();
        $time = time();

        $user_model = new Model('user', $config);
        $data = [
            'username' => 'test',
            'password' => md5('123456'),
            'status' => 1,
            'create_time' => $time,
        ];
        $user_id = $user_model->add($data);
        $data = [
            'username' => 'test2',
            'password' => md5('000000'),
            'status' => 1,
            'create_time' => $time,
        ];
        $user_model->add($data, true);

        $data = [
            [
                'user_id' => $user_id,
                'consignee' => '张三',
                'area_info' => '广东深圳',
                'city_id' => '42',
                'area_id' => '111',
                'address' => 'xx路xx号',
                'mobile' => '1380000000000',
                'isdefault' => '1',
            ],
            [
                'user_id' => $user_id,
                'consignee' => '李四',
                'area_info' => '广东深圳',
                'city_id' => '42',
                'area_id' => '111',
                'address' => 'xx路xx号',
                'mobile' => '13999999999',
                'isdefault' => '0',
            ],
        ];
        $address_model = new Model('user_address', $config);
        $address_id = $address_model->addAll($data, [], true);

        $data = [
            [
                'user_id' => $user_id,
                'sn' => '10001',
                'amount' => '200',
                'freight_fee' => '10',
                'address_id' => $address_id - 1,
                'status' => '1',
                'create_time' => $time,
            ],
            [
                'user_id' => $user_id,
                'sn' => '10002',
                'amount' => '350',
                'freight_fee' => '10',
                'address_id' => $address_id,
                'status' => '0',
                'create_time' => $time,
            ],
        ];
        $address_model = new Model('order', $config);
        $address_model->addAll($data, [], true);

        $data = [
            'user_id' => $user_id,
            'role_id' => 1,
        ];
        $model = new Model('', $config);
        $model->table($config['prefix'] . 'role_user')->add($data);
    }

    public function testQuery()
    {
        $user_model = new Model('user', $this->getConfig());

        $sql = "select id,create_time from tp_user where username='test' limit 1";
        $result = $user_model->query($sql);
        $id = $result[0]['id'];
        $time = $result[0]['create_time'];
        $info = $user_model->where('create_time=' . $time)->where(['status' => 1])->field(true)->find(['cache' => ['key' => true]]);
        $data = [
            'id' => $id,
            'username' => 'test',
            'password' => md5('123456'),
            'status' => '1',
            'create_time' => $time,
        ];
        $this->assertEquals($data, $info);

        $result = $user_model->where(['id' => $id])->field('password,create_time', true)->order('id')->limit('0,10')->select(['cache' => ['key' => true, 'expire' => 0], 'index' => 'username']);
        $data = [
            'id' => $id,
            'username' => 'test',
            'status' => '1',
        ];
        $this->assertEquals($data, $result['test']);

        $time = $user_model->where(['status'=>1])->getField('create_time');
        $ids = $user_model->where(['status'=>1])->getField('id', true);
        $this->assertEquals(2, count($ids));
        $result = $user_model->getField('username,status,create_time', '|');
        $data = [
            'test' => '1|' . $time,
            'test2' => '1|' . $time,
        ];
        $this->assertEquals($data, $result);
    }

    public function testJoin()
    {
        $config = $this->getConfig();
        $user_model = new Model('user', $config);

        $join = [
            [['order o', 'tp_'], 'u.id=o.user_id'],
            [['user_address' => 'a'], 'u.id=a.user_id'],
        ];
        $result = $user_model->alias('u')->join($join)->field('u.username,a.consignee,o.amount')->select();
        $data = [
            'username' => 'test',
            'consignee' => '张三',
            'amount' => '200',
        ];
        $this->assertEquals($data, $result[0]);

        $result = $user_model->alias('u')->join('__USER_ADDRESS__ a', 'u.id=a.user_id', 'left')->field('u.username,a.consignee')->select();
        $data = [
            'username' => 'test',
            'consignee' => '张三',
        ];
        $this->assertEquals($data, $result[0]);

        $subsql = "(select user_id,amount from {$config['prefix']}order where status=1 limit 1) o";
        $result = $user_model->alias('u')->join($subsql, 'u.id=o.user_id', 'left')->field('u.username,o.amount')->select();
        $data = [
            'username' => 'test',
            'amount' => '200',
        ];
        $this->assertEquals($data, $result[0]);

        // 兼容_join方法
        $result = $user_model->alias('u')->join('__USER_ADDRESS__ a on u.id=a.user_id', 'left')->field('u.username,a.consignee')->select();
        $data = [
            'username' => 'test',
            'consignee' => '张三',
        ];
        $this->assertEquals($data, $result[0]);
    }

    public function testUnion()
    {
        $config = $this->getConfig();
        $user_model = new Model('user', $config);

        $union = "SELECT consignee FROM __USER_ADDRESS__";
        $result = $user_model->field('username')->union($union)->select();
        $this->assertEquals(4, count($result));

        $model = new Model('', $config);
        $union = ["SELECT create_time FROM __ORDER__"];
        $result = $model->table([$config['prefix'] . 'user'])->field('create_time')->union($union, true)->select();
        $this->assertEquals(4, count($result));
    }

    public function testSave()
    {
        $config = $this->getConfig();
        $order_model = new Model('order', $config);

        $data = [
            'id' => '1',
            'amount' => '180',
            'status' => 0,
            'create_time' => time(),
        ];
        $flag = $user_id = $order_model->save($data);
        $this->assertEquals(1, $flag);

        $data = [
            'status' => 1,
        ];
        $flag = $order_model->where(['id' => 2])->setField($data);
        $this->assertEquals(1, $flag);

        $flag = $order_model->where(['amount'=>['lt',200]])->setField('freight_fee', 15);
        $this->assertEquals(1, $flag);

        $map = [
            'amount' => ['gt', 300],
            'freight_fee' =>['gt', 5],
        ];
        $flag = $order_model->where($map)->setDec('freight_fee', 5, 1);
        $this->assertEquals(1, $flag);

        sleep(1);
        $flag = $order_model->where($map)->setInc('freight_fee', 5, 1);
        $this->assertEquals(1, $flag);
    }

    public function testDelete()
    {
        $config = $this->getConfig();

        $order_model = new Model('order', $config);
        $order_model->id = 2;
        $flag = $order_model->delete();
        $this->assertEquals(1, $flag);

        $flag = $order_model->delete('1');
        $this->assertEquals(1, $flag);

        $address_model = new Model('user_address', $config);
        $flag = $address_model->delete(['1','2']);
        $this->assertEquals(2, $flag);

        $user_model = new Model('user', $config);
        $flag = $user_model->where('1=1')->delete();
        $this->assertEquals(2, $flag);

        $ru_model = new Model('role_user', $config);
        $flag = $ru_model->delete(['1','1']);
        $this->assertEquals(1, $flag);

        $sql = <<<EOF
DROP TABLE IF EXISTS `tp_user`;
DROP TABLE IF EXISTS `tp_order`;
DROP TABLE IF EXISTS `tp_user_address`;
DROP TABLE IF EXISTS `tp_role_user`;
EOF;
        $model = new Model('', $this->getConfig());
        $model->execute($sql);
    }
}
