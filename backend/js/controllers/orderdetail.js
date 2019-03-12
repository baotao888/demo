'use strict';

/* 订单详情 */
app.controller('OrderDetailController', function($http, $filter, $stateParams, $modalInstance, id) {
  var vm = this;

  vm.loadorder = function() {
    $http({method: 'get', url: '/api/salesorder/'+id, params: {'id': id}}).success(function(res){
      vm.orderlist = res;
      vm.ordercustomer = res.customer;
      vm.ordersale = res.sales;
      vm.orderstatus = res.status;
      if (vm.ordersale.type==1) {
        vm.ordertype = '正式工';
      } else if (vm.ordersale.type==2) {
        vm.ordertype = '小时工';
      } else if (vm.ordersale.type==3) {
        vm.ordertype = '其他';
      }

      if (vm.orderstatus.paid_invite_way==0) {
        vm.ordertext = '未领取';
      } else if (vm.orderstatus.paid_invite_way==1) {
        vm.ordertext = '现金';
      } else if (vm.orderstatus.paid_invite_way==2) {
        vm.ordertext = '转账';
      }

     if (vm.orderstatus.paid_allowance_way==0) {
        vm.ordertextallow = '未领取';
      } else if (vm.orderstatus.paid_allowance_way==1) {
        vm.ordertextallow = '现金';
      } else if (vm.orderstatus.paid_allowance_way==2) {
        vm.ordertextallow = '转账';
      }

      if (vm.orderstatus.paid_allowance_way==0) {
        vm.ordertextallow = '未领取';
      } else if (vm.orderstatus.paid_allowance_way==1) {
        vm.ordertextallow = '现金';
      } else if (vm.orderstatus.paid_allowance_way==2) {
        vm.ordertextallow = '转账';
      }

    })
  }

  vm.ok = function() {
    $modalInstance.close();
  }

  vm.loadorder();
});