app.controller('PerformanceStaticController', function() {
    var vm = this;
    vm.header = ['部门', '员工', '在职人数', '补贴总金额', '个人提成'];
    vm.fields = ['branch', 'staff', 'job_persons', 'subsidy_money', 'personal_accom'];
    vm.performace = [
        {'branch': '富康康', 'staff': '猪猪', 'job_persons': '100', 'subsidy_money': '1000', 'personal_accom': '107'},
        {'branch': '仁宝宝', 'staff': '花花', 'job_persons': '200', 'subsidy_money': '500', 'personal_accom': '130'},
        {'branch': '富康康', 'staff': '毛毛', 'job_persons': '300', 'subsidy_money': '600', 'personal_accom': '160'},
        {'branch': '仁宝宝', 'staff': '狗狗', 'job_persons': '100', 'subsidy_money': '300', 'personal_accom': '70'},
        {'branch': '富康康', 'staff': '大黄', 'job_persons': '200', 'subsidy_money': '500', 'personal_accom': '100'},
        {'branch': '仁宝宝', 'staff': '二哈', 'job_persons': '500', 'subsidy_money': '200', 'personal_accom': '50'}
    ];

})