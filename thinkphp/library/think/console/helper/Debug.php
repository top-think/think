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


class Debug extends Helper
{

    private $colors  = ['black', 'red', 'green', 'yellow', 'blue', 'magenta', 'cyan', 'white'];
    private $started = [];
    private $count   = -1;

    /**
     * 启动调试会话的格式
     * @param string $id      会话的 id
     * @param string $message 要显示的消息
     * @param string $prefix  要使用的前缀
     * @return string
     */
    public function start($id, $message, $prefix = 'RUN')
    {
        $this->started[$id] = ['border' => ++$this->count % count($this->colors)];

        return sprintf("%s<bg=blue;fg=white> %s </> <fg=blue>%s</>\n", $this->getBorder($id), $prefix, $message);
    }

    /**
     * 添加设置会话的进度条格式
     * @param string $id          会话的 id
     * @param string $buffer      要显示的消息
     * @param bool   $error       是否输出错误
     * @param string $prefix      输出的前缀
     * @param string $errorPrefix 输出错误的前缀
     * @return string
     */
    public function progress($id, $buffer, $error = false, $prefix = 'OUT', $errorPrefix = 'ERR')
    {
        $message = '';

        if ($error) {
            if (isset($this->started[$id]['out'])) {
                $message .= "\n";
                unset($this->started[$id]['out']);
            }
            if (!isset($this->started[$id]['err'])) {
                $message .= sprintf("%s<bg=red;fg=white> %s </> ", $this->getBorder($id), $errorPrefix);
                $this->started[$id]['err'] = true;
            }

            $message .= str_replace("\n", sprintf("\n%s<bg=red;fg=white> %s </> ", $this->getBorder($id), $errorPrefix), $buffer);
        } else {
            if (isset($this->started[$id]['err'])) {
                $message .= "\n";
                unset($this->started[$id]['err']);
            }
            if (!isset($this->started[$id]['out'])) {
                $message .= sprintf("%s<bg=green;fg=white> %s </> ", $this->getBorder($id), $prefix);
                $this->started[$id]['out'] = true;
            }

            $message .= str_replace("\n", sprintf("\n%s<bg=green;fg=white> %s </> ", $this->getBorder($id), $prefix), $buffer);
        }

        return $message;
    }

    /**
     * 停止一个会话
     * @param string $id         会话的 id
     * @param string $message    要显示的消息
     * @param bool   $successful 是否显示成功消息
     * @param string $prefix     前缀
     * @return string
     */
    public function stop($id, $message, $successful, $prefix = 'RES')
    {
        $trailingEOL = isset($this->started[$id]['out']) || isset($this->started[$id]['err']) ? "\n" : '';

        if ($successful) {
            return sprintf("%s%s<bg=green;fg=white> %s </> <fg=green>%s</>\n", $trailingEOL, $this->getBorder($id), $prefix, $message);
        }

        $message = sprintf("%s%s<bg=red;fg=white> %s </> <fg=red>%s</>\n", $trailingEOL, $this->getBorder($id), $prefix, $message);

        unset($this->started[$id]['out'], $this->started[$id]['err']);

        return $message;
    }

    /**
     * @param string $id
     * @return string
     */
    private function getBorder($id)
    {
        return sprintf('<bg=%s> </>', $this->colors[$this->started[$id]['border']]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'debug_formatter';
    }
}