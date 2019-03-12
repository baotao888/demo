app.controller('JobCoverController', ['$scope', "Job", "$stateParams", function($scope, Job, $stateParams) {
    var vm = this;
    vm.job = {};
    vm.job.id = $stateParams.id;
    vm.job.cover = '';

    $scope.myImage='';
    $scope.myCroppedImage='';
    $scope.cropType="circle";
    $scope.error = '';
    $scope.submitting = false;
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
    Job.get({id: $stateParams.id},function(response){
        $scope.job = response;
    });
    $scope.updateCover = function () {
        $scope.submitting = true;
        Job.update({id:$stateParams.id}, {"cover":$scope.myCroppedImage},function(){
            $scope.error = '已更新';
            $scope.submitting = false;
        },function(){
            $scope.error = '更新失败';
            $scope.submitting = false;
        });
    }
}]);