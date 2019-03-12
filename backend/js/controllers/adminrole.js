/**
 * 后台用户选择角色
 */
app.controller('AdminRoleController', function($scope, $http, $stateParams, $q, Admin, Role) {
    var vm = this;
    vm.submitting = false;
    vm.error = '';
    vm.admin = {};
    //vm.admin_role = {};
    vm.roles = [];
    //vm.roles = [{"id":1, "role_name":"CEO"}, {"id":2, "role_name":"CTO"}];
    var deferred = $q.defer();
    var promise = deferred.promise;
    promise.then(function(){
        /*获取用户角色*/
        return Admin.get({id: $stateParams.id},function(response){
            vm.admin = response;
            if (response.role_id!=null) vm.admin_role = {"id" : response.role_id, "name" : response.role_name};
        });
    }).then(function(){
        /*获取所有角色*/
        return Role.get(function(response){
            var roles = response;
            if (roles){
                angular.forEach(roles, function(role){
                    if (role.id != undefined){
                        vm.roles.push({"id" : role.id, "name" : role.role_name});
                    }
                });
            }
        });
    });
    deferred.resolve('A');
    vm.update = function(){
        vm.submitting = true;
        Admin.update({id:$stateParams.id}, {"role_id":vm.admin_role.id},function(){
            vm.error = 'success';
            vm.submitting = false;
        },function(){
            vm.error = 'failure';
            vm.submitting = false;
        });
    }
});