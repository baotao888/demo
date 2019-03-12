/**
 * 信息提示
 */
app.controller('ModalshowsureController', ['$scope', '$modalInstance', 'msg',function($scope, $modalInstance, msg) {
    $scope.msg = msg;
    $scope.tosure = false;
    $scope.ok = function () {
      $scope.tosure = true;
      $modalInstance.close($scope.tosure);
    };
    $scope.cancel = function () {
      $scope.tosure = false;
      $modalInstance.dismiss('cancel');
    };
    
}]);