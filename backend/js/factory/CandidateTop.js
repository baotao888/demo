/**
 * 人选置顶
 */
angular.module('app').factory('CandidateTop', [
    '$q',
    '$http',
    'MessageWindow',
    'Errors',
    function ($q, $http, MessageWindow, Errors) {
        return {
            /*置顶人选*/
            top: function (candidate) {
                var deferred = $q.defer(),
                    promise = deferred.promise;
                $http({
                    method:'post',
                    url:'/api/customer/candidate/top',
                    data:{'id':candidate}
                }).success(function(req){
                    if (req){
                        MessageWindow.open(Errors.common_success);
                        deferred.resolve(true);//返回成功
                    } else {
                        MessageWindow.open(Errors.common_error);
                        deferred.resolve(false);
                    }
                }).error(function(req){
                    MessageWindow.open(Errors.sys_error);
                    deferred.resolve(false);
                });
                return promise;
            },
            /*取消置顶人选*/
            cancel: function (candidate) {
                var deferred = $q.defer(),
                    promise = deferred.promise;
                $http({
                    method:'post',
                    url:'/api/customer/candidate/canceltop',
                    data:{'id':candidate}
                }).success(function(req){
                    MessageWindow.open(Errors.common_success);
                    deferred.resolve(true);
                }).error(function(req){
                    MessageWindow.open(Errors.sys_error);
                    deferred.resolve(false);
                });
                return promise;
            }
        }
    }]
);
