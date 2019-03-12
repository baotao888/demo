/**
 * 员工选择职位
 */
app.controller('EmployeePosController', function($scope, $http, Employee, $stateParams, $q, Position) {
    var vm = this;
    vm.submitting = false;
    vm.error = '';
    vm.employee = {};
    //vm.position = {};
    vm.options = [];
    var deferred = $q.defer();
    var promise = deferred.promise;
    promise.then(function(){
        /*获取员工的职位编号*/
        return Employee.get({id: $stateParams.id},function(response){
            vm.employee = response;
            if (response.pos_id!=null) vm.position = {"id" : response.pos_id, "name" : response.pos_name};
        });
    }).then(function(){
        /*获取所有职位*/
        return Position.get(function(response){
            var options = response;
            if (options){
                angular.forEach(options, function(item){
                    if (item.id != undefined){
                        vm.options.push({"id" : item.id, "name" : item.pos_name});
                    }
                });
            }
        });
    });
    deferred.resolve('A');
    vm.update = function(){
        vm.submitting = true;
        Employee.update({id:$stateParams.id}, {"pos_id":vm.position.id},function(){
            vm.error = 'success';
            vm.submitting = false;
        },function(){
            vm.error = 'failure';
            vm.submitting = false;
        });
    }
});