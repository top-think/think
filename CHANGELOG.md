## V5.1.39 LTS（2019-11-18）

本次更新为常规更新，主要包括：

* 修正`memcached`驱动
* 改进`HasManyThrough`关联查询
* 改进`Request`类`isJson`方法
* 改进关联查询
* 改进`redis`驱动
* 增加 Model类`getWhere`方法对复合主键的支持
* 改进`newQuery`方法
* 改进闭包查询的参数绑定
* 修正`Validate`
* 修复某些情况下URL会多一个冒号
* 调整composer.json
* 修复使用`Cache::clear()`时，报错缓存文件不存在问题
* 使用File类的unlink方法进行文件删除
* 改进`paraseData`方法
* 修正image验证方法
* 改进Url生成
* 改进空操作对数字的支持
* 改进一处PHP7.4兼容性问题

## V5.1.38 LTS（2019-8-8）

本次更新为常规更新，主要包括：

* `Request`类增加`isJson`方法
* 改进浮点型查询
* 修正关联查询关联外键为空的查询错误
* 远程一对多支持关联统计和预载入查询
* 远程一对多关联支持`has`/`hasWhere`查询
* 优化`parseIn`解析
* 改进`parseLike`查询
* 改进Url生成
* 改进模型的`toArray`方法
* 修正`notIn`查询
* 改进`JSON`字段查询
* 改进Controller类`display`/`fetch`方法返回`ViewResponse`对象
* 改进`param`方法
* 改进`mysql`驱动`getExplain`方法
* 改进时间查询
* 改进模型关联的`has`/`hasWhere`方法对软删除的支持
* 修正社区反馈的BUG

## V5.1.37 LTS（2019-5-26）

本次更新为常规更新，主要更新如下：

* 改进关联数据更新
* 修正关联动态获取器
* 改进`redis`驱动 
* 修复验证规则里面出现二维数组时的错误
* 改进跨域请求支持
* 完善模型`hidden`方法对关联属性的支持
* 改进`where`查询方法传入`Query`对象的支持`bind`数据
* 改进数据集对象的`load`方法
* 修正缓存类`clear`方法对`tag`的支持

## V5.1.36 LTS（2019-4-28）

本次更新为常规更新，主要更新如下：

* 修正`chunk`方法一处异常抛出的错误
* 修正模型输出的`visible`
* 改进环境变量加载
* 改进命令行日志的`level`配置支持
* 修复设置有缓存前缀时，无法清空缓存标签的问题
* HasMony对象`saveAll`方法兼容`Collection`格式参数格式
* 修正`whereOr`查询使用字符串的问题
* 改进`dateFormat`设置对写入数据的影响
* 修正查询缓存
* 记住指定的跳转地址
* 改进软删除
* 改进聚合查询SQL去除limit 1
* 改进缓存驱动

## V5.1.35 LTS（2019-3-2）

本次主要为常规更新，修正了一些反馈的问题。

* 修正验证类自定义验证方法执行两次的问题
* 模型增加`isEmpty`方法用于判断是否空模型
* 改进获取器对`append`的支持
* 修正一对多关联的`withCount`自关联问题
* facade类注释调整
* 改进关联属性的`visible`和`hidden`判断
* 修正路由分组的`MISS`路由
* 改进pgsql.sql

## V5.1.34 LTS（2019-1-30）

本次更新为常规更新，修正了一些反馈的问题。

* 改进Request类的`has`方法，支持`patch`
* 改进`unique`验证的多条件支持
* 修复自定义上传验证，检测文件大小
* 改进`in`查询支持表达式
* 改进路由的`getBind`方法
* 改进验证类的错误信息获取
* 改进`response`助手函数默认值
* 修正mysql的`regexp`查询
* 改进模型类型强制转换写入对`Expression`对象的支持

## V5.1.33 LTS（2019-1-16）

* 修复路由中存在多个相同替换的正则BUG
* 修正whereLike查询
* join方法支持参数绑定
* 改进union方法
* 修正多对多关联的attach方法
* 改进验证类的正则规则自定义
* 改进Request类method方法
* 改进File日志类型的CLI日志写入
* 改进文件日志time_format配置对JSON格式的支持

## V5.1.32 LTS（2018-12-24）

本次主要为常规更新，修正了一些反馈的问题。


* 改进多对多关联的`attach`方法 
* 改进聚合查询的`field`处理
* 改进关联的`save`方法
* 修正模型`exists`方法返回值
* 改进时间字段写入和输出
* 改进控制器中间件的调用
* 改进路由变量替换的性能
* 改进缓存标签的处理机制

## V5.1.31 LTS (2018-12-9)

本次版本包含一个安全更新，建议升级。

* 改进`field`方法
* 改进`count`方法返回类型
* `download`函数增加在浏览器中显示文件功能
* 修正多对多模型的中间表数据写入
* 改进`sqlsrv`驱动支持多个Schemas模式查询
* 统一助手函数与\think\response\Download函数文件过期时间
* 完善关联模型的`save`方法 增加`make`方法仅创建对象不保存
* 修改条件表达式对静态变量的支持
* 修正控制器名获取
* 改进view方法的`field`解析

## V5.1.30 LTS（2018-11-30）

该版本为常规更新，修正了一些社区反馈的问题。

主要更新如下：

* 改进查询类的`execute`方法
* 判断路由规则定义添加对请求类型的判断
* 修复`orderRaw`异常
* 修正 `optimize:autoload`指令
* 改进软删除的`destroy`方法造成重复执行事件的问题
* 改进验证类对扩展验证规则 始终验证 不管是否`require`
* 修复自定义验证`remove`所有规则的异常
* 改进时间字段的自动写入支持微秒数据
* 改进`Connection`类的`getrealsql`方法
* 修正`https`地址的URL生成
* 修复 `array_walk_recursive` 在低于PHP7.1消耗内部指针问题
* 改进手动参数绑定使用
* 改进聚合查询方法的`field`参数支持`Expression`

## V5.1.29 LTS（2018-11-11）

该版本主要改进了参数绑定的解析问题和提升性能，并修正了一些反馈的问题。

* 改进手动参数绑定
* 修正MISS路由的分组参数无效问题
* 行为支持对象的方法
* 修正全局查询范围
* 改进`belongsto`关联的`has`方法
* 改进`hasMany`关联
* 改进模型观察者多次注册的问题
* 改进`query`类的默认查询参数处理
* 修正`parseBetween`解析方法
* 改进路由地址生成的本地域名支持
* 改进参数绑定的实际URL解析性能
* 改进`Env`类的`getEnv`和`get`方法
* 改进模板缓存的生成优化
* 修复验证类的多语言支持
* 修复自定义场景验证`remove`规则异常
* File类添加是否自动补全扩展名的选项
* 改进`strpos`对子串是否存在的判断
* 修复`choice`无法用值选择第一个选项问题
* 验证器支持多维数组取值验证
* 改进解析`extend`和`block`标签的正则

## V5.1.28 LTS（2018-10-29）

该版本主要修正了上一个版本存在的一些问题，并改进了关联查询

* 改进聚合查询方法的字段支持DISTINCT
* 改进定义路由后url函数的端口生成
* 改进控制器中间件对`swoole`等的支持
* 改进Log类`save`方法
* 改进验证类的闭包验证参数
* 多对多关联支持指定中间表数据的名称
* 关联聚合查询支持闭包方式指定聚合字段
* 改进Lang类`get`方法
* 多对多关联增加判断关联数据是否存在的方法
* 改进关联查询使用`fetchsql`的情况
* 改进修改器的是否已经执行判断
* 增加`afterWith`和`beforeWith`验证规则 用于比较日期字段

## V5.1.27 LTS（2018-10-22）

该版本主要修正了路由绑定的参数，改进了修改器的执行多次问题，并正式宣布为LTS版本！


* 修正路由绑定的参数丢失问题
* 修正路由别名的参数获取
* 改进修改器会执行多次的问题

## V5.1.26（2018-10-12）

该版本主要修正了上一个版本的一些问题，并改进了全局查询范围的支持，同时包含了一个安全更新。


* 修正单一模块下注解路由无效的问题
* 改进数据库的聚合查询的字段处理
* 模型类增加`globalScope`属性定义 用于指定全局的查询范围
* 模型的`useGlobalScope`方法支持传入数组 用于指定当前查询需要使用的全局查询范围
* 改进数据集的`order`方法对数字类型的支持
* 修正上一个版本`order`方法解析的一处BUG
* 排序字段不合法或者错误的时候抛出异常
* 改进`Request`类的`file`方法对上传文件的错误判断

##  V5.1.25（2018-9-21）

该版本主要改进了查询参数绑定的性能和对浮点型的支持，以及一些细节的完善。

* 修正一处命令行问题
* 改进`Socketlog`日志驱动，支持自定义默认展开日志类别
* 修正`MorphMany`一处bug
* 跳转到上次记住的url，并支持默认值
* 改进模型的异常提示
* 改进参数绑定对浮点型的支持
* 改进`order`方法解析
* 改进`json`字段数据的自动编码
* 改进日志`log_write`可能造成的日志写入死循环
* Log类增加`log_level`行为标签位置，用于对某个类型的日志进行处理
* Route类增加`clear`方法清空路由规则
* 分布式数据库配置支持使用数组
* 单日志文件也支持`max_files`参数
* 改进查询参数绑定的性能
* 改进别名路由的URL后缀参数检测
* 控制器前置方法和控制器中间件的`only`和`except`定义不区分大小写

## V5.1.24（2018-9-5）

该版本主要增加了命令行的表格输出功能，并增加了查看路由定义的指令，以及修正了社区的一些反馈问题。

* 修正`Request`类的`file`方法
* 修正路由的`cache`方法
* 修正路由缓存的一处问题
* 改进上传文件获取的异常处理
* 改进`fetchCollection`方法支持传入数据集类名
* 修正多级控制器的注解路由生成
* 改进`Middleware`类`clear`方法
* 增加`route:list`指令用于[查看定义的路由](752690) 并支持排序
* 命令行增加`Table`输出类
* `Command`类增加`table`方法用于输出表格
* 改进搜索器查询方法支持别名定义
* 命令行配置增加`auto_path`参数用于定义自动载入的命令类路径
* 增加`make:command`指令用于[快速生成指令](354146)
* 改进`make:controller`指令对操作方法后缀的支持
* 改进命令行的定义文件支持索引数组 用于指令对象的惰性加载
* 改进`value`和`column`方法对后续查询结果的影响
* 改进`RuleName`类的`setRule`方法

## V5.1.23（2018-8-23）

该版本主要改进了数据集对象的处理，增加了`findOrEmpty`方法，并且修正了一些社区反馈的BUG。

* 数据集类增加`diff`/`intersect`方法用于获取差集和交集（默认根据主键值比较）
* 数据集类增加`order`方法支持指定字段排序
* 数据集类增加`map`方法使用回调函数处理数据并返回新的数据集对象
* Db增加`allowEmpty`方法允许`find`方法在没有数据的时候返回空数组或者空模型对象而不是null
* Db增加`findOrEmpty`方法
* Db增加`fetchCollection`方法用于指定查询返回数据集对象
* 改进`order`方法的数组方式解析，增强安全性
* 改进`withSearch`方法，支持第三个参数传入字段前缀标识，用于多表查询字段搜索
* 修正`optimize:route`指令开启类库后缀后的注解路由生成
* 修正redis缓存及session驱动
* 支持指定`Yaconf`的独立配置文件
* 增加`yaconf`助手函数用于配置文件


## V5.1.22（2018-8-9）

该版本主要增加了模型搜索器和`withJoin`方法，完善了模型输出和对`Yaconf`的支持，修正了一些社区反馈的BUG。

* 改进一对一关联的`table`识别问题
* 改进内置`Facade`类
* 增加`withJoin`方法支持`join`方式的[一对一关联](一对一关联.md)查询
* 改进`join`预载入查询的空数据问题
* 改进`Config`类的`load`方法支持快速加载配置文件
* 改进`execute`方法和事务的断线重连
* 改进`memcache`驱动的`has`方法
* 模型类支持定义[搜索器](搜索器.md)方法
* 完善`Config`类对`Yaconf`的支持
* 改进模型的`hidden/visible/append/withAttr`方法，支持在[查询前后调用](数组访问.md)，以及支持数据集对象
* 数据集对象增加`where`方法根据字段或者关联数据[过滤数据](模型数据集.md)
* 改进AJAX请求的`204`判断


## V5.1.21（2018-8-2）

该版本主要增加了下载响应对象和数组查询对象的支持，并修正了一些社区反馈的问题。

* 改进核心对象的无用信息调试输出
* 改进模型的`isRelationAttr`方法判断
* 模型类的`get`和`all`方法并入Db类
* 增加[下载响应对象](文件下载.md)和`download`助手函数
* 修正别名路由配置定义读取
* 改进`resultToModel`方法
* 修正开启类库后缀后的注解路由生成
* `Response`类增加`noCache`快捷方法
* 改进路由对象在`Swoole`/`Workerman`下面参数多次合并问题
* 修正路由`ajax`/`pjax`参数后路由变量无法正确获取的问题
* 增加清除中间件的方法
* 改进依赖注入的参数规范自动识别（便于对接前端小写+下划线规范）
* 改进`hasWhere`的数组条件的字段判断
* 增加[数组查询对象](高级查询.md)`Where`支持（喜欢数组查询的福音）
* 改进多对多关联的闭包支持

## V5.1.20（2018-7-25）

该版本主要增加了Db和模型的动态获取器的支持，并修正了一些已知问题。

* Db类添加[获取器支持](703981)
* 支持模型及关联模型字段[动态定义获取器](354046)
* 动态获取器支持`JSON`字段
* 改进路由的`before`行为执行（匹配后执行）
*  `Config`类支持`Yaconf`
* 改进Url生成的端口问题
* Request类增加`setUrl`和`setBaseUrl`方法
* 改进页面trace的信息显示
* 修正`MorphOne`关联
* 命令行添加[查看版本指令](703994)

## V5.1.19 （2018-7-13）

该版本是一个小幅改进版本，针对`Swoole`和`Workerman`的`Cookie`支持做了一些改进，并修正了一些已知的问题。


* 改进query类`delete`方法对软删除条件判断
* 修正分表查询的软删除问题
* 模型查询的时候同时传入`table`和`name`属性
* 容器类增加`IteratorAggregate`和`Countable`接口支持
* 路由分组支持对下面的资源路由统一设置`only/except/vars`参数
* 改进Cookie类更好支持扩展
* 改进Request类`post`方法
* 改进模型自关联的自动识别
* 改进Request类对`php://input`数据的处理


## V5.1.18 （2018-6-30）

该版本主要完善了对`Swoole`和`Workerman`的`HttpServer`运行支持，改进`Request`类，并修正了一些已知的问题。

* 改进关联`append`方法的处理
* 路由初始化和检测方法分离
* 修正`destroy`方法强制删除
* `app_init`钩子位置移入`run`方法
* `think-swoole`扩展更新到2.0版本
* `think-worker`扩展更新到2.0版本
* 改进Url生成的域名自动识别
* `Request`类增加`setPathinfo`方法和`setHost`方法
* `Request`类增加`withGet`/`withPost`/`withHeader`/`withServer`/`withCookie`/`withEnv`方法进行赋值操作
* Route类改进`host`属性的获取
* 解决注解路由配置不生效的问题
* 取消Test日志驱动，改为使用`close`设置关闭全局日志写入
* 修正路由的`response`参数
* 修正204响应输出的判断

## V5.1.17 （2018-6-18）

该版本主要增加了控制器中间件的支持，改进了路由功能，并且修正了社区反馈的一些问题。

* 修正软删除的`delete`方法
* 修正Query类`Count`方法
* 改进多对多`detach`方法
* 改进Request类`Session`方法
* 增加控制器中间件支持
* 模型类增加`jsonAssoc`属性用于定义json数据是否返回数组
* 修正Request类`method`方法的请求伪装
* 改进静态路由的匹配
* 分组首页路由自动完整匹配
* 改进sqlsrv的`column`方法
* 日志类的`apart_level`配置支持true自动生成对应类型的日志文件
* 改进`204`输出判断
* 修正cli下页面输出的BUG
* 验证类使用更高效的`ctype`验证机制
* 改进Request类`cookie`方法
* 修正软删除的`withTrashed`方法
* 改进多态一对多的预载入查询
* 改进Query类`column`方法的缓存读取
* Query类增加`whereBetweenTimeField`方法
* 改进分组下多个相同路由规则的合并匹配问题
* 路由类增加`getRule`/`getRuleList`方法获取定义的路由

## V5.1.16 （2018-6-7）

该版本主要修正了社区反馈的一些问题，并对Request类做了进一步规范和优化。

* 改进Session类的`boot`方法
* App类的初始化方法可以单独执行
* 改进Request类的`param`方法
* 改进资源路由的变量替换
* Request类增加`__isset`方法
* 改进`useGlobalScope`方法对软删除的影响
* 修正命令行调用
* 改进Cookie类`init`方法
* 改进多对多关联删除的返回值
* 一对多关联写入支持`replace`
* 路由增加`filter`检测方法,用于通过请求参数检测路由是否匹配
* 取消Request类`session/env/server`方法的`filter`参数
* 改进关联的指定属性输出
* 模型删除操作删除后不清空对象数据仅作标记
* 调整模型的`save`方法返回值为布尔值
* 修正Request类`isAjax`方法
* 修正中间件的模块配置读取
* 取消Request类的请求变量的设置功能
* 取消请求变量获取的默认修饰符
* Request类增加`setAction/setModule/setController`方法
* 关联模型的`delete`方法调用Query类
* 改进URL生成的域名识别
* 改进URL检测对已定义路由的域名判断
* 模型类增加`isExists`和`isForce`方法
* 软删除的`destroy`和`restore`方法返回值调整为布尔值

## V5.1.15 （2018-6-1）

该版本主要改进了路由缓存的性能和缓存方式设置，增加了JSON格式文件日志的支持，并修正了社区反馈的一些问题。

* 容器类增加`exists`方法 仅判断是否存在对象实例
* 取消配置类的`autoload`方法
* 改进路由缓存大小提高性能
* 改进Dispatch类`init`方法
* 增加`make:validate`指令生成验证器类
* Config类`get`方法支持默认值参数
* 修正字段缓存指令
* 改进App类对`null`数据的返回
* 改进模型类的`__isset`方法判断
* 修正`Query`类的`withAggregate`方法
* 改进`RuleItem`类的`setRuleName`方法
* 修正依赖注入和参数的冲突问题
* 修正Db类对第三方驱动的支持
* 修正模型类查询对象问题
* 修正File缓存驱动的`has`方法
* 修正资源路由嵌套
* 改进Request类对`$_SERVER`变量的读取
* 改进请求缓存处理
* 路由缓存支持指定单独的缓存方式和参数
* 修正资源路由的中间件多次执行问题
* 修正`optimize:config`指令
* 文件日志支持`JSON`格式日志保存
* 修正Db类`connect`方法
* 改进Log类`write`方法不会自动写入之前日志
* 模型的关联操作默认启用事务
* 改进软删除的事件响应

## V5.1.14 （2018-5-18）

该版本主要对底层容器进行了一些优化改进，并增加了路由缓存功能，可以进一步提升路由性能。

* 依赖注入的对象参数传入改进
* 改进核心类的容器实例化
* 改进日期字段的读取
* 改进验证类的`getScene`方法
* 模型的`create`方法和`save`方法支持`replace`操作
* 改进`Db`类的调用机制
* App类调整为容器类
* 改进容器默认绑定
* `Loader`类增加工厂类的实例化方法
* 增加路由变量默认规则配置参数
* 增加路由缓存设计
* 错误处理机制改进
* 增加清空路由缓存指令


## V5.1.13 （2018-5-11）

该版本主要增加了MySQL的XA事务支持，模型事件支持观察者，以及对Facade类的改进。

* 改进自动缓存
* 改进Url生成
* 修正数据缓存
* 修正`value`方法的缓存
* `join`方法和`view`方法的条件支持使用`Expression`对象
* 改进驱动的`parseKey`方法
* 改进Request类`host`方法和`domain`方法对端口的处理
* 模型增加`withEvent`方法用于控制当前操作是否需要执行模型事件
* 模型`setInc/setDec`方法支持更新事件
* 模型添加`before_restore/after_restore`事件
* 增加模型事件观察者
* 路由增加`mobile`方法设置是否允许手机访问
* 数据库XA事务支持
* 改进索引数组查询对`IN`查询的支持
* 修正`invokeMethod`方法
* 修正空数据写入返回值的BUG
* redis驱动支持`predis`
* 改进`parseData`方法
* 改进模块加载
* App类初始化方法调整
* 改进数组查询对表达式`Expression`对象支持
* 改进闭包的依赖注入调用
* 改进多对多关联的中间表模型更新
* 增加容器中对象的自定义实例化

## V5.1.12 （2018-4-25）

该版本主要改进了主从查询的及时性，并支持动态设置请求数据。

* 支持动态设置请求数据
* 改进`comment`方法解析
* 修正App类`__unset`方法
* 改进url生成的域名绑定
* 改进主从查询的及时性
* 修正`value`的数据缓存功能
* 改进分页类的集合对象方法调用
* 改进Db类的代码提示
* SQL日志增加主从标记

## V5.1.11 （2018-4-19）

该版本为安全和修正版本，改进了JSON查询的参数绑定问题和容器类对象实例获取，并包含一处可能的安全隐患，建议更新。

* 支持指定JSON数据查询的字段类型
* 修正`selectInsert`方法
* `whereColumn`方法支持数组方式
* 改进容器类`make`方法
* 容器类`delete`方法支持数组
* 改进`composer`自动加载
* 改进模板引擎
* 修正`like`查询的一处安全隐患

## V5.1.10 （2018-4-16）

该版本为修正版本，修正上一个版本的一些BUG，并增强了`think clear`指令。

* 改进`orderField`方法
* 改进`exists`查询
* 修改cli模式入口文件位置计算
* 修正`null`查询
* 改进`parseTime`方法
* 修正关联预载入查询
* 改进`mysql`驱动
* 改进`think clear`指令 支持 `-c -l -r `选项
* 改进路由规则对`/`结尾的支持

## V5.1.9 （2018-4-12）

该版本主要是一些改进和修正，并包含一个安全更新，是一个推荐更新版本。

* 默认模板渲染规则支持配置保持操作方法名
* 改进`Request`类的`ip`方法
* 支持模型软删除字段的默认值定义
* 改进路由变量规则对中文的支持
* 使用闭包查询的时候使用`cache(true)` 抛出异常提示
* 改进`Loader`类`loadComposerAutoloadFiles`方法
* 改进查询方法安全性
* 修正路由地址中控制器名驼峰问题
* 调整上一个版本的`module_init`和`app_begin`的钩子顺序问题
* 改进CLI命令行执行的问题
* 修正社区反馈的其它问题

## V5.1.8 （2018-4-5）

该版本主要改进了中间件的域名和模块支持，并同时修正了几个已知问题。

* 增加`template.auto_rule` 参数设置默认模板渲染的操作名自动转换规则
* 默认模板渲染规则改由视图驱动实现
* 修正路由标识定义
* 修正控制器路由方法
* 改进Request类`ip`方法支持自定义代理IP参数
* 路由注册中间件支持数组方式别名
* 改进命令行执行下的`composer`自动加载
* 添加域名中间件注册支持
* 全局中间件支持模块定义文件
* Log日志配置支持`close`参数可以全局关闭日志写入
* 中间件方法中捕获`HttpResponseException`异常
* 改进中间件的闭包参数传入
* 改进分组路由的延迟解析
* 改进URL生成对域名绑定的支持
* 改进文件缓存和文件日志驱动的并发支持

## V5.1.7 （2018-3-28）

该版本主要修正了路由的一些问题，并改进了查询的安全性。

* 支持`middleware`配置文件预先定义中间件别名方便路由调用
* 修正资源路由
* 改进`field`方法 自动识别`fieldRaw`
* 增加`Expression`类
* Query类增加`raw`方法
* Query类的`field`/ `order` 和` where`方法都支持使用`raw`表达式查询
* 改进`inc/dec`查询 支持批量更新
* 改进路由分组
* 改进Response类`create`方法
* 改进composer自动加载
* 修正域名路由的`append`方法
* 修正操作方法的初始化方法获取不到问题

## V5.1.6 （2018-3-26）

该版本主要改进了路由规则的匹配算法，大幅提升了路由性能。并正式引入了中间件的支持，可以在路由中定义或者全局定义。另外包含了一个安全更新，是一个建议更新版本。

* 改进URL生成对路由`ext`方法的支持
* 改进查询缓存对不同数据库相同表名的支持
* 改进composer自动加载的性能
* 改进空路由变量对默认参数的影响
* mysql的`json`字段查询支持多级
* Query类增加`option`方法
* 优化路由匹配
* 修复验证规则数字键名丢失问题
* 改进路由Url生成
* 改进一对一关联预载入查询
* Request类增加`rootDomain`方法
* 支持API资源控制器生成 `make:controller --api`
* 优化Template类的标签解析
* 容器类增加删除和清除对象实例的方法
* 修正MorphMany关联的`eagerlyMorphToMany`方法一处错误
* Container类的异常捕获改进
* Domain对象支持`bind`方法
* 修正分页参数
* 默认模板的输出规则不受URL影响
* 注解路由支持多级控制器
* Query类增加`getNumRows`方法获取前次操作影响的记录数
* 改进查询条件的性能
* 改进模型类`readTransform`方法对序列化类型的处理
* Log类增加`close`方法可以临时关闭当前请求的日志写入
* 文件日志方式增加自动清理功能（设置`max_files`参数）
* 修正Query类的`getPk`方法
* 修正模板缓存的布局开关问题
* 修正Query类`select`方法的缓存
* 改进input助手函数
* 改进断线重连的信息判断
* 改进正则验证方法
* 调整语言包的加载顺序 放到`app_init`之前
* controller类`fetch`方法改为`final`
* 路由地址中的变量支持使用`<var>`方式
* 改进XMLResponse 支持传入编码过的xml内容
* 修正Query类`view`方法的数组表名支持
* 改进路由的模型闭包绑定
* 改进分组变量规则的继承
* 改进`cli-server`模式下的`composer`自动加载
* 路由变量规则异常捕获
* 引入中间件支持
* 路由定义增加`middleware`方法
* 增加生成中间件指令`make:middleware` 
* 增加全局中间件定义支持
* 改进`optimize:config`指令对全局中间件的支持
* 改进config类`has`方法
* 改进时间查询的参数绑定
* 改进`inc/dec/exp`查询的安全性


## V5.1.5 （2018-1-31）

该版本主要增强了数据库的JSON查询，并支持JSON字段的聚合查询，改进了一些性能问题，修正了路由的一些BUG，主要更新如下：

* 改进数据集查询对`JSON`数据的支持
* 改进聚合查询对`JSON`字段的支持
* 模型类增加`getOrFail`方法
* 改进数据库驱动的`parseKey`方法
* 改进Query类`join`方法的自关联查询
* 改进数据查询不存在不生成查询缓存
* 增加`run`命令行指令启动内置服务器
* `Request`类`pathinfo`方法改进对`cli-server`支持
* `Session`类增加`use_lock`配置参数设置是否启用锁机制
* 优化`File`缓存自动生成空目录的问题
* 域名及分组路由支持`append`方法传递隐式参数
* 改进日志的并发写入问题
* 改进`Query`类的`where`方法支持传入`Query`对象
* 支持设置单个日志文件的文件名
* 修正路由规则的域名条件约束 
* `Request`类增加`subDomain`方法用于获取当前子域名
* `Response`类增加`allowCache`方法控制是否允许请求缓存
* `Request`类增加`sendData`方法便于扩展
* 改进`Env`类不依赖`putenv`方法
* 改进控制台`trace`显示错误
* 改进`MorphTo`关联
* 改进完整路由匹配后带斜线访问出错的情况
* 改进路由的多级分组问题
* 路由url地址生成支持多级分组
* 改进路由Url生成的`url_convert`参数的影响
* 改进`miss`和`auto`路由内部解析
* 取消预载入关联查询缓存功能

## V5.1.4 （2018-1-19）

该版本主要增强了数据库和模型操作，主要更新如下：

* 支持设置 `deleteTime`属性为`false` 关闭软删除
* 模型增加`getError`方法
* 改进Query类的`getTableFields`/`getFieldsType`方法 支持表名自动获取
* 模型类`toCollection`方法增加参数指定数据集类
* 改进`union`查询
* 关联预载入`with`方法增加缓存参数
* 改进模型类的`get`和`all`方法的缓存 支持关联缓存
* 支持`order by field`操作
* 改进`insertAll`分批写入
* 改进`json`字段数据支持
* 增加JSON数据的模型对象化操作
* 改进路由`ext`参数检测 
* 修正`rule`方法的`method`参数使用 `get|post` 方式注册路由的问题

## V5.1.3 （2018-1-12）

该版本主要改进了路由及调整函数加载顺序，主要更新如下：

* 增加`env`助手函数；
* 增加`route`助手函数;
* 增加视图路由方法；
* 增加路由重定向方法;
* 路由默认区分最后的目录斜杆（支持设置不区分）;
* 调整公共文件和配置文件的加载顺序（可以在配置文件中直接使用助手函数）；
* 视图类增加`filter`方法设置输出过滤；
* `view`助手函数增加`filter`参数;
* 改进缓存生成指令；
* Session类的`get`方法支持获取多级；
* Request类`only`方法支持指定默认值;
* 改进路由分组;
* 修正使用闭包查询的时候自动数据缓存出错的情况;
* 废除`view_filter`钩子位置;
* 修正分组下面的资源路由;
* 改进session驱动;

## V5.1.2 （2018-1-8）

该版本改进了配置类及数据库类，主要更新如下：

* 修正嵌套路由分组；
* 修正自定义模板标签界定符后表达式语法出错的情况；
* 修正自关联的多次调用问题；
* 修正数组查询的`null`条件查询；
* 修正Query类的`order`及`field`的一处可能的BUG；
* 配置参数设置支持三级；
* 配置对象支持`ArrayAccess`；
* App类增加`path`方法用于设置应用目录；
* 关联定义增加`selfRelation`方法用于设置是否为自关联；

## V5.1.1 （2018-1-3）

修正一些反馈的BUG，包括：

* 修正Cookie类存取数组的问题
* 修正Controller的`fetch`方法
* 改进跨域请求
* 修正`insertAll`方法
* 修正`chunk`方法

## V5.1.0 （2018-1-1）

主要更新如下：

* 增加注解路由支持
* 路由支持跨域请求设置
* 增加`app_dispatch`钩子位置
* 修正多对多关联的`detach`方法
* 修正软删除的`destroy`方法
* Cookie类`httponly`参数默认为false
* 日志File驱动增加`single`参数配置记录同一个文件（不按日期生成）
* 路由的`ext`和`denyExt`方法支持不传任何参数
* 改进模型的`save`方法对`oracle`的支持
* Query类的`insertall`方法支持配合`data`和`limit`方法
* 增加`whereOr`动态查询支持
* 日志的ip地址记录改进
* 模型`saveAll`方法支持`isUpdate`方法
* 改进`Pivot`模型的实例化操作
* 改进Model类的`data`方法
* 改进多对多中间表模型类
* 模型增加`force`方法强制更新所有数据
* Hook类支持设置入口方法名称
* 改进验证类
* 改进`hasWhere`查询的数据重复问题
* 模型的`saveall`方法返回数据集对象
* 改进File缓存的`clear`方法
* 缓存添加统一的序列化机制
* 改进泛三级域名的绑定
* 改进泛域名的传值和取值
* Request类增加`panDomain`方法
* 改进废弃字段判断
* App类增加`create`方法用于实例化应用类库
* 容器类增加`has`方法
* 改进多数据库切换连接
* 改进断线重连的异常捕获
* 改进模型类`buildQuery`方法
* Query类增加`unionAll`方法
* 关联统计功能增强（支持Sum/Max/Min/Avg）
* 修正延迟写入
* chunk方法支持复合主键
* 改进JSON类型的写入
* 改进Mysql的insertAll方法
* Model类`save`方法改进复合主键包含自增的情况
* 改进Query类`inc`和`dec`方法的关键字处理
* File缓存inc和dec方法保持原来的有效期
* 改进redis缓存的有效期判断
* 增加checkRule方法用于单独数据的多个验证规则
* 修正setDec方法的延迟写入
* max和min方法增加force参数
* 二级配置参数区分大小写
* 改进join方法自关联的问题
* 修正关联模型自定义表名的情况
* Query类增加getFieldsType和getTableFields方法
* 取消视图替换功能及view_replace_str配置参数
* 改进域名绑定模块后的额外路由规则问题
* 改进mysql的insertAll方法
* 改进insertAll方法写入json字段数据的支持
* 改进redis长连接多编号库的情况

## RC3版本（2017-11-6）

主要更新如下：

* 改进redis驱动的`get`方法
* 修正Query类的`alias`方法
* `File`类错误信息支持多语言
* 修正路由的额外参数解析
* 改进`whereTime`方法
* 改进Model类`getAttr`方法
* 改进App类的`controller`和`validate`方法支持多层
* 改进`HasManyThrough`类
* 修正软删除的`restore`方法
* 改进`MorpthTo`关联
* 改进数据库驱动类的`parseKey`方法
* 增加`whereField`动态查询方法
* 模型增加废弃字段功能
* 改进路由的`after`行为检查和`before`行为机制
* 改进路由分组的检查
* 修正mysql的`json`字段查询
* 取消Connection类的`quote`方法
* 改进命令行的支持
* 验证信息支持多语言
* 修正路由模型绑定
* 改进参数绑定类型对枚举类型的支持
* 修正模板的`{$Think.version} `输出
* 改进模板`date`函数解析
* 改进`insertAll`方法支持分批执行
* Request类`host`方法支持反向代理
* 改进`JumpResponse`支持区分成功和错误模板
* 改进开启类库后缀后的关联外键自动识别问题
* 修正一对一关联的JOIN方式预载入查询问题
* Query类增加`hidden`方法

## RC2版本（2017-10-17）

主要更新如下：

* 修正视图查询
* 修正资源路由
* 修正`HasMany`关联 修正`where`方法的闭包查询
* 一对一关联绑定属性到父模型后 关联属性不再保留
* 修正应用的命令行配置文件读取
* 改进`Connection`类的`getCacheKey`方法
* 改进文件上传的非法图像异常
* 改进验证类的`unique`规则
* Config类`get`方法支持获取一级配置
* 修正count方法对`fetchSql`的支持
* 修正mysql驱动对`socket`支持
* 改进Connection类的`getRealSql`方法
* 修正`view`助手函数
* Query类增加`leftJoin` `rightJoin` 和 `fullJoin`方法
* 改进app_namespace的获取
* 改进`append`方法对一对一`bind`属性的支持
* 改进关联的`saveall`方法的返回值
* 路由标识设置异常修复
* 改进Route类`rule`方法
* 改进模型的`table`属性设置
* 改进composer autofile的加载顺序
* 改进`exception_handle`配置对闭包的支持
* 改进app助手函数增加参数
* 改进composer的加载路径判断
* 修正路由组合变量的URL生成
* 修正路由URL生成
* 改进`whereTime`查询并支持扩展规则
* File类的`move`方法第二个参数支持`false`
* 改进Config类
* 改进缓存类`remember`方法
* 惯例配置文件调整 Url类当普通模式参数的时候不做`urlencode`处理
* 取消`ROOT_PATH`和`APP_PATH`常量定义 如需更改应用目录 自己重新定义入口文件
* 增加`app_debug`的`Env`获取
* 修正泛域名绑定
* 改进查询表达式的解析机制
* mysql增加`regexp`查询表达式 支持正则查询
* 改进查询表达式的异常判断
* 改进model类的`destroy`方法
* 改进Builder类 取消`parseValue`方法
* 修正like查询的参数绑定问题
* console和start文件移出核心纳入应用库
* 改进Db类主键删除方法
* 改进泛域名绑定模块
* 取消`BIND_MODULE`常量 改为在入口文件使用`bind`方法设置
* 改进数组查询
* 改进模板渲染的异常处理
* 改进控制器基类的架构方法参数
* 改进Controller类的`success`和`error`方法
* 改进对浏览器`JSON-Handle`插件的支持
* 优化跳转模板的移动端显示
* 修正模型查询的`chunk`方法对时间字段的支持
* 改进trace驱动
* Collection类增加`push`方法
* 改进Redis Session驱动
* 增加JumpResponse驱动


## RC1（2017-9-8）

主要新特性为：

* 引入容器和Facade支持
* 依赖注入完善和支持更多场景
* 重构的（对象化）路由
* 配置和路由目录独立
* 取消系统常量
* 助手函数增强
* 类库别名机制
* 模型和数据库增强
* 验证类增强
* 模板引擎改进
* 支持PSR-3日志规范
* RC1版本取消了5.0多个字段批量数组查询的方式