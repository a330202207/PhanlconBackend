var Login = function () {
    var handleLogin = function () {
        $("#submit").click(function () {
            var username = $("#username").val();
            var password = $("#password").val();

            if (username == '') {
                layer.msg('请填写用户名!', {icon: 2});
                return false;
            }

            if (password == '') {
                layer.msg('请填写密码!', {icon: 2});
                return false;
            }

             $.ajax({
                 url : "/admin/login/login",
                 type : "post",
                 dataType : "json",
                 data: {
                     username: username,
                     password: password
                 },
                 success : function(res) {
                     if (res.status == 0) {
                         window.location.href = '/admin/index/index';
                     } else {
                         layer.msg(res.msg, {icon: 2});
                         return false;
                     }
                 }
             });
        });
    }

    return {
        init: function () {
            handleLogin();
        }
    };

}();

jQuery(document).ready(function () {
    Login.init();
});