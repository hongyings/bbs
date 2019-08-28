### 基于thinkphp与overtrue/wechat开发的项目

------

##### 一、资源版本：

1. thinkphp 5.0.24  

   详情请查看http://www.thinkphp.cn/

2. overtrue/wechat 4.2.2 

   **Requirement**

   1. PHP >= 7.1
   2. **Composer**
   3. openssl 拓展
   4. fileinfo 拓展（素材管理模块需要用到）

   **Installation**

   ```
   $ composer require "overtrue/wechat:^4.2" -vvv
   ```

   **Usage**
   基本使用（以服务端为例）:

   ```php
   <?php
   
   use EasyWeChat\Factory;
   
   $options = [
       'app_id'    => 'wx3cf0f39249eb0exxx',
       'secret'    => 'f1c242f4f28f735d4687abb469072xxx',
       'token'     => 'easywechat',
       'log' => [
           'level' => 'debug',
           'file'  => '/tmp/easywechat.log',
       ],
       // ...
   ];
   
   $app = Factory::officialAccount($options);
   
   $server = $app->server;
   $user = $app->user;
   
   $server->push(function($message) use ($user) {
       $fromUser = $user->get($message['FromUserName']);
   
       return "{$fromUser->nickname} 您好！欢迎关注 overtrue!";
   });
   
   $server->serve()->send();
   ```

   详情请查看 https://packagist.org/packages/overtrue/wechat

##### 二、使用方式

​	1.下载项目

```git
git clone git@github.com:hongyings/gzh.git
```

​	2.修改application/wechat/Index.php

```php
//修改为自己的公众号配置，最好是存在数据库。
$options = [
            'app_id'    => 'wx3fb38d0d15ae7***',    
            'secret'    => 'c66c5215c458f79609cacb4ef6130***',
            'token'     => 'MTU2NjM3MjQ4OS40NzAzMDE2NWY0NzAxYjk1MjVkZTQzZTk0MjBjZWFmMWM5N***',
            'response_type' => 'array',
            'log' => [
                'level' => 'debug',
                'file'  => '/logs/easywechat.log',
            ],
            // ...
        ];
```

##### 三、暂时不支持数据库操作，后面升级会用到

测试号：https://pan.baidu.com/s/1lfITufx2aKb6i9JveztUmA

##### 如果有更好建议请发送邮件到email:17610826019@126.com

##### License

------

GNU General Public License v3.0