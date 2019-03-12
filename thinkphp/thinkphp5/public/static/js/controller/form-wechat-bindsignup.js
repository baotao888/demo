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
	  mobile: {
		validators: {
		  notEmpty: {
			message: '手机号码不能为空'
		  },
		  regexp: {
			regexp: /^(1)[3,4,5,6,7,8][0-9]{9}$/,
			message: '手机号码为11位'
		  },
		  remote: {
			url: '/user/check/mobile',//验证手机号码是否未注册
			message: '此手机号码已注册，请登录后报名吧',//提示消息
			delay : 800
		  }
		}
	  },
	  realname: {
		message: '姓名有误',
		validators: {
		  notEmpty: {
			message: '姓名不能为空'
		  },
		  regexp: {
			regexp: /^[\u4E00-\u9FA5\uF900-\uFA2D]+$/,
			message: '姓名只能是中文'
		  },
		  stringLength: {
			min: 2,
			max: 5,
			message: '姓名必须在2到5个字之间'
		  }
		}
	  },
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
	  },
	  sms_code: {
		validators: {
		  notEmpty: {
			message: '验证码不能为空'
		  },
		  remote: {
			url: '/message/sms/checkregistercode',//验证手机短信验证码是否正确
			message: '验证码错误',//提示消息
			delay : 800,
			data: function(validator) {
			   return {
				   mobile: $('#inputMobile').val()
			   };
			}
		  }
		}
	  }
	}
  });
});