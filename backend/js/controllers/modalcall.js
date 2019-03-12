app.controller('ModalCallController', [
    '$scope',
    '$http',
    '$stateParams',
    '$state',
    '$modalInstance',
    'toaster',
    'contactId',
    function($scope, $http, $stateParams, $state, $modalInstance, toaster, contactId) {
        /*获取用户信息*/
        $scope.loadCustomerInfo = function () {
            $http({
                method: 'get',
                url: '/api/customer/pool/detail',
                params: {
                    'id' : contactId
                }
            }).success(function (response) {
                $scope.customer = response;
            });
        }

        $scope.toaster = {
            result: angular.isDefined($scope.$parent.contact_result)?$scope.$parent.contact_result:0,
            content: ''
        };
        $scope.results = [];
        $scope.toasterConfig = {
            type : 'success',
            title : '提示信息',
            timeout : 10000,
            bodyOutputType : ''
        };
        /**
         * 保存联系记录
         */
        $scope.pop = function () {
            $http({
                method: 'post',
                url: '/api/customer/contact/save',
                data: {cp_id: contactId, content: $scope.toaster.content, result: $scope.toaster.result}
            }).success(function (req) {
                $scope.toaster.content = '';
                toaster.pop(
                    $scope.toasterConfig.type,
                    $scope.toasterConfig.title,
                    '创建成功',
                    $scope.toasterConfig.timeout,
                    $scope.toasterConfig.bodyOutputType
                );
            }).error(function () {
                toaster.pop('error', $scope.toasterConfig.title, '创建失败');
            });
        };

        $scope.ok = function () {
            $modalInstance.close('ok');
        };
        $scope.contactList = function () {
            $modalInstance.close('contactList');
            $state.go('app.candidate.contact');
        }

        $scope.editCustomer = function (id) {
            $state.go('app.customer.form', {id: id});
            $modalInstance.close('editCustomer');
        }

        /**
         * 设置联系内容
         */
        $scope.setContent = function (content) {
            $scope.toaster.content = content.html;
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

        /*加载*/
        $scope.init = function () {
            $scope.loadCustomerInfo();
            $scope.loadContactSetting();
        }
        $scope.init();
    }
]);