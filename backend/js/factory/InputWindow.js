/**
 * 输入框
 */
angular.module('app').factory('InputWindow', ['$modal',
    function ($modal) {
        return {
            /**
             * 打开弹出框
             * @param operate 操作类型
             * @param candidates 人选列表
             */
            open: function(operate, candidates) {
                var selected = null;
                if (operate == 3) {
                    /*接站*/
                    selected = candidates[0];
                } else if (operate == 4) {
                    /*入职*/
                    selected = candidates[0];
                } else {
                    selected = candidates;
                }
                $modal.open({
                    templateUrl: 'tpl/modal_input.html',
                    controller: 'ModalInputController',
                    resolve: {
                        operate: function () {
                            return operate;
                        },
                        selectedCustomer: function() {
                            return selected;
                        }
                    }
                });
            }
        }
    }]
);
