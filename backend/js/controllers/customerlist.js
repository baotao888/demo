'use strict';

/* Controllers */
// customer pool list controller
app.controller('CustomerListController', function($http, $modal, $stateParams, $scope, $q, $filter, $localStorage, Errors, OperateButtons, MessageWindow, Customer, ConfirmWindow, Adviser) {
    var vm = this;
    vm.list = {};
    vm.type = '公海';
    vm.customerType = $stateParams.type;
    /*操作*/
    vm.operate = 0;
    vm.operates = [];
    /*全选*/
    vm.checked = [];
    vm.check_all = false;
    /*搜索*/
    vm.searchText = '';
    vm.onlyMy = false;//是否只显示可认领的人选
    //导航菜单
    vm.navi = {};
    vm.sure = "确定";
    vm.unresubmit = false;
    vm.showOpenTime = false;//是否显示入库时间
    var startTime, endTime, isDetail;
    vm.timesearch = false;
    vm.loadNavi = function(){
        return OperateButtons.success(function(response){
            vm.navi = response;
        });
    };
    /*分页*/
    vm.inputPage = 1;
    vm.pagingOptions = {
        count: 0,//总数量
        pageSize: 20,//每页显示数量
        pageShowLength: [20,30,50,100,1000],
        currentPage: 1,//当前页
        pages: 1,//总页数
        pageSizes : [1],
        pageMax: 10//限制分页按钮显示的数量大小
    };
    vm.selectLength = function(){
        vm.getPagedDataAsync();
    }
    vm.setPagingData = function(){
        vm.pagingOptions.pages = Math.ceil(vm.pagingOptions.count/vm.pagingOptions.pageSize);//总页数
        vm.cancelAll();//取消全选
    };
    vm.loadView = function () {
        if ($stateParams.type == 'signned') {
            vm.type = '已分配';
            vm.customerType = 'signned';
        } else if ($stateParams.type == 'unsignned') {
            vm.type = '未分配';
            vm.customerType = 'unsignned';
        } else {
            vm.type = '公海';
        }
        if (vm.customerType == 'my') {
            vm.subType = '可认领'
        } else {
            vm.subType = '所有'
        }
    }
	/*更新表格数据*/
    vm.getPagedDataAsync = function () {
        vm.showLoading = true;
        Customer.list(vm.customerType, vm.searchText, vm.pagingOptions.currentPage, vm.pagingOptions.pageSize, isDetail, startTime, endTime).then(
            function (response) {
                vm.list = response.data.list;
                vm.pagingOptions.count = response.data.count;
                vm.setPagingData();
                vm.showLoading = false;
            },
            function () {
                vm.showLoading = false;
            }
        );
	};
	  /*切换分页*/
    vm.switchPage = function (){
        vm.pagingOptions.currentPage = Math.min(Math.max(1, parseInt(vm.inputPage)), vm.pagingOptions.pages);
        vm.getPagedDataAsync();
    }
    /*搜索*/
    vm.search = function(){
        vm.pagingOptions.currentPage = 1;
        vm.getPagedDataAsync();
    };
    vm.showdatalist = function(){
        isDetail = false;
        vm.pagingOptions.currentPage = 1;
        vm.getPagedDataAsync();
    };
    /*切换显示范围*/
    vm.switchType = function() {
        vm.customerType = vm.onlyMy?'my':'public';
        var searchSetting = {'customerType': vm.customerType};
        if (angular.isDefined($localStorage.searchSetting)) {
            searchSetting = $localStorage.searchSetting;
            searchSetting.customerType = vm.customerType;
        }
        $localStorage.searchSetting = searchSetting;//全局搜索设置
        vm.init();
    };
    /*设置客户的操作*/
    vm.loadOperates = function(){
        if ($stateParams.type == 'signned'){
        } else if ($stateParams.type == 'unsignned'){
            vm.operates.push({'id':4, 'text':'分配'});
            vm.operates.push({'id':2, 'text':'释放'});
        } else {
            vm.operates.push({'id':3, 'text':'认领'});
            if (vm.navi.customer_btn.distribute) vm.operates.push({'id':4, 'text':'分配'});
            vm.showOpenTime = true;
        }
    }
    /*操作*/
    vm.doOperate = function(){
        var arr_id = [];
        angular.forEach(vm.list, function(value, key){
            if (vm.checked[key]) arr_id.push(value.id);
        });
        var url = false;
        if (vm.operate == 4) {
            /*分配顾问*/
            if (arr_id.length > 0){
                vm.popupEmployee(arr_id);
            } else {
                vm.showMessage('请选择客户');
            }
        } else if (vm.operate == 2) {
            /*释放客户到公海客户池*/
            vm.sendingSubmit();
            Customer.release(arr_id).then(function (data) {
                if (data) {
                    vm.getPagedDataAsync();
                }
                vm.submitCompleted();
            });
        } else if (vm.operate == 3) {
            /*认领客户*/
            vm.sendingSubmit();
            Customer.recognize(arr_id).then(function (data) {
                if (data) {
                    vm.getPagedDataAsync();
                }
                vm.submitCompleted();
            });
        } else {
            vm.showMessage('请选择操作');
        }
    }
    vm.sendingSubmit = function () {
        vm.sure = "加载中..";
        vm.unresubmit = true;
    }
    vm.submitCompleted = function () {
        vm.sure = "确定";
        vm.unresubmit = false;
    }
    /*选择顾问弹出框*/
    vm.popupEmployee = function (arr_id) {
        Adviser.open(arr_id, vm.operate).then(function (response) {
            if (response) {
                vm.getPagedDataAsync();
                MessageWindow.open(Errors.distribute_success);
            } else {
                MessageWindow.open(Errors.distribute_error);
            }
        });
    };
    /*全选*/
    vm.selectCustomer = function (){
        var checked_length = 0;
        angular.forEach(vm.checked, function(value, key){
            if (value) checked_length += 1;
        });
        if (checked_length==vm.list.length) {
            vm.select_all = true;
        } else {
            vm.select_all = false;
        }
    }
    vm.selectAll = function (){
        angular.forEach(vm.list, function (value, key) {
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
    /*提示信息*/
    vm.showMessage = function (msg) {
        MessageWindow.open(msg);
    };
    vm.opens = [false, false];
    $scope.open = function($event,a) {
        vm.opens[a] = true;
        $event.preventDefault();
        $event.stopPropagation();
    };
    /*高级搜索*/
    vm.timersearch = function(){
        isDetail = true;
        startTime = vm.searchSign!=''?$filter('date')(vm.searchDis, 'yyyy-MM-dd'):'';
        endTime = vm.searchSign!=''?$filter('date')(vm.searchDised, 'yyyy-MM-dd'):'';
        vm.getPagedDataAsync();
    };
    vm.btnSearchDetail = function(){
        vm.timesearch = true;
    };
    /**
     * 客户预览
     */
    vm.customerDetail = function (id) {
        Customer.open(id);
    }
    /**
     * 删除客户
     */
    vm.drop = function (id) {
        /*丢弃*/
        ConfirmWindow.open("确定要删除吗").then(function(){
            Customer.drop(id).then(function (data) {
                if (data) {
                    vm.getPagedDataAsync();
                }
            });
        });
    }
    /*初始化表格*/
    vm.init = function(){
        var deferred = $q.defer();
        var promise = deferred.promise;
        promise.then(function(){
            /*获取用户权限*/
            return vm.loadNavi();
        }).then(function(){
            /*设置操作*/
            return vm.loadOperates();
        });
        deferred.resolve('A');
        if (angular.isDefined($localStorage.searchSetting)) {
            var searchSetting = $localStorage.searchSetting;
            //显示范围
            if (searchSetting.customerType != undefined && searchSetting.customerType != null) {
                vm.onlyMy = searchSetting.customerType=='my'?true:false;
                vm.customerType = searchSetting.customerType;
            }
        }
        vm.loadView();
        vm.getPagedDataAsync();
    }
    vm.init();
})
;