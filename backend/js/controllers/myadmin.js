app.controller('MyAdminController', function($scope, $state, $http, Errors, $stateParams, $modal) {
    var vm = this;
    /*初始化*/
    vm.error = '';
    vm.submitting = false;
    vm.account = {"id":"", "admin_name":"", "admin_pwd":""};
    vm.confirm_password = "";

    /*修改密码*/
	vm.submitInfo = function () {
		vm.submitting = true;
		$http({
			method:'post',
			url:'/api/index/index/updatePassword',
			data:{admin_pwd:vm.account.admin_pwd}
		}).success(function(req){
			vm.open('密码已更新');
			vm.error = '已更新';
			vm.submitting = false;
		}).error(function(){
			vm.open('更新失败');
			vm.error = '更新失败';
			vm.submitting = false;
		});
	}
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
