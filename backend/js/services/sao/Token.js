'use strict';

angular.module('app').factory('Token', function TokenFactory($resource) {
    return $resource('/api/token/:id');
});
