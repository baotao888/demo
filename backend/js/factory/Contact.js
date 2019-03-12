/**
 * 联系记录
 */
angular.module('app').factory('Contact', ['$modal',
    function ($modal) {
        return {
            /**
             * 打开新建弹出框
             * @param candidates 人选
             */
            open: function(candidate) {
                $modal.open({
                    templateUrl: 'tpl/modal_call.html',
                    controller: 'ModalCallController',
                    size: 'lg',
                    resolve: {
                        contactId : function() {
                            return candidate;
                        }
                    }
                });
            }
        }
    }]
);
