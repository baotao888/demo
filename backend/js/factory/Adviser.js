/**
 * 顾问
 */
angular.module('app').factory('Adviser', [
    '$q',
    '$http',
    '$modal',
    'MessageWindow',
    'Errors',
    function ($q, $http, $modal, MessageWindow, Errors) {
        return {
            /*打开选择弹出框*/
            open: function (advisers, operate) {
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
            /*获取员工*/
            list: function () {
                return $http.get('/api/index/index/groupEmployee');
            },
            /*分配呼入注册用户*/
            distributeUser: function (adviser, users) {
                return $http({method:'post', url:'/api/user/callin/assignUser', data: {adviser: adviser, 'users/a': users}});
            },
            /*分配呼入报名用户*/
            distributeSignup: function (adviser, signups) {
                return $http({method:'post', url:'/api/user/callin/assignApplicant', data: {adviser: adviser, 'users/a': signups}});
            },
            /*分配客户池*/
            distributeCustomer: function (adviser, customers) {
                return $http({method:'post', url:'/api/customer/pool/distribute', data:{adviser: adviser, 'customerpools/a': customers}});
            },
            /*划转人选*/
            moveCandidate: function (adviser, candidates) {
                return $http({method:'post', url:'/api/customer/candidate/move', data:{adviser: adviser, 'candidates/a': candidates}});
            },
        }
    }]
);
