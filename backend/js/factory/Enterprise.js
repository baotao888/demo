/**
 * 企业
 */
angular.module('app').factory('Enterprise', ['$http',
    function ($http) {
        return {
            //企业列表
            list: function() {
                return $http.get('/api/job/enterprise/index');
            }
        }
    }]
);
