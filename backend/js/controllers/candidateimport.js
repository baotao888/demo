app.controller('CandidateImportController', ['$scope', 'FileUploader', 'authService', '$stateParams', '$http', '$filter', 'AdviserOrganizations', function($scope, FileUploader, authService, $stateParams, $http, $filter, AdviserOrganizations) {
    var uploader = $scope.uploader = new FileUploader({
        url: '/api/index/upload/candidate',
        headers: authService.getToken()
    });
    $scope.error = '';
    $scope.import_title = [];
    $scope.import_content = [];
    $scope.import_result = [];
    $scope.import_total = 0;
    $scope.submitting = false;//是否正在导入
    $scope.can_import = false;//是否可以导入
    /*获取顾问信息*/
    AdviserOrganizations.then(function (resp) {
        $scope.groups = resp.data.orgs;
        $scope.items = resp.data.employees;
        $scope.item = $filter('orderBy')($scope.items, 'nickname')[0];
        $scope.item.selected = true;
    });
    $scope.filter = '';
    /*选择部门*/
    $scope.selectGroup = function(item){
        angular.forEach($scope.groups, function(item) {
            item.selected = false;
        });
        $scope.group = item;
        $scope.group.selected = true;
        $scope.filter = item.name;
    };
    /*选择顾问*/
    $scope.selectItem = function(item){
        angular.forEach($scope.items, function(item) {
            item.selected = false;
            item.editing = false;
        });
        $scope.item = item;
        $scope.item.selected = true;
    };
    // FILTERS
    uploader.filters.push({
        name: 'customFilter',
        fn: function(item /*{File|FileLikeObject}*/, options) {
            return this.queue.length <= 1;
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
        $scope.error = '文件有误，请联系开发人员，辨别文件是否可识别';
    };
    uploader.onCancelItem = function(fileItem, response, status, headers) {
        //console.info('onCancelItem', fileItem, response, status, headers);
    };
    /*上传完成*/
    uploader.onCompleteItem = function(fileItem, response) {
        $scope.import_title = [];
        $scope.import_result = [];
        $scope.import_content = [];
        $scope.can_import = true;
        angular.forEach(response.import_title, function(value, key){
            var flag = false,
                th = value;
            if (key==0){
                if (value=='姓名'){
                    flag = true;
                } else {
                    th += "【姓名】";
                }
            } else if (key==1){
                if (value=='性别'){
                    flag = true;
                } else {
                    th += "【性别】";
                }
            } else if (key==2){
                if (value=='年龄'){
                    flag = true;
                } else {
                    th += "【年龄】";
                }
            } else if (key==3){
                if (value=='学历'){
                    flag = true;
                } else {
                    th += "【学历】";
                }
            } else if (key==4){
                if (value=='联系电话'){
                    flag = true;
                } else {
                    th += "【联系电话】";
                }
            } else if (key==5){
                if (value=='户籍'){
                    flag = true;
                } else {
                    th += "【户籍】";
                }
            } else if (key==6){
                if (value=='状态'){
                    flag = true;
                } else {
                    th += "【状态】";
                }
            } else if (key==7){
                if (value=='标签'){
                    flag = true;
                } else {
                    th += "【标签】";
                }
            }
            $scope.import_title.push({'th':th, 'flag':flag});
            if (flag == false) $scope.can_import = false;
        });
        angular.forEach(response.import_content, function(value, key){
            $scope.import_result.push('text-primary');
            $scope.import_content.push(value);
        });
        $scope.import_total = response.import_total;
        $scope.error = '上传成功，确认无误之后，点击下方【确认】按钮，完成导入操作';
    };
    uploader.onCompleteAll = function() {
        //console.info('onCompleteAll');
    };
    $scope.pageOption = {'current':1};
    $scope.importData = function (){
        $scope.submitting = true;
        /*导入数据*/
        $http({
            method:'post',
            url:'/api/index/import/candidate',
            data:{page:$scope.pageOption.current, adviser:$scope.item.id}
        }).success(function(req){
            //console.log(req);
            if (req.finished){
                var info = "导入完毕。";
                if (req.success_total > 0) info += "导入成功的记录数：" + req.success_total + "。";
                if (req.failure_total > 0) info += "导入失败的记录数：" + req.failure_total + "。";
                $scope.error = info;
            }else{
                angular.forEach(req.result, function(result, index){
                    $scope.import_result[index] = result?'text-success':'text-danger';
                });
                $scope.error = "已处理记录数：" + req.offset + "/" + req.total + "。正在导入剩余数据中……";
                $scope.pageOption.current ++;
                $scope.importData();
            }
            $scope.submitting = false;
        }).error(function (req) {
            $scope.error = "上传失败，系统繁忙";
        });
    };

    $scope.removeSlides = function (i){
        $scope.slides.splice(i, 1);
    }
}]);