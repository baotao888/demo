'use strict';

/* Controllers */
// poster list controller
app.controller('PosterListController', function ($http, $stateParams) {
    var vm = this;
    vm.list = {};
    $http.get('/api/poster/index', {params: {space: $stateParams.space}}).success(function (response) {
        vm.list = response;
    });
})
;