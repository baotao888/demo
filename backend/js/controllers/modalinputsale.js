/**
 * 输入表单项
 */
app.controller('ModalInputSaleController', ['$scope', '$state', '$modalInstance', '$http', '$state', 'type', 'operate', 'selectedCustomer', function($scope, $state, $modalInstance, $http, $state, type, operate, selectedCustomer) {
   $scope.operate = operate;
   $scope.type = type;
   var data, url;

   $scope.ok = function() {
      if (operate == 1) { //入账
        url = '/api/salesorder/index/sure';
        if ($scope.ondutyday) {
          data = $scope.ondutyday;
        } else if ($scope.worktime) {
           data = $scope.worktime
        }
        var params = {
            id: selectedCustomer,
            work_time: data
        };
        $scope.requestOperate(url, params);
      } else if (operate == 2) { //删除
         url = '/api/salesorder/index/delete';
         var arrid = [];
         arrid.push(selectedCustomer);
         var params = {
            'id/a': arrid,
            'note': $scope.deletereason
         };
        $scope.requestOperate(url, params);
      } else if (operate == 3) { //恢复
        url = '/api/salesorder/index/recover';
        var arrid = [];
        arrid.push(selectedCustomer);
        var params = {
            'id/a': arrid,
            'note': $scope.recovery
         };

        $scope.requestOperate(url, params);
      } else if (operate == 4) {
        url = '/api/salesorder/index/receiveallowance';
        var arrid = [];
        arrid.push(selectedCustomer);
        var params = {
          'id/a': arrid,
          'pay_way': $scope.receive,
          'is_borrow': $scope.advance,
          'note': $scope.remarks
        };
        $scope.requestOperate(url, params);
      } else if (operate == 5) {
        url = '/api/salesorder/index/receiverecommend';
        var arrid = [];
        arrid.push(selectedCustomer);
        var params = {
          'id/a': arrid,
          'pay_way': $scope.recomm,
          'is_borrow': $scope.isadvance,
          'note': $scope.marks
        };
        $scope.requestOperate(url, params);
      } else if (operate == 7) {
        url = '/api/salesorder/index/adjusthourprice';
        var arrid = [];
        arrid.push(selectedCustomer);
        var params = {
          'id/a': arrid,
          'price': $scope.difference,
          'note': $scope.mark
        };
        $scope.requestOperate(url, params);
      } else if (operate == 8) {
        url = '/api/salesorder/index/goonduty';
        var arrid = [];
        arrid.push(selectedCustomer);
        var params = {
          'id/a': arrid,
          'note': $scope.mark
        };
        $scope.requestOperate(url, params);
      } else if (operate == 9) {
        url = '/api/salesorder/index/outduty';
        var arrid = [];
        arrid.push(selectedCustomer);
        var params = {
          'id/a': arrid,
          'note': $scope.mark
        };
        $scope.requestOperate(url, params);
      }
   }

   $scope.requestOperate = function(url, params) {
    $http({method: 'post', url: url, params: params}).success(function(res) {
        $modalInstance.close('ok');
        $state.reload('app.performance.salelist');
      });
   }

}]);