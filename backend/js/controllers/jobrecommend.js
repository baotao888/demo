'use strict';

/* Controllers */
// job recommend list controller
app.controller('JobRecommendController', function ($http) {
    var vm = this;
    vm.list = {};
    $http.get('/api/job/recommend/space').success(function (response) {
        vm.list = response;
    });
})
;