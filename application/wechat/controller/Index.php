<?php
/**
 * Created by PhpStorm.
 * User: 17610
 * Date: 2019/8/21
 * Time: 15:07
 */

namespace app\wechat\controller;
use EasyWeChat\Factory;
use EasyWeChat\Kernel\Messages\Text;
use EasyWeChat\Kernel\Messages\Image;
use EasyWeChat\Kernel\Messages\Video;
use EasyWeChat\Kernel\Messages\Voice;
use EasyWeChat\Kernel\Messages\News;
use EasyWeChat\Kernel\Messages\NewsItem;
use EasyWeChat\Kernel\Messages\Article;
use EasyWeChat\Kernel\Messages\Media;
use EasyWeChat\Kernel\Messages\Raw;
use think\Request;

class Index extends Base
{
    const APPID='wx3fb38d0d15ae7820';
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
    }
    
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
       $app  = self::checkAppId();
       
       if(empty($app)){
           echo 'success';die;
       }
        //公众号配置存入数据库（这里不安全）
        $options = [
            'wx3fb38d0d15ae7820'=>[
                'app_id'    => 'wx3fb38d0d15ae7820',
                'secret'    => 'c66c5215c458f79609cacb4ef61308ab',
                'token'     => 'MTU2NjM3MjQ4OS40NzAzMDE2NWY0NzAxYjk1MjVkZTQzZTk0MjBjZWFmMWM5NmE=',
                'response_type' => 'array',
                'log' => [
                    'level' => 'debug',
                    'file'  => '/opt/code/wxgzh/logs/easywechat.log',
                ],
                // ...
            ]
        ];
        $appId = $app['appid'];
        $openId = $app['openid'];
        $option = isset($options[$appId])?$options[$appId]:null;
        
        if(empty($option)){
            echo 'success';die;
        }
        
        //使用cache缓存
        if(cache($appId)){
            cache($appId,$option);
        }
        else{
            cache($appId,$option);
        }
        
        self::shuntMsg($appId,$openId,$option);
    }
    
    
    /**
     * 消息分流
     *
     * @param $appId
     * @param $openid
     * @param $options
     * @throws \EasyWeChat\Kernel\Exceptions\BadRequestException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \ReflectionException
     */
    private function shuntMsg($appId,$openid,$options)
    {
        $app = Factory::officialAccount($options);
        
        // 获取 access token 实例
        $server = $app->server;
        $user = $app->user;
        
        $server->push(function($message) use ($user) {
            $openid = $message['FromUserName'];
            $user = $user->get($openid);   //用户信息 array

            switch ($message['MsgType']) {
                case 'event':
                    $msg = self::_event($openid,$user);
                    break;
                case 'text':
                    $keyword=trim($message['Content']);
                    $msg = self::_text($openid,$keyword);
                    break;
                case 'image':
                    $mediaId=trim($message['MediaId']);
                    $msg = self::_image($openid,$mediaId);
                    break;
                case 'voice':
                    $mediaId=trim($message['MediaId']);
                    $msg = self::_voice($openid,$mediaId);
                    break;
                case 'video':
                    $mediaId=trim($message['MediaId']);
                    $msg = self::_video($openid,$mediaId,'');
                    break;
                case 'location'://微信目前不支持回复坐标消息
                    $msg = self::_location($openid);
                    break;
                case 'link'://微信目前不支持回复链接消息
                    $msg = self::_link($openid);
                    break;
                case 'file':
                    $msg = self::_file($openid);
                    break;
                // ... 其它消息
                default:
                    $msg='success';
                    break;
            }
            return empty($msg)?'success':$msg;

        });
        
       
        //发送回复
        $server->serve()->send();
        
    }
    
    
    //事件消息
    public static function _event($openid,$keyword=''){}
    //文字消息
    public static function _text($openid,$keyword='')
    {
        //查询数据库
        if(!empty($msg)){
            $text = new Text('您好！overtrue。');
            $msg = $text;
        }else {
            //机器人回复
            $msg = self::_robot($keyword);
        }
        
        return  $msg;
    }
    //图片消息
    public static function _image($openid,$mediaId)
    {
        return  $image = new Image($mediaId);
    }
    //语音消息
    public static function _voice($openid,$mediaId)
    {
        return  $voice = new Voice($mediaId);
    }
    //视频消息
    public static function _video($openid,$mediaId,$title)
    {
          $video = new Video($mediaId, [
            'title' => $title,
            'description' => '...',
        ]);
        return $video;
    }
    //坐标消息
    public static function _location($openid,$keyword=''){}
    //链接消息
    public static function _link($openid,$keyword=''){}
    //文件消息
    public static function _file($openid,$keyword=''){}
    
   
    
    
    /**
     * 发送客服消息
     *
     * @param $openId
     * @param $msgType
     * @param $content
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\RuntimeException
     */
    public static function _kf($openId,$msgType,$content)
    {
        $msg=[
           'touser' => $openId,
           'msgtype' => $msgType,
           
        ];
        $msgType == 'text' && $msg['text'] = ['content'=>$content];
        in_array($msgType,['image','voice','video']) && $msg["$msgType"] = ['media_id'=>$content];

        $message = new Raw(json_encode($msg));
    
        $app = Factory::officialAccount(cache(self::APPID));
        $result = $app->customer_service->message($message)->to($openId)->send();
        return $result;
    }
    
    /**
     * @throws \Exception
     */
    public function _menu()
    {
        $buttons = [
            [
                "type" => "click",
                "name" => "今日歌曲",
                "key"  => "V1001_TODAY_MUSIC"
            ],
            [
                "name"       => "菜单",
                "sub_button" => [
                    [
                        "type" => "view",
                        "name" => "搜索",
                        "url"  => "http://www.soso.com/"
                    ],
                    [
                        "type" => "view",
                        "name" => "视频",
                        "url"  => "http://v.qq.com/"
                    ],
                    [
                        "type" => "click",
                        "name" => "赞一下我们",
                        "key" => "V1001_GOOD"
                    ],
                ],
            ],
        ];
    
        $matchRule = [
            "tag_id" => "2",
            "sex" => "1",
            "country" => "中国",
            "province" => "广东",
            "city" => "广州",
            "client_platform_type" => "2",
            "language" => "zh_CN"
        ];
        $app = Factory::officialAccount(cache(self::APPID));
//        $app->menu->create($buttons,$matchRule);
        $current = $app->menu->current();
        var_dump($current);
        
    }
    
    
   
    /**
     * 验证appId
     * @return array
     */
    private static function  checkAppId()
    {
        $query = urldecode($_SERVER['QUERY_STRING']);
        $pos = strrpos($query,"/");
        $params = explode('&',substr($query,$pos+1 , mb_strlen($query)));
        
        if(in_array($params[0],[self::APPID])){
            return [
                'appid'=>$params[0],
                'openid'=>explode('=',$params[4])[1],
            ] ;
        }
    }
    
    
}
