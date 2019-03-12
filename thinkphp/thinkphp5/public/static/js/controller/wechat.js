/* =========================================================
 * controller/wechat.js
 * =========================================================
 * 微信应用
 * ========================================================= */

/**
 * 绑定验证
 */
function ajaxBind(){
  var str_user = window.localStorage.getItem('yl_user');
  if (str_user == undefined || str_user == null || str_user == ''){	
	$.ajax({
	  url:"/wechat/user/auth"
	}).then(function(data){
		ajaxBindCallback(data);
	});
  } else {
	 bindShowUser(JSON.parse(str_user));
  }
} 
 
/**
 * 绑定回调
 */
function ajaxBindCallback(obj){
	if (obj != ''){
		bindShowUser(obj);
		window.localStorage.setItem("yl_user", JSON.stringify(obj));//设置客户端缓存
	} else {
		$("#userEntranceAjax").on('click', function(){
			window.location.href = '/wechat/user/bind';
		});
		window.localStorage.removeItem("yl_user");
	}
}

/**
 * 绑定成功
 */
function bindShowUser(user){
	var real_name = user.real_name==null?user.uname:user.real_name;
	$("#userEntranceAjax").hide();
	var html = '<div class="user">'+real_name+'</div>';
	$("#topInfoContainer").append(html);
}

/**
 * 切换城市
 */
function switchCity(city){
  if (city != ''){
	window.localStorage.setItem("yl_city", JSON.stringify(city));//设置客户端缓存
	switchShowCity();	
  } else {
	  window.localStorage.setItem("yl_city", '昆山');//设置客户端缓存
  }
}

/**
 * 显示城市
 */
function switchShowCity(){
	var city = window.localStorage.getItem("yl_city");
	if (city != undefined && city != null && city != '') $("#currentCityContainer").html(JSON.parse(city));
}

function sendSms(obj, mobileObj){
  var mobile = $('#'+mobileObj).val();
  $(obj).next().css('opacity', 1);  
  if (mobile=='') {
	$(obj).next().find(".tooltip-inner").html('请输入手机号码');
  } else {
	var url = '/message/sms/sendRegisterCode';
	if (mobileObj == 'loginMobile') url = '/message/sms/sendLoginCode';  
	$.get(url, {mobile:mobile}, function(response){
		if (response.status==1){
			sendCountdown(obj);//倒计时
		}
		$(obj).next().find(".tooltip-inner").html(response.message);
	});
  }
}

function sendCountdown(obj) {  
  if (countdown == 0) {  
	  $(obj).attr("disabled",false);
	  $(obj).val("重新获取");
	  countdown = 30;  
	  return;  
  } else {    
	  $(obj).attr("disabled", true);
	  $(obj).val("已发送(" + countdown + ")");  
	  countdown--;  
  }  
  setTimeout(function() {sendCountdown(obj)}, 1000);
}

function ajaxMarket() {
  $.ajax({
	url:"/wechat/web/market"
  }).then(function(data){
	var str_code = window.localStorage.getItem('yl_marketcode');
	if (str_code == undefined || str_code == null || str_code == ''){	
	  if (data != '') {
	    window.localStorage.setItem("yl_marketcode", data);//设置客户端缓存
	  }
	} else {
	  if (data == '') {
		$.ajax({url:"/wechat/web/market?ylmcode="+str_code});//带参数重新请求设置服务端缓存
	  } else if (data != str_code) {
	    window.localStorage.setItem("yl_marketcode", data);//更新客户端缓存
	  }	
	}
  });	
}

$(".shortcut_qrcode").click(function(){
	if($('.qrcode_img').is(':hidden')){
		$('.qrcode_img').show();
	}else{
		$('.qrcode_img').hide();
	}
})
