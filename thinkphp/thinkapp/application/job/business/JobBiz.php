<?php
namespace app\job\business;

use think\Config;

use ylcore\Agreement;
use ylcore\Biz;
use app\job\model\Job;
use app\location\business\LocationBiz;
use app\user\business\UserBiz;
use app\user\model\UserJobProcess;

class JobBiz extends Biz
{
    protected $domainObjectFields = [
    	'id',	
        'cash_back', 
        'condition_short', 
        'cover', 
        'is_vip',	
        'job_name', 
        'salary_ceil', 
        'salary_floor',
        'status',
    	'deliveries',
    	'welfare_tag',
        'base_time'
    ];
    protected $orders = ['id', 'salary_ceil', 'salary_floor', 'list_order', 'cash_back'];
	
    /**
     * 检索职位
     * @param array $condition 检索条件
     * @return array
     */
    public function search($condition = [], $page = 1, $pagesize = 10, $order = 'list_order', $by = 'desc') {
    	$return = [];
    	if ($order == 'location' && isset($condition['user'])) {
    		$return = $this->locationSearch($condition['user'], $condition, $page, $pagesize);
    	} else {
    		$return = $this->commonSearch($condition, $page, $pagesize, $order, $by);
    	}
        return $return;
    }
    
    /**
     * 职位普通检索方式
     */
    public function commonSearch($condition = [], $page = 1, $pagesize = 10, $order = 'list_order', $by = 'desc') {
    	$order_by = $this->selectOrderBy($order, 'list_order', $by);
    	$model = new Job();
    	$list = $model->alias('job')
    		->join('JobStatistics sta', 'job.id=sta.job_id')
    		->where($this->searchCondition($condition))
    		->order($order_by)
    		->page($page, $pagesize)
    		->select();
    	$return = $this->o2a($list);
    	return $return;
    }

    /**
     * 检索条件
     */
    private function searchCondition($condition) {
    	$where = $this->defaultCondition();
        if ($condition) {
        	//地区
        	if (isset($condition['region_id']) && $condition['region_id'] > 0) {
        		$where .= ' AND `region_id`=' . $condition['region_id'];
        	}
        	//关键字
        	if (isset($condition['keyword']) && $condition['keyword'] != '') {
        		$where .= ' AND `job_name` LIKE "%' . $condition['keyword'] . '%"';
        	}
        	//顶薪
        	if (isset($condition['salary_ceil']) && $condition['salary_ceil'] > 0) {
        		$where .= ' AND `salary_ceil`<=' . $condition['salary_ceil'];
        	}
        	//底薪
        	if (isset($condition['salary_floor']) && $condition['salary_floor'] > 0) {
        		$where .= ' AND `salary_floor`>=' . $condition['salary_floor'];
        	}
        	//标签
        	if (isset($condition['tag']) && $condition['tag'] != '') {
        		$where .= " AND MATCH (`welfare_tag`) AGAINST ('" . $condition['tag'] . "' IN BOOLEAN MODE)";
        	}
        	//类型
        	if (isset($condition['type']) && $condition['type']) {
        		$where .= ' AND `type`=' . $condition['type'];
        	}
        	//认证企业
        	if (isset($condition['is_vip']) && $condition['is_vip'] == 1) {
        		$where .= ' AND `is_vip`=1';
        	}
        	//返费企业
        	if (isset($condition['is_subsidy']) && $condition['is_subsidy'] == 1) {
        		$where .= ' AND (`cash_back`!="" AND `cash_back` IS NOT NULL)';
        	}
        }
        return $where;
    }

    /**
     * 默认条件
     */
    public function defaultCondition() {
        return '`status` > 0';
    }
    
    /**
     * 推荐的职位
     * @param integer $id 推荐位编号
     * @param integer $size 职位个数
     */
    public function recommend($id, $size) {
    	$model = new Job();
    	$list = $model->alias('job')
    		->join('JobRecommendedData jrd', "jrd.job_id = job.id")
    		->join('JobStatistics js', "js.job_id = job.id")
    		->where($this->defaultCondition())
    		->where('re_id', $id)
    		->field('job.*, js.deliveries')
    		->order('jrd.list_order desc')
    		->limit($size)
    		->select();
    	$return = $this->o2a($list);
    	return $return;
    }
    
    /**
     * @override
     * 格式化视图字段
     * @param array $item
     */
    public function formatViewField($item) {
        isset($item['welfare_tag']) && ($item['welfare_tag'] != '')
            ? $item['welfare_tag'] = $this->formatTag($item['welfare_tag'])
            : false;
        isset($item['cash_back']) && ($item['cash_back'] == null) ? $item['cash_back'] = '' : false;
        isset($item['base_time']) ? $item['base_time'] = date("Y-m-d", $item['base_time']) : false;
    	return $item;
    }
    
    /**
     * 格式化标签
     * @param unknown $value
     */
    private function formatTag($value) {
    	return explode(',', $value);
    }
    
    /**
     * 获取职位详情
     * @param integer $id
     */
    public function get($id) {
    	$model = new Job();
        $agreement = new Agreement();
    	$job_obj = $model->get($id);
    	if ($this->isPublished($job_obj)) {
    		$job_obj['welfare_tag'] = $this->formatTag($job_obj['welfare_tag']);
    		$job_obj['job_tag'] = $this->formatTag($job_obj['job_tag']);
    		$job_obj['recommend_tag'] = $this->formatTag($job_obj['recommend_tag']);
    		$job_obj['publish_time'] = date('Y-m-d', $job_obj['publish_time']);
    		if ($job_obj->detail) {
    			$job_obj['detail']['pictures'] = unserialize($job_obj['detail']['pictures']);
                $image = [];
                foreach ($job_obj['detail']['pictures'] as $key => $value) {
                    $image[$key]['image'] = $agreement->httpAgreement($value['image']);
                    $image[$key]['active'] = $value['active'];
                }
                $job_obj['detail']['pictures'] = $image;
    		}
    		if ($job_obj->enterprise) {
    			$job_obj['enterprise']['tag'] = $this->formatTag($job_obj['enterprise']['tag']);
    		}
    		$job_obj->statistics;
            $job_obj['cover'] = $this->cover( $job_obj['cover']);
    	} else {
    		$job_obj = [];
    	}
    	return $job_obj;
    }
    
    /**
     * 职位是否已发布
     */
    public function isPublished($job) {
    	return $job['status'] > 0;
    }
    
    /**
     * 获取职位申请人
     */
    public function getApplicants($job_id, $page, $pagesize) {
    	$model = new UserJobProcess();
    	$result = $model->alias('jp')
    		->join('UserData ud', 'jp.user_id=ud.uid')
    		->field('ud.uid, ud.gender, ud.birth, ud.nickname')
    		->where('job_id', $job_id)
    		->order('creat_time desc')
    		->page($page, $pagesize)
    		->select();
    	/*转换用户领域模型*/
    	$user_biz = new UserBiz();
    	$return = $user_biz->transfer($this->o2a($result));
    	return $return;
    }
    
    /**
     * 获取相似的职位
     * @param int $job_id
     * @param int $page
     * @param int $pagesize
     */
    public function getSimilar($job_id, $page, $pagesize) {
    	$model = new Job();
    	$job = $model->get($job_id);//职位详情
    	/*获取同地区的职位*/
    	$list = $model->where("`id`!=$job_id")
    		->where('region_id', $job->region_id)
    		->where($this->defaultCondition())
    		->order("list_order desc")
    		->page($page, $pagesize)
    		->select();
    	$return = $this->o2a($list);
    	/*补足剩余职位*/
    	if (count($list) < $pagesize) {
    		$str_id = $job_id;
    		foreach ($list as $item) {
    			$str_id .= ',' . $item['id'];
    		}
    		$sub_list = $model->where("`id` NOT IN (" . $str_id . ")")
    			->where($this->hiringCondition())
    			->order("list_order desc")
    			->limit($pagesize - count($list))
    			->select();
    		$return = array_merge($return, $this->o2a($sub_list));
    	}
    	return $return;
    }
    
    /**
     * 默认条件
     */
    private function hiringCondition() {
    	return '`status` = 1';
    }
    
    /**
     * 职位标签
     */
    public function tags($type, $size) {
    	$return = [];
    	Config::load(APP_PATH . 'job/config_jobtag.php');
    	$arr_job_tag = Config::get('job_tag');
    	if (isset($arr_job_tag[$type])) {
    		$return = array_slice($arr_job_tag[$type], 0, $size);
    	}
    	return $return;
    }
    
    /**
     * 获取职位的所有地图坐标
     */
    public function locationSearch($user_id, $condition = [], $page = 1, $pagesize = 10) {
    	$model = new Job();
    	//获取所有职位
    	$list = $model->alias('m')
    		->join('JobData s', 'm.id=s.job_id')
    		->join('JobStatistics sta', 'm.id=sta.job_id')
    		->where($this->searchCondition($condition))
    		->select();
    	/*按照位置排序*/
    	if ($list) {
    		$tmp_list = [];
    		$arr_order = [];//距离排序数组
    		/*获取用户位置*/
    		$location_biz = new LocationBiz();
    		$user = $location_biz->myLocation($user_id);
    		if ($user) {
    			foreach ($list as $item) {
    				if (! $item['address_mark']) continue;
    				list($longitude, $latitude) = explode(',', $item['address_mark']);
    				if ($longitude <= 0 || $latitude <= 0) continue;
    				$distance = $location_biz->getDistance($latitude, $longitude, $user['latitude'], $user['longitude']);//计算距离
    				$tmp_list[] = $item;
    				$arr_order[] = $distance;
    			}
    			array_multisort($arr_order, $tmp_list);
    			$list = array_slice($tmp_list, ($page - 1) * $pagesize, $pagesize);
    		}
    	}
    	$return = $this->o2a($list);
    	return $return;
    }
    
    /**
     * 城市
     */
    public function cities() {
    	$return = [];
    	Config::load(APP_PATH . 'job/config_city.php');
    	$return = Config::get('cities');
    	return $return;
    }
    
    /**
     * 类别
     */
    public function categories() {
    	$return = [];
    	Config::load(APP_PATH . 'job/config_jobcategory.php');
    	$return = Config::get('job_categories');
    	return $return;
    }

    /**
     *封面格式化
     * @param $cover
     * @return $return
     */
    public function cover($cover) {
        $agreement = new Agreement();
        $return = $agreement->httpAgreement($cover);
        return $return;
    }
}