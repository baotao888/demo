app.controller('MessageController', function($http, $http,$modal) {
    var vm = this;
    vm.employees = [];
    vm.message = [];
    vm.navi = {};
    vm.submitting = false;
    vm.more = true;
    vm.page = 1;
    vm.pageSize = 10;
        /*按钮的权限*/
    vm.loadNavi = function(){
        $http.get('/api/index/index/operatebtn').success(function(response){
            vm.navi = response;
        });
    }
	/**
	 * 加载员工列表
	 */
	vm.loadEmployee = function () {
	  $http.get('/api/index/index/allEmployee').success(function(response){
		  var employees = response;
		  if (employees){
			angular.forEach(employees, function(employee){
				if (employee.id != undefined){
					vm.employees.push({"id" : employee.id, "name" : employee.real_name, "nickname" : employee.nickname});
				}
			});
		  }
	  });
	}

    /**
     * 加载消息
     */
    vm.loadMessage = function() {
        $http.get('/api/index/index/messages', {
		  params: {
			pagesize: vm.pageSize,
			page: vm.page
		  }
		}).then(function(res){
			var list = res.data;
			if(list.length>0){
                angular.forEach(list, function (value) {
                    vm.message.push(value);
                });
			}
			if(list.length<vm.pageSize){
                vm.more = false;
			}
		});
    };
    vm.loadMore = function() {
    	//vm.page++;//消息显示以后会自动设置为已读，所以此处无需加一
    	vm.loadMessage();
	}

    /**
     * 发送短消息
     */
    vm.sendMessge = function() {
      if (vm.receiver==undefined){
		vm.open('请选择接收者');
	  } else if (vm.new_content==undefined){
          vm.open('请输入短信内容');
	  } else {
          vm.submitting = true;
          $http({
              method:'post',
              url:'/api/index/index/sendMessage',
              data:{receiver:vm.receiver.id, content:vm.new_content}
          }).success(function(req){
              if (req.id > 0) vm.open('已发送!');
              else vm.open('发送失败!');
              vm.submitting = false;
              vm.new_content = '';
          }).error(function(){
              vm.open('发送失败');
              vm.submitting = false
          });
	  }
    };
	/**
	 * 回复短消息
	 */
	vm.replyMessage = function(id, receiver) {
	  $http({
		  method:'post',
		  url:'/api/index/index/sendMessage',
		  data:{receiver:receiver, content:vm.reply[id]}
	  }).success(function(req){
		  if (req.id > 0){
              vm.open('已发送!');
              vm.reply[id] = '';
          }
		  else vm.open('发送失败');
	  }).error(function(){
		  vm.open('发送失败');
	  });
	};
    /**
     * 群发消息
     */
    vm.mass = function() {
        if (vm.mass_content==undefined){
            vm.open('请输入群发内容');
        } else {
            vm.submitting = true;
            $http({
                method:'post',
                url:'/api/index/index/mass',
                data:{content:vm.mass_content}
            }).success(function(req){
                if (req.id > 0) vm.open('已发送!');
                else vm.open('发送失败!');
                vm.submitting = false;
                vm.mass_content = '';
            }).error(function(){
                vm.open('发送失败');
                vm.submitting = false;
            });
        }
    };
  vm.open = function (msg) {
    var modalInstance = $modal.open({
      templateUrl: 'modal.html',
      controller: 'ModalInstanceController',
      resolve: {
        msg: function () {
          return msg;
        }
      }
    });
  };

	/*初始化数据*/
    vm.init = function(){
        vm.loadNavi();
        vm.loadMessage();
        vm.loadEmployee();
    }
    vm.init();
});