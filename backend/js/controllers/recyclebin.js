'use strict';

/* Controllers */
// customer recycle bin
app.controller('RecycleBinController', function($scope, $http, MessageWindow, ConfirmWindow, RecycleBin, Errors) {
    $scope.totalServerItems = 0;
    $scope.selectedItems = [];//选中项目
    $scope.pagingOptions = {
        pageSizes: [50, 250, 500, 1000],
        pageSize: 50,
        currentPage: 1
    };
    $scope.gridOptions = {
        data: 'myData',
        enablePaging: true,
        showFooter: true,
        totalServerItems: 'totalServerItems',
        pagingOptions: $scope.pagingOptions,
        showSelectionCheckbox: true,//显示选择框
        selectedItems: $scope.selectedItems//选中列表
    };
    $scope.operate = 0;//操作
    $scope.operates = [{'id':1, 'text':'还原'},{'id':2, 'text':'彻底删除'}];

    $scope.setPagingData = function(data, length){
        $scope.myData = data;
        $scope.totalServerItems = length;
        if (!$scope.$$phase) {
            $scope.$apply();
        }
    };
    $scope.getPagedDataAsync = function () {
        setTimeout(function () {
            var data,
                ft = '';
            if ($scope.filterText) {
                /*搜索*/
                ft = $scope.filterText.toLowerCase();
            }
            RecycleBin.searchCustomer($scope.pagingOptions.currentPage, $scope.pagingOptions.pageSize, ft).then(function (response) {
                data = response.data.list;
                $scope.setPagingData(data, response.data.count);
            });
        }, 100);
    };

    $scope.getPagedDataAsync();

    $scope.$watch('pagingOptions', function (newVal, oldVal) {
        if (newVal !== oldVal && newVal.currentPage !== oldVal.currentPage) {
            $scope.getPagedDataAsync();
        }
    }, true);

    $scope.doOperate = function(){
        var arr_id = [];
        angular.forEach($scope.selectedItems, function(value, key){
            arr_id.push(value.id);
        });
        if ($scope.operate == 1) {
            /*还原*/
            ConfirmWindow.open("确定要还原此客户吗？").then(function(){
                RecycleBin.restore(arr_id).then(function (response) {
                    MessageWindow.open("成功还原"+response.data.success+"个客户。"+response.data.error+"个客户还原有误。");
                    $scope.getPagedDataAsync();
                }, function () {
                    MessageWindow.open(Errors.sys_error);
                });
            });
        } else if ($scope.operate == 2) {
            /*还原*/
            ConfirmWindow.open("删除之后无法还原！您确定要彻底删除此客户吗？").then(function(){
                RecycleBin.delete(arr_id).then(function (response) {
                    MessageWindow.open("成功删除"+response.data.success+"个客户。"+response.data.error+"个客户删除有误。");
                    $scope.getPagedDataAsync();
                }, function () {
                    MessageWindow.open(Errors.sys_error);
                });
            });
        }
    }
})
;