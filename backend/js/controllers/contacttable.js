'use strict';

/* Controllers */
// 全部
app.controller('ContactTableController', function($http, $scope, OperateButtons) {
    var vm = this;
    var params = {};
    vm.opens = [false, false];
    var startTime;
    var endTime;
    /*分页*/
    vm.pagingOptions = {
        count: 0,//总数量
        pageSize: 20,//每页显示数量
        pageShowLength: [20,30,50,100,1000],
        currentPage: 1,//当前页
        pages: 1,//总页数
        pageSizes : [1]
    };
    $scope.open = function($event,a) {
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
    vm.setPagingData = function(){
        vm.pagingOptions.pages = Math.ceil(vm.pagingOptions.count/vm.pagingOptions.pageSize);//总页数
    //显示分页
        var pageSizes = [];
        if ( vm.pagingOptions.pages<=5 ) {
            for(var i=1; i<=vm.pagingOptions.pages; i++){
                pageSizes.push(i);
            }
        } else {
            if ( vm.pagingOptions.currentPage >= 3 ) {
                pageSizes.push(vm.pagingOptions.currentPage-2);
                pageSizes.push(vm.pagingOptions.currentPage-1);
            }
            pageSizes.push(vm.pagingOptions.currentPage);
            if ( vm.pagingOptions.currentPage <= vm.pagingOptions.pages-2 ) {
                pageSizes.push(vm.pagingOptions.currentPage+1);
                pageSizes.push(vm.pagingOptions.currentPage+2);
            }
        }
        vm.pagingOptions.pageSizes = pageSizes;
    };
    /*切换分页*/
    vm.prevPage = function (){
        vm.pagingOptions.currentPage -= 1;
        if (vm.pagingOptions.currentPage <= 0) vm.pagingOptions.currentPage = 1;
        vm.contactDataAsync(vm.pagingOptions.currentPage, vm.pagingOptions.pageSize);
    }
    vm.nextPage = function (){
        vm.pagingOptions.currentPage += 1;
        if (vm.pagingOptions.currentPage > vm.pagingOptions.pages) vm.pagingOptions.currentPage = vm.pagingOptions.pages;
        vm.contactDataAsync(vm.pagingOptions.currentPage, vm.pagingOptions.pageSize);
    }
    vm.switchPage = function (page){
        vm.pagingOptions.currentPage = parseInt(page);
        vm.contactDataAsync(vm.pagingOptions.currentPage, vm.pagingOptions.pageSize);
    }
    vm.firstPage = function (){
        vm.pagingOptions.currentPage = 1;
        vm.contactDataAsync(vm.pagingOptions.currentPage, vm.pagingOptions.pageSize);
    }
    vm.lastPage = function (){
        vm.pagingOptions.currentPage = vm.pagingOptions.pages;
        vm.contactDataAsync(vm.pagingOptions.currentPage, vm.pagingOptions.pageSize);
    }
    /*按钮的权限*/
    vm.myButtons = {};
    vm.loadOperateButtons = function(){
        OperateButtons.success(function(response){
            vm.myButtons = response;
        });
    };
    vm.contactDataAsync = function(page,pageSize,searchTime){
        if(searchTime){
            params = {
            page: vm.pagingOptions.currentPage,
            pagesize: vm.pagingOptions.pageSize,
            contact_start: startTime,
            contact_end:  endTime
        }
        }else{
            params = {
                page: vm.pagingOptions.currentPage,
                pagesize: vm.pagingOptions.pageSize
            }
        }
        $http({
            method: 'get',
            url: 'api/customer/contact/my',
            params:params
        }).success(function(response){
            vm.contactdetail = response.list;
            vm.pagingOptions.count = response.total;
            vm.setPagingData();
        })
    };
    vm.contactSearch = function(){
        startTime = vm.startTime!=''?vm.setdater(vm.startTime):'';
        endTime = vm.endTime!=''?vm.setdater(vm.endTime):'';
        vm.contactDataAsync(vm.pagingOptions.currentPage, vm.pagingOptions.pageSize,true);
    };
    vm.contactDataAsync();
    vm.init = function(){
        vm.loadOperateButtons(vm.pagingOptions.currentPage, vm.pagingOptions.pageSize);
    };
    vm.init();
});