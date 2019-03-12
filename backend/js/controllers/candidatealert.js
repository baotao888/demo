'use strict';

app.controller('CandidateAlertController', function($http, $modal, $stateParams, $filter, CandidateRemain, MessageWindow, CandidateTag, Contact, CandidateTask, Customer) {
    var vm = this,
        params = {};
    vm.searchText = '';
    vm.opens = [false, false];
    vm.inputPage = 1;
    vm.pagingOptions = {
        count: 0,
        pageSize: 20,
        currentPage: 1,
        pages: 1,
        pageSizes : [1],
        pageMax: 8//限制分页按钮显示的数量大小
    };

    /**
     * 打开日期选择框
     */
    vm.open = function($event, a) {
        vm.opens[a] = true;
        $event.preventDefault();
        $event.stopPropagation();
    };

    /**
     * 分页
     */
    vm.setPagingData = function(){
        vm.pagingOptions.pages = Math.ceil(vm.pagingOptions.count/vm.pagingOptions.pageSize);//总页数
    };
    /**
     * 分页跳转
     */
    vm.switchPage = function (){
        vm.pagingOptions.currentPage = Math.min(Math.max(1, parseInt(vm.inputPage)), vm.pagingOptions.pages);
        vm.getPagedDataAsync();
    }

    /**
     * 请求数据
     */
    vm.getPagedDataAsync = function () {
        vm.showLoading = true;
        var searchText = null,
            startTime = vm.startTime!=''?$filter('date')(vm.startTime, 'yyyy-MM-dd'):'',
            endTime = vm.endTime!=''?$filter('date')(vm.endTime, 'yyyy-MM-dd'):'',
            page = vm.pagingOptions.currentPage,
            pageSize = vm.pagingOptions.pageSize;
        if (vm.searchText != '') { searchText = vm.searchText.toLowerCase();}
        /*高级搜索*/
        params = {
            pagesize: pageSize,
            page: page,
            search: searchText,
            distribute_time_s: startTime,
            distribute_time_e: endTime,
            is_remain: vm.remain,
            is_intention: vm.intention
        };
        var promise = $http({method: 'get', url: '/api/customer/candidate/danger', params: params}).success(function(response){
            vm.remindlist = response.list;
            vm.pagingOptions.count = response.count;
            vm.setPagingData();
        });
        promise.then(function () {
            vm.showLoading = false;
        }, function(){
            vm.showLoading = false;
        })
    };

    /**
     * 搜索
     */
    vm.searchar = function(){
        vm.getPagedDataAsync();
    };

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
    }

    /**
     * 保留人选
     */
    vm.remainCandidate = function (candidate){
        CandidateRemain.remain(candidate).then(function (data) {
            if (data) vm.getPagedDataAsync();
        });
    }

    /**
     * 取消保留人选
     */
    vm.cancelRemain = function (candidate) {
        CandidateRemain.cancel(candidate).then(function (data) {
            if (data) vm.getPagedDataAsync();
        });
    }

    /**
     * 设置标签
     */
    vm.setTag = function (candidate) {
        CandidateTag.open(candidate, 0);
    }

    /*创建联系记录*/
    vm.contactRecord = function (candidate) {
        Contact.open(candidate);
    };

    /*一键保留*/
    vm.remainAll = function () {
        CandidateRemain.all().then(function (data) {
            if (data) vm.getPagedDataAsync();
        });
    }

    /**
     * 客户预览
     */
    vm.customerDetail = function (id) {
        Customer.open(id);
    }

    /**
     * 初始化数据
     */
    vm.init = function(){
        vm.getPagedDataAsync(vm.pagingOptions.currentPage, vm.pagingOptions.pageSize);
    };
    vm.init();//加载
})
;