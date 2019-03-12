/**
 * 员工选择组织机构
 */
app.controller('EmployeeOrgController', function($scope, $http, Employee, $stateParams, $q, Organization) {
    var vm = this;
    vm.submitting = false;
    vm.error = '';
    vm.employee = {};
    //vm.organization = {};
    vm.options = [];
    var deferred = $q.defer();
    var promise = deferred.promise;
    promise.then(function(){
        /*获取员工的组织结构编号*/
        return Employee.get({id: $stateParams.id},function(response){
            vm.employee = response;
            if (response.org_id!=null) vm.organization = {"id" : response.org_id, "name" : response.org_name};
        });
    }).then(function(){
        /*获取所有组织机构*/
        return Organization.get(function(response){
            var options = response;
            if (options){
                angular.forEach(options, function(item){
                    if (item.id != undefined){
                        vm.options.push({"id" : item.id, "name" : item.org_name + '/' + item.nickname});
                    }
                });
            }
        });
    });
    deferred.resolve('A');
    vm.update = function(){
        vm.submitting = true;
        Employee.update({id:$stateParams.id}, {"org_id":vm.organization.id},function(){
            vm.error = 'success';
            vm.submitting = false;
        },function(){
            vm.error = 'failure';
            vm.submitting = false;
        });
    }
});