# 腾讯云服务端 SDK for PHP  ![](https://imgcache.qq.com/open_proj/proj_qcloud_v2/gateway/portal/css/img/nav/logo-bg-color.svg)
##### 目前没有找到任何相关腾讯云服务端api-sdk，类似的composer包又只包含部分内容，所以自己开发一个composer包，方便大家使用，正在开发中，前端时间忙，终于抽出时间继续这个包的开发，一点一点的加上功能吧！


### 主要新特性
* 采用新版本签名算法（如果您的 SDKAppID 是2019.07.19之前创建的，建议升级为 HMAC-SHA256 算法）
* 仅抛出请求错误异常、请求失败异常、其余原样返回
* 对接口进行封装，不需要使用者对照文档进行复杂的参数构建，很多时间只需要传单个实参，或一维数组，尽可能避免使用者参阅文档，提高生产力


### 更新日志
- 1.0.5 腾讯IM即时通讯（增加账号管理相关接口）

### 需求
- [x] 帐号管理
- [ ] 单聊消息
- [ ] 全员推送
- [ ] 资料管理
- [ ] 关系链管理
- [ ] 群组管理
- [ ] 全局禁言管理
- [ ] 运营管理
- [ ] 回调

## 安装
> 运行环境要求PHP7.1+。
```shell
$ composer require cccdl/tencent_sdk
```

### 接口对应文件

| 文件                       | 方法                 |  说明    |
| :-----------------------  | --------------         |  :----    |
| ImOpenLoginSvc.php        | `accountImport()`       | 导入单个帐号 |
| ImOpenLoginSvc.php        | `multiAccountImport()`  | 导入多个帐号 |
| ImOpenLoginSvc.php        | `accountDelete()`      | 删除帐号    |
| ImOpenLoginSvc.php        | `accountCheck()`       | 查询帐号    |
| ImOpenLoginSvc.php        | `kick()`               | 失效帐号登录态 |
| ImOpenLoginSvc.php        | `queryState()`         | 查询帐号在线状态 |
| OpenIm.php                | `sendMsg()`         | 单发单聊消息 |

### 快速使用
在您开始之前，您需要注册腾讯云并获取您的[凭证](https://console.cloud.tencent.com)。


```php
<?php

use cccdl\tencent_sdk\Im\ImOpenLoginSvc;

$im = new imOpenLoginSvc($appId, $key, $identifier);

$res = $im->queryState(['1000001', '1000002']);
```

## 文档

[腾讯云文档中心](https://cloud.tencent.com/document/product)

## 问题
[提交 Issue](https://github.com/cccdl/tencent_sdk/issues)，不符合指南的问题可能会立即关闭。


## Contributing

You can contribute in one of three ways:

1. File bug reports using the [issue tracker](https://github.com/cccdl/tencent_sdk/issues).
2. Answer questions or fix bugs on the [issue tracker](https://github.com/cccdl/tencent_sdk/issues).
3. Contribute new features or update the wiki.

_The code contribution process is not very formal. You just need to make sure that you follow the PSR-0, PSR-1, and PSR-2 coding guidelines. Any new code contributions must be accompanied by unit tests where applicable._

## License

MIT