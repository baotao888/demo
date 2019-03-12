'use strict';

angular.module('app').factory('Position', function TokenFactory($resource) {
    return $resource('/api/Position/:id', null, {
        update: {
            method: 'PUT'
        }
    });
});
