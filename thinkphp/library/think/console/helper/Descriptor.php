<?php
// +----------------------------------------------------------------------
// | TopThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2015 http://www.topthink.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: zhangyajun <448901948@qq.com>
// +----------------------------------------------------------------------

namespace think\console\helper;

use think\console\helper\descriptor\Descriptor as OutputDescriptor;
use think\console\Output;

class Descriptor extends Helper
{

    /**
     * @var OutputDescriptor
     */
    private $descriptor;

    /**
     * 构造方法
     */
    public function __construct()
    {
        $this->descriptor = new OutputDescriptor();
    }

    /**
     * 描述
     * @param Output $output
     * @param object $object
     * @param array  $options
     * @throws \InvalidArgumentException
     */
    public function describe(Output $output, $object, array $options = [])
    {
        $options = array_merge([
            'raw_text' => false
        ], $options);

        $this->descriptor->describe($output, $object, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'descriptor';
    }
}
