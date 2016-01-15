thinkphp5.0 beta
===============

[![Build Status](https://img.shields.io/travis/top-think/think.svg)](https://travis-ci.org/top-think/think)
[![Coverage Status](https://img.shields.io/codecov/c/github/top-think/think.svg)](https://codecov.io/github/top-think/think)
[![Downloads](https://img.shields.io/github/downloads/top-think/think/total.svg)](https://github.com/top-think/think/releases)
[![Releases](https://img.shields.io/github/release/top-think/think.svg)](https://github.com/top-think/think/releases/latest)
[![Releases Downloads](https://img.shields.io/github/downloads/top-think/think/latest/total.svg)](https://github.com/top-think/think/releases/latest)
[![Packagist Status](https://img.shields.io/packagist/v/top-think/think.svg)](https://packagist.org/packages/topthink/think)
[![Packagist Downloads](https://img.shields.io/packagist/dt/top-think/think.svg)](https://packagist.org/packages/topthink/think)

ThinkPHP5在保持快速开发和大道至简的核心理念不变的同时，PHP版本要求提升到5.4，对已有的CBD模式做了更深的强化，优化核心，减少依赖，基于全新的架构思想和命名空间实现，是ThinkPHP突破原有框架思路的颠覆之作，其主要特性包括：

 + 基于命名空间和众多PHP新特性
 + 核心功能组件化
 + 强化路由功能
 + 更灵活的控制器
 + 配置文件可分离
 + 简化扩展机制
 + API支持完善
 + 改进的Log类
 + 命令行访问支持
 + REST支持
 + 引导文件支持
 + 方便的自动生成定义
 + 真正惰性加载
 + 分布式环境支持
 + 更多的社交类库

> ThinkPHP5的运行环境要求PHP5.4以上，目前处于开发测试阶段，不排除正式发布之前有所调整，
请谨慎用于实际项目 ^_^。

详细开发文档参考 [ThinkPHP5开发手册](http://www.kancloud.cn/thinkphp/thinkphp5-guide)

## 目录结构

初始的目录结构如下：

~~~
www  WEB部署目录（或者子目录）
├─composer.json         composer定义文件
├─README.md             README文件
├─LICENSE.txt           授权说明文件
├─application           应用目录
│  ├─common             公共模块目录（可以更改）
│  ├─runtime            应用的运行时目录（可写，可定制）
│  ├─module             模块目录
│  │  ├─config.php      模块配置文件
│  │  ├─common.php      模块函数文件
│  │  ├─controller      控制器目录
│  │  ├─model           模型目录
│  │  ├─view            视图目录
│  │  └─ ...            更多类库目录
│  │
│  ├─common.php         公共函数文件
│  ├─config.php         公共配置文件
│  ├─route.php          路由配置文件
│  └─database.php       数据库配置文件
│
├─public                WEB目录（对外访问目录）
│  ├─index.php          入口文件
│  ├─.htaccess          用于apache的重写
│  └─router.php         快速测试文件（用于PHP内置webserver）
│
├─thinkphp              框架系统目录
│  ├─library            框架类库目录
│  │  ├─behavior        行为类库目录
│  │  ├─think           Think类库包目录
│  │  ├─org             Org类库包目录
│  │  ├─traits          系统Trait目录
│  │  └─ ...            更多类库目录
│  │
│  ├─extend             扩展类库目录（可选）
│  ├─vendor             第三方类库目录（Composer依赖库）
│  ├─mode               应用模式目录
│  ├─tpl                系统模板目录
│  ├─base.php           基础定义文件
│  ├─helper.php         助手函数文件
│  ├─convention.php     框架惯例配置文件
│  └─start.php          框架入口文件
~~~

> router.php用于php自带webserver支持，可用于快速测试
> 切换到public目录后，启动命令：php -S localhost:8888  router.php
> 上面的目录结构和名称是可以改变的，这取决于你的入口文件和配置参数。

## 命名规范

ThinkPHP5的命名规范如下：

### 目录和文件

*   目录不强制规范，驼峰和小写+下划线模式均支持；
*   类库、函数文件统一以`.php`为后缀；
*   类的文件名均以命名空间定义，并且命名空间的路径和类库文件所在路径一致；
*   类名和类文件名保持一致，统一采用驼峰法命名（首字母大写）；

### 函数和类、属性命名
*   类的命名采用驼峰法，并且首字母大写，例如 `User`、`UserType`，不需要添加后缀，例如UserController应该直接命名为User；
*   函数的命名使用小写字母和下划线（小写字母开头）的方式，例如 `get_client_ip`；
*   方法的命名使用驼峰法，并且首字母小写或者使用下划线“_”，例如 `getUserName`，`_parseType`，通常下划线开头的方法属于私有方法；
*   属性的命名使用驼峰法，并且首字母小写或者使用下划线“_”，例如 `tableName`、`_instance`，通常下划线开头的属性属于私有属性；
*   以双下划线“__”打头的函数或方法作为魔法方法，例如 `__call` 和 `__autoload`；

### 常量和配置
*   常量以大写字母和下划线命名，例如 `APP_DEBUG`和 `APP_MODE`；
*   配置参数以小写字母和下划线命名，例如 `url_route_on`；

### 数据表和字段
*   数据表和字段采用小写加下划线方式命名，并注意字段名不要以下划线开头，例如 think_user 表和 user_name字段，类似 _username 这样的数据表字段可能会被过滤。

## 参与开发
注册并登录 Github 帐号， fork 本项目并进行改动。

更多细节参阅 [CONTRIBUTING.md](CONTRIBUTING.md)

## 版权信息

ThinkPHP遵循Apache2开源协议发布，并提供免费使用。

本项目包含的第三方源码和二进制文件之版权信息另行标注。

版权所有Copyright © 2006-2016 by ThinkPHP (http://thinkphp.cn)

All rights reserved。

ThinkPHP® 商标和著作权所有者为上海顶想信息科技有限公司。

更多细节参阅 [LICENSE.txt](LICENSE.txt)
