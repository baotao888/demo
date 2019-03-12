app.controller('FileUploadController', ['$scope', '$http', '$location', '$stateParams', function($scope, $http, $location, $stateParams) {
    var vm = this;
    vm.submitting = false;
    vm.operate = "新增";
    vm.advertisement = {title: "", listorder: 0, content: '',space_id: $stateParams.space};
    $scope.myImage='';
    vm.error = '';

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

    vm.submitInfo = function () {
        vm.submitting = true;
        $http({
            method:'post',
            url:'/api/poster/index/save',
            data:{title: vm.advertisement.title, listorder: vm.advertisement.listorder, content: $scope.myImage, space_id: vm.advertisement.space_id}
        }).success(function(req){
            $location.path('/app/poster/list/' + vm.advertisement.space_id);
        }).error(function(reg){
            $scope.error = '更新失败';
            vm.submitting = false;
        });
    };
}]);