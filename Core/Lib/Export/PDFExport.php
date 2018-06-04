<?php
/**
 * This file is part of FacturaScripts
 * Copyright (C) 2017-2018  Carlos García Gómez  <carlos@facturascripts.com>
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

namespace FacturaScripts\Core\Lib\Export;

use FacturaScripts\Core\App\AppSettings;
use FacturaScripts\Core\Base;
use FacturaScripts\Dinamic\Model\Base\BusinessDocument;
use FacturaScripts\Dinamic\Model\Empresa;
use Symfony\Component\HttpFoundation\Response;

/**
 * PDF export data.
 *
 * @author Carlos García Gómez <carlos@facturascripts.com>
 * @author Carlos Jiménez Gómez <carlos@evolunext.es>
 */
class PDFExport implements ExportInterface
{

    const LIST_LIMIT = 500;

    /**
     * Y position to start footer
     */
    const FOOTER_Y = 10;

    /**
     * X position to start writting.
     */
    const CONTENT_X = 30;

    /**
     * Default text size for footer and header.
     */
    const TEXT_SIZE_FH = 9;
    /**
     * PDF object.
     *
     * @var \Cezpdf
     */
    protected $pdf;
    /**
     * Tools to work with currencies.
     *
     * @var Base\DivisaTools
     */
    private $divisaTools;
    /**
     * Translator object
     *
     * @var Base\Translator
     */
    private $i18n;
    /**
     * Class with number tools (to format numbers)
     *
     * @var Base\NumberTools
     */
    private $numberTools;
    /**
     * PDF table width.
     *
     * @var int|float
     */
    private $tableWidth;

    /**
     * PDFExport constructor.
     */
    public function __construct()
    {
        $this->divisaTools = new Base\DivisaTools();
        $this->i18n = new Base\Translator();
        $this->numberTools = new Base\NumberTools();
        $this->tableWidth = 0.0;
    }

    /**
     * Return the full document.
     *
     * @return mixed
     */
    public function getDoc()
    {
        if ($this->pdf === null) {
            $this->newPage();
            $this->pdf->ezText('');
        }

        return $this->pdf->ezStream(['Content-Disposition' => 'doc_' . random_int(1, 999999) . '.pdf']);
    }

    /**
     * Blank document.
     */
    public function newDoc()
    {
    }

    /**
     * Set headers and output document content to response.
     *
     * @param Response $response
     */
    public function show(Response $response)
    {
        $response->headers->set('Content-type', 'application/pdf');
        $response->setContent($this->getDoc());
    }

    /**
     * Adds a new page with the model data.
     *
     * @param mixed  $model
     * @param array  $columns
     * @param string $title
     */
    public function generateModelPage($model, $columns, $title = '')
    {
        $this->newPage();
        $tableCols = [];
        $tableColsTitle = [];
        $tableOptions = [
            'width' => $this->tableWidth,
            'showHeadings' => 0,
            'shaded' => 0,
            'lineCol' => [1, 1, 1],
            'cols' => [],
        ];

        /// Get the columns
        $this->setTableColumns($columns, $tableCols, $tableColsTitle, $tableOptions);

        $tableDataAux = [];
        foreach ($tableColsTitle as $key => $colTitle) {
            $value = null;
            if (isset($model->{$key})) {
                $value = $model->{$key};
            }

            if (\is_bool($value)) {
                $txt = $this->i18n->trans($value ? 'yes' : 'no');
                $tableDataAux[] = ['key' => $colTitle, 'value' => $txt];
            } elseif ($value !== null && $value !== '') {
                $value = \is_string($value) ? Base\Utils::fixHtml($value) : $value;
                $tableDataAux[] = ['key' => $colTitle, 'value' => $value];
            }
        }

        $this->pdf->ezText($title . \PHP_EOL, 12, ['justification' => 'center']);
        $this->newLine();

        $tableData = $this->paralellTableData($tableDataAux, 'key', 'value', 'data1', 'data2');
        $this->pdf->ezTable($tableData, ['data1' => 'data1', 'data2' => 'data2'], '', $tableOptions);
    }

    /**
     * Adds a new page with a table listing the models data.
     *
     * @param mixed                         $model
     * @param Base\DataBase\DataBaseWhere[] $where
     * @param array                         $order
     * @param int                           $offset
     * @param array                         $columns
     * @param string                        $title
     */
    public function generateListModelPage($model, $where, $order, $offset, $columns, $title = '')
    {
        $orientation = 'portrait';
        $tableCols = [];
        $tableColsTitle = [];
        $tableOptions = ['cols' => [], 'shadeCol' => [0.95, 0.95, 0.95], 'shadeHeadingCol' => [0.95, 0.95, 0.95]];
        $tableData = [];
        $longTitles = [];

        /// Get the columns
        $this->setTableColumns($columns, $tableCols, $tableColsTitle, $tableOptions);
        if (count($tableCols) > 5) {
            $orientation = 'landscape';
            $this->removeLongTitles($longTitles, $tableColsTitle);
        }

        $this->newPage($orientation);
        $tableOptions['width'] = $this->tableWidth;

        $cursor = $model->all($where, $order, $offset, self::LIST_LIMIT);
        if (empty($cursor)) {
            $this->pdf->ezTable($tableData, $tableColsTitle, '', $tableOptions);
        }
        while (!empty($cursor)) {
            $tableData = $this->getTableData($cursor, $tableCols, $tableOptions);
            $this->removeEmptyCols($tableData, $tableColsTitle);
            $this->pdf->ezTable($tableData, $tableColsTitle, $title, $tableOptions);

            /// Advance within the results
            $offset += self::LIST_LIMIT;
            $cursor = $model->all($where, $order, $offset, self::LIST_LIMIT);
        }

        $this->newLongTitles($longTitles);
    }

    /**
     * Adds a new page with the document data.
     *
     * @param BusinessDocument $model
     */
    public function generateDocumentPage($model)
    {
        $columns = [];
        foreach (array_keys((array) $model) as $key) {
            $columns[$key] = $key;
        }
        $this->generateModelPage($model, $columns, $model->primaryDescription());

        $this->pdf->ezText(\PHP_EOL);

        $headers = [
            'reference' => $this->i18n->trans('reference+description'),
            'quantity' => $this->i18n->trans('quantity'),
            'price' => $this->i18n->trans('price'),
            'discount' => $this->i18n->trans('discount'),
            'tax' => $this->i18n->trans('tax'),
            'total' => $this->i18n->trans('total'),
        ];
        $tableData = [];
        foreach ($model->getLines() as $line) {
            $tableData[] = [
                'reference' => Base\Utils::fixHtml($line->referencia . ' - ' . $line->descripcion),
                'quantity' => $this->numberTools::format($line->cantidad),
                'price' => $this->numberTools::format($line->pvpunitario),
                'discount' => $this->numberTools::format($line->dtopor),
                'tax' => $this->numberTools::format($line->iva),
                'total' => $this->numberTools::format($line->pvptotal),
            ];
        }

        $tableOptions = [
            'cols' => [
                'quantity' => ['justification' => 'right'],
                'price' => ['justification' => 'right'],
                'discount' => ['justification' => 'right'],
                'tax' => ['justification' => 'right'],
                'total' => ['justification' => 'right'],
            ],
            'shadeCol' => [0.95, 0.95, 0.95],
            'shadeHeadingCol' => [0.95, 0.95, 0.95],
            'width' => $this->tableWidth
        ];
        $this->removeEmptyCols($tableData, $headers);
        $this->pdf->ezTable($tableData, $headers, '', $tableOptions);
    }

    /**
     * Adds a new page with the table.
     *
     * @param array $headers
     * @param array $rows
     */
    public function generateTablePage($headers, $rows)
    {
        $orientation = 'portrait';
        if (count($headers) > 5) {
            $orientation = 'landscape';
        }

        $this->newPage($orientation);
        $tableOptions = ['width' => $this->tableWidth];
        $this->pdf->ezTable($rows, $headers, '', $tableOptions);
    }

    /**
     * Adds a new page.
     *
     * @param string $orientation
     */
    protected function newPage($orientation = 'portrait')
    {
        if ($this->pdf === null) {
            $this->pdf = new \Cezpdf('a4', $orientation);
            $this->pdf->addInfo('Creator', 'FacturaScripts');
            $this->pdf->addInfo('Producer', 'FacturaScripts');
            $this->pdf->tempPath = FS_FOLDER . '/MyFiles/Cache';

            $this->tableWidth = $this->pdf->ez['pageWidth'] - self::CONTENT_X * 2;

            $this->pdf->ezStartPageNumbers($this->pdf->ez['pageWidth'] / 2, self::FOOTER_Y, self::TEXT_SIZE_FH, 'left', '{PAGENUM} / {TOTALPAGENUM}');
        } elseif ($this->pdf->y < 200) {
            $this->pdf->ezNewPage();
        } else {
            $this->pdf->ezText(\PHP_EOL);
        }

        $this->insertHeader();
        $this->insertFooter();
    }

    /**
     * Insert header details.
     */
    protected function insertHeader()
    {
        $headerPos = $this->pdf->ez['pageHeight'] - 25;
        $header = $this->pdf->openObject();
        // Top Left
        $this->pdf->addText(self::CONTENT_X, $headerPos, self::TEXT_SIZE_FH + 2, $this->getCompanyName());
        // Top Center
        //$this->pdf->addText($this->pdf->ez['pageWidth']/2, $headerPos, self::TEXT_SIZE_FH + 2, 'Top Center', 0, 'center');
        // Top Right
        //$this->pdf->addText($this->tableWidth + self::CONTENT_X, $headerPos, self::TEXT_SIZE_FH + 2, 'Top Right', 0, 'right');
        $this->pdf->closeObject();
        $this->pdf->addObject($header, 'all');
    }

    /**
     * Insert footer details.
     */
    protected function insertFooter()
    {
        $footer = $this->pdf->openObject();
        // Bottom Left
        //$this->pdf->addText(self::CONTENT_X, self::FOOTER_Y, self::TEXT_SIZE_FH, 'Bottom Left');
        // Bottom Center
        //$this->pdf->addText($this->pdf->ez['pageWidth']/2, self::FOOTER_Y, self::TEXT_SIZE_FH, 'Bottom Center', 0, 'center');
        // Bottom Right
        $now = $this->i18n->trans('generated-at', ['%when%' => date('d-m-Y H:i')]);
        $this->pdf->addText($this->tableWidth + self::CONTENT_X, self::FOOTER_Y, self::TEXT_SIZE_FH, $now, 0, 'right');
        $this->pdf->closeObject();
        $this->pdf->addObject($footer, 'all');
    }

    /**
     * Adds a new line to the PDF.
     */
    private function newLine()
    {
        $posY = $this->pdf->y + 5;
        $this->pdf->line(self::CONTENT_X, $posY, $this->tableWidth + self::CONTENT_X, $posY);
    }

    /**
     * Adds a description of long titles to the PDF.
     *
     * @param array $titles
     */
    private function newLongTitles(&$titles)
    {
        $txt = '';
        foreach ($titles as $key => $value) {
            if ($txt !== '') {
                $txt .= ', ';
            }

            $txt .= '*' . $key . ' = ' . $value;
        }

        if ($txt !== '') {
            $this->pdf->ezText($txt);
        }
    }

    /**
     * Set the table content.
     *
     * @param array $columns
     * @param array $tableCols
     * @param array $tableColsTitle
     * @param array $tableOptions
     */
    private function setTableColumns(&$columns, &$tableCols, &$tableColsTitle, &$tableOptions)
    {
        foreach ($columns as $col) {
            if (\is_string($col)) {
                $tableCols[$col] = $col;
                $tableColsTitle[$col] = $col;
                continue;
            }

            if (isset($col->columns)) {
                $this->setTableColumns($col->columns, $tableCols, $tableColsTitle, $tableOptions);
                continue;
            }

            if (isset($col->display, $col->widget->fieldName) && $col->display !== 'none') {
                $tableCols[$col->widget->fieldName] = $col->widget->fieldName;
                $tableColsTitle[$col->widget->fieldName] = $this->i18n->trans($col->title);
                $tableOptions['cols'][$col->widget->fieldName] = [
                    'justification' => $col->display,
                    'col-type' => $col->widget->type,
                ];
            }
        }
    }

    /**
     * Returns the table data
     *
     * @param array $cursor
     * @param array $tableCols
     * @param array $tableOptions
     *
     * @return array
     */
    private function getTableData($cursor, $tableCols, $tableOptions): array
    {
        $tableData = [];

        /// Get the data
        foreach ($cursor as $key => $row) {
            foreach ($tableCols as $col) {
                if (!isset($row->{$col})) {
                    $tableData[$key][$col] = '';
                    continue;
                }

                $value = $row->{$col};
                if ($tableOptions['cols'][$col]['col-type'] === 'number') {
                    $value = $this->numberTools::format($value);
                } elseif ($tableOptions['cols'][$col]['col-type'] === 'money') {
                    $this->divisaTools->findDivisa($row);
                    $value = $this->divisaTools::format($value, FS_NF0, 'coddivisa');
                } elseif (\is_bool($value)) {
                    $value = $this->i18n->trans($value === 1 ? 'yes' : 'no');
                } elseif (null === $value) {
                    $value = '';
                } elseif ($tableOptions['cols'][$col]['col-type'] === 'text') {
                    $value = Base\Utils::fixHtml($value);
                }

                $tableData[$key][$col] = $value;
            }
        }

        return $tableData;
    }

    /**
     * Adds to $longTitles, and replace all long titles from $titles
     *
     * @param array $longTitles
     * @param array $titles
     */
    private function removeLongTitles(&$longTitles, &$titles)
    {
        $num = 1;
        foreach ($titles as $key => $value) {
            if (mb_strlen($value) > 12) {
                $longTitles[$num] = $value;
                $titles[$key] = '*' . $num;
                ++$num;
            }
        }
    }

    /**
     * Remove the empty columns to save space.
     *
     * @param array $tableData
     * @param array $tableColsTitle
     */
    private function removeEmptyCols(&$tableData, &$tableColsTitle)
    {
        foreach (array_keys($tableColsTitle) as $key) {
            $remove = true;
            foreach ($tableData as $row) {
                if (!empty($row[$key])) {
                    $remove = false;
                    break;
                }
            }

            if ($remove) {
                unset($tableColsTitle[$key]);
            }
        }
    }

    /**
     * Returns a new table with 2 columns. Each column with colName1: colName2
     *
     * @param array  $table
     * @param string $colName1
     * @param string $colName2
     * @param string $finalColName1
     * @param string $finalColName2
     *
     * @return array
     */
    private function paralellTableData($table, $colName1, $colName2, $finalColName1, $finalColName2): array
    {
        $tableData = [];
        $key = 0;
        foreach ($table as $value) {
            $txt = '<b>' . $value[$colName1] . '</b>: ' . $value[$colName2];

            if (isset($tableData[$key])) {
                $tableData[$key][$finalColName2] = $txt;
                ++$key;
                continue;
            }

            $tableData[$key][$finalColName1] = $txt;
            $tableData[$key][$finalColName2] = '';
        }

        return $tableData;
    }

    /**
     * Returns the company from given id or the default company.
     *
     * @param string $idEmpresa
     *
     * @return string
     */
    private function getCompanyName($idEmpresa = null)
    {
        $idEmpresa = $idEmpresa ?? AppSettings::get('default', 'idempresa', '');
        $empresa = new Empresa();
        $empresa->loadFromCode($idEmpresa);
        return $empresa->nombre ?? '';
    }
}
