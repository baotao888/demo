app.controller('LabourserviceformController', function($http, $state, $stateParams) {
  var vm = this;
  vm.labouroption = "新增";
  vm.labourlist = [{id:0, name: ''}];

  //新增
  vm.recruitform = function() {
   var  data = {'name': vm.labourlist.name}
    $http.post('/api/labourservice', data).success(function(res) {
      $state.go('app.labourservice.list');
    })
  }

  //更新
   if ($stateParams.id != null && $stateParams.id !='') {
    vm.labouroption = "更新";

    $http.get('/api/labourservice/'+$stateParams.id).success(function(res) {
      vm.labourlist.name = res.name;
    })

    vm.recruitform = function() {
      var data =  {'name': vm.labourlist.name};
     $http.put('/api/labourservice/'+$stateParams.id, data).success(function() {
        $state.go('app.labourservice.list');
     })
    }
   }

});