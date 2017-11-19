<?php
/**
 * This file is part of FacturaScripts
 * Copyright (C) 2013-2017  Carlos Garcia Gomez  carlos@facturascripts.com
 * Copyright (C) 2017  Francesc Pineda Segarra  <francesc.pineda.segarra@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
namespace FacturaScripts\Core\Base\ExtendedController;

/**
 * Description of WidgetItemSelect2
 *
 * @author Artex Trading sa <jcuello@artextrading.com>
 * @author Francesc Pineda Segarra <francesc.pineda.segarra@gmail.com>
 */
class WidgetItemSelect2 extends WidgetItem implements WidgetItemJQueryInterface
{

    /**
     * Accepted values for the field associated to the widget
     *
     * @var array
     */
    public $values;

    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->type = 'select2';
        $this->values = [];
    }

    /**
     * Loads the attributes structure from a XML file
     *
     * @param \SimpleXMLElement $column
     */
    public function loadFromXML($column)
    {
        parent::loadFromXML($column);
        $this->getAttributesGroup($this->values, $column->widget->values);
    }

    /**
     * Loads the attributes structure from the database
     *
     * @param array $column
     */
    public function loadFromJSON($column)
    {
        parent::loadFromJSON($column);
        $this->values = (array) $column['widget']['values'];
    }

    /**
     * Loads the value list from an array with value and title (description)
     *
     * @param array $rows
     */
    public function setValuesFromCodeModel(&$rows)
    {
        $this->values = [];
        foreach ($rows as $codeModel) {
            $this->values[] = [
                'value' => $codeModel->code,
                'title' => $codeModel->description,
            ];
        }
    }

    /**
     * Loads the value list from a given array.
     * The array must have one of the two following structures:
     * - If it's a value array, it must uses the value of each element as title and value
     * - If it's a multidimensional array, the indexes value and title must be set for each element
     *
     * @param array $values
     */
    public function setValuesFromArray(&$values)
    {
        $this->values = [];
        foreach ($values as $value) {
            if (is_array($value)) {
                $this->values[] = ['title' => $value['title'], 'value' => $value['value']];
                continue;
            }

            $this->values[] = [
                'value' => $value,
                'title' => $value,
            ];
        }
    }

    /**
     * Generates the HTML code to display the data in the List controller
     *
     * @param string $value
     *
     * @return string
     */
    public function getListHTML($value)
    {
        if ($value === null || $value === '') {
            return '';
        }

        return '<span>' . $value . '</span>';
    }

    /**
     * Generates the HTML code to display and edit  the data in the Edit / EditList controller
     *
     * @param string $value
     *
     * @return string
     */
    public function getEditHTML($value)
    {
        $specialAttributes = $this->specialAttributes();

        if ($this->readOnly) {
            return $this->standardEditHTMLWidget($value, $specialAttributes, '', 'text');
        }

        $fieldName = '"' . $this->fieldName . '"';
        $html = $this->getIconHTML()
            . '<select name=' . $fieldName . ' id=' . $fieldName
            . ' class="form-control select2"' . $specialAttributes . '>';

        foreach ($this->values as $selectValue) {
            if ($selectValue['value'] == $value) {
                $html .= '<option value="' . $selectValue['value'] . '" selected="selected" >' . $selectValue['title'] . '</option>';
            }
        }
        $html .= '</select>';

        if (!empty($this->icon)) {
            $html .= '</div>';
        }

        return $html;
    }

    /**
     * Generates the jQuery required code
     *
     * @return string
     */
    public function getJQuery($model)
    {
        $modelName = 'pais';    // TODO: source in XML View in singular
        $fieldName = 'codpais'; // TODO: #codpais => #(fieldname of XML)
        $fieldCode = 'codpais'; // TODO: codpais must be fieldcode in XML View in singular
        $fieldTitle = 'nombre'; // TODO: nombre must be fieldtitle in XML View in singular

        $jquery = '        /* Code needed to use Select2 */'. "\n" .
        'var apiUrl = "api.php?v=3&resource="' . $modelName . ';'. "\n" .
        "\n\n" .
        '$("#' . $fieldName . '").select2({'. "\n" .
        '    tags: "true",'. "\n" .
        '    placeholder: "{{ i18n.trans(\'select-an-option\') }}",'. "\n" .
        '    minimumInputLength: 1,'. "\n" .
        '    allowClear: true,'. "\n" .
        '    ajax: {'. "\n" .
        '        url: apiUrl,'. "\n" .
        '        dataType: "json",'. "\n" .
        '        quietMillis: 250,'. "\n" .
        '        data: function (params) {'. "\n" .
        '            /* Query parameters will be ?search=[term]&page=[page] */'. "\n" .
        '            return {'. "\n" .
        '                search: params.term,'. "\n" .
        '                page: params.page || 1'. "\n" .
        '            };'. "\n" .
        '        },'. "\n" .
        '        processResults: formatProcessResults,'. "\n" .
        '        cache: false'. "\n" .
        '    },'. "\n" .
        '    initSelection: formatInitSelection,'. "\n" .
        '    templateSelection: formatTemplateSelection'. "\n" .
        '});'. "\n" .
        "\n\n" .
        'function formatProcessResults (data, params) {'. "\n" .
        '    params.page = params.page || 1;'. "\n" .
        '    var items = [];'. "\n" .
        '    data.forEach(function(element) {'. "\n" .
        '        item = {'. "\n" .
        '            id: element.' . $fieldCode . ','. "\n" .
        '            text: element.' . $fieldTitle . ''. "\n" .
        '        };'. "\n" .
        '        items.push(item);'. "\n" .
        '    });'. "\n" .
        ''. "\n" .
        '    return {'. "\n" .
        '        results: items,'. "\n" .
        '        pagination : {'. "\n" .
        '            more: (params.page * 10) < data.count_filtered'. "\n" .
        '        }'. "\n" .
        '    };'. "\n" .
        '}'. "\n" .
        "\n\n" .
        'function formatInitSelection(element, callback) {'. "\n" .
        '    /* The input tag has a value attribute preloaded that points to a preselected repository\'s id'. "\n" .
        '     * this function resolves that id attribute to an object that select2 can render'. "\n" .
        '     * using its formatResult renderer - that way the repository name is shown preselected'. "\n" .
        '     */'. "\n" .
        '    var id = $(element).val();'. "\n" .
        '    if (id !== "") {'. "\n" .
        '        $.ajax(apiUrl + "&cod=" + id, {'. "\n" .
        '            dataType: "json"'. "\n" .
        '        }).done(function(data) { callback(data); });'. "\n" .
        '    }'. "\n" .
        '}'. "\n" .
        "\n\n" .
        'function formatTemplateSelection (reply) {'. "\n" .
        '    return reply.' . $fieldTitle . ';'. "\n" .
        '}'. "\n";

        return $jquery;
    }
}
