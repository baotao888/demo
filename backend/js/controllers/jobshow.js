app.controller('JobShowController', function($scope, $stateParams, Job, $state) {
    var vm = this;
    Job.get({id: $stateParams.id},function(response){
        vm.job = response;
    });
    vm.addTag = function () {
        $state.go('app.job.tag', {id: $stateParams.id});
    }
    vm.addCover = function () {
        $state.go('app.job.cover', {id: $stateParams.id});
    }
    vm.uploadImages = function () {
        $state.go('app.job.images', {id: $stateParams.id});
    }
    vm.editInfo = function () {
        $state.go('app.job.form', {id: $stateParams.id});
    }
    vm.editEnterprise = function () {
        $state.go('app.job.enterpriseform', {id: vm.job.enterprise_id});
    }
});