'use strict';

angular.module('app').factory('Admin', function TokenFactory($resource) {
    return $resource('/api/Admin/:id', null, {
        update: {
            method: 'PUT'
        }
    });
});
