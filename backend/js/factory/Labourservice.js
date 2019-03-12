/**
 * 劳务公司
 */
angular.module('app').factory('Labourservice', function($http){
    return $http.get('/api/labourservice');
});




