/**
 * 更新候选人返费金额
 */
app.controller('AwardFormController', function($http, $stateParams, $modal) {
    var vm = this;
    vm.submitting = false;
    vm.error = '';
    vm.candidate = {};
	vm.getCandidate = function(){
		$http({
			method:'get',
			url:'/api/customer/candidate/detail',
			params:{id:$stateParams.id}
		}).success(function(response){
			vm.candidate = response;
			console.log(vm.candidate);
		});
	}
	
    vm.update = function(){
        vm.submitting = true;
        $http({
			method:'post',
			url:'/api/customer/candidate/updateAward',
			data:{id:$stateParams.id, award:vm.candidate.award}
		}).success(function(req){
			vm.showMessage('更新成功');
			console.log(req);
		}).error(function(){
			vm.showMessage('更新失败');
			vm.submitting = false;
		});
    }
	vm.showMessage = function (msg) {
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
	
	vm.loadCandidate = function(){
		vm.getCandidate();
	}
	vm.loadCandidate();
});