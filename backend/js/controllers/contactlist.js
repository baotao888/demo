'use strict';

/* Controllers */
//今日联系记录
app.controller('CandidateContactController', function($http, OperateButtons) {
     var vm = this;
    /*按钮的权限*/
    vm.myButtons = {};
    vm.loadOperateButtons = function(){
        OperateButtons.success(function(response){
            vm.myButtons = response;
        });
    };
    vm.contactPiechart = function(){
       $http({
            method: 'get',
            url: 'api/customer/contact/today'
       }).success(function(response){
           vm.contactdetail = response;
           vm.contactList = vm.contactdetail.list;
           vm.contactPercent = [vm.contactdetail.success, vm.contactdetail.total-vm.contactdetail.success];//意向客户百分比
           vm.customerPercent = [vm.contactdetail.intention, vm.contactdetail.effective-vm.contactdetail.intention];//意向客户百分比
       })
    };
    vm.init = function(){
        vm.loadOperateButtons();
        vm.contactPiechart();
    };
    vm.init();

});

