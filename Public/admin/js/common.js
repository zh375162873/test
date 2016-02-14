var emee = {};
//*********************
// ajax封装方法 
// 2014-5-9 用法 new ajaxLoading({type:"get"}); 有默认值的都可以为空
//*********************
emee.ajaxExec = function(options) {
    var self = this;
    //提交方式默认为post
    self.type = options.type || "POST";
    //数据类型默认为json
    self.dataType = options.dataType || "json";
    //参数默认为""
    self.data = options.data || "";
    //Url
    self.url = options.url;
    //成功函数
    self.success = options.success || function(data, ts) {
    };
    //完成函数
    self.complete = options.complete || function(xr, ts) {
    };
    //错误函数
    self.error = options.error || function(e) {
    };
    //loading图片元素
    self.loadImg = options.loadImg || "";
    //loading图片元素
    self.clickBtn = options.clickBtn || "";
    //错误显示元素
    self.showError = options.showError || "";
    //请求开始时需要执行的函数 可为空
    self.ajaxStart = options.ajaxStart || function() {
    };
    self.showLoading = function(isLoading)
    {
        if (isLoading)
        {
            if ('' != self.clickBtn)
            {
                $(self.clickBtn).hide();
            }
            if ('' != self.loadImg)
            {
                $(self.loadImg).show();
            }
        }
        else
        {
            if ('' != self.clickBtn)
            {
                $(self.clickBtn).show();
            }
            if ('' != self.loadImg)
            {
                $(self.loadImg).hide();
            }
        }
    };
    try {
        self.ajaxStart();
        self.showLoading(true);
        $.ajax({
            type: self.type,
            dataType: self.dataType,
            data: self.data,
            url: self.url,
            cache: false,
            success: function(data, ts) {
                options.success(data, ts);
                self.showLoading(false);
            },
            complete: function(xr, ts) {
                self.complete(xr, ts);
                self.showLoading(false);
            },
            error: function(xr, ts, e) {
                if ('' != self.showError)
                {
                    $(self.showError).text('服务器繁忙请稍后再试！:' + xr.statusText);
                }
                self.error(xr, ts, e);
                self.showLoading(false);
            }
        });
    } catch (e) {

    }
};

//******************
//单个元素验证 elId->元素ID
//例子 <input type="text" isValidate isRequired requiredMsg="用户名不能为空" />
//isValidate 此项需要验证
//isRequired 非空  requiredMsg非空空提示消息
//minLength 最小长度  minLengthMsg提示消息
//compare 比较 compareMsg 比较提示消息
//regular 正则 regularMsg 正则提示消息
//2014-4-12
//******************
InputValidate = function(elId) {
    if ($("#" + elId).is(":disabled"))
    {
        return true; //禁用的元素不需要验证
    }
    var elValue = $.trim($("#" + elId).val());
    var tipContent = $("#" + elId).attr("tipContent") || "";

    //$("#" + elId).siblings(errorElment).text("");
    var errorElment = $("span[for='" + elId + "']");
    $(errorElment).text("");

    //显示提示
    if (tipContent != '') {
        //$("#" + elId).siblings(errorElment).removeClass().addClass("alertInfo").text(tipContent);
        $(errorElment).removeClass().addClass("alertInfo").text(tipContent);
    }

    //非空
    var isRequired = $("#" + elId).attr("isRequired");
    if (isRequired != null && elValue == "") {
        $("#" + elId).focus();
        var requiredMsg = isRequired || "此项不能为空";
        //$("#" + elId).siblings(errorElment).removeClass().addClass("alertError").text(requiredMsg);
        $(errorElment).removeClass().addClass("alertError").text(requiredMsg);
        return false;
    }

    //最小长度  最大长度可以属性 maxLength 控制
    var minLength = $("#" + elId).attr("minLength");
    if (minLength != null && elValue.length < parseInt(minLength)) {
        $("#" + elId).focus();
        var minLengthMsg = $("#" + elId).attr("minLengthMsg") || "此项长度不正确";
        //$("#" + elId).siblings(errorElment).removeClass().addClass("alertError").text(minLengthMsg);
        $(errorElment).removeClass().addClass("alertError").text(minLengthMsg);
        return false;
    }

    //比较
    var compare = $("#" + elId).attr("compare");
    if (compare != null && compare != '') {
        if (elValue != $("#" + compare).val()) {
            $("#" + elId).focus();
            var compareMsg = $("#" + elId).attr("compareMsg") || "两次输入不一致";
            //$("#" + elId).siblings(errorElment).removeClass().addClass("alertError").text(compareMsg);
            $(errorElment).removeClass().addClass("alertError").text(compareMsg);
            return false;
        }
    }
    //正则
    //常用正则 regular = "^[0-9]*[1-9][0-9]*$"  正整数
    //^\\d+$ 非负整数（正整数 + 0）
    // ^[\u0391-\uFFE5]+$ 中文   腾讯QQ号：^[1-9]*[1-9][0-9]*$
    // ^-?\d+$  整数    E-mail地址：^[\w-]+(\.[\w-]+)*@[\w-]+(\.[\w-]+)+$ 
    // \d{15}|\d{18} 身份证
    //更多正则请查看 http://www.cnblogs.com/wenanry/archive/2010/09/06/1819552.html
    var regular = $("#" + elId).attr("regular");
    if (regular != null && regular != "") {
        var reg = new RegExp(regular);
        if (!reg.test(elValue)) {
            $("#" + elId).focus();
            var regularMsg = $("#" + elId).attr("regularMsg") || "此项输入格式不正确";
            //$("#" + elId).siblings(errorElment).removeClass().addClass("alertError").text(regularMsg);
            $(errorElment).removeClass().addClass("alertError").text(regularMsg);
            return false;
        }
    }


    return true;

};

//************************
//form提交验资 formId 表单ID 默认为MyForm
//目前针对Input onsubmit="return formValidate(this)"
//************************
formValidate = function(formEl) {
//    formId = "MyForm";
//    if (formEl != null) {
//        formId = $(formEl).attr("id");
//    }
    var arrayValidate = $(formEl).find("*[isValidate]");
    try {
        $(arrayValidate).each(function(i, el) {
            var id = $(el).attr("id");
            if (!InputValidate(id)) {
                //alert("验证失败" + id);
                throw "验证失败";
            }
        });
    } catch (e) {
        return false;
    }
    //如果存在函数beforeSubmit //则在提交前执行 返回false 则不提交表单
    if (typeof beforeSubmit != 'undefined' && $.isFunction(beforeSubmit)) {
        return beforeSubmit();
    }
    else {
        return true;
    }

};
//***************
//获取弹出框的样式
//****************
getAlertCss = function(msgType)
{
    var alertCss = "alert alert-info";
    switch (msgType) {
        case "WARNING":
            alertCss = "alert";
            break;
        case "SUCCESS":
            alertCss = "alert  alert-success";
            break;
        case "FAILURE":
            alertCss = "alert  alert-error";
            break;

    }
    return alertCss;
};

//***************
//弹出框消息
//****************
showAlert = function(options)
{
    var msg = options.msg || "";
    var autoClose = options.autoClose || true; //点击后是否自动关闭
    var okFun = options.okFun || function() {
    };
    var alertCss = options.alertCss || "INFO";
    if (msg != null && msg != '') {
        //自动判断成功失败
        if (msg.indexOf("成功") > -1) {
            alertCss = "SUCCESS";
        }
        if (msg.indexOf("失败") > -1) {
            alertCss = "FAILURE";
        }
    }
    var cssName = getAlertCss(alertCss);

    if ($('#systemAlert').length <= 0)
    {
        //data-dismiss="modal" aria-hidden="true"
        var alertHtml = '<div id="systemAlert" style="z-index:1200" class="modal hide in soalert" tabindex="-1" role="dialog" aria-labelledby="systemAlertLabel" aria-hidden="true">';
        alertHtml += '<div class="modal-header">';
        alertHtml += '<button type="button" id="closeAlertBtn" class="close" >×</button>';
        alertHtml += '<h3 id="systemAlertLabel">系统提示</h3>';
        alertHtml += '</div>';

        alertHtml += '<div class="modal-body">';
        alertHtml += '<div id="showAlertContent" class="' + cssName + '">';
        alertHtml += '<h4> ' + msg + '</h4>';
        alertHtml += '</div>';
        alertHtml += '</div>';
        alertHtml += '<div class="modal-footer">';
        alertHtml += '<button id="showAlertBtn" type="button"  class="btn btn-primary">确定</button>';
        alertHtml += '</div>';
        alertHtml += '</div>';
        $('body').append(alertHtml);
    }
    else
    {
        $("#showAlertContent").removeClass().addClass(cssName).html('<h4> ' + msg + '</h4>');
    }
    $("#showAlertBtn,#closeAlertBtn").unbind("click").click(function() {
        if (autoClose) {
            $("#systemAlert").modal('hide');
        }
        okFun();
    });
    $("#systemAlert").modal('show');
};

//***************
//确认框消息
//****************
showConfirm = function(options)
{
    var msg = options.msg || "";
    var alerCss = options.alerCss || "INFO";
    var autoClose = options.autoClose || false; //点击后是否自动关闭
    var okFun = options.okFun || function() {
    };
    var cssName = getAlertCss(alerCss);

    if ($('#systemConfirm').length <= 0)
    {
        //data-dismiss="modal" aria-hidden="true"
        var alertHtml = '<div id="systemConfirm" style="z-index:1100" class="modal hide in soalert" tabindex="-1" role="dialog" aria-labelledby="systemConfirmLabel" aria-hidden="true">';
        alertHtml += '<div class="modal-header">';
        alertHtml += '<button type="button" class="close" data-dismiss="modal" aria-hidden="true" >×</button>';
        alertHtml += '<h3 id="systemConfirmLabel">系统提示</h3>';
        alertHtml += '</div>';
        alertHtml += '<div class="modal-body">';
        alertHtml += '<div  id="showConfirmContent" class="' + cssName + '">';
        alertHtml += '<h4> ' + msg + '</h4>';
        alertHtml += '</div>';
        alertHtml += '</div>';
        alertHtml += '<div class="modal-footer">';
        alertHtml += '<button class="btn btn_no" data-dismiss="modal" aria-hidden="true">取消</button>';
        alertHtml += '<button id="showConfirmBtn" type="button"  class="btn btn-primary">确定</button>';
        alertHtml += '</div>';
        alertHtml += '</div>';
        $('body').append(alertHtml);
    }
    else
    {
        $("#showConfirmContent").removeClass().addClass(cssName).html('<h4> ' + msg + '</h4>');
    }

    $("#showConfirmBtn").unbind("click").click(function() {
        if (autoClose) {
            $("#systemConfirm").modal('hide');
        }
        okFun();
    });
    $("#systemConfirm").modal('show');
};

//***************
//关闭确认框消息
//****************
closeConfirm = function()
{
    if ($('#systemConfirm').length <= 0)
    {
        $("#systemConfirm").modal('hide');
    }
};

//***************
//弹出消息框并跳转
//****************
showAlertJump = function(msg,jumpUrl)
{
    showAlert({msg: msg, okFun: function() {
                location.href = jumpUrl;
        }
    });
};
//***************
//弹出消息框并刷新当前页面
//****************
showAlertRefresh = function(msg)
{
    showAlert({msg: msg, okFun: function() {
                location.reload();
        }
    });
};


//***************
//弹出框显示内容
//****************
showInfo = function(options)
{
    var msg = options.msg || "";
    var title = options.title || "查看信息";
    var autoClose = options.autoClose || true; //点击后是否自动关闭
    var okFun = options.okFun || function() {
    };

    if ($('#systemInfo').length <= 0)
    {
        //data-dismiss="modal" aria-hidden="true"
        var alertHtml = '<div id="systemInfo" style="z-index:1200" class="modal hide in soalert" tabindex="-1" role="dialog" aria-labelledby="systemInfoLabel" aria-hidden="true">';
        alertHtml += '<div class="modal-header">';
        alertHtml += '<button type="button" id="closeInfoBtn" class="close" >×</button>';
        alertHtml += '<h3 id="systemInfoLabel">' + title + '</h3>';
        alertHtml += '</div>';

        alertHtml += '<div class="modal-body">';
        alertHtml += '<div id="showInfoContent" >';
        alertHtml += '<h4> ' + msg + '</h4>';
        alertHtml += '</div>';
        alertHtml += '</div>';
        alertHtml += '<div class="modal-footer">';
        alertHtml += '<button id="showInfoBtn" type="button"  class="btn btn-primary">确定</button>';
        alertHtml += '</div>';
        alertHtml += '</div>';
        $('body').append(alertHtml);
    }
    else
    {
        $("#showInfoContent").html('<h4> ' + msg + '</h4>');
        $("#systemInfoLabel").html(title);
    }
    $("#showInfoBtn,#closeInfoBtn").unbind("click").click(function() {
        if (autoClose) {
            $("#systemInfo").modal('hide');
        }
        okFun();
    });
    $("#systemInfo").modal('show');
};


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

//***************
//菜单选中
//****************
menuActive = function(menuName)
{
    $("a[menuName='"+ menuName +"']").addClass("active").siblings('a').removeClass("active");
};


//功能设置页面用到弹出多个modal
$.fn.modal.Constructor.prototype.enforceFocus = function() {
};
/**
 * 弹出框
 */
showPageForLst = function(url, title, width, height) {
    var d = dialog({
        title: title,
        url: url,
        width: width,
        height: height,
        onshow: function() {
        },
        oniframeload: function() {
        },
        onclose: function() {
            location.reload();
        },
        onremove: function() {
        }
    });
    d.showModal();
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