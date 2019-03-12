// [微信端绑定验证]
$(function () {	
  $('#registerForm').bootstrapValidator({
  　 message: '请输入……',
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
			regexp: /^(1)[3,4,5,6,7,8][0-9]{9}$/,
			message: '手机号码为11位'
		  },
		  remote: {
			url: '/user/check/mobile',//验证手机号码是否已注册
			message: '手机号码已经注册，立即登录吧',//提示消息
			delay : 800//每输入一个字符，就发ajax请求，服务器压力还是太大，设置2秒发送一次ajax（默认输入一个字符，提交一次，服务器压力太大）
		  }
		}
	  },
	  password: {
	    validators: {
		  notEmpty: {
			message: '密码不能为空'
		  },
		  stringLength: {
		    min: 8,
			message: '密码长度不能小于8位'
		  },
		  different: {
			field: 'mobile',
			message: '密码不能太简单'
		  }
		}
	  },
	  re_password: {
		validators: {
		  notEmpty: {
			message: '确认密码不能为空'
		  },
		  identical: {
			field: 'password',
			message: '两次输入密码不一致'
		  }
		} 
	  },
	  userservice: {
		validators: {
		  notEmpty: {
			message: '请选择用户协议'
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
				   mobile: $('#registerMobile').val()
			   };
			}
		  }
		}
	  }
	}
  });
  $('#loginForm').bootstrapValidator({
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
			url: '/user/check/notmobile',
			message: '此手机号码还未注册，立即去注册吧',
			delay : 800
		  }
		}
	  },
	  password: {
	    validators: {
		  notEmpty: {
			message: '密码不能为空'
		  },
		  stringLength: {
		    min: 8,
			message: '密码有误'
		  }
		}
	  }
	}
  });
  $('#loginSignupForm').bootstrapValidator({
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
	  userservice: {
		validators: {
		  notEmpty: {
			message: '请选择用户协议'
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
				   mobile: $('#signupMobile').val()
			   };
			}
		  }
		}
	  }
	}
  });
  $('#signupForm').bootstrapValidator({
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
  $('#inviteForm').bootstrapValidator({
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
			regexp: /^(1)[3-57-8][0-9]{9}$/,
			message: '手机号码为11位'
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
		  }
		}
	  }
	}
  });
});
