app.controller('JobrecommendListController', ['$scope', '$http', '$stateParams', 'MessageWindow', 'Errors', function ($scope, $http, $stateParams, MessageWindow, Errors) {
    $scope.list = [];
    /*获取已推荐的职位*/
    $scope.loadJob = function () {
        $http.get('/api/job/recommend/details', {'params': {id: $stateParams.id}}).success(function (response) {
            $scope.list = response;
        });
    }

    /*推荐职位排序*/
    $scope.listorder = function (index) {
        $http({
            method: 'post',
            url: '/api/job/recommend/listorder',
            data: {id: $stateParams.id, job: $scope.list[index].id, order: $scope.list[index].list_order}
        }).success(function (){
            MessageWindow.open(Errors.common_success);
        }).error(function () {
            MessageWindow.open(Errors.sys_error);
        });
    }

    $scope.loadJob();
}]);