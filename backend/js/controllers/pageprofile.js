'use strict';

/* Controllers */
// admin profile controller
app.controller('PageProfileController', function($http) {
    var vm = this;
    vm.user = {};
    /*获取用户信息*/
	$http.get('/api/index/index/profile').success(function(response){
	  vm.user = response;
	});
})
;