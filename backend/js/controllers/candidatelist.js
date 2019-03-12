'use strict';

/* Controllers */
// candidate list controller
app.controller('CandidateListController', function(
    $http,
    $modal,
    $stateParams,
    $scope,
    $localStorage,
    $q,
    $filter,
    ConfirmWindow,
    MessageWindow,
    AdviserOrganizations,
    OperateButtons,
    CandidateRemain,
    CandidateTag,
    API,
    JobChoice,
    InputWindow,
    Errors,
    CandidateTask,
    Contact,
    Customer,
    CandidateTop,
    Adviser,
    CallinCustomer
) {
    var vm = this,
        params = {},
        stype = 1,
        arr_cpid = [];//选中人选
    /*显示*/
    vm.type = '所有';
    vm.navRadio = 'all';
    vm.candidates = {};
    vm.header = ['人选姓名', '手机号', '性别', '分配时间', '联系时间', '最后联系内容', '顾问', '状态'];
    vm.fields = ['real_name', 'phone', 'gender', 'show_time', 'latest_contact_time', 'latest_contact_content', 'employee_name', 'status'];
    vm.callin = 0;
    /*操作*/
    vm.checked = [];
    vm.check_all = false;//全选
    vm.operate = 0;//操作标识
    vm.operates = [];//1=>意向；2=>报名；3=>接站；4=>入职；5=>离职
    vm.buttons = {'award': false, 'remain': false, 'top': false};//按钮显示
    vm.navi = {};//按钮操作权限
    /*搜索*/
    vm.searchText = '';
    vm.detailSearchParam = {'org':'', 'employee':''};//详细搜索参数
    vm.searchactive = 1;
    vm.searchTypes = [{'id':1, 'text':'人选'},{'id':2, 'text':'标签'}];//1=>人选；2=>标签；3=>顾问；4=>部门
    vm.onlyMy = true;//是否只搜索自己的人选
    vm.searchCon = 0;//入职企业
    vm.opens = [false, false, false, false, false, false];//日期开关
    /*排序*/
    vm.showSort = true;
    vm.sortactive = 'create_time';//默认排序
    vm.sort = [{'sorttime':'create_time', 'text':'分配时间'},{'sorttime':'latest_contact_time', 'text':'最后联系时间'}];
    /*分页*/
    vm.pagingOptions = {
        count: 0,//总的记录数
        pageSize: 20,//每页记录数
        pageShowLength: [20,30,50,100,1000],//每页记录可选数
        currentPage: 1,//当前页
        pages: 1,//总页数
        pageSizes: [1],//显示页数
        pageMax: 8//限制分页按钮显示的数量大小
    };
    vm.inputPage = 1;

    /**
     * 加载按钮的权限
     */
    vm.loadNavi = function(){
        return OperateButtons.success(function(response){
            vm.navi = response;
        });
    };

    /**
     * 加载职位列表
     */
    vm.loadJob = function(){
        JobChoice.all().then(function (response) {
            vm.job_list = [];
            angular.forEach(response.data, function (i) {
                vm.job_list.push(i);
            })
        });
    };

    /**
     * 显示设置
     */
    vm.loadHead = function() {
        if ($stateParams.type == 'signup') {
            vm.header = ['人选姓名', '手机号', '性别', '报名时间', '企业名称', '顾问'];//列表头部
            vm.fields = ['real_name', 'phone', 'gender', 'show_time', 'job_name', 'employee_name'];//列表字段
            vm.type = '已报名';//人选类型
            vm.showSort = false;//搜索结果
            vm.navRadio = 'signup';
        } else if ($stateParams.type == 'outduty'){
            vm.header = ['人选姓名', '手机号', '性别', '身份证号', '企业名称', '离职时间', '顾问'];
            vm.fields = ['real_name', 'phone', 'gender', 'idcard', 'job_name', 'show_time', 'employee_name'];
            vm.type = '已离职';
            vm.showSort = false;
            vm.navRadio = 'outduty';
        } else if ($stateParams.type == 'onduty'){
            vm.header = ['人选姓名', '手机号', '性别', '身份证号', '企业名称', '在职天数', '顾问'];
            vm.fields = ['real_name', 'phone', 'gender', 'idcard', 'job_name', 'show_time', 'employee_name'];
            vm.type = '在职';
            vm.showSort = false;
            vm.navRadio = 'onduty';
        } else if ($stateParams.type == 'meet'){
            vm.header = ['人选姓名', '手机号', '性别', '身份证号', '企业名称', '报名时间', '顾问'];
            vm.fields = ['real_name', 'phone', 'gender', 'idcard', 'job_name', 'show_time', 'employee_name'];
            vm.type = '已接站';
            vm.showSort = false;
            vm.navRadio = 'meet';
        }else if($stateParams.type == 'intention'){
            vm.type = '有意向';
            vm.showSort = false;
            vm.navRadio = 'intention';
        }else if($stateParams.type == 'other'){
            vm.type = '其他';
            vm.showSort = false;
            vm.navRadio = 'other';
        }
    }

    /**
     * 分页
     */
    vm.setPagingData = function(){
        vm.pagingOptions.pages = Math.ceil(vm.pagingOptions.count/vm.pagingOptions.pageSize);//总页数
        vm.cancelAll();//取消全选
    };

    /**
     * 设置显示数量
     */
    vm.selectLength = function(){
        vm.getPagedDataAsync();
    }

    /**
     * 切换跳转
     */
    vm.switchPage = function () {
        vm.pagingOptions.currentPage = Math.min(Math.max(1, parseInt(vm.inputPage)), vm.pagingOptions.pages);
        vm.getPagedDataAsync();
    };

    /**
     * 请求并加载数据
     */
    vm.getPagedDataAsync = function () {
        vm.showLoading = true;//显示加载
        var url = API.candidateData($stateParams.type);//请求地址
        /*参数格式化*/
        var searchSign = vm.searchSign!=''?$filter('date')(vm.searchSign, 'yyyy-MM-dd'):'',
            searchSigned = vm.searchSigned!=''?$filter('date')(vm.searchSigned, 'yyyy-MM-dd'):'',
            searchDis = vm.searchDis!=''?$filter('date')(vm.searchDis, 'yyyy-MM-dd'):'',
            searchDised = vm.searchDised!=''?$filter('date')(vm.searchDised, 'yyyy-MM-dd'):'',
            searchContact = vm.searchContact!=''?$filter('date')(vm.searchContact, 'yyyy-MM-dd'):'',
            searchContacted = vm.searchContacted!=''?$filter('date')(vm.searchContacted, 'yyyy-MM-dd'):'',
            searchRetain = vm.searchRetain?1:0;
        params = {
            page: vm.pagingOptions.currentPage,
            pagesize: vm.pagingOptions.pageSize,
            search: vm.searchText,
            stype: stype,
            signup_time_s: searchSign,
            signup_time_e: searchSigned,
            distribute_time_s: searchDis,
            distribute_time_e: searchDised,
            contact_time_s: searchContact,
            contact_time_e: searchContacted,
            job: vm.searchCon.id,
            is_remain: searchRetain,
            employee: vm.detailSearchParam.employee,
            org: vm.detailSearchParam.org,
            order: vm.sortactive,
            onlyMy: vm.onlyMy
        };
        var promise = $http({method: 'get', url: url, params: params}).success(function(response){
            if (response.count == 0) {
                /*结果为空*/
                if (stype == 1) {
                    /*按照人选搜索提示*/
                    vm.tipMess = false;
                    vm.tipMessage = true;
                } else {
                    /*其他搜索提示*/
                    vm.tipMessage = false;
                    vm.tipMess = true;
                }
            } else {
                vm.tipMessage = false;
                vm.tipMess = false;
            }
            vm.candidates = response.list;
            vm.pagingOptions.count = response.count;
            vm.setPagingData();
        });
        promise.then(function(){
            vm.showLoading = false;
        },function(){
            vm.showLoading = false;
        });
    };

    /**
     * 日期显示
     */
    $scope.open = function($event,a) {
        vm.opens[a] = true;
        $event.preventDefault();
        $event.stopPropagation();
    };

    /*切换搜索方式*/
    vm.switchSearch = function() {
        var searchSetting = {'searchScope': vm.onlyMy};
        if (angular.isDefined($localStorage.searchSetting)) {
            searchSetting = $localStorage.searchSetting;
            searchSetting.searchScope = vm.onlyMy;
        }
        $localStorage.searchSetting = searchSetting;//全局搜索设置
    };

    /**
     * 显示高级搜索
     */
    vm.btnSearchDetail = function (){
        if (vm.job_list == null) {
            vm.loadJob();//加载职位
            /*高级搜索*/
            if (vm.navi.search_btn.adviser || vm.navi.search_btn.organization){
                /*获取顾问信息*/
                AdviserOrganizations.then(function (resp) {
                    if (vm.navi.search_btn.adviser) {
                        vm.employees_options = resp.data.employees;
                    }
                    if (vm.navi.search_btn.organization) {
                        vm.orgs_options = resp.data.orgs;
                    }
                });
            }
        }
    };

    /**
     * 高级搜索
     */
    vm.searchDetail = function(){
        stype = 99;
        vm.getPagedDataAsync();
    };

    /**
     * 快速搜索
     */
    vm.search = function() {
        stype = vm.searchactive;
        vm.getPagedDataAsync();
    };

    /**
     * 操作设置
     */
    vm.loadOperates = function() {
        if ($stateParams.type=='intention'){
            vm.operates.push({'id':2, 'text':'报名'});
            vm.operates.push({'id':98, 'text':'回退'});
            vm.operates.push({'id':99, 'text':'丢弃'});
            vm.buttons.remain = true;//保留
            vm.buttons.top = true;//置顶
        } else if ($stateParams.type=='signup'){
            vm.operates.push({'id':3, 'text':'接站'});
            vm.operates.push({'id':7, 'text':'重新报名'});
            vm.operates.push({'id':98, 'text':'回退'});
            vm.operates.push({'id':99, 'text':'丢弃'});
        } else if ($stateParams.type=='onduty'){
            vm.operates.push({'id':5, 'text':'离职'});
            vm.buttons.award = true;
        } else if ($stateParams.type=='outduty'){
            vm.operates.push({'id':6, 'text':'再次报名'});
            vm.operates.push({'id':99, 'text':'丢弃'});
        } else if ($stateParams.type=='meet'){
            vm.operates.push({'id':4, 'text':'入职'});
            vm.operates.push({'id':7, 'text':'重新报名'});
            vm.operates.push({'id':98, 'text':'回退'});
            vm.operates.push({'id':99, 'text':'丢弃'});
        } else if ($stateParams.type=='other'){
            vm.operates.push({'id':1, 'text':'意向'});
            vm.operates.push({'id':99, 'text':'丢弃'});
            vm.buttons.remain = true;
            vm.buttons.top = true;//置顶
        } else {
            if (vm.navi.candidate_btn.move) vm.operates.push({'id':97, 'text':'划转'});
            if (vm.navi.search_btn.adviser){
                vm.searchTypes.push({'id':3, 'text':'顾问'});
            }
            if (vm.navi.search_btn.organization){
                vm.searchTypes.push({'id':4, 'text':'部门'});
            }
            vm.operates.push({'id':99, 'text':'丢弃'});
            vm.buttons.top = true;//置顶
        }
    }

    /**
     * 操作
     */
    vm.doOperate = function() {
        var arr_id = [];
        angular.forEach(vm.candidates, function(value, key){
            if (vm.checked[key]) {
                arr_id.push(value.id);
                arr_cpid.push(value.cp_id);
            }
        });
        if (arr_id.length==0) {
            vm.showMessage('请选择人选');
            return;
        }
        var url = false;
        if (vm.operate == 1) {
            /*意向*/
            vm.submitOperate(arr_id);
        } else if (vm.operate == 2 || vm.operate == 6 || vm.operate == 7) {
            /*报名*/
            JobChoice.open(vm.operate, arr_id);//选择职位
        } else if (vm.operate == 3) {
            /*接站*/
            if (arr_id.length!=1) {
                vm.showMessage('只能选择一人');
            } else {
                InputWindow.open(vm.operate, arr_id);//输入身份证号码
            }
        } else if (vm.operate == 4) {
            /*入职*/
            if (arr_id.length!=1) {
                vm.showMessage('只能选择一人');
            } else {
                InputWindow.open(vm.operate, arr_id);//输入补贴等信息
            }
        } else if (vm.operate == 5) {
            /*离职*/
            ConfirmWindow.open("确定此人选已离职吗").then(function(){
                vm.submitOperate(arr_id);
            });
        } else if (vm.operate == 97) {
            /*划转*/
            Adviser.open(arr_id, vm.operate).then(function (response) {
                if (response) {
                    vm.getPagedDataAsync();
                    MessageWindow.open(Errors.move_success);
                } else {
                    MessageWindow.open(Errors.move_error);
                }
            });//选择顾问
        } else if (vm.operate == 98) {
            /*回退*/
            InputWindow.open(vm.operate, arr_id);//选择回退状态
        } else if (vm.operate == 99) {
            /*丢弃*/
            ConfirmWindow.open("确定要丢弃吗").then(function(){
                vm.submitOperate(arr_id);
            });
        }
    }

    /**
     * 提交操作
     * @param arr_id array 选中记录
     */
    vm.submitOperate = function(arr_id){
        var url = API.candidateOperate(vm.operate);
        if (url != '') {
            $http({method: 'post', url: url, data: {'id/a': arr_id}}).success(function(req){
                if (req) {
                    vm.getPagedDataAsync();
                    if (vm.operate == 1) {
                        /*意向客户提示信息*/
                        $modal.open({
                            templateUrl: 'tpl/modal_intention.html',
                            controller: 'ModalIntentionController as vm',
                            resolve: {
                                msg: function () {
                                    return Errors.common_success;
                                },
                                selectCustomerId: function(){
                                    return arr_cpid[0];
                                }
                            }
                        });
                    } else {
                        vm.showMessage(Errors.common_success);
                    }
                } else {
                    vm.showMessage(Errors.common_error);
                }
            }).error(function(req){
                  vm.showMessage(Errors.sys_error);
            });
        }
    }

    /**
     * 单选
     */
    vm.selectCandidates = function (){
        var checked_length = 0;
        angular.forEach(vm.checked, function(value, key){
            if (value) checked_length += 1;
        });
        if (checked_length==vm.candidates.length) {
            vm.select_all = true;
        } else {
            vm.select_all = false;
        }
    }

    /**
     * 全选
     */
    vm.selectAll = function (){
        angular.forEach(vm.candidates, function (value, key) {
            vm.checked[key] = vm.select_all;//全选
        })
    }

    /**
     * 取消全选
     */
    vm.cancelAll = function () {
        vm.select_all = false;
        vm.selectAll();
    }

    /**
     * 提示信息
     */
    vm.showMessage = function (msg) {
        MessageWindow.open(msg);
    };

    /**
     * 创建拨打计划
     */
    vm.newTask = function (cutomer) {
        CandidateTask.open(10, cutomer);
    };

    /**
     * 保留人选
     */
    vm.remainCandidate = function (candidate){
        CandidateRemain.remain(candidate).then(function (data) {
            if (data) vm.getPagedDataAsync();
        });
    };

    /**
     * 取消保留人选
     */
    vm.cancelRemain = function (candidate) {
        CandidateRemain.cancel(candidate).then(function (data) {
            if (data) vm.getPagedDataAsync();
        });
    };

    /**
     * 设置标签
     */
    vm.setTag = function (candidate) {
        CandidateTag.open(candidate, 0);
    };

    /**
     * 创建联系记录
     */
    vm.contactRecord = function (candidate) {
        Contact.open(candidate);
    };

    /**
     * 客户预览
     */
    vm.customerDetail = function (id) {
        Customer.open(id);
    };

    /**
     * 置顶人选
     */
    vm.topCandidate = function (candidate){
        CandidateTop.top(candidate).then(function (data) {
            if (data) vm.getPagedDataAsync();
        });
    };

    /**
     * 取消置顶人选
     */
    vm.cancelTop = function (candidate) {
        CandidateTop.cancel(candidate).then(function (data) {
            if (data) vm.getPagedDataAsync();
        });
    };

    /**
     * 排序
     */
    vm.sortsearch = function () {
        vm.getPagedDataAsync();
    };

    /**
     * 显示置顶
     */
    vm.showTop = function (candidate) {
        return vm.buttons.top && candidate.is_top==0;
    };

    /**
     * 显示取消置顶
     */
    vm.showCancelTop = function (candidate) {
        return vm.buttons.top && candidate.is_top==1;
    };

    /**
     * 显示更新返费
     */
    vm.showAwardForm = function () {
        return vm.buttons.award && vm.navi.candidate_btn.award;
    };

    /**
     * 显示保留
     */
    vm.showRemain = function (candidate) {
        return vm.buttons.remain && candidate.is_remain==0 && vm.navi.candidate_btn.remain;
    };

    /**
     * 显示取消保留
     */
    vm.showCandelRemain = function (candidate) {
        return vm.buttons.remain && candidate.is_remain==1 && vm.navi.candidate_btn.remain;
    };

    /**
     * 加载未确认客户数目
     */
    vm.loadCallinCount = function () {
        return CallinCustomer.unSureCount().then(function (response) {
            vm.callin = response.data.count;
        });
    }

    /**
     * 初始化数据
     */
    vm.init = function() {
        var deferred = $q.defer();
        var promise = deferred.promise;
        promise.then(function () {
            /*获取用户权限*/
            return vm.loadNavi();
        }).then(function () {
            vm.loadHead();//加载列表头部
            vm.loadCallinCount();//加载未确认人选
            return vm.loadOperates();//加载操作
        });
        deferred.resolve('A');
        if (angular.isDefined($localStorage.searchSetting)) {
            var searchSetting = $localStorage.searchSetting;
            if (searchSetting.searchScope!=undefined && searchSetting.searchScope!=null) vm.onlyMy = searchSetting.searchScope;//搜索范围
            vm.searchText = searchSetting.customer;
            vm.getPagedDataAsync();
            /*首次搜索之后，清空缓存*/
            if (searchSetting.customer!=''){
                searchSetting.customer = '';
                $localStorage.searchSetting = searchSetting;
            }
        } else {
            vm.getPagedDataAsync();
        }
    };

    vm.init();//加载
})
;
