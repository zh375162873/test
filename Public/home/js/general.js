//简单的删除确认方法
function delete_confirm(delete_url){
    if (confirm("确认要删除吗？")){
        window.location.href = delete_url;
        return true;
    }  else  { 
        return false;
    };
}