app.controller('RoleFormController', function($scope, $state, Role, Errors, $stateParams, $modal) {
    var vm = this;
    /*初始化*/
    vm.error = '';
    vm.operate = "新增";
    vm.edit = false;
    vm.submitting = false;
    vm.role = {"id":"", "role_name":"", "description":""};
    /*新增权限*/
    vm.submitInfo = function(){
        vm.submitting = true;
        Role.save({
            role_name: vm.role.role_name,
            description: vm.role.description
        },function(response) {
            $state.go('app.role.list');//跳转到列表页
        }, function() {
            vm.open(Errors.params);
            vm.error = '保存失败';
            vm.submitting = false;
        });
    }

    /*更新权限信息*/
    if ($stateParams.id != null && $stateParams.id != ''){
        Role.get({id: $stateParams.id},function(response){
            vm.role = response;
        });
        vm.operate = "编辑";
        vm.edit = true;
        vm.submitInfo = function () {
            vm.submitting = true;
            //更新基本信息
            Role.update({id:$stateParams.id}, {
                role_name: vm.role.role_name,
                description: vm.role.description
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
