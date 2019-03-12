'use strict';

angular.module('app').factory('Job', function TokenFactory($resource) {
    return $resource('/api/Job/:id', null, {
        update: {
            method: 'PUT'
        }
    });
});
