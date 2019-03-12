/**
 * 人选意向操作信息提示
 */
app.controller('ModalIntentionController', [
    '$scope',
    '$modalInstance',
    '$rootScope',
    '$modal',
    'msg',
    'selectCustomerId',
    'CandidateTask',
    function($scope, $modalInstance, $rootScope, $modal, msg, selectCustomerId, CandidateTask) {
        var vm = this;
        vm.msg = msg;
        vm.ok = function () {
            $modalInstance.close('ok');
        };

        //创建联系记录
        vm.createContact = function () {
            var scope = $rootScope.$new(true);
            scope.contact_result = 1;
            $modal.open({
                templateUrl: 'tpl/modal_call.html',
                controller: 'ModalCallController',
                size: 'lg',
                scope: scope,
                resolve: {
                    contactId : function () {
                      return selectCustomerId;
                    }
                }
            });
            $modalInstance.close('createContact');
        };

        //创建拨打电话
        vm.createPlan = function () {
            CandidateTask.open(10, selectCustomerId);
            $modalInstance.close('createPlan');
        }
    }
]);