/**
 * 人选保留
 */
angular.module('app').factory('CandidateRemain', [
    '$q',
    '$http',
    'MessageWindow',
    'Errors',
    function ($q, $http, MessageWindow, Errors) {
        return {
            /*保留人选*/
            remain: function (candidate) {
                var deferred = $q.defer(),
                    promise = deferred.promise;
                $http({
                    method:'post',
                    url:'/api/customer/candidate/remain',
                    data:{'id':candidate}
                }).success(function(req){
                    if (req){
                        MessageWindow.open(Errors.common_success);
                        deferred.resolve(true);//返回成功
                    } else {
                        MessageWindow.open(Errors.remain_limit_error_1);
                        deferred.resolve(false);
                    }
                }).error(function(req){
                    MessageWindow.open(Errors.sys_error);
                    deferred.resolve(false);
                });
                return promise;
            },
            /*取消保留人选*/
            cancel: function (candidate) {
                var deferred = $q.defer(),
                    promise = deferred.promise;
                $http({
                    method:'post',
                    url:'/api/customer/candidate/cancelremain',
                    data:{'id':candidate}
                }).success(function(req){
                    MessageWindow.open(Errors.common_success);
                    deferred.resolve(true);
                }).error(function(req){
                    MessageWindow.open(Errors.sys_error);
                    deferred.resolve(false);
                });
                return promise;
            },
            /*一键保留即将释放人选*/
            all: function () {
                var deferred = $q.defer(),
                    promise = deferred.promise;
                $http({
                    method:'post',
                    url:'/api/customer/candidate/remainAll'
                }).success(function(req){
                    if (req){
                        MessageWindow.open(Errors.common_success);
                        deferred.resolve(true);
                    } else {
                        MessageWindow.open(Errors.remain_limit_error_1);
                        deferred.resolve(false);
                    }
                }).error(function(req){
                    MessageWindow.open(Errors.sys_error);
                    deferred.resolve(false);
                });
                return promise;
            }
        }
    }]
);
