// 标签管理
app.controller('TagListController', function($scope, CandidateTag, CandidateTask, Contact, Customer) {
    var vm = this;
    vm.searchTag = '';

    vm.getTagList = function(){
        CandidateTag.list(vm.searchTag).then(function (response) {
            vm.tagList = response.data;
            vm.taglen = vm.tagList.length;
        });
    };

    vm.selectTag = function(){
      vm.getTagList();
    };

    /*设置标签*/
    vm.setTag = function (candidate) {
        CandidateTag.open(candidate, 1).then(function (data) {
            if (data == 'ok') {
                vm.getTagList();//加载
            }
        });
    }

    /**
     * 创建拨打计划
     */
    vm.newTask = function (cutomer) {
        CandidateTask.open(10, cutomer);
    }

    /**
     * 创建联系记录
     */
    vm.contactRecord = function (candidate) {
        Contact.open(candidate);
    };

    /**
     * 客户详情
     */
    vm.customerDetail = function (id) {
        Customer.open(id);
    }

    vm.getTagList();//加载
});