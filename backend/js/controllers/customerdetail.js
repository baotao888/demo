'use strict';

/* 客户详情 */
app.controller('CustomerDetailController', function($http, $stateParams, MessageWindow, $state, Errors, Customer) {
    var vm = this;
    vm.loadCustomer = function (){
        Customer.detail($stateParams.id).then(function (response) {
            vm.customer = response.data;
            vm.customer_data = vm.customer.detail;
            vm.customer_status = vm.customer.poolStatus;
        });
    };
    /*分配记录*/
    vm.Candidatelist = function(){
        Customer.assignHistory($stateParams.id).then(function (response) {
            vm.assign = response.data;
        });
    };
    /*工作经验*/
    vm.workhistory = function(){
        Customer.workHistory($stateParams.id).then(function(response){
            vm.workhistorylist  = response.data;
        })
    };
    /*联系记录*/
    vm.Crecord = function(){
        Customer.contactLog($stateParams.id).then(function(response){
            vm.contactrecord = response.data;
        })
    };
    /*操作记录*/
    vm.Orecord = function(){
        Customer.log($stateParams.id).then(function(response){
            vm.detaildata = response.data;
        })
    };
   /*认领客户*/
   vm.recognize = function(id){
       if (vm.customer_status.is_open){
           Customer.recognize([id]).then(function (data) {
               if (data) {
                   $state.go('app.candidate.list');
               }
           });
       } else {
           MessageWindow.open('已被认领');
       }
   }
   vm.init = function (){
      vm.workhistory();
      vm.Candidatelist();
      vm.loadCustomer();
      vm.Orecord();
      vm.Crecord();
   };
   vm.init();
});