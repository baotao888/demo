'use strict';

angular.module('app').factory('Role', function TokenFactory($resource) {
    return $resource('/api/Role/:id', null, {
        update: {
            method: 'PUT'
        }
    });
});
