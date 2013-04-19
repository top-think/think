<?php
// +----------------------------------------------------------------------
// | TOPThink [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://topthink.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi.cn@gmail.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Think\Transform\Driver;

class Xml{
    /**
     * XML数据默认配置项
     * @var array
     */
    private $config = [
        'root_name' => 'think', //根节点名称
        'root_attr' => [],      //根节点属性
        'item_name' => 'item',  //数字节点转换的名称
        'item_key'  => 'id',    //数字节点转换的属性名
        'encoding'  => 'utf-8', //字符集编码
    ];

    /**
     * 编码XML数据
     * @param  mixed $data   被编码的数据
     * @param  array $config 数据配置项
     * @return string        编码后的XML数据
     */
    public function encode($data, array $config = []) {
        //初始化配置
        $config = array_merge($this->config, $config);

        if(is_array($config['root_attr'] && !empty($config['root_attr']))){
            $attr = [];
            foreach ($config['root_attr'] as $key => $value) {
                $attr[] = "{$key}=\"{$value}\"";
            }
            $attr = implode(' ', $attr);
        }
        $attr  = empty($attr) ? '' : " {$attr}";
        $xml   = "<?xml version=\"1.0\" encoding=\"{$config['encoding']}\"?>";
        $xml  .= "<{$config['root_name']}{$attr}>";
        $xml  .= self::data2xml($data, $config['item_name'], $config['item_key']);
        $xml  .= "</{$config['root_name']}>";
        return $xml;
    }

    public function decode($data, $assoc = true, array $config = []){
        //初始化配置
        $config = array_merge($this->config, $config);

        //创建XML对象
        $xml  = new SimpleXMLElement($data);
        self::xml2data($xml, $data, $config['item_name'], $config['item_key']);
        return $data;
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
    static public function data2xml($data, $item = 'item', $id = 'id') {
        $xml = $attr = '';
        foreach ($data as $key => $val) {
            if(is_numeric($key)){
                $id && $attr = " {$id}=\"{$key}\"";
                $key = $item;
            }
            $xml .= "<{$key}{$attr}>";
            $xml .= (is_array($val) || is_object($val)) ? self::data2xml($val, $item, $id) : $val;
            $xml .= "</{$key}>";
        }
        return $xml;
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
    static public function xml2data(SimpleXMLElement $xml, &$data, $item = 'item', $id = 'id'){
        foreach ($xml as $items) {
            $key  = $items->getName();
            $attr = $items->attributes();
            if($key == $item && isset($attr[$id])){
                $key = strval($attr[$id]);
            }

            $child = $items->children();
            if(empty($child)){
                $val = strval($items);
            } else {
                self::xml2data($child, $val);
            }

            $data[$key] = $val;
        }
    }
}