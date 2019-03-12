app.controller('EmployeeListController', function(Employee) {
    var vm = this;
    vm.list = [];
    vm.load = function(){
        Employee.get(function(response){
            vm.list = response;
        });
    }
    vm.outduty = function(id, status){
        if (status==1) {
            /*在职情况下，点击离职*/
            //更新基本信息
            Employee.update({id:id}, {
                status: 0
            },function(){
                vm.load();
            });
        }
    }
    vm.load();
});