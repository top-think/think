<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Pakey <Pakey@qq.com> <http://pakey.net>
// +----------------------------------------------------------------------
namespace think;

class Page
{
    // 列表每页显示行数
    public $listRows;
    // 分页跳转时要带的参数
    public $parameter;
    // 总行数
    public $totalRows;
    // 分页总页面数
    public $totalPages;
    // 分页栏每页显示的页数
    public $rollPage = 11;
    // 分页参数名
    private $p       = 'p';
    // 当前链接URL
    private $url     = '';
    // 当前页码
    private $nowPage = 1;
    // 分页显示定制
    private $config = array(
        'header' => '<span class="rows">共 %TOTAL_ROW% 条记录</span>',
        'prev'   => '<<',
        'next'   => '>>',
        'first'  => '1...',
        'last'   => '...%TOTAL_PAGE%',
        'theme'  => '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%',
    );
    /**
     * 架构函数
     * @param string $totalRows  总的记录数
     * @param mixed $listRows  每页显示记录数
     * @param array $parameter  分页跳转的参数
     */
    public function __construct($totalRows, $listRows = 20, $parameter = array())
    {
        // 设置分页参数名称
        Config::has('var_page') && $this->p = Config::has('var_page');
        /* 基础设置 */
        // 设置总记录数
        $this->totalRows = $totalRows;
        // 设置每页显示行数
        $this->listRows  = $listRows;
        // 设置url参数
        $this->parameter = empty($parameter) ? $_GET : $parameter;
        // 设置当前页数
        $this->nowPage   = max(1,Input::data($this->parameter,$this->p,1));
    }

    /**
     * 定制分页链接设置
     * @param string $name  设置名称
     * @param string $value 设置值
     */
    public function setConfig($name, $value)
    {
        if (isset($this->config[$name])) {
            $this->config[$name] = $value;
        }
    }

    /**
     * 设置链接URL
     * @param  string $url 链接
     * @return string
     */
    public function setUrl($url)
    {
        $this->url=$url;
    }

    /**
     * 设置分页栏每页显示的页数
     * @param  string $num 页数
     * @return string
     */
    public function setRoll($num)
    {
        $this->rollPage=$num;
    }

    /**
     * 生成链接URL
     * @param  integer $page 页码
     * @return string
     */
    private function url($page)
    {
        return str_replace(urlencode('[PAGE]'), $page, $this->url);
    }

    public function data()
    {
        if($this->totalRows==0){
            return [];
        }
        $data=[
            'totalRows'=>$this->totalRows,
            'totalPages'=>0,
            'nowPage'=>$this->nowPage,
            'prevPage'=>0,
            'nextPage'=>0,
            'firstPage'=>0,
            'lastPage'=>0,
            'page'=>[]
        ];
        $data['totalPages']=ceil($this->totalRows / $this->listRows);
        if($data['nowPage']>$data['totalPages']) $data['nowPage']=$data['totalPages'];
        if($data['totalPages']==1){
            $data['page']=[1];
            return $data;
        }
        $data['prevPage']=$data['nowPage']-1;
        $data['nextPage']=$data['nowPage']<$data['totalPages']?$data['nowPage']+1:0;
        $now_cool_page      = ceil($this->rollPage / 2);
        if($data['totalPages'] > $this->rollPage){
            $data['firstPage']=($data['nowPage'] - $now_cool_page) >= 1?1:0;
            $data['lastPage']=(($data['nowPage'] + $now_cool_page) < $data['totalPages'])?$data['totalPages']:0;
        }
        for ($i = 1; $i <= $this->rollPage; $i++) {
            if (($data['nowPage'] - $now_cool_page) <= 0) {
                $page = $i;
            } elseif (($data['nowPage'] + $now_cool_page ) >= $data['totalPages']) {
                $page = $data['totalPages'] - $this->rollPage + $i;
            } else {
                $page = $data['nowPage'] - $now_cool_page + $i;
            }
            if ($page > 0 && $page <= $data['totalPages']) {
                $data['page'][]=$page;
            }
        }
        return $data;
    }

    /**
     * 组装分页链接
     * @return string
     */
    public function show()
    {
        if(empty($this->url)){
            $this->parameter[$this->p] = '[PAGE]';
            $this->url                 = U(ACTION_NAME, $this->parameter);
        }
        $data=$this->data();
        $up_page = $data['prevPage']  ? '<a class="prev" href="' . $this->url($data['prevPage']) . '">' . $this->config['prev'] . '</a>' : '';
        $down_page = $data['nextPage']  ? '<a class="next" href="' . $this->url($data['nextPage']) . '">' . $this->config['next'] . '</a>' : '';
        $the_first = $data['firstPage']  ? '<a class="first" href="' . $this->url($data['firstPage']) . '">' . $this->config['last'] . '</a>' : '';
        $the_end = $data['lastPage']  ? '<a class="end" href="' . $this->url($data['lastPage']) . '">' . $this->config['prev'] . '</a>' : '';

        $link_page='';
        foreach($data['page'] as $page){
            if($data['page']==$data['nowPage']){
                $link_page .= '<span class="current">' . $page . '</span>';
            }else{
                $link_page .= '<a class="num" href="' . $this->url($page) . '">' . $page . '</a>';
            }
        }

        //替换分页内容
        $page_str = str_replace(
            ['%HEADER%', '%NOW_PAGE%', '%UP_PAGE%', '%DOWN_PAGE%', '%FIRST%', '%LINK_PAGE%', '%END%', '%TOTAL_ROW%', '%TOTAL_PAGE%'],
            [$this->config['header'], $data['nowPage'], $up_page, $down_page, $the_first, $link_page, $the_end, $data['totalRows'], $data['totalPages']],
            $this->config['theme']
        );
        return "<div>{$page_str}</div>";
    }
}