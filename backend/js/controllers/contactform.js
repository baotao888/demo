app.controller('ContactFormController', ['$scope', '$http', '$stateParams', '$state', 'toaster', 'Customer', function($scope, $http, $stateParams, $state, toaster, Customer) {
    /*获取用户信息*/
    $scope.loadUserInfo = function () {
        Customer.detail($stateParams.id).then(function (response) {
            $scope.customer = response.data;
        });
    }

    $scope.toaster = {
        result: 0,
        content: ''
    };
    $scope.cp_status = null;
    $scope.results = [];//联系结果
    $scope.contents = [];//联系内容
    $scope.toasterConfig = {
        type : 'success',
        title : '提示信息',
        timeout : 10000,
        bodyOutputType : '',
        clickHandler : function(){
            //$state.go('app.candidate.contact');//跳转到候选人列表页
            $scope.Crecord();
            $scope.toaster.content = '';
        }
    };

    /**
     * 保存联系记录
     */
    $scope.pop = function(){
        $http({
            method:'post',
            url:'/api/customer/contact/save',
            data:{cp_id:$stateParams.id, content:$scope.toaster.content, result:$scope.toaster.result, cp_status:$scope.cp_status}
        }).success(function(req){
            toaster.pop($scope.toasterConfig.type,$scope.toasterConfig.title,'创建成功', $scope.toasterConfig.timeout, $scope.toasterConfig.bodyOutputType, $scope.toasterConfig.clickHandler);
        }).error(function(){
           toaster.pop('error', $scope.toasterConfig.title, '创建失败');
        });
    };

    /**
     * 设置联系内容
     */
    $scope.setContent = function (content) {
        $scope.toaster.content = content.html;
        $scope.cp_status = content.value;
    }

    $scope.loadContactSetting = function () {
        $http({
            method: 'get',
            url: '/api/customer/contact/getsetting'
        }).success(function (response) {
            $scope.results = response.result;
            $scope.contents = response.content;
        });
    }

    /*联系记录*/
    $scope.Crecord = function(){
        Customer.contactLog($stateParams.id).then(function(response){
            $scope.contactrecord = response.data;
        })
    };

    $scope.init = function () {
        $scope.loadUserInfo();
        $scope.loadContactSetting();
        $scope.Crecord();//加载过往联系记录
    }
    $scope.init();
}]);