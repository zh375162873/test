$(function(){
    $(".format_code").each(function(){
        $(this).text(ddt_format_code($(this).text()));
    });

});
//***************
//checkbox全选
//isCheck 是否全选
//obj 需要全选的范围 比如$(".checkAll")中的所有复选框
//****************
checkAll = function(isCheck, obj)
{
    $(obj).find(':checkbox').prop('checked', isCheck);
};
//***************
//获取复选框的值
//****************
getCheck = function(options)
{
    var getVals = '';
    var obj = options.obj || "body";
    var attr = options.attr || "";
    var separate = options.separate || ",";
    var allCheck = $(obj).find('input:checked');
    
    $.each(allCheck, function(i, o) {
            var rcheck = '';
            if('' == attr)
            {
                rcheck =  $(o).val();
            }
            else
            {
                rcheck = $(o).attr(attr);
            }
            if (getVals == '')
            {

                getVals += rcheck;
            }
            else
            {
                getVals += separate + rcheck;
            }
    });
    return getVals;
};

/**
 * 表单提交统一验证函数
 * @param __form
 */
function ddt_validate(__form){
    var $form = $(__form);
    $.validator.setDefaults({
        ignore:"",//验证隐藏域，默认不验证
        highlight: function(element) {
            $(element).closest('.form-group').addClass('has-error');
        },
        unhighlight: function(element) {
            $(element).closest('.form-group').removeClass('has-error');
        },
        errorElement: 'div',
        errorClass: 'help-block with-errors',
        errorPlacement: function(error, element) {
            element.parents('.form-group').append(error);

        }
    });
    $form.validate();
}

/**
 * 格式化兑换码
 * @param _code
 * @returns {string|XML}
 */
function ddt_format_code(_code){
    return _code.replace(/(\d{4})/g,'$1 ').replace(/\s*$/,'');
}

/* 加入购物车 */
function addcart(goods_id,quantity,callbackfunc) {
    var url = SITE_URL+'/index.php/home/cart/add';
    quantity = parseInt(quantity);
    var code = $('input[name="goods_code"]').val();
    $.getJSON(url, {'goods_id':goods_id, 'quantity':quantity,'goods_code':code}, function(data) {
        if (data != null) {
            if (data.state == "true") {
                if(callbackfunc){
                    eval(callbackfunc + "(data)");
                }
                // 头部加载购物车信息
                if($(".goods_shopCart").length>0){
                    shoppingCartAnimate("button.shopCartBtn",".goods_shopCart img");
                }else{
                    shoppingCartAnimate("button.cart",".home_footer_hd");
                }
            } else {
                alert(data.msg);
            }
        }
    });
}

/*开始元素和结束元素*/
function shoppingCartAnimate(s,e){
    var btn = $(s);
    var sup = $(e).next();
    var start = btn[0].getBoundingClientRect();
    var url = btn.attr("data-url");
    var end = $(e)[0].getBoundingClientRect();
    var flyer = $('<img id="animateFlyImg" style="border-radius:100%;" src="'+url+'" width="40" height="40">');
    setTimeout(function(){
        var $fly = $("#animateFlyImg");
        if($fly.length>0){
            $fly.remove();
            get_cart_info();
        }
    },800);
    flyer.fly({
        start: {left: start.left+20,top: start.top},
        end: {left: end.left + 10,top: end.top + 10},
        onEnd: function() {
            //更新购物车数量
            get_cart_info();
            this.destory();
        }
    });
}