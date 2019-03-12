'use strict';

/* 客户详情弹出框 */
app.controller('ModalCustomerController', [
    '$scope',
    '$modalInstance',
    '$http',
    'Customer',
    'customerId',
    function ($scope, $modalInstance, $http, Customer, customerId) {
        var vm = this;
        vm.loadCustomer = function (){
            Customer.detail(customerId).then(function (response) {
                vm.customer = response.data;
                vm.customer_data = vm.customer.detail;
                vm.customer_status = vm.customer.poolStatus;
            });
        };
        /*分配记录*/
        vm.Candidatelist = function(){
            Customer.assignHistory(customerId).then(function (response) {
                vm.assign = response.data;
            });
        };
        /*工作经验*/
        vm.workhistory = function(){
            Customer.workHistory(customerId).then(function(response){
                vm.workhistorylist  = response.data;
            })
        };
        /*联系记录*/
        vm.Crecord = function(){
            Customer.contactLog(customerId).then(function(response){
                vm.contactrecord = response.data;
            })
        };

        vm.init = function (){
            vm.workhistory();
            vm.Candidatelist();
            vm.loadCustomer();
            vm.Crecord();
        };
        vm.init();

        $scope.ok = function () {
            $modalInstance.close('ok');
        };
    }
]);