/**
 * 确认框
 */
angular.module('app').factory('ConfirmWindow', ['$q', '$modal',
    function ($q, $modal) {
        return {
            open: function(msg) {
                var deferred = $q.defer();
                var confirmModal = $modal.open({
                    templateUrl: 'tpl/modal_sure.html',
                    controller: 'ModalshowsureController',
                    resolve: {
                        msg: function () {
                            return msg;
                        }
                    }
                });
                // 处理modal关闭后返回的数据
                confirmModal.result.then(function() {
                    deferred.resolve();
                },function(){
                });
                return deferred.promise;
            }
        }
    }]
);
