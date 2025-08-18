let hotOt;
let hotGlobal;
const container = document.getElementById('hot_table');
let dataCount = 0;
const inventorystatusList = ["未完了", "完了"];

window.onload = function () {
  init();
  setHotConf();
  setHotData()
    .always(() => {
      end();
    })
}

function setHotConf() {
  hotconf.colHeaders = ['', 'ドメイン', 'ファイルパス', 'タイトル', '棚卸期日', 'ステータス', '備考'];
  hotconf.colWidths = [50, 150, 230, 220, 100, 80, 300];
  hotconf.columns = [
    {
      data: 'alert',
      className: 'center',
      renderer: 'html',
      readOnly: true,
    },
    {
      data: 'domain',
      type: 'text',
      readOnly: true,
    },
    {
      data: 'path',
      type: 'text',
      readOnly: true,
    },
    {
      data: 'titlelink',
      className: 'norap',
      renderer: 'html',
      readOnly: true,
    },
    {
      data: 'inventoryduedate',
      className: 'center',
      type: 'text',
      className: 'center',
      readOnly: true,
    },
    {
      data: 'inventorystatus',
      type: 'dropdown',
      className: 'edit center',
      source: inventorystatusList
    },
    {
      data: 'remarks',
      type: 'text',
      className: ''
    },
    {
      data: 'contentid',
      type: 'text'
    },
    {
      data: 'update',
      type: 'checkbox'
    }
  ];
  hotconf.hiddenColumns = {
    columns: [7, 8]
  };
}


function setHotData() {
  var deferred = $.Deferred();

  $.ajax({
    type: "get",
    url: "https://cums-api.oly.jp/api/listMyContent",
    dataType: "json",
    data: {
      exclude_disable: 1,
      userid: getCookieValue("userid"),
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
        let apiDataList = [];
        response.data.list.forEach(contents => {
          contents.content.forEach(row => {
            // コンテンツステータスが削除以外、且つ、棚卸対象ステータスが棚卸対象を対象とする
            if (row.contentstatus !== 3 && row.inventory === 0) {
              
              // アラートの格納
              // 棚卸が未完了を対象
              if (row.inventorystatus === 0) {
                // 棚卸日が設定されいるデータが対象
                if (row.inventoryduedate) {
                  row.alert = setAlert(row.inventoryduedate);
                }
              }

              row.path = row.path.replace("/www/www.olympus.co.jp/htdocs/", "").replace("/www/www.olympus-global.com/htdocs/", "");

              // ドメイン、タイトルの格納
              if (contents.domain.domainid === 1) {
                row.domain = "www.olympus.co.jp";
                row.titlelink = "<a href='https://www.olympus.co.jp/" + row.path + "' target='_blank' class='norap'>" + row.title + "</a>";
              } else {
                row.domain = "www.olympus-global.com";
                row.titlelink = "<a href='https://www.olympus-global.com/" + row.path + "' target='_blank' class='norap'>" + row.title + "</a>";
              }

              if(row.inventoryduedate){
                row.inventoryduedate = row.inventoryduedate.replace(/-/g, '/');
              }
              
              if (row.inventorystatus === 0) {
                row.inventorystatus = "未完了";
              } else {
                row.inventorystatus = "完了";
              }

              apiDataList.push(row);
            }
          });
        });

        if (apiDataList.length === 0) {
          window.confirm("現在、割り当てられているコンテンツがありません。");
        }

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
      if (row[1] !== 'update') {
        if (row[2] !== row[3]) {
          hot.setDataAtCell(row[0], 8, true);
        } else {
          hot.setDataAtCell(row[0], 8, false);
        }
      }
    });
  }
});


Handsontable.hooks.add('afterRenderer', function (td, row, col, prop, value, cellProperties) {
  var setting = this.getSettings();

  if (prop === "inventorystatus") {
    if (value) {
      if (inventorystatusList.includes(value)) {
        hot_tooltipsterreset(td, row + col);
      } else {
        hot_tooltipstererr(td, row + col, "[ステータス]はリストから選んでください。");
      }
    } else {
      hot_tooltipstererr(td, row + col, "[ステータス]は必須です。");
    }
  }

  return td;
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
    return row[8] === true;
  });

  if (updateHotData.length === 0) {
    window.confirm("更新対象のデータがありません。");
    document.getElementById("save_btn").disabled = false;
    return false;
  }

  // 登録データの格納
  let tempcontentid = [];
  let tempinventoryduedate = [];
  let tempinventorystatus = [];
  let tempuserid = [];
  let tempinventory = [];
  let tempremarks = [];
  let tempdisableflg = [];

  let userid = getCookieValue("userid");

  updateHotData.forEach(row => {
    tempcontentid.push(row[7]);
    tempinventoryduedate.push(row[4]);

    if (row[5] === "未完了") {
      tempinventorystatus.push(0);
    } else {
      tempinventorystatus.push(1);
    }
    
    tempinventory.push(0);
    tempuserid.push(userid);
    tempremarks.push(row[6]);
    tempdisableflg.push(0);
  });

  $.ajax({
    type: "PUT",
    url: "https://cums-api.oly.jp/api/updateMyContent",
    dataType: "json",
    data: {
      "contentid": tempcontentid,
      "inventoryduedate": tempinventoryduedate,
      "inventorystatus": tempinventorystatus,
      "userid": tempuserid,
      "inventory": tempinventory,
      "remarks": tempremarks,
      "disableflg": tempdisableflg,
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