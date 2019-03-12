app.controller('PerformanceSaleController', function($http, $modal, $filter, $scope, $timeout, Enterprise, AdviserOrganizations, Labourservice, $stateParams, MessageWindow, InputSale, OperateButtons) {
  var vm = this;
  var params = {};
  vm.headers = ['入职企业', '劳务公司', '姓名', '接站时间', '到期时间', '人选在职天数', '企业返费', '补贴金额'];
  vm.fileds = ['enterprise', 'labour_service', 'real_name', 'go_to_time', 'receive_date', 'onduty_day', 'amount', 'allowance'];
  vm.myButtons = {};

  vm.pagingOptions = {
    count: 0,
    pageSize: 20,
    pageShowLength: [20,30,50,100,1000],
    currentPage: 1,
    pages: 1,
    pageSizes: [1],
    pageMax: 8
  };
  vm.searchString = {};
  vm.searchCompany = [];
  vm.employees_options = [];
  vm.orgs_options = [];
  vm.labourlist = [];
  vm.saleslist = [];
  vm.checked = [];
  vm.enterpriseArr = [];
  vm.isChecked = false;
  vm.ent = {id: '',  enterprise_name: '请选择企业'};
  vm.larbours = {id: '', name: '请选择劳务公司'};
  vm.opens = [false, false, false, false, false, false];

  $scope.open = function($event,a) {
      vm.opens[a] = true;
      $event.preventDefault();
      $event.stopPropagation();
    };

  vm.setPagingData = function(){
    vm.pagingOptions.pages = Math.ceil(vm.pagingOptions.count/vm.pagingOptions.pageSize);
  };

  vm.selectLength = function(){
    vm.getPagedDataAsync();
  };

  vm.switchPage = function () {
    vm.pagingOptions.currentPage = Math.min(Math.max(1, parseInt(vm.inputPage)), vm.pagingOptions.pages);
    vm.getAchieventment();
  };

  if ($stateParams.type==2) {
    vm.headers = ['入职企业', '劳务公司', '姓名', '接站时间', '本月工时(小时)', '企业单价', '人选单价', '调整差价'];
    vm.fileds = ['enterprise', 'labour_service', 'real_name', 'go_to_time', 'worked_time', 'ent_wage', 'cp_wage', 'adjusted_price'];
  } else if($stateParams.type==3) {
    vm.headers = ['入职企业', '劳务公司', '姓名', '接站时间', '到期时间', '人选在职天数', '企业返费', '企业单价'];
    vm.fileds = ['enterprise', 'labour_service', 'real_name', 'go_to_time', 'receive_date', 'onduty_day', 'amount', 'allowance'];
  }

  vm.getAchieventment = function() {
    $stateParams.type == '' ? vm.type = 1 : vm.type = $stateParams.type;
    params = {
      type: vm.type,
      page: vm.pagingOptions.currentPage == 'undefined'? '' : vm.pagingOptions.currentPage,
      pagesize: vm.pagingOptions.pageSize,
      time_start: vm.time_start!=''?$filter('date')(vm.time_start, 'yyyy-MM-dd'):'',
      time_end: vm.time_end!=''?$filter('date')(vm.time_end, 'yyyy-MM-dd'):'',
      receive_start: vm.receive_start!=''?$filter('date')(vm.receive_start, 'yyyy-MM-dd'):'',
      receive_end: vm.receive_end!=''?$filter('date')(vm.receive_end, 'yyyy-MM-dd'):'',
      keyword: vm.keyword == 'undefined' ? '' : vm.keyword,
      adviser: vm.adviser == 'undefined' ? '' : vm.adviser,
      org: vm.org == 'undefined' ? '' : vm.org,
      is_invalid: vm.is_invalid == 'undefined' ? '' : vm.is_invalid,
      is_sure: vm.is_sure == 'undefined' ? '' : vm.is_sure
    };

    vm.loadData(params);
  };

  vm.ordiarySearch = function() {
    vm.getAchieventment();
  };

  vm.selectLength = function() {
    vm.getAchieventment();
  };

  /*全选*/
  vm.checkedAll = function() {
    if (vm.isChecked) {
      for (var i=0;i<vm.pagingOptions.count;i++) {
          vm.checked[i] = true;
      }
    } else {
      for (var i=0;i<vm.pagingOptions.count;i++) {
          vm.checked[i] = false;
      }
    }

  };

/*单选*/
  vm.checkalone = function(a) {
    var checked_length = 0;
    angular.forEach(vm.checked, function(value, key){ //如果value为真，
      if (value) checked_length += 1;
      if (checked_length==vm.saleslist.length) {
        vm.isChecked = true;
      } else {
        vm.isChecked = false;
      }
    })
  };

  vm.loadData = function(data) {
    var url = '/api/salesorder';
    $http({method: 'get', url: url, params: data}).success(function(res){
      vm.sales = res;
      vm.saleslist = res.list;
      vm.pagingOptions.count = res.count;
    })
  };

  /*加载企业，劳务公司*/
  vm.searchMore = function() {
    Enterprise.list().then(function(res) {
      vm.searchCompany = res.data;//企业
      angular.forEach(vm.searchCompany, function(k, v){
        vm.enterpriseArr.push({'id': k.id, 'enterprise_name': k.enterprise_name});
      })
    });

    Labourservice.then(function(res) {
      vm.labourlist = res.data;
    })

  };

  /*高级搜索*/
  vm.senior = function() {
    params.ent_id = vm.ent.id;
    params.ls_id = vm.larbours.id;
    vm.loadData(params);
  };

  /* 提示信息*/
  vm.showMessage = function (msg) {
      MessageWindow.open(msg);
  };

  /**入账,删除，恢复，领补贴，领推荐费**/
  vm.doperation = function(operation, id) {
    var arrid = [];
    arrid.push(id);
    if (operation==4) {
      if ($stateParams.type!='') {
        if ($stateParams.type!=1) {
          vm.showMessage('只有正式工才可以领补贴哦！');
          return;
        }
      }
    }
    InputSale.open(vm.type, operation, arrid);
  };

  vm.opendetail = function(id) {
     $modal.open({
      templateUrl: 'tpl/order_detail.html',
      controller: 'OrderDetailController as vm',
      size: 'lg',
      resolve: {
        id: function () {
          return id;
        }
      }
     });
  };

    /**
     * 批量操作
     */
   vm.patchOperate = function () {
       var arr_id = [];
       angular.forEach(vm.saleslist, function(value, key){
           if (vm.checked[key]) {
               arr_id.push(value.id);
           }
       });
       if (arr_id.length==0) {
           vm.showMessage('请选择业绩');
           return;
       }
       if (vm.operation == 6) {
           //导出数据
           $http({
               method: 'post',
               url: '/api/salesorder/index/export',
               data: {'ids/a': arr_id, 'type': $stateParams.type},
               responseType: 'arraybuffer'
           }).success(function(data){
               var blob = new Blob([data], {type: "application/vnd.ms-excel"});
               var objectUrl = URL.createObjectURL(blob);
               var aForExcel = $("<a><span class='forExcel'>下载excel</span></a>").attr("href",objectUrl);
               $("body").append(aForExcel);
               $timeout(function() {
                   $(".forExcel").click();
                   aForExcel.remove();
                   URL.revokeObjectURL(objectUrl);
               }, 0, false);
           }).error(function(data){
               console.log(data);
           });
       } else {
         vm.showMessage('请选择操作');
       }
   };

  /**
    * 加载操作按钮
    */
  vm.loadOperateButtons = function(){
      OperateButtons.success(function(response){
          vm.myButtons = response;
          vm.myButtons.performance_btn.paid_allowance = ($stateParams.type == 1 || $stateParams.type == '') ? true : false;
          vm.myButtons.performance_btn.adjust_price = $stateParams.type == 2 ? true : false; // 调整差价
		  vm.myButtons.performance_btn.out_duty = $stateParams.type == 2 ? true : false; // 离职
		  vm.myButtons.performance_btn.on_duty = $stateParams.type == 2 ? true : false; // 继续在职
          if (vm.myButtons.search_btn.adviser || vm.myButtons.search_btn.organization){
              /*获取顾问信息*/
              AdviserOrganizations.then(function (resp) {
                  if (vm.myButtons.search_btn.adviser) vm.employees_options = resp.data.employees; // 员工
                  if (vm.myButtons.search_btn.organization) vm.orgs_options = resp.data.orgs; // 部门
              });
          }
      });
  };

  vm.init = function () {
    vm.loadOperateButtons();
    vm.getAchieventment();
  };

  vm.init();

});
