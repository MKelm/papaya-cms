/**
* papaya Installer
*
* The script provides the ajax logic for the installer actions. This includes the database
* validation and updates and the module/options initialization.
*/
(function($) {
  $.papayaInstaller = {

    url : 'install.php',
    options : {},
    tables : [],
    tableUpdatesRequired : 0,
    nextAction : null,

    init : function(options, tables) {
      $('.headerJavascriptWarning').hide();
      this.options = options;
      this.tables = tables;
    },

    startStep : function(step) {
      if (this.tables && this.tables.length > 0) {
        switch (step.toLowerCase()) {
        case 'analyze' :
          this.tableUpdatesRequired = 0;
          this.showProgress('Analyze', '', 0);
          this.updateTableStatus(0);
          break;
        case 'create' :
          this.showProgress('Create', '', 0);
          this.syncTableStructure(0);
          break;
        case 'insert' :
          this.showProgress('Insert', '', 0);
          this.resetTableData(0);
          break;
        case 'init' :
          this.showProgress('Init', '', 0);
          this.initDefaultData(0);
          break;
        case 'goto' :
          document.location.href = 'auth.php';
          break;
        }
      }
      return false;
    },

    showProgress : function(id, text, position) {
      PapayaLightBox.init(id, 'Cancel');
      PapayaLightBox.update(text, position);
    },

    updateTableStatus : function(tableIndex) {
      this.nextAction = null;
      if (tableIndex < this.tables.length) {
        var table = this.tables[tableIndex];
        if (table) {
          if (table.exists) {
            this.showProgress(
              'Analyze',
              'Table ' + table.name + '...',
              Math.round(tableIndex * 100 / this.tables.length)
            );
            this.nextAction = function(installer, index) {
              return function() {
                installer.updateTableStatus(index);
              };
            }(this, tableIndex + 1);
            this.sendRequest(
              {
                step : 'database',
                cmd : 'check_database',
                table : table.name,
                table_idx : tableIndex
              },
              {
                type : 'GET',
                cache : false,
                context : this,
                success : this.handleRequestSuccess,
                error : this.handleRequestError
              }
            );
          } else {
            this.tableUpdatesRequired++;
            this.showProgress(
              'Analyze',
              'Table ' + table.name + '...',
              Math.round(tableIndex * 100 / this.tables.length)
            );
            this.updateTableStatus(tableIndex + 1);
          }
        } else {
          this.updateTableStatus(tableIndex + 1);
        }
      } else {
        PapayaLightBox.hide();
        // disable step - activate next step
        this.disableAction('Analyze');
        if (this.tableUpdatesRequired > 0) {
          this.enableAction('Create');
        } else {
          this.enableAction('Insert');
          this.enableAction('Init');
        }
      }
    },

    syncTableStructure : function(tableIndex) {
      this.nextAction = null;
      if (tableIndex < this.tables.length) {
        var table = this.tables[tableIndex];
        if (table) {
          if (table.exists && table.synced) {
            this.showProgress(
              'Create',
              'Ignoring table ' + table.name + '...',
              Math.round(tableIndex * 100 / this.tables.length)
            );
            this.syncTableStructure(tableIndex + 1);
          } else {
            if (table.exists) {
              this.showProgress(
                'Create',
                'Syncing table ' + table.name + '...',
                Math.round(tableIndex * 100 / this.tables.length)
              );
            } else {
              this.showProgress(
                'Create',
                'Creating table ' + table.name + '...',
                Math.round(tableIndex * 100 / this.tables.length)
              );
            }
            this.nextAction = function(installer, index) {
              return function() {
                installer.syncTableStructure(index);
              };
            }(this, tableIndex + 1);
            this.sendRequest(
              {
                step : 'database',
                cmd : 'install_database',
                table : table.name,
                table_idx : tableIndex
              },
              {
                type : 'GET',
                cache : false,
                context : this,
                success : this.handleRequestSuccess,
                error : this.handleRequestError
              }
            );
          }
        } else {
          this.syncTableStructure(tableIndex + 1);
        }
      } else {
        PapayaLightBox.hide();
        // disable step - activate next step
        this.disableAction('Create');
        this.enableAction('Insert');
        this.enableAction('Init');
      }
    },

    resetTableData :  function(tableIndex) {
      this.nextAction = null;
      if (tableIndex < this.tables.length) {
        var table = this.tables[tableIndex];
        if (table) {
          if (table.exists && table.synced && table.csv && table.insert) {
            this.showProgress(
              'Insert',
              'Reset table ' + this.tables[tableIndex].name + '...',
              Math.round(tableIndex * 100 / this.tables.length)
            );
            this.nextAction = function(installer, index) {
              return function() {
                installer.resetTableData(index);
              };
            }(this, tableIndex + 1);
            this.sendRequest(
              {
                step : 'database',
                cmd : 'reset_data',
                table : table.name,
                table_idx : tableIndex
              },
              {
                type : 'GET',
                cache : false,
                context : this,
                success : this.handleRequestSuccess,
                error : this.handleRequestError
              }
            );
          } else {
            this.showProgress(
              'Insert',
              'Ignoring table ' + this.tables[tableIndex].name + '...',
              Math.round(tableIndex * 100 / this.tables.length)
            );
            this.resetTableData(tableIndex + 1);
          }
        } else {
          this.resetTableData(tableIndex + 1);
        }
      } else {
        PapayaLightBox.hide();
      }
    },

    initDefaultData :  function(step) {
      this.nextAction = null;
      if (step == 0) {
        this.showProgress('Init', 'Check options', 0);
        this.nextAction = function(installer, index) {
          return function() {
            installer.initDefaultData(index);
          };
        }(this, 1);
        this.sendRequest(
          {
            step : 'database',
            cmd : 'init_options',
            table : '',
            table_idx : step
          },
          {
            type : 'GET',
            cache : false,
            context : this,
            success : this.handleRequestSuccess,
            error : this.handleRequestError
          }
        );
      } else if (step == 1) {
        this.showProgress('Init', 'Search modules', 50);
        this.nextAction = function(installer, index) {
          return function() {
            installer.initDefaultData(index);
          };
        }(this, 2);
        this.sendRequest(
          {
            step : 'database',
            cmd : 'init_modules',
            table : '',
            table_idx : step
          },
          {
            type : 'GET',
            cache : false,
            context : this,
            success : this.handleRequestSuccess,
            error : this.handleRequestError
          }
        );
      } else {
        PapayaLightBox.hide();
      }
    },

    getTableIndex : function(tableName) {
      for (var i in this.tables) {
        if (this.tables[i].name == tableName) {
          return i;
        }
      }
      return null;
    },

    disableAction : function(id) {
      var row1 = document.getElementById('header'+id);
      var row2 = document.getElementById('headerDisabled'+id);
      if (row2) {
        if (document.all) {
          row2.style.display = 'block';
        } else {
          row2.style.display = 'table-row';
        }
        if (row1) {
          row1.style.display = 'none';
        }
      }
    },

    enableAction : function(id) {
      var row1 = document.getElementById('header'+id);
      var row2 = document.getElementById('headerDisabled'+id);
      if (row1) {
        if (document.all) {
          row1.style.display = 'block';
        } else {
          row1.style.display = 'table-row';
        }
        if (row2) {
          row2.style.display = 'none';
        }
      }
    },

    showStatus : function(column, tableName, status) {
      $('#sign_'+column+'_'+tableName).attr(
        'src',
        'pics/icons/16x16/' +
          ((status) ? this.options.imageOk : this.options.imageProblem)
      );
    },

    setBox : function(column, tableName, checked) {
      $('#box_'+column+'_'+tableName).attr(
        'src',
        'pics/icons/16x16/' +
          ((checked) ? this.options.imageChecked : this.options.imageUnchecked)
      );
    },

    selectData : function(table, idx) {
      var i = -1;
      if (this.tables[idx] && table == this.tables[idx].name) {
        i = idx;
      } else {
        i = this.getTableIndex(table);
      }
      if (i >= 0 && table == this.tables[i].name) {
        if (this.tables[i].exists && this.tables[i].synced) {
          this.tables[i].insert = (!this.tables[i].insert);
          this.setBox('data', table, this.tables[i].insert);
        }
      }
    },

    rpcCallbackInstallerCount : function(parameters) {
       var idx = parseInt(parameters.filter('[name="idx"]').attr('value'));
       var success = parseInt(parameters.filter('[name="success"]').attr('value'));
       if (success != 0) {
         if (isNaN(idx)) {
           idx = this.getTableIndex(parameters.filter('[name="idx"]').attr('value'));
         }
         if (this.tables.length > idx) {
           this.tables[idx].recordCount = parseInt(parameters.filter('[name="message"]').attr('value'));
           $('#records_'+this.tables[idx].name).html(this.tables[idx].recordCount);
         }
       }
    },

    rpcCallbackInstallerCheck : function(parameters) {
      var idx = parseInt(parameters.filter('[name="idx"]').attr('value'));
      var success = parseInt(parameters.filter('[name="success"]').attr('value'));
      if (success) {
        this.showProgress(
          'Analyze',
          parameters.filter('[name="message"]').attr('value'),
          Math.round(idx * 100 / this.tables.length)
        );
        if (this.tables.length > idx) {
          this.showStatus('struct', this.tables[idx].name, true);
          this.tables[idx].synced = true;
          this.tables[idx].insert = (this.tables[idx].recordCount == 0);
          this.setBox('data', this.tables[idx].name, this.tables[idx].insert);
        }
      } else {
        this.tableUpdatesRequired++;
        this.showProgress(
          'Analyze',
          parameters.filter('[name="message"]').attr('value'),
          Math.round(idx * 100 / this.tables.length)
        );
        this.showStatus('struct', this.tables[idx].name, false);
      }
      if (this.nextAction) {
        this.currentTimeout = window.setTimeout(
          this.nextAction, 50
        );
      }
    },

    rpcCallbackInstallerSync : function(parameters) {
      var idx = parseInt(parameters.filter('[name="idx"]').attr('value'));
      var success = parseInt(parameters.filter('[name="success"]').attr('value'));
      if (success != 0) {
        this.showProgress(
          'Create',
          parameters.filter('[name="message"]').attr('value'),
          Math.round(idx * 100 / this.tables.length)
        );
        if (this.tables.length > idx) {
          this.tables[idx].exists = true;
          this.tables[idx].synced = true;
          this.tables[idx].insert = (this.tables[idx].recordCount == 0);
          this.showStatus('exists', this.tables[idx].name, true);
          this.showStatus('struct', this.tables[idx].name, true);
          this.setBox('data', this.tables[idx].name, this.tables[idx].insert);
        }
      } else {
        this.showProgress(
          'Create',
          parameters.filter('[name="message"]').attr('value'),
          Math.round(idx * 100 / this.tables.length)
        );
      }
      if (this.nextAction) {
        this.currentTimeout = window.setTimeout(
          this.nextAction, 50
        );
      }
    },

    rpcCallbackInstallerReset : function(parameters) {
      var idx = parseInt(parameters.filter('[name="idx"]').attr('value'));
      var success = parseInt(parameters.filter('[name="success"]').attr('value'));
      if (success != 0) {
        this.showProgress(
          'Insert',
          parameters.filter('[name="message"]').attr('value'),
          Math.round(idx * 100 / this.tables.length)
        );
        if (this.tables.length > idx) {
          this.tables[idx].exists = true;
          this.tables[idx].synced = true;
          this.tables[idx].insert = false;
          this.showStatus('exists', this.tables[idx].name, true);
          this.showStatus('struct', this.tables[idx].name, true);
          this.setBox('data', this.tables[idx].name, this.tables[idx].insert);
        }
      } else {
        this.showProgress(
          'Insert',
          parameters.filter('[name="message"]').attr('value'),
          Math.round(idx * 100 / this.tables.length)
        );
      }
      if (this.nextAction) {
        this.currentTimeout = window.setTimeout(
          this.nextAction, 50
        );
      }
    },

    rpcCallbackInstallerUpdate : function(parameters) {
      var idx = parseInt(parameters.filter('[name="idx"]').attr('value'));
      var success = parseInt(parameters.filter('[name="success"]').attr('value'));
      if ((idx != '') && success) {
        this.showProgress(
          idx,
          parameters.filter('[name="message"]').attr('value'),
          -1
        );
      }
    },

    rpcCallbackInstallerInit : function(parameters) {
      var idx = parseInt(parameters.filter('[name="idx"]').attr('value'));
      var success = parseInt(parameters.filter('[name="success"]').attr('value'));
      this.showProgress(
        'Init',
        parameters.filter('[name="message"]').attr('value'),
        Math.round((idx+1) * 100 / 2)
      );
      if (this.nextAction) {
        this.currentTimeout = window.setTimeout(
          this.nextAction, 50
        );
      }
    },

    rpcCallbackInstallerAuthNeeded : function(parameters) {
      alert(parameters.filter('[name="message"]').attr('value'));
      document.location.href = 'install.php?login';
    },

    sendRequest : function(parameters, settings) {
      var url = this.url+'?';
      var parameterString = '';
      for (var i in parameters) {
        parameterString += '&'+escape(this.options.parameterName)+
          '['+escape(i)+']='+escape(parameters[i]);
      }
      settings.url = url + parameterString.substring(1, parameterString.length);
      $.ajax(settings);
    },

    handleRequestSuccess : function(data, textStatus, jqXhr) {
      var installer = this;
      $('responses response', data).each(
        function () {
          var callback = $(this).find('method').text();
          installer[callback]($(this).find('param'));
        }
      );
    },

    handleRequestError : function(jqXhr, textStatus, errorThrown) {
      alert('Failed to fetch rpc response: ' + textStatus);
      this.nextAction = null;
      PapayaLightBox.hide();
    }

  };
})(jQuery);