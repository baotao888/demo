app.controller('PatchController', function($scope, $http, MessageWindow, ConfirmWindow) {
    $scope.updateUserOfAddAdviser = function () {
        ConfirmWindow.open("确定要更新所有端口人选的分配顾问吗").then(function(){
            $http.get('/api/index/patch/updateUserAdviser').success(function (response) {
                $scope.error = response;
                MessageWindow.open($scope.error);
            });
        });
    }
    $scope.clearRepeatCandidate = function () {
        ConfirmWindow.open("确定要清理所有重复的人选吗").then(function(){
            $http.get('/api/index/patch/deletedRepeatCandidate').success(function (response) {
                $scope.error = response;
                MessageWindow.open($scope.error);
            });
        });
    }
    $scope.trimCustomerPhone = function () {
        ConfirmWindow.open("确定要去除客户手机号码前面的空格吗").then(function(){
            $http.get('/api/index/patch/trimCustomerPhone').success(function (response) {
                $scope.error = response;
                MessageWindow.open($scope.error);
            });
        });
    }
    $scope.importUserToCallin = function () {
        ConfirmWindow.open("确定要导入端口用户到CRM系统呼入用户吗").then(function(){
            $http.get('/api/index/patch/importUserToCallin').success(function (response) {
                $scope.error = response;
                MessageWindow.open($scope.error);
            });
        });
    }
    $scope.importUserJobProcessToCallinTrace = function () {
        ConfirmWindow.open("确定要导入端口用户报名申请到CRM系统呼入用户动态吗").then(function(){
            $http.get('/api/index/patch/importUserJobProcessToCallinTrace').success(function (response) {
                $scope.error = response;
                MessageWindow.open($scope.error);
            });
        });
    }
    $scope.candidate2salesorder = function () {
        ConfirmWindow.open("确定生成销售订单吗").then(function(){
            $http.get('/api/index/patch/candidate2salesorder').success(function (response) {
                $scope.error = response;
                MessageWindow.open($scope.error);
            });
        });
    }
    $scope.job2recruit = function () {
        ConfirmWindow.open("确定导入招聘职位吗").then(function(){
            $http.get('/api/index/patch/job2recruit').success(function (response) {
                $scope.error = response;
                MessageWindow.open($scope.error);
            });
        });
    }
});