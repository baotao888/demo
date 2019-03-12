/**
 * 职位选择框
 */
angular.module('app').factory('JobChoice', [
    '$modal',
    '$http',
    function ($modal, $http) {
        return {
            /**
             * 打开弹出框
             * @param operate 操作类型
             * @param candidates 人选列表
             */
            open: function (operate, candidates) {
                $modal.open({
                    templateUrl: 'tpl/modal_job.html',
                    controller: 'ModalJobController',
                    size: 'lg',
                    resolve: {
                        operate: function () {
                            return operate;
                        },
                        selectedCustomers : function() {
                            return candidates;
                        }
                    }
                });
            },
            //获取所有职位
            all: function () {
                return $http.get('/api/index/index/allJob');
            }
        }
    }]
);
