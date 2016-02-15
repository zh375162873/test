// JavaScript Document

//在线客服
$(document).ready(function(e) {
  
/*个人菜单类*/	
	//个人中心菜单弹出
	$(".yonghu").siblings().hide();
	$(".admin_user").on("mouseover",function(){
		$(".yonghu a").css({"background":"#f45749"});
		$(".yonghu").siblings().show();
	});
	$(".admin_user").on("mouseout",function(){
		$(".yonghu a").css({"background":"#f45749"});
		$(".yonghu").siblings().hide();
	});
	
/**/

//表格的隔行换色
	$(".table tr:odd").css({"background":"#edf4fd"});
	
	
//选择模板通用
	$(".weileft ul li a").on("click",function(){
	 	$(this).children("span").show();
		$(this).addClass("active").parent().siblings().children("a").removeClass("active");
		$(this).children("span").addClass("active");
		$(this).parent().siblings().children("a").children("span").removeClass("active");
		$(this).parent().siblings().children("a").children("span").hide();	 
	});
	$(".weileft ul li a").on("mouseover",function(){
		$(this).children("span").show();
	});
	$(".weileft ul li a").on("mouseout",function(){
		if(!$(this).children("span").hasClass("active"))
		{
		 $(this).children("span").hide();
		}
	});
	
//功能模块
	$(".off_or_on a").on("click",function(){
		if($(this).hasClass("active")){
			$(this).removeClass("active");
		}else{
			$(this).addClass("active");
		}
	});	
//认证上网开启或关闭
	$(".renzheng_table tr th span a").on("click",function(){
		if($(this).hasClass("active")){
			$(this).removeClass("active");
                        var ob = $(this).attr("id");
                        var hd = ob + "_hidden";
                        $("#"+hd).val(0);
		}else{
			$(this).addClass("active");	
                        var ob = $(this).attr("id");
                        var hd = ob + "_hidden";
                        $("#"+hd).val(1);
		}
	});	
});

//简单的删除确认方法
function delete_confirm(delete_url){
    if (confirm("确认要删除吗？")){
        window.location.href = delete_url;
        return true;
    }  else  { 
        return false;
    };
}