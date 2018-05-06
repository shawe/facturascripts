/*
 * This file is part of FacturaScripts
 * Copyright (C) 2013-2018 Carlos García Gómez <carlos@facturascripts.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

let autocompleteColumns = [];
let documentLineData = [];
let documentUrl = "";
let hsTable = null;

function beforeChange(changes, source) {
    // Check if the value has changed. Not Multiselection
    if (changes !== null && changes[0][2] !== changes[0][3]) {
        for (let i = 0; i < autocompleteColumns.length; i++) {
            if (changes[0][1] === autocompleteColumns[i]) {
                // aply for autocomplete columns
                if (typeof changes[0][3] === 'string') {
                    changes[0][3] = changes[0][3].split(' | ', 1)[0];
                }
            }
        }
    }
}

function documentRecalculate() {
    let data = {};
    $.each($("form[name=f_document_primary]").serializeArray(), function (key, value) {
        data[value.name] = value.value;
    });
    data.action = "recalculate-document";
    data.lines = getGridData();
    console.log('data', data);

    $.ajax({
        type: "POST",
        url: documentUrl,
        dataType: "json",
        data: data,
        success: function (results) {
            $("#doc_total").val(results.total);

            let rowPos = 0;
            results.lines.forEach(function (element) {
                let visualRow = hsTable.toVisualRow(rowPos);
                documentLineData.rows[visualRow] = element;
                rowPos++;
            });

            hsTable.render();
            console.log('results', results);
        },
        error: function (xhr, status, error) {
            alert(xhr.responseText);
        }
    });
}

function documentSave() {
    // Prevent do the same selector two times
    const save_btn = $("#btn-document-save");
    save_btn.prop("disabled", true);

    let data = {};
    $.each($("form[name=f_document_primary]").serializeArray(), function (key, value) {
        data[value.name] = value.value;
    });
    data.action = "save-document";
    data.lines = getGridData();
    console.log(data);
    $.ajax({
        type: "POST",
        url: documentUrl,
        dataType: "text",
        data: data,
        success: function (results) {
            if (results.substring(0, 3) === "OK:") {
                location.href = results.substring(3);
            } else {
                alert(results);
            }
        }
    });

    save_btn.prop("disabled", false);
}

function getGridData() {
    let rowIndex, lines = [];
    for (let i = 0, max = documentLineData.rows.length; i < max; i++) {
        rowIndex = hsTable.toVisualRow(i);
        if (hsTable.isEmptyRow(rowIndex)) {
            continue;
        }

        lines[rowIndex] = documentLineData.rows[i];
    }
    return lines;
}

function setAutocompletes(columns) {
    for (let key = 0; key < columns.length; key++) {
        if (columns[key].type === "autocomplete") {
            autocompleteColumns.push(columns[key].data);
            let source = columns[key].source["source"];
            let field = columns[key].source["fieldcode"];
            let title = columns[key].source["fieldtitle"];
            columns[key].source = function (query, process) {
                const ajaxData = {
                    term: query,
                    action: "autocomplete",
                    source: source,
                    field: field,
                    title: title
                };
                $.ajax({
                    type: "POST",
                    url: documentUrl,
                    dataType: "json",
                    data: ajaxData,
                    success: function (response) {
                        let values = [];
                        response.forEach(function (element) {
                            values.push(element.key + " | " + element.value);
                        });
                        process(values);
                    }
                });
            };
        }
    }

    return columns;
}

$(document).ready(function () {
    const container = document.getElementById("document-lines");
    hsTable = new Handsontable(container, {
        data: documentLineData.rows,
        columns: setAutocompletes(documentLineData.columns),
        rowHeaders: true,
        colHeaders: documentLineData.headers,
        stretchH: "all",
        autoWrapRow: true,
        manualRowResize: true,
        manualColumnResize: true,
        manualRowMove: true,
        manualColumnMove: false,
        contextMenu: true,
        filters: true,
        dropdownMenu: true,
        preventOverflow: "horizontal",
        minSpareRows: 5,
        enterMoves: {row: 0, col: 1}
    });

    Handsontable.hooks.add('beforeChange', beforeChange);
    Handsontable.hooks.add('afterChange', documentRecalculate);

    $("#doc_codserie").change(function () {
        documentRecalculate();
    });
});
