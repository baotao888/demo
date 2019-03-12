app.controller('PositionFormController', function($scope, $state, Position, Errors, $stateParams, $modal,$location) {
    var vm = this;
    /*初始化*/
    vm.error = '';
    vm.operate = "新增";
    vm.edit = false;
    vm.submitting = false;
    vm.position = {"id":"", "pos_name":"", "description":"", "is_manager":false,level:"","is_adviser":false};
    /*新增职位*/
    vm.submitInfo = function(){
        vm.submitting = true;
        Position.save({
            pos_name: vm.position.pos_name,
            description: vm.position.description,
            level : vm.position.level,
            is_manager: vm.position.is_manager,
            is_adviser: vm.position.is_adviser
        },function(response) {
            $state.go('app.organization.position');//跳转到列表页
        }, function() {
            vm.open(Errors.params);
            vm.error = '保存失败';
            vm.submitting = false;
        });
    }

    /*更新职位信息*/
    if ($stateParams.id != null && $stateParams.id != ''){
        Position.get({id: $stateParams.id},function(response){
            vm.position = response;
            if (vm.position.is_manager == 1) vm.position.is_manager = true;
            else vm.position.is_manager = false;
            if (vm.position.is_adviser == 1) vm.position.is_adviser = true;
            else vm.position.is_adviser = false;
        });
        vm.operate = "编辑";
        vm.edit = true;
        vm.submitInfo = function () {
            vm.submitting = true;
            //更新基本信息
            Position.update({id:$stateParams.id}, {
                pos_name: vm.position.pos_name,
                description: vm.position.description,
                level : vm.position.level,
                is_manager: vm.position.is_manager,
                is_adviser: vm.position.is_adviser
            },function(){
                vm.open('success');
                vm.error = '已更新';
                $location.path('app/organization/position');
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
