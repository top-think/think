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

use think\Config;
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
            'prefix'     => 'tp_',
        ];
        return $config;
    }

    public function testValidate()
    {
        $model = new Model('', $this->getConfig());
        $data  = $_POST  = [
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
                ['username', [ & $this, 'checkName'], '用户名长度为5到15个字符', 'callback', 'username'],
                ['username', function ($value, $data) {
                    return 'admin' == $value ? '此用户名已被使用' : true;
                }],
                'nickname'   => ['require', '请填昵称'],
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
                    'patch'           => true,
                ],
            ],
        ];
        Config::set('validate', $validate);
        $result = $model->validate('user.add')->create();
        $this->assertEmpty($model->getError());

        unset($data['password'], $data['repassword']);
        $data['email'] = '';
        $result        = $model->validate('user.edit')->create($data);
        $this->assertEmpty($model->getError());

    }

    public function checkName($value, $field)
    {
        switch ($field) {
            case 'username':
                $len = strlen($value);
                return $len >= 5 && $len <= 15;
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
            'cityid'   => '1',
            'a'        => 'a',
            'b'        => 'b',
        ];
        $auto = [
            'user' => [
                '__option__' => [
                    'scene'       => [
                        'edit' => 'username,nickname,phone,hobby,cityid,address,integral,reg_time,login_time,ab',
                    ],
                    'value_fill'  => 'username,phone',
                    'exists_fill' => 'nickname',
                ],
                'username'   => ['strtolower', 'callback'],
                'password'   => ['md5', 'callback'],
                'nickname'   => [[ & $this, 'fillName'], 'callback', 'cn_'],
                'phone'      => function ($value, $data) {
                    return trim($value);
                },
                'cityid'     => ['1', 'ignore'],
                'address'    => ['address'],
                'integral'   => 0,
                ['reg_time', 'time', 'callback'],
                ['login_time', function ($value, $data) {
                    return $data['reg_time'];
                }],
                'ab'         => ['a,b', 'serialize'],
            ],
        ];
        Config::set('auto', $auto);
        $result             = $model->auto('user.edit')->create($data);
        $data['nickname']   = 'cn_nickname';
        $data['phone']      = '123456';
        $data['address']    = 'address';
        $data['integral']   = 0;
        $data['reg_time']   = time();
        $data['login_time'] = $data['reg_time'];
        $data['ab']         = serialize(['a' => 'a', 'b' => 'b']);
        unset($data['cityid'], $data['a'], $data['b']);
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
ALTER TABLE `tp_user` ADD INDEX(`create_time`);

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
  `remark` varchar(250) NOT NULL DEFAULT '',
  PRIMARY KEY (`role_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
EOF;

        $model = new Model('', $this->getConfig());
        $model->execute($sql);
    }

    public function testAdd()
    {
        $config = $this->getConfig();
        $time   = time();

        $user_model = new Model('test.user', $config);
        $data       = [
            'username'    => 'test',
            'password'    => md5('123456'),
            'status'      => 1,
            'create_time' => $time,
        ];
        $user_id = $user_model->data($data)->add();
        $data    = [
            'username'    => 'test2',
            'password'    => md5('000000'),
            'status'      => 1,
            'create_time' => $time,
        ];
        $user_model->add($data, true);

        $data = [
            [
                'user_id'   => $user_id,
                'consignee' => '张三',
                'area_info' => '广东深圳',
                'city_id'   => '42',
                'area_id'   => '111',
                'address'   => 'xx路xx号',
                'mobile'    => '1380000000000',
                'isdefault' => '1',
            ],
            [
                'user_id'   => $user_id,
                'consignee' => '李四',
                'area_info' => '广东深圳',
                'city_id'   => '42',
                'area_id'   => '111',
                'address'   => 'xx路xx号',
                'mobile'    => '13999999999',
                'isdefault' => '0',
            ],
        ];
        $address_model = new Model('user_address', $config);
        $address_id    = $address_model->addAll($data, [], true);

        $data = [
            [
                'user_id'     => $user_id,
                'sn'          => '10001',
                'amount'      => '200',
                'freight_fee' => '10',
                'address_id'  => $address_id - 1,
                'status'      => '1',
                'create_time' => $time,
            ],
            [
                'user_id'     => $user_id,
                'sn'          => '10002',
                'amount'      => '350.80',
                'freight_fee' => '10',
                'address_id'  => $address_id,
                'status'      => '0',
                'create_time' => $time,
            ],
        ];
        $address_model = new Model('order', $config);
        $address_model->addAll($data);

        $data = [
            'user_id' => $user_id,
            'role_id' => 1,
        ];
        $config['db_name']   = 'test';
        $config['attr_case'] = 2;
        $model               = new Model('', $config);
        $model->table($config['prefix'] . 'role_user')->data($data)->add();
    }

    public function testQuery()
    {
        $user_model = new Model('user', $this->getConfig());

        $sql    = "select id,create_time from tp_user where username='test' limit 1";
        $result = $user_model->query($sql);
        $id     = $result[0]['id'];
        $time   = $result[0]['create_time'];
        $bind   = ['create_time' => $time, 'status' => 1];
        $info   = $user_model->where(['create_time' => ':create_time'])->where(['status' => ':status'])->bind($bind)->field(true)->find(['cache' => ['key' => true]]);
        $data   = [
            'id'          => $id,
            'username'    => 'test',
            'password'    => md5('123456'),
            'status'      => '1',
            'create_time' => $time,
        ];
        $this->assertEquals($data, $info);

        $_GET['id'] = $id;
        $result     = $user_model->where(['id' => ':id'])->bind('id', $_GET['id'])->field('password,create_time', true)->order('id')->limit('0,10')->select(['cache' => ['key' => true, 'expire' => 0], 'index' => 'username']);
        $data       = [
            'id'       => $id,
            'username' => 'test',
            'status'   => '1',
        ];
        $this->assertEquals($data, $result['test']);

        $_GET['status'] = '1';
        $result         = $user_model->where(['status' => ':status'])->bind('status', $_GET['status'], \PDO::PARAM_INT)->field('password,create_time', true)->order('id', 'desc')->index('id,username')->page('0,10')->select();
        $data           = [
            '1' => 'test',
            '2' => 'test2',
        ];
        $this->assertEquals($data, $result);

        $time = $user_model->where(['status' => 1])->cache('user_create_time')->getField('create_time');
        $ids  = $user_model->where(['status' => 1])->cache('user_id')->getField('id', true);
        $this->assertEquals(2, count($ids));

        $result = $user_model->cache(true)->getField('username,status,create_time', '|');
        $data   = [
            'test'  => '1|' . $time,
            'test2' => '1|' . $time,
        ];
        $this->assertEquals($data, $result);

        $result = $user_model->cache(10)->getField('username,status');
        $data   = [
            'test'  => '1',
            'test2' => '1',
        ];
        $this->assertEquals($data, $result);

        $result = $user_model->scope(['field' => 'username', 'where' => 'status=1'])->select();
        $data   = [
            ['username' => 'test'],
            ['username' => 'test2'],
        ];
        $this->assertEquals($data, $result);

        $result = $user_model->master()->lock(true)->distinct(true)->force('create_time')->comment('查询用户名')->field('username')->fetchSql(true)->select();
        $sql    = 'SELECT DISTINCT  `username` FROM `tp_user` FORCE INDEX ( create_time )   FOR UPDATE  /* 查询用户名 */';
        $this->assertEquals($sql, $result);

        $order_model = new Model('order', $this->getConfig());

        $result = $order_model->field('user_id,sum(amount) amount')->group('user_id')->having('sum(amount) > 1000')->select();
        $this->assertEmpty($result);

        $result = $order_model->getLastSql();
        $sql    = 'SELECT `user_id`,sum(amount) amount FROM `tp_order` GROUP BY user_id HAVING sum(amount) > 1000 ';
        $this->assertEquals($sql, $result);
    }

    public function testJoin()
    {
        $config     = $this->getConfig();
        $user_model = new Model('user', $config);

        $join = [
            [['order o', 'tp_'], 'u.id=o.user_id'],
            [['user_address' => 'a'], 'u.id=a.user_id'],
        ];
        $result = $user_model->alias('u')->join($join)->field('u.username,a.consignee,o.amount')->select();
        $data   = [
            'username'  => 'test',
            'consignee' => '张三',
            'amount'    => '200',
        ];
        $this->assertEquals($data, $result[0]);

        $result = $user_model->alias('u')->join('__USER_ADDRESS__ a', 'u.id=a.user_id', 'left')->field('u.username,a.consignee')->select();
        $data   = [
            'username'  => 'test',
            'consignee' => '张三',
        ];
        $this->assertEquals($data, $result[0]);

        $result = $user_model->alias('u')->join('role_user ru', 'u.id=ru.user_id', 'left')->field('u.username,ru.role_id')->select();
        $data   = [
            'username' => 'test',
            'role_id'  => '1',
        ];
        $this->assertEquals($data, $result[0]);

        $order_model = new Model('order', $config);
        $subsql      = $order_model->limit(1)->buildSql();
        $result      = $user_model->alias('u')->join($subsql . ' o', 'u.id=o.user_id', 'left')->field('u.username,o.amount')->select();
        $data        = [
            'username' => 'test',
            'amount'   => '200',
        ];
        $this->assertEquals($data, $result[0]);

        // 兼容_join方法
        $result = $user_model->alias('u')->join('__USER_ADDRESS__ a on u.id=a.user_id', 'left')->field('u.username,a.consignee')->select();
        $data   = [
            'username'  => 'test',
            'consignee' => '张三',
        ];
        $this->assertEquals($data, $result[0]);
    }

    public function testUnion()
    {
        $config     = $this->getConfig();
        $user_model = new Model('user', $config);

        $union  = "SELECT consignee FROM __USER_ADDRESS__";
        $result = $user_model->field('username')->union($union)->select();
        $this->assertEquals(4, count($result));

        $model  = new Model('', $config);
        $union  = ["SELECT create_time FROM __ORDER__"];
        $result = $model->table([$config['prefix'] . 'user'])->field('create_time')->union($union, true)->select();
        $this->assertEquals(4, count($result));
    }

    public function testSave()
    {
        $config      = $this->getConfig();
        $order_model = new Model('order', $config);

        $data = [
            'id'          => '1',
            'total'       => '180.50',
            'status'      => 1,
            'create_time' => time(),
            'about'       => '',
        ];
        \think\Config::set('db_fields_strict', false);
        $info = $order_model->where(['id' => 1])->map('amount', 'total')->find();
        $flag = $user_id = $order_model->map(['total' => 'amount'])->filter('trim')->save($data);
        $this->assertSame(1, $flag);
        \think\Config::set('db_fields_strict', true);

        $data = [
            'status' => 1,
        ];
        $flag = $order_model->where(['id' => 2])->setField($data);
        $this->assertSame(1, $flag);

        $flag = $order_model->where(['amount' => ['lt', 200]])->setField('freight_fee', 15);
        $this->assertSame(1, $flag);

        $map = [
            'amount'      => ['gt', 300],
            'freight_fee' => ['gt', 0],
        ];
        $flag = $order_model->where($map)->setDec('freight_fee', 5, 30);
        $this->assertTrue($flag);

        $flag = $order_model->where($map)->setInc('freight_fee', 5, 30);
        $this->assertTrue($flag);

        $flag = $order_model->where($map)->setDec('freight_fee', 5);
        $this->assertSame(1, $flag);

        $flag = $order_model->where($map)->setInc('freight_fee', 5);
        $this->assertSame(1, $flag);

        $ru_model = new Model('role_user', $config);
        $data     = [
            'user_id' => 1,
            'role_id' => 1,
            'remark'  => 'remark',
        ];
        $info = $ru_model->where(['user_id' => 1])->find();
        $flag = $ru_model->data($data)->save();
        $this->assertSame(1, $flag);
    }

    public function testMagicMethods()
    {
        $model = new Model('user', $this->getConfig());
        $model->data("first=a&last=z");
        $model->username = 'test';

        $data = [
            'first'    => 'a',
            'last'     => 'z',
            'username' => 'test',
        ];
        $this->assertEquals($data, $model->data());

        $this->assertEquals('a', $model->first);

        $this->assertTrue(isset($model->last));

        unset($model->username);
        $this->assertTrue(!isset($model->username));

        $this->assertEquals(2, $model->count());

        $info = $model->getByUsername('test');
        $this->assertEquals(1, $info['id']);

        $id = $model->getFieldByUsername('test', 'id');
        $this->assertEquals(1, $id);

        $id = $model->getFieldByUsername('test', 'id');
        $this->assertEquals(1, $id);
    }

    public function testDelete()
    {
        $config = $this->getConfig();

        $order_model     = new Model('order', $config);
        $order_model->id = 2;
        $flag            = $order_model->delete();
        $this->assertEquals(1, $flag);

        $flag = $order_model->delete('1');
        $this->assertEquals(1, $flag);

        $address_model = new Model('user_address', $config);
        $flag          = $address_model->delete(['1', '2']);
        $this->assertEquals(2, $flag);

        $user_model = new Model('user', $config);
        $flag       = $user_model->using([''])->where('1=1')->delete();
        $this->assertEquals(2, $flag);

        $ru_model = new Model('role_user', $config);
        $flag     = $ru_model->delete(['1', '1']);
        $this->assertEquals(1, $flag);

        $sql = <<<EOF
DROP TABLE IF EXISTS `tp_user`;
DROP TABLE IF EXISTS `tp_order`;
DROP TABLE IF EXISTS `tp_user_address`;
DROP TABLE IF EXISTS `tp_role_user`;
EOF;
        $model = new Model('', $this->getConfig());
        $model->execute($sql);
        $flag = $model->db(0, null);
        $this->assertNull($flag);
    }
}
