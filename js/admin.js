let hotOt;
let hotGlobal;
const containerOt = document.getElementById('hot_table_ot');
const containerGlobal = document.getElementById('hot_table_global');
let dataCountOt = 0;
let dataCountGlobal = 0;
let userList = [];
let userNameList = [];
const inventorystatusList = ["未完了", "完了"];
let tabViewFlag = "ot";
let csvExportDataList = [];

window.onload = function () {
  init();
  getUserList()
    .done(() => {
      setHotConf();
      setHotData()
        .always(() => {
          end();
        })
    })
}


function getUserList() {
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
        userList = response.data.list;
        userList.forEach(row => {
          if(row.enableflg === 1){
            userNameList.push(row.username);
          }
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
  hotconf.colHeaders = ['', '', 'ファイルパス', 'タイトル', '棚卸担当者', '棚卸期限日', 'ステータス', '棚卸対象外', '前回棚卸日', '備考'];
  hotconf.colWidths = [40, 70, 150, 250, 120, 110, 80, 70, 100, 220];
  hotconf.columns = [
    {
      data: 'alert',
      className: 'center',
      renderer: 'html',
      readOnly: true,
    },
    {
      data: 'status',
      className: 'center',
      renderer: 'html',
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
      data: 'username',
      type: 'dropdown',
      className: 'edit center',
      source: userNameList
    },
    {
      data: 'inventoryduedate',
      type: 'date',
      className: 'edit center',
      dateFormat: 'YYYY/MM/DD',
      correctFormat: true,
      defaultDate: formatDate(new Date()),
      datePickerConfig: {
        firstDay: 0,// First day of the week (0: Sunday, 1: Monday, etc)
        showWeekNumber: true,
        numberOfMonths: 1,
        licenseKey: 'non-commercial-and-evaluation',
        disableDayFn(date) {
          // Disable Sunday and Saturday
          return date.getDay() === 0 || date.getDay() === 6;
        }
      }
    },
    {
      data: 'inventorystatus',
      type: 'dropdown',
      className: 'edit center',
      source: inventorystatusList
    },
    {
      data: 'inventory',
      className: 'edit center',
      type: 'checkbox'
    },
    {
      data: 'inventorydate',
      type: 'text',
      className: 'center',
      readOnly: true,
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
    columns: [10, 11]
  };
}


function setHotData() {
  var deferred = $.Deferred();

  $.ajax({
    type: "get",
    url: "https://cums-api.oly.jp/api/listAdminContent",
    dataType: "json",
    data: {
      exclude_disable: 1
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
        let apiOtDataList = [];
        let apiGlobalDataList = [];
        csvExportDataList = [];
        csvExportDataList.push(['ドメイン', 'ファイルステータス', 'ファイルパス', 'タイトル', '棚卸担当者', '棚卸期限日', 'ステータス', '棚卸対象外', '前回棚卸日', '備考']);

        response.data.list.forEach(contents => {
          contents.content.forEach(row => {
            let csvData = [];
            // コンテンツステータスが削除のものは非表示
            if (row.contentstatus !== 3) {
              
              // アラートの格納
              // 棚卸対象ステータスが棚卸対象を対象とする
              if (row.inventory === 0) {
                // 棚卸が未完了を対象
                if (row.inventorystatus === 0) {
                  // 棚卸日が設定されいるデータが対象
                  if (row.inventoryduedate) {
                    row.alert = setAlert(row.inventoryduedate);
                  }
                }
              }

              // コンテンツステータスの格納
              switch (row.contentstatus) {
                case 1:
                  row.status = "<span class='mdl-chip green'><span class='mdl-chip__text'>更新有り</span ></span >";
                  row.csv_status = "更新有り";
                  break;
                case 2:
                  row.status = "<span class='mdl-chip blue'><span class='mdl-chip__text'>新規</span ></span >";
                  row.csv_status = "新規";
                  break;
                default:
                  row.status = "";
                  row.csv_status = "";
              }

              // ファイルパスの格納
              row.path = row.path.replace("/www/www.olympus.co.jp/htdocs/", "").replace("/www/www.olympus-global.com/htdocs/", "");

              // 担当者の格納
              userList.forEach(temp => {
                if (temp.userid.toString() === row.userid) {
                  row.username = temp.username;
                  return;
                }
              });

              // 棚卸期限日の格納
              if (row.inventoryduedate) {
                row.inventoryduedate = row.inventoryduedate.replace(/-/g, '/');
              }

              // 棚卸ステータスの格納
              if (row.inventorystatus === 0) {
                row.inventorystatus = "未完了";
              } else {
                row.inventorystatus = "完了";
              }

              // 棚卸対象外フラグの格納
              if (row.inventory === 0) {
                row.inventory = false;
              } else {
                row.inventory = true;
              }

              // 前回棚卸日の格納
              if (row.inventorydate) {
                row.inventorydate = row.inventorydate.replace(/-/g, '/');
              }

              // OTサイトの場合
              if (row.domainid === 1) {
                row.titlelink = "<a href='https://www.olympus.co.jp/" + row.path + "' target='_blank' class='norap'>" + row.title + "</a>";
                row.csvdomainname = "www.olympus.co.jp";
                apiOtDataList.push(row);
              }
              // Globalサイトの場合
              else {
                row.titlelink = "<a href='https://www.olympus-global.com/" + row.path + "' target='_blank' class='norap'>" + row.title + "</a>";
                row.csvdomainname = "www.olympus-global.com";
                apiGlobalDataList.push(row);
              }

              csvData.push(row.csvdomainname);
              csvData.push(row.csv_status);
              csvData.push(row.path);
              csvData.push(row.title);
              csvData.push(row.username);
              csvData.push(row.inventoryduedate);
              csvData.push(row.inventorystatus);
              csvData.push(row.inventory);
              csvData.push(row.inventorydate);
              csvData.push(row.remarks);
              csvExportDataList.push(csvData);
            }
          });
        });

        hotconf.data = apiOtDataList;
        hotOt = new Handsontable(containerOt, hotconf);

        hotconf.data = apiGlobalDataList;
        hotGlobal = new Handsontable(containerGlobal, hotconf);

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
          if (tabViewFlag === "ot") {
            hotOt.setDataAtCell(row[0], 11, true);
          } else {
            hotGlobal.setDataAtCell(row[0], 11, true);
          }
        } else {
          if (tabViewFlag === "ot") {
            hotOt.setDataAtCell(row[0], 11, false);
          } else {
            hotGlobal.setDataAtCell(row[0], 11, false);
          }
        }
      }
    });
  }
});


Handsontable.hooks.add('afterRenderer', function (td, row, col, prop, value, cellProperties) {
  var setting = this.getSettings();

  if (prop === "inventoryduedate") {
    if (value) {
      if (ckDate(value)) {
        hot_tooltipsterreset(td, row + col);
      } else {
        hot_tooltipstererr(td, row + col, "日付の形式で入力してください。");
      }
    }
  }

  if (prop === "username") {
    if (value) {
      if (userNameList.includes(value)) {
        hot_tooltipsterreset(td, row + col);
      } else {
        hot_tooltipstererr(td, row + col, "[担当者]はリストから選んでください。");
      }
    }
  }

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

document.getElementById("ot-tab").addEventListener('click', function () {
  // レンダリング崩れ対応
  setTimeout(function () {
    hotOt.render();
    tabViewFlag = "ot";
  }, 0);
});

document.getElementById("global-tab").addEventListener('click', function () {
  // レンダリング崩れ対応
  setTimeout(function () {
    hotGlobal.render();
    tabViewFlag = "global";
  }, 0);
});

document.getElementById("save_btn").addEventListener('click', function () {
  document.getElementById("save_btn").disabled = true;

  if (document.getElementsByClassName('valueerr').length > 0) {
    window.confirm("入力エラーがあるため、更新できません。");
    document.getElementById("save_btn").disabled = false;
    return false;
  }

  let hotOtdata = hotOt.getData();
  let hotGlobadata = hotGlobal.getData();
  let hotdata = hotOtdata.concat(hotGlobadata);

  // 更新データ
  let updateHotData = hotdata.filter(row => {
    return row[11] === true;
  });

  if (updateHotData.length === 0) {
    window.confirm("更新対象のデータがありません。");
    document.getElementById("save_btn").disabled = false;
    return false;
  }

  // 登録データの格納
  let tempcontentid = [];
  let tempinventoryduedate = [];
  let tempuserid = [];
  let tempinventorystatus = [];
  let tempinventory = [];
  let tempremarks = [];
  let tempdisableflg = [];

  updateHotData.forEach(row => {
    tempcontentid.push(row[10]);
    tempinventoryduedate.push(row[5]);
    let matchUserid = null;
    userList.forEach(temp => {
      if (temp.username === row[4]) {
        matchUserid = temp.userid;
        return;
      }
    });
    tempuserid.push(matchUserid);
    
    if (row[6] === "未完了") {
      tempinventorystatus.push(0);
    } else {
      tempinventorystatus.push(1);
    }
    if (row[7]) {
      tempinventory.push(1);
    } else {
      tempinventory.push(0);
    }
    tempremarks.push(row[9]);
    tempdisableflg.push(0);
  });

  $.ajax({
    type: "PUT",
    url: "https://cums-api.oly.jp/api/updateAdminContent",
    dataType: "json",
    data: {
      "contentid": tempcontentid,
      "inventoryduedate": tempinventoryduedate,
      "userid": tempuserid,
      "inventorystatus": tempinventorystatus,
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
        hotOt.destroy();
        hotGlobal.destroy();
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


document.getElementById("csv_btn").addEventListener('click', function () {
  document.getElementById("csv_btn").disabled = true;

  let fileName = "cums_exportdata_" + getCurrentTime() + ".csv";
  downloadArrayAsCsv(csvExportDataList, fileName);

  document.getElementById("csv_btn").disabled = false;
});