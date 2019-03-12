'use strict';
/**
 * 获取业务部门及其下顾问
 */
angular.module('app').factory('AdviserOrganizations', function ($http) {
    return $http.get('/api/index/index/adviserOrganization');
});
