app.controller('PanelAdminController', function($scope, $http, MessageWindow, ConfirmWindow) {
	$scope.error = "";
    $scope.updateCache = function (){
		$http.get('/api/index/panel/cache').success(function (response) {
			$scope.error = response;
            MessageWindow.open($scope.error);
		});
	}
	$scope.cronCandidate = function () {
        ConfirmWindow.open("确定要清理所有顾问的过期人选吗").then(function(){
            $http.get('/api/index/panel/cronCandidates').success(function (response) {
                $scope.error = response;
                MessageWindow.open($scope.error);
            }).error(function () {
                MessageWindow.open('sys error')
            });
        });
    }
    $scope.updateJobStatistics = function(){
        $http.get('/api/job/statistics/updatesignup').success(function (response) {
            $scope.error = response;
            MessageWindow.open($scope.error);
        });
	}
    $scope.deleteCustomer = function (type) {
        var arr_type = ['保留','空号','用户关机','不在服务区', '停机','被叫忙','网络忙','对方设置了呼入限制','久叫不应'];
        ConfirmWindow.open("确定要删除" + arr_type[type] + "的客户吗").then(function(){
            $http.get('/api/customer/pool/deleteCustomer', {params: {type: type}} ).success(function (response) {
                $scope.error = response;
                MessageWindow.open($scope.error);
            });
        });
    }
    $scope.deleteAdminLog = function () {
        ConfirmWindow.open("确定要删除后台操作日志吗").then(function(){
            $http.get('/api/index/panel/deleteAdminLog').success(function (response) {
                $scope.error = response;
                MessageWindow.open($scope.error);
            });
        });
    }
    $scope.clearQuitEmployeeCandidate = function () {
        ConfirmWindow.open("确定要清理离职顾问的人选吗").then(function(){
            $http.get('/api/index/panel/clearQuitEmployeeCandidate').success(function (response) {
                $scope.error = response;
                MessageWindow.open($scope.error);
            });
        });
    }
    $scope.deleteCandidate = function () {
        ConfirmWindow.open("确定要删除丢弃的人选吗").then(function(){
            $http.get('/api/index/panel/deleteCandidate').success(function (response) {
                $scope.error = response;
                MessageWindow.open($scope.error);
            });
        });
    }
});