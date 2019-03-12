app.controller('ArticleThumbController', ['$scope', "Article", "$stateParams", function($scope, Article, $stateParams) {
    var vm = this;
    vm.article = {};
    vm.article.id = $stateParams.id;
    vm.article.cover = '';
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
    Article.get({id: $stateParams.id},function(response){
        $scope.article = response;
    });
    $scope.updateCover = function () {
        vm.submitting = true;
        Article.update({id:$stateParams.id}, {"cover":$scope.myCroppedImage},function(){
            $scope.error = '已更新';
            vm.submitting = false;
        },function(){
            $scope.error = '更新失败';
            vm.submitting = false;
        });
    }
}]);