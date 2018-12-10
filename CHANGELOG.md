 
## 2018-12-9 V5.0.23

本次版本更新主要涉及一个安全更新，推荐尽快更新到最新版本。

* Query支持调用模型的查询范围
* 聚合查询字段支持`DISTINCT`
* 改进闭包验证的参数
* 多对多关联支持指定中间表数据名称
* after/before验证支持指定字段验证
* 改进多对多关联
* 改进验证类
* 增加`afterWith`和`beforeWith`验证规则 用于比较日期字段
* 完善规则提示
* 改进断线重连
* 修正软删除的`destroy`方法
* 修复模型的`save`方法当`data`变量为空 数据不验证
* 模型增加`replace`方法
* MorphOne 增加 make 方法创建关联对象实例
* 改进`count`方法返回值类型
* 改进聚合查询方法的正则判断
* 改进`sqlsrv`驱动
* 完善关联的`save`方法
* 修正控制器名获取


## 2018-10-22 V5.0.22

该版本主要增加了JSON日志格式的支持，并且包含了一个安全更新。

* 调试模式下关闭路由解析缓存
* 改进Log类支持`json`日志格式
* 改进聚合查询的安全性
* 改进`count`查询的返回值类型

## 2018-9-7 V5.0.21

该版本主要做了一些已知问题的修正，改进了对Swoole的支持，以及增加路由解析缓存功能。

* 增加路由解析缓存功能
* 改进url生成的端口问题
* 改进缓存驱动
* 改进value方法的缓存处理
* 修正Builder类的insertAll方法
* 改进对Swoole的支持（使用参考：[xavier-swoole](https://github.com/xavieryang007/xavier-swoole)）

## 2018-5-11 V5.0.20

该版本为修正版本，修正了一些已知的问题。

* `join`方法的条件支持传入`Expression`对象
* 改进驱动的`parseKey`方法
* 改进Request类的`host`方法
* 使用`exp`表达式更新数据的异常提示
* 修正查询
* 改进多对多关联的中间表模型更新

## 2018-4-25 V5.0.19

该版本属于改进版本，主要改进了composer自动加载及内置模板引擎的一处可能的安全隐患。

* 改进composer自动加载
* 改进模板引擎一处安全隐患
* 改进`comment`方法解析
* 改进分布式写入数据后及时读取的问题
* 改进url操作方法的自动转换
* 改进分页类魔术方法的返回值
* SQL日志增加主从标记

## 2018-4-14 V5.0.18

该版本主要修正上一个发布的一些BUG，并且改进了`exp`表达式查询/写入的严谨性。

* 修正`field`方法`*`兼容问题；
* 修正`inc/dec`方法；
* 修正`setInc/setDec`方法；
* 改进`insertAll`方法；
* 改进`parseTime`方法；
* 改进`exp`表达式查询/写入的严谨性；

## 2018-4-12 V5.0.17

该版本主要是一些修正和改进，并且包含了一个安全更新。

* 改进Response类`create`方法
* 改进`inc/dec`查询
* 默认模板渲染规则支持直接使用操作方法名
* 改进视图驱动
* 改进Request类ip方法 支持代理设置
* 修正request类的`create`方法
* 闭包查询使用`cache(true)`抛出异常
* 改进composer自动加载文件
* 增加`Expression`类及相关方法

## 2018-3-26 V5.0.16

该版本主要做了一些修正和改进，由于包含了一个安全更新，是一个推荐更新的版本。

* 改进Url生成
* 改进composer自动加载性能
* 改进一对一查询
* 改进查询缓存
* 改进field方法
* 优化Template类
* 修正分页参数
* 改进默认模板的自动识别
* 改进Query类查询
* Collection类改进
* 改进模型类`readTransform`方法对序列化类型的处理
* 改进trace显示
* 文件日志支持自动清理
* 改进断线重连的判断
* 改进验证方法
* 修正Query类view方法的数组表名定义
* 改进参数绑定
* 改进文件缓存的并发删除
* 改进`inc/dec/exp`更新的安全性
* 增加控制台配置

## 2018-1-31 V5.0.15

该版本主要进行了一些修正和完善

* 改进View类
* 改进chunk方法
* 改进模板引擎的表达式语法
* 改进自关联查询多级调用问题
* 关联定义增加`selfRelation`方法用于设置是否自关联
* 改进file类型的缓存`inc`和`dec`方法不改变缓存有效期
* 改进软删除 支持设置`deleteTime`属性关闭
* 改进`union`查询
* 改进查询缓存
* 优化File缓存自动生成空目录的问题
* 改进日志写入并发问题
* 修正`MorphTo`关联
* 改进`join`自关联查询
* 改进`case`标签解析
* 改进Url类对`url_convert`配置的支持


## 2018-1-1 V5.0.14

V5.0.14版本主对复合主键进行了更多支持，改进了PHP7的兼容性，并且对数据库的一些问题做了改进。

主要更新如下：

* 改进Validate类的unique验证
* Validate类增加checkRule方法用于静态验证多个规则
* 改进多对多关联的save方法
* 改进多对多的pivot对象
* 修正setDec方法的延迟写入
* max和min方法增加第二个参数用于设置是否强制转换数字
* 改进View类
* 改进join关联自身的问题
* 改进union查询
* 改进Url类
* 改进同名路由不同请求的注册
* 改进Builder类parseData对空数组的判断
* 改进模板替换
* 调整BelongsTo的hasWhere方法
* 改进模板的编译缓存命名规则 增加布局模板的标识
* 改进insertall方法
* 改进chunk方法支持复合主键
* 改进Error类的一个兼容问题
* 改进model类的save方法的复合主键包含自增的情况
* save方法改进复合主键的支持
* 改进mysql的insertAll方法
* 改进redis长连接多编号库的情况

## 2017-12-12 V5.0.13

`V5.0.13`主要是对模型和日志方面做了一些改进

### [数据库和模型]

* 改进Model类`save`方法对`oracle`的支持
* 改进中间表模型的实例化
* 改进`Pivot`类
* 模型`saveall`方法支持配合`isUpdate`方法
* 模型类增加`force`方法设置是否强制更新所有数据
* 关联自动删除增加一对多关联删除支持
* 改进`hasWhere`查询的数据重复问题
* 改进一对多`with`关联查询的`field`支持
* 模型`saveall`方法支持返回数据集 读取`resultSetType`属性
* 改进废弃字段判断
* 模型的`hasWhere`方法增加`fields`参数
* 改进断线重连异常捕获机制
* 修正Query类的`inc`和`dec`方法的Mysql关键词问题
* 修正数据集对象的BUG

### [其它]

* 增加`app_dispatch`钩子位置
* cookie类`httponly`参数默认改为false
* File日志驱动增加`single`参数配置是否记录单个文件日志
* 单个日志文件支持大小设置
* 改进日志记录的ip地址
* Redis缓存驱动改用`serialize`序列化替代json序列化
* 改进异常捕获
* 改进上传文件验证
* 修正redis驱动
* 改进File缓存的`clear`方法
* 代码格式化规范
* 改进一处PHP7.2的兼容问题
* 调试模式下不读取字段缓存文件
* `default_filter`支持在模块中配置生效

## 2017-11-06 V5.0.12

5.0.12是一个修正版本，包含了上个版本发布以来的一些修正和完善，主要包括：

* 上传类和验证类的多语言支持；
* 模型增加排除和废弃字段支持；
* 改进insertAll方法的分批处理；
* 改进对枚举类型的参数绑定支持；
* 修正社区反馈的问题；


### [数据库和模型]

* 改进Connection类的getRealSql方法
* 改进append方法支持一对一关联的bind设置
* 改进whereTime查询
* 改进model类的`destroy`方法
* 修正softdelete
* 修正`chunk`方法对时间字段的支持
* Collection类增加`push`方法
* 改进alias方法 
* 修正模型类的`append`处理
* 改进`appendRelationAttr`方法
* 改进HasManyThrough关联
* 改进MorphTo关联
* 模型增加废除字段`disuse`定义
* 增加排除字段方法`except`
* 修正`has`方法 
* 改进参数绑定类型对枚举类型的支持
* 改进`insertAll`方法的分批处理


### [其它]

* 改进Loader类`controller`和`validate`方法支持多层
* 验证提示信息支持多语言
* File类错误信息支持多语言 
* 模板渲染异常处理
* 修正rest控制器
* 改进trace驱动
* 改进Cache类的`remember`方法 
* 改进`url_common_param`的情况下urlencode的问题
* 改进Url类
* 改进`exception_handle`配置参数对闭包的支持 
* 执行路由缓存命令前检测RUNTIME_PATH是否存在 
* 调整部分`CacheDriver::dec`在为空的时候递减的行为
* 优化移动端的显示
* 改进对JSON-Handle插件的支持
* 改进redis的`get`方法
* 改进Request类的`host`方法


## 2017-09-08 V5.0.11

5.0.11是一个安全及修正版本，包含了上个版本发布以来的一些修正和完善，更新了几处可能的安全问题，主要包括：

* 完善缓存驱动；
* 改进数据库查询；
* 改进URL生成类；
* 缓存有效期支持指定过期时间；

### [数据库和模型]

* 改进数据库驱动类
* 改进`group`方法的字段关键字冲突
* 修正聚合查询返回null的问题
* 改进Db类的强制重连
* 改进关联的属性绑定
* 修正事务的断线重连
* 修正对象的条件查询
* Db类增加`clear`方法
* 改进数组查询条件中的`null`查询
* 改进Query类的`chunk`方法支持排序设置
* 改进HasOne和HasMany关联的`has`方法
* 改进软删除的关联删除
* 改进一个字段多次查询条件

### [其它]

* 缓存有效期支持指定过期时间（`DateTime`）；
* 改进Url生成对端口号的支持
* 改进`RouteNotFound`异常提示
* 改进路由分组的全局完整路由匹配
* 修正部分验证规则的错误提示问题
* 支持数据集和模型的XML响应输出
* 改进模板的三元运算标签
* 改进控制器不存在的错误提示
* input助手函数支持`route`变量获取
* 支持在配置文件中读取额外配置参数
* 完善分页类
* 修复Trait命名空间重复问题
* 修正Request类的env方法
* 优先使用Cookie中的多语言设置
* 获取缓存标签的时候过滤无效的缓存标识
* 修正路由批量注册的一个BUG
* `exception_handle`配置参数支持使用闭包定义`render`处理
* 请求缓存支持缓存标签设置
* 缓存类`remember`方法增加并发锁定机制
* 改进上传类对`swf`的支持
* 改进Session类的`prefix`方法

## 2017-07-04 V5.0.10

5.0.10是一个修正版本，并包含了一个安全更新，推荐更新，主要包含：

* 数据库和模型的多处改进
* 添加新的行为监听
* 路由支持Response设置
* 改进调试模式下数据库敏感信息暴露

### [数据库和模型]

* 修正join其他表时生成的delete语句错误
* 修正远程一对多
* insertall支持replace
* 修正多对多默认的中间表获取
* 改进更新后的模型`update_time`数据更新
* model类增加`removeRelation`方法
* 模型类增加`setInc`和`setDec`方法
* 模型类增加`autoWriteTimestamp`方法动态设置时间字段写入
* 改进驱动类方法的断线重连判断
* 改进多对多的数据更新
* 改进BelongsToMany关联查询
* 修正Query类的value和column方法
* 改进in查询的去重问题
* 修正模型类的scope方法传值问题
* 调整模型的save方法`before_update`检查位置
* 修改器和获取器方法支持第三个关联数据参数

### [其它]

* 默认关闭调试模式
* 修复配置extra目录临时文件的错误加载
* 添加log存储完成行为监听 `log_write_done`
* 改进Build类生成公共文件的目录判断
* 增加`response_send`行为监听 
* 路由增加response参数用于绑定response处理行为
* 改进redirect的参数传入
* 改进环境变量的布尔值读取
* 改进Url类的域名传入
* 修正命令行文件生成
* 改进命令行下面的URL生成
* 添加`app_host`参数设置默认的URL根地址
* 改进`Request`类`isSsl`方法判断支持CDN
* 增加`record_trace`配置参数用于日志记录trace信息

## 2017-05-20 V5.0.9

5.0.9是一个修正版本，推荐更新，主要更新包含：

### [数据库和模型]

* 修正关联自动写入
* 修正模型数据变化判断对为空数据的支持
* 修正Query类的useSoftDelete方法返回值
* 修正一对一嵌套关联数组定义的问题
* 修正使用了手动参数绑定的时候的缓存BUG
* 改进数据库类的一处不能嵌套查询的缺陷
* 改进数据库断线重连判断
* 改进模型的appendRelationAttr方法
* 改进模型类destroy方法传入空数组的时候不进行任何删除操作
* 改进一对多关联数据的输出
* 改进模型的save方法对allowField方法的支持
* 改进分页类的toarray方法 增加总页数
* 比较运算增加闭包子查询支持
* db助手函数默认不再强制重新连接
* 改进belongsToMany的查询字段指定
* 分页类增加each方法

### [其它]

* 修正路由分组的路由规则大小写识别问题
* 修正命令行的日志切割生成
* 修复URL生成时路由规则中的参数没有进行 urlencode
* 改进Request类的filter过滤机制 支持正则
* 改进Response类支持手动设置contentType
* 修正异常模板中助手函数未定义错误

## 2017-04-28 V5.0.8

### 主要调整

* 改进关联模型
* 改进日志记录
* 增加多态一对一关联
* 修正社区反馈的一些BUG

### [ 请求和路由 ]

* 修正Request类`cookie`方法对前缀的支持
* 改进全局请求缓存的缓存标识
* 改进Request类`param`方法
* 修正别名路由

### [ 模型和数据库 ]

* 改进模型数据的更新检查
* 改进Query类的`column`方法
* 改进软删除条件在使用闭包查询情况下多次生成的问题
* belongsToMany增加数据同步方法
* 查询范围支持静态调用
* 增加多态一对一（MorphOne）关联
* 改进BelongsTo关联
* 改进多态关联支持关联数据添加和注销
* 改进多对多关联，支持中间表模型自定义 并且定义的时候不需要使用完整表名
* 改进浮点数类型转换避免出现逗号
* 调整关联模型的save方法返回值
* 模型类的get方法第一个参数必须 如果传入null则返回null
* model的save方法改进如果数据没有更新不执行
* Query增加`useSoftDelete`方法可以单独设置软删除条件
* 重载BelongsToMany的`selectOrFail`和`findOrFail`方法
* 重载BelongsToMany的`select` 、`find`和 `paginate`方法
* 增加模型和`Pivot`对象的`parent`属性
* 多对多关联支持设置中间表模型
* 改进Query类的`view`方法中字段的关键字问题
* 主从数据库的时候开启事务始终操作主库

### [ 其它 ]

* 改进Cookie类的`get`方法支持获取全部
* `schema`指令增加`config`参数，支持传入数据库连接配置
* 改进cache类的`store`方法为当次有效
* 修正cache助手函数对`option`传参的支持
* 修复`optimize:autoload`命令在`EXTEND_PATH`目录不存在的情况下，类库映射生成错误问题
* 支持自定义的根命名空间也可以生成类库映射缓存
* 验证字段比较支持对比其他字段
* 修复`Session::prefix('xxx');`设置当前作用域BUG
* 改进`optimize::schema`指令
* 修复`clear`指令无法删除多级目录下文件的问题
* 改进默认语言读取和自动侦测
* 改进日志记录格式 并且命令行下面日志改为实时写入
* 修正模板标签默认值某些情况无效bug
* 改进Url生成对完整域名的支持
* 改进`Clear`指令不删除`.gitignore` 文件
* 修复Memcache缓存驱动的`inc`方法

### 调整

* 如果自定义了应用的命名空间的话，原来的`app_namespace`配置参数改为`APP_NAMESPACE`常量在入口文件中定义
* 多对多关联的中间表名称不需要添加表前缀
* 模型的scope方法之后只能使用数据库查询方法而不能使用模型的方法

## 2017-02-24 V5.0.7

### 主要调整

本次更新主要为BUG修正和改进，主要改进如下：

* 改进全局请求缓存对子域名的支持；
* 改进数据缓存自动更新机制；
* 关联统计支持指定统计属性名；
* 模型嵌套关联支持数组方式；
* HasOne关联支持`has`和`hasWhere`方法；
* 路由的`ext`和`deny_ext`参数允许设置为空（表示不允许任何后缀或者必须使用后缀访问）；

### 修正如下

* 修正 IN / NOT IN 型查询条件为空导致的 sql 语法错误
* 修正分页类的`toArray`方法对简洁模式的支持
* 修正Model类`delete`方法对多主键的处理
* 修正软删除对`Mongodb`的支持
* 修正`Connection`类一处可能的错误
* 改进Query类的find方法的缓存机制
* 修正BelongsTo关联
* 修正JOIN方式一对一关联预载入闭包查询
* 修正Query类的`insert`方法一处可能存在的警告错误
* 修正Model类一处Collection的`use`冲突
* 修正Model类`hasWhere`方法
* 修正URl生成对`ext`参数的支持
* 文件缓存`clear`方法会删除空目录
* 修正Route类的`parseUrlPath`方法一处问题

### 调整如下

* 默认关闭session的安全参数`secure`，此选项仅能在HTTPS下设置开启

## 2017-02-07 V5.0.6

### 主要调整：

本次更新主要为BUG修正及优化（可无缝升级）：

* 数据库支持断线重连机制；
* 改进查询事件的回调参数；
* 改进数据自动缓存机制；
* 增加时间字段自动格式转换设置；
* `MongoDb`和`Oracle`扩展更新至最新核心框架；

### [数据库和模型]

* 修正hasMany关联的`has`方法
* 去除一些数据库惯例配置 避免使用数据库扩展的时候影响
* 改进多对多的`attach`方法的返回值
* 增加Mysql的断线重连机制和开关
* 改进Query类的`find`方法数据缓存机制
* 改进Query类查询事件的回调参数
* 改进Query类的自动缓存更新
* Model类增加`readonly`方法
* 改进Model类的`has`和`hasWhere`方法
* 改进模型类的`get`和`all`方法 第二个参数为true或者数字表示缓存参数
* 修复闭包查询条件为空导致的 sql 语法错误
* 改进Query类的`setBuilder`方法 避免因自定义连接器类后找不到生成器类
* 删除Connection类废弃属性`resultSetType`
* 优化Connection类`close`方法
* 修正Connection类的`bindParam`方法对存储过程的支持
* 数据库配置参数`datetime_format` 设置为`false`表示关闭时间字段自动转换输出
* 改进软删除的数据库兼容性问题 支持`Mongodb`

### [其它]

* 改进Url类生成 `root`为`/`的情况
* redirect助手函数和controller类的redirect方法增加with参数
* 全局请求缓存添加排除规则 添加request_cache_except配置参数
* Cache类store方法参数允许为空 表示获取当前缓存驱动句柄
* 改进Validate类的ip验证规则

## 2017-01-23 V5.0.5
### 主要调整：

本次更新主要改进了数据访问层和模型关联：

* 增加快捷查询及设置方法；
* 增加关联统计功能；
* 增加关联查询延迟预载入功能；
* 增加关联一对一自动写入和删除；
* 改进存储过程查询；
* 改进关联数据输出；
* 优化查询性能；
* 模型时间字段自动格式化输出；

### [请求和路由]

* 改进路由定义的后缀检测
* Route类的`rest`方法支持覆盖定义
* 改进Request类的`put`和`post`方法对`json`格式参数的接收
* Request类增加`contentType`方法
* 改进Route类`setRule`方法 
* 改进Request类的`create`方法
* 改进路由到控制器类的方法对默认渲染模板的影响
* 修正Url类`build`方法定义路由别名后的BUG

### [数据库和模型]

* 增加关联统计功能
* 增加一对一关联自动写入功能
* 修正聚合模型的`delete`方法
* 改进Model类的`useGlobalScope`方法
* Model类的日期类型支持设置为类名
* Query类增加`data`/`inc`/`dec`/`exp`方法用于快捷设置数据 `insert`和`update`方法参数可以为空 读取`data`设置数据
* 优化Connection的查询性能
* 修正Builder类的`parseOrder`方法
* 修正BelongsToMany类的`attach`方法
* BelongsToMany类的`attach`方法改进 支持批量写入
* 改进BelongsToMany类的`saveall`方法 增加第三个参数 用于指定额外参数是否一致
* Query类的`order`方法支持多次调用合并
* 改进`count`方法对`group`查询的支持
* 增加时间戳自动写入的判断
* 改进Model类`writeTransform`方法
* 改进Model的时间戳字段写入和读取
* 写入数据为对象的时候检测是否有`__toString`方法
* 改进Mysql驱动的`getFields`方法
* 改进自动时间字段的输出
* `like`查询条件支持数组
* 自动时间字段的获取自动使用时间格式化
* 改进单个字段多次Or查询情况的查询
* 修正`null`查询的条件合并
* 改进Query类`paginate`方法第一个参数可以使用数组参数
* 改进数据集对象的返回，由Query类的select方法进行数据集转换，原生查询不再支持返回数据集对象
* 增加`whereNull`、`whereIn`等一系列快捷查询方法
* `fetchPdo`方法调整
* 改进对存储过程调用的支持 改进`getRealSql`的调用机制 改进数据表字段使用中划线的参数绑定支持
* 数据库配置参数增加`result_type` 用于设置数据返回类型 方法参数名称调整
* 改进Query类的`whereTime`方法支持更多的时间日期表达式（默认查询条件为大于指定时间表达式）
* 取消`min`/`max`/`sum`/`avg`方法的参数默认值
* Query类增加`getPdo`方法用于返回`PDOStatement`对象
* 改进`today`的日期表达式查询
* 改进关联属性的获取
* 改进关联定义中包含查询条件后重复执行的问题
* 改进参数绑定支持中文字段自动绑定
* 改进Builder类的`insertall`方法 增加对null和对象数据的处理
* 改进参数绑定类型 支持`bit`类型自动绑定
* Connection类`model`方法更改为`getQuery`
* 优化Connection类`__call`方法
* 修正聚合模型
* 一对一关联预载入默认改为IN查询方式
* 增加`collection`助手函数用于数据集转换
* 增加`load_relation`助手函数用于数组的延迟预载入
* 改进Model类的`has`方法第二个参数支持使用数组和闭包，无需再使用`hasWhere`
* `relation`方法支持嵌套关联查询
* 增加`think\model\Collection`作为模型的数据集查询集合对象
* 取消关联定义的`alias`参数（仅`morphTo`保留）
* Model类的`delete`方法，支持没有主键的情况
* Model类的`allowField`方法支持逗号分割的字符串
* 改进写入数据的自动参数绑定的参数名混淆问题
* 关联预载入查询的属性名默认使用小写+下划线命名
* Query类的`with`和`relation`方法支持多次调用
* Collection类增加`hidden`、`visible`和`append`方法
* 修正软删除的强制删除方法

### [其它]

* `unique`验证规则支持指定完整模型类 并且默认会优先检测模型类是否存在 不存在则检测数据表
* 改进`Loader`类的`model`、`controller` 和 `validate`方法 支持直接传入类名实例化
* `Session`类增加安全选项`httponly`和`secure`
* 可以允许自定义`Output`的driver，以适应命令行模式下调用其它命令行指令 
* 改进`loader`类`action`的参数污染问题
* Validate类的`confirm`验证改为恒等判断
* 改进`Validate`类的错误信息处理
* 修正`Validate`类的布尔值规则验证
* 改进`cookie`助手函数对前缀的支持
* 文件缓存默认开启子目录缓存避免文件过多导致性能问题

### [调整]
* Connection类`model`方法更改为`getQuery`
* 原生查询不再支持返回数据集对象
* 分页查询返回类型变成`think\Paginator`（用法不变）
* 模型的时间日期字段会自动进行格式化输出，不需要进行额外处理。
* Session类添加了`secure`和`httponly`参数，并且默认是true

## 2016-12-20 V5.0.4
### 主要调整：

* 关联模型重构并增加多态一对多关联；
* 数据库支持一个字段多次调用不同查询条件；
* 增加数据库CURD事件支持；
* 路由到类和控制器的方法支持传入额外参数；
* 支持全局模板变量赋值；
* 模型支持独立设置查询数据集对象；
* 日志针对命令行及调试做出改进；
* 改进Hook类的行为方法调用

### [请求和路由]
* 请求缓存支持模块单独开启
* Request类`post`方法支持获取`json`方式的请求数据
* 路由到类的方法和控制器方法 支持传入额外参数，用于方法的参数
* 改进控制器自动搜索的目录规范
* 改进请求缓存
* 改进自动参数绑定
* 修正路由的请求缓存设置
* 改进Route类name方法

### [数据库和模型]
* 增加数据库查询（CURD）事件
* 改进多表更新的字段不存在问题
* 改进Model类的`useGlobalScope`方法
* 修正子查询作为表名查询的问题
* Model类增加`resultSetType`属性 用于指定模型查询的数据集对象（默认为空返回数组） 
* Model类增加`toCollection`方法（自动调用）
* 关联模型架构调整
* 改进预载入`with`方法的参数支持小写和下划线定义
* 修正关联多对多一处错误
* 改进关联多对多的查询
* 关联模型支持多态一对多关联
* 预载入关联查询支持关联对象属性绑定到当前模型
* 支持追加关联对象的属性到当前模型数据
* 一对一关联预载入支持JOIN和IN两种方式（默认为JOIN）
* 改进多对多查询
* 改进模型更新的数据变化比较规则
* 查询支持一个字段多次查询条件
* 改进sql日志的sql语句
* 修正`join`自身表的别名覆盖问题
* 模型类的`connection`属性和数据库默认配置合并
* 改进`in`和`between`查询条件的自动参数绑定
* 改进Query类对数据集对象以及关联字段排序的支持
* 增加模型的快捷事件方法
* 改进Query类的`getTableInfo`方法缓存读取
* model类的`saveAll`方法支持调用`allowField`方法进行字段过滤
* 修正关联查询的时候 `whereTime`方法的bug
* 改进Query类的聚合查询
* table方法支持字符串方式的子查询
* 修正`count` `avg`方法使用`fetchsql`无法正确返回sql的问题

### [其它]
* 改进命令行下的日志记录
* 部署模式下简化日志记录
* 增加debug日志类型 仅限调试模式记录
* 改进Template类`parseTemplateFile`方法
* 改进Validate类的`getRuleMsg`方法
* 控制器的`error`方法在AJAX请求默认返回url为空
* Validate类架构方法增加`field`参数 用于设置验证字段的描述
* 改进App类`invokeMethod`方法对架构函数依赖注入的支持
* 增加RedirectResponse的`restore`方法返回值
* View类增加`share`静态方法 用于静态赋值模板变量
* 验证类增加`hasScene`方法判断是否存在某个场景的验证配置
* 修正redis和session驱动的`destroy`方法返回值
* 空操作方法的参数传入去掉操作方法后缀
* 在控制器中调用request和view增加类型提示
* 改进`input`助手函数支持多维数据获取
* Cache类增加`pull`和`remember`方法
* 改进验证类的`confirm`验证规则 支持自动规则识别
* 改进验证类的错误信息定义
* 增加Validate类自定义验证错误信息的替换规则
* Cookie类增加`forever`方法用于永久保存
* 模板渲染支持从视图根目录读取模板
* 改进Hook类的exec方法

### [调整]
* Db类查询不再支持设置自定义数据集对象
* 废除Query类的`fetchClass`方法
* 控制器的`error`方法在AJAX请求默认返回的url为空
* 关联方法定义不支持使用小写下划线，必须使用驼峰法
* 行为类的方法必须使用驼峰法命名

## 2016-11-11 V5.0.3
### 主要调整：
* 请求缓存增强；
* 路由增强；
* 数据库和模型完善；
* 支持反射的异常捕获；
* File类改进；
* 修正社区反馈的一些BUG；

### [ 请求和路由 ]

* 资源路由自动注册的路由规则的时候会记录当前使用的资源标识；
* 增强请求缓存功能和规则定义，支持全局自动缓存
* 修正控制器自动搜索的大小写问题
* 修正路由绑定到命名空间后 类的自动定位
* 改进Route类的parseRule方法 路由地址中的变量替换不自动去除路由变量
* 改进控制器自动搜索
* Route类增加setOption和getOption方法 用于记录当前路由执行过程中的参数信息
* 优化路由分组方法
* 改进分组路由的url生成

### [ 数据库和模型 ]

* 一对一关联查询方法支持定义`field`方法
* 聚合模型支持设置`field`属性
* 改进Query类的`alias`方法
* 改进Query类`join`和`view`方法的table参数
* 改进Query类`where`方法
* 改进Query类的`paginate`方法，支持`order`方法
* 改进Query类的`min`和`max`方法支持日期类型
* 修正软删除`withTrashed`方法
* 优化Connection类的`getRealSql`方法生成的sql

### [ 其它 ]
* 增加request_cache和request_cache_expire配置参数用于配置全局请求缓存；
* 修正input助手函数的数组过滤
* cache助手函数支持清空操作
* 改进Config类load方法 一级配置名称强制转为小写
* 修正Url多次生成的问题
* File类修正某些环境下面无法识别上传文件的问题
* 改进App类的空操作方法调用
* 域名部署URL生成不依赖 url_domain_deploy 配置参数
* 修正Url类域名部署的问题
* 视图文件目录支持集中式存放 不放入模块目录
* cache助手函数支持 remember方法
* Request类的input方法或者input助手函数的`filter`参数支持传入null 表示不过滤

## 2016-10-24 V5.0.2
### 主要调整：

* 数据库和模型完善；
* 路由功能完善；
* 增加`yaml`配置格式支持；
* 依赖注入完善；
* Session类完善；
* Cookie类完善；
* Validate类完善；
* 支持反射类的异常捕获；
* 修正社区反馈BUG；

### [ 请求和路由 ]
* 依赖注入的类如果定义了`invoke`方法则自动调用
* Request类的`header`方法增加自定义header支持
* Request类禁止直接实例化调用
* 改进Request类ip方法
* 路由变量规则支持闭包定义
* 路由参数增加`ajax`和`pjax`判断
* 别名路由增加允许和排除操作
* 改进路由域名绑定后的url生成
* 路由生成改进对路由到类的支持
* 路由生成支持`url_param_type`配置参数
* 路由生成支持别名路由
* Route重定向规则支持更多` schema`
* 别名路由支持定义单独方法的请求类型
* 改进路由分组的url生成
* 路由规则的组合变量支持可选分隔符定义
* 改进路由合并参数的获取
* 路由规则支持单独设置url分隔符，路由参数为 `param_depr`
* 自动搜索控制器支持自定义访问控制器层的情况
* 改进路由标识不区分大小写
* 改进路由地址是否定义过路由规则的检测

### [ 数据库和模型 ]
* 改进Query类的join方法
* 改进Query类分页方法的参数绑定
* 修正软删除方法
* 修正Query类parseOrder方法一处错误
* 修正sqlsrv驱动parseOrder方法
* 修正Query类setInc和setDec方法
* 改进Model类的save方法支持非自增主键的处理
* 整型字段的参数绑定如果为空写入默认值0
* 改进Model类has和hasWhere方法
* 改进Query类的value方法缓存判断
* 改进Query类join方法对子查询支持
* 改进Query类的table方法和alias方法用法
* 关联预载入支持`hasOne`自关联
* 改进Builder类的parseKey方法
* 改进Builder类的join/alias/table方法的解析
* 改进全局查询范围
* 改进Query类的聚合查询方法的返回值
* 改进关联属性的读取
* 改进聚合模型主键和关联键相同的情况
* 改进模型在开启`class_suffix`参数情况下的name属性的识别

### [ 其它 ]
* Cache类增加`remember`方法 用于当获取的缓存不存在的时候自动写入
* Session类增加`flash`方法用于设置下一次请求有效的值 
* Session类增加`flush`方法用于清空当前请求有效的值 
* Session类增加`push`方法用于更新数组数据
* 增加yaml配置格式支持
* 改进App类的反射异常无法捕获问题
* 修正session助手函数的清空操作
* 改进验证类的`image`方法
* 改进验证类的`activeUrl`方法 
* 改进自定义验证规则的使用
* 改进控制器自动搜索后的控制器名获取
* 修正import方法加载extend目录类库
* 修正json_encode时 "Failed calling XXX::jsonSerialize()" 的异常
* 改进Loader类model和validate方法的单例问题
* 改进方法执行的日志记录
* 改进模板引擎的Think变量解析
* 改进Lang类`load`方法
* 验证错误信息支持多语言读取
* 改进ROOT_PATH常量
* 改进语言包加载
* 改进模板session和cookie变量获取，自动判断前缀
* 缓存驱动统一增加handler方法用于获取操作对象的句柄（某些缓存类型可能为null）
* File类增加`__call`方法用于兼容5.0版本的`md5`和 `sha1`方法
* 改进文件缓存驱动的`clear`方法
* Lang类增加`setLangCookieExpire`方法设置多语言cookie过期时间
* 增加`route_complete_match`配置参数

### [ 调整 ]
下列模型属性和方法由原来的静态（static）定义改为动态定义：
* 聚合模型的`relationModel`属性
* Model类的`useGlobalScope `属性
* 全局查询范围方法`base`改为动态方法
* 软删除属性 `deleteTime`属性


## 2016-9-28 V5.0.1
### 主要调整：
* [依赖注入](215849)完善；
* [扩展配置](118027)文件位置调整；
* 新增数据表[字段缓存命令](211524)；
* 支持设置当前的查询对象；
* 支持[请求和路由缓存](215850)；

### [ 请求和路由 ]
* 改进Controller类的`success`和`error`方法的跳转地址识别 支持更多Scheme
* 操作方法和架构方法支持任何对象自动注入
* Requesst类增加`getInput`方法 用于获取` php://input`值
* 路由到方法的时候 支持架构方法注入请求对象
* 改进Route类路由到类的判断
* Request增加`cache`方法，支持请求缓存
* 绑定到模块后 路由依然优先检查
* 路由增加请求缓存参数
* 修正路由组合变量的可选变量的BUG

### [ 数据库 ]
* 修正`pgsql`数据库驱动的数据表字段信息读取
* 改进Query类的`view`方法 第二个参数默认值更改为true 获取全部的字段
* 数据库配置信息增加`query`参数用于配置查询对象名称
* 型类增加`query`属性用于配置模型需要的查询对象名称
* 改进数据表字段缓存读取
* 改进数据表字段缓存生成 模型为抽象类或者 没有继承Model类 不生成字段缓存
* 改进模型的字段缓存 虚拟模型不生成字段缓存
* 改进数据表字段缓存生成 支持读取模块的模型生成
* 改进聚合模型的`save`方法 主键写入
* 模型类的field属性定义简化 取消`Query`类的`allowField`和`setFieldType`方法及相关属性
* 改进数据表字段缓存生成 支持生成多个数据库的
* 更新数据库驱动类 改进`getTables`方法
* 增加` optimize:schema` 命令 用于生成数据表字段信息缓存
* 修正一个查询条件多个条件的时候的参数绑定BUG
* 分页查询方法`paginate`第二个参数传入数字表示总记录数
* 修正mysql的`JSON`字段查询
* 改进Query类的getOptions方法 当name参数不存在的时候返回null

### [ 模型和关联 ]
* 模型类的field属性不需要添加字段类型定义
* 改进Model类 添加`getDb`静态方法获取db查询对象
* 改进聚合模型`save`方法返回值
* 改进Relation类`save`方法
* 修正关联模型 多对多`save`方法一处问题
* 改进Model类的save方法 修正不按主键查询的更新问题
* 时间字段获取器获取的时候为NULL则不做转换

### [ 其它 ]

* 改进配置缓存生成 支持扩展配置
* 取消`extra_config_list`配置参数 扩展配置文件直接放到 `extra`目录下面即可自动加载（数据库配置文件位置不变）
* cache助手函数支持判断缓存是否有效
* 修正 模板引擎驱动类的`config`方法
* 修复在配置Model属性field=true情况下,通过`__call`调用db()引发的BUG
* 改进模板引擎驱动的config方法 支持获取配置参数值
* 改进redirct的url地址解析
* 删除`File`类的`md5`和`sha1`方法 改为`hash`方法 支持更多的散列值类型生成
* 增加`response_end`行为标签
* 改进默认语言的加载

## 2016-9-15 V5.0

### [ 请求和路由 ]

* Request对象支持动态绑定属性
* 定义了路由规则的URL原地址禁止访问
* 改进路由规则存储结构
* 路由分组功能增强，支持嵌套和虚拟分组
* 路由URL高效反解
* 改进Request对象param方法获取优先级
* 路由增加name方法设置和获取路由标识
* 增加MISS和AUTO路由规则
* Route类增加auto方法 支持注册一个自动解析URL的路由
* 路由规则支持模型绑定
* 路由变量统一使用param方法获取
* 路由规则标识功能和自动标识
* 增加生成路由缓存指令 optimize:route
* Request对象增加route方法单独获取路由变量
* Request对象的param get post put request delete server cookie env方法的第一个参数传入false 则表示获取原始数据 不进行过滤
* 改进自动路由标识生成 支持不同的路由规则 指向同一个路由标识，改进Url自动生成对路由标识的支持
* 改进Request类 filter属性的初始化
* 改进Request类的isAjax和isPjax方法
* Request类增加token方法
* 路由配置文件支持多个 使用 route_config_file 配置参数配置
* 域名绑定支持https检测
* 改进域名绑定 支持同时绑定模块和其他 支持绑定到数组定义的路由规则，取消域名绑定到分组
* 路由规则增加PATCH请求类型支持
* 增加route_complete_match配置参数设置全局路由规则定义是否采用完整匹配 可以由路由规则的参数complete_match 进行覆盖
* 改进路由的 后缀参数识别 优先于系统的伪静态后缀参数
* Url类增加root方法用于指定当前root地址（不含域名）
* 改进Url生成对可选参数的支持

### [ 数据库 ]

* 查询条件自动参数绑定
* 改进分页方法支持参数绑定
* Query类的cache方法增加缓存标签参数
* Query类的update和delete方法支持调用cache方法 会自动清除指定key的缓存 配合查询方法的cache方法一起使用 
* 改进Query类的延迟写入方法
* Query类的column和value方法支持fetchsql
* 改进日期查询方法
* 改进存储过程方法exec的支持
* 改进Connection类的getLastInsID方法获取
* 记录数据库的连接日志（连接时间和DSN）
* 改进Query类的select方法的返回结果集判断  
* Connection类增加getNumRows方法
* 数据库事务方法取消返回值
* 改进Query类的chunk方法对主键的获取
* 改进当数据库驱动类型使用完整命名空间的时候 Query类的builder方法的问题

### [ 模型 ]

* 增加软删除功能
* 关联模型和预载入改进
* 关联预载入查询闭包支持更多的连贯操作
* 完善savell方法支持更新和验证
* 关联定义统一返回Relation类
* Model类的has和hasWhere方法对join类型的支持
* Model类的data方法 批量赋值数据的时候 清空原始数据
* Model类的get方法第三个参数传入true的时候会自动更新缓存
* Model类增加只读字段支持
* Model类增加useGlobalScope方法设置是否启用全局查询范围
* Model类的base方法改为静态定义 全局多次调用有效
* Model类支持设定主键、字段信息和字段类型，不依赖自动获取，提高性能
* Model类的data方法 支持修改器
* 改进Relation类对非数字类型主键的支持
* 改进Relation类的一对多删除
* 修正Relation类的一对多关联预载入查询

### [ 日志和缓存 ]

* 支持日志类型分离存储
* 日志允许设置记录级别
* 增加缓存标签功能
* 缓存类增加pull方法用于获取并删除
* cache助手函数增加tag参数
* 简化日志信息，隐藏数据库密码
* 增加cache/session redis驱动的库选择逻辑;
* memcached驱动的配置参数支持option参数
* 调试模式下面 日志记录增加页面的header和param参数记录
* memcached缓存驱动增加连接账号密码参数
* 缓存支持设置complex类型 支持配置多种缓存并用store切换
* 缓存类增加tag方法 用于缓存标签设置 clear方法支持清除某个缓存标签的数据
* File类型日志驱动支持设置单独文件记录不同的日志级别
* 改进文件缓存和日志的存储文件名命名规范
* 缓存类增加inc和dec方法 针对数值型数据提供自增和自减操作
* Cache类增加has方法 get方法支持默认值

### [ 其它 ]

* 视图类支持设置模板引擎参数
* 增加表单令牌生成和验证
* 增加中文验证规则
* 增加image和文件相关验证规则
* 重定向Response对象支持with方法隐含传参
* 改进Session类自动初始化
* session类增加pull方法用于获取并删除
* 增加Env类用于获取环境变量
* Request类get/post/put等更改赋值后param方法依然有效
* 改进Jump跳转地址支持Url::build 解析
* 优化Hook类
* 应用调试模式和页面trace支持环境变量设置
* config助手函数支持 config('?name') 用法
* 支持使用BIND_MODULE常量的方式绑定模块
* 入口文件自动绑定模块功能
* 改进验证异常类的错误信息和模板输出，支持批量验证的错误信息抛出
* 完善console 增加output一些常用的方法
* 增加token助手函数 用于在页面快速显示令牌
* 增加halt方法用于变量调试并中断输出
* 改进Validate类的number验证规则 和 integer区分开
* optimize:autoload增加对extend扩展目录的扫描
* 改进Validate类的boolean验证规则 支持表单数据
* 改进cookie助手函数支持 判断是否存在某个cookie值
* 改进abort助手函数 支持抛出HttpResponseException异常
* 改进File类增加对上传错误的处理
* 改进File类move方法的返回对象增加上传表单信息，增加获取文件散列值的方法
* 改进File类的move方法的返回对象改为返回File对象实例
* 增加clear和optimize:config 指令
* 改进File类和Validate类的图像文件类型验证
* 控制器的操作方法支持注入Request之外的对象实例
* Request类 param(true) 支持获取带文件的数据
* input助手函数第一个参数增加默认值
* Validate类增加image验证规则 并改进max min length支持多种数据类型
* json输出时数据编码失败后抛出异常

### [ 调整 ]
* 废除路由映射（静态路由）定义
* 取消url_deny_suffix配置 改由路由的deny_ext参数设置
* 模型save方法返回值改为影响的记录数，取消getId参数
* Request对象controller方法返回驼峰控制器名
* 控制器前置操作方法不存在则抛出异常
* Loader类db方法增加name标识参数
* db助手函数增加第三个参数用于指定连接标识
* Sqlsrv驱动默认不对数据表字段进行小写转换
* 移除sae驱动 改为扩展包
* Oracle驱动移出核心包
* Firebird驱动移出核心包
* 取消别名定义文件alias.php
* 配置参数读取的时候取消环境变量判断 需要读取环境变量的时候使用Env类
* 环境变量定义文件更改为 .env 由原来的PHP数组改为ini格式定义（支持数组方式）
* 状态配置和扩展配置的加载顺序调整 便于状态配置文件中可以更改扩展配置的参数
* 取消域名绑定到路由分组功能
* 控制器类的success和error方法url参数支持传入空字符串，则不做任何处理
* 控制器的error success result redirect方法均不需要使用return
* 创建目录的权限修改为0644


## 2016-7-1 RC4版本
### [ 底层架构 ]
* 增加Request类 并支持自动注入
* 统一Composer的自动加载机制
* 增加Response类的子类扩展
* 增加File类用于上传和文件操作
* 取消模式扩展 SAE支持降权
* 优化框架入口文件
* 改进异常机制
* App类输入/输出调整
* 单元测试的完美支持
* 增加新的控制台指令
* 取消系统路径之外的大部分常量定义
* 类库映射文件由命令行动态生成 包含应用类库

### [ 数据库 ]

* 增加分表规则方法
* 增加日期和时间表达式查询方法
* 增加分页查询方法
* 增加视图查询方法
* 默认保持数据表字段大小写
* 数据缓存自动更新机制
* 完善事务嵌套支持
* 改进存储过程数据读取
* 支持设置数据库查询数据集返回类型

### [ 模型 ]
* 增加Merge扩展模型
* 模型支持动态查询
* 增加更多的类型自动转换支持
* 增加全局查询范围
* toJson/toArray支持隐藏和增加属性输出
* 增加远程一对多关联

### [ 其它 ]
* 日志存储结构调整
* Trace调试功能从日志类独立并增强
* 原Input类功能并入Request类
* 类库映射文件采用命令行生成 包含应用类库
* 验证类的check方法data数据取消引用传参
* 路由增加MISS路由规则
* 路由增加路由别名功能

## 2016-4-23 RC3版本
### [ 底层架构 ]
* 框架核心仓库和应用仓库分离 便于composer独立更新
* 数据库类重构，拆分为Connection（连接器）/Query（查询器）/Builder（SQL生成器）
* 模型类重构，更加对象化

### [ 数据库 ]

* 新的查询语法
* 闭包查询和闭包事务
* Query对象查询
* 数据分批处理
* 数据库SQL执行监听

### [ 模型 ]
* 对象化操作
* 支持静态调用（查询）
* 支持读取器/修改器
* 时间戳字段
* 对象/数组访问
* JSON序列化
* 事件触发
* 命名范围
* 类型自动转换
* 数据验证和完成
* 关联查询/写入
* 关联预载入

### [ 其它更新 ]
* 路由类增加快速路由支持
* 验证Validate类重构
* Build类增加快速创建模块的方法
* Url生成类改进
* Validate类改进
* View类及模板引擎驱动设计改进
* 取消模板引擎的模板主题设计
* 修正社区反馈的一些问题
* 助手函数重新命名
* `router.php`文件位置移动

## 2016-3-11 RC2版本

* 重新设计的自动验证和自动完成机制（原有自动验证和完成支持采用traits\model\Auto兼容）；
* 验证类Validate独立设计；
* 自动生成功能交给Console完成；
* 对数据表字段大小写的处理；
* 改进Controller类（取消traits\contorller\View）；
* 改进Input类；
* 改进Url类；
* 改进Cookie类；
* 优化Loader类；
* 优化Route类；
* 优化Template类；
* Session类自动初始化；
* 增加traits\model\Bulk模型扩展用于大批量数据写入和更新；
* 缓存类和日志类增加Test驱动；
* 对异常机制和错误处理的改进；
* 增加URL控制器和操作是否自动转换开关；
* 支持类名后缀设置；
* 取消操作绑定到类的功能；
* 取消use_db_switch参数设计；

## 2016-1-30 RC1版本
### [ 底层架构 ]

*   真正的惰性加载
*   核心类库组件化
*   框架引导文件
*   完善的类库自动加载（支持Composer）
*   采用Traits扩展
*   API友好（输出、异常和调试）
*   文件命名规范调整

### [ 调试和异常 ]

*   专为API开发而设计的输出、调试和异常处理
*   日志类支持本地文件/SAE/页面Trace/SocketLog输出，可以实现远程浏览器插件调试
*   内置trace方法直接远程调试
*   异常预警通知驱动设计
*   数据库SQL性能分析支持

### [ 路由 ]

*   动态注册路由
*   自定义路由检测方法
*   路由分组功能
*   规则路由中的变量支持采用正则规则定义（包括全局和局部）
*   闭包路由
*   支持路由到多层控制器

### [ 控制器 ]

*   控制器类无需继承controller类
*   灵活的多层控制器支持
*   可以Traits引入高级控制器功能
*   rest/yar/rpc/hprose/jsonrpc控制器扩展
*   前置操作方法支持排除和指定操作


### [ 模型 ]

*   简化的核心模型
*   Traits引入高级模型/视图模型/关联模型
*   主从分布时候主数据库读操作支持
*   改进的join方法和order方法

### [ 视图 ]

*   视图解析驱动设计（模板引擎）
*   所有方法不再直接输出而是返回交由系统统一输出处理
*   动态切换模板主题设计
*   动态切换模板引擎设计

### [ 数据库 ]

*   完全基于PDO实现
*   简化的数据库驱动设计
*   SQL性能监控（需要开启数据库调试模式）
*   PDO参数绑定改进

### [ 其他方面 ]

*   目录和MVC文件自动生成支持
*   I函数默认添加变量修饰符为/s
*   一个行为类里面支持为多个标签位定义不同的方法
*   更多的社交扩展类库