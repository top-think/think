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

        $ecologies = [
            [
                'url'         => 'https://www.niushop.com',
                'name'        => 'NIUSHOP开源商城系统',
                'description' => '帮助商家快速锁定私域流量的电商平台系统,助力商家轻松获客、裂变',
                'logo'        => 'images/ecologies/1.jpg',
            ],
            [
                'url'         => 'https://gitee.likeshop.cn/thinkphp',
                'name'        => 'likeshop 全开源商城系统',
                'description' => '致力打造源码行业最具性价比产品，免去开发者重复造轮子',
                'logo'        => 'images/ecologies/2.png',
            ],
            [
                'url'         => 'https://www.topthink.com/product/knowledge',
                'name'        => '顶想云文档',
                'description' => '快速搭建企业文档中心和内部知识库，支持私有化部署',
                'logo'        => 'images/ecologies/3.png',
            ],
            [
                'url'         => 'https://www.thinkphp.cn/app/28',
                'name'        => 'niucloud-admin开发框架',
                'description' => '一款快速开发SAAS通用管理系统后台框架',
                'logo'        => 'images/ecologies/4.png',
            ],
            [
                'url'         => 'https://www.upyun.com',
                'name'        => '又拍云',
                'description' => '致力于为客户提供一站式的在线业务加速服务',
                'logo'        => 'images/ecologies/5.png',
            ],
            [
                'url'         => 'https://www.thinkphp.cn/app/60',
                'name'        => '运营助理',
                'description' => '快速构建产品支持服务，自定义悬浮按钮，免费使用',
                'logo'        => 'images/ecologies/6.png',
            ],
        ];

        return view('index')
            ->assign('services', $services)
            ->assign('ecologies', $ecologies)
            ->assign('version', App::version());
    }

    public function hello($name = 'ThinkPHP8')
    {
        return 'hello,' . $name;
    }
}
