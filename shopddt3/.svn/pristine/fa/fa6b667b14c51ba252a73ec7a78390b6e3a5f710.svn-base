/**
 * Created by Administrator on 2015/12/29.
 */
var DDT = (function(){
    //var delay = 2000;
    var fun = {};
    var mask;
    fun.alert = function(str){
        mask = document.createElement("div");
        mask.setAttribute("id","ddtDialog_mask");
        var _html = '<div class="ddtd_alert alert_active"><p>'+str+'</p></div>';
        mask.innerHTML = _html;
        mask.onclick = function(){
            document.body.removeChild(this);
            this.onclick = null;
            mask = null;
        };
        document.body.appendChild(mask);
    };
    fun.confirm = function(options){
        var default_options = {ok:"确定",cancel:"取消"};
        if(options){
            if(options.ok&&options.ok.text){
                default_options.ok = options.ok.text;
            }
            if(options.cancel&&options.cancel.text){
                default_options.cancel = options.cancel.text;
            }
        }
        mask = document.createElement("div");
        mask.setAttribute("id","ddtDialog_mask");
        var _html = '<div class="ddtd_confirm confirm_active"><h3>提示</h3><p>'+options.text+'</p><div class="ddtd_btn">';
        if(options.ok&&options.cancel){
            _html += '<button type="button">'+default_options.ok+'</button>' +
                '<button type="button" class="dialogCancel">'+default_options.cancel+'</button>';
        }else if(options.ok){
            _html += '<button style="width:100%;" type="button">'+default_options.ok+'</button>';
        }else{
            _html += '<button style="width:100%;" type="button">确定</button>';
        }
        _html += '</div></div>';
        mask.innerHTML = _html;
        mask.onclick = function(e){
            var tag = e.target;
            if(tag.tagName=="BUTTON"){
                if(tag.className!="dialogCancel"&&options.ok&&options.ok.callback){
                    options.ok.callback();
                }
                if(tag.className=="dialogCancel"&&options.cancel&&options.cancel.callback){
                    options.cancel.callback();
                }
                document.body.removeChild(this);
                this.onclick = null;
                mask = null;
            }
        };
        document.body.appendChild(mask);
    };
    fun.showLoad = function(){
        var ddt_load = document.getElementById("ddt_load");
        if(ddt_load){
            ddt_load.style.display = "block";
        }else{
            var _load = '<div class="spinner">' +
                '<div class="rect1"></div><div class="rect2"></div>' +
                '<div class="rect3"></div><div class="rect4"></div><div class="rect5"></div>' +
                '<p>加载中...</p>' +
                '</div>';
            var loadBg = document.createElement("div");
            loadBg.setAttribute("id","ddt_load");
            loadBg.setAttribute("class","ddt_load");
            loadBg.innerHTML = _load;
            document.body.appendChild(loadBg);
        }
    };
    fun.closeLoad = function(){
        var ddt_load = document.getElementById("ddt_load");
        if(ddt_load){
            ddt_load.style.display = "none";
        }
    };
    return fun;
})();
