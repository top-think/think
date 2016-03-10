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

// 开始运行时间和内存使用
define('START_TIME', microtime(true));
define('START_MEM', memory_get_usage());
//  版本信息
define('THINK_VERSION', '5.0.0 RC2');
// 系统常量
defined('DS') or define('DS', DIRECTORY_SEPARATOR);
defined('THINK_PATH') or define('THINK_PATH', dirname(__FILE__) . DS);
defined('LIB_PATH') or define('LIB_PATH', THINK_PATH . 'library' . DS);
defined('EXTEND_PATH') or define('EXTEND_PATH', THINK_PATH . 'extend' . DS);
defined('MODE_PATH') or define('MODE_PATH', THINK_PATH . 'mode' . DS); // 系统应用模式目录
defined('CORE_PATH') or define('CORE_PATH', LIB_PATH . 'think' . DS);
defined('TRAIT_PATH') or define('TRAIT_PATH', LIB_PATH . 'traits' . DS);
defined('APP_PATH') or define('APP_PATH', dirname($_SERVER['SCRIPT_FILENAME']) . DS);
defined('APP_NAMESPACE') or define('APP_NAMESPACE', 'app');
defined('COMMON_MODULE') or define('COMMON_MODULE', 'common');
defined('RUNTIME_PATH') or define('RUNTIME_PATH', realpath(APP_PATH) . DS . 'runtime' . DS);
defined('LOG_PATH') or define('LOG_PATH', RUNTIME_PATH . 'log' . DS);
defined('CACHE_PATH') or define('CACHE_PATH', RUNTIME_PATH . 'cache' . DS);
defined('TEMP_PATH') or define('TEMP_PATH', RUNTIME_PATH . 'temp' . DS);
defined('VENDOR_PATH') or define('VENDOR_PATH', THINK_PATH . 'vendor' . DS);
defined('EXT') or define('EXT', '.php');
defined('MODEL_LAYER') or define('MODEL_LAYER', 'model');
defined('VIEW_LAYER') or define('VIEW_LAYER', 'view');
defined('CONTROLLER_LAYER') or define('CONTROLLER_LAYER', 'controller');
defined('APP_MULTI_MODULE') or define('APP_MULTI_MODULE', true); // 是否多模块
defined('APP_DEBUG') or define('APP_DEBUG', false); // 是否调试模式
defined('APP_HOOK') or define('APP_HOOK', false); // 是否开启HOOK
defined('ENV_PREFIX') or define('ENV_PREFIX', 'T_'); // 环境变量的配置前缀
defined('IS_API') or define('IS_API', false); // 是否API接口
defined('APP_AUTO_RUN') or define('APP_AUTO_RUN', true); // 是否自动运行
defined('APP_ROUTE_ON') or define('APP_ROUTE_ON', true); // 是否允许路由
defined('APP_ROUTE_MUST') or define('APP_ROUTE_MUST', true); // 是否严格检查路由
defined('CLASS_APPEND_SUFFIX') or define('CLASS_APPEND_SUFFIX', false); // 是否追加类名后缀
// 应用模式 默认为普通模式
defined('APP_MODE') or define('APP_MODE', function_exists('saeAutoLoader') ? 'sae' : 'common');

// 环境常量
define('IS_CGI', strpos(PHP_SAPI, 'cgi') === 0 ? 1 : 0);
define('IS_WIN', strstr(PHP_OS, 'WIN') ? 1 : 0);
define('IS_MAC', strstr(PHP_OS, 'Darwin') ? 1 : 0);
define('IS_CLI', PHP_SAPI == 'cli' ? 1 : 0);
define('IS_AJAX', (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') ? true : false);
define('NOW_TIME', $_SERVER['REQUEST_TIME']);
define('REQUEST_METHOD', IS_CLI ? 'GET' : $_SERVER['REQUEST_METHOD']);
define('IS_GET', REQUEST_METHOD == 'GET' ? true : false);
define('IS_POST', REQUEST_METHOD == 'POST' ? true : false);
define('IS_PUT', REQUEST_METHOD == 'PUT' ? true : false);
define('IS_DELETE', REQUEST_METHOD == 'DELETE' ? true : false);
