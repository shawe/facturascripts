<?php
/**
 * This file is part of FacturaScripts
 * Copyright (C) 2017-2018  Carlos Garcia Gomez  <carlos@facturascripts.com>
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

namespace FacturaScripts\Core\Lib\ExtendedController;

/**
 * Description of WidgetButton
 *
 * @author Artex Trading sa <jcuello@artextrading.com>
 */
class WidgetButton implements VisualItemInterface
{

    /**
     * Type of button.
     *
     * @var string
     */
    public $type;

    /**
     * Aditional code for the button.
     *
     * @var string
     */
    public $hint;

    /**
     * Icon for the button.
     *
     * @var string
     */
    public $icon;

    /**
     * JavaScritp action for the button.
     *
     * @var string
     */
    public $onClick;

    /**
     * Label for the button.
     *
     * @var string
     */
    public $label;

    /**
     * Action for the button.
     *
     * @var string
     */
    public $action;

    /**
     * Color for the button.
     *
     * @var string
     */
    public $color;

    /**
     * Unique ID for button
     *
     * @var string
     */
    public $id;

    /**
     * WidgetButton constructor.
     */
    public function __construct()
    {
        $this->type = 'action';
        $this->label = '';
        $this->icon = '';
        $this->action = '';
        $this->onClick = '';
        $this->color = 'light';
        $this->hint = '';
        $this->id = '';
    }

    /**
     * Create and load the structure of attributes from a XML file.
     *
     * @param \SimpleXMLElement $button
     *
     * @return WidgetButton
     */
    public static function newFromXML($button): WidgetButton
    {
        $widget = new self();
        $widget->loadFromXML($button);

        return $widget;
    }

    /**
     * Create and load element structure from JSON file
     *
     * @param array $button
     *
     * @return WidgetButton
     */
    public static function newFromJSON($button): WidgetButton
    {
        $widget = new self();
        $widget->loadFromJSON($button);

        return $widget;
    }

    /**
     * Loads the attributes structure from a XML file
     *
     * @param \SimpleXMLElement $button
     */
    public function loadFromXML($button)
    {
        $widgetAtributes = $button->attributes();
        $this->type = (string) $widgetAtributes->type;
        $this->label = (string) $widgetAtributes->label;
        $this->icon = (string) $widgetAtributes->icon;
        $this->action = (string) $widgetAtributes->action;
        $this->hint = (string) $widgetAtributes->hint;
        $this->id = $this->getOptionalAtribute('id', $widgetAtributes);
        $this->color = $this->getOptionalAtribute('color', $widgetAtributes);
        $this->onClick = $this->getOptionalAtribute('onclick', $widgetAtributes);
    }

    /**
     * Loads the attributes structure from a JSON file
     *
     * @param array $button
     */
    public function loadFromJSON($button)
    {
        $this->type = (string) $button['type'];
        $this->label = (string) $button['label'];
        $this->icon = (string) $button['icon'];
        $this->action = (string) $button['action'];
        $this->hint = (string) $button['hint'];
        $this->color = (string) $button['color'];
        $this->onClick = (string) $button['onClick'];
    }

    /**
     * Generate the html code to visualize the visual element header
     *
     * @param string $value
     *
     * @return string
     */
    public function getHeaderHTML($value): string
    {
        return '';
    }

    /**
     * Array with list of personalization functions of the column
     */
    public function columnFunction(): array
    {
        return ['ColumnClass', 'ColumnHint', 'ColumnDescription'];
    }

    /**
     * Returns the HTML code to display a button
     *
     * @param string $label
     * @param string $value
     * @param string $hint
     * @param string $class
     *
     * @return string
     */
    public function getHTML($label, $value = '', $hint = '', $class = 'col-sm-auto'): string
    {
        switch ($this->type) {
            case 'calculate':
                return $this->getCalculateHTML($label, $value, $hint);

            case 'action':
                return $this->getActionHTML($label, $hint, $value, $class);

            case 'modal':
                return $this->getModalHTML($label, $class);

            default:
                return '';
        }
    }

    /**
     * Returns the HTML code to show a popover with the received text.
     *
     * @param string $hint
     *
     * @return string
     */
    public function getHintHTML($hint): string
    {
        return empty($hint) ? '' : ' data-toggle="popover" data-placement="auto" data-trigger="hover" data-content="'
            . $hint . '" ';
    }

    /**
     * Return optional atribute value
     *
     * @param string $field
     * @param mixed $atributes
     *
     * @return string
     */
    private function getOptionalAtribute($field, &$atributes): string
    {
        if (!empty($atributes->{$field})) {
            return (string) $atributes->{$field};
        }
        return '';
    }

    /**
     * Returns the HTML code for the icon
     *
     * @return string
     */
    private function getIconHTML(): string
    {
        return empty($this->icon) ? '' : '<i class="fa ' . $this->icon . '"></i>&nbsp;&nbsp;';
    }

    /**
     * Returns the HTML code for the id
     *
     * @return string
     */
    private function getIdHTML(): string
    {
        return empty($this->id) ? '' : ' id="' . $this->id . '" ';
    }

    /**
     * Returns the HTML code for the onclick event
     *
     * @param string $onclick
     * @param string $addParam
     *
     * @return string
     */
    private function getOnClickHTML($onclick, $addParam = ''): string
    {
        if (empty($onclick)) {
            return '';
        }

        if (empty($addParam)) {
            return ' onclick="' . $onclick . '" ';
        }

        $pos = strpos($onclick, ')');
        if ($pos === false) {
            return ' onclick="' . $onclick . '(' . $addParam . ')" ';
        }

        if ($onclick[$pos - 1] !== '(') {
            $addParam = ', ' . $addParam;
        }
        return ' onclick="' . substr($onclick, 0, $pos) . $addParam . ')" ';
    }

    /**
     * Returns the HTML code to display a statistic button
     *
     * @param string $label
     * @param string $value
     * @param string $hint
     *
     * @return string
     */
    private function getCalculateHTML($label, $value, $hint): string
    {
        $html = '<button type="button" class="btn btn-' . $this->color . '" '
            . $this->getIdHTML()
            . $this->getOnClickHTML($this->onClick) . ' style="margin-right: 5px;" ' . $hint . '>'
            . $this->getIconHTML()
            . '<span class="cust-text">' . $label . ' ' . $value . '</span></button>';

        return $html;
    }

    /**
     * Returns the HTML code to display an action button
     *
     * @param string $label
     * @param string $hint
     * @param string $formName
     * @param string $class
     *
     * @return string
     */
    private function getActionHTML($label, $hint, $formName = 'main_form', $class = 'col-sm-auto'): string
    {
        $onclick = empty($this->onClick) ? 'execActionForm()' : $this->onClick;
        $param = '\'' . $formName . '\',\'' . $this->action . '\'';

        $html = '<button type="button" class="' . $class . ' btn btn-' . $this->color . '" '
            . $this->getIdHTML()
            . $this->getOnClickHTML($onclick, $param) . $hint . ' >'
            . $this->getIconHTML()
            . $label
            . '</button>';

        return $html;
    }

    /**
     * Returns the HTML code to display a button that links to a modal form
     *
     * @param string $label
     * @param string $class
     *
     * @return string
     */
    private function getModalHTML($label, $class = 'col-sm-auto'): string
    {
        $html = '<button type="button" class="' . $class . ' btn btn-' . $this->color . '" '
            . $this->getIdHTML()
            . ' data-toggle="modal" data-target="#' . $this->action . '" >'
            . $this->getIconHTML()
            . $label
            . '</button>';

        return $html;
    }
}
