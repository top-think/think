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
                'title' => 'CRMEB  TP6开源商城系统',
                'description' => '基于TP6 + uniapp开发，文档齐全，方便二开，可免费商用',
                'image' => 'images/services/4.png',
                'url' => 'http://github.crmeb.net/u/TPH1',
            ],
        ];

        $sponsors = [
            'special' => [
                'url'         => 'http://github.crmeb.net/u/TPSY',
                'name'        => 'CEMEB',
                'logo'        => 'https://www.thinkphp.cn/uploads/images/20230630/33e1089da8c500bce9b27658da9c2306.png',
                'title'       => '高品质开源商城系统40w+开发者的选择，值得托付',
            ],
            'platinum' => [
                [
                    'url'         => 'https://www.niushop.com',
                    'name'        => 'NiuShop电商系统',
                    'logo'        => 'https://www.thinkphp.cn/uploads/images/20230629/0728817651b219055c3393b4988187fa.png',
                ],
                [
                    'url'         => 'https://www.likeshop.cn/?utm_source=thinkphp&s',
                    'name'        => 'LikeShop电商系统',
                    'logo'        => 'https://www.thinkphp.cn/uploads/images/20230629/81b98332a39d32d1e30e306da4415bd0.png',
                ],
                [
                    'url'         => 'https://www.niucloud-admin.com/',
                    'name'        => 'niucloud-admin开发框架',
                    'logo'        => 'https://www.thinkphp.cn/uploads/images/20230629/76104be7e16b260476005d51558270f6.png',
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
