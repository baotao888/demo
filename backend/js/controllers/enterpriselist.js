'use strict';

/* Controllers */
// enterprise list controller
app.controller('EnterpriseListController', function(Enterprise) {
    var vm = this;
    vm.enterprises = {};
    Enterprise.list().then(function (response) {
        vm.enterprises = response.data;
    });
})
;