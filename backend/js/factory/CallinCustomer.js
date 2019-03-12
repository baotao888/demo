/**
 * 呼入人选
 */
angular.module('app').factory('CallinCustomer', [
    '$http',
    '$modal',
    'API',
    function ($http, $modal, API) {
        return {
            /*我的呼入注册人选*/
            myUser: function (page, pageSize, keyword, from, sure) {
                return $http({method: 'get', url: API.callinUser(), params: {page: page, pagesize: pageSize, search: keyword, from: from, sure: sure}});
            },
            /*我的呼入报名人选*/
            myApplicant: function (page, pageSize, keyword, from, sure) {
                return $http({method: 'get', url: API.callinApplication(), params: {page: page, pagesize: pageSize, search: keyword, from: from, sure: sure}});
            },
            /*端口注册人选*/
            users: function (page, pageSize, keyword, reg_time_start, reg_time_end, is_assign) {
                return $http({method: 'get', url: API.allCallinUsers(), params: {page: page, pagesize: pageSize, keyword: keyword, reg_time_start: reg_time_start, reg_time_end: reg_time_end, is_assign: is_assign}});
            },
            /*端口报名人选*/
            applicants: function (page, pageSize, keyword, time_start, time_end, is_assign) {
                return $http({method: 'get', url: API.allCallinApplicants(), params: {page: page, pagesize: pageSize, keyword: keyword, time_start: time_start, time_end: time_end, is_assign: is_assign}});
            },
            /*确认呼入人选*/
            sure: function (arr_uid, operate, type, from) {
                if (operate == 3 || operate == 2) {
                    return $http({method: 'post', url: '/api/user/callin/confirm', data: {'users/a': arr_uid, type: type, from: from}});
                }
            },
            /*分配呼入人选*/
            assign: function (advisers, operate) {
                var modalEmployee = $modal.open({
                    templateUrl: 'tpl/modal_employee.html',
                    controller: 'ModalEmployeeController',
                    resolve: {
                        operate: function () {
                            return operate;
                        },
                        selectedUsers : function() {
                            return advisers;
                        }
                    }
                });
                return modalEmployee.result;
            },
            /*未确认人选数目*/
            unSureCount: function () {
                return $http({method: 'get', url: API.callinUnsure()});
            }
        }
    }]
);
