<?php
/**
 * Created by PhpStorm.
 * User: 17610
 * Date: 2019/8/29
 * Time: 16:39
 */

namespace app\wechat\job;
use think\Controller;

/**
 * 自定义微信接口
 * Class WeChatService
 * @package app\wechat\job
 */
class WeChatService extends Controller
{
    const HOST='http://api.weixin.qq.com';
    const CUSTOM='/cgi-bin/message/custom/send';
    const TEMPLATE='/cgi-bin/message/template/send';
    const SUPERRESOLUTION='/cv/img/superresolution';
    const AICROP='/cv/img/aicrop';
    
    
    //普通消息
    public static function sendGeneralMsg()
    {
    
    }
    
    //客服消息
    public static function sendServiceMsg($access_token,$openid,$msgtype,$msg)
    {
        if(empty($access_token) || empty($openid) || empty($msgtype) || empty($msg)){
            return null;
        }
        $url=self::HOST.self::CUSTOM.'?access_token='.$access_token;
        $message = [
            'touser'=>$openid,
            'msgtype'=>$msgtype,
            "$msgtype"=>$msg
        ];
        return curl($url,json_encode($message,JSON_UNESCAPED_UNICODE));
    }
    
    //模板消息
    public static function sendTemplateMsg($access_token,$openid,$template_id,$msg,$goalUrl='',$miniProgram=[])
    {
        if(empty($access_token) || empty($openid) || empty($template_id) || empty($msg)){
            return null;
        }
        
        $url=self::HOST.self::TEMPLATE.'?access_token='.$access_token;
        $message = [
            'touser'=>$openid,
            'template_id'=>$template_id,
        ];
        !empty($goalUrl) && $message['url']=$goalUrl;
        !empty($miniProgram) && $message['miniprogram'] = $miniProgram;
        $message['data']=$msg;
        
        return curl($url,json_encode($message,JSON_UNESCAPED_UNICODE));
    }
   
  
    /**
     * 图片处理（高清化）
     * ps1:需要微信认证
     * ps2:文件大小限制：小于2M
     * ps3:目前支持将图片超分辨率高清化2倍，即生成图片分辨率为原图2倍大小
     * @param $encode_url
     * @param $access_token
     * @return string
     */
    public static function pictureHD($encode_url,$access_token)
    {
        $media_id='';
        $url = self::HOST.self::SUPERRESOLUTION.'?img_url='.$encode_url.'&access_token'=$access_token;
        
        $res = json_decode(curl($url),true);
        
        isset($res['errcode']) && $media_id= $res['media_id'];
        return $media_id;
    }
    
    /**
     * 图片智能裁剪接口
     * ps1:文件大小限制：小于2M
     * ps2:ratios参数为可选，如果为空，则算法自动裁剪最佳宽高比；如果提供多个宽高比，请以英文逗号“,”分隔，最多支持5个宽高比
     * @param $encode_url
     * @param $access_token
     * @return mixed
     */
    public static function picAiCrop($encode_url,$access_token)
    {
        $url = self::HOST.self::AICROP.'?img_url='.$encode_url.'&access_token'=$access_token;
        
        $res = json_decode(curl($url),true);
        
        return $res;
    }
}