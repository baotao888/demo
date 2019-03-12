'use strict';

/* Controllers */
// user list controller
app.controller('UserListController', function($scope, User, $modal, $http, $stateParams, $filter, OperateButtons, MessageWindow, Adviser, CallinCustomer) {
    var vm = this;
    $scope.filterText = "";//搜索
    vm.searchParam = {'reg_time_start':'', 'reg_time_end':'', 'adviser_id':''};//详细搜索
    vm.myButtons = {};//按钮权限
    $scope.operate = 0;//操作
    $scope.selectedItems = [];//选中项目
    $scope.totalServerItems = 0;//记录总数
	vm.type = '所有';
	vm.operates = [];//1=>分配顾问；2=>导出数据

    /*分页设置*/
    $scope.pagingOptions = {
        pageSizes: [10, 20, 50, 100, 200, 500],
        pageSize: 50,
        currentPage: 1
    };
    /*填充表格数据*/
    $scope.setPagingData = function(data, page, pageSize, length){
        $scope.myData = data;
        $scope.totalServerItems = length;
        if (!$scope.$$phase) {
            $scope.$apply();
        }
    };
    /*更新表格数据*/
    $scope.getPagedDataAsync = function () {
        setTimeout(function () {
            var data,
                ft = null,
                start_date = null,
                end_date = null;
            if ($scope.filterText) {
                /*搜索*/
                ft = $scope.filterText.toLowerCase();
            }
            if (vm.searchParam.reg_time_start!='') {
                start_date = $filter('date')(vm.searchParam.reg_time_start, 'yyyy-MM-dd');
            }
            if (vm.searchParam.reg_time_end!='') {
                end_date = $filter('date')(vm.searchParam.reg_time_end, 'yyyy-MM-dd');
            }
			if ($stateParams.type == 'unsignned'){
				vm.type = '其他人选';
                $http({method: 'get', url: '/api/user/index/index', params: {
                    page: $scope.pagingOptions.currentPage,
                    pagesize: $scope.pagingOptions.pageSize,
                    keyword: ft,
                    reg_time_start: start_date,
                    reg_time_end: end_date
                }}).then(function (response) {
                    data = response.data.list;
                    $scope.setPagingData(data, response.data.count);
                });
			} else if ($stateParams.type == 'signned'){
                vm.type = '名下人选';
                CallinCustomer.advisers($scope.pagingOptions.currentPage, $scope.pagingOptions.pageSize, ft, start_date, end_date).then(
                    function (response) {
                        $scope.setPagingData(response.data.list, response.data.count);
                    }
                );
            } else {
			    /*所有用户*/
                User.get({
                    page: $scope.pagingOptions.currentPage,
                    pagesize: $scope.pagingOptions.pageSize,
                    keyword: ft,
                    reg_time_start: start_date,
                    reg_time_end: end_date
                },function(response){
                    data = response.list;
                    $scope.setPagingData(data, response.count);
                });
            }
        }, 100);
    };
    
    /*分页*/
    $scope.$watch('pagingOptions', function (newVal, oldVal) {
        if (newVal !== oldVal && newVal.currentPage !== oldVal.currentPage) {
            $scope.getPagedDataAsync();
        }
    }, true);
    /*搜索*/
    $scope.date_opens = [false, false];
    $scope.date_format = 'yyyy-MM-dd';
    $scope.dateOptions = {
        formatYear: 'yy',
        startingDay: 1,
        class: 'datepicker'
    };
    $scope.date_open = function($event, $index) {
        $event.preventDefault();
        $event.stopPropagation();

        $scope.date_opens[$index] = true;
    };
    $scope.search = function(){
        $scope.getPagedDataAsync();
    }
    /*操作*/
	/*设置客户的操作*/
	vm.loadOperates = function(){
        OperateButtons.success(function(response){
            vm.myButtons = response;
        });
		if ($stateParams.type == 'unsignned'){
			vm.operates.push({'id':1, 'text':'分配顾问'});
			//vm.operates.push({'id':2, 'text':'导出数据'});
		} else if ($stateParams.type == 'all') {
			vm.operates.push({'id':2, 'text':'导出数据'});
		}
	}
    $scope.doOperate = function(){
        if ($scope.operate == 1) {
            //分配顾问
            vm.popup();
        }
        else if ($scope.operate == 2) {
            var arr_uid = [];
            angular.forEach($scope.selectedItems, function(value, key){
                arr_uid.push(value.uid);
            });
            //导出数据
            $http({
                method:'post',
                url:'/api/user/index/export',
                data:{'users/a':arr_uid},
                responseType: 'arraybuffer'
            }).success(function(data){
                var blob = new Blob([data], {type: "application/vnd.ms-excel"});
                var objectUrl = URL.createObjectURL(blob);
                var aForExcel = $("<a><span class='forExcel'>下载excel</span></a>").attr("href",objectUrl);
                $("body").append(aForExcel);
                $(".forExcel").click();
                aForExcel.remove();
                URL.revokeObjectURL(objectUrl);
            }).error(function(data){
                console.log(data);
            });
        }
    }
    /*表格配置*/
    $scope.gridOptions = {
        data: 'myData',
        enablePaging: true,
        showFooter: true,
        totalServerItems: 'totalServerItems',
        pagingOptions: $scope.pagingOptions,//分页设置
        showSelectionCheckbox: true,//显示选择框
        selectedItems: $scope.selectedItems//选中列表
    };
    /*弹出框*/
    vm.popup = function () {
        var arr_uid = [];
        angular.forEach($scope.selectedItems, function(value, key){
            arr_uid.push(value.uid);
        });
        Adviser.open(arr_uid, $scope.operate).then(function (response) {
            if (response) {
                $scope.getPagedDataAsync();
                MessageWindow.open('分配成功');
            } else {
                MessageWindow.open('分配失败');
            }
        });
    };
	/*初始化表格*/
	vm.init = function(){
		vm.loadOperates();
		/*初始化表格*/
    	$scope.getPagedDataAsync();
	}
	vm.init();
})
;