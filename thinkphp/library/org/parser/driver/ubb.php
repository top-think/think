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
// | Ubb.php 2013-04-03
// +----------------------------------------------------------------------

namespace org\parser\driver;

class Ubb
{
    /**
     * UBB标签匹配规则
     * @var array
     */
    private $ubb = [
        ['table', '\[table(?:=([\d%]*))?\]', '\[\/table\]', 'width'],
        ['tr', '\[tr\]', '\[\/tr\]', 'tag'],
        ['th', '\[th(?:=([\d%]*)(?:,([\d%]*))?)?\]', '\[\/th\]', 'widthAndHeight'],
        ['td', '\[td(?:=([\d%]*)(?:,([\d%]*))?)?\]', '\[\/td\]', 'widthAndHeight'],
        ['img', '\[img(?:=([\d%]*)(?:,([\d%]*))?)?\]', '\[\/img\]', 'imgWidthAndHeight'],
        ['img', '\[img=(.*?)(?:,([\d%]*)(?:,([\d%]*))?)?\/\]', 'img'],
        ['a', '\[url(?:=(.*?)(?:,([\w\-]*))?)?\]', '\[\/url\]', 'urlClass'],
        ['a', '\[a(?:=(.*?)(?:,([\w\-]*))?)?\]', '\[\/a\]', 'urlClass'],
        ['a', '\[url=(.*?)(?:,([\w\-]*))?\/\]', 'url'],
        ['a', '\[a=(.*?)(?:,([\w\-]*))?\/\]', 'url'],
        ['a', '\[email(?:=([\w\-]*))?\]', '\[\/email\]', 'emailClass'],
        ['ul', '\[ul(?:=([\w\-]*))?\]', '\[\/ul\]', 'class'],
        ['ol', '\[ol(?:=([\w\-]*))?\]', '\[\/ol\]', 'class'],
        ['li', '\[li(?:=([\w\-]*))?\]', '\[\/li\]', 'class'],
        ['span', '\[span(?:=([\w\-]*))?\]', '\[\/span\]', 'class'],
        ['div', '\[div(?:=([\w\-]*))?\]', '\[\/div\]', 'class'],
        ['p', '\[p(?:=([\w\-]*))?\]', '\[\/p\]', 'class'],
        ['strong', '\[b\]', '\[\/b\]', 'tag'],
        ['strong', '\[strong\]', '\[\/strong\]', 'tag'],
        ['i', '\[i\]', '\[\/i\]', 'tag'],
        ['em', '\[em\]', '\[\/em\]', 'tag'],
        ['sub', '\[sub\]', '\[\/sub\]', 'tag'],
        ['sup', '\[sup\]', '\[\/sup\]', 'tag'],
        ['pre', '\[code(?:=([a-z#\+\/]*))?\]', '\[\/code\]', 'code'],
        ['code', '\[line(?:=([a-z#\+\/]*))?\]', '\[\/line\]', 'code'],
    ];

    /**
     * 解析UBB代码为HTML
     * @param  string $content 要解析的UBB代码
     * @return string          解析后的HTML代码
     */
    public function parse($content = '')
    {
        if (empty($content)) {
            return '';
        }

        for ($i = 0, $count = count($this->ubb); $i < $count; $i++) {
            if (count($this->ubb[$i]) == 4) {
                //解析闭合标签
                $content = $this->closeTag($content, $this->ubb[$i]);
            } else {
                $content = $this->onceTag($content, $this->ubb[$i]);
            }
        }

        return nl2br($content);
    }

    /**
     * 解析闭合标签，支持嵌套
     * @param  string $data 要解析的数据
     * @param  array  $rule 解析规则
     * @return string       解析后的内容
     */
    private function closeTag($data, $rule = '')
    {
        static $tag, $reg, $func, $count = 0;
        if (is_string($data)) {
            list($tag, $reg[0], $reg[1], $func) = $rule;
            do {
                $data = preg_replace_callback("/({$reg[0]})(.*?)({$reg[1]})/is",
                    [$this, 'closeTag'], $data);
            } while ($count && $count--); //递归解析，直到嵌套解析完毕
            return $data;
        } elseif (is_array($data)) {
            $num = count($data);
            if (preg_match("/{$reg[0]}/is", $data[$num - 2])) {
                //存在嵌套，进一步解析
                $count          = 1;
                $data[$num - 2] = preg_replace_callback("/({$reg[0]})(.*?)({$reg[1]})/is",
                    [$this, 'closeTag'], $data[$num - 2] . $data[$num - 1]);
                return $data[1] . $data[$num - 2];
            } else {
                //不存在嵌套，直接解析内容
                $parse          = '_' . $func;
                $data[$num - 2] = trim($data[$num - 2], "\r\n"); //去掉标签内容两端的换行符
                return $this->$parse($tag, $data);
            }
        }
    }

    /**
     * 解析单标签
     * @param  string $data 要解析的数据
     * @param  array  $rule 解析规则
     * @return string       解析后的内容
     */
    private function onceTag($data, $rule = '')
    {
        list($tag, $reg, $func) = $rule;
        return preg_replace_callback("/{$reg}/is", [$this, '_' . $func], $data);
    }

    /**
     * 解析img单标签
     * @param  array $data 解析数据
     * @return string      解析后的标签
     */
    private function _img($data)
    {
        $data[4] = $data[1];
        return $this->_imgWidthAndHeight('', $data);
    }

    /**
     * 解析url单标签
     * @param  array $data 解析数据
     * @return string      解析后的标签
     */
    private function _url($data)
    {
        $data[3] = $data[2];
        $data[4] = $data[2] = $data[1];
        return $this->_urlClass('', $data);
    }

    /**
     * 解析没有属性的标签
     * @param  string $name 标签名
     * @param  array  $data 解析数据 [2] - 标签内容
     * @return string       解析后的标签
     */
    private function _tag($name, $data)
    {
        return "<{$name}>{$data[2]}</{$name}>";
    }

    /**
     * 解析代码
     * @param  string $name 标签名
     * @param  array  $data 解析数据 [2] - 语言类型，[3] - 代码内容
     * @return string       解析后的标签
     */
    private function _code($name, $data)
    {
        $fix = ('pre' == $name) ? ['<pre>', '</pre>'] : ['', ''];
        if (empty($data[2])) {
            $data = "{$fix[0]}<code>{$data[3]}</code>{$fix[1]}";
        } else {
            $data = "{$fix[0]}<code data-lang=\"{$data[2]}\">{$data[3]}</code>{$fix[1]}";
        }
        return $data;
    }

    /**
     * 解析含有width属性的标签
     * @param  string $name 标签名
     * @param  array  $data 解析数据 [2] - width, [3] - 标签内容
     * @return string       解析后的标签
     */
    private function _width($name, $data)
    {
        if (empty($data[2])) {
            $data = "<{$name}>{$data[3]}</{$name}>";
        } else {
            $data = "<{$name} width=\"{$data[2]}\">{$data[3]}</{$name}>";
        }
        return $data;
    }

    /**
     * 解析含有width和height属性的标签
     * @param  string $name 标签名
     * @param  array  $data 解析数据 [2] - width, [3] - height, [4] - 标签内容
     * @return string       解析后的标签
     */
    private function _widthAndHeight($name, $data)
    {
        if (empty($data[2]) && empty($data[3])) {
            $data = "<{$name}>{$data[4]}</{$name}>";
        } elseif (!empty($data[2]) && empty($data[3])) {
            $data = "<{$name} width=\"{$data[2]}\">{$data[4]}</{$name}>";
        } elseif (empty($data[2]) && !empty($data[3])) {
            $data = "<{$name} height=\"{$data[3]}\">{$data[4]}</{$name}>";
        } else {
            $data = "<{$name} width=\"{$data[2]}\" height=\"{$data[3]}\">{$data[4]}</{$name}>";
        }
        return $data;
    }

    /**
     * 解析含有width和height属性的图片标签
     * @param  string $name 标签名
     * @param  array  $data 解析数据 [2] - width, [3] - height, [4] - 图片URL
     * @return string       解析后的标签
     */
    private function _imgWidthAndHeight($name, $data)
    {
        if (empty($data[2]) && empty($data[3])) {
            $data = "<img src=\"{$data[4]}\" />";
        } elseif (!empty($data[2]) && empty($data[3])) {
            $data = "<img width=\"{$data[2]}\" src=\"{$data[4]}\" />";
        } elseif (empty($data[2]) && !empty($data[3])) {
            $data = "<img height=\"{$data[3]}\" src=\"{$data[4]}\" />";
        } else {
            $data = "<img width=\"{$data[2]}\" height=\"{$data[3]}\" src=\"{$data[4]}\" />";
        }
        return $data;
    }

    /**
     * 解析含有class属性的标签
     * @param  string $name 标签名
     * @param  array  $data 解析数据 [2] - class, [3] - 标签内容
     * @return string       解析后的标签
     */
    private function _class($name, $data)
    {
        if (empty($data[2])) {
            $data = "<{$name}>{$data[3]}</{$name}>";
        } else {
            $data = "<{$name} class=\"{$data[2]}\">{$data[3]}</{$name}>";
        }
        return $data;
    }

    /**
     * 解析含有class属性的url标签
     * @param  string $name 标签名
     * @param  array  $data 解析数据 [2] - url, [3] - text
     * @return string       解析后的标签
     */
    private function _urlClass($name, $data)
    {
        empty($data[2]) && $data[2] = $data[4];
        if (empty($data[3])) {
            $data = "<a href=\"{$data[2]}\">{$data[4]}</a>";
        } else {
            $data = "<a href=\"{$data[2]}\" class=\"{$data[3]}\">{$data[4]}</a>";
        }
        return $data;
    }

    /**
     * 解析含有class属性的email标签
     * @param  string $name 标签名
     * @param  array  $data 解析数据 [2] - class, [3] - email地址
     * @return string       解析后的标签
     */
    private function _emailClass($name, $data)
    {
        //不是正确的EMAIL则不解析
        if (preg_match('/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/', $data[3])) {
            return $data[0];
        }

        //编码email地址，防治被采集
        $email = $this->encodeEmailAddress($data[3]);

        if (empty($data[2])) {
            $data = "<a href=\"{$email[0]}\">{$email[1]}</a>";
        } else {
            $data = "<a href=\"{$email[0]}\" class=\"{$data[2]}\">{$email[1]}</a>";
        }
        return $data;
    }

    /**
     * 编码EMAIL地址，可以防治部分采集软件
     * @param  string $addr EMAIL地址
     * @return array        编码后的EMAIL地址 [0] - 带mailto, [1] - 不带mailto
     */
    private function encodeEmailAddress($addr)
    {
        $addr  = "mailto:" . $addr;
        $chars = preg_split('/(?<!^)(?!$)/', $addr);
        $seed  = (int) abs(crc32($addr) / strlen($addr)); # Deterministic seed.

        foreach ($chars as $key => $char) {
            $ord = ord($char);
            # Ignore non-ascii chars.
            if ($ord < 128) {
                $r = ($seed * (1 + $key)) % 100; # Pseudo-random function.
                # roughly 10% raw, 45% hex, 45% dec
                # '@' *must* be encoded. I insist.
                if ($r > 90 && '@' != $char) /* do nothing */;
                elseif ($r < 45) {
                    $chars[$key] = '&#x' . dechex($ord) . ';';
                } else {
                    $chars[$key] = '&#' . $ord . ';';
                }

            }
        }

        $addr = implode('', $chars);
        $text = implode('', array_slice($chars, 7)); # text without `mailto:`

        return [$addr, $text];
    }
}
