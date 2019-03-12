$(function(){
  //用户登录验证
  ajaxLogin();
  //登录注册入口
  $("#btnRegisterModal").on('click', function(){
	 $("#loginModal").modal();
	 showRegisterModal();
  });
  $("#btnLoginModal").on('click', function(){
	 $("#loginModal").modal();
	 showLoginModal();
  });
  $("#signup2LoginBtn").on('click', function(){
	 $("#signupModal").modal('hide'); 
	 $("#loginModal").modal();
	 showLoginModal();
  });
  //快捷方式
  //scroll2top_shortcut();
  $(".shortcut .qbtn").on('click', function(){
	if ($(this).attr('data-target')!=undefined && $(this).attr('data-target').length>0){
	  $("#"+$(this).attr('data-target')).siblings(".popup").hide();
	  $("#"+$(this).attr('data-target')).toggle();
	} 
  });
  //邀请
  $("#popupInvite").on('click', function(){
	$.ajax({
	  url:"/user/index/inviteList"
	}).then(function(data){
		ajaxInviteCallback(data);
	}); 
    $("#inviteModal").modal();
  });
  //加载用户城市
  switchShowCity();
  //切换城市
  $("#switchCity a").on('click', function(){
	switchCity($(this).html());
	$(".top-city-list").toggle();
  });
  $("#topCityList").on('click', function(){
	 $(".top-city-list").toggle();  
  });
});