'use strict';

/* Controllers */
// user list controller
app.controller('WechatUserListController', function($scope, $http) {
    $scope.filterOptions = {
        filterText: "",
        useExternalFilter: true
    };
    $scope.totalServerItems = 0;
    $scope.pagingOptions = {
        pageSizes: [50, 250, 500, 1000],
        pageSize: 50,
        currentPage: 1
    };
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
                url = '/api/user/wechat/subscribers',
                ft = '';
            if ($scope.filterText) {
                /*搜索*/
                ft = $scope.filterText.toLowerCase();
            }
            $http({
                method:'get',
                url:url,
                params:{page:$scope.pagingOptions.currentPage, pagesize:$scope.pagingOptions.pageSize, search:ft}
            }).success(function(response){
                data = response.list;
                $scope.setPagingData(data, response.count);
            });
        }, 100);
    };

    $scope.getPagedDataAsync();

    $scope.$watch('pagingOptions', function (newVal, oldVal) {
        if (newVal !== oldVal && newVal.currentPage !== oldVal.currentPage) {
            $scope.getPagedDataAsync();
        }
    }, true);

    $scope.gridOptions = {
        data: 'myData',
        enablePaging: true,
        showFooter: true,
        totalServerItems: 'totalServerItems',
        pagingOptions: $scope.pagingOptions
    };
})
;