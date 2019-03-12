'use strict';

/* Controllers */
// callin user list controller
app.controller('CallinUserController', function($scope, $modal, $http, $stateParams, $filter, MessageWindow, Adviser, CallinCustomer) {
    var vm = this;
    vm.operates = [];//1=>分配顾问；2=>导出数据
    vm.searchParam = {'reg_time_start': '', 'reg_time_end': '', 'adviser_id': ''};//详细搜索
    vm.type = '';

    /*搜索*/
    $scope.date_format = 'yyyy-MM-dd';
    $scope.date_opens = [false, false];
    $scope.dateOptions = {
        formatYear: 'yy',
        startingDay: 1,
        class: 'datepicker'
    };
    $scope.filterText = "";//搜索
    $scope.operate = 0;//操作
    /*分页设置*/
    $scope.pagingOptions = {
        pageSizes: [10, 20, 50, 100, 200, 500],
        pageSize: 50,
        currentPage: 1
    };
    $scope.selectedItems = [];//选中项目
    $scope.totalServerItems = 0;//记录总数
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

    /*填充表格数据*/
    $scope.setPagingData = function(data, length){
        $scope.myData = data;
        $scope.totalServerItems = length;
        if (!$scope.$$phase) {
            $scope.$apply();
        }
    };
    /*更新表格数据*/
    $scope.getPagedDataAsync = function () {
        setTimeout(function () {
            var is_assign = $stateParams.type == 'unsignned' ? 1 : 0,
                ft = null,
                start_date = null,
                end_date = null;
            if ($scope.filterText) {
                /*搜索*/
                ft = $scope.filterText.toLowerCase();
            }
            if (vm.searchParam.reg_time_start != '') {
                start_date = $filter('date')(vm.searchParam.reg_time_start, 'yyyy-MM-dd');
            }
            if (vm.searchParam.reg_time_end != '') {
                end_date = $filter('date')(vm.searchParam.reg_time_end, 'yyyy-MM-dd');
            }
            CallinCustomer.users($scope.pagingOptions.currentPage, $scope.pagingOptions.pageSize, ft, start_date, end_date, is_assign).then(
                function (response) {
                    $scope.setPagingData(response.data.list, response.data.count);
                }
            );
        }, 100);
    };

    /*分页*/
    $scope.$watch('pagingOptions', function (newVal, oldVal) {
        if (newVal !== oldVal && newVal.currentPage !== oldVal.currentPage) {
            $scope.getPagedDataAsync();
        }
    }, true);
    $scope.date_open = function($event, $index) {
        $event.preventDefault();
        $event.stopPropagation();

        $scope.date_opens[$index] = true;
    };
    $scope.search = function(){
        $scope.getPagedDataAsync();
    }

    /*加载视图*/
    vm.loadViews = function(){
        if ($stateParams.type == 'unsignned'){
            vm.operates.push({'id':1, 'text':'分配顾问'});
            vm.type = '其他人选';
        } else if ($stateParams.type == 'signned') {
            vm.type = '名下人选';
        }
    }
    /*操作*/
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

    /*弹出框*/
    vm.popup = function () {
        var arr_uid = [];
        angular.forEach($scope.selectedItems, function(value, key){
            arr_uid.push(value.uid);
        });
        CallinCustomer.assign(arr_uid, $scope.operate).then(function (response) {
            if (response) {
                $scope.getPagedDataAsync();
                MessageWindow.open('分配成功');
                $scope.selectedItems.length = 0;//清空选中数据
            } else {
                MessageWindow.open('分配失败');
            }
        });
    };
    /*初始化表格*/
    vm.init = function(){
        vm.loadViews();
        /*初始化表格*/
        $scope.getPagedDataAsync();
    }
    vm.init();
})
;