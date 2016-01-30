<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2015 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------

namespace think\console\helper;

use think\console\output\Formatter as OutputFormatter;

class Formatter extends Helper
{

    /**
     * 设置消息在某一节的格式
     * @param string $section 节名称
     * @param string $message 消息
     * @param string $style   样式
     * @return string
     */
    public function formatSection($section, $message, $style = 'info')
    {
        return sprintf('<%s>[%s]</%s> %s', $style, $section, $style, $message);
    }

    /**
     * 设置消息作为文本块的格式
     * @param string|array $messages 消息
     * @param string       $style    样式
     * @param bool         $large    是否返回一个大段文本
     * @return string The formatter message
     */
    public function formatBlock($messages, $style, $large = false)
    {
        if (!is_array($messages)) {
            $messages = [$messages];
        }

        $len   = 0;
        $lines = [];
        foreach ($messages as $message) {
            $message = OutputFormatter::escape($message);
            $lines[] = sprintf($large ? '  %s  ' : ' %s ', $message);
            $len     = max($this->strlen($message) + ($large ? 4 : 2), $len);
        }

        $messages = $large ? [str_repeat(' ', $len)] : [];
        for ($i = 0; isset($lines[$i]); ++$i) {
            $messages[] = $lines[$i] . str_repeat(' ', $len - $this->strlen($lines[$i]));
        }
        if ($large) {
            $messages[] = str_repeat(' ', $len);
        }

        for ($i = 0; isset($messages[$i]); ++$i) {
            $messages[$i] = sprintf('<%s>%s</%s>', $style, $messages[$i], $style);
        }

        return implode("\n", $messages);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'formatter';
    }
}