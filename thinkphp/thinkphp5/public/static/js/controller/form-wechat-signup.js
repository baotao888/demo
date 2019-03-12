$(function(){
  $('#signupBirthday').datetimepicker({
	language: 'zh-CN',
	autoclose: true,
	minView: "month",
	format: 'yyyy-mm-dd',
	startDate: '1956-01-01',
	endDate: '2000-12-31',
	initialDate: '1995-01-01'
  }).on('hide',function(e) {  
	$('form').data('bootstrapValidator')  
		.updateStatus('birthday', 'NOT_VALIDATED',null)  
		.validateField('birthday');  
  });
  $('form').bootstrapValidator({
  　 message: '请输入……',
	feedbackIcons: {
  　　　　valid: 'glyphicon glyphicon-ok',
  　　　　invalid: 'glyphicon glyphicon-remove',
  　　　　validating: 'glyphicon glyphicon-refresh'
　　　 },
	fields: {
	  birthday: {
		validators: {
		  notEmpty: {
			message: '请选择出生日期，某些岗位有年龄要求'
		  },	
		  date: {
			format: 'YYYY-MM-DD',
			message: '日期格式错误'
		  }
		}
	  },
	  gender: {
		validators: {
		  notEmpty: {
			message: '请选择性别，某些岗位有性别要求'
		  }
		}
	  }
	}
  });
});