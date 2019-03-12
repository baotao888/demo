// [微信端绑定验证]
$(function () {	
  $('form').bootstrapValidator({
  　　message: 'This value is not valid',
	feedbackIcons: {
  　　　　valid: 'glyphicon glyphicon-ok',
  　　　　invalid: 'glyphicon glyphicon-remove',
  　　　　validating: 'glyphicon glyphicon-refresh'
　　　 },
	fields: {
	  realname: {
		message: '姓名有误',
		validators: {
		  notEmpty: {
			message: '姓名不能为空'
		  },
		  regexp: {
			regexp: /^[\u4E00-\u9FA5\uF900-\uFA2D]+$/,
			message: '姓名只能是中文'
		  }
		}
	  },
	  mobile: {
		validators: {
		  notEmpty: {
			message: '手机号码不能为空'
		  },
		  regexp: {
			regexp: /^(1)[3-57-8][0-9]{9}$/,
			message: '手机号码有误'
		  },
		  remote: {
			url: '/user/check/mobile',//验证地址
			message: '手机号码已经注册',//提示消息
			delay :  1000//每输入一个字符，就发ajax请求，服务器压力还是太大，设置2秒发送一次ajax（默认输入一个字符，提交一次，服务器压力太大）
		  },
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