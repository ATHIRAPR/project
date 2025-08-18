// 日付をYYYY-MM-DDの書式で返すメソッド
function formatDate(dt) {
    var y = dt.getFullYear();
    var m = ('00' + (dt.getMonth() + 1)).slice(-2);
    var d = ('00' + dt.getDate()).slice(-2);
    return (y + '/' + m + '/' + d);
}

function ckDate(strDate) {
    if (!strDate.match(/^\d{4}\/\d{2}\/\d{2}$/)) {
        return false;
    }
    var y = strDate.split("/")[0];
    var m = strDate.split("/")[1] - 1;
    var d = strDate.split("/")[2];
    var date = new Date(y, m, d);
    if (date.getFullYear() != y || date.getMonth() != m || date.getDate() != d) {
        return false;
    }
    return true;
}

function ckMail(value) {
    var reg = /^[A-Za-z0-9]{1}[A-Za-z0-9_.-]*@{1}[A-Za-z0-9_.-]{1,}.[A-Za-z0-9]{1,}$/;
    if (reg.test(value)) {
        return true;
    } else {
        return false;
    }
}

function ckPath(value) {
    if (value.endsWith("/") || value.endsWith(".html")) {
        return true;
    } else {
        return false;
    }
}

function getCookieValue(key) {
    cookies = document.cookie; //全てのcookieを取り出して
    var cookiesArray = cookies.split(';'); // ;で分割し配列に

    for (var c of cookiesArray) { //一つ一つ取り出して
        var cArray = c.split('='); //さらに=で分割して配列に
        if (cArray[0].indexOf(key) != -1) {  // 取り出したいkeyと合致したら
            return cArray[1];
        }
    }
    return false;
}

function hot_tooltipstererr(obj, id, msg) {
    obj.className = obj.className + " htInvalid valueerr";
    obj.id = "err" + id;
    $(obj).tooltipster();
    $(obj).tooltipster("content", $('<span>' + msg + '</span>'));
    $(obj).tooltipster('enable');
}

function hot_tooltipsterreset(obj, id) {
    if (obj.id === "err" + id) {
        $(obj).tooltipster('disable');
        obj.id = "";
        obj.className = obj.className.replace("htInvalid", "").replace("valueerr", "");
    }
}


function init() {
    document.getElementById("save_btn").disabled = true;

    // 棚卸担当者一覧ページ以外の場合
    if (!location.pathname.match(/operator.html/)) {
        // 管理者権限ではない場合、ログイン画面へ戻す
        if (getCookieValue("permissiontype") !== "1") {
            document.location = "index.html";
        }
    }
    let username = getCookieValue("username");
    document.getElementById("username").textContent = username;
}

function end() {
    // Cokkieの有効期限をリセット
    document.cookie = "token=" + getCookieValue("token") + "; max-age=3600";
    document.cookie = "username=" + getCookieValue("username") + "; max-age=3600";
    document.cookie = "userid=" + getCookieValue("userid") + "; max-age=3600";
    document.cookie = "permissiontype=" + getCookieValue("permissiontype") + "; max-age=3600";

    document.getElementById("save_btn").disabled = false;
    $("#load").hide();
}

function setAlert(inventoryduedate) {
    var today = new Date();
    let termDay = (new Date(inventoryduedate) - today) / 86400000;
    let result = "";
    if (termDay >= 6) {
        result = "<span class='circle_green'></span>";
    } else if (termDay < 6 && termDay >= 0) {
        result = "<span class='circle_yellow'></span>";
    } else if (termDay < 0) {
        result = "<span class='circle_red'></span>";
    }

    return result;
}

function downloadArrayAsCsv(array, fileName) {
    var UTF_8_BOM = '%EF%BB%BF';
    var csv = [];

    array.forEach(function (row, index) {
        csv.push(row.join(','));
    });

    csv = csv.join('\n');
    var data = 'data:text/csv;charset=utf-8,' + UTF_8_BOM + encodeURIComponent(csv);
    var element = document.createElement('a');
    element.href = data;
    element.setAttribute('download', fileName);
    document.body.appendChild(element);
    element.click();
    document.body.removeChild(element);
}

function getCurrentTime() {
    var date = new Date();
    var res = date.getFullYear() + ('0' + (date.getMonth() + 1)).slice(-2) + ('0' + date.getDate()).slice(-2) + ('0' + date.getHours()).slice(-2) + ('0' + date.getMinutes()).slice(-2) + ('0' + date.getSeconds()).slice(-2) + date.getMilliseconds();
    return res;
}

//**************************************
//Handsontable
//**************************************
const hotconf = {
    height: 'auto',
    licenseKey: 'non-commercial-and-evaluation', // for non-commercial use only
    width: 1280, //全体の横枠指定
    height: document.body.clientHeight - 112 - 50, //全体の高さ指定
    autoColumnSize: true, //カラム自動調整
    autoRowSize: true, //行高さ自動調整
    autoColumnSize: true, //列幅自動調整
    rowHeaders: true, //行ヘッダー
    columnSorting: true, //ソート
    sortIndicator: true, //ソートの矢印
    minSpareRows: 0, //1行だけの空白セル
    fillHandle: true, //possible values: true, false, "horizontal", "vertical" フィル有効
    manualColumnMove: false, //ドラッグで移動（列）
    manualColumnResize: true, //ドラッグでサイズ調整(列) 
    manualRowMove: false, //ドラッグで移動(行)
    manualRowResize: false, //ドラッグでサイズ調整(行)
    comments: false,
    mergeCells: false, //セル結合(右クリックメニュー)
    customBorders: false, //罫線(右クリックメニュー)
    renderAllRows: true,
    search: false,  //検索有効
};
