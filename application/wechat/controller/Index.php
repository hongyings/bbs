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
use EasyWeChat\Kernel\Messages\Transfer;
use think\Request;
use app\wechat\model\KeyModel;
use app\wechat\job\WeChatService;
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
        //如果不是上面格式，请注释下面四行代码
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
        $appId  = isset($app['appid']) ? trim($app['appid']) : null;
        $openId = isset($app['openid']) ? trim($app['openid']) : null;
        $option = isset($options[$appId]) ? $options[$appId] : null;
        
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
        $user   = $app->user;
        $message = $server->getMessage();
        Logs(['appid'=>$appId,'openid'=>$openid,'message'=>$message],'record','gzh');
    
        $app->server->push(function(){return 'success';});
       
        $app->server->push(function ($message) use ($appId) {
            $openid   =  isset($message['FromUserName']) ? trim($message['FromUserName']):null;
            $event    =  isset($message['Event'])        ? trim($message['Event']):null;
            $eventKey =  isset($message['EventKey'])     ? trim($message['EventKey']):null;
            $mediaId  =  isset($message['MediaId'])      ? trim($message['MediaId']):null;
            
            //接受消息类型
            switch ($message['MsgType']) {
                case 'event':
                    $msg = self::_event($appId,$openid,$event,$eventKey);
                    break;
                case 'text':
                    $keyword  =  isset($message['Content']) ? trim($message['Content']):null;
                    $msg = self::_response($appId,$openid,$keyword);
                    break;
                case 'image':
                    $picUrl  =  isset($message['PicUrl']) ? trim($message['PicUrl']):null;
                    $msg = self::_image($openid,$mediaId,$picUrl);
                    break;
                case 'voice':
                    $format  =  isset($message['Format']) ? trim($message['Format']):null;
                    $msg = self::_voice($openid,$mediaId,$format);
                    break;
                case 'video':
                case 'shortvideo':
                    $msg = self::_video($openid,$mediaId,'');
                    break;
                case 'location':    //微信暂不支持
                    $msg = self::_location($openid);
                    break;
                case 'link':    //微信暂不支持
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
    public static function _event($appId,$openid,$event,$eventKey)
    {
        $msg='';
        Logs(['event'=>$event,'eventKey'=>$eventKey],'event','gzh');
        switch ($event){
            case 'subscribe'://用户未关注时，进行关注后的事件推送 $eventKey=null
                $msg = self::_response($appId,$openid,$event);
                break;
            case 'SCAN':// 扫码
                $msg = self::_response($appId,$openid,$eventKey);
                break;
            case 'LOCATION':// 上报地理位置
                break;
            case 'CLICK':// 点击菜单拉取消息时的事件推送
                $msg = self::_response($appId,$openid,$eventKey);
                break;
            case 'VIEW':// 点击菜单跳转链接时的事件推送
                $msg = self::_response($appId,$openid,$eventKey);
                break;
        }
        return $msg;
    }
    
    //回复消息主入口
    public static function _response($appId,$openid,$keyword='')
    {
        //查询数据库
        $con = self::getKeysMsg($appId,$keyword);
        $msg='';
        if(!empty($con)){
            foreach ($con as $item){
                $content=json_decode(trim($item['content']),true);
                $msg =  self::shuntResponseMsg($item['msgtype'],$content,$openid);
            }
        }
        else {
            //机器人回复
            $msg = self::_robot($keyword);
        }
        return  $msg;
    }
    //图片消息
    public static function _image($openid,$mediaId,$picUrl)
    {
        return  $image = new Image($mediaId);
    }
    //语音消息
    public static function _voice($openid,$mediaId,$format)
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
            $message = self :: shuntResponseMsg($msgType,$content,$openId);
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
                "name"=> "扫码",
                "sub_button"=>[
                    [
                        "type"=>"scancode_waitmsg",
                        "name"=>"扫码带提示",
                        "key"=> "rselfmenu_0_0",
                        "sub_button"=>[]
                    ],
                    [
                        "type"=> "scancode_push",
                        "name"=> "扫码推事件",
                        "key"=> "rselfmenu_0_1",
                        "sub_button"=>[]
                    ],
                    [
                        "type" => "click",
                        "name" => "今日歌曲",
                        "key"  => "V1001_TODAY_MUSIC"
                    ],
                    [
                        "type" => "view",
                        "name" => "搜索",
                        "url"  => "http://www.soso.com/"
                    ],
                ],
                [
                    "name"=> "发图",
                    "sub_button"=>[
                        [
                            "type"=> "pic_sysphoto",
                            "name"=> "系统拍照发图",
                            "key"=> "rselfmenu_1_0",
                            "sub_button"=>[]
                        ],
                        [
                            "type"=>"pic_photo_or_album",
                            "name"=> "拍照或者相册发图",
                            "key"=> "rselfmenu_1_1",
                            "sub_button"=>[]
                        ],
                        [
                            "type"=>"pic_weixin",
                            "name"=> "微信相册发图",
                            "key"=> "rselfmenu_1_2",
                            "sub_button"=>[]
                        ]
                    ]
                ],
                [
                    "name"=> "发送位置",
                    "sub_button"=>[
                        [
                            "type"=> "location_select",
                            "key"=> "rselfmenu_2_0",
                            "sub_button"=>[]
                        ],
                        [
                            "type"=>"media_id",
                            "name"=> "图片",
                            "media_id"=> "MEDIA_ID1",
                            "sub_button"=>[]
                        ],
                        [
                            "type"=>"view_limited",
                            "name"=> "图文消息",
                            "key"=> "MEDIA_ID2",
                            "sub_button"=>[]
                        ]
                    ]
                ],
            ]
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
        $app->menu->create($buttons,$matchRule);
//        $current = $app->menu->current();
//        var_dump($current);
        
    }
    public function scene()
    {
        $app = Factory::officialAccount(cache(self::APPID));
        $result = $app->qrcode->temporary('foo', 6 * 24 * 3600);
        var_dump($result);
    }
    
    //回复消息分流(非原始方式)
   private static function shuntResponseMsg($msgType,$msg,$openId)
   {
        switch ($msgType){
            case 'text':
                if(isset($msg['content'])){
                    $msg = new Text($msg['content']);
                }
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
            case 'msgmenu':
                self::_kf($openId,$msgType,$msg);
                break;
            default:
                break;
        }
        return $msg;
   }
   
    /**
     * 验证appId
     * /wechat/Index/service/wx3fb38d0d15ae7820
     * @return array
     */
    private static function  checkAppId()
    {
        $query = $_SERVER['REQUEST_URI'];
        $start = strrpos($query,"/");
        $params = explode('?',substr($query,$start+1 , mb_strlen($query)));
        Logs($params,'params');
        //查询出appid数组，替换
        if(in_array($params[0],[self::APPID])){
            $openid='';
            $param=['appid'=>$params[0]];
            if(isset($params[1])){
                parse_str($params[1]);
                $param['openid']=$openid;
            }
            return $param;
        }
        return [];
    }
    
    
    //获取自定义回复内容
    public static function getKeysMsg($appId,$keys)
    {
        $res = KeyModel::getKeyMsg('msg_id,msgtype,content',['status'=>1,'keys'=>$keys,'appid'=>$appId]);
       return $res;
    }
    
}
