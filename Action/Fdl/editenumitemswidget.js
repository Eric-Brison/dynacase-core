(function ($, window, document) {
    if (!("console" in window)) {
        window.console = {'log': function (s) {
            return s;
        }};
    }

    $.fn.dataTableExt.oApi.fnProcessingIndicator = function (oSettings, onoff) {
        if (typeof(onoff) == 'undefined') {
            onoff = true;
        }
        this.oApi._fnProcessingDisplay(oSettings, onoff);
    };

    /**
     * Basic error log (in console if exist)
     *
     * @param error
     */
    var onError = function onError(error) {
        console.log(error);
    };

    var editableClass = "editable";

    var htmlEncode = function htmlEncode(value) {
        return $('<div/>').text(value).html();
    };

    var htmlDecode = function htmlDecode(value) {
        return $('<div/>').html(value).text();
    };

    $.widget("dcpui.editenumitems", {

//Options to be used as defaults
        options: {
            withlocale: true,
            saveOnBottom: false,
            famid: "",
            enumid: "",
            rowToUpdateClass: "rowToUpdate ui-state-highlight",
            title: "%f: %s &gt; %e",
            helpMessage: "",
            dataTableOptions: {
                aoColumnDefs: [
                    {
                        "aTargets": [0],
                        "mDataProp": "order",
                        "bSearchable": false,
                        "bSortable": false,
                        "bUseRendered": false,
                        "sClass": editableClass + " ui-widget",
                        "fnRender": function (data) {
                            return '<span>' + (data.aData.order || "") + '</span>';
                        },
                        "sWidth": "60px",
                        "sTitle": "[TEXT:EnumWidget:Rank]"
                    },
                    {
                        "aTargets": [1],
                        "mDataProp": "key",
                        "sClass": "ui-widget",
                        "bSortable": false,
                        "sTitle": "[TEXT:EnumWidget:Key]"
                    },
                    {
                        "aTargets": [2],
                        "bSortable": false,
                        "mDataProp": "label",
                        "bUseRendered": false,
                        "fnRender": function (data) {
                            return '<span title="' + this._getTranslateTooltip(data.aData) + '">' + (data.aData.label || "") + '</span>';
                        },
                        "sClass": editableClass + " ui-widget",
                        "sTitle": "[TEXT:EnumWidget:Label]"
                    },
                    {
                        "aTargets": [3],
                        "mDataProp": "disabled",
                        "sTitle": "Active",
                        "bSortable": false,
                        "bSearchable": false,
                        "sClass": "ui-widget",
                        "sWidth": "125px",
                        "bUseRendered": false,
                        fnRender: function (data) {
                            if (data.aData.newline) {
                                return this._getActiveDeleteButton(data);
                            } else {
                                return this._getActiveRadioButton(data.aData);
                            }
                        }
                    }
                ],
                bSort: false,
                bFilter: true,
                sScrollY: "200px"
            }
        },

        _dataTableDefaultOptions: {
            sDom: '<"#titleHeader">rt<"#newLine">',
            bServerSide: false,
            bJQueryUI: true,
            bAutoWidth: false,
            bProcessing: true,
            aaSorting: [],
            aaData: [],
            "bScrollCollapse": false,
            "bPaginate": false,
            "bDestroy": true
        },

        _orderTitle: "[TEXT:EnumWidget:tooltip order]",

        _dataTableWidget: null,

        _dataTableElement: $(),

        _firstDraw: true,

        _noResize: false,
        famTitle: "",

        enumLabel: "",

        enumParentLabel: "",

        _originalData: {},

        _lastRow: null,

        _keyRowChanged: {},

        _activeButtonLabel: {
            "on": "[TEXT:EnumWidget:ON]",
            "off": "[TEXT:EnumWidget:OFF]"
        },

        _columnIndex: {},

        _localeId: [],
        _localeConfig: [],
        _translateInit: false,

        //Setup widget (eg. element creation, apply theming
        // , bind events etc.)
        _create: function () {

            this._dataTableElement = this.element.append('<table></table>').find("table");

            this._getEnumItems();

            $(window).on({
                'resize': $.proxy(this._resizeWindow, this),
                "beforeunload": $.proxy(this._unloadWindow, this)
            });
        },

        _unloadWindow: function (e) {
            if (this._keyRowChangedLength() > 0) {
                e.preventDefault();
                return this._convertStringWithInfo("[TEXT:Some change in the enum '%s > %e' are not saved do you really wish to quit?]");
            }
        },

        _resizeWindow: function _resizeWindow() {
            if (this._dataTableWidget) {
                this._firstDraw = true;
                if (! this._noResize) {
                    this._dataTableWidget.fnAdjustColumnSizing();
                }
            }
        },

        _showWaiting: function () {
             $("<div>[TEXT:Loading enum data ...]</div>").addClass("reload-dialog").dialog({
                 modal: true,
                 resizable:false,
                 height:100,
                 dialogClass: 'no-dialog-title' // Hide title bar
                });
        },
        _hideWaiting: function () {
             $(".reload-dialog").dialog( "close").remove();
        },

        _getEnumItems: function () {
            var parent = this;
            this._showWaiting();
            this._firstDraw = true;
            $.ajax({
                "type": "GET",
                "url": "?app=FDL&action=GETENUMITEMS",
                "data": {
                    famid: this.options.famid,
                    enumid: this.options.enumid
                },
                "success": $.proxy(parent._initiateDataTable, parent)
            });
        },

        _createSaveButton: function _createSaveButton() {
            var self = this;
            var buttonSave = $('<button class="saveButton" title="[TEXT:EnumWidget:Save change]">[TEXT:Save]</button>').on(
                "click", function (event) {
                    $(this).button({"disabled": true}); // Prevent multiple click/trigger
                    self._modEnumItems();
                }
            );
            var buttonReload = $('<button class="reloadButton" title="[TEXT:EnumWidget:Cancel change]">[TEXT:EnumWidget:Reload]</button>').on("click", $.proxy(this._beforeOnReload, this));
            var buttonHelp = $('<button class="helpButton">[TEXT:Help]</button>').button().on("click", $.proxy(this._showHelp, this));
            var buttonsDiv = $("<div></div>").append(buttonSave).append(buttonReload).addClass("headerButton");
            var disabled = this._keyRowChangedLength() <= 0;

            if (this.options.saveOnBottom) {
                $("#newLine").append(buttonsDiv.clone(true).css("text-align", "right"));
            }
            if (this.options.helpMessage) {
                buttonsDiv.append(buttonHelp);
            }
            $("#titleHeader").prepend(buttonsDiv.css("float", "right"));

            $(".saveButton").button({
                "disabled": disabled
            });
            $(".reloadButton").button();

        },

        _showHelp: function _showHelp(e) {
            this._message(this.options.helpMessage);
        },

        _beforeOnReload: function _beforeOnReload(e) {
            this._confirm("[TEXT:Some changes are not saved, do you really wish to reload?]",
                $.proxy(this._getEnumItems, this), e);
        },

        _modEnumItems: function _modEnumItems() {
            var widget = this;
            var toSendData = [];
            var localeIds = widget._localeId;

            widget._dataTableWidget.fnProcessingIndicator();

            for (var key in this._keyRowChanged) {
                if (!this._keyRowChanged.hasOwnProperty(key)) {
                    continue;
                }
                /* Find the datatable row index corresponding to the changed key */
                var rowIndex = this._findKey(key);
                if (rowIndex === false) {
                    continue;
                }
                var rowData = widget._dataTableWidget.fnGetData(rowIndex);
                rowData.localeLabel = [];
                $.each(localeIds, function (i, locale) {
                    rowData.localeLabel.push({
                        "lang": locale,
                        "label": rowData[locale] || ""
                    });
                });
                toSendData.push(rowData);
            }

            if (toSendData.length > 0) {
                $.ajax({
                    "type": "POST",
                    "url": "?app=FDL&action=MODENUMITEMS",
                    "data": {
                        famid: widget.options.famid,
                        enumid: widget.options.enumid,
                        items: JSON.stringify(toSendData)
                    },
                    "success": $.proxy(widget._afterSendDataToServer, widget)
                });
            } else {
                widget._dataTableWidget.fnProcessingIndicator(false);
            }
        },

        _afterSendDataToServer: function _afterSendDataToServer(data) {
            if (!$.isPlainObject(data)) {
                this._error(data);
                this._dataTableWidget.fnProcessingIndicator(false);
            } else if (data.error) {
                this._error(data.error);
                this._dataTableWidget.fnProcessingIndicator(false);
            } else {
                this._keyRowChanged =  {};
                this._getEnumItems();
            }

        },

        _rowCallback: function _rowCallback(nRow, aData) {
            if (aData.disabled == true) {
                $(nRow).addClass("disabledEnum");
            } else {
                $(nRow).removeClass("disabledEnum");
            }
        },

        _drawCallback: function _drawCallback(oSettings) {
            if (this._firstDraw) {
                this._initActiveButtonSet(this._dataTableElement);
                this._initActiveTranslateButton(this._dataTableElement);
                this._initiateHeader();
                this._initWidgetHeader();


                this._dataTableElement.find("tbody tr").find("." + editableClass).on("click.editenumitems", {"widget": this}, this._rowClick);

                this._createNewLine();
                this._createSaveButton();

                this._firstDraw = false;
                this._trigger("redraw");
            }
            this.element.find("[title]").tipsy("hide");
            this._initTipsy(this.element.find("[title]"));
        },

        _convertStringWithInfo: function (str) {
            return str.replace(/%f/g, this.famTitle).replace(/%e/g, this.enumLabel).replace(/%s/g, this.enumParentLabel);
        },

        _initWidgetHeader: function _initWidgetHeader() {
            var title = this._convertStringWithInfo(this.options.title);

            $("#titleHeader").html('<h2 class="title" title="' + this.options.enumid + '">' + title + '</h2>').addClass("ui-state-default");
        },

        _initActiveButtonSet: function _initActiveButtonSet($element) {
            var widget = this;
            $element.find(".activebuttonset").buttonset()
                .find(".ui-button").on("click", function (e) {
                    if ($(this).text() === widget._activeButtonLabel.on) {
                        $(this).parent().parent().parent().removeClass("disabledEnum");
                        widget._updateRow(false, $(this).parent(), true);
                    } else if ($(this).text() === widget._activeButtonLabel.off) {
                        $(this).parent().parent().parent().addClass("disabledEnum");
                        widget._updateRow(true, $(this).parent(), true);
                    }
                });
        },

        _initActiveDeleteButton: function _initiActiveDeleteButton($element) {
            $element.find(".activedeletebutton").button().one("click.editenumitems", {"widget": this, "row": $element}, this._deleteClick);
        },
        _initActiveTranslateButton: function _initActiveTranslateButton($element) {
            $element.find(".activetranslatebutton").button();
        },

        _createNewLine: function _createNewLine() {
            var tr = $("<tr></tr>"),
                html = "",
                widget = this,
                $newLine = $("#newLine"),
                $oldLineElems = $newLine.find("td"),
                hasValue = false;

            $.each(this.options.dataTableOptions.aoColumnDefs, function (index, col) {
                if (this.mDataProp != "disabled") {
                    var aTarget = this.aTargets[0],
                        width = $(".dataTable").find("th").eq(aTarget).css("width"),
                        value = "",
                        title = "";

                    if ($oldLineElems.length > 0) {
                        value = $oldLineElems.eq(aTarget).find("input").val();
                        if (value) hasValue = true;
                    }

                    if (this.mDataProp == "order") {
                        title = widget._orderTitle;
                    } else {
                        title = this.sTitle;
                    }
                    if (this.mDataProp == "locale") {
                        html += '<td  style="width:' + width + '"/></td>';
                    } else {
                        html += '<td><input type="text" class="ui-widget ui-widget-content ui-state-active newLineField" data-id="' + this.mDataProp + '" placeholder="' + this.sTitle + '" style="width:' + width + '" value="' + value + '" title="' + title + '"/></td>';
                    }
                }
            });

            html += '<td><button id="addNewEnum">[TEXT:EnumWidget:Add]</button></td>';
            var newLine = $("<table></table>").prepend(tr.append(html)),
                newLineTitle = $('<h3>[TEXT:Add new enum choice: ]</h3>').addClass("ui-widget footerTitle");

            if ($newLine.find("table").length > 0) {
                $newLine.find("table").remove();
                $newLine.find(".footerTitle").remove()
            }
            $newLine.append(newLine).addClass(" ui-state-default").prepend(newLineTitle);

            $newLine.find("input").off("keyup").on("keyup", function () {
                var addEnumButton = $("#addNewEnum"),
                    hasValue = false;

                if ($(this).val()) {
                    addEnumButton.button("enable");
                } else {
                    $("#newLine").find("input").each(function () {
                        if ($(this).val()) {
                            hasValue = true;
                            return false;
                        }
                    });
                    if (hasValue)  addEnumButton.button("enable");
                    else  addEnumButton.button("disable");
                }
            });

            $("#addNewEnum").button({
                disabled: !hasValue
            }).off("click").on("click", $.proxy(this._addNewRow, this));
        },

        _initiateDataTable: function _initiateDataTable(data) {
            if (!$.isPlainObject(data)) {
                this._error(data);
            } else if (data.error) {
                this._error(data.error);
            } else {

                $('.tipsy').hide();
                var element = this._dataTableElement;

                this.options.dataTableOptions = this._generateDataTableOptions(data.localeConfig, data.items);

                this.options.dataTableOptions.aaData = this._generateDataTableData(data.items, this.options.dataTableOptions.aoColumnDefs);

                this.famTitle = data.familyTitle || "";
                this.enumLabel = data.enumLabel || "";
                this.enumParentLabel = data.parentLabel || "";

                this._noResize=true;
                this._dataTableWidget = element.dataTable(this.options.dataTableOptions);
                this._dataTableWidget.fnAdjustColumnSizing();
                this._noResize=false;
                this._hideWaiting();
            }

        },

        _getTranslateTooltip: function (lineData) {
            var langConfig = this._localeConfig,
                html = "<table class='translateTooltip'>",
                data = lineData;

            $.each(langConfig, function (index, lang) {
                html += "<tr><td><img class='form-icon-flag' src='" + lang.flag + "' alt='" + lang.id + "'/></td><td class='langLabel'>" + (lang.localeLabel || "") + ": </td><td class='langValue'>" + (data[lang.id] || "[TEXT:EnumWidget:No translation]") + "</td></tr>";
            });

            html += "</table>";
            return html;
        },

        _getTranslateButton: function (lineData) {
            return '<button class="activetranslatebutton" data-key="' + lineData.key + '" data-index="' + lineData.iDataRow + '" title="' + this._getTranslateTooltip(lineData.aData) + '">[TEXT:EnumWidget:Translate]</button>';
        },

        _getActiveDeleteButton: function (lineData) {
            return '<button class="activedeletebutton" data-key="' + lineData.aData.key + '" data-index="' + lineData.iDataRow + '">[TEXT:EnumWidget:Remove]</button>';
        },

        _getActiveRadioButton: function (lineData) {

            var key = lineData.key ? lineData.key.replace(/"/g, "&quot;").replace(/'/g,'--'): "";
            return '<div class="activebuttonset" >' +
                '<input type="radio" id="activebuttonset_' + key  + '_on" name="activebuttonset_' + key  + '" ' + (!lineData.disabled ? 'checked="checked"' : "") + '/><label class="enum-on" for="activebuttonset_' + key + '_on">' + this._activeButtonLabel.on + '</label>' +
                '<input type="radio" id="activebuttonset_' + key  + '_off" name="activebuttonset_' + key + '"' + (lineData.disabled ? 'checked="checked"' : "") + '/><label class="enum-off" for="activebuttonset_' + key + '_off">' + this._activeButtonLabel.off + '</label>' +
                '</div>';
        },

        //initiate the Header (used for filtering)
        _initiateHeader: function _initiateHeader() {
            var HeaderElement = [], widget = this,
                fixedHeader = $(".dataTables_scrollHead"),
                analyseColumns = function analyseColumns() {
                    var currentColumn = this;
                    if (currentColumn.bSearchable !== false) {
                        $.each(this.aTargets, function each(index, value) {
                            HeaderElement[value] = ' <div class="search">' +
                                '<span class="ui-icon ui-icon-search"></span>' +
                                '<input class="searchField" placeholder="' + currentColumn.sTitle + '" name="' + currentColumn.mDataProp + '"/>' +
                                '</div>';
                        });
                    }
                    widget._columnIndex[currentColumn.mDataProp] = currentColumn.aTargets[0];
                };


            $.each(this.options.dataTableOptions.aoColumnDefs, analyseColumns);
            var th = fixedHeader.find("th");
            $.each(HeaderElement, function each(index, value) {
                if (value !== undefined) {
                    th.eq(index).html((value || ""));
                }
            });

            var searchField = $("th").find(".searchField");

            searchField.on({
                "keyup.editenumitems": widget._searchFieldKeyup
            }, {
                widget: widget,
                searchField: searchField
            });
        },

        _addNewRow: function () {
            var newLine = {
                "disabled": false,
                "newline": true
            };
            var locale = {};

            $.each(this._localeId, function (index, value) {
                locale[value] = "";
            });

            newLine.locale = locale;
            $("#newLine").find("input").each(function () {
                newLine[$(this).attr("data-id")] = $(this).val();
            });

            if (this._findKey(newLine.key) >= 0) {
                this._error("[TEXT:Key already exists, must be unique]");
                return false;
            }

            var indexes = this._dataTableWidget.fnAddData(newLine, false);
            var $trNode = $(this._dataTableWidget.fnGetNodes(indexes[0]));

            this._addAndReorganizeOrder(newLine.order, indexes[0], true);

            this._initActiveDeleteButton($trNode.addClass(this.options.rowToUpdateClass));
            this._initActiveTranslateButton($trNode.addClass(this.options.rowToUpdateClass));

            this._addDataToSave(newLine.key);
            $trNode.find("." + editableClass).on("click.editenumitems", {"widget": this}, this._rowClick);
        },

        _updateOrder: function _updateOrder(newOrder, lineNumber, aData) {
            this._dataTableWidget.fnUpdate(newOrder, lineNumber, 0, false, false);
            if (this._lastRow && this._lastRow.key == aData.key) {
                this._lastRow = aData;
            }
        },

        _findLastRow: function () {
            var data = this._dataTableWidget.fnGetData(),
                dataToReturn = null;

            $.each(data, function (index, aData) {
                if (!dataToReturn || aData.order > dataToReturn.order) {
                    dataToReturn = aData;
                }
            });
            return dataToReturn;
        },

        _deleteRowWithKey: function(rowKey) {
            var keyHasBeenChanged = this._keyRowChanged.hasOwnProperty(rowKey);
            if (!keyHasBeenChanged) {
                this._error("[TEXT:Can't delete already created row]");
            }
            var rowIndex = this._getRowIndexWithKey(rowKey);
            this._dataTableWidget.fnDeleteRow(rowIndex);
            this._removeDataToSave(rowKey);
        },

        _getRowIndexWithKey: function(lookupKey) {
            var rowsData = this._dataTableWidget.fnGetData();
            for( var index = 0; index < rowsData.length; index++) {
                if (rowsData[index].key == lookupKey) {
                    return index;
                }
            }
            return false;
        },

        _addAndReorganizeOrder: function _addAndReorganizeOrder(val, lineNumber, newLine) {
            var linePut = false,
                widget = this,
                $tr = this._dataTableElement.find("tbody tr"),
                rowsData = this._dataTableWidget.fnGetData();
                lineData = this._dataTableWidget.fnGetData(lineNumber),
                lineOrder = lineData.order,
                datas = widget._dataTableWidget.fnGetData(),
                value = parseInt(val, 10);

            $.each(datas, function (index, aData) {
                if (aData.order == value + 1) {
                    lineData.order = aData.order;
                    if (aData.key != lineData.key) lineData.orderBeforeThan = aData.key;
                    widget._dataTableWidget.fnUpdate(lineData, lineNumber, undefined, false, false);
                    linePut = aData.order;
                    if (widget._lastRow && lineData.key === widget._lastRow.key) {
                        widget._lastRow = widget._findLastRow();
                    }
                    return false;
                }
            });

            if (newLine) lineOrder = lineData.order;
            $.each(datas, function (index, aData) {
                if (lineData.key != aData.key) {
                    if (lineOrder < aData.order && !newLine) {
                        widget._updateOrder(aData.order - 2, index, aData);
                    }
                    if (linePut && linePut <= aData.order) {
                        widget._updateOrder(aData.order + 2, index, aData);
                    }
                }
            });

            if (!linePut) {
                newOrder = rowsData.length * 2;

                if (widget._lastRow) {
                    widget._lastRow.orderBeforeThan = lineData.key;
                    widget._dataTableWidget.fnUpdate(widget._lastRow, widget._findKey(widget._lastRow.key), undefined, false, false);
                }

                widget._initActiveButtonSet($tr.last());
                widget._initActiveDeleteButton($tr.last());
                widget._initActiveTranslateButton($tr.last());

                lineData.order = newOrder;
                lineData.orderBeforeThan = null;
                widget._dataTableWidget.fnUpdate(lineData, lineNumber, undefined, false, false);
                widget._lastRow = lineData;
            }

            this._dataTableWidget.fnSort([
                [0, "asc"]
            ]);
        },

        _findKey: function (key) {
            var datas = this._dataTableWidget.fnGetData(),
                indexOfKey = -1;

            $.each(datas, function (index, aData) {
                if (aData.key === key) {
                    indexOfKey = index;
                    return false;
                }
            });
            return indexOfKey;
        },

        _reinitField: function (value, $td) {
            var widget= this,
                title = $td.find("[original-title]").attr("original-title"),
                span = $("<span></span>").html(value).attr("title", title);

            $td.find("input").remove();
            $td.append(span);

            widget._initTipsy($td.find("[title]"));
        },

        _updateRow: function (value, element, fromButton) {
            var $element = $(element),
                $td = $element.parent(),
                widget = this,
                aPos = this._dataTableWidget.fnGetPosition($td.get(0)),
                aData = this._dataTableWidget.fnGetData($td.get(0));

            if (value == aData) {
                if (!fromButton) widget._reinitField(value, $td);
                return false;
            }

            if (aPos[2] == widget._columnIndex.order) {
                if (parseInt(value, 10) % 2 == 0) {
                    if (!fromButton) widget._reinitField(value, $td);
                    widget._error("[TEXT:Order must be an odd number]");
                    return false;
                }
                widget._addAndReorganizeOrder(value, aPos[0]);
            } else {
                if (aPos[2] == widget._columnIndex.key) {
                    if (widget._findKey(value) >= 0) {
                        if (!fromButton) widget._reinitField(value, $td);
                        widget._error("[TEXT:Key already exists, must be unique]");
                        return false;
                    }
                }
                this._dataTableWidget.fnUpdate(value, aPos[0], aPos[2], false, false);
            }

            if (aPos[2] == widget._columnIndex.order || aPos[2] == widget._columnIndex.disabled) {
                widget._initActiveButtonSet($td.parent());
                widget._initActiveDeleteButton($td.parent());
                widget._initActiveTranslateButton($td.parent());
            }

            this._setRowToChange($td.parent(), aPos);
            widget._initTipsy($td.find("[title]"));
        },

        _setRowToChange: function _setRowToChange($tr, dataTableIndex) {
            var data = this._dataTableWidget.fnGetData(dataTableIndex[0]);
            var originalData = this._originalData[data.key];
            var changeInLine = false;
            var widget = this;

            if (originalData) {
                $.each(originalData, function (key, val) {
                    switch (key) {
                        case "order":
                        case "locale":
                            return true;
                        default:
                            if (data[key] !== undefined && data[key] !== val) {
                                changeInLine = true;
                                return false;
                            } else if (key === "orderBeforeThan" && $tr.next().hasClass(widget.options.rowToUpdateClass)) {
                                changeInLine = true;
                                return false;
                            }
                    }
                });
            } else changeInLine = true;

            var keyHasBeenChanged = this._keyRowChanged.hasOwnProperty(data.key);
            if (changeInLine) {
                if (!keyHasBeenChanged) this._addDataToSave(data.key);
                $tr.addClass(this.options.rowToUpdateClass);
            } else {
                if (keyHasBeenChanged) this._removeDataToSave(data.key);
                $tr.removeClass(this.options.rowToUpdateClass);
            }
        },

        _deleteClick: function (e) {
            var widget = e.data.widget;
            var dataKey = $(this).attr("data-key");

            return widget._deleteRowWithKey(dataKey);
        },

        _translateClick: function (e) {
            var widget = e.data.widget;
            var trNode = $(this).parent();

            while (trNode && !trNode.is("tr")) {
                trNode = trNode.parent();
            }
            trNode = trNode.get(0);

            var langsConfig = widget._localeConfig;
            var rowData = widget._dataTableWidget.fnGetData(trNode);

            var dialogForm = $('#dialog-translate');

            if (dialogForm.length == 0) {
                var htmlDialog = '<div id="dialog-translate" title="Update translation" class="translate-form"></div>';
                $('body').append(htmlDialog);

                dialogForm = $('#dialog-translate');
                dialogForm.dialog({
                    autoOpen: false,
                    height: "auto",
                    width: 350,
                    modal: true,
                    buttons: {
                        "[TEXT:EnumWidget:Translate]": function () {
                            // @todo save data
                            var trNode = $(this).dialog("option", "rowTr");
                            var rowGridData = $(this).dialog("option", "rowGridData");
                            var idlang;
                            for (var li = 0; li < langsConfig.length; li++) {
                                idlang = langsConfig[li].id;
                                rowGridData[idlang] = $('#trans-' + idlang).val();
                            }
                            widget._dataTableWidget.fnUpdate(rowGridData, trNode, undefined, false, false);

                            widget._setRowToChange($(trNode), [widget._dataTableWidget.fnGetPosition(trNode)]);
                            widget._initActiveButtonSet($(trNode));
                            widget._initActiveDeleteButton($(trNode));
                            widget._initActiveTranslateButton($(trNode));
                           // console.log("initiate tipsy for ", $(trNode).find("td").find("[orignial-title]"), $(trNode).find("td").find("[title]"))
                            widget._initTipsy($(trNode).find("td").find("[title]"));
                            $(this).dialog("close");
                        },
                        Cancel: function () {
                            $(this).dialog("close");
                        }
                    },
                    close: function () {

                    }
                });
            }
            var formDialog = '<p >' + "[TEXT:EnumWidget:Translate] : <span class='form-label'>" + (rowData.label || "") + '</span></p><form>'
                + '<fieldset>';
            var idl;
            var ivalue;
            for (var i = 0; i < langsConfig.length; i++) {
                idl = langsConfig[i].id;
                ivalue = rowData[idl];
                if (ivalue == undefined) {
                    ivalue = '';
                }
                formDialog += '<label for="trans-' + idl + '"><img class="form-icon-flag" src="' + langsConfig[i].flag + '"> ' + langsConfig[i].localeLabel + ' </label>';
                formDialog += '<input type="text" name="' + idl + '" id="trans-' + idl + '" value="' + ivalue + '" class="text ui-widget-content ui-corner-all" />'
            }


            formDialog += '</fieldset></form>';
            dialogForm.html(formDialog).dialog().dialog('open').dialog("option", "title", "[TEXT:EnumWidget:Translate] " + '"' + rowData.key+ '"');
            dialogForm.dialog("option", "rowGridData", rowData);
            dialogForm.dialog("option", "rowTr", trNode);

        },

        _addDataToSave: function (key) {
            this._keyRowChanged[key] = key;
            $(".saveButton").each(function () {
                $(this).button("enable");
            });
        },

        _removeDataToSave: function (key) {
            delete this._keyRowChanged[key];
            if (this._keyRowChangedLength() <= 0) {
                $(".saveButton").each(function () {
                    $(this).button("disable");
                });
            }
        },

        _rowClick: function (e) {
            if ($("#rowToChange").length > 0 || $(this).find(".activebuttonset").length > 0) {
                return false;
            }
            var widget = e.data.widget,
                $this = $(this),
                val = $this.text(),
                updateRow = $.proxy(widget._updateRow, widget),
                aPos = widget._dataTableWidget.fnGetPosition(this),
                tipsyElem = $this.find("[original-title]"),
                title = tipsyElem.attr("original-title");

            widget._trigger("rowclick", e, {widget: widget, elem: $this});

            if (aPos[2] == widget._columnIndex.order) {
                title = widget._orderTitle;
            }
            var colName = '';
            for (var ci in widget._columnIndex) {
                if (widget._columnIndex[ci] == aPos[2]) {
                    colName = ci;
                }
            }

            tipsyElem.tipsy("hide");
            $this.html('<input type="text" id="rowToChange" />').find("input").val(val).attr("title", title).addClass('edit-' + colName);

            var $rowChange = $("#rowToChange").focus().one("blur.editenumitems",function (e) {
                $(this).tipsy("hide");
                updateRow(htmlEncode($(this).val()), this);
            }).on("keypress.editenumitems", function (e) {
                    if (e.keyCode == 13) {
                        $(this).trigger("blur.editenumitems");
                    }
                });

            widget._initTipsy($rowChange);
        },

        _initTipsy: function ($element) {
            return $element.tipsy({
                gravity: this._getTipsyGravity,
                html: true
            });
        },

        _getTipsyGravity: function () {
            var autoNS = $.proxy($.fn.tipsy.autoNS, this),
                autoWE = $.proxy($.fn.tipsy.autoWE, this);
            return  autoNS() + autoWE();
        },

        _searchFieldKeyup: function (e) {
            var widget = e.data.widget;
            var searchField = e.data.searchField;
            var me = this;

            widget._trigger("searchkeyup", e, {widget: widget, elem: $(me)});
            widget._dataTableWidget.fnFilter(this.value, searchField.index(this) + 1);
        },

        // Compute and generate options at dataTableFormat
        _generateDataTableOptions: function _generateDataTableOptions(localeConfigs, items) {
            var options = {},
                widget = this;
            if (this.options.withlocale) options.aoColumnDefs = this._addTranslationColumn(localeConfigs);
            else options.aoColumnDefs = this.options.dataTableOptions.aoColumnDefs;

            $.each(options.aoColumnDefs, function (index, column) {
                if (column.fnRender) {
                    column.fnRender = $.proxy(column.fnRender, widget);
                }
            });

            options.fnDrawCallback = this.options.dataTableOptions.fnDrawCallback || $.proxy(this._drawCallback, this);

            options.fnRowCallback = this.options.dataTableOptions.fnDrawCallback || $.proxy(this._rowCallback, this);

            return $.extend(true, options, this._dataTableDefaultOptions, this.options.dataTableOptions);
        },
        _addTranslationColumn: function _addTranslationColumn(localeConfigs) {
            var aoColumnDefs = this.options.dataTableOptions.aoColumnDefs,
                lastIndexColumnDefs = aoColumnDefs.length ,
                widget = this,
                aTargets = lastIndexColumnDefs - 1;


            $.each(localeConfigs, function (index, locale) {
                if ($.inArray(locale.id, widget._localeId) < 0) {
                    widget._localeId.push(locale.id);
                    widget._localeConfig.push(locale);
                }
            });
            if (!widget._translateInit) {
                aoColumnDefs.push({
                    "aTargets": [aTargets],
                    "mDataProp": 'locale',
                    "bSortable": false,
                    "bSearchable": false,
                    "sClass": " ui-widget",
                    "sTitle": "[TEXT:EnumWidget:Translate]",
                    "bUseRendered": false,
                    "sWidth": "125px",
                    "fnRender": function (data) {
                        return this._getTranslateButton(data);
                    }
                });
                aoColumnDefs[lastIndexColumnDefs - 1].aTargets = [aoColumnDefs.length - 1];

                $(widget.element).on("click.translate", ".activetranslatebutton", {"widget": this }, this._translateClick);
            }

            widget._translateInit = true;

            return aoColumnDefs;
        },

        _generateDataTableData: function _generateDataTableData(items, columDefs) {
            var data = [],
                widget = this,
                index = 0;

            $.each(items, function (i, item) {
                var rowInfo = item;
                if (widget.options.withlocale && item.locale) {
                    $.each(item.locale, function (index, locale) {
                        rowInfo[locale.lang] = locale.label;
                    });
                }
                rowInfo.order *= 2;
                rowInfo.orderBeforeThan = null;
                widget._originalData[i] = rowInfo;
                data.push(rowInfo);
                if (index > 0) {
                    data[index - 1].orderBeforeThan = rowInfo.key;
                }
                index++;
                if (!widget._lastRow || widget._lastRow.order < rowInfo.order) {
                    widget._lastRow = rowInfo;
                }
            });
            return data;
        },

        _generateDataTableColumns: function _generateDataTableColumns(localeConfigs) {
            var aoColumnDefs = this.options.dataTableOptions.aoColumnDefs,
                lastIndexColumnDefs = aoColumnDefs.length - 1,
                widget = this;

            $.each(localeConfigs, function (index, locale) {
                var aTargets = lastIndexColumnDefs + index;

                if ($.inArray(locale.id, widget._localeId) < 0) {
                    widget._localeId.push(locale.id);
                    aoColumnDefs.push({
                        "aTargets": [aTargets],
                        "mDataProp": locale.id,
                        "sClass": editableClass + " ui-widget",
                        "sTitle": locale.localeLabel
                    });
                }
            });
            if (aoColumnDefs.length - 1 > lastIndexColumnDefs) {
                aoColumnDefs[lastIndexColumnDefs].aTargets = [aoColumnDefs.length - 1];
            }
            return aoColumnDefs;
        },

        // trigger an error event and log error
        _error: function _error(error, event) {
            event = event || {};
            onError(error || "");
            this._trigger("error", event, { error: error });
        },

        _confirm: function _confirm(message, callBack, event) {
            event = event || {};
            this._trigger("confirm", event, {
                msg: message,
                callback: callBack
            });
        },

        _message: function _message(message, event) {
            event = event || {};
            this._trigger("message", event, { msg: message });
        },

// Destroy an instantiated plugin and clean up
// modifications the widget has made to the DOM
        destroy: function () {

            // this.element.removeStuff();
            // For UI 1.8, destroy must be invoked from the
            // base widget
            $.Widget.prototype.destroy.call(this);
            // For UI 1.9, define _destroy instead and don't
            // worry about
            // calling the base widget
        },

// Respond to any changes the user makes to the
// option method
        _setOption: function (key, value) {
            var parent = this;
            switch (key) {
                default:
                    this.options[key] = value;
                    break;
            }

            // For UI 1.8, _setOption must be manually invoked
            // from the base widget
            $.Widget.prototype._setOption.apply(this, arguments);
            // For UI 1.9 the _super method can be used instead
            // this._super( "_setOption", key, value );

        },

// Get number of keys in keyRowChanged struct
        _keyRowChangedLength: function() {
            return $.map(this._keyRowChanged, function(n, i) { return i; }).length
        },

// Print internal datatable data
        _printState: function() {
            console.log({
                _keyRowChanged: this._keyRowChanged,
                fnGetData: this._dataTableWidget.fnGetData()
            });
        }

    });


})(jQuery, window, document);
