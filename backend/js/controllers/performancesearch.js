app.controller('PerformanceSearchController', function() {
    var vm = this;
    vm.header = ['人选姓名', '手机号', '入职日期', '企业名称',  '职位类型', '总金额', '提成', '返费日期'];
    vm.fields = ['candidate_name', 'candidate_phone', 'entry_time', 'company_name','job_type', 'total_money', 'reward', 'fee_time'];
    vm.performace = [
        {'candidate_name': '猪猪', 'candidate_phone': '13678218506', 'entry_time': '2017-12-1', 'company_name': '富康康', 'job_type': '工人', 'total_money': '1600', 'reward': '600', 'fee_time': '2017-12-19'},
        {'candidate_name': '花花', 'candidate_phone': '13887068506', 'entry_time': '2017-12-5', 'company_name': '仁宝宝', 'job_type': '工人', 'total_money': '1500', 'reward': '500', 'fee_time': '2017-12-19'},
        {'candidate_name': '毛毛', 'candidate_phone': '13887095306', 'entry_time': '2017-12-10', 'company_name': '富康康', 'job_type': '工人', 'total_money': '1400', 'reward': '400', 'fee_time': '2017-12-19'},
        {'candidate_name': '狗狗', 'candidate_phone': '13678218506', 'entry_time': '2017-12-15', 'company_name': '仁宝宝', 'job_type': '工人', 'total_money': '1300', 'reward': '300', 'fee_time': '2017-12-19'},
        {'candidate_name': '大黄', 'candidate_phone': '13678218506', 'entry_time': '2017-12-20', 'company_name': '富康康', 'job_type': '工人', 'total_money': '1200', 'reward': '200', 'fee_time': '2017-12-19'},
        {'candidate_name': '二哈', 'candidate_phone': '13678218506', 'entry_time': '2017-12-30', 'company_name': '仁宝宝', 'job_type': '工人', 'total_money': '1100', 'reward': '100', 'fee_time': '2017-12-19'}
    ];

})