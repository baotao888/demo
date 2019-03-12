'use strict';

/* Controllers */
// wechat log controller
app.controller('WechatLogController', function($http) {
    var vm = this;
    vm.list = [];
    vm.page = 1;
    vm.more = true;
    vm.loadLogs = function () {
        $http.get('/api/user/wechat/logs', {
            params: {page: vm.page}
        }).success(function (res) {
            var list = res;
            if(list.length>0){
                angular.forEach(list, function (value) {
                    vm.list.push(value);
                });
            } else {
                vm.more = false;
            }
        });

    }
    vm.loadMore = function() {
        vm.page++;
        vm.loadLogs();
    }
    vm.loadLogs();
})
;