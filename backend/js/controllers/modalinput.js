/**
 * 输入表单项
 */
app.controller('ModalInputController', ['$scope', '$modalInstance', '$http', '$state', 'operate', 'selectedCustomer', function($scope, $modalInstance, $http, $state, operate, selectedCustomer) {
    $scope.back = {status:1};
    $scope.candidate = {};
    $scope.continue = false;
    $scope.dt = new Date();
    $scope.error = '';
    $scope.format = 'yyyy-MM-dd';
    $scope.inviter = '';
    $scope.invite_amount = 0;
    $scope.invite_is_member = false;
    $scope.inviter_phone = '';
    $scope.minDate = new Date();
    $scope.operate = operate;
    $scope.status_list = [{value:0, text:'无意向'}, {value:1, text:'有意向'}];
    $scope.submitting = false;
    $scope.ok = function () {
        $scope.submitting = true;
        if (operate == 3) {
            /*客户接站*/
            $http({
                method:'post',
                url:'/api/customer/candidate/meet',
                data:{idcard:$scope.idcard, customer:selectedCustomer, birth: $scope.candidate.customer.birthday, gender: $scope.candidate.customer.gender}
            }).success(function(req){
                $state.go('app.candidate.list', {type:'meet'});
            });
        } else if (operate == 4){
            /*客户入职*/
            $http({
                method:'post',
                url:'/api/customer/candidate/onduty',
                data:{inviter: $scope.inviter, inviter_phone: $scope.inviter_phone, invite_amount: $scope.invite_amount, customer:selectedCustomer, invite_is_member: $scope.invite_is_member}
            }).success(function(req){
                $state.go('app.candidate.list', {type:'onduty'});
            });
        } else if (operate == 10){
            /*新增计划*/
            $http({
                method:'post',
                url:'/api/customer/contact/addTask',
                data:{customer:selectedCustomer, start_time:$scope.dt,content:$scope.content}
            }).success(function(req){
                if ($scope.continue == false) $state.go('app.my.tasklist');
            }).error(function(){
                $scope.error = '创建失败，系统繁忙';
            });
        } else if (operate == 98){
            /*人选状态回退*/
            $http({
                method:'post',
                url:'/api/customer/candidate/back',
                data:{"customers/a":selectedCustomer, status:$scope.back.status}
            }).success(function(req){
                if ($scope.back_status==1) $state.go('app.candidate.list', {type:'intention'});
                else $state.go('app.candidate.list', {type:'other'});
            });
        }
        $modalInstance.close('ok');
    };
    $scope.cancel = function () {
        $modalInstance.dismiss('cancel');
    };
    $scope.close = function () {
        $scope.continue = true;
        $scope.ok();
    };
    $scope.open = function($event) {
        $event.preventDefault();
        $event.stopPropagation();

        $scope.opened = true;
    };
    /*加载页面*/
    $scope.init = function () {
        /*入职*/
        if (operate == 4) {
            /*获取用户的入职日期和入职职位*/
            $http({method: 'get', url: '/api/customer/candidate/detail', params: {id: selectedCustomer}}).success(function (req) {
                $scope.candidate = req;
            });
        } else if (operate == 3) {
            /*获取用户的基本信息*/
            $http({method: 'get', url: '/api/customer/candidate/detail', params: {id: selectedCustomer}}).success(function (req) {
                $scope.candidate = req;
                $scope.idcard = $scope.candidate.customer.idcard;
            });
        }
    }
    $scope.init();
}]);