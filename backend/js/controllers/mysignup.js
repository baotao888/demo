'use strict';

/* Controllers */
// 呼入人选
app.controller('MySignupController', function($scope, $http, $modal, $stateParams, $state, MessageWindow, CallinCustomer) {
    var vm = this,
        from,
        type;
    vm.navRadio = '';
    vm.operates = [];//2=>确认报名；3=>确认注册
    vm.listTitle = '端口报名';
    vm.unsure = {user_web: 0, user_qrcode: 0, applicant_web: 0, applicant_qrcode: 0};
    vm.user_status = {status: 0, text: '未确认'};
    vm.user_status_list = [{status: -1, text: '全部'}, {status: 1, text: '已确认'}, {status: 0, text: '未确认'}];

    $scope.filterText = "";//搜索
    /*表格配置*/
    $scope.selectedItems = [];//选中项目，必须在gridOptions声明之前
    $scope.gridOptions = {
        data: 'myData',
        enablePaging: true,
        showFooter: true,
        totalServerItems: 'totalServerItems',
        pagingOptions: $scope.pagingOptions,//分页设置
        showSelectionCheckbox: true,//显示选择框
        selectedItems: $scope.selectedItems//选中列表
    };
    $scope.operate = 0;//操作
    /*分页设置*/
    $scope.pagingOptions = {
        pageSizes: [10, 20, 50, 100, 200],
        pageSize: 50,
        currentPage: 1
    };
    $scope.totalServerItems = 0;//记录总数

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
            var data,
                ft = '';
            if ($scope.filterText) {
                /*搜索*/
                ft = $scope.filterText.toLowerCase();
            }
            if (type == 'register') {
                CallinCustomer.myUser($scope.pagingOptions.currentPage, $scope.pagingOptions.pageSize, ft, from, vm.user_status.status).then(function (response) {
                    data = response.data.list;
                    $scope.setPagingData(data, response.data.count);
                });
            } else if (type == 'signup') {
                CallinCustomer.myApplicant($scope.pagingOptions.currentPage, $scope.pagingOptions.pageSize, ft, from, vm.user_status.status).then(function (response) {
                    data = response.data.list;
                    $scope.setPagingData(data, response.data.count);
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
    $scope.search = function(){
        $scope.getPagedDataAsync();
    }
    /*操作*/
    $scope.doOperate = function(){
        if ($scope.selectedItems.length == 0) {
            MessageWindow.open('请选择用户');
            return;
        }
        if ($scope.operate == 0) {
            MessageWindow.open('请选择操作');
            return;
        }
        var arr_uid = [];
        angular.forEach($scope.selectedItems, function(value, key){
            arr_uid.push(value.user_id);
        });
        CallinCustomer.sure(arr_uid, $scope.operate, type, from).then(function () {
            MessageWindow.open('操作成功');
            $scope.getPagedDataAsync();
            vm.loadUnsure();
            $scope.selectedItems.length = 0;//清空选中数据
        }, function () {
            MessageWindow.open('操作失败，系统繁忙');
        });
    };

    /*加载操作类型*/
    vm.loadType = function(){
        if ($stateParams.type == 'register'){
            vm.listTitle = '端口注册';
            from = 'web';
            type = 'register';
        } else if ($stateParams.type == 'signup'){
            vm.listTitle = '端口报名';
            from = 'web';
            type = 'signup';
        } else if ($stateParams.type == 'qrcodereg'){
            vm.listTitle = '扫码注册';
            from = 'qrcode';
            type = 'register';
        } else if ($stateParams.type == 'qrcodeapply'){
            vm.listTitle = '扫码报名';
            from = 'qrcode';
            type = 'signup';
        }
    }
    /*加载客户的操作*/
    vm.loadOperates = function(){
        if ($stateParams.type=='register'){
            vm.operates.push({'id':3, 'text':'确认'});//确认注册用户
        } else {
            vm.operates.push({'id':2, 'text':'确认'});//确认报名用户
        }
    };
    /*未确认客户数*/
    vm.loadUnsure = function () {
        CallinCustomer.unSureCount().then(function (response) {
            vm.unsure = response.data;
        });
    };
    /*选择类型*/
    vm.selectStatus = function (item) {
        vm.user_status = item;
        $scope.getPagedDataAsync();
    };
    /*默认加载项*/
    vm.init = function(){
        vm.navRadio = $stateParams.type;
        /*初始化类型*/
        vm.loadType();
        /*加载操作*/
        vm.loadOperates();
        /*初始化表格*/
        $scope.getPagedDataAsync();
        vm.loadUnsure();
    };
    vm.init();
})
;