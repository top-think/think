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

namespace Think\Model;
use Traits\Think\Model\Extend,Traits\Think\Model\Query;
T('Think/Model/Extend');
T('Think/Model/Query');
class ExtendModel extends \Think\Model {
	use Extend,Query;
}