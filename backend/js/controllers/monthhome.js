'use strict';

/* 个人主页 */
app.controller('MonthDashboardController', function($http, HomeStatistics) {
    var vm = this;
    vm.isActive = 2;

    vm.showStatistics = function (data) {
        vm.candidate_statistics = data;
        vm.meet_data = vm.candidate_statistics.signup ? vm.candidate_statistics.meet/vm.candidate_statistics.signup*100 : 0;//接站比例
        vm.onduty_data = vm.candidate_statistics.meet ? vm.candidate_statistics.onduty/vm.candidate_statistics.meet*100 : 0;//入职比例
        vm.offduty_data = vm.candidate_statistics.onduty ? vm.candidate_statistics.offduty/vm.candidate_statistics.onduty*100 : 0;//离职比例
        /*趋势图统计*/
        vm.my_onduty_data = [];//我的入职人数
        angular.forEach(vm.candidate_statistics.day, function (v, k){
            vm.my_onduty_data.push([k,v]);
            //console.log(vm.my_onduty_data);
        });

    }

	/**
     * 本月人选统计
     * */
    vm.statisticsMonthCandidate = function () {
        HomeStatistics.candidateMonth().then(function (response) {
            vm.showStatistics(response.data.list);
        }, function () {
            vm.candidate_statistics = false;
        });
     }

	/**
     * 今日人选统计
     * */
    vm.statisticsTodayCandidate = function () {
        HomeStatistics.candidateToday().then(function (response) {
            vm.showStatistics(response.data);
        }, function () {
            vm.candidate_statistics = false;
        });
    }

	/**
     * 本周人选统计
     * */
    vm.statisticsWeekCandidate = function () {
        HomeStatistics.candidateWeek().then(function (response) {
            vm.showStatistics(response.data);
        }, function () {
            vm.candidate_statistics = false;
        });
    }

	/**
     * 本季度人选统计
     * */
    vm.statisticsQuarterCandidate = function () {
        HomeStatistics.candidateQuarter().then(function (response) {
            vm.showStatistics(response.data);
        }, function () {
            vm.candidate_statistics = false;
        });
    }

	/**
     * 组织架构关系统计
     * */
	vm.load_organization = function () {
	  $http.get('/api/index/home/organization').success(function(response){
		vm.organization = response;
	  }).error(function(){
		vm.organization = false;
	  });
	}

	/**
     * 公告
     * */
	vm.load_announcement = function () {
	  $http.get('/api/index/home/announcement').success(function(response){
		vm.announcement = response;
	  }).error(function(){
		vm.announcement = false;
	  });
	}

    /**
     * 本月入职图表统计
     * */
    vm.load_monthplot = function () {
        $http.get('/api/index/home/monthPlotStatistics').success(function(response){
            var month_plot_statistics = response;
            vm.ticks = month_plot_statistics.group.ticks;
            vm.group_signup_data = [];//各部门报名人数
            angular.forEach(month_plot_statistics.group.data.signup, function(v, k){
                vm.group_signup_data.push([k,v]);
            });
            vm.group_meet_data = [];//各部门接站人数
            angular.forEach(month_plot_statistics.group.data.meet, function(v, k){
                vm.group_meet_data.push([k,v]);
            });
            vm.group_onduty_data = [];//各部门入职人数
            angular.forEach(month_plot_statistics.group.data.onduty, function(v, k){
                vm.group_onduty_data.push([k,v]);
            });
            vm.group_outduty_data = [];//各部门离职人数
            angular.forEach(month_plot_statistics.group.data.outduty, function(v, k){
                vm.group_outduty_data.push([k,v]);
            });
        }).error(function(){
            vm.ticks = false;
        });
    }

	vm.loading = function () {
		vm.statisticsMonthCandidate();
		//vm.load_organization();
		//vm.load_announcement();
        vm.load_monthplot();
	}
	vm.loading();
  });