<?php
/**
 * Created by PhpStorm.
 * User: 17610
 * Date: 2019/8/21
 * Time: 15:07
 */

namespace app\wechat\controller;
use EasyWeChat\Factory;

class Index extends Base
{
    /**
     * 消息主入口 ()
     *
     * 多公众号可以用appid作为参数 ，匹配数据库中的公号配置
     *
     * @throws \EasyWeChat\Kernel\Exceptions\BadRequestException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \ReflectionException
     */
    public function service()
    {
        //公众号配置存入数据库（这里不安全）
        $options = [
            'app_id'    => 'wx3fb38d0d15ae7820',
            'secret'    => 'c66c5215c458f79609cacb4ef61308ab',
            'token'     => 'MTU2NjM3MjQ4OS40NzAzMDE2NWY0NzAxYjk1MjVkZTQzZTk0MjBjZWFmMWM5NmE=',
            'response_type' => 'array',
            'log' => [
                'level' => 'debug',
                'file'  => '/opt/code/wxgzh/logs/easywechat.log',
            ],
            // ...
        ];
        //使用cache缓存
        if(cache('gzh')){
            $options=cache('gzh');
        }
        else{
            cache('gzh',$options);
        }
        
      self::shuntMsg($options);
    }
    
    /**
     * 消息分流
     *
     * @param $options
     * @throws \EasyWeChat\Kernel\Exceptions\BadRequestException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \ReflectionException
     */
    private function shuntMsg($options)
    {
        $app = Factory::officialAccount($options);
    
        $server = $app->server;
        $user = $app->user;
        
        $server->push(function($message) use ($user) {
            $user = $user->get($message['FromUserName']);   //array
            $keyword=trim($message['Content']);
            switch ($message['MsgType']) {
                case 'event':
                    $msg = self::_event($user,$keyword);
                    break;
                case 'text':
                    $msg = self::_text($user,$keyword);
                    break;
                case 'image':
                    $msg = self::_image($user,$keyword);
                    break;
                case 'voice':
                    $msg = self::_voice($user,$keyword);
                    break;
                case 'video':
                    $msg = self::_video($user,$keyword);
                    break;
                case 'location':
                    $msg = self::_location($user,$keyword);
                    break;
                case 'link':
                    $msg = self::_link($user,$keyword);
                    break;
                case 'file':
                    $msg = self::_file($user,$keyword);
                    break;
                // ... 其它消息
                default:
                    $msg = self::_robot($user['nickname'].$keyword);
                    break;
            }
            return $msg;
        
        });
        //发送回复
        $server->serve()->send();
    }
    
    
    //事件消息
    public static function _event($user,$keyword){}
    //文字消息
    public static function _text($user,$keyword)
    {
        return  self::_robot($user['nickname'].$keyword);
    }
    //图片消息
    public static function _image($user,$keyword){}
    //语音消息
    public static function _voice($user,$keyword){}
    //视频消息
    public static function _video($user,$keyword){}
    //坐标消息
    public static function _location($user,$keyword){}
    //链接消息
    public static function _link($user,$keyword){}
    //文件消息
    public static function _file($user,$keyword){}
    
}
