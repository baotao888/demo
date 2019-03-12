app.controller('ArticleFormController', function($scope, $state, Article, Errors, $stateParams, $modal) {
    var vm = this;
    /*初始化*/
    vm.error = '';
    vm.submitting = false;
    vm.operate = "新增";
    vm.thumbclass = "btn-danger";
    vm.edit = false;
    vm.article = {"title":"", "content":""};
    $scope.config = {
        //focus时自动清空初始化时的内容
        autoClearinitialContent: true,
        //关闭字数统计
        wordCount: false,
        //关闭elementPath
        elementPathEnabled: false,
        initialFrameHeight: 520
    };
    /*新增*/
    vm.submitInfo = function(){
        vm.error = '';
        vm.submitting = true;
        Article.save({
            title: vm.article.title,
            content: vm.article.content
        },function(response) {
            vm.open('success');
            vm.submitting = false;
            vm.error = '保存成功';
        }, function() {
            vm.error = Errors.params;
            vm.submitting = false;
        });
    }

    /*获取文章内容，更新文章信息*/
    if ($stateParams.id != null && $stateParams.id != ''){
        Article.get({id: $stateParams.id},function(response){
            vm.article = response;
            if (vm.article.thumb != '' && vm.article.thumb != null ) vm.thumbclass = 'btn-info';
        });
        vm.operate = "编辑";
        vm.edit = true;
        vm.submitInfo = function () {
            //更新基本信息
            vm.submitting = true;
            //console.log(vm.article.content);
            Article.update({id:$stateParams.id}, {
                title: vm.article.title,
                content: vm.article.content
            },function(){
                vm.open('success');
                vm.error = '已更新';
                vm.submitting = false;
            },function(){
                vm.open('failure');
                vm.error = '更新失败';
                vm.submitting = false;
            });
        }
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
