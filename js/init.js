$(
    function () {
        $("#view").css("height", $(window).height() / 2 - 150);
        $(window).resize(function () {
            $("#view").css("height", $(window).height() / 2 - 150);
        });

        showAlert("若加载的页面为空白,请再次刷新网页!");

        $("#queryScore").click(function () {
            queryScoreByAjax();
        });

        $("#resetPwd").click(function () {
            resetPwdByAjax();
        });

        $("#verifyImg").attr('src', 'api/getVerifyImg.php?' + Math.random());

        $("#verifyImg").click(function () {
            $(this).attr('src', 'api/getVerifyImg.php?' + Math.random());
        });

        $(".needHide").hide();
        $("#queryFragment").show();

        $("a[href=#coder]").click(function () {
            showAlert("此网站作者是 编程154班 庄鹏远!<br/>闲暇之余,练习所做.<br/>By:曉莊 QQ:75769837");
            return false;
        });

        $("a[href=#query]").click(function () {
            $("#findFragment").hide();
            $("#queryFragment").show();
            return false;
        });

        $("a[href=#reset]").click(function () {
            $("#queryFragment").hide();
            $("#findFragment").show();
            return false;
        });

    }
);

function resetPwdByAjax() {
    if ($("form input[name=stuId]").val() == "" || $("form input[name=idCard]").val() == "" || $("form input[name=verifyCode]").val() == "") {
        showAlert("请输入必要信息!");
        return false;
    }

    $.ajax({
        type: "post",
        url: "api/resetPwd.php",
        async: true,
        dataType: "json",
        data: $("form").serialize(),
        cache: false,
        beforeSend: function () {
            $("#resetPwd").button('loading');
        },
        success: function (data) {
            if (data.status == -1) {
                showAlert("请刷新验证码后再次尝试重置密码!");
            } else if (data.status == 0) {
                showAlert("重置密码失败,请检查验证码,密码,身份证!");
            } else {
                showAlert("您的新的密码为:<br/><br/> <input type='text' value='" + data.result + "'><br/><br/>请复制您的密码!");
            }
        },
        error: function () {
            showAlert("重置密码失败!请稍后尝试!<br/>或者刷新验证码后再次尝试!");
        },
        complete: function () {
            $("#resetPwd").button('reset');
        }
    });

}

function queryScoreByAjax() {

    if ($("form input")[0].value == "" || $("form input")[1].value == "") {
        showAlert("请输入必要信息!");
        return false;
    }

    $.ajax({
        type: "post",
        url: "api/getScore.php",
        data: $("form").serialize(),
        dataType: "json",
        cache: false,
        beforeSend: function () {
            $("#queryScore").button('loading');
        },
        success: function (data) {
            if (data.status == 0) {
                showAlert("帐号或者密码不正确!");
            } else if (data.status == 1) {
                showScore(data);
            } else {
                showAlert("不在查分时间段!");
            }
        },
        error: function () {
            showAlert("查询成绩失败!请稍后尝试!");
        },
        complete: function () {
            $("#queryScore").button('reset');
        }
    });
}

function showAlert(content) {
    var $alert = $("#errorAlert");
    $alert.find(".am-modal-bd").html(content);
    $alert.modal();
};

function showScore(data) {
    $(".stuInfo:eq(0)").text("姓名: " + data.stuName);
    $(".stuInfo:eq(1)").text("学号: " + data.stuId);

    if (data.result.length == 0) {
        var $noHave = $(".needHide:eq(2)");
        $noHave.text("未查到成绩信息,请明天再来查询!");
        $noHave.show();
    } else {
        var $ret = $(".myTableS");
        $ret.find(".Score").remove();
        $.each(data.result, function (index, obj) {
            $ret.append('<tr class="Score"><td class="myTdS">' + obj.subject + '</td><td class="myTdS">' + obj.score + '</td></tr>');
        });
        $ret.show();
    }
    $("#showSource").modal({
        closeViaDimmer: false
    });
}