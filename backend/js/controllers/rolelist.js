app.controller('RoleListController', function(Role) {
    var vm = this;
    vm.list = [];
    Role.get(function(response){
        vm.list = response;
    });
});