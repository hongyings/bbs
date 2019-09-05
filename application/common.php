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
 * 记录日志 txt (支持两层数组)
 * @param $msg array | string 要写入的日志信息
 * @param $key string 要传入的日志类型
 * @param $lastDir string 要传入的日志路径
 * @param $file string 要传入的日志文件名
 */
function Logs($msg, $key='',$lastDir='gzh',$file='')
{
    if(!empty($lastDir) && is_string($lastDir)){
        $dir=LOG_PATH.trim($lastDir,'/').'/';
    }else{
        $dir=LOG_PATH;
    }
    
    if(!file_exists($dir)){
        mkdir(iconv("UTF-8", "utf-8", $dir), 0777, true);
    }
    $record = date('Y-m-d H:i:s') . ' ==>>> ' .(empty($key)?'>':$key). ':' ;
    is_object($msg) && $msg=(array)$msg;
    
    if(is_array($msg)){
        foreach ($msg as $k => $item){
            if(is_array($item)){
                foreach ($item as $i => $val){
                    $record.= $k.' => '.$i.' => '.$val."\n";
                }
            }else{
                $record.=$k.' => '.$item."\n";
            }
        }
    }else{
        $record.=$msg."\n";
    }
   
    $path =$dir .date('Ym');
    //设置路径目录信息
    if(empty($file)){
        $filePath = $path . '/' . date('Y.m.d') . '.log';
    }else{
        $filePath = $path . '/' . $file . '.log';
    }
    
    //目录不存在就创建
    if (!file_exists($path)) {
        //iconv防止中文名乱码
        mkdir(iconv("UTF-8", "utf-8", $path), 0777, true);
    }
    if(file_exists($path)){
        file_put_contents($filePath,$record.PHP_EOL,FILE_APPEND);
    }
    
}

/**
 * uniqid(prefix,more_entropy) prefix 参数为空，则返回 13 个字符串长,more_entropy 参数设置为true，则是 23 个字符串
 * @param string $fix
 * @param int $length
 * @return string
 */
function uid($fix='',$length=13)
{
    $id = $fix.substr(md5(uniqid(mt_rand(), true)),0,$length);
    return $id;
}

