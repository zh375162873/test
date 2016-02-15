/**
 * Created by Administrator on 2015/8/18.
 */
var initIndex = 0;
function getTemplate(t,i,id){
    var start = "<div nctype='special_item' class='special-item "+id+" unusable' data-item-id='"+i+"'>" +
        "<div class='item_type'>"+id+"</div>" +
        "<div id='item_edit_content'>";
    var end = "</div><div class='handle'>" +
        "<a nctype='btn_move_up' href='javascript:;'><i class='icon-arrow-up'></i>上移</a>"+
        "<a nctype='btn_move_down' href='javascript:;'><i class='icon-arrow-down'></i>下移</a>"+
        "<a nctype='btn_usable' data-item-id='"+i+"' href='javascript:;'><i class='icon-off'></i>启用</a>"+
        "<a nctype='btn_edit_item' data-item-id='"+i+"' href='javascript:;'><i class='icon-edit'></i>编辑</a>"+
        "<a nctype='btn_del_item' data-item-id='"+i+"' href='javascript:;'><i class='icon-trash'></i>删除</a>" +
        "</div>" +
        "</div>";

    var templateGg = start+
        "<div class='index_block adv_list'>" +
        "<div nctype='item_content' class='content'>" +
        "</div>"+
        "</div>"+end;
    var templateA = start+
        '<div class="index_block home1">'+
        '<div class="title">'+
        '<span></span>'+
        '</div>'+
        '<div nctype="item_content" class="content">'+
        '<div nctype="item_image" class="item"> <img nctype="image" src="http://localhost/yii/web/temp/res/default_image.gif" alt="">'+
        '</div>'+
        '</div>'+
        '</div>'+end;
    var templateB = start+
        '<div class="index_block home2">'+
        '<div class="title">'+
        '<span></span>'+
        '</div>'+
        '<div class="content">'+
        '<div class="home2_1">'+
        '<div nctype="item_image" class="item"> <img nctype="image" src="http://localhost/yii/web/temp/res/default_image.gif" alt="">'+
        '</div>'+
        '</div>'+
        '<div class="home2_2">'+
        '<div class="home2_2_1">'+
        '<div nctype="item_image" class="item"> <img nctype="image" src="http://localhost/yii/web/temp/res/default_image.gif" alt="">'+
        '</div>'+
        '</div>'+
        '<div class="home2_2_2">'+
        '<div nctype="item_image" class="item"> <img nctype="image" src="http://localhost/yii/web/temp/res/default_image.gif" alt="">'+
        '</div>'+
        '</div>'+
        '</div>'+
        '</div>'+
        '</div>'+end;
    var templateC = start+
        "<div class='index_block home3'>" +
        "<div nctype='item_content' class='content'>" +
        "</div>"+
        "</div>"+end;
    var templateD = start+
        '<div class="index_block home2">'+
        '<div class="title">'+
        '<span></span>'+
        '</div>'+
        '<div class="content">'+
        '<div class="home2_2">'+
        '<div class="home2_2_1">'+
        '<div nctype="item_image" class="item"> <img nctype="image" src="http://localhost/yii/web/temp/res/default_image.gif" alt="">'+
        '</div>'+
        '<div class="home2_2_2">'+
        '<div nctype="item_image" class="item"> <img nctype="image" src="http://localhost/yii/web/temp/res/default_image.gif" alt="">'+
        '</div>'+
        '</div>'+
        '</div>'+
        '</div>'+
        '<div class="home2_1">'+
        '<div nctype="item_image" class="item"> <img nctype="image" src="http://localhost/yii/web/temp/res/default_image.gif" alt="">'+
        '</div>'+
        '</div>'+
        '</div>'+
        '</div>'+end;
    var templateSp = start+
        "<div class='index_block goods-list'>" +
        "<div nctype='item_content' class='content'>" +
        "</div>"+
        "</div>"+end;
    var templates ={
        adv_list:templateGg,
        home1:templateA,
        home2:templateB,
        home3:templateC,
        home4:templateD,
        goods:templateSp
    };
    return templates[t];
}
window.templates = {};
function updateSort(){
    var list = $("[nctype='special_item']");
    var arrs = [];
    list.each(function(index,item){
        arrs.push($(item).attr("data-item-id"));
    });
    //alert(arrs.join(","));
}
$(function(){
    $("[nctype='btn_add_item']").on("click",function(){
        var type = $(this).attr("data-module-type");
        if(type=="adv_list"&&$('.adv_list').filter("[nctype='special_item']").length>0){
            alert("广告板块只能添加一个！");
            return;
        }else{
            var dom = getTemplate(type,initIndex,type);
            $(".item-list").append(dom);
            window.templates[initIndex] = type;
            initIndex++;
        }
    });

    $(".item-list").on("click","[nctype='btn_move_up']",function(){
        var $curDom = $(this).parents('[nctype="special_item"]');
        var $prev = $curDom.prev();
        if($prev.length>0){
            $prev.before($curDom);
            updateSort();
        }else{
            return;
        }
    });
    $(".item-list").on("click","[nctype='btn_move_down']",function(){
        var $curDom = $(this).parents('[nctype="special_item"]');
        var $next = $curDom.next();
        if($next.length>0){
            $next.after($curDom);
            updateSort();
        }else{
            return;
        }
    });
    $(".item-list").on("click","[nctype='btn_usable']",function(){
        if($(this).children("i").hasClass("icon-off")){
            $(this).html('<i class="icon-on"></i>启用');
            $(this).parents('[nctype="special_item"]').removeClass("unusable").addClass("usable");
        }else{
            $(this).html('<i class="icon-off"></i>禁用');
            $(this).parents('[nctype="special_item"]').removeClass("usable").addClass("unusable");
        }
    });
  /*  $(".item-list").on("click","[nctype='btn_edit_item']",function(){
        var opId = $(this).parents('[nctype="special_item"]').attr("data-item-id");
        window.open("./edit.html?opId="+opId);
    });*/
    $(".item-list").on("click","[nctype='btn_del_item']",function(){
        var id = $(this).parents('[nctype="special_item"]').attr("data-item-id");
        delete window.templates[id];
        $(this).parents('[nctype="special_item"]').remove();
    });
});
