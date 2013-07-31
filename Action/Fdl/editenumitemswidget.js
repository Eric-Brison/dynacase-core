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
            famid: "",
            enumid: "",
            rowToUpdateClass: "rowToUpdate",
            dataTableOptions: {
                aoColumnDefs: [
                    {
                        "aTargets": [0],
                        "mDataProp": "order",
                        "bSearchable": false,
                        "bSortable": false,
                        "sClass": editableClass,
                        "sWidth": "60px",
                        "sTitle": "Rank"
                    },
                    {
                        "aTargets": [1],
                        "mDataProp": "key",
                        "bSortable": false,
                        "sTitle": "Key"
                    },
                    {
                        "aTargets": [2],
                        "bSortable": false,
                        "mDataProp": "label",
                        "sClass": editableClass,
                        "sTitle": "Label"
                    },
                    {
                        "aTargets": [3],
                        "mDataProp": "disabled",
                        "sTitle": "Active",
                        "bSortable": false,
                        "bSearchable": false,
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

        _dataTableWidget: null,

        _dataTableElement: $(),

        _firstDraw: true,

        famTitle: "",

        enumLabel: "",

        _originalData: {},

        _lastRow: null,

        _indexRowChanged: [],

        _activeButtonLabel: {
            "on": "ON",
            "off": "OFF"
        },

        _columnIndex: {},

        _localeId: [],

        //Setup widget (eg. element creation, apply theming
        // , bind events etc.)
        _create: function () {

            this._dataTableElement = this.element.append('<table></table>').find("table");

            this._getEnumItems();

            $(window).on('resize', $.proxy(this._resizeWindow, this));
        },

        _resizeWindow: function _resizeWindow() {
            if (this._dataTableWidget) {
                this._firstDraw = true;
                this._dataTableWidget.fnAdjustColumnSizing();
            }
        },

        _getEnumItems: function () {
            var parent = this;

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
            var button = $('<button id="saveButton">Save change</button>').button({
                "disabled": true
            }).on("click", $.proxy(this.modEnumItems, this));
            $("#addNewEnum").parent().append(button);
        },

        modEnumItems: function modEnumItems() {
            var widget = this,
                toChangeKey = widget._indexRowChanged,
                toSendData = [],
                localeIds = widget._localeId;

            widget._dataTableWidget.fnProcessingIndicator();

            $.each(toChangeKey, function (index, value) {
                var rowData = widget._dataTableWidget.fnGetData(value);
                rowData.localeLabel = [];
                $.each(localeIds, function (i, locale) {
                    rowData.localeLabel.push({
                        "lang": locale,
                        "label": rowData[locale]
                    });
                });
                toSendData.push(rowData);
            });

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
                this._firstDraw = true;
                this._getEnumItems();
            }

        },

        _drawCallback: function _drawCallback(oSettings) {
            if (this._firstDraw) {
                this._indexRowChanged = [];
                this._initActiveButtonSet(this._dataTableElement);
                this._initiateHeader();
                this._initWidgetHeader();


                this._dataTableElement.find("tbody tr").find("." + editableClass).on("click.editenumitems", {"widget": this}, this._rowClick);

                this._createNewLine();
                this._firstDraw = false;

                this._trigger("redraw");
            }
        },

        _initWidgetHeader: function _initWidgetHeader() {
            $("#titleHeader").html('<h3>' + this.famTitle + ': ' + this.enumLabel + '</h3>').addClass("ui-state-default").css("text-align", "center");
        },

        _initActiveButtonSet: function _initActiveButtonSet($element) {
            var widget = this;

            $element.find(".activebuttonset").buttonset()
                .find(".ui-button").on("click", function (e) {
                    if ($(this).text() === widget._activeButtonLabel.on) {
                        widget._updateRow(false, $(this).parent(), true);
                    } else if ($(this).text() === widget._activeButtonLabel.off) {
                        widget._updateRow(true, $(this).parent(), true);
                    }
                });
        },

        _initActiveDeleteButton: function _initiActiveDeleteButton($element) {
            $element.find(".activedeletebutton").button().one("click.editenumitems", {"widget": this, "row": $element}, this._deleteClick);
        },

        _createNewLine: function _createNewLine() {
            var tr = $("<tr></tr>"),
                html = "",
                widget = this,
                $newLine = $("#newLine"),
                $oldLineElems = $newLine.find("td");

            $.each(this.options.dataTableOptions.aoColumnDefs, function (index, col) {
                if (this.mDataProp != "disabled") {
                    var aTarget = this.aTargets[0],
                        width = $(".dataTable").find("th").eq(aTarget).css("width"),
                        value = "";
                    if ($oldLineElems.length > 0) {
                        value = $oldLineElems.eq(aTarget).find("input").val();
                    }
                    html += '<td><input type="text" class="ui-widget ui-widget-content ui-corner-all ui-state-active" data-id="' + this.mDataProp + '" placeholder="' + this.sTitle + '" style="width:' + width + '" value="' + value + '"/></td>';
                }
            });
            html += '<td><button id="addNewEnum">Add</button></td>';
            var newLine = $("<table></table>").prepend(tr.append(html));

            if ($newLine.find("table").length > 0) {
                $newLine.find("table").remove("table");
            }
            $newLine.append(newLine).addClass(" ui-state-default");
            $("#addNewEnum").button().on("click", $.proxy(this._addNewRow, this));
            this._createSaveButton();
        },

        _initiateDataTable: function _initiateDataTable(data) {
            if (!$.isPlainObject(data)) {
                this._error(data);
            } else if (data.error) {
                this._error(data.error);
            } else {
                var element = this._dataTableElement;

                this.options.dataTableOptions = this._generateDataTableOptions(data.localeConfig, data.items);

                this.options.dataTableOptions.aaData = this._generateDataTableData(data.items, this.options.dataTableOptions.aoColumnDefs);

                this.famTitle = data.familyTitle;
                this.enumLabel = data.enumLabel;

                this._dataTableWidget = element.dataTable(this.options.dataTableOptions);
            }

        },

        _getActiveDeleteButton: function (lineData) {
            return '<button class="activedeletebutton" data-index="' + lineData.iDataRow + '">Remove</button>';
        },

        _getActiveRadioButton: function (lineData) {
            return '<div class="activebuttonset" >' +
                '<input type="radio" id="activebuttonset_' + lineData.key + '_on" name="activebuttonset_' + lineData.key + '" ' + (!lineData.disabled ? 'checked="checked"' : "") + '/><label for="activebuttonset_' + lineData.key + '_on">' + this._activeButtonLabel.on + '</label>' +
                '<input type="radio" id="activebuttonset_' + lineData.key + '_off" name="activebuttonset_' + lineData.key + '"' + (lineData.disabled ? 'checked="checked"' : "") + '/><label for="activebuttonset_' + lineData.key + '_off">' + this._activeButtonLabel.off + '</label>' +
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

            var searchField = $(".searchField");
            searchField.on({
                "keyup.editenumitems": widget._searchFieldKeyup
            }, {
                widget: widget,
                searchField: searchField
            });
        },

        _addNewRow: function () {
            var newLine = {"disabled": false, "newline": true};

            $("#newLine").find("input").each(function () {
                newLine[$(this).attr("data-id")] = $(this).val();
            });

            if (this._findKey(newLine.key) >= 0) {
                this._error("Key already exists, must be unique");
                return false;
            }

            var indexes = this._dataTableWidget.fnAddData(newLine, false);
            var $trNode = $(this._dataTableWidget.fnGetNodes(indexes[0]));

            this._addAndReorganizeOrder(newLine.order, indexes[0], true);

            this._initActiveDeleteButton($trNode.addClass(this.options.rowToUpdateClass));

            this._addDataToSave(indexes[0]);
            $trNode.find("." + editableClass).on("click.editenumitems", {"widget": this}, this._rowClick);
        },

        _updateOrder: function _updateOrder(newOrder, lineNumber, aData) {
            this._dataTableWidget.fnUpdate(newOrder, lineNumber, 0, false, false);
            if (this._lastRow.key == aData.key) {
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

        _addAndReorganizeOrder: function _addAndReorganizeOrder(val, lineNumber, newLine) {
            var linePut = false,
                widget = this,
                $tr = this._dataTableElement.find("tbody tr"),
                lineData = this._dataTableWidget.fnGetData(lineNumber),
                lineOrder = lineData.order,
                datas = widget._dataTableWidget.fnGetData(),
                value = parseInt(val);

            $.each(datas, function (index, aData) {
                if (aData.order == value + 1) {
                    lineData.order = aData.order;
                    if (aData.key != lineData.key) lineData.orderBeforeThan = aData.key;
                    widget._dataTableWidget.fnUpdate(lineData, lineNumber, undefined, false, false);
                    linePut = aData.order;
                    if (lineData.key === widget._lastRow.key) {
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
                var newOrder = $tr.length * 2;
                if (newLine) newOrder += 2;

                widget._lastRow.orderBeforeThan = lineData.key;
                widget._dataTableWidget.fnUpdate(widget._lastRow, widget._findKey(widget._lastRow.key), undefined, false, false);

                widget._initActiveButtonSet($tr.last());
                widget._initActiveDeleteButton($tr.last());

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

        _updateRow: function (value, element, fromButton) {
            var $element = $(element),
                $td = $element.parent(),
                widget = this,
                aPos = this._dataTableWidget.fnGetPosition($td.get(0)),
                aData = this._dataTableWidget.fnGetData($td.get(0));

            if (value == aData) {
                if (!fromButton) {
                    $td.find("input").remove();
                    $td.html(value);
                }
                return false;
            }

            if (aPos[2] == widget._columnIndex.order) {
                if (parseInt(value) % 2 == 0) {
                    if (!fromButton) $td.html(aData);
                    widget._error("Order must be an odd number");
                    return false;
                }
                widget._addAndReorganizeOrder(value, aPos[0]);
            } else {
                if (aPos[2] == widget._columnIndex.key) {
                    if (widget._findKey(value) >= 0) {
                        if (!fromButton) $td.html(aData);
                        widget._error("Key already exists, must be unique");
                        return false;
                    }
                }
                this._dataTableWidget.fnUpdate(value, aPos[0], aPos[2], false, false);
            }

            if (aPos[2] == widget._columnIndex.order || aPos[2] == widget._columnIndex.disabled) {
                widget._initActiveButtonSet($td.parent());
                widget._initActiveDeleteButton($td.parent());
            }
            this._setRowToChange($td, aPos);
        },

        _setRowToChange: function _setRowToChange($td, dataTableIndex) {
            var data = this._dataTableWidget.fnGetData(dataTableIndex[0]),
                originalData = this._originalData[data.key],
                changeInLine = false,
                $tr = $td.parent(),
                widget = this;

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

            var indexOfElem = $.inArray(dataTableIndex[0], this._indexRowChanged);
            if (changeInLine) {
                if (indexOfElem < 0) this._addDataToSave(dataTableIndex[0]);
                $tr.addClass(this.options.rowToUpdateClass);
            } else {
                if (indexOfElem >= 0) this._removeDataToSave(indexOfElem);
                $tr.removeClass(this.options.rowToUpdateClass);
            }
        },

        _deleteClick: function (e) {
            var widget = e.data.widget,
                $trNode = e.data.row,
                dataIndex = parseInt($(this).attr("data-index")),
                indexOfElem = $.inArray(dataIndex, widget._indexRowChanged);

            if (indexOfElem < 0) {
                widget._error("Can't delete already created row");
            } else {
                widget._addAndReorganizeOrder("", dataIndex);
                widget._dataTableWidget.fnDeleteRow(dataIndex);

                widget._removeDataToSave(indexOfElem);
                widget._lastRow = widget._findLastRow();
            }
        },

        _addDataToSave: function (dataIndex) {
            this._indexRowChanged.push(dataIndex);
            $("#saveButton").button("enable");
        },

        _removeDataToSave: function (indexOfElem) {
            this._indexRowChanged.splice(indexOfElem, 1);
            if (this._indexRowChanged.length <= 0) {
                $("#saveButton").button("disable");
            }
        },

        _rowClick: function (e) {
            if ($("#rowToChange").length > 0 || $(this).find(".activebuttonset").length > 0) {
                return false;
            }
            var widget = e.data.widget;
            var $this = $(this);
            var val = $this.text();
            var updateRow = $.proxy(widget._updateRow, widget);

            widget._trigger("rowclick", e, {widget: widget, elem: $this});

            $this.html('<input type="text" id="rowToChange" />').find("input").val(val);

            $("#rowToChange").focus().one("blur.editenumitems",function (e) {
                updateRow(htmlEncode($(this).val()), this);
            }).on("keypress.editenumitems", function (e) {
                    if (e.keyCode == 13) {
                        $(this).trigger("blur.editenumitems");
                    }
                });
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
            if (this.options.withlocale) options.aoColumnDefs = this._generateDataTableColumns(localeConfigs);
            else options.aoColumnDefs = this.options.dataTableOptions.aoColumnDefs;

            $.each(options.aoColumnDefs, function (index, column) {
                if (column.fnRender) {
                    column.fnRender = $.proxy(column.fnRender, widget);
                }
            });

            options.fnDrawCallback = this.options.dataTableOptions.fnServerData || $.proxy(this._drawCallback, this);

            return $.extend(true, options, this._dataTableDefaultOptions, this.options.dataTableOptions);
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
                        "sClass": editableClass,
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

        }

    });


})(jQuery, window, document);
