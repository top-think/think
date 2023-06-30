<?php
namespace app\controller;

use app\BaseController;
use think\facade\App;

class Index extends BaseController
{
    public function index()
    {
        $services = [
            [
                'title' => '产品运营助理',
                'description' => '提供文档、反馈、统计、客服、广播功能及自定义悬浮按钮，支持免费使用',
                'image' => 'images/services/1.png',
                'url' => 'https://doc.topthink.com/assistant/default.html',
            ],
            [
                'title' => 'ThinkSSL证书',
                'description' => '支持多域名和通配符证书，一键申购，快速签发，所有证书30天无条件退款',
                'image' => 'images/services/2.jpg',
                'url' => 'https://doc.topthink.com/think-ssl/default.html',
            ],
            [
                'title' => 'ThinkAPI',
                'description' => '官方统一接口服务，提供优雅SDK，一键接入',
                'image' => 'images/services/3.jpg',
                'url' => 'https://doc.topthink.com/think-api/default.html',
            ],
            [
                'title' => '官方云市场',
                'description' => '为ThinkPHP开发者和爱好者精选的官方以及第三方的产品和服务',
                'image' => 'images/services/4.jpg',
                'url' => 'http://market.topthink.com',
            ],
        ];

        $sponsors = [
            'special' => [
                'url'         => 'http://github.crmeb.net/u/TPSY',
                'name'        => 'CEMEB',
                'logo'        => 'images/sponsors/crmeb.png',
                'title'       => '高品质开源商城系统40w+开发者的选择，值得托付',
            ],
            'platinum' => [
                [
                    'url'         => 'https://www.niushop.com',
                    'name'        => 'NiuShop电商系统',
                    'logo'        => 'images/sponsors/niushop.png',
                ],
                [
                    'url'         => 'https://www.likeshop.cn/?utm_source=thinkphp&s',
                    'name'        => 'LikeShop电商系统',
                    'logo'        => 'images/sponsors/likeshop.svg',
                ],
                [
                    'url'         => 'https://www.niucloud-admin.com/',
                    'name'        => 'niucloud-admin开发框架',
                    'logo'        => 'images/sponsors/niucloud.png',
                ],
                [
                    'url'         => 'https://doc.topthink.com/',
                    'name'        => '顶想云服务',
                    'logo'        => 'images/sponsors/topthink-cloud.svg',
                ],
            ],
        ];

        return view('index')
            ->assign('services', $services)
            ->assign('sponsors', $sponsors)
            ->assign('version', App::version());
    }

    public function hello($name = 'ThinkPHP8')
    {
        return 'hello,' . $name;
    }
}
