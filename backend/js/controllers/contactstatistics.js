'use strict';

/* Controllers */
// 部门联系记录
app.controller('ContactStatisticsController', function($http, $scope, OperateButtons, AdviserOrganizations) {
    var vm = this;
    var params;
    vm.opens = [false, false];
    $scope.open = function ($event, a) {
        vm.opens[a] = true;
        $event.preventDefault();
        $event.stopPropagation();
    };
    vm.setdater = function(searchdate){
        if(searchdate){
            var month = parseInt(searchdate.getMonth()+1);
            var txt_month = (month<10)?'0'+ month : month;
            var day = searchdate.getDate();
            var txt_day = (day<10)?'0'+ day : day;
            searchdate = searchdate.getFullYear()+'-'+txt_month+'-'+txt_day;
            return searchdate;
        }else{
            return;
        }
    };
    /**
     * 加载操作按钮
      */
    vm.loadOperateButtons = function(){
        OperateButtons.success(function(response){
            vm.myButtons = response;
            if (vm.myButtons.search_btn.organization){
                /*获取顾问信息*/
                AdviserOrganizations.then(function (resp) {
                    vm.orgs = resp.data.orgs;
                });
            }
        });
    };

    /*选择部门*/
    vm.selectOrganization = function(org){
        vm.org_name = org.name;
        vm.org_id = org.id;
        vm.contactSearch();
    }
    /*初始化部门*/
    vm.initOrganization = function(){
        vm.org_name = '部门';
        vm.org_id = '';
    }
    /*重置部门*/
    vm.resetOrganization = function () {
        vm.initOrganization();
        vm.contactSearch();
    }

    vm.contactloadlist = function (params){
        $http({
            method: 'get',
            url: 'api/customer/contact/search',
            params: params
        }).success(function(response){
            vm.org = response;
        })
    }
    vm.contactSearch = function(){
        var startTime = vm.startTime!=''?vm.setdater(vm.startTime):'';
        var endTime =  vm.endTime!=''?vm.setdater(vm.endTime):'';
        params = {contact_start: startTime,contact_end: endTime, org_id: vm.org_id};
        vm.contactloadlist(params);
    };

    vm.init = function(){
        vm.loadOperateButtons();
        vm.initOrganization();
        vm.contactloadlist();
    };
    vm.init();
});