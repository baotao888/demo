/**
 * 员工选择后台用户
 */
app.controller('EmployeeUserController', function($scope, $http, Employee, $stateParams, $q, Admin) {
    var vm = this;
    vm.submitting = false;
    vm.error = '';
    vm.employee = {};
    //vm.admin = {};
    vm.users = [];
    var deferred = $q.defer();
    var promise = deferred.promise;
    promise.then(function(){
        /*获取员工的后台用户编号*/
        return Employee.get({id: $stateParams.id},function(response){
            vm.employee = response;
            if (response.admin_id!=null) vm.admin = {"id" : response.admin_id, "name" : response.admin_name};
        });
    }).then(function(){
        /*获取所有后台用户*/
        return Admin.get(function(response){
            var users = response;
            if (users){
                angular.forEach(users, function(user){
                    if (user.id != undefined){
                        vm.users.push({"id" : user.id, "name" : user.admin_name});
                    }
                });
            }
        });
    });
    deferred.resolve('A');
    vm.update = function(){
        vm.submitting = true;
        Employee.update({id:$stateParams.id}, {"admin_id":vm.admin.id},function(){
            vm.error = 'success';
            vm.submitting = false;
        },function(){
            vm.error = 'failure';
            vm.submitting = false;
        });
    }
});