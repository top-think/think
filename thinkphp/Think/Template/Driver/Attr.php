<?php
// +----------------------------------------------------------------------
// | TOPThink
// +----------------------------------------------------------------------
// | Copyright (c) 2009 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// $Id$
namespace Think\Template\Driver;
// 属性类型标签库
class Attr extends \Think\TagLib{
    // 标签定义
    protected $tags   =  array(
        // 标签定义： attr 属性列表 close 是否闭合（0 或者1 默认1） alias 标签别名 level 嵌套层次
            'attr'=>array('attr'=>'var,complex,type,default','close'=>0),
        );

    // <attr var='' complex='' />
    public function _attr($tag,$content){
        $var   = $tag['var'];
        $default =  isset($tag['default'])?$tag['default']:1;
        $read   =   isset($tag['read'])?$tag['read']:false;
        if(!empty($tag['type'])) {
            $parse   =  $this->_{$tag['type']}($var);
        }else{
            $parse   =  '<?php switch($'.$var.'[\'type\']): ';
            $parse   .=  ' case "string":?>'.$this->_string($var,$default,$read);
            $parse   .=  '<?php break;case "num":?>'.$this->_num($var,$default,$read);
            $parse   .=  '<?php break;case "bool":?>'.$this->_bool($var,$default,$read);
            $parse   .=  '<?php break;case "textarea":?>'.$this->_textarea($var,$default,$read);
            $parse   .=  '<?php break;case "text":?>'.$this->_text($var,$default,$read);
            $parse   .=  '<?php break;case "editor":?>'.$this->_editor($var,$default,$read);
            $parse   .=  '<?php break;case "file":?>'.$this->_file($var,$read);
            $parse   .=  '<?php break;case "files":?>'.$this->_files($var,$read);
            $parse   .=  '<?php break;case "radio":?>'.$this->_radio($var,$default,$read);
            $parse   .=  '<?php break;case "checkbox":?>'.$this->_checkbox($var,$default,$read);
            $parse   .=  '<?php break;case "select":?>'.$this->_select($var,$default,$read);
            $parse   .=  '<?php break;case "image":?>'.$this->_image($var,$read);
            $parse   .=  '<?php break;case "images":?>'.$this->_images($var,$read);
            $parse   .=  '<?php break;case "date":?>'.$this->_date($var,$default,$read);
            $parse   .=  '<?php break;case "zone":?>'.$this->_zone($var,$read);
            $parse   .=  '<?php break;case "html":?>'.$this->_html($var,$read);
            $parse   .=  '<?php break;case "dynamic":?>'.$this->_dynamic($var,$read);
            $parse   .=  '<?php break;case "hidden":?>'.$this->_hidden($var,$read);
            $parse   .=  '<?php break;case "verify":?>'.$this->_verify($var,$read);
            $parse   .=  '<?php break;case "password":?>'.$this->_password($var,$default,$read);
            $parse   .=  '<?php break;case "serialize":?>'.$this->_serialize($var,$read);
            $parse   .=  '<?php break;case "link":?>'.$this->_link($var,$read);
            if(!empty($tag['complex'])) {
                $parse   .=  '<?php break;case "complex":?>'.$this->_complex($var,$read);
            }
            $parse   .= '<?php endswitch;?>';
        }
        return $parse;
    }
    
    // 验证码类型
    protected function _verify($var) {
        $parse   =  '<input type="text" name="{$'.$var.'.name}" class=" {$'.$var.'.is_must|getFieldMust} small" /> <img src="__GROUP__/Public/verify/" style="cursor:hand" onclick="this.src=\'__APP__/Public/verify/\'+new Date().getTime()" align="absmiddle" /> ';
        return $parse;
    }

    // 密码类型
    protected function _password($var,$default) {
        $value   =  $default?'{$'.$var.'.value}':'';
        $parse   =  '<input type="password" name="{$'.$var.'.name}" class="{$'.$var.'.is_must|getFieldMust} medium" value="'.$value.'" /> ';
        return $parse;
    }

    // 字符串类型
    protected function _string($var,$default,$read) {
        $value   =  $default?'{$'.$var.'.value}':'';
        if($read) {
            $parse  =   $value;
        }else{
            $parse   =   '<input type="text" class="{$'.$var.'.is_must|getFieldMust} {$'.$var.'.length|default=\'medium\'} {$'.$var.'.readonly}" title="{$'.$var.'.remark}" name="{$'.$var.'.name}" value="'.$value.'" {$'.$var.'.readonly}> ';
        }
        return $parse;
    }

    // 字符串类型
    protected function _link($var,$read) {
        $value   =  '{$'.$var.'.value}';
        if($read) {
            $parse  =   $value;
        }else{
            $parse   =   '<input type="text" class="{$'.$var.'.length|default=\'medium\'} {$'.$var.'.readonly}" title="{$'.$var.'.remark}" name="{$'.$var.'.name}" value="'.$value.'" {$'.$var.'.readonly}> ';
        }
        return $parse;
    }

    // 数字类型
    protected function _num($var,$default,$read) {
        $value   =  $default?'{$'.$var.'.value}':'';
        if($read) {
            $parse  =   $value;
        }else{
            $parse   =  '<input type="text" class="{$'.$var.'.is_must|getFieldMust} {$'.$var.'.length|default=\'small\'} {$'.$var.'.readonly}" title="{$'.$var.'.remark}" check="Number" warning="{$'.$var.'.title}必须是数字" name="{$'.$var.'.name}" value="'.$value.'"  {$'.$var.'.readonly}> ';
        }
        return $parse;
    }

    // 布尔类型 采用下拉列表模拟
    protected function _bool($var,$default,$read) {
        if($read) {
            $parse  =   '<?php $option	=	explode(\',\',$'.$var.'[\'extra\']);?>';
            $parse  =   '<?php echo $'.$var.'[\'value\']==\'0\'?$options[0]:$options[1];unset($options);?>';
        }else{
            $parse   =  '<select name="{$'.$var.'.name}" class="{$'.$var.'.is_must|getFieldMust} {$'.$var.'.length|default=\'medium\'} {$'.$var.'.readonly}" title="{$'.$var.'.remark}"  {$'.$var.'.readonly} <present name="'.$var.'.readonly">onbeforeactivate="return false" onfocus="this.blur()" onmouseover="this.setCapture()" onmouseout="this.releaseCapture()"</present>>
            <option value="">选择</option>
            <?php if (!empty($'.$var.'[\'extra\'])) { $option	=	explode(\',\',$'.$var.'[\'extra\']);?>
            <option '.($default?'<?php if ($'.$var.'[\'value\']==\'0\'){ ?>selected<?php };?>':'').' value="0"> {$option[0]} </option>
            <option '.($default?'<?php if ($'.$var.'[\'value\']==\'1\'){ ?>selected<?php };?>':'').' value="1"> {$option[1]} </option>
            <?php }; ?>
            </select> ';
        }
        return $parse;
    }

    // 文本域类型
    protected function _textarea($var,$default,$read) {
        $value   =  $default?'{$'.$var.'.value}':'';
        if($read) {
            $parse  =   $value;
        }else{
            $parse   =  '<textarea style="overflow:auto" class="{$'.$var.'.is_must|getFieldMust} {$'.$var.'.length|default=\'large\'}  {$'.$var.'.readonly}" title="{$'.$var.'.remark}" name="{$'.$var.'.name}" ROWS="5" COLS="35"  {$'.$var.'.readonly}>'.$value.'</textarea> ';
        }
        return $parse;
    }

    // 文本型
    protected function _text($var,$default,$read) {
        $value   =  $default?'{$'.$var.'.value}':'';
        if($read) {
            $parse  =   $value;
        }else{
            $parse   =  '<script src="__PUBLIC__/Js/jquery.min.js" type="text/javascript"></script>
            <script src="__PUBLIC__/Js/jquery.shortcuts.js" type="text/javascript"></script><textarea style="overflow:auto;width:545px" class="{$'.$var.'.is_must|getFieldMust} {$'.$var.'.length|default=\'huge\'} {$'.$var.'.readonly}" id="{$'.$var.'.name}" title="{$'.$var.'.remark}" name="{$'.$var.'.name}" ROWS="8" COLS="35"  {$'.$var.'.readonly}>'.$value.'</textarea><script> $("#{$'.$var.'.name}").shortcuts();</script>';
        }
        return $parse;
    }

    // 编辑器型
    protected function _editor($var,$default,$read) {
        $value   =  $default?'{$'.$var.'.value}':'';
        if($read) {
            $parse  =   $value;
        }else{
            $parse   =  '<script type="text/javascript"> KE.show({ id : \'{$'.$var.'.name}\'  ,urlType : "absolute"});</script><textarea id="{$'.$var.'.name}"  style="width:550px;height:220px"  name="{$'.$var.'.name}"  {$'.$var.'.readonly}>'.$value.'</textarea> ';
        }
        return $parse;
    }

    // 附件上传型 可配置
    protected function _file($var) {
        $parse   =  '<div class="impBtn  fLeft" ><input type="file" name="{$'.$var.'.name}" class="{$'.$var.'.is_must|getFieldMust} file {$'.$var.'.length|default=\'huge\'} {$'.$var.'.readonly}"  {$'.$var.'.readonly}></div><INPUT TYPE="button" value="删 除" onclick="delPic(\'{$'.$var.'.name}\');" class="button small"  {$'.$var.'.readonly}><input type="hidden" name="__upload_{$'.$var.'.name}" value="{$'.$var.'.extra|base64_encode}"/><neq name="'.$var.'.value" value=""><div class="cBoth">{$'.$var.'.value|extension|showExt} <a href="__UPLOAD__/{$'.$var.'.value}" title="{$'.$var.'.name}" >{$'.$var.'.value}</a></div></neq> ';
        return $parse;
    }

    // 多附件型 
    protected function _files($var) {
        $parse   =  '<input type="button" class="button {$'.$var.'.readonly}" value="添加" onclick="__addFileAttach()"  {$'.$var.'.readonly}/> <INPUT TYPE="button" value="删 除" onclick="delPic(\'{$'.$var.'.name}\');" class="button small {$'.$var.'.readonly}"  {$'.$var.'.readonly}><input type="hidden" name="__upload_{$'.$var.'.name}" value="{$'.$var.'.extra|base64_encode}"/><div id=\'__fileattach__\' style="clear:both"><div class="impBtn  fLeft" ><input type="file" name="{$'.$var.'.name}[]" class="file large {$'.$var.'.readonly}"  {$'.$var.'.readonly}></div></div><script type="text/javascript">
        <!--
    function __addFileAttach(){
        document.getElementById(\'__fileattach__\').innerHTML +=\'<div class="cBoth"><div class="impBtn  fLeft" ><input type="file" name="{$'.$var.'.name}[]" class="file  huge"></div></div> \';
    }
        //-->
        </script>
        <neq name="v.value" value=""><php>$array = explode(\',\',$'.$var.'[\'value\']);</php>
        <iterate name="array" id="attach">
        <div class="cBoth">
            <switch name="attach|extension">
                <case value=\'mp3\'>
                <object type="application/x-shockwave-flash" data="__PUBLIC__/Images/player.swf" width="290" height="24" id="audioplayer1"><param name="movie" value="__PUBLIC__/Images/player.swf" /><param name="FlashVars" value="playerID=1&amp;bg=0xf8f8f8&amp;leftbg=0xeeeeee&amp;lefticon=0x666666&amp;rightbg=0xcccccc&amp;rightbghover=0x999999&amp;righticon=0x666666&amp;righticonhover=0xffffff&amp;text=0x666666&amp;slider=0x666666&amp;track=0xFFFFFF&amp;border=0x666666&amp;loader=0x9FFFB8&amp;soundFile=__UPLOAD__/{$attach}" /><param name="quality" value="high" /><param name="menu" value="false" /><param name="bgcolor" value="#FFFFFF" /></object>
                </case>
                <case value= \'wav\'>
                <embed   src="__UPLOAD__/{$attach}"   width="290" height="24"  style="border:1px solid silver" autostart="false"></embed>.
                </case>
                <case value="gif|jpg|jpeg|png">
                <a href="__UPLOAD__/{$attach}" alt="点击查看原图" target="_blank"><img src="__UPLOAD__/{$vo._module}/{$attach}" class="pic" width="100px" /></a>
                </case>
                <default />{$attach|extension|showExt} <A HREF="__UPLOAD__/{$attach}">{$attach}</A>
            </switch>
        </div>
        </iterate></neq> ';
        return $parse;
    }

    // 单选型 
    //  选项1,选项2,...
    //  选项1:显示1,选项2:显示2,...
    //  @model.id 调用模型
    //  :fun 函数
    protected function _radio($var,$default) {
        $parse   =  '<?php if(0===strpos($'.$var.'[\'extra\'],\':\')):
        $fun  =  substr($'.$var.'[\'extra\'],1);
        $options =  $fun();
        elseif(0===strpos($'.$var.'[\'extra\'],\'@\')):
        $options =  parseT(substr($'.$var.'[\'extra\'],1));
        else:
        $options	=	explode(\',\',$'.$var.'[\'extra\']);
        endif;?>
	<iterate name="options" id="extra">
	<php>$array	=	explode(\':\',$extra);$value	=	$array[0];$show	=	isset($array[1])?$array[1]:$array[0];</php>
	<input type="radio" name="{$'.$var.'.name}" '.($default?'<?php if ($'.$var.'[\'value\']==$value){ ?>checked<?php };?>':'').' value="{$value}" class="{$'.$var.'.readonly}" {$'.$var.'.readonly} /> {$show}
	</iterate> ';
        return $parse;
    }

    // 组合字段
    protected function _complex($var,$read) {
        if($read) {
            $parse = '<iterate name="'.$var.'.complex" id="_v"><attr var="_v" read="1" /> </iterate>';
        }else{
            $parse = '<iterate name="'.$var.'.complex" id="_v"><attr var="_v" /> </iterate>';
        }
        return $parse;
    }

    // 多选型 用多选下拉列表模拟 支持函数定义
    //  选项1,选项2,...
    //  选项1:显示1,选项2:显示2,...
    //  @model.id 调用模型
    //  :fun 函数
    protected function _checkbox($var,$default) {
        $parse   =  '<table class="select" style="width:245px" id="{$'.$var.'.name}">
        <tr><td height="5" colspan="3" class="topTd" ></td></tr>
        <tr><th class="tCenter">选择{$'.$var.'.title}</th></tr>
        <tr><td >
        <select  name="{$'.$var.'.name}[]" class="hMargin medium multiSelect {$'.$var.'.readonly}" multiple="multiple" size="12"  {$'.$var.'.readonly} <present name="'.$var.'.readonly">onbeforeactivate="return false" onfocus="this.blur()" onmouseover="this.setCapture()" onmouseout="this.releaseCapture()"</present>>
        <?php if(0===strpos($'.$var.'[\'extra\'],\':\')):
        $fun  =  substr($'.$var.'[\'extra\'],1);
        $options =  $fun();
        elseif(0===strpos($'.$var.'[\'extra\'],\'@\')):
        $options =  parseT(substr($'.$var.'[\'extra\'],1));
        else:
        $options	=	explode(\',\',$'.$var.'[\'extra\']);
        endif;?>
        <iterate name="options" id="option">
        <php>$array	=	explode(\':\',$option);$value	=	$array[0];$show	=	isset($array[1])?$array[1]:$array[0];</php>
            <option '.($default?'<?php if (in_array($value,explode(",",$'.$var.'[\'value\']),true)){ ?>selected<?php };?>':'').' value="{$value}" >{$show}</option>
            </iterate>
            </select>
            </td>
        </tr>
        <tr><th class="tCenter"><input type="button" onclick="allSelect(\'{$'.$var.'.name}\')" value="全 选" class="submit  ">
        <input type="button" onclick="InverSelect(\'{$'.$var.'.name}\')" value="反 选" class="submit  ">
        <input type="button" onclick="allUnSelect(\'{$'.$var.'.name}\')" value="全 否" class="submit ">
        </th></tr>
        <tr>
        <td height="5" class="bottomTd" >
        </td>
        </tr>
        </table> ';
        return $parse;
    }

    // 枚举型 采用下拉列表模拟
    //  选项1,选项2,...
    //  选项1:显示1,选项2:显示2,...
    //  @model.id 调用模型
    //  :fun 函数
    protected function _select($var,$default,$read) {
        if($read) {
            $parse  =   '<?php if(0===strpos($'.$var.'[\'extra\'],\'@\')):
            $options =  parseT(substr($'.$var.'[\'extra\'],1));
            elseif(0===strpos($'.$var.'[\'extra\'],\':\')):
            $fun  =  substr($'.$var.'[\'extra\'],1);
            $options =  $fun();
            else:
            $options	=	explode(\',\',$'.$var.'[\'extra\']);
            endif;?><iterate name="options" id="option">
        <php>$array	=	explode(\':\',$option);$value	=	$array[0];$show	=	isset($array[1])?$array[1]:$array[0];</php>
        <?php if ($'.$var.'[\'value\']==$value){ echo $show;unset($options);break;}?></iterate>
        ';
        }else{
            $parse   =  '<select name="{$'.$var.'.name}" class="{$'.$var.'.is_must|getFieldMust} {$'.$var.'.length|default=\'medium\'} {$'.$var.'.readonly}" title="{$'.$var.'.remark}"  {$'.$var.'.readonly} <present name="'.$var.'.readonly">onbeforeactivate="return false" onfocus="this.blur()" onmouseover="this.setCapture()" onmouseout="this.releaseCapture()"</present>>
        <option  value=""> --选择-- </option>
        <?php if (!empty($'.$var.'[\'extra\'])) { 
            if(0===strpos($'.$var.'[\'extra\'],\'@\')):
            $options =  parseT(substr($'.$var.'[\'extra\'],1));
            elseif(0===strpos($'.$var.'[\'extra\'],\':\')):
            $fun  =  substr($'.$var.'[\'extra\'],1);
            $options =  $fun();
            else:
            $options	=	explode(\',\',$'.$var.'[\'extra\']);
            endif;?>
        <iterate name="options" id="option">
        <php>$array	=	explode(\':\',$option);$value	=	$array[0];$show	=	isset($array[1])?$array[1]:$array[0];</php>
        <option '.($default?'<?php if ($'.$var.'[\'value\']==$value){ ?>selected<?php };?>':'').' value="{$value}"> {$show} </option>
        </iterate>
        <?php }; ?>
        </select> ';
        }
        return $parse;
    }

    // 序列化型 只支持字符串类型
    // 字段名1:显示名称:样式,...
    protected function _serialize($var) {
        $parse = '<?php $options = explode(\',\',$'.$var.'[\'extra\']);?>
        <iterate name="options" id="option">
        <php>$array	=	explode(\':\',$option);$var	=	$array[0];$show	=	isset($array[1])?$array[1]:\'\';$class = isset($array[2])?$array[2]:\'medium\';$value = $'.$var.'[\'value\'][$var];</php>
        {$show} <input type="text" class="{$class}" title="{$show}" name="{$var}" value="{$value}"  {$'.$var.'.readonly} >
        </iterate>';
        return $parse;
    }

    // 图片型 支持配置
    protected function _image($var,$read) {
        if($read) {
            $parse = '<neq name="v.value" value=""><php>parse_str($'.$var.'[\'extra\'],$extra);$array = explode(\',\',$extra[\'thumbPrefix\']);</php>
	<eq name="extra.thumb" value="1">
	<iterate name="array" id="thumbPre"><a href="__UPLOAD__/{$'.$var.'.value}" title="点击查看原图" target="_blank"><img class="cBoth pic" src="__UPLOAD__/{$thumbPre}{$'.$var.'.value}"></a>&nbsp;&nbsp;</iterate><else/><a href="__UPLOAD__/{$'.$var.'.value}" title="点击查看原图" target="_blank"><img class="cBoth pic" src="__UPLOAD__/{$'.$var.'.value}"></a></eq></neq>';
        }else{
        $parse   =  '<div class="impBtn" ><input type="file" name="{$'.$var.'.name}" class="{$'.$var.'.is_must|getFieldMust} file  {$'.$var.'.length|default=\'medium\'} {$'.$var.'.readonly}"  {$'.$var.'.readonly}></div><div><input type="hidden" class="fNone" name="__upload_{$'.$var.'.name}" value="{$'.$var.'.extra|base64_encode}"/><neq name="v.value" value=""><php>parse_str($'.$var.'[\'extra\'],$extra);$array = explode(\',\',$extra[\'thumbPrefix\']);</php>
	<eq name="extra.thumb" value="1">
	<iterate name="array" id="thumbPre"><a href="__UPLOAD__/{$'.$var.'.value}" title="点击查看原图" target="_blank"><img class="cBoth pic" src="__UPLOAD__/{$thumbPre}{$'.$var.'.value}"></a>&nbsp;&nbsp;</iterate><else/><a href="__UPLOAD__/{$'.$var.'.value}" title="点击查看原图" target="_blank"><img class="cBoth pic" src="__UPLOAD__/{$'.$var.'.value}"></a></eq><INPUT TYPE="button" value="删 除" onclick="delPic(\'{$'.$var.'.name}\');" class="button small"></neq></div> ';
    }
        return $parse;
    }

    // 多图型 
    protected function _images($var) {
        $parse   =  '<input type="button" class="button {$'.$var.'.readonly}" value="添加" onclick="__addAttach()"  {$'.$var.'.readonly} /> <INPUT TYPE="button" value="删 除" onclick="delPic(\'{$'.$var.'.name}\');" class="button small {$'.$var.'.readonly}"  {$'.$var.'.readonly}><input type="hidden" name="__upload_{$'.$var.'.name}" value="{$'.$var.'.extra|base64_encode}"/><div id=\'__attach__\' style="clear:both"><div class="impBtn  fLeft" ><input type="file" name="{$'.$var.'.name}[]" class="file  huge {$'.$var.'.readonly}"  {$'.$var.'.readonly}></div></div><script type="text/javascript">
        <!--
    function __addAttach(){
        document.getElementById(\'__attach__\').innerHTML +=\'<div class="cBoth"><div class="impBtn  fLeft" ><input type="file" name="{$'.$var.'.name}[]" class="file  huge"></div></div> \';
    }
        //-->
        </script>
        <neq name="v.value" value=""><php>$array = explode(\',\',$'.$var.'[\'value\']);</php>
        <iterate name="array" id="attach">
        <div class="cBoth"><a href="__UPLOAD__/{$attach}" title="点击查看原图" target="_blank"><img class="cBoth pic" src="__UPLOAD__/{$thumbPre}{$attach}"></a> &nbsp;&nbsp;</div>
        </iterate></neq> ';
        return $parse;
    }

    // 日期型
    protected function _date($var,$default) {
        $parse   =  '<present name="'.$var.'.readonly"><input type="text" name="{$'.$var.'.name}" value="'.($default?'{$'.$var.'.value|toDate=\'Y-m-d\'}':'').'" class="Wdate {$'.$var.'.is_must|getFieldMust} {$'.$var.'.readonly}" {$'.$var.'.readonly}><else/><input type="text" name="{$'.$var.'.name}" value="'.($default?'{$'.$var.'.value|toDate=\'Y-m-d\'}':'').'" onClick="WdatePicker()" class="Wdate {$'.$var.'.is_must|getFieldMust} " ></present>';
        return $parse;
    }

    // 动态型 用方法控制输出显示
    protected function _dynamic($var) {
        $parse   =  '<php>$fun = $'.$var.'[\'extra\'];echo $fun($'.$var.'[\'value\']);</php> ';
        return $parse;
    }

    // 地区联动
    protected function _zone($var) {
        $parse   =  '<style>#ppc select{ width:100px }</style>
          <script src="__PUBLIC__/Js/ppc/jquery-1.4.js" type="text/javascript"></script>
          <script src="__PUBLIC__/Js/ppc/jquery.provincesCity.1.4.js" type="text/javascript"></script>
          <script src="__PUBLIC__/Js/ppc/provincesdata.js" type="text/javascript"></script>
          <script>
            //调用插件
            $(function(){
                $("#ppc").ProvinceCity(\'{$vo[\'province\']}\', \'{$vo[\'city\']}\', \'{$vo[\'county\']}\');
            });
          </script>
          <div id="ppc"></div> ';
        return $parse;
    }

    // 固定值 采用隐藏字段模拟
    protected function _hidden($var) {
        $parse   =  '<input type="hidden" name="{$'.$var.'.name}" value="{$'.$var.'.value}"> ';
        return $parse;
    }

    // HTML型
    protected function _html($var) {
        $parse   =  '{$'.$var.'.value}';
        return $parse;
    }
}
?>