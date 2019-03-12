/**
 * 拨打计划
 */
angular.module('app').factory('CandidateTask', ['$modal',
    function ($modal) {
        return {
            /**
             * 打开新建弹出框
             * @param operate 操作类型
             * @param candidates 人选列表
             */
            open: function(operate, candidates) {
                $modal.open({
                    templateUrl: 'tpl/modal_input.html',
                    controller: 'ModalInputController',
                    resolve: {
                        operate: function () {
                            return operate;//拨打计划
                        },
                        selectedCustomer : function() {
                            return candidates;
                        }
                    }
                });
            }
        }
    }]
);
