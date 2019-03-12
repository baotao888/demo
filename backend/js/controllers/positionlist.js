app.controller('PositionListController', function(Position) {
    var vm = this;
    vm.list = [];
    Position.get(function(response){
        vm.list = response;
    });
});