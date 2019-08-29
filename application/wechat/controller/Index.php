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
use app\wechat\model\KeyModel;
class Index extends Base
{
    const APPID='wx3fb38d0d15ae7820';
    static $app;
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
    }
    
    /**
     * 消息主入口 ()
     * 域名/wechat/Index/service/wx3fb38d0d15ae7820 服务器配置(带上appid)
     * 多公众号可以用appid作为参数 ，匹配数据库中的公号配置已达到同时支持多公众号应用的目的
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
            ]
        ];
        $appId = trim($app['appid']);
        $openId = trim($app['openid']);
        $option = isset($options[$appId])?$options[$appId]:null;
        
        if(empty($option)){
            echo 'success';die;
        }
        
        //使用cache缓存
        if(cache($appId)){
            $option =  cache($appId);
        } else {
            cache($appId,$option);
        }
        
        self::shuntMsg($appId,$openId,$option);
    }
    
    
    /**
     * 消息分流
     *
     * @param $appId
     * @param $openid
     * @param $option
     * @throws \EasyWeChat\Kernel\Exceptions\BadRequestException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \ReflectionException
     */
    private function shuntMsg($appId,$openid,$option)
    {
        $app = Factory::officialAccount($option);
        
        // 获取 access token 实例
        $server = $app->server;
        $user = $app->user;
//        $message = $server->getMessage();
        
        $app->server->push(function ($message) use ($appId) {
            $openid = $message['FromUserName'];
            //接受消息类型
            switch ($message['MsgType']) {
                case 'event':
                    $msg = self::_event($appId,$openid,$message['Event'],$message['EventKey']);
                    break;
                case 'text':
                    $keyword=trim($message['Content']);
                    $msg = self::_text($appId,$openid,$keyword);
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
                case 'shortvideo':
                    $mediaId=trim($message['MediaId']);
                    $msg = self::_video($openid,$mediaId,'');
                    break;
                case 'location':
                    $msg = self::_location($openid);
                    break;
                case 'link':
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
            return $msg;
        });
    
        
        //发送回复
        $server->serve()->send();
    
    }
    
    //事件消息
    public static function _event($appId,$openid,$event,$eventKey)
    {
        $msg='';
        Logs(['event'=>$event,'eventKey'=>$eventKey],'event','gzh');
        switch ($event){
            case 'subscribe'://用户未关注时，进行关注后的事件推送
                $msg = self::_text($appId,$openid,$event);
                break;
            case 'SCAN':// 扫码
                break;
            case 'LOCATION':// 上报地理位置
                break;
            case 'CLICK':// 点击菜单拉取消息时的事件推送
                break;
            case 'VIEW':// 点击菜单跳转链接时的事件推送
                break;
        }
        return $msg;
    }
    //文字消息
    public static function _text($appId,$openid,$keyword='')
    {
        //查询数据库
        $con = self::getKeysMsg($appId,$keyword);
        $msg='';
        if(!empty($con)){
            foreach ($con as $item){
                $content=json_decode(trim($item['content']),true);
                $msg =  self::shuntResponseMsg($item['msgtype'],$content);
            }
        }
        else {
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
     * 发送客服消息(原始客服消息)
     *
     * @param $openId
     * @param $msgType
     * @param $content
     * @param bool $kf_type
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\RuntimeException
     */
    public static function _kf($openId,$msgType,$content,$kf_type=true)
    {
        if($kf_type){
            $msg=[
                'touser' => $openId,
                'msgtype' => $msgType,
                "$msgType"=>$content
            ];
            $message = new Raw(json_encode($msg,JSON_UNESCAPED_UNICODE));
            
        }else{
            $message = self :: shuntResponseMsg($msgType,$content);
        }
        
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
    
    //回复消息分流(非原始方式)
   private static function shuntResponseMsg($msgType,$msg)
   {
        switch ($msgType){
            case 'text':
                $msg = new Text($msg['content']);
                break;
            case 'image':
                $msg = new Image($msg['mediaId']);
                break;
            case 'voice':
                $msg = new Voice($msg['mediaId']);
                break;
            case 'video':
                $msg = new Video($msg['mediaId'], [
                    'title' => $msg['title'],
                    'description' => $msg['description'],
                ]);
                break;
            case 'news':    //单条
                if(isset($msg['title'])){   //自定义
                    $items = [
                        new NewsItem([
                            'title'       => $msg['title'],
                            'description' => $msg['description'],
                            'url'         => $msg['url'],  //路径
                            'image'       => $msg['image'],//路径
                        ]),
                    ];
                    $msg = new News($items);
                }else{  //素材
                    $msg = new Media($msg['mediaId'], 'mpnews');
                }
                break;
            case 'article': //待确认
                $items = [
                    'title'   => 'EasyWeChat',
                    'author'  => 'overtrue',
                    'content' => 'EasyWeChat 是一个开源的微信 SDK，它... ...',
                    'thumb_media_id ' => '',
                    'digest ' => '',
                    'source_url ' => '',
                    'show_cover ' => '',
                ];
                $msg = new Article($items);
                break;
            default:
                break;
        }
        return $msg;
   }
   
    /**
     * 验证appId
     * @return array
     */
    private static function  checkAppId()
    {
        $query = $_SERVER['REQUEST_URI'];
        $start = strrpos($query,"/");
        $params = explode('?',substr($query,$start+1 , mb_strlen($query)));
       
        //查询出appid数组，替换
        if(in_array($params[0],[self::APPID])){
            $openid='';
            parse_str($params[1]);
            
            return ['appid'=>$params[0], 'openid'=>$openid] ;
        }
        return [];
    }
    
    
    
    public static function getKeysMsg($appId,$keys)
    {
        $res = KeyModel::getKeyMsg('msg_id,msgtype,content',['status'=>1,'keys'=>$keys,'appid'=>$appId]);
       return $res;
    }
    
}
