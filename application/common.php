<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
/**
 *  支持Java接口header头请求json格式
 * 【处理get,post请求】
 * @param $url string
 * @param $data array
 * @return mixed|string
 */
 function curl($url,$data=array())
{
    //封装curl方法
    stripos($url,'%') && $url = urldecode($url);
    is_array($data) && $data = json_encode($data, JSON_UNESCAPED_UNICODE);
    $header=array(
        'Cache-Control: max-age=0',
        'Upgrade-Insecure-Requests:1',
        'Content-Type: application/json;charset=utf-8',
        'Content-Length: ' .strlen($data)
    );
    $ch = curl_init();//初始化curl
    curl_setopt($ch, CURLOPT_URL,urldecode($url));//抓取指定网页
   
    if(stripos('$'.$url,'https://')>0){
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);  //https协议需要以下两行，否则请求不成功
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
       
    }
    curl_setopt($ch, CURLOPT_HEADER, 0);            //设置header不输出
    
    if(!empty($data)){
        curl_setopt($ch, CURLOPT_POST, 1);          //post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER,$header);         //设置支持json格式
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);   //要求结果为字符串且输出到屏幕上
    $data = curl_exec($ch);     //运行curl
    curl_close($ch);
    return $data;
}

/**
 * 记录日志 txt 删除日志将写入计划任务中
 * @param $msg array | string 要写入的日志信息
 * @param $key string 要传入的日志类型
 * @param $minDir string 要传入的日志路径
 */
function Logs($msg, $key='',$minDir='')
{
    if(!empty($minDir) && is_string($minDir)){
        $dir=LOG_PATH.trim($minDir,'/').'/';
    }
    
    if(!file_exists($dir)){
        mkdir(iconv("UTF-8", "utf-8", $dir), 0777, true);
    }
    
    $record = date('Y-m-d H:i:s') . " ===>>> " . ' ' . $key. " :" ;
    if(is_array($msg)){
        foreach ($msg as $k => $item){
            $record.=$k.' '.$item."\n";
        }
    }else{
        $record.=$msg;
    }
   
    $path =$dir .date('Y-m');
    //设置路径目录信息
    $filePath = $path . '/' . date('Y.m.d') . '_log.txt';
    //目录不存在就创建
    if (!file_exists($path)) {
        //iconv防止中文名乱码
        mkdir(iconv("UTF-8", "utf-8", $path), 0777, true);
    }
    if(file_exists($path)){
        //方式一
        file_put_contents($filePath,$record.PHP_EOL,FILE_APPEND);
    }
}