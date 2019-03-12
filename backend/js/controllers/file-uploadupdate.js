app.controller('FileUploadupdateController', ['$scope', '$http', '$location', '$stateParams', function($scope, $http, $location, $stateParams) {
    var vm = this;
    vm.operate = "更新";
    vm.advertisement = {id: "", title: "", listorder: "", content: "", disabled: true};
    $scope.myImage = '';
    vm.error = '';
    vm.advertisement.id = $stateParams.id;

    vm.fileloadupdate = function (){
        $http({
        method: 'get',
        url: '/api/poster/index/detail',
        params: {
            'id' : $stateParams.id
        }
        }).success (function (response) {
            vm.advertisement = response;
            if (vm.advertisement.disabled == 1) vm.advertisement.disabled = true;
        });
    };

    /*上传图片*/
    var handleFileSelect=function (evt) {
        var file = evt.currentTarget.files[0];
        var reader = new FileReader();
        reader.onload = function (evt) {
            $scope.$apply (function ($scope) {
                $scope.myImage = evt.target.result;
            });
        };
        reader.readAsDataURL(file);
    };

    vm.submitInfo = function(){
        $http({
            method: 'post',
            url: '/api/poster/index/update',
            data: {
                id: vm.advertisement.id,
                title: vm.advertisement.title,
                listorder: vm.advertisement.listorder,
                content: $scope.myImage,
                disabled: vm.advertisement.disabled
            }
        }).success(function () {
            $location.path('/app/poster/list/' + vm.advertisement.space_id);
        }).error(function (res) {
            $scope.error = '更新失败';
        })
    };

    vm.fileloadupdate();
    angular.element(document.querySelector('#fileInput')).on('change', handleFileSelect);
}]);