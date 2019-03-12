app.controller('AdminFormController', function($scope, $state, $stateParams, $modal, Admin, Errors) {
    var vm = this;
    /*初始化*/
    vm.error = '';
    vm.operate = "新增";
    vm.edit = false;
    vm.submitting = false;
    vm.account = {"id":"", "admin_name":"", "admin_pwd":"", "status":1, "role_id":0, "is_admin":0};
    vm.confirm_password = "";
    /*新增用户*/
    vm.submitInfo = function(){
        vm.submitting = true;
        Admin.save({
            admin_name: vm.account.admin_name,
            admin_pwd: vm.account.admin_pwd
        },function(response) {
            $state.go('app.admin.list');//跳转到列表页
        }, function() {
            vm.open(Errors.params);
            vm.error = '保存失败';
            vm.submitting = false;
        });
    }

    /*更新用户信息*/
    if ($stateParams.id != null && $stateParams.id != ''){
        Admin.get({id: $stateParams.id},function(response){
            vm.account = response;
            vm.account.admin_pwd = '';//密码不能更新
            if (vm.account.status == 1) vm.account.status = true;
            else vm.account.status = false;
            if (vm.account.is_admin == 1) vm.account.is_admin = true;
            else vm.account.is_admin = false;
        });
        vm.operate = "编辑";
        vm.edit = true;
        vm.submitInfo = function () {
            vm.submitting = true;
            //更新基本信息
            Admin.update({id:$stateParams.id}, {
                admin_name: vm.account.admin_name,
                admin_pwd: vm.account.admin_pwd,
                status: vm.account.status,
                is_admin: vm.account.is_admin
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
});
