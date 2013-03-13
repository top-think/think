<?php
// +----------------------------------------------------------------------
// | TOPThink [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://topthink.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// $Id$
namespace Think;
class Input {
    static private $filter =   null;   // 输入过滤
    static private $_input  =   array('get','post','request','env','server','cookie','session','globals','files','call');

    //html标签设置
    public static $htmlTags = array(
        'allow' => 'table|td|th|tr|i|b|u|strong|img|p|br|div|strong|em|ul|ol|li|dl|dd|dt|a',
        'ban' => 'html|head|meta|link|base|basefont|body|bgsound|title|style|script|form|iframe|frame|frameset|applet|id|ilayer|layer|name|script|style|xml',
    );

    /**
     +----------------------------------------------------------
     * 魔术方法 有不存在的操作的时候执行
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $type 输入数据类型
     * @param array $args 参数 array(key,filter,default)
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
    static public function __callStatic($type,$args=array()) {
        $type    =   strtolower(trim($type));
        if(in_array($type,self::$_input,true)) {
            switch($type) {
                case 'get':      $input      =& $_GET;break;
                case 'post':     $input      =& $_POST;break;
                case 'put'     :   parse_str(file_get_contents('php://input'), $input);break;
                case 'param'   :  
                    switch($_SERVER['REQUEST_METHOD']) {
                        case 'POST':
                            $input  =  $_POST;
                            break;
                        case 'PUT':
                            parse_str(file_get_contents('php://input'), $input);
                            break;
                        default:
                            $input  =  $_GET;
                    }
                    break;
                case 'request': $input      =& $_REQUEST;break;
                case 'env':      $input      =& $_ENV;break;
                case 'server':   $input      =& $_SERVER;break;
                case 'cookie':   $input      =& $_COOKIE;break;
                case 'session':  $input      =& $_SESSION;break;
                case 'globals':   $input      =& $GLOBALS;break;
                case 'files':      $input      =& $_FILES;break;
                default:return NULL;
            }

            if(0==count($args)) {
                // 返回全部数据
                return $input;
            }elseif(array_key_exists($args[0],$input)) {
                $filters	=	isset($args[1])?$args[1]:self::$filter;
                $filters    =   explode(',',$filters);
                $data	 =	 $input[$args[0]];
                foreach($filters as $filter){
                    if(is_callable($filter)) {
                        $data   =   is_array($data)?array_map($filter,$data):$filter($data); // 参数过滤
                    }
                }
            }else{
                // 不存在指定输入
                $data	 =	 isset($args[2])?$args[2]:NULL;
            }
            return $data;
        }
    }

    /**
     +----------------------------------------------------------
     * 设置数据过滤方法
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param mixed $filter 过滤方法
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    static public function filter($filter) {
        self::$filter   =   $filter;
    }

    /**
     +----------------------------------------------------------
     * 字符MagicQuote转义过滤
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    static public function noGPC() {
        if ( get_magic_quotes_gpc() ) {
           $_POST = stripslashes_deep($_POST);
           $_GET = stripslashes_deep($_GET);
           $_COOKIE = stripslashes_deep($_COOKIE);
           $_REQUEST= stripslashes_deep($_REQUEST);
        }
    }

    /**
     +----------------------------------------------------------
     * 处理字符串，以便可以正常进行搜索
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $string 要处理的字符串
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    static public function forSearch($string) {
        return str_replace( array('%','_'), array('\%','\_'), $string );
    }

    /**
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $string 要处理的字符串
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    static public function forShow($string) {
        return self::nl2Br( self::hsc($string) );
    }

    /**
     +----------------------------------------------------------
     * 处理纯文本数据，以便在textarea标签中显示
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $string 要处理的字符串
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    static public function forTarea($string) {
        return str_ireplace(array('<textarea>','</textarea>'), array('&lt;textarea>','&lt;/textarea>'), $string);
    }

    /**
     +----------------------------------------------------------
     * 将数据中的单引号和双引号进行转义
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $text 要处理的字符串
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    static public function forTag($string) {
        return str_replace(array('"',"'"), array('&quot;','&#039;'), $string);
    }

    /**
     +----------------------------------------------------------
     * 把换行转换为<br />标签
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $string 要处理的字符串
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    static public function nl2Br($string) {
        return nl2Br($string);
    }

    /**
     +----------------------------------------------------------
     * 如果 magic_quotes_gpc 为关闭状态，这个函数可以转义字符串
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $string 要处理的字符串
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    static public function addSlashes($string) {
        if (!get_magic_quotes_gpc()) {
            $string = addslashes($string);
        }
        return $string;
    }

    /**
     +----------------------------------------------------------
     * 从$_POST，$_GET，$_COOKIE，$_REQUEST等数组中获得数据
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $string 要处理的字符串
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    static public function getVar($string) {
        return self::stripSlashes($string);
    }

    /**
     +----------------------------------------------------------
     * 如果 magic_quotes_gpc 为开启状态，这个函数可以反转义字符串
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $string 要处理的字符串
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    static public function stripSlashes($string) {
        if (get_magic_quotes_gpc()) {
            $string = stripslashes($string);
        }
        return $string;
    }

    /**
     +----------------------------------------------------------
     * 用于在textbox表单中显示html代码
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $string 要处理的字符串
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    static function hsc($string) {
        return preg_replace(array("/&amp;/i", "/&nbsp;/i"), array('&', '&amp;nbsp;'), htmlspecialchars($string, ENT_QUOTES));
    }

    /**
     +----------------------------------------------------------
     * 是hsc()方法的逆操作
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $text 要处理的字符串
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    static function undoHsc($text) {
        return preg_replace(array("/&gt;/i", "/&lt;/i", "/&quot;/i", "/&#039;/i", '/&amp;nbsp;/i'), array(">", "<", "\"", "'", "&nbsp;"), $text);
    }

    /**
     +----------------------------------------------------------
     * 输出安全的html，用于过滤危险代码
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $text 要处理的字符串
     * @param mixed $allowTags 允许的标签列表，如 table|td|th|td
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    static public function safeHtml($text, $allowTags = null) {
        $text =  trim($text);
        //完全过滤注释
        $text = preg_replace('/<!--?.*-->/','',$text);
        //完全过滤动态代码
        $text =  preg_replace('/<\?|\?'.'>/','',$text);
        //完全过滤js
        $text = preg_replace('/<script?.*\/script>/','',$text);

        $text =  str_replace('[','&#091;',$text);
        $text = str_replace(']','&#093;',$text);
        $text =  str_replace('|','&#124;',$text);
        //过滤换行符
        $text = preg_replace('/\r?\n/','',$text);
        //br
        $text =  preg_replace('/<br(\s\/)?'.'>/i','[br]',$text);
        $text = preg_replace('/(\[br\]\s*){10,}/i','[br]',$text);
        //过滤危险的属性，如：过滤on事件lang js
        while(preg_match('/(<[^><]+)(lang|on|action|background|codebase|dynsrc|lowsrc)[^><]+/i',$text,$mat)){
            $text=str_replace($mat[0],$mat[1],$text);
        }
        while(preg_match('/(<[^><]+)(window\.|javascript:|js:|about:|file:|document\.|vbs:|cookie)([^><]*)/i',$text,$mat)){
            $text=str_replace($mat[0],$mat[1].$mat[3],$text);
        }
        if( empty($allowTags) ) { $allowTags = self::$htmlTags['allow']; }
        //允许的HTML标签
        $text =  preg_replace('/<('.$allowTags.')( [^><\[\]]*)>/i','[\1\2]',$text);
        //过滤多余html
        if ( empty($banTag) ) { $banTag = self::$htmlTags['ban']; }
        $text =  preg_replace('/<\/?('.$banTag.')[^><]*>/i','',$text);
        //过滤合法的html标签
        while(preg_match('/<([a-z]+)[^><\[\]]*>[^><]*<\/\1>/i',$text,$mat)){
            $text=str_replace($mat[0],str_replace('>',']',str_replace('<','[',$mat[0])),$text);
        }
        //转换引号
        while(preg_match('/(\[[^\[\]]*=\s*)(\"|\')([^\2=\[\]]+)\2([^\[\]]*\])/i',$text,$mat)){
            $text=str_replace($mat[0],$mat[1].'|'.$mat[3].'|'.$mat[4],$text);
        }
        //空属性转换
        $text =  str_replace('\'\'','||',$text);
        $text = str_replace('""','||',$text);
        //过滤错误的单个引号
        while(preg_match('/\[[^\[\]]*(\"|\')[^\[\]]*\]/i',$text,$mat)){
            $text=str_replace($mat[0],str_replace($mat[1],'',$mat[0]),$text);
        }
        //转换其它所有不合法的 < >
        $text =  str_replace('<','&lt;',$text);
        $text = str_replace('>','&gt;',$text);
        $text = str_replace('"','&quot;',$text);
        //反转换
        $text =  str_replace('[','<',$text);
        $text =  str_replace(']','>',$text);
        $text =  str_replace('|','"',$text);
        //过滤多余空格
        $text =  str_replace('  ',' ',$text);
        return $text;
    }

    /**
     +----------------------------------------------------------
     * 删除html标签，得到纯文本。可以处理嵌套的标签
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $string 要处理的html
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    static public function deleteHtmlTags($string) {
        while(strstr($string, '>')) {
            $currentBeg = strpos($string, '<');
            $currentEnd = strpos($string, '>');
            $tmpStringBeg = @substr($string, 0, $currentBeg);
            $tmpStringEnd = @substr($string, $currentEnd + 1, strlen($string));
            $string = $tmpStringBeg.$tmpStringEnd;
        }
        return $string;
    }

    /**
     +----------------------------------------------------------
     * 处理文本中的换行
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $string 要处理的字符串
     * @param mixed $br 对换行的处理，
     *        false：去除换行；true：保留原样；string：替换成string
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    static public function nl2($string, $br = '<br />') {
        if ($br == false) {
            $string = preg_replace("/(\015\012)|(\015)|(\012)/", '', $string);
        } elseif ($br != true){
            $string = preg_replace("/(\015\012)|(\015)|(\012)/", $br, $string);
        }
        return $string;
    }
}