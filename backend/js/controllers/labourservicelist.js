app.controller('LabourservicelistController', function($http) {
  var vm = this;
  vm.headers = ["劳务公司编号", "劳务公司名称", "操作"];

  vm.init = function () {
    $http({method: 'get', url: '/api/labourservice'}).success(function(res){
      vm.labourlist = res;
    });
   }
   vm.init();
});