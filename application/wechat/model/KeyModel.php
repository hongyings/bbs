<?php
/**
 * Created by PhpStorm.
 * User: 17610
 * Date: 2019/8/29
 * Time: 14:18
 */

namespace app\wechat\model;
use think\Model;
use think\Db;

class KeyModel extends Model
{
    const table = 'wechat_keys';
    public function __construct($data = [])
    {
        parent::__construct($data);
        
    }
    
   //获取自定义消息
    public static function getKeyMsg($fields,$where)
    {
       return Db::name(self::table)->field($fields)->where($where)->order('sort asc')->select();
    }
}