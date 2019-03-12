$(function(){
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
			regexp: /^(1)[3-57-8][0-9]{9}$/,
			message: '手机号码位11位'
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