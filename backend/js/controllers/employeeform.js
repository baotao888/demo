/**
 * 职员表单
 */
app.controller('EmployeeFormController', function($scope, $state, Employee, Errors, $stateParams, $modal) {
    var vm = this;
    /*初始化*/
    vm.error = '';
    vm.operate = "新增";
    vm.edit = false;
    vm.submitting = false;
    vm.account = {"id":"", "real_name":"", "nickname":"", "phone":"", "gender":0, "join_at":"2017-08-01", "status":1,"number": ""};
    /*新增职员*/
    vm.submitInfo = function(){
        vm.submitting = true;
        Employee.save({
            real_name: vm.account.real_name,
            nickname: vm.account.nickname,
            phone: vm.account.phone,
            gender: vm.account.gender,
            join_at: vm.account.join_at,
            status: vm.account.status,
            number: vm.account.number
        },function(response) {
            $state.go('app.organization.employee');//跳转到列表页
        }, function() {
            vm.open(Errors.params);
            vm.error = '保存失败';
            vm.submitting = false;
        });
    }

    /*更新职员信息*/
    if ($stateParams.id != null && $stateParams.id != ''){
        Employee.get({id: $stateParams.id},function(response){
            vm.account = response;
            if (vm.account.status == 1) vm.account.status = true;
            else vm.account.status = false;
        });
        vm.operate = "编辑";
        vm.edit = true;
        vm.submitInfo = function () {
            vm.submitting = true;
            //更新基本信息
            Employee.update({id:$stateParams.id}, {
                real_name: vm.account.real_name,
                nickname: vm.account.nickname,
                phone: vm.account.phone,
                gender: vm.account.gender,
                join_at: vm.account.join_at,
                status: vm.account.status,
                number: vm.account.number
            },function(){
                vm.open('success');
                vm.error = '已更新';
                vm.submitting = false;
            },function(){
                vm.open('failure');
                vm.error = '更新失败';
                vm.submitting = false;
            });
        }
    }
    vm.open = function (msg) {
        var modalInstance = $modal.open({
            templateUrl: 'modal.html',
            controller: 'ModalInstanceController',
            resolve: {
                msg: function () {
                    return msg;
                }
            }
        });
    };

    /*日期插件*/
    $scope.today = function() {
        $scope.dt = new Date();
    };
    $scope.today();

    $scope.clear = function () {
        $scope.dt = null;
    };

    // Disable weekend selection
    $scope.disabled = function(date, mode) {
        return ( mode === 'day' && ( date.getDay() === 0 || date.getDay() === 6 ) );
    };

    $scope.toggleMin = function() {
        $scope.minDate = $scope.minDate ? null : new Date();
    };
    $scope.toggleMin();

    $scope.open = function($event) {
        $event.preventDefault();
        $event.stopPropagation();

        $scope.opened = true;
    };

    $scope.dateOptions = {
        formatYear: 'yy',
        startingDay: 1,
        class: 'datepicker'
    };

    $scope.initDate = new Date('2017-08-17');
    $scope.formats = ['shortDate', 'yyyy-MM-dd'];
    $scope.format = $scope.formats[1];
});
