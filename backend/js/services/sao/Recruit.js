'use strict';

angular.module('app').factory('Recruit', function TokenFactory($resource) {
    return $resource('/api/recruit/:id', null, {
        update: {
            method: 'PUT'
        }
    });
});
