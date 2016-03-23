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

namespace think\console\output;


use think\console\Output;

class Null extends Output
{
    /** @noinspection PhpMissingParentConstructorInspection */
    public function __construct()
    {
        
    }

    /**
     * {@inheritdoc}
     */
    public function setFormatter(Formatter $formatter)
    {
        // do nothing
    }

    /**
     * {@inheritdoc}
     */
    public function getFormatter()
    {
        // to comply with the interface we must return a OutputFormatterInterface
        return new Formatter();
    }

    /**
     * {@inheritdoc}
     */
    public function setDecorated($decorated)
    {
        // do nothing
    }

    /**
     * {@inheritdoc}
     */
    public function isDecorated()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function setVerbosity($level)
    {
        // do nothing
    }

    /**
     * {@inheritdoc}
     */
    public function getVerbosity()
    {
        return self::VERBOSITY_QUIET;
    }

    public function isQuiet()
    {
        return true;
    }

    public function isVerbose()
    {
        return false;
    }

    public function isVeryVerbose()
    {
        return false;
    }

    public function isDebug()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function writeln($messages, $options = self::OUTPUT_NORMAL)
    {
        // do nothing
    }

    /**
     * {@inheritdoc}
     */
    public function write($messages, $newline = false, $options = self::OUTPUT_NORMAL)
    {
        // do nothing
    }
}