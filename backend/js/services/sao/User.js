'use strict';

angular.module('app').factory('User', function UserFactory($resource) {
    return $resource('/api/user/:id');
});
