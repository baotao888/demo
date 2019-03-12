app.controller('AdminListController', function(Admin) {
    var vm = this;
    vm.list = [];
    Admin.get(function(response){
        vm.list = response;
    });
});