let hot;
const container = document.getElementById('hot_table');
let datacount = 0;
let domainList = [];
let domainameList = [];

window.onload = function () {
  init();
  getDomainList()
    .done(() => {
      setHotConf();
      setHotData()
        .always(() => {
          end();
        })
    })
}

function getDomainList() {
  var deferred = $.Deferred();

  $.ajax({
    type: "get",
    url: "https://cums-api.oly.jp/api/listDomain",
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
        domainList = response.data.list;
        domainList.forEach(row => {
          domainameList.push(row.domainname);
        });
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


function setHotConf() {
  hotconf.minSpareRows = 1;
  hotconf.columnSorting = false;
  hotconf.colHeaders = ['ドメイン', '追加パス/ファイル名', '無効'];
  hotconf.colWidths = [200, 500, 80];
  hotconf.columns = [
    {
      data: 'domainame',
      type: 'dropdown',
      className: 'center',
      source: domainameList
    },
    {
      data: 'path',
      type: 'text',
    },
    {
      data: 'enableflg',
      className: 'center',
      type: 'checkbox'
    },
    {
      data: 'directoryid',
      type: 'text'
    },
    {
      data: 'update',
      type: 'checkbox'
    },
  ];
  hotconf.hiddenColumns = {
    columns: [3, 4]
  };
}

function setHotData() {
  var deferred = $.Deferred();

  $.ajax({
    type: "get",
    url: "https://cums-api.oly.jp/api/listDirectory",
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
          
          domainList.forEach(temp => {
            if (temp.domainid === row.domainid) {
              row.domainame = temp.domainname;
            }
          });
          if (row.enableflg === 1) {
            row.enableflg = false;
          } else {
            row.enableflg = true;
          }
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
            hot.setDataAtCell(row[0], 4, true);
          } else {
            hot.setDataAtCell(row[0], 4, false);
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
    if (prop === "domainame") {
      if (value) {
        if (domainameList.includes(value)) {
          hot_tooltipsterreset(td, row + col);
        } else {
          hot_tooltipstererr(td, row + col, "[ドメイン]はリストから選んでください。");
        }
      } else {
        hot_tooltipstererr(td, row + col, "[ドメイン]は必須です。");
      }
    }

    if (prop === "path") {
      if (value) {
        if (ckPath(value)) {
          hot_tooltipsterreset(td, row + col);
        } else {
          hot_tooltipstererr(td, row + col, '[パス]は末尾を".html"、又は、"/"としてください。');
        }
      } else {
        hot_tooltipstererr(td, row + col, "[パス]は必須です。");
      }
    }
  }

  // 新規行の場合、必須のチェック以外を実行
  if (row >= datacount) {
    if (prop === "domainame") {
      if (value) {
        if (domainameList.includes(value)) {
          hot_tooltipsterreset(td, row + col);
        } else {
          hot_tooltipstererr(td, row + col, "[ドメイン]はリストから選んでください。");
        }
      } else {
        hot_tooltipsterreset(td, row + col);
      }
    }

    if (prop === "path") {
      if (value) {
        if (ckPath(value)) {
          hot_tooltipsterreset(td, row + col);
        } else {
          hot_tooltipstererr(td, row + col, '[パス]は末尾を".html"、又は、"/"としてください。');
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
    return row[4] === true;
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
  let tempdirectoryid = [];
  let tempdomainid = [];
  let tempdirectorypass = [];
  let tempenableflg = [];

  updateHotData.forEach(row => {
    tempdirectoryid.push(row[3]);
    domainList.forEach(temp => {
      if (temp.domainname === row[0]) {
        tempdomainid.push(temp.domainid);
        return;
      }
    });
    if (row[4] || row[4] === null) {
      tempdirectorypass.push(row[1]);
    } else {
      tempdirectorypass.push(null);
    }

    if (row[2]) {
      tempenableflg.push(0);
    } else {
      tempenableflg.push(1);
    }
  });

  $.ajax({
    type: "PUT",
    url: "https://cums-api.oly.jp/api/createOrUpdateDirectory",
    dataType: "json",
    data: {
      "directoryid": tempdirectoryid,
      "domainid": tempdomainid,
      "path": tempdirectorypass,
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
      window.confirm(datacount + rowcount + "行目：[ドメイン]は必須です。");
      result = false;
      return;
    } else {
      if (!domainameList.includes(row[0])) {
        window.confirm(datacount + rowcount + "行目：[ドメイン]はリストから選んでください。");
        result = false;
        return;
      }
    }

    if (!row[1]) {
      window.confirm(datacount + rowcount + "行目：[パス]は必須です。");
      result = false;
      return;
    } else {
      if (!ckPath(row[1])) {
        window.confirm(datacount + rowcount + '行目：[パス]は末尾を".html"、又は、" / "としてください。');
        result = false;
        return;
      }
    }

  });
  return result;
}