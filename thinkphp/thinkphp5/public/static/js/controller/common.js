/* =========================================================
 * controller/wechat.js
 * =========================================================
 * PC端应用
 * ========================================================= */

/**
 * ajax登录回调
 */
function ajaxLoginCallback(obj){
	if (obj != ''){
		loginShowUser(obj);
		window.localStorage.setItem("yl_user", JSON.stringify(obj));//设置客户端缓存
	} else {
		window.localStorage.removeItem("yl_user");
	}
}

/**
 * ajax登录
 */
function ajaxLogin(){
  var str_user = window.localStorage.getItem('yl_user');
  if (str_user == undefined || str_user == null || str_user == ''){	
	$.ajax({
	  url:"/user/index/auth"
	}).then(function(data){
		ajaxLoginCallback(data);
	});
  } else {
	 loginShowUser(JSON.parse(str_user));
  }
} 

/**
 * 登录成功
 */
function loginShowUser(user){
	var name = user.uname; 
	if (user.real_name != undefined && user.real_name != null && user.real_name != '') name = user.real_name;
	$("#userEntranceAjax").hide();
	var html = '<div class="user">'+name+' [<a href="javascript:logout()" class="logout">安全退出</a>]</div>';
	$("#topInfoContainer").append(html);
}

/**
 * 安全退出
 */
function logout(){
  window.localStorage.removeItem("yl_user");
  //$.cookie('yl_auth', '');
  window.location.href = '/user/index/logout';
}  

/**
 * 报名
 */
function signup(id){
  /*验证用户是否登录*/
  $.ajax({
	url:"/user/index/auth"
  }).then(function(data){
	  if (data == ''){
		//未登录
		$(".signup-content").hide();
		$("#loginSignupContent").show();
		$("#loginSignupJob").val(id);
		$("#signupModal").modal();
	  } else {
	    //已登录
		/*验证用户是否已经报名*/
		ajaxSignupCheck(id).then(function(data){
		  $(".signup-content").hide();
		  if (data==1) $("#signupMessage").show();
		  else $("#signupContent").show();
		  $("#signupJob").val(id);
		  $("#signupModal").modal();
		});
	  }
  });	
}

/**
 * 验证用户是否报名成功
 */
function ajaxSignupCheck(id){
  return $.ajax({
	url:"/user/index/checkSignup",
	data:{job_id:id}
  })
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

/**
 * 回到顶部
 */
function scroll2top_shortcut() { 
	var scrooll2top = $("#goTopBtn"); 
	//var shortcut = $("#shortcut"); 
	function getScrollTop() { 
		return document.documentElement.scrollTop + document.body.scrollTop;
	} 
	window.onscroll = function() { 
		getScrollTop() > 200 ? scrooll2top.show(): scrooll2top.hide();
	}
	scrooll2top.click(function(){
	 	window.scrollTo(0,0);
	});
}

function show_qrcode(){
  $("#towCode").toggle();
}

function showLoginModal(){
  $('#registerContent').hide();		
  $('#loginContent').show();
}

function showRegisterModal(){
  $('#loginContent').hide();	
  $('#registerContent').show();
}

function ajaxInviteCallback(response){
  var str = '';
  $.each(response, function(index, value){
    str += '<tr><td>';
	if(index==0) str += '<span class="num first">1</span>';
	else if(index==1) str += '<span class="num second">2</span>';
	else if(index==2) str += '<span class="num third">3</span>';
	else str += '<span class="num">'+(parseInt(index)+1)+'</span>'; 
    str += '</td><td><img src="'+value.pictures+'"/></td><td>'+value.user_name+'</td><td>￥'+value.amount+'</td></tr>';
  });
  $("#inviteTable tbody").html(str);
}

function sendSms(obj, mobileObj){
  var mobile = $('#'+mobileObj).val();
  $(obj).next().css('opacity', 1);  
  if (mobile=='') {
	$(obj).next().find(".tooltip-inner").html('请输入手机号码');
  } else {
	$.get('/message/sms/sendRegisterCode',{mobile:mobile},function(response){
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