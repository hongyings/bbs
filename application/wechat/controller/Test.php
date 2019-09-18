<?php
/**
 * Created by PhpStorm.
 * User: 17610
 * Date: 2019/9/5
 * Time: 13:56
 */

namespace app\wechat\controller;

use EasyWeChat\Factory;

class Test extends Base
{
    public function version()
    {
        var_dump(phpinfo());
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
                                        "key"=> "foo",
                                        "sub_button"=>[]
                                    ],
                                    [
                                        "type"=> "scancode_push",
                                        "name"=> "扫码推事件",
                                        "key"=> "boo",
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
                        ],
                        [
                            "name"=> "发图",
                            "sub_button"=>[
                                [
                                    "type"=> "pic_sysphoto",
                                    "name"=> "拍照发图",
                                    "key"=> "coo",
                                    "sub_button"=>[]
                                ],
                                [
                                    "type"=>"pic_photo_or_album",
                                    "name"=> "相册发图",
                                    "key"=> "doo",
                                    "sub_button"=>[]
                                ],
                                [
                                    "type"=>"pic_weixin",
                                    "name"=> "微信发图",
                                    "key"=> "eoo",
                                    "sub_button"=>[]
                                ],
                                [
                                    "type"=> "location_select",
                                    "name"=> "发送定位",
                                    "key"=> "hoo",
                                    "sub_button"=>[]
                                ],
                                
                            ]
                        ]
        ];
        
        $app = Factory::officialAccount(cache(self::APPID));
        $res = $app->menu->create($buttons);
//        $current = $app->menu->current();
        var_dump($res);
    
    }
    public function scene()
    {
        $app = Factory::officialAccount(cache(self::APPID));
        $result = $app->qrcode->temporary('foo', 6 * 24 * 3600);
        var_dump($result);
    }
}