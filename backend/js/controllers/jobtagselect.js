'use strict';

app.controller('JobTagAddController', function($scope, $http, $stateParams, Job, JobTag) {
    var vm = this;
    vm.error = '';
    vm.job = {};
    vm.job.id = $stateParams.id;
    vm.job.job_tag = ['认证', '推荐'];
    vm.job.recommend_tag = ['认证', '推荐'];
    vm.job.welfare_tag = ['认证', '推荐'];
    vm.submitting = false;
    vm.tag = JobTag;

    Job.get({id: $stateParams.id}, function (response) {
        vm.job = response;
    });

    vm.updateTag = function () {
        vm.submitting = true;
        Job.update(
            {id: $stateParams.id},
            {"job_tag": vm.job.job_tag, "welfare_tag": vm.job.welfare_tag, "recommend_tag": vm.job.recommend_tag},
            function () {
                vm.error = 'success';
                vm.submitting = false;
            }, function () {
                vm.error = 'failure';
                vm.submitting = false;
            }
        );
    }
});