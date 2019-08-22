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
 * 处理post请求
 * @param $url string
 * @param $data array
 * @return mixed|string
 */
 function curl($url,$data=[])
{
    $data=json_encode($data, JSON_UNESCAPED_UNICODE);
    $postUrl = urldecode($url);
    $ch = curl_init();//初始化curl
    curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
    curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
    curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
    if(!empty($data)){
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
    $data = curl_exec($ch);     //运行curl
    curl_close($ch);
    return $data;
}