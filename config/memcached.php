<?php
return extension_loaded('memcached') ? [
    'host'          => '',
    'port'          => 11211,
    'expire'        => 3 * 86400,
    'timeout'       => 0, // 超时时间（单位：毫秒）
    'prefix'        => '',
    'username'      => '', //账号
    'password'      => '', //密码
    'option'        => [
        \Memcached::OPT_COMPRESSION     => false,
    ],
    'pconnect'      => true,
    'max_pool_size' => 0,
    'name'          => '',
] : [
    'host'    => '127.0.0.1',
    'port'    => 11211,                                                             //--支持集群，使用,隔开多个port
    'expire'  => 3 * 86400,                                                         //--默认过期时间
    'timeout' => 0,
];