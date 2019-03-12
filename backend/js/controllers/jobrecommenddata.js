app.controller('JobrecommendDataController', ['$scope', '$http', '$stateParams', '$state', 'MessageWindow', 'Errors', 'JobChoice', function ($scope, $http, $stateParams, $state, MessageWindow, Errors, JobChoice) {
    $scope.list = [];
    $scope.error = '';
    $scope.submitting = false;
    $scope.recommenddata = [];
    $scope.checked = [];
    JobChoice.all().then(function (response) {
        var list = response.data;
        angular.forEach(list, function (i) {
            $scope.list.push(i);
            $scope.checked[i.id] = false;
        });
    }).then(function () {
        /*获取已推荐的职位*/
        $http.get('/api/job/recommend/jobs', {'params': {id: $stateParams.id}}).success(function (response) {
            angular.forEach(response, function (i) {
                $scope.checked[i] = true;
            });
        });
    });

    /*添加推荐职位*/
    $scope.submitInfo = function () {
        $scope.submitting = true;
        var jobs = [];
        angular.forEach($scope.checked, function (value, id) {
            if (value) jobs.push(id);
        })
        $http({
            method: 'post',
            url: '/api/job/recommend/add',
            data: {id: $stateParams.id, "jobs/a": jobs}
        }).success(function (){
            MessageWindow.open(Errors.common_success);
            $state.go('app.job.recommendlist', {id: $stateParams.id});
        }).error(function () {
            MessageWindow.open(Errors.sys_error);
            $scope.submitting = false;
        });
    }
}]);