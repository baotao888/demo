'use strict';

app.controller('RecruitFormController', function($http, $filter, $state, Enterprise, Cities, $stateParams, MessageWindow) {
  var vm = this;
  vm.recruitopton = '新增';
  var recriutModel = {
        amount: 0,
        term: 1,
        allowance: 0,
        onduty_type: 1,
        conditions: {field: '', operator: '', value: ''}
     };
  var detail_id = 0, clone_id = 0;
  vm.enterpriseList = [];
  vm.regions = [];
  vm.labours = [];
  vm.arr = [];
  vm.writeModel = [recriutModel];
  vm.jobtype = [{'id': 1, 'name': '正式工'}, {'id': 2, 'name': '临时工'}, {'id': 3, 'name': '小时工'}];
  vm.rewardtype = [{'id': 1, 'name': '正式型'}, {'id': 2, 'name': '小时型'}, {'id': 3, 'name': '其他'}];
  vm.conditionfield = {field: [{'value':'gender', 'name': '性别'}, {'value':'age', 'name': '年龄'}], sex: [{'vid': 0, 'name': '女'}, {'vid': 1, 'name': '男'}], operator: [{'oid': -1, 'reship': '小于'}, {'oid': 0, 'reship': '等于'}, {'oid': 1, 'reship': '大于'}], onduty: [{'onid': 0, 'name': '在职'}, {'onid': 1, 'name': '在职打卡'}]};
  vm.hourJob = {'company_hour_salary': '', 'candidate_hour_salary': ''};
  vm.opens = [false, false];
  vm.recruit = {enterprise: { enterprise_name: '', enterprise_id: ''}, labourlist: {id: 0, name: '请选择劳务公司...'}, type: 0, region: '', salary_intro: '', validity_period: '', list_order: 0, ent_wage: 0,  cp_wage: 0, allowance_type: 0, allowance: vm.writeModel};
  vm.recruit.allowance_type = 1;
  vm.recruit.type = 1;
  vm.recruit.validity_period = $filter('date')(new Date(), 'yyyy-MM-dd');
  if ($stateParams.id != null && $stateParams.id !='') {
      if ($stateParams.id.indexOf('-') > 0) {
          var arr_params = $stateParams.id.split('-');
          detail_id = arr_params[0];
          clone_id = arr_params[1];
      } else if (! isNaN($stateParams.id)) {
        detail_id = $stateParams.id;
      }
  }

  /*城市*/
  for( var i=0; i<Cities.length; i++) {
    vm.regions.push(Cities[i].name);
  }
  vm.open = function($event, a) {
      vm.opens[a] = true;
      $event.preventDefault();
      $event.stopPropagation();
  };

  vm.addIf = function() {
     var recriutModel = {
        amount: 0,
        term: 1,
        allowance: 0,
        onduty_type: 1,
        conditions: {field: '', operator: '', value: ''}
     };
      vm.writeModel.push(recriutModel);
  }

  vm.closeIf = function(index) {
    if(vm.writeModel.length==1) {
      return;
    }
    vm.writeModel.splice(index, 1);
  }

 /*新增*/
 vm.recruitform = function() {
    vm.recruit.validity_period = $filter('date')(vm.recruit.validity_period, 'yyyy-MM-dd');
    var data = {
      enterprise_id: vm.recruit.enterprise.enterprise_id,
      enterprise_name: vm.recruit.enterprise.enterprise_name,
      labour: vm.recruit.labourlist.id,
      type: vm.recruit.type,
      region: vm.recruit.region,
      salary_intro: vm.recruit.salary_intro,
      validity_period: vm.recruit.validity_period,
      list_order: vm.recruit.list_order,
      allowance_type: vm.recruit.allowance_type,
      ent_wage: vm.recruit.ent_wage,
      cp_wage: vm.recruit.cp_wage,
      allowance : vm.writeModel
    }
    if(vm.compare(vm.writeModel)==1) {
         return;
    }
    $http({method: 'post', url: '/api/recruit', data: data}).success(function(req){
      $state.go('app.recruit.list');
     })
 };

  /*更新*/
  vm.getLabourService = function() {
    $http({method: 'get', url: '/api/labourservice'}).success(function(resposne){
      vm.labours = resposne;
      for(var i=0;i<resposne.length;i++) {
        if(vm.recruit.labourlist.id == resposne[i].id) {
          vm.recruit.labourlist.name = resposne[i].name;
        }
      }
    });
  }

  vm.loadrecruit = function() {
    vm.writeModel = [];
    return $http({method: 'get', url: '/api/recruit/' + detail_id}).success(function(resposne) {
       vm.recruit.enterprise.enterprise_id = resposne.enterprise_id;
       vm.recruit.labourlist.id = resposne.labour;
       vm.recruit.type = resposne.type;
       vm.recruit.region = resposne.region;
       vm.recruit.salary_intro = resposne.salary_intro;
       vm.recruit.validity_period = resposne.validity_period;
       vm.recruit.list_order = resposne.list_order;
       vm.recruit.allowance_type = resposne.allowance.type;
       vm.recruit.ent_wage = resposne.allowance.ent_wage;
       vm.recruit.cp_wage = resposne.allowance.cp_wage;
       vm.getLabourService();
       for(var i=0; i < resposne.allowance.conditions.length; i++) {
         var recriutModel = {
          id: resposne.allowance.conditions[i].id,
          amount: resposne.allowance.conditions[i].amount,//企业返费
          term: resposne.allowance.conditions[i].term,
          allowance: resposne.allowance.conditions[i].allowance,//补贴金额
          onduty_type: resposne.allowance.conditions[i].onduty_type,
          conditions: {field: resposne.allowance.conditions[i].field, operator: resposne.allowance.conditions[i].operator, value: resposne.allowance.conditions[i].value}
        };
        vm.writeModel.push(recriutModel);
       }
      });
  }

  if (detail_id > 0 && clone_id == 0) {
    vm.recruitopton = '更新';
    vm.recruitform = function() {
      var data = {
        enterprise_id: vm.recruit.enterprise.enterprise_id,
        enterprise_name: vm.recruit.enterprise.enterprise_name,
        labour: vm.recruit.labourlist.id,
        type: vm.recruit.type,
        region: vm.recruit.region,
        salary_intro: vm.recruit.salary_intro,
        validity_period: vm.recruit.validity_period,
        list_order: vm.recruit.list_order,
        allowance_type: vm.recruit.allowance_type,
        ent_wage: vm.recruit.ent_wage,
        cp_wage: vm.recruit.cp_wage,
        allowance : vm.writeModel
      };
      if(vm.compare(vm.writeModel)==1) {
        return;
      }
      $http.put('/api/recruit/' + detail_id, data).success(function(resposne) {
        $state.go('app.recruit.list');
      });
    }
  }

  vm.compare = function(model) {
   for(var i=0; i<vm.writeModel.length;i++) {
      if(vm.writeModel[i].amount < vm.writeModel[i].allowance) {
        MessageWindow.open("补贴金额不能大于企业返费哦！");
        return 1;
      }
    }
  }

  vm.init = function () {
    vm.getLabourService();
    Enterprise.list().then(function (resposne) {
      angular.forEach(resposne.data, function (key, val){
        vm.arr.push({'enterprise_name': resposne.data[val].enterprise_name, 'enterprise_id': resposne.data[val].id});
      })
    }).then(function(){
      if (detail_id > 0) {
        return vm.loadrecruit();
      }
    }).then(function(){
     if (detail_id > 0) {
      for(var i=0; i<vm.arr.length; i++) {
        if(vm.arr[i].enterprise_id==vm.recruit.enterprise.enterprise_id) {
          vm.recruit.enterprise.enterprise_name = vm.arr[i].enterprise_name;
         }
      }
    }
    })
  };

  vm.init();

});