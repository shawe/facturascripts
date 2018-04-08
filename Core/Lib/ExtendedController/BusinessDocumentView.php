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

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Base\DivisaTools;
use FacturaScripts\Core\Base\MiniLog;
use FacturaScripts\Core\Base\Utils;
use FacturaScripts\Dinamic\Lib\BusinessDocumentTools;
use FacturaScripts\Dinamic\Lib\ExportManager;
use FacturaScripts\Dinamic\Model\Base\BusinessDocumentLine;
use FacturaScripts\Dinamic\Model;

/**
 * Description of BusinessDocumentView
 *
 * @author Carlos García Gómez
 */
class BusinessDocumentView extends BaseView
{

    /**
     * List of document states.
     *
     * @var Model\EstadoDocumento[]
     */
    public $documentStates;

    /**
     * Lines of document, the body.
     *
     * @var BusinessDocumentLine[]
     */
    public $lines;

    /**
     * Common tools to manipulate business documents.
     *
     * @var BusinessDocumentTools
     */
    private $documentTools;

    /**
     * Line columns from xmlview.
     *
     * @var array
     */
    private $lineOptions;

    /**
     * DocumentView constructor and initialization.
     *
     * @param string $title
     * @param string $modelName
     * @param string $lineXMLView
     * @param string $userNick
     */
    public function __construct(string $title, string $modelName, string $lineXMLView, string $userNick)
    {
        parent::__construct($title, $modelName);
        $this->documentStates = [];
        $this->documentTools = new BusinessDocumentTools();

        // Loads the view configuration for the user
        $this->pageOption->getForUser($lineXMLView, $userNick);

        $this->lineOptions = [];
        foreach ($this->pageOption->columns['root']->columns as $col) {
            $this->lineOptions[] = $col;
        }

        $this->lines = [];

        // Loads document states
        $estadoDocModel = new Model\EstadoDocumento();
        $modelClass = explode('\\', $modelName);
        $this->documentStates = $estadoDocModel->all([new DataBaseWhere('tipodoc', end($modelClass))], ['nombre' => 'ASC'], 0, 0);
    }

    /**
     * Returns the data of lines to the view.
     *
     * @return string
     */
    public function getLineData(): string
    {
        $data = [
            'headers' => [],
            'columns' => [],
            'rows' => []
        ];

        foreach ($this->lineOptions as $col) {
            $item = [
                'data' => $col->widget->fieldName,
                'type' => $col->widget->type,
            ];

            if ($item['type'] === 'number' || $item['type'] === 'money') {
                $item['type'] = 'numeric';
                $item['numericFormat'] = DivisaTools::gridMoneyFormat();
            } elseif ($item['type'] === 'autocomplete') {
                $item['source'] = $col->widget->values[0];
                $item['strict'] = false;
                $item['visibleRows'] = 5;
                $item['trimDropdown'] = false;
            }

            if ($col->display !== 'none') {
                $data['columns'][] = $item;
                $data['headers'][] = self::$i18n->trans($col->title);
            }
        }

        foreach ($this->lines as $line) {
            $lineArray = [];
            foreach ($line->getModelFields() as $key => $field) {
                $lineArray[$key] = $line->{$key};
            }
            $lineArray['descripcion'] = Utils::fixHtml($lineArray['descripcion']);
            $data['rows'][] = $lineArray;
        }

        return json_encode($data);
    }

    /**
     * Method to export the view data
     *
     * @param ExportManager $exportManager
     */
    public function export(ExportManager $exportManager)
    {
        $exportManager->generateDocumentPage($this->model);
    }

    /**
     * Load the data in the cursor property, according to the where filter specified.
     * Adds an empty row/model at the end of the loaded data.
     *
     * @param string $code
     */
    public function loadData(string $code)
    {
        if ($this->newCode !== null) {
            $code = $this->newCode;
        }

        $this->model->loadFromCode($code);
        $this->count = empty($this->model->primaryColumnValue()) ? 0 : 1;
        $this->lines = empty($this->model->primaryColumnValue()) ? [] : $this->model->getLines();
        $this->title = $this->model->codigo;
    }

    /**
     * Loads data, recalculate document and returns a json with the results.
     *
     * @param array $data
     *
     * @return string
     */
    public function recalculateDocument(array &$data): string
    {
        $newLines = isset($data['lines']) ? $this->processFormLines($data['lines']) : [];
        unset($data['lines']);
        $this->loadFromData($data);

        return $this->documentTools->recalculateForm($this->model, $newLines);
    }

    /**
     * Save all document related data.
     *
     * @param array $data
     *
     * @return string
     */
    public function saveDocument(array &$data): string
    {
        $result = 'OK';
        $codcliente = $data['codcliente'] ?? '';
        $codproveedor = $data['codproveedor'] ?? '';
        $newLines = isset($data['lines']) ? $this->processFormLines($data['lines']) : [];
        unset($data['codcliente'], $data['codproveedor'], $data['lines']);
        $this->loadFromData($data);
        $this->model->setDate($this->model->fecha, $this->model->hora);
        $this->lines = empty($this->model->primaryColumnValue()) ? [] : $this->model->getLines();

        if (\in_array('codcliente', $this->model->getSubjectColumns(), false)) {
            $result = $this->setCustomer($codcliente, $data['new_cliente'], $data['new_cifnif']);
        }
        if (\in_array('codproveedor', $this->model->getSubjectColumns(), false)) {
            $result = $this->setSupplier($codproveedor, $data['new_proveedor'], $data['new_cifnif']);
        }

        if ($result !== 'OK') {
            return $result;
        }

        $exists = $this->model->exists();
        if ($this->save()) {
            $result = ($this->model->editable || !$exists) ? $this->saveLines($newLines) : 'OK';
        } else {
            $result = 'ERROR';
        }

        if ($result === 'OK') {
            $this->documentTools->recalculate($this->model);
            return $this->model->save() ? 'OK:' . $this->model->url() : 'ERROR';
        }

        $miniLog = new MiniLog();
        foreach ($miniLog->read() as $msg) {
            $result = $msg['message'];
        }

        return $result;
    }

    /**
     * Updates oldLine with newLine data.
     *
     * @param mixed $oldLine
     * @param array $newLine
     *
     * @return bool
     */
    protected function updateLine($oldLine, array $newLine): bool
    {
        foreach ($newLine as $key => $value) {
            $oldLine->{$key} = $value;
        }

        $oldLine->pvpsindto = $oldLine->pvpunitario * $oldLine->cantidad;
        $oldLine->pvptotal = $oldLine->pvpsindto * (100 - $oldLine->dtopor) / 100;

        if ($oldLine->save()) {
            return $oldLine->updateStock($this->model->codalmacen);
        }

        return false;
    }

    /**
     * Process form lines to assign only configurated columns.
     * Also adds order column.
     *
     * @param array $formLines
     *
     * @return array
     */
    protected function processFormLines(array $formLines): array
    {
        $newLines = [];
        $order = count($formLines);
        foreach ($formLines as $data) {
            $line = ['orden' => $order];
            foreach ($this->lineOptions as $col) {
                $line[$col->widget->fieldName] = $data[$col->widget->fieldName] ?? null;
            }
            $newLines[] = $line;
            $order--;
        }

        return $newLines;
    }

    /**
     * Set the customer for this model.
     *
     * @param string $codcliente
     * @param string $newCliente
     * @param string $newCifnif
     *
     * @return string
     */
    private function setCustomer(string $codcliente, string $newCliente = '', string $newCifnif = ''): string
    {
        if ($this->model->codcliente === $codcliente && !empty($this->model->codcliente)) {
            return 'OK';
        }

        $cliente = new Model\Cliente();
        if ($cliente->loadFromCode($codcliente)) {
            $this->model->setSubject([$cliente]);
            return 'OK';
        }

        if ($newCliente !== '') {
            $cliente->nombre = $cliente->razonsocial = $newCliente;
            $cliente->cifnif = $newCifnif;
            if ($cliente->save()) {
                return $this->setCustomer($cliente->codcliente);
            }
        }

        return 'ERROR: NO CUSTOMER';
    }

    /**
     * Set the supplier for this model.
     *
     * @param string $codproveedor
     * @param string $newProveedor
     * @param string $newCifnif
     *
     * @return string
     */
    private function setSupplier(string $codproveedor, string $newProveedor = '', string $newCifnif = ''): string
    {
        if ($this->model->codproveedor === $codproveedor && !empty($this->model->codproveedor)) {
            return 'OK';
        }

        $proveedor = new Model\Proveedor();
        if ($proveedor->loadFromCode($codproveedor)) {
            $this->model->setSubject([$proveedor]);
            return 'OK';
        }

        if ($newProveedor !== '') {
            $proveedor->nombre = $proveedor->razonsocial = $newProveedor;
            $proveedor->cifnif = $newCifnif;
            if ($proveedor->save()) {
                return $this->setSupplier($proveedor->codproveedor);
            }
        }

        return 'ERROR: NO SUPPLIER';
    }

    /**
     * Saves the lines for the document.
     *
     * @param array $newLines
     *
     * @return string
     */
    private function saveLines(array &$newLines): string
    {
        $result = 'OK';

        /// remove or modify old lines
        foreach ($this->lines as $oldLine) {
            $found = false;
            foreach ($newLines as $newLine) {
                if ($newLine['idlinea'] === $oldLine->idlinea) {
                    $found = true;
                    if (!$this->updateLine($oldLine, $newLine)) {
                        $result = 'ERROR ON LINE: ' . $oldLine->idlinea;
                    }
                    break;
                }
            }

            if (!$found) {
                $oldLine->delete();
                $oldLine->updateStock($this->model->codalmacen);
            }
        }

        /// add new lines
        $skip = true;
        foreach (array_reverse($newLines) as $fLine) {
            if ($skip && empty($fLine['referencia']) && empty($fLine['descripcion'])) {
                continue;
            }

            if (empty($fLine['idlinea'])) {
                $newDocLine = $this->model->getNewLine($fLine);
                $newDocLine->pvpsindto = $newDocLine->pvpunitario * $newDocLine->cantidad;
                $newDocLine->pvptotal = $newDocLine->pvpsindto * (100 - $newDocLine->dtopor) / 100;

                if ($newDocLine->save()) {
                    $newDocLine->updateStock($this->model->codalmacen);
                } else {
                    $result = 'ERROR ON NEW LINE';
                }
                $skip = false;
            }
        }

        return $result;
    }
}
