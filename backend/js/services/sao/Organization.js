'use strict';

angular.module('app').factory('Organization', function TokenFactory($resource) {
    return $resource('/api/Organization/:id', null, {
        update: {
            method: 'PUT'
        }
    });
});
