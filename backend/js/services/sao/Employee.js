'use strict';

angular.module('app').factory('Employee', function TokenFactory($resource) {
    return $resource('/api/Employee/:id', null, {
        update: {
            method: 'PUT'
        }
    });
});
