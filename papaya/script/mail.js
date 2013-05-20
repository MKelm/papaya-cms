PapayaMails = {
  
  mailExpr : /,?\s*(("(([^"]|\\")+)"|([^<,]+))\s+<([^>]+)>|([^@\s]+@[^@\s,;]+)|([^,]+))/,
  mailLineExpr : /,?\s*(("(([^"]|\\")+)"|([^<,]+))\s+<([^>]+)>|([^@\s]+@[^@\s,;]+)|([^,]+))/g,
  
  systemUsers : null,
  systemUserNames : null,
  
  controls : {
    addressBook : null,
    dialog : null,
    fieldTO : null,
    fieldCC : null,
    fieldBCC : null
  },
  
  userCombo : null,
  
  init : function() {
    if (!this.systemUsers) {
      this.systemUsers = new Array();
      this.systemUserNames = new Array();
      
      this.controls.addressBook = document.getElementById('addrcombo_mailto');      
      if (this.controls.addressBook) {
        for (var i = 0; i < this.controls.addressBook.options.length; i++) {
          var addressStr = this.controls.addressBook.options[i].value;
          var userData = this.parseEmail(addressStr);
          if (userData[0]) {
            this.systemUserNames[userData[0]] = addressStr;
          }
          if (userData[1]) {
            this.systemUsers[userData[1]] = addressStr;
          }
        }
      }
      
      this.controls.dialog = document.getElementById('dialogNewMessage');
      this.controls.fieldTO = document.getElementById('dialogNewMessage_to');
      this.controls.fieldCC = document.getElementById('dialogNewMessage_cc');
      this.controls.fieldBCC = document.getElementById('dialogNewMessage_bcc');
    }
  },

  parseEmail : function(addressStr) {
    var matches = addressStr.match(this.mailExpr);
    var userName = '';
    var userEmail = '';
    if (typeof matches[5] !== 'undefined') {
      userName = matches[5];
      userEmail = matches[6];
    } else if (typeof matches[4] !== 'undefined') {
      userName = matches[4];
      userEmail = matches[6];
    } else if (typeof matches[7] !== 'undefined') {
      userName = null;
      userEmail = matches[7];
    } else if (typeof matches[8] !== 'undefined') {
      userName = matches[8];
      userEmail = null;
    } else {
      userName = matches[1];
      userEmail = null;
    }
    return [userName, userEmail];
  },
  
  reformatAddressLine : function(addressLine) {
    var addressList = addressLine.match(this.mailLineExpr);
    if (addressList) {
      var rcpts = new Array();
      for (var i = 0; i < addressList.length; i++) {
        var address = this.parseEmail(addressList[i]);
        if (address[1]) {
          if (address[0]) {
            rcpts[address[1]] = address[0] + ' <' + address[1] + '>';
          } else {
            rcpts[address[1]] = address[1];          
          }
        } else if (address[0]) {
          var internal = address[0] + '@papaya';
          if (this.systemUsers[internal]) {
            rcpts[internal] = this.systemUsers[internal];
          }
        }
      }
      addressLine = '';
      for (var i in rcpts) {
        addressLine += ', ' + rcpts[i];
      }
      return addressLine.substr(2);
    }
    return addressLine;
  },
  
  reformatAddressField : function(fieldIdent) {
    PapayaMails.init(); 
    switch (fieldIdent) {
    case 'BCC' :
      field = this.controls.fieldBCC;
      break;
    case 'CC' :
      field = this.controls.fieldCC;
      break;
    case 'TO' :
    default :
      field = this.controls.fieldTO;
      break;
    }
    if (field) {
      var addressLine = field.value + ', ' + address;
      field.value = this.reformatAddressLine(addressLine);
    }  
  },
  
  addAddress : function(fieldIdent) {
    PapayaMails.init();
    var address = '';
    if (this.controls.addressBook) {
      if (this.controls.addressBook.value) {
        address = this.controls.addressBook.value;
      } else {
        address = this.controls.addressBook.options[this.controls.addressBook.selectedIndex].value;      
      }
    }
    var field;
    switch (fieldIdent) {
    case 'BCC' :
      field = this.controls.fieldBCC;
      break;
    case 'CC' :
      field = this.controls.fieldCC;
      break;
    case 'TO' :
    default :
      field = this.controls.fieldTO;
      break;
    }
    if (field) {
      var addressLine = field.value + ', ' + address;
      field.value = this.reformatAddressLine(addressLine);
    }
  }
}