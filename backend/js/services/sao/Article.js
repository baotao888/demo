'use strict';

angular.module('app').factory('Article', function TokenFactory($resource) {
    return $resource('/api/Article/:id', null, {
        update: {
            method: 'PUT'
        }
    });
});
