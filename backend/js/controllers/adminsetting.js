/**
 * 后台用户个性化设置
 */
app.controller('AdminSettingController', function($scope, $http, $stateParams, $q, MessageWindow) {
    var vm = this;
    vm.submitting = false;
    vm.error = '';
    vm.admin_setting = {'menu':{}};
	vm.settings = '';
	vm.checked = {};
    var deferred = $q.defer();
    var promise = deferred.promise;
    promise.then(function(){
        /*获取设置选项*/
        return $http.get('/api/admin/setting/personal').success(function(response){
            vm.settings = response;
			angular.forEach(vm.settings, function (sv, si) {
				vm.checked[si] = {};
				angular.forEach(sv, function (value, index) {
					vm.checked[si][value] = false;
				});
			})
        });
		
    }).then(function(){
        /*获取用户的设置*/
        return $http.get('/api/admin/setting/admin', {"params":{'id':$stateParams.id}}).success(function(response){
			if (response.system != undefined){
				angular.forEach(response.system, function(s_value, s_index){
					angular.forEach(s_value, function (value, index) {
						if (value) vm.checked[s_index][index] = true;
					});
				});
			}
        });
    });
    deferred.resolve('A');
    vm.update = function(){
        vm.submitting = true;
		$http({
			method:'post',
			url:'/api/admin/setting/updatesystem',
			data:{id:$stateParams.id, system:vm.checked}
		}).success(function(req){
			vm.submitting = false;
			vm.error = "已更新";
            MessageWindow.open('已更新');
		}).error(function(){
            MessageWindow.open('更新失败');
		});
    }
});