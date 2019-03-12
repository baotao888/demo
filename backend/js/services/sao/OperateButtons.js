'use strict';
/**
 * 获取按钮操作权限
 */
angular.module('app').factory('OperateButtons', function ($http) {
    return $http.get('/api/index/index/operatebtn');
});
