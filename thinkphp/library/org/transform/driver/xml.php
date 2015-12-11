<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi.cn@gmail.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace org\transform\driver;

class Xml
{
    /**
     * XML数据默认配置项
     * @var array
     */
    private $config = [
        'root_name' => 'think', //根节点名称
        'root_attr' => [], //根节点属性
        'item_name' => 'item', //数字节点转换的名称
        'item_key'  => 'id', //数字节点转换的属性名
    ];

    /**
     * 编码XML数据
     * @param  mixed $data   被编码的数据
     * @param  array $config 数据配置项
     * @return string        编码后的XML数据
     */
    public function encode($data, array $config = [])
    {
        //初始化配置
        $config = array_merge($this->config, $config);

        //创建XML对象
        $xml = new \SimpleXMLElement("<{$config['root_name']}></{$config['root_name']}>");
        self::data2xml($xml, $data, $config['item_name'], $config['item_key']);
        return $xml->asXML();
    }

    /**
     * 解码XML数据
     * @param  string  $str    XML字符串
     * @param  boolean $assoc  是否转换为数组
     * @param  array   $config 数据配置项
     * @return string          解码后的XML数据
     */
    public function decode($str, $assoc = true, array $config = [])
    {
        //初始化配置
        $config = array_merge($this->config, $config);

        //创建XML对象
        $xml = new \SimpleXMLElement($str);
        if ($assoc) {
            self::xml2data($xml, $data, $config['item_name'], $config['item_key']);
            return $data;
        }

        return $xml;
    }

    /**
     * 数据XML编码
     * @static
     * @access public
     * @param  mixed  $data 数据
     * @param  string $item 数字索引时的节点名称
     * @param  string $id   数字索引key转换为的属性名
     * @return string
     */
    public static function data2xml(\SimpleXMLElement $xml, $data, $item = 'item', $id = 'id')
    {
        foreach ($data as $key => $value) {
            //指定默认的数字key
            if (is_numeric($key)) {
                $id && $val = $key;
                $key        = $item;
            }

            //添加子元素
            if (is_array($value) || is_object($value)) {
                $child = $xml->addChild($key);
                self::data2xml($child, $value, $item, $id);
            } else {
                $child = $xml->addChild($key, $value);
            }

            //记录原来的key
            isset($val) && $child->addAttribute($id, $val);
        }
    }

    /**
     * 数据XML解码
     * @static
     * @access public
     * @param  SimpleXMLElement $xml  xml对象
     * @param  array            $data 解码后的数据
     * @param  string           $item 数字索引时的节点名称
     * @param  string           $id   数字索引key转换为的属性名
     */
    public static function xml2data(SimpleXMLElement $xml, &$data, $item = 'item', $id = 'id')
    {
        foreach ($xml->children() as $items) {
            $key  = $items->getName();
            $attr = $items->attributes();
            if ($key == $item && isset($attr[$id])) {
                $key = strval($attr[$id]);
            }

            if ($items->count()) {
                self::xml2data($items, $val);
            } else {
                $val = strval($items);
            }

            $data[$key] = $val;
        }
    }
}
