<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

/**
 * 模板测试
 * @author    Haotong Lin <lofanmi@gmail.com>
 */

namespace tests\thinkphp\library\think\tempplate\taglib;

use think\Template;
use think\template\taglib\Cx;

class templateTest extends \PHPUnit_Framework_TestCase
{
    public function testPhp()
    {
        $template = new template();
        $cx       = new Cx($template);

        $content = <<<EOF
{php}echo \$a;{/php}
EOF;
        $data    = <<<EOF
<?php echo \$a; ?>
EOF;
        $cx->parseTag($content);
        $this->assertEquals($content, $data);
    }

    public function testVolist()
    {
        $template = new template();
        $cx       = new Cx($template);

        $content = <<<EOF
{volist name="list" id="vo" key="key"}

{/volist}
EOF;
        $data    = <<<EOF
<?php if(is_array(\$list)): \$key = 0; \$__LIST__ = \$list;if( count(\$__LIST__)==0 ) : echo "" ;else: foreach(\$__LIST__ as \$key=>\$vo): \$mod = (\$key % 2 );++\$key;?>

<?php endforeach; endif; else: echo "" ;endif; ?>
EOF;
        $cx->parseTag($content);
        $this->assertEquals($content, $data);
    }

    public function testForeach()
    {
        $template = new template();
        $cx       = new Cx($template);

        $content = <<<EOF
{foreach \$list as \$key=>\$val}

{/foreach}
EOF;
        $data    = <<<EOF
<?php foreach(\$list as \$key=>\$val): ?>

<?php endforeach; ?>
EOF;
        $cx->parseTag($content);
        $this->assertEquals($content, $data);

        $content = <<<EOF
{foreach name="list" id="val" key="key"}

{/foreach}
EOF;
        $data    = <<<EOF
<?php if(is_array(\$list)): foreach(\$list as \$key=>\$val): ?>

<?php endforeach; endif; ?>
EOF;
        $cx->parseTag($content);
        $this->assertEquals($content, $data);

    }

    public function testIf()
    {
        $template = new template();
        $cx       = new Cx($template);

        $content = <<<EOF
{if \$var.a==\$var.b}
one
{elseif !empty(\$var.a) /}
two
{else /}
default
{/if}
EOF;
        $data    = <<<EOF
<?php if(\$var['a']==\$var['b']): ?>
one
<?php elseif(!empty(\$var['a'])): ?>
two
<?php else: ?>
default
<?php endif; ?>
EOF;
        $cx->parseTag($content);
        $this->assertEquals($content, $data);
    }

    public function testSwitch()
    {
        $template = new template();
        $cx       = new Cx($template);

        $content = <<<EOF
{switch \$var}
{case \$a /}
a
{/case}
{case b}
b
{/case}
{default /}
default
{/switch}
EOF;
        $data    = <<<EOF
<?php switch(\$var): ?>
<?php case \$a: ?>
a
<?php break; ?>
<?php case "b": ?>
b
<?php break; ?>
<?php default: ?>
default
<?php endswitch; ?>
EOF;
        $cx->parseTag($content);
        $this->assertEquals($content, $data);
    }

    public function testCompare()
    {
        $template = new template();
        $cx       = new Cx($template);

        $content = <<<EOF
{eq name="\$var.a" value="0"}
default
{/eq}
EOF;
        $data    = <<<EOF
<?php if(\$var['a'] == '0'): ?>
default
<?php endif; ?>
EOF;
        $cx->parseTag($content);
        $this->assertEquals($content, $data);

        $content = <<<EOF
{equal name="\$var.a" value="0"}
default
{/equal}
EOF;
        $data    = <<<EOF
<?php if(\$var['a'] == '0'): ?>
default
<?php endif; ?>
EOF;
        $cx->parseTag($content);
        $this->assertEquals($content, $data);

        $content = <<<EOF
{neq name="\$var.a" value="0"}
default
{/neq}
EOF;
        $data    = <<<EOF
<?php if(\$var['a'] != '0'): ?>
default
<?php endif; ?>
EOF;
        $cx->parseTag($content);
        $this->assertEquals($content, $data);

        $content = <<<EOF
{notequal name="\$var.a" value="0"}
default
{/notequal}
EOF;
        $data    = <<<EOF
<?php if(\$var['a'] != '0'): ?>
default
<?php endif; ?>
EOF;
        $cx->parseTag($content);
        $this->assertEquals($content, $data);

        $content = <<<EOF
{gt name="\$var.a" value="0"}
default
{/gt}
EOF;
        $data    = <<<EOF
<?php if(\$var['a'] > '0'): ?>
default
<?php endif; ?>
EOF;
        $cx->parseTag($content);
        $this->assertEquals($content, $data);

        $content = <<<EOF
{egt name="\$var.a" value="0"}
default
{/egt}
EOF;
        $data    = <<<EOF
<?php if(\$var['a'] >= '0'): ?>
default
<?php endif; ?>
EOF;
        $cx->parseTag($content);
        $this->assertEquals($content, $data);

        $content = <<<EOF
{lt name="\$var.a" value="0"}
default
{/lt}
EOF;
        $data    = <<<EOF
<?php if(\$var['a'] < '0'): ?>
default
<?php endif; ?>
EOF;
        $cx->parseTag($content);
        $this->assertEquals($content, $data);

        $content = <<<EOF
{elt name="\$var.a" value="0"}
default
{/elt}
EOF;
        $data    = <<<EOF
<?php if(\$var['a'] <= '0'): ?>
default
<?php endif; ?>
EOF;
        $cx->parseTag($content);
        $this->assertEquals($content, $data);

        $content = <<<EOF
{heq name="\$var.a" value="0"}
default
{/heq}
EOF;
        $data    = <<<EOF
<?php if(\$var['a'] === '0'): ?>
default
<?php endif; ?>
EOF;
        $cx->parseTag($content);
        $this->assertEquals($content, $data);

        $content = <<<EOF
{nheq name="\$var.a" value="0"}
default
{/nheq}
EOF;
        $data    = <<<EOF
<?php if(\$var['a'] !== '0'): ?>
default
<?php endif; ?>
EOF;
        $cx->parseTag($content);
        $this->assertEquals($content, $data);

    }

    public function testRange()
    {
        $template = new template();
        $cx       = new Cx($template);

        $content = <<<EOF
{in name="var" value="1,2,3"}
default
{/in}
EOF;
        $data    = <<<EOF
<?php if(in_array((\$var), explode(',',"1,2,3"))): ?>
default
<?php endif; ?>
EOF;
        $cx->parseTag($content);
        $this->assertEquals($content, $data);

        $content = <<<EOF
{notin name="var" value="1,2,3"}
default
{/notin}
EOF;
        $data    = <<<EOF
<?php if(!in_array((\$var), explode(',',"1,2,3"))): ?>
default
<?php endif; ?>
EOF;
        $cx->parseTag($content);
        $this->assertEquals($content, $data);
    }

    public function testPresent()
    {
        $template = new template();
        $cx       = new Cx($template);

        $content = <<<EOF
{present name="var"}
default
{/present}
EOF;
        $data    = <<<EOF
<?php if(isset(\$var)): ?>
default
<?php endif; ?>
EOF;
        $cx->parseTag($content);
        $this->assertEquals($content, $data);

        $content = <<<EOF
{notpresent name="var"}
default
{/notpresent}
EOF;
        $data    = <<<EOF
<?php if(!isset(\$var)): ?>
default
<?php endif; ?>
EOF;
        $cx->parseTag($content);
        $this->assertEquals($content, $data);
    }

    public function testEmpty()
    {
        $template = new template();
        $cx       = new Cx($template);

        $content = <<<EOF
{empty name="var"}
default
{/empty}
EOF;
        $data    = <<<EOF
<?php if(empty(\$var)): ?>
default
<?php endif; ?>
EOF;
        $cx->parseTag($content);
        $this->assertEquals($content, $data);

        $content = <<<EOF
{notempty name="var"}
default
{/notempty}
EOF;
        $data    = <<<EOF
<?php if(!empty(\$var)): ?>
default
<?php endif; ?>
EOF;
        $cx->parseTag($content);
        $this->assertEquals($content, $data);
    }

    public function testDefined()
    {
        $template = new template();
        $cx       = new Cx($template);

        $content = <<<EOF
{defined name="URL"}
default
{/defined}
EOF;
        $data    = <<<EOF
<?php if(defined("URL")): ?>
default
<?php endif; ?>
EOF;
        $cx->parseTag($content);
        $this->assertEquals($content, $data);

        $content = <<<EOF
{notdefined name="URL"}
default
{/notdefined}
EOF;
        $data    = <<<EOF
<?php if(!defined("URL")): ?>
default
<?php endif; ?>
EOF;
        $cx->parseTag($content);
        $this->assertEquals($content, $data);
    }

    public function testImport()
    {
        $template = new template();
        $cx       = new Cx($template);

        $content = <<<EOF
{import file="base" value="\$name.a" /}
EOF;
        $data    = <<<EOF
<?php if(isset(\$name['a'])): ?><script type="text/javascript" src="__ROOT__/Public/base.js"></script><?php endif; ?>
EOF;
        $cx->parseTag($content);
        $this->assertEquals($content, $data);

        $content = <<<EOF
{import file="base" type="css" /}
EOF;
        $data    = <<<EOF
<link rel="stylesheet" type="text/css" href="__ROOT__/Public/base.css" />
EOF;
        $cx->parseTag($content);
        $this->assertEquals($content, $data);

        $content = <<<EOF
{import file="base" type="php" /}
EOF;
        $data    = <<<EOF
<?php import("base"); ?>
EOF;
        $cx->parseTag($content);
        $this->assertEquals($content, $data);

        $content = <<<EOF
{load file="base.php" value="\$name.a" /}
EOF;
        $data    = <<<EOF
<?php if(isset(\$name['a'])): ?><?php require_cache("base.php"); ?><?php endif; ?>
EOF;
        $cx->parseTag($content);
        $this->assertEquals($content, $data);

        $content = <<<EOF
{js file="base.js" /}
EOF;
        $data    = <<<EOF
<script type="text/javascript" src="base.js"></script>
EOF;
        $cx->parseTag($content);
        $this->assertEquals($content, $data);

        $content = <<<EOF
{css file="base.css" /}
EOF;
        $data    = <<<EOF
<link rel="stylesheet" type="text/css" href="base.css" />
EOF;
        $cx->parseTag($content);
        $this->assertEquals($content, $data);
    }

    public function testAssign()
    {
        $template = new template();
        $cx       = new Cx($template);

        $content = <<<EOF
{assign name="total" value="0" /}
EOF;
        $data    = <<<EOF
<?php \$total = '0'; ?>
EOF;
        $cx->parseTag($content);
        $this->assertEquals($content, $data);
    }

    public function testDefine()
    {
        $template = new template();
        $cx       = new Cx($template);

        $content = <<<EOF
{define name="INFO_NAME" value="test" /}
EOF;
        $data    = <<<EOF
<?php define('INFO_NAME', 'test'); ?>
EOF;
        $cx->parseTag($content);
        $this->assertEquals($content, $data);
    }

    public function testFor()
    {
        $template = new template();

        $content = <<<EOF
{for start="1" end="10" comparison="lt" step="1" name="ii" }
{\$ii}
{/for}
EOF;
        $template->fetch($content);
        $this->expectOutputString('123456789');
    }

}
