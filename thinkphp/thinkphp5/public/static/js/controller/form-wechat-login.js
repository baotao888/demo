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
			url: '/user/check/notmobile',//验证地址
			message: '手机号码未注册',//提示消息
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
			url: '/message/sms/checklogincode',//验证手机短信验证码是否正确
			message: '验证码错误',//提示消息
			delay : 800,
			data: function(validator) {
			   return {
				   mobile: $('#loginMobile').val()
			   };
			}
		  }
		}
	  }
	}
  });
});