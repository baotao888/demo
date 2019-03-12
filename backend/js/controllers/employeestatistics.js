/**
 * 分配数据给员工
 */
app.controller('EmployeeStatisticsController', function($scope, $state, Employee, Errors, $stateParams, $http, $modal) {
   var vm = this;
  vm.operate = "编辑";
  vm.submitting = false;
  vm.error = '';
  vm.statistics = {id: "", candidates: "", remains: "", remain_days:"",recognize_prior_time:"",recognize_priors:""};
  if ($stateParams.id != null && $stateParams.id != '') {
     $http({
       method: 'get',
       url: '/api/admin/setting/statistics',
       params:{id:$stateParams.id}
     }).success(function(response){
          vm.statistics = response;
     });
  }


  vm.submitAlldata = function(){
    vm.submitting = true;
    var params = {employee:$stateParams.id, candidates:vm.statistics.candidates, remains:vm.statistics.remains,remain_days:vm.statistics.remain_days,recognize_prior_time:vm.statistics.recognize_prior_time,recognize_priors:vm.statistics.recognize_priors};
    $http({
      method: 'post',
      url: '/api/admin/setting/updatestatistics',
      params : params
    }).success(function(){
      vm.open('success');
      vm.error = '已分配成功!';
      $state.go('app.organization.employee');//跳转到列表页
      vm.submitting = false;
    }).error(function(){
      vm.open('failure');
      vm.error = '更新失败';
      vm.submitting = false;
    });
  };
  vm.open = function (msg) {
    var modalInstance = $modal.open({
      templateUrl: 'modal.html',
      controller: 'ModalInstanceController',
      resolve: {
        msg: function () {
          return msg;
        }
      }
    });
  };


});
