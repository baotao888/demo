'use strict';

app.controller('RecruitListController', function($http, $filter, MessageWindow, ConfirmWindow, Recruit) {
  var vm = this;
  vm.headers = ['地区', '企业', '职位类型', '工资说明', '补贴', '有效期', '操作'];
  vm.opens = [false, false];
  vm.date = $filter('date')(new Date(), 'yyyy-MM-dd');

  vm.open = function($event, a) {
    vm.opens[a] = true;
    $event.preventDefault();
    $event.stopPropagation();
  };

  vm.init = function () {
    vm.showLoading = true;
    if (vm.date==undefined) {
      var url = '/api/recruit';
    } else {
      vm.date = $filter('date')(vm.date, 'yyyy-MM-dd');
      url = '/api/recruit?date='+vm.date;
    }
    var  promise = $http({method: 'get', url: url}).success(function(res){
      vm.recruitlist = res;
    });
    promise.then(function(){
     vm.showLoading = false;
    },function(){
     vm.showLoading = false;
    });
   };

   vm.init();

  vm.delete = function (id) {
      ConfirmWindow.open('确定要删除此职位吗?').then(function () {
          if (id <= 0) {
              MessageWindow.open('职位无效');
              return;
          }
          Recruit.delete({id: id},function(response){
              MessageWindow.open('删除成功');
              vm.init();
          });
      });
  };

   vm.searchvalidity = function() {
     vm.init();
   }
});