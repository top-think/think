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

namespace tests\thinkphp\library\think;

use think\Template;

class templateTest extends \PHPUnit_Framework_TestCase
{
    public function testVar()
    {
        $template = new Template();

        $content = <<<EOF
{\$name.a.b|default='test'}
EOF;
        $data = <<<EOF
<?php echo (isset(\$name['a']['b']) && (\$name['a']['b'] !== '')?\$name['a']['b']:'test'); ?>
EOF;

        $template->parse($content);
        $this->assertEquals($content, $data);

        $content = <<<EOF
{\$name.a??'test'}
EOF;
        $data = <<<EOF
<?php echo isset(\$name['a']) ? \$name['a'] : 'test'; ?>
EOF;

        $template->parse($content);
        $this->assertEquals($content, $data);

        $content = <<<EOF
{\$name.a?='test'}
EOF;
        $data = <<<EOF
<?php if(!empty(\$name['a'])) echo 'test'; ?>
EOF;

        $template->parse($content);
        $this->assertEquals($content, $data);

        $content = <<<EOF
{\$name.a?:'test'}
EOF;
        $data = <<<EOF
<?php echo !empty(\$name['a'])?\$name['a']:'test'; ?>
EOF;

        $template->parse($content);
        $this->assertEquals($content, $data);

        $content = <<<EOF
{\$name.a?\$name.b:'no'}
EOF;
        $data = <<<EOF
<?php echo !empty(\$name['a'])?\$name['b']:'no'; ?>
EOF;

        $template->parse($content);
        $this->assertEquals($content, $data);

        $content = <<<EOF
{\$name.a==\$name.b?='test'}
EOF;
        $data = <<<EOF
<?php if(\$name['a']==\$name['b']) echo 'test'; ?>
EOF;

        $template->parse($content);
        $this->assertEquals($content, $data);

        $content = <<<EOF
{\$name.a==\$name.b?'a':'b'}
EOF;
        $data = <<<EOF
<?php echo (\$name['a']==\$name['b'])?'a':'b'; ?>
EOF;

        $template->parse($content);
        $this->assertEquals($content, $data);

        $content = <<<EOF
{\$name.a|default='test'==\$name.b?'a':'b'}
EOF;
        $data = <<<EOF
<?php echo ((isset(\$name['a']) && (\$name['a'] !== '')?\$name['a']:'test')==\$name['b'])?'a':'b'; ?>
EOF;

        $template->parse($content);
        $this->assertEquals($content, $data);

        $content = <<<EOF
{:ltrim(rtrim(\$name.a))}
EOF;
        $data = <<<EOF
<?php echo ltrim(rtrim(\$name['a'])); ?>
EOF;

        $template->parse($content);
        $this->assertEquals($content, $data);

        $content = <<<EOF
{~echo(trim(\$name.a))}
EOF;
        $data = <<<EOF
<?php echo(trim(\$name['a'])); ?>
EOF;

        $template->parse($content);
        $this->assertEquals($content, $data);

        $content = <<<EOF
{+\$name.a}
EOF;
        $data = <<<EOF
<?php echo +\$name['a']; ?>
EOF;

        $template->parse($content);
        $this->assertEquals($content, $data);

        $content = <<<EOF
{/*\$name*/}
EOF;
        $data = '';

        $template->parse($content);
        $this->assertEquals($content, $data);

    }

    public function testVarIdentify()
    {
        $config['tpl_begin']        = '<#';
        $config['tpl_end']          = '#>';
        $config['tpl_var_identify'] = '';
        $template                   = new Template($config);

        $content = <<<EOF
<#\$info.a??'test'#>
EOF;
        $data = <<<EOF
<?php echo (is_array(\$info)?\$info['a']:\$info->a) ? (is_array(\$info)?\$info['a']:\$info->a) : 'test'; ?>
EOF;

        $template->parse($content);
        $this->assertEquals($content, $data);

        $content = <<<EOF
<#\$info.a==\$info.b?='test'#>
EOF;
        $data = <<<EOF
<?php if((is_array(\$info)?\$info['a']:\$info->a)==(is_array(\$info)?\$info['b']:\$info->b)) echo 'test'; ?>
EOF;

        $template->parse($content);
        $this->assertEquals($content, $data);

        $content = <<<EOF
<#\$info.a|default='test'?'yes':'no'#>
EOF;
        $data = <<<EOF
<?php echo ((is_array(\$info)?\$info['a']:\$info->a) !== ''?(is_array(\$info)?\$info['a']:\$info->a):'test')?'yes':'no'; ?>
EOF;

        $template->parse($content);
        $this->assertEquals($content, $data);
    }

    public function testTag()
    {
        $template = new Template();

        $content = <<<EOF
{if \$var.a==\$var.b}
one
{elseif !empty(\$var.a) /}
two
{else /}
default
{/if}
EOF;
        $data = <<<EOF
<?php if(\$var['a']==\$var['b']): ?>
one
<?php elseif(!empty(\$var['a'])): ?>
two
<?php else: ?>
default
<?php endif; ?>
EOF;
        $template->parse($content);
        $this->assertEquals($content, $data);

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
        $data = <<<EOF
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
        $template->parse($content);
        $this->assertEquals($content, $data);

        $content = <<<EOF
{foreach \$list as \$key=>\$val}

{/foreach}
EOF;
        $data = <<<EOF
<?php foreach(\$list as \$key=>\$val): ?>

<?php endforeach; ?>
EOF;
        $template->parse($content);
        $this->assertEquals($content, $data);

        $content = <<<EOF
{foreach name="list" id="val" key="key"}

{/foreach}
EOF;
        $data = <<<EOF
<?php if(is_array(\$list)): foreach(\$list as \$key=>\$val): ?>

<?php endforeach; endif; ?>
EOF;
        $template->parse($content);
        $this->assertEquals($content, $data);

    }
}
