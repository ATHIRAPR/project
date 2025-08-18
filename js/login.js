document.getElementById("login_btn").addEventListener(
    'click',
    function () {
        if (validation()) {
            let data = {
                mail: document.getElementById("mail").value,
                password: document.getElementById("password").value
            };
            login(data);
        }

    }
);

function validation() {
    document.getElementById("errmsg").textContent = "";

    if (!document.getElementById("mail").value) {
        document.getElementById("errmsg").textContent = "メールアドレスを入力してください。";
        return false;
    }

    if (!document.getElementById("password").value) {
        document.getElementById("errmsg").textContent = "パスワードを入力してください。";
        return false;
    }

    if (!ckMail(document.getElementById("mail").value)) {
        document.getElementById("errmsg").textContent = "メールアドレスの形式が異なります。";
        return false;
    }

    return true;
}

function login(data) {
    $.ajax({
        type: "post",
        url: "https://cums-api.oly.jp/api/login",
        data: data,
        dataType: "json",
        beforeSend: function () {
            // Loading画像表示
            $("#loaderimg").show();
        }
    })
        .done(function (response, textStatus) {
            if (response.status.message === "success") {
                document.cookie = "token=" + response.data.token + "; max-age=3600";
                document.cookie = "username=" + response.data.username + "; max-age=3600";
                document.cookie = "userid=" + response.data.userid + "; max-age=3600";
                document.cookie = "permissiontype=" + response.data.permissiontype + "; max-age=3600";
                if (response.data.permissiontype === 1) {
                    document.location = "admin.html";
                } else {
                    document.location = "operator.html";
                }
            } else {
                document.getElementById("errmsg").textContent = "入力内容を見直してください。";
            }
        })
        .fail(function (response, textStatus) {
            document.getElementById("errmsg").textContent = "システムエラーが発生しました。";
        })
        .always(function () {
            $("#loaderimg").hide();
        });
}