'use strict';

/* Controllers */
// job list controller
app.controller('MyOrganizationController', function($http) {
    var vm = this;
    vm.organization = [];
    /*组织架构关系统计*/
	vm.load_organization = function () {
	  $http.get('/api/index/index/myOrganization').success(function(response){
		vm.organization = response;
	  }).error(function(){
		vm.organization = false;
	  });
	}
	vm.load_organization();
})
;