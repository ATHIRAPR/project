let hot;
const container = document.getElementById('hot_table');
let datacount = 0;

const permissiontypeList = ['作業者', '管理者'];

hotconf.minSpareRows = 1;
hotconf.columnSorting = false;
hotconf.colHeaders = ['氏名', 'メールアドレス', 'パスワード', '権限タイプ', '無効'];
hotconf.colWidths = [200, 250, 100, 100, 80];
hotconf.columns = [
  {
    data: 'username',
    type: 'text',
  },
  {
    data: 'mail',
    type: 'text',
  },
  {
    data: 'password',
    type: 'password',
  },
  {
    data: 'permissiontype',
    type: 'dropdown',
    className: 'center',
    source: permissiontypeList
  },
  {
    data: 'enableflg',
    className: 'center',
    type: 'checkbox'
  },
  {
    data: 'userid',
    type: 'text'
  },
  {
    data: 'update',
    type: 'checkbox'
  },
  {
    data: 'mailupdate',
    type: 'checkbox'
  },
];
hotconf.hiddenColumns = {
  columns: [5, 6, 7]
};


window.onload = function () {
  init();
  setHotData()
    .done(() => {
    })
    .fail(() => {
    })
    .always(() => {
      end();
    })

}

function setHotData() {
  var deferred = $.Deferred();

  $.ajax({
    type: "get",
    url: "https://cums-api.oly.jp/api/listUser",
    dataType: "json",
    headers: {
      "Cums-Api-Token": getCookieValue("token"),
    },
    beforeSend: function () {
      // Loading画像表示
      $("#loaderimg").show();
    }
  })
    .done(function (response, textStatus) {
      if (response.status.message === "success") {
        let apiDataList = [];
        datacount = response.data.list.length;
        // データの加工
        response.data.list.forEach(row => {
          row.password = "XXXXYYYYZZZZ";
          if (row.permissiontype === 1) {
            row.permissiontype = "管理者";
          } else {
            row.permissiontype = "作業者";
          }
          if (row.enableflg === 1) {
            row.enableflg = false;
          } else {
            row.enableflg = true;
          }
          row.update = false;
          row.mailupdate = false;
          apiDataList.push(row);
        });

        hotconf.data = apiDataList;
        hot = new Handsontable(container, hotconf);

        deferred.resolve();
      } else {
        document.location = "index.html";
      }
    })
    .fail(function (jqXHR, textStatus) {
      window.confirm("システムエラーが発生しました。");
      deferred.reject();
    })
    .always(function () {
      $("#loaderimg").hide();
    });
  return deferred.promise();
}

Handsontable.hooks.add('beforeChange', function (changes, source) {
  if (source === 'edit' || source === 'CopyPaste.paste') {
    changes.forEach(row => {
      if (row[0] < datacount) {
        if (row[1] !== 'update') {
          if (row[2] !== row[3]) {
            hot.setDataAtCell(row[0], 6, true);
          } else {
            hot.setDataAtCell(row[0], 6, false);
          }
        }
        if (row[1] === 'mail') {
          if (row[2] !== row[3]) {
            hot.setDataAtCell(row[0], 7, true);
          } else {
            hot.setDataAtCell(row[0], 7, false);
          }
        }
      }
    });
  }
});

Handsontable.hooks.add('afterRenderer', function (td, row, col, prop, value, cellProperties) {
  var setting = this.getSettings();

  // 既存の行の場合
  if (row < datacount) {

    if (prop === "username") {
      if (value) {
        hot_tooltipsterreset(td, row + col);
      } else {
        hot_tooltipstererr(td, row + col, "[氏名]は必須です。");
      }
    }

    if (prop === "mail") {
      if (value) {
        if (ckMail(value)) {
          hot_tooltipsterreset(td, row + col);
        } else {
          hot_tooltipstererr(td, row + col, "[メールアドレス]の入力形式が誤っています。");
        }
      } else {
        hot_tooltipstererr(td, row + col, "[メールアドレス]は必須です。");
      }
    }

    if (prop === "password") {
      if (value) {
        if (value.length > 7) {
          hot_tooltipsterreset(td, row + col);
        } else {
          hot_tooltipstererr(td, row + col, "[パスワード]は8桁以上で入力したさい。");
        }
      } else {
        hot_tooltipstererr(td, row + col, "[パスワード]は必須です。");
      }
    }

    if (prop === "permissiontype") {
      if (value) {
        if (permissiontypeList.includes(value)) {
          hot_tooltipsterreset(td, row + col);
        } else {
          hot_tooltipstererr(td, row + col, "[権限タイプ]はリストから選んでください。");
        }
      } else {
        hot_tooltipstererr(td, row + col, "[権限タイプ]は必須です。");
      }
    }
  }

  // 新規行の場合、必須のチェック以外を実行
  if (row >= datacount) {
    if (prop === "mail") {
      if (value) {
        if (ckMail(value)) {
          hot_tooltipsterreset(td, row + col);
        } else {
          hot_tooltipstererr(td, row + col, "[メールアドレス]の入力形式が誤っています。");
        }
      } else {
        hot_tooltipsterreset(td, row + col);
      }
    }

    if (prop === "password") {
      if (value) {
        if (value.length > 7) {
          hot_tooltipsterreset(td, row + col);
        } else {
          hot_tooltipstererr(td, row + col, "[パスワード]は8桁以上で入力したさい。");
        }
      } else {
        hot_tooltipsterreset(td, row + col);
      }
    }

    if (prop === "permissiontype") {
      if (value) {
        if (permissiontypeList.includes(value)) {
          hot_tooltipsterreset(td, row + col);
        } else {
          hot_tooltipstererr(td, row + col, "[権限タイプ]はリストから選んでください。");
        }
      } else {
        hot_tooltipsterreset(td, row + col);
      }
    }
  }


  return td;
});

Handsontable.hooks.add('afterChange', function (changes) {
  if (changes) {
    changes.forEach((row, prop, oldValue, newValue) => {
      // 新規行の場合
      if (row[0] >= datacount) {
        // 更新対象が空になった場合
        if (!row[3]) {
          let emptyflg = true;
          // その他の項目で、値が存在するか確認
          hot.getData()[row[0]].forEach((val) => {
            if (val) {
              emptyflg = false;
            }
          });

          if (emptyflg) {
            hot.alter('remove_row', row[0], 1);
          }
        }
      }
    });
  }
});

document.getElementById("save_btn").addEventListener('click', function () {
  document.getElementById("save_btn").disabled = true;

  if (document.getElementsByClassName('valueerr').length > 0) {
    window.confirm("入力エラーがあるため、更新できません。");
    document.getElementById("save_btn").disabled = false;
    return false;
  }

  let hotdata = hot.getData();

  // 更新データ
  let updateHotData = hotdata.filter(row => {
    return row[6] === true;
  });
  // 新規データ(最後の1行は削除)
  let insertHotData = hotdata.slice(datacount);
  insertHotData.pop();

  // 新規データのバリデーション
  if (insertHotData.length > 0) {
    if (!insertValidation(insertHotData)) {
      document.getElementById("save_btn").disabled = false;
      return false;
    }
  }

  updateHotData = updateHotData.concat(insertHotData);
  if (updateHotData.length === 0) {
    window.confirm("更新対象のデータがありません。");
    document.getElementById("save_btn").disabled = false;
    return false;
  }

  // 登録データの格納
  let tempuserid = [];
  let tempmail = [];
  let tempusername = [];
  let temppassword = [];
  let temppermissiontype = [];
  let tempenableflg = [];

  updateHotData.forEach(row => {
    tempuserid.push(row[5]);
    if (row[7] || row[7] === null) {
      tempmail.push(row[1]);
    } else {
      tempmail.push(null);
    }
    tempusername.push(row[0]);
    if (row[2] !== "XXXXYYYYZZZZ") {
      temppassword.push(row[2]);
    } else {
      temppassword.push(null);
    }
    if (row[3] === "管理者") {
      temppermissiontype.push(1);
    } else {
      temppermissiontype.push(0);
    }
    if (row[4]) {
      tempenableflg.push(0);
    } else {
      tempenableflg.push(1);
    }
  });

  $.ajax({
    type: "PUT",
    url: "https://cums-api.oly.jp/api/createOrUpdateUser",
    dataType: "json",
    data: {
      "userid": tempuserid,
      "mail": tempmail,
      "username": tempusername,
      "password": temppassword,
      "permissiontype": temppermissiontype,
      "enableflg": tempenableflg,
    },
    headers: {
      "Cums-Api-Token": getCookieValue("token"),
    },
    beforeSend: function () {
      // Loading画像表示
      $("#loaderimg").show();
    }
  })
    .done(function (response, textStatus) {
      if (response.status.message === "success") {
        window.confirm("更新が完了いたしました。");
        hot.destroy();
        setHotData();
      } else {
        window.confirm(response.status.message);
      }
    })
    .fail(function (response, textStatus) {
      window.confirm("システムエラーが発生しました。");
    })
    .always(function () {
      $("#loaderimg").hide();
      document.getElementById("save_btn").disabled = false;
    });

});


function insertValidation(data) {
  let rowcount = 0;
  let result = true;
  data.forEach(row => {
    rowcount++;
    if (!row[0]) {
      window.confirm(datacount + rowcount + "行目：[氏名]は必須です。");
      result = false;
      return;
    }

    if (!row[1]) {
      window.confirm(datacount + rowcount + "行目：[メールアドレス]は必須です。");
      result = false;
      return;
    } else {
      if (!ckMail(row[1])) {
        window.confirm(datacount + rowcount + "行目：[メールアドレス]の入力形式が誤っています。");
        result = false;
        return;
      }
    }

    if (!row[2]) {
      window.confirm(datacount + rowcount + "行目：[パスワード]は必須です。");
      result = false;
      return;
    } else {
      if (row[2].length < 8) {
        window.confirm(datacount + rowcount + "行目：[パスワード]は8桁以上で入力したさい。");
        result = false;
        return;
      }
    }

    if (!row[3]) {
      window.confirm(datacount + rowcount + "行目：[権限タイプ]は必須です。");
      result = false;
      return;
    } else {
      if (!permissiontypeList.includes(row[3])) {
        window.confirm(datacount + rowcount + "行目：[権限タイプ]はリストから選んでください。");
        result = false;
        return;
      }
    }
  });
  return result;
}