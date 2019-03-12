/**
 * 信息提示
 */
app.controller('ModalInstanceController', ['$scope', '$modalInstance', 'msg', function($scope, $modalInstance, msg) {
    $scope.msg = msg;
    $scope.buttons = angular.isDefined($scope.$parent.buttons)?$scope.$parent.buttons:[];
    $scope.ok = function () {
        $modalInstance.close('ok');
    };
}]);