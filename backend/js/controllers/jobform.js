app.controller('JobFormController', function($scope, $state, $stateParams, $modal, Errors, MessageWindow, Job, Cities, JobCategories, JobStatus, JobType, Enterprise) {
    var vm = this;
    /*初始化*/
    vm.categories = JobCategories;
    vm.error = '';
    vm.edit = false;
    vm.enterprise = {"id": "", "name": ""};//企业详情
    vm.enterpriseList = [];//企业列表
    vm.job = {
        "cash_back": "",
        "cat_id": "",
        "condition_short": "",
        "is_vip": true,
        "job_name": "",
        "list_order": "",
        "region": {"id": 0, "name": ""},
        "salary_floor": "",
        "salary_ceil": "",
        "status": 1,
        "type": "",
        "welfare": ""
    };
    vm.job_detail = {"content": "", "view_time": "", "salary_detail": "", "address_short": "", "address_mark": {'lng': 120.96914, 'lat': 31.361753, 'level': 11}};//职位详情
    vm.operate = "新增";
    vm.regions = Cities;
    vm.status_list = JobStatus;
    vm.submitting = false;
    vm.types = JobType;
    $scope.config = {
        //focus时自动清空初始化时的内容
        autoClearinitialContent: true,
        //关闭字数统计
        wordCount: false,
        //关闭elementPath
        elementPathEnabled: false,
        initialFrameHeight: 220
    };
    /**
     * 新增职位
     * 添加基本信息
     */
    vm.submitInfo = function(){
        vm.submitting = true;
        /*参数格式化*/
        vm.formatParam();
        Job.save({
            cash_back: vm.job.cash_back,
            cat_id: vm.job.cat_id,
            condition_short: vm.job.condition_short,
            enterprise_id: vm.enterprise.id,
            enterprise_name: vm.enterprise.name,
            is_vip: vm.job.is_vip,
            job_name: vm.job.job_name,
            list_order: vm.job.list_order,
            region_id: vm.job.region.id,
            region_name: vm.job.region.name,
            salary_floor: vm.job.salary_floor,
            salary_ceil: vm.job.salary_ceil,
            status: vm.job.status,
            type: vm.job.type,
            welfare: vm.job.welfare,
        },function (response) {
            vm.open(Errors.common_success);
            $state.go('app.job.show', {id: response.id});//跳转到详情页
        }, function () {
            vm.open(Errors.params);
            vm.error = '保存失败';
            vm.submitting = false;
        });
    }

    /*获取职位内容，更新职位信息*/
    if ($stateParams.id != null && $stateParams.id != ''){
        Job.get({id: $stateParams.id}, function (response) {
            vm.job = response;
            if (vm.job.is_vip == 1) vm.job.is_vip = true;
            else vm.job.is_vip = false;
            vm.job.region = {"id": parseInt(response.region_id), "name": response.region_txt};
            vm.job_detail = vm.job.detail;
            if (response.enterprise != undefined && response.enterprise != null) {
                vm.enterprise.id = response.enterprise.id;
                vm.enterprise.name = response.enterprise.enterprise_name;
            }
            if (response.detail.address_mark[0] != undefined && response.detail.address_mark[0] != 'undefined') vm.job_detail.address_mark.lng = response.detail.address_mark[0];
            else vm.job_detail.address_mark.lng = 120.96914;
            if (response.detail.address_mark[1] != undefined && response.detail.address_mark[1] != 'undefined') vm.job_detail.address_mark.lat = response.detail.address_mark[1];
            else vm.job_detail.address_mark.lat = 31.361753;
        });
        vm.edit = true;
        vm.operate = "编辑";
        vm.submitInfo = function () {
            vm.submitting = true;//提交中
            /*参数格式化*/
            vm.formatParam();
            var str_mark = vm.job_detail.address_mark.lng + ',' + vm.job_detail.address_mark.lat;
            //更新信息
            Job.update({id: $stateParams.id}, {
                address_mark: str_mark,
                address_short: vm.job_detail.address_short,
                cash_back: vm.job.cash_back,
                cat_id: vm.job.cat_id,
                condition_short: vm.job.condition_short,
                content: vm.job_detail.content,
                enterprise_id: vm.enterprise.id,
                is_vip: vm.job.is_vip,
                job_name: vm.job.job_name,
                list_order: vm.job.list_order,
                region_id: vm.job.region.id,
                region_name: vm.job.region.name,
                salary_ceil: vm.job.salary_ceil,
                salary_detail: vm.job_detail.salary_detail,
                salary_floor: vm.job.salary_floor,
                status: vm.job.status,
                type: vm.job.type,
                view_time: vm.job_detail.view_time,
                welfare: vm.job.welfare,
            },function(){
                vm.open('success');
                vm.error = '已更新';
                vm.submitting = false;
            },function(){
                vm.open('failure');
                vm.error = '更新失败';
                vm.submitting = false;
            });
        }
    }

    /**
     * 消息提醒
     * @param 消息内容
     */
    vm.open = function (msg) {
        MessageWindow.open(msg);
    };

    vm.formatParam = function () {
        angular.forEach(vm.regions, function (region) {
            if (region.id == vm.job.region.id) vm.job.region.name = region.name;
        });
    }

    /**
     * 选择企业
     */
    vm.selectEnterprise = function () {
        angular.forEach(vm.enterpriseList, function (enterpries) {
            if (enterpries.id == vm.enterprise.id) vm.enterprise.name = enterpries.enterprise_name;
        })
    };
    vm.init = function () {
        Enterprise.list().then(function (resposne) {
            vm.enterpriseList = resposne.data;
        });
    };
    vm.init();
});
