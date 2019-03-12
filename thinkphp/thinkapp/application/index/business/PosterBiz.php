<?php
namespace app\index\business;

use think\Config;

use ylcore\Agreement;
use ylcore\Biz;

use app\user\service\Uccenter;

class PosterBiz extends Biz
{
    protected $domainObjectFields = ['content', 'title'];
	
    /**
     * 根据广告位获取所有广告
     * @param integer $space_id 广告位。2=>APP职位模块banner
     * @param integer $limit 广告个数
     * @return array
     */
    public function getAll($space_id = 2, $limit = 5) {
        $model = model('poster');
        $list = $model->where('space_id', $space_id)->where('disabled', 0)->order('listorder')->limit($limit)->select();
        $agreement = new Agreement();
        foreach ($list as $key => $value) {
            $list[$key]['content'] = $agreement->httpAgreement($value['content']);

        }
        $return = $this->o2a($list);
        return $return;
    }
    
    /**
     * 上传图片
     * @param $file string 文件
     */
    public function upload($file, $is_avatar = false) {
    	$flag = false;
	    // 移动到框架应用根目录/public/uploads/ 目录下
	    if($file){
	        $info = $file->move(Config::get('upload.path'));
	        if($info){
	        	$file = $info->getSaveName();
	            $flag = $this->getUrl($file);
	            if ($is_avatar) {
	            	//保存为ucenter头像
	            	$uc_service = new Uccenter();
	            	$uc_service->saveAvatar($is_avatar, Config::get('upload.path') . '/' . $file);
	            }
	        }
	    }
	    return $flag;
    }
    
    /**
     * 返回图片url
     */
    public function getUrl($file){
    	return 'http://' . Config::get('upload.domain') . "/" . str_replace('\\', '/', $file);
    }
}