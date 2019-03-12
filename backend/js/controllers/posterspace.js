'use strict';

/* Controllers */
// poster space list controller
app.controller('PosterSpaceController', function ($http) {
    var vm = this;
    vm.list = {};
    $http.get('/api/poster/index/space').success(function (response) {
        vm.list = response;
    });
})
;