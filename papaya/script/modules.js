var currentTimeOut = null;
var packageCount = 0;
var packagePosition = 0;
var limitPackageId = 0;

function checkDatabaseStructure() {
  clearTimeout(currentTimeOut);
  limitPackageId = 0;
  var rpcURL = 'modules.php?mods[cmd]=rpc_count';
  loadXMLDoc(rpcURL, true);
  return false;
}

function checkPackageStructure(packageId) {
  clearTimeout(currentTimeOut);
  limitPackageId = packageId;
  PapayaLightBox.init('Check database', 'Close');
  packageCount = 2;
  PapayaLightBox.update('Checking tables', 0);
  checkTableStructure(limitPackageId);
  return false;
}

function checkTableStructure(packageId, tableName) {
  clearTimeout(currentTimeOut);
  var rpcURL = 'modules.php?mods[cmd]=rpc_check';
  if (typeof packageId != 'undefined' && packageId) {
    rpcURL += '&mods[rpc_pkg_id]=' + escape(packageId);
  }
  if (typeof tableName != 'undefined' && tableName) {
    rpcURL += '&mods[rpc_table]=' + escape(tableName);
  }
  loadXMLDoc(rpcURL, true);
}

function rpcCallbackDatabaseStructureInit(data, params) {
  clearTimeout(currentTimeOut);
  var responseParams = xmlParamNodesToArray(params);
  packageCount = parseInt(responseParams.packageCount) + 1;
  packagePosition = 1;
  if ((!isNaN(packageCount)) && (packageCount > 0)) {
    PapayaLightBox.init('Check database', 'Close');
    PapayaLightBox.update('Checking tables', 1);
    currentTimeOut = window.setTimeout('checkTableStructure();', 50);
  }
}

function rpcCallbackDatabaseStructure(data, params) {
  clearTimeout(currentTimeOut);
  var responseParams = xmlParamNodesToArray(params);
  var nextPackage = parseInt(responseParams.nextPackage);
  var nextPackageName = responseParams.nextPackageName;
  var currentPosition = Math.floor(packagePosition * 100 / packageCount);
  if (currentPosition > 99) {
    currentPosition = 99;
  }
  if (isNaN(nextPackage) || (nextPackage <= 0)) {
    //finished
  } else if (limitPackageId > 0 && limitPackageId != nextPackage) {
    PapayaLightBox.hide();
    reloadDocument(true);
  } else if (nextPackage > 0) {
    if (responseParams.nextTable) {
      var nextTable = responseParams.nextTable;
      currentTimeOut =
        window.setTimeout('checkTableStructure("' + nextPackage +'", "' + nextTable + '");', 100);
      PapayaLightBox.update('Checking table: \n' + nextPackageName + '::' + nextTable, currentPosition);
    } else {
      PapayaLightBox.update('Checking package: \n' + nextPackageName, currentPosition);
      packagePosition++;
      currentTimeOut =
        window.setTimeout('checkTableStructure("' + nextPackage +'");', 100);
    }
  }
}

function rpcCallbackDatabaseStructureFinish(data, params) {
  clearTimeout(currentTimeOut);
  PapayaLightBox.hide();
  reloadDocument(true);
}

/* import table data from csv */

function importTableData(pkgId, table, pathIdent, file, offset) {
  clearTimeout(currentTimeOut);
  if (offset == 0) {
    PapayaLightBox.init('Import', 'Close');
    PapayaLightBox.update('Importing data: ...\n', 0);
  }
  var rpcURL = 'modules.php?mods[cmd]=rpc_import_table_data';
  rpcURL += '&mods[pkg_id]=' + escape(pkgId);
  rpcURL += '&mods[table]=' + escape(table);
  rpcURL += '&mods[path]=' + escape(pathIdent);
  rpcURL += '&mods[file]=' + escape(file);
  rpcURL += '&mods[offset]=' + escape(offset);
  loadXMLDoc(rpcURL, true);
  return false;
}

function rpcCallbackImportTableData(data, params) {
  clearTimeout(currentTimeOut);
  var responseParams = xmlParamNodesToArray(params);
  var pkgId = parseInt(responseParams.pkg_id);
  var table = responseParams.table;
  var path = responseParams.path;
  var file = responseParams.file;
  var offset = responseParams.offset;
  var offsetBytes = parseInt(responseParams.offset_bytes);
  var sizeBytes = parseInt(responseParams.size_bytes);
  if (!isNaN(pkgId)) {
    if (!(isNaN(sizeBytes) || isNaN(offsetBytes))) {
      var progress = Math.round(offsetBytes * 100 / sizeBytes);
      PapayaLightBox.update(null, progress);
    }
    currentTimeOut =
      window.setTimeout('importTableData("' + pkgId + '", "' +
        responseParams.table + '", "' +
        responseParams.path + '", "' +
        responseParams.file + '", "' +
        responseParams.offset + '");',
        100
      );
  } else {
    PapayaLightBox.hide();
  }
}

function rpcCallbackImportTableDataFinish(data, params) {
  clearTimeout(currentTimeOut);
  PapayaLightBox.hide();
  reloadDocument(true);
}

function reloadDocument(stripParameters) {
  if (stripParameters) {
    var newLocation = document.location.href.replace(/\?.*/, '');
    document.location.href = newLocation;
  } else {
    document.location.href = document.location.href;
  }
}