'use strict';

/* Controllers */
// job list controller
app.controller('JobListController', function (Job) {
    var vm = this;
    vm.jobs = {};
    vm.showStopRecruit = function (job) {
        return job.status == 99;
    }
    vm.showRecruit = function (job) {
        return job.status == 1;
    }
    vm.stopRecruit = function (id) {
        //更新基本信息
        Job.update({id: id}, {status: 99},function(){
            vm.init();
        });
    }
    vm.sendRecruit = function (id) {
        //更新基本信息
        Job.update({id: id}, {status: 1},function(){
            vm.init();
        });
    }
    vm.init = function () {
        Job.get(function(response){
            vm.jobs = response;
        });
    }
    vm.init();
})
;