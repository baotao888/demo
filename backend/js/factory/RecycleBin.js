/**
 * 回收站
 */
angular.module('app').factory('RecycleBin', [
    '$http',
    function ($http) {
        return {
            /*查询客户*/
            searchCustomer: function (page, pagesize, search) {
                return $http({
                    method: 'get',
                    url: '/api/recycle/index/customerPool',
                    params: {page: page, pagesize: pagesize, search: search}
                });
            },
            /*还原客户*/
            restore: function (arr_id) {
                return $http({method: 'post', url: '/api/recycle/index/restore', data: {'customers/a': arr_id}});
            },
            /*删除客户*/
            delete: function (arr_id) {
                return $http({method: 'post', url: '/api/recycle/index/delete', data: {'customers/a': arr_id}});
            }
        }
    }]
);
