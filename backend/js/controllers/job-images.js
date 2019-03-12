app.controller('JobImagesController', ['$scope', 'FileUploader', 'authService', 'Job', '$stateParams', function($scope, FileUploader, authService, Job, $stateParams) {
    var uploader = $scope.uploader = new FileUploader({
        url: '/api/job/picture/upload',
        headers: authService.getToken()
    });
    $scope.error = '';
    $scope.slides = [];
    //GET INFO
    $scope.job = {};
    $scope.submitting = false;
    Job.get({id: $stateParams.id},function(response){
        $scope.job = response;
        $scope.slides = response.detail.pictures;
    });
    // FILTERS
    uploader.filters.push({
        name: 'customFilter',
        fn: function(item /*{File|FileLikeObject}*/, options) {
            return this.queue.length < 10;
        }
    });

    // CALLBACKS
    uploader.onWhenAddingFileFailed = function(item /*{File|FileLikeObject}*/, filter, options) {
        //console.info('onWhenAddingFileFailed', item, filter, options);
    };
    uploader.onAfterAddingFile = function(fileItem) {
        //console.info('onAfterAddingFile', fileItem);
    };
    uploader.onAfterAddingAll = function(addedFileItems) {
        //console.info('onAfterAddingAll', addedFileItems);
    };
    uploader.onBeforeUploadItem = function(item) {
        //console.info('onBeforeUploadItem', item);
    };
    uploader.onProgressItem = function(fileItem, progress) {
        //console.info('onProgressItem', fileItem, progress);
    };
    uploader.onProgressAll = function(progress) {
        //console.info('onProgressAll', progress);
    };
    uploader.onSuccessItem = function(fileItem, response) {
        //console.info('onSuccessItem', fileItem, response);
    };
    uploader.onErrorItem = function(fileItem, response) {
        //console.info('onErrorItem', fileItem, response);
    };
    uploader.onCancelItem = function(fileItem, response, status, headers) {
        //console.info('onCancelItem', fileItem, response, status, headers);
    };
    uploader.onCompleteItem = function(fileItem, response) {
        for(var i in response){
            $scope.slides.push({'image':response[i]});
        }
    };
    uploader.onCompleteAll = function() {
        //console.info('onCompleteAll');
    };

    $scope.updateImages = function (){
        $scope.submitting = true;
        Job.update({id:$stateParams.id}, {"pictures":$scope.slides},function(){
            $scope.error = '已更新';
            $scope.submitting = false;
        },function(response){
            $scope.error = '更新失败';
            $scope.submitting = false;
        });
    };

    $scope.removeSlides = function (i){
        $scope.slides.splice(i, 1);
    }
}]);