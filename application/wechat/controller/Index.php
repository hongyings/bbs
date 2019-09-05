<?php
/**
 * Created by PhpStorm.
 * User: 17610
 * Date: 2019/8/21
 * Time: 15:07
 */

namespace app\wechat\controller;
use EasyWeChat\Factory;
use think\Request;


class Index extends Base
{
    
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
        $params = input('param.');
        
        //如果不是上面格式，请注释下面四行代码
       $app  = self::checkAppId();
        //Logs($app,'app','gzh');
        
       if(empty($app)){
           echo self::RESPONSE;die;
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
        $appId  = isset($params['appid']) ? trim($params['appid']) : $app['appid'];
        $openId = isset($params['openid']) ? trim($params['openid']) : $app['openid'];
        $option = isset($options[$appId]) ? $options[$appId] : null;
        
        if(empty($option)){
            echo self::RESPONSE;die;
        }
        
        //使用cache缓存
        if(cache($appId)){
            $option =  cache($appId);
        } else {
            cache($appId,$option);
        }
        //转发消息
        self::transmitMsg($appId,$openId,$option);
    }
    
    /**
     *  转发消息
     * @param $appId
     * @param $openId
     * @param $option
     * @throws \EasyWeChat\Kernel\Exceptions\BadRequestException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    /**
     * @param $appId
     * @param $openId
     * @param $option
     * @throws \EasyWeChat\Kernel\Exceptions\BadRequestException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \ReflectionException
     */
    public function transmitMsg($appId,$openId,$option)
    {
        $app = Factory::officialAccount($option);
    
        // 获取 access token 实例
        $server = $app->server;
        $user   = $app->user;
        $message = $server->getMessage();
        //Logs($message,'message','gzh');
        //处理请求重试
        if(isset($message['MsgId']) && ($message['MsgId'] == cache('MsgId'))){
            Logs($message,'Request','gzh');
        }else{
    
            //发送回复
            $server->push(function(){return self::RESPONSE;});
            $server->serve()->send();
            
            //转发消息
            $message['appid'] = $appId;
            
            switch ($appId){
                case 'wx3fb38d0d15ae7820':
                    curl('/wechat/Message/shuntMsg',$message);
                    break;
            }
            //缓存消息id
            cache('MsgId',trim($message['MsgId']));
            
            
        }
    
        //日志记录
//            Logs($message,'message','gzh');

//            $ips = $app->base->getValidIps();
        
      
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
    
        $openid='';$param=[];
        if(in_array($params[0],[self::APPID])){
           
            $param=['appid'=>$params[0]];
            
            isset($params[1]) && parse_str($params[1]); $param['openid']=$openid;
            
        }
        return $param;
    }
    
    
   
    
}
