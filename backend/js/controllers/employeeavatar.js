/**
 * 员工上传头像
 */
app.controller('EmployeeAvatarController', ['$scope', "Employee", "$stateParams", "$http", function($scope, Employee, $stateParams, $http) {
    var vm = this;
    vm.employee = {};
    vm.employee.id = $stateParams.id;
    vm.employee.cover = '';
    vm.submitting = false;

    $scope.myImage='';
    $scope.myCroppedImage='';
    $scope.cropType="circle";
    $scope.error = '';
    var handleFileSelect=function(evt) {
        var file=evt.currentTarget.files[0];
        var reader = new FileReader();
        reader.onload = function (evt) {
            $scope.$apply(function($scope){
                $scope.myImage=evt.target.result;
            });
        };
        reader.readAsDataURL(file);
    };

    angular.element(document.querySelector('#fileInput')).on('change',handleFileSelect);
    $scope.updateCover = function () {
        vm.submitting = true;
		$http({
			method:'post',
			url:'/api/index/index/uploadAvatar',
			data:{avatar:$scope.myCroppedImage}
		}).success(function(req){
			$scope.error = '已更新';
            vm.submitting = false;
		}).error(function(reg){
		    $scope.error = '更新失败';
            vm.submitting = false;
		});
    }
}]);