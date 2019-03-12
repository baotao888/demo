/**
 * 拨打计划列表
 */
app.controller('TaskTableController', function ($http, $modal, $scope, $filter, Contact, ConfirmWindow) {
    var vm = this,
        parames,
        startTime,
        endTime;
    vm.tasklist = {start_date: "", end_date: ""};
    vm.opens = [false, false];
    vm.size = 10;

    $scope.open = function ($event,a) {
        vm.opens[a] = true;
        $event.preventDefault();
        $event.stopPropagation();
    };
    
    vm.toreLoad = function(size, searchtime) {
        if(!searchtime) {
            parames = {size: size};
        }else{
            parames = {size: size, start_date: startTime, end_date: endTime};
        }
        $http({
            method: 'get',
            url: '/api/index/index/task',
            params: parames
        }).success(function (response) {
            vm.tasklist = response;
        });
    };

    /*拨打*/
    vm.toCall = function (candidate) {
        Contact.open(candidate);
    };

    /*完成*/
    vm.complete = function(index) {
        var rmoveid = vm.tasklist[index].id;
        ConfirmWindow.open('确认已完成此任务吗？').then(function () {
            $http({
                method: 'get',
                url: '/api/index/index/finishTask',
                params: {
                    id : rmoveid
                }
            }).success(function() {
                vm.tasklist.splice(index, 1);
            })
        });
    };

    vm.toreLoad(vm.size);
    vm.taskSearch = function(){
        startTime = $filter('date')(vm.startTime, 'yyyy-MM-dd');
        endTime = $filter('date')(vm.endTime, 'yyyy-MM-dd');
        vm.toreLoad(vm.size, true);
    };
});