<?php
/**
 * Created by PhpStorm.
 * User: 17610
 * Date: 2019/8/22
 * Time: 16:45
 */

namespace app\wechat\controller;


class Robot
{
    /**
     * 青云客智能聊天机器人API
     * @return string
     */
    public function index()
    {
        $msg = $keyword ='你好！';
        $url = "http://api.qingyunke.com/api.php?key=free&appid=0&msg=".$keyword;
        
        $res = curl(urlencode($url));
        if($res){
            $msg = json_decode($res,true)['content'];
        }
        return $msg;
    }
    
    public function tuling()
    {
        $key = "25b4063b9d794bc08b599b6b50a5e916";
        $keyword = $_GET['text'];
        $ip = $_SERVER["REMOTE_ADDR"];
        $userid = ip2long($ip);
        $url = "http://openapi.tuling123.com/openapi/api/v2";
        $params = [
          'reqType' => 0,
          'perception' => [
              'inputText'=> [
                'text'=>$keyword
              ],
              ],
          'userInfo' => [
              'apiKey'=>$key,
              'userId'=>$userid,
          ],
        ];
        
        $output = curl(urlencode($url),$params);
       
        $chatArr = json_decode($output,true);
       
        $ChatCode = $chatArr['intent']['code'];//编码
        $ChatText = $chatArr['results'][0]['values']['text'];//回答
        if($ChatCode == "100000"){
            echo $ChatText;
        }elseif($ChatCode == "40002"){
            echo $ChatText;
        }elseif($ChatCode == "200000"){
            $ChatUrl = $chatArr->url;//链接
            echo $ChatText . "<br/>" . $ChatUrl;
        }else{
            echo "系统错误  ErrorCode=" . $ChatCode;
        }
    }
    
    
}