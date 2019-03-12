'use strict';

/* Controllers */
// invite list controller
app.controller('InviteListController', function($scope, $http, $modal, MessageWindow, Adviser) {
    var vm = this;
    $scope.filterText = "";//搜索
    $scope.operate = 0;//操作
    $scope.selectedItems = [];//选中项目
    $scope.totalServerItems = 0;//记录总数
    /*分页设置*/
    $scope.pagingOptions = {
        pageSizes: [10, 20, 50, 100, 200],
        pageSize: 20,
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
    $scope.getPagedDataAsync = function (pageSize, page, searchText) {
        setTimeout(function () {
            var data;
            if (searchText) {
                /*搜索*/
                var ft = searchText.toLowerCase();
                $http({
                    method:'get',
                    url:'/api/user/index/invitelist',
                    params:{page:page, pagesize:pageSize, search:ft}
                }).success(function(response){
                    data = response.list;
                    $scope.setPagingData(data,page,pageSize, response.count);
                });
            } else {
                $http({
                    method:'get',
                    url:'/api/user/index/invitelist',
                    params:{page:page, pagesize:pageSize}
                }).success(function(response){
                    data = response.list;
                    $scope.setPagingData(data,page,pageSize, response.count);
                });
            }
        }, 100);
    };
    /*初始化表格*/
    $scope.getPagedDataAsync($scope.pagingOptions.pageSize, $scope.pagingOptions.currentPage);
    /*分页*/
    $scope.$watch('pagingOptions', function (newVal, oldVal) {
        if (newVal !== oldVal && newVal.currentPage !== oldVal.currentPage) {
            $scope.getPagedDataAsync($scope.pagingOptions.pageSize, $scope.pagingOptions.currentPage, $scope.filterText);
        }
    }, true);
    /*搜索*/
    $scope.search = function(){
        $scope.getPagedDataAsync($scope.pagingOptions.pageSize, $scope.pagingOptions.currentPage, $scope.filterText);
    }
    /*操作*/
    $scope.doOperate = function(){
        if ($scope.operate == 5) {
            //分配顾问
            //vm.popup();
            MessageWindow.open('功能暂不可用，攻城狮加班开发中...');//[test]
        }
        else if ($scope.operate == 6) {
            MessageWindow.open('功能暂不可用，攻城狮加班开发中...');//[test]
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
    /*顾问选择弹出框*/
    vm.popup = function () {
        var arr_uid = [];
        angular.forEach($scope.selectedItems, function(value, key){
            arr_uid.push(value.uid);
        });
        Adviser.open(arr_uid, $scope.operate);
    };
})
;