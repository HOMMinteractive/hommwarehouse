<?php

namespace homm\hommwarehouse\exporters;

use Craft;
use craft\base\ElementExporter;
use craft\base\ElementInterface;
use craft\elements\db\ElementQuery;
use craft\elements\db\ElementQueryInterface;
use craft\elements\GlobalSet;
use craft\helpers\Db;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class WarehouseExporter extends ElementExporter
{
    public static function isFormattable(): bool
    {
        return false;
    }

    public static function displayName(): string
    {
        return Craft::t('hommwarehouse', 'Warehouse');
    }

    /**
     * @inheritdoc
     */
    public function export(ElementQueryInterface $query): mixed
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheetRow = 1;

        $globalAddress = GlobalSet::find()->handle('globalAddress')->one();

        $sheet->fromArray(['Wein-Inventar - ' . $globalAddress->globalAddressContactCompany . ' - ' . date('d.m.Y')], null, 'A' . $sheetRow++);
        $sheet->getStyle('A' . $sheetRow - 1)->getFont()->setSize(20);
        $sheet->getStyle('A' . $sheetRow - 1)->getFont()->setBold(true);
        $sheet->fromArray([], null, 'A' . $sheetRow++);

        $cols = [
            'Nummer',
            'Weinname',
            'Kategorie',
            'Land',
            'Region',
            'Jahrgang',
            'Rebsorte',
            'Produzent',
            'Lagerbestand',
            'Flaschengroesse',
            'Lagerbestand Total',
            'Preise Flaschen',
            'Flaschen Total',
            'Ausgetrunken',
        ];
        $sheet->fromArray($cols, null, 'A' . $sheetRow++);

        foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N'] as $col) {
            $sheet->getStyle($col . $sheetRow - 1)->getFont()->setBold(true);
        }

        $eagerLoadableFieldHandles = ['weinKategorie', 'weinRegion', 'weinJahrgange', 'weinProduzent', 'weinRebsorten', 'weinFlaschengrossen'];
        $eagerLoadableSuperTableFieldHandles = ['weinFlaschengrossen:grosse', 'weinFlaschengrossen:lagerbestand', 'weinFlaschengrossen:einkaufspreis', 'weinRebsorten:rebsorte'];
        $eagerLoadableFields = [];
        foreach ([...$eagerLoadableFieldHandles, ...$eagerLoadableSuperTableFieldHandles] as $key => $value) {
            $eagerLoadableFields[] = [
                'path' => $value,
                'criteria' => [
                    'status' => null,
                ],
            ];
        }

        /** @var ElementQuery $query */
        $query->with($eagerLoadableFields);

        $sheetCol = 1;
        $totalLagerbestand = 0;
        $totalFlaschenTotal = 0;

        foreach (Db::each($query) as $element) {
            $elementArr = $element->toArray(['title', 'weinLagerbestand', 'weinAusgetrunken', 'lagerbestandTotal']);
            foreach ($eagerLoadableFieldHandles as $handle) {
                $values = [];

                if ($handle === 'weinRebsorten') {
                    $values = [];
                    $rebsorten = $element->getFieldValue($handle);

                    foreach ($rebsorten->all() as $block) {
                        $rebsorte = $block->rebsorte;
                        if ($rebsorte && isset($rebsorte[0]->title)) {
                            $values[] = $rebsorte[0]->title;
                        }
                    }
                    $elementArr[$handle] = join(' | ', array_filter($values));
                } elseif ($handle === 'weinFlaschengrossen') {
                    $groessen = [];
                    $lagerbestaende = [];
                    $einkaufspreise = [];
                    $flaschenTotalArray = [];
                    $flaschenTotalSum = 0;

                    $flaschengrossen = $element->getFieldValue($handle);

                    foreach ($flaschengrossen->all() as $block) {
                        $grosse = $block->grosse;
                        $lagerbestandData = $block->lagerbestand;
                        $einkaufspreis = $block->einkaufspreis;

                        if ($grosse && isset($grosse[0])) {
                            $groessen[] = $grosse[0]->title;
                        }

                        $lagerbestand = 0;
                        if ($lagerbestandData && isset($lagerbestandData['stock']) && $lagerbestandData['stock'] !== '') {
                            $stockValue = $lagerbestandData['stock'];
                            $lagerbestand = is_numeric($stockValue) ? floatval($stockValue) : 0;
                            $lagerbestaende[] = $lagerbestand;
                        } else {
                            $lagerbestaende[] = '0';
                        }

                        $preis = 0;
                        if ($einkaufspreis !== null && $einkaufspreis !== '') {
                            $preis = is_numeric($einkaufspreis) ? floatval($einkaufspreis) : 0;
                            $einkaufspreise[] = number_format($preis, 2);
                        } else {
                            $einkaufspreise[] = '0.00';
                        }

                        $total = $lagerbestand * $preis;
                        $flaschenTotalArray[] = number_format($total, 2);
                        $flaschenTotalSum += $total;
                    }

                    $elementArr['weinFlaschengrossen'] = join(' | ', array_filter($groessen));
                    $elementArr['weinFlaschenlagerbestand'] = array_filter($lagerbestaende, fn($v) => $v > 0) ? join(' | ', $lagerbestaende) : '';
                    $elementArr['weinEinkaufspreis'] = array_filter($einkaufspreise, fn($v) => $v > 0) ? join(' | ', $einkaufspreise) : '';
                    $elementArr['flaschenTotalArray'] = $flaschenTotalArray;
                    $elementArr['flaschenTotalSum'] = $flaschenTotalSum;
                } elseif ($handle === 'weinRegion') {
                    $values = array_column($element->getFieldValue($handle)->toArray(), 'title');
                    $elementArr['Land'] = $values[0] ?? '';
                    unset($values[0]);
                    $elementArr[$handle] = join(' | ', array_filter($values));
                } else {
                    $values = array_column($element->getFieldValue($handle)->toArray(), 'title');
                    $elementArr[$handle] = join(' | ', array_filter($values));
                }
            }

            $sheet->fromArray([
                'Nummer' => $sheetCol++,
                'Weinname' => $elementArr['title'],
                'Kategorie' => $elementArr['weinKategorie'],
                'Land' => $elementArr['Land'],
                'Region' => $elementArr['weinRegion'],
                'Jahrgang' => $elementArr['weinJahrgange'],
                'Rebsorte' => $elementArr['weinRebsorten'],
                'Produzent' => $elementArr['weinProduzent'],
                'Lagerbestand' => $elementArr['weinFlaschenlagerbestand'],
                'Flaschengroesse' => $elementArr['weinFlaschengrossen'],
                'Lagerbestand Total' => $elementArr['lagerbestandTotal'] ?: '',
                'Preise Flaschen' => $elementArr['weinEinkaufspreis'],
                'Flaschen Total' => $elementArr['flaschenTotalSum'] > 0 ? join(' | ', $elementArr['flaschenTotalArray']) : '',
                'Ausgetrunken' => $elementArr['weinAusgetrunken'] ? strtoupper(Craft::t('hommwarehouse', 'finished')) : '',
            ], null, 'A' . $sheetRow++);

            $totalLagerbestand += $elementArr['lagerbestandTotal'] ?: 0;
            $totalFlaschenTotal += $elementArr['flaschenTotalSum'] ?: 0;

            $sheet->getStyle('F')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('I')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('K')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('L')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('M')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

            if (($sheetRow - 1) % 2 === 0) {
                foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N'] as $col) {
                    $sheet->getStyle($col . $sheetRow - 1)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('00EDEDED');
                }
            }
        }

        $sheet->fromArray([
            '', // Nummer
            '', // Weinname
            '', // Kategorie
            '', // Land
            '', // Region
            '', // Jahrgang
            '', // Rebsorte
            '', // Produzent
            '', // Lagerbestand
            '', // Flaschengroesse
            'Total: ' . number_format($totalLagerbestand, 0), // Lagerbestand Total
            '', // Preise Flaschen
            'Total: ' . number_format($totalFlaschenTotal, 2), // Flaschen Total
            '', // Ausgetrunken
        ], null, 'A' . $sheetRow++);

        $sheet->getStyle('K' . ($sheetRow - 1))->getFont()->setBold(true);
        $sheet->getStyle('K' . ($sheetRow - 1))->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('00DDDDDD');
        $sheet->getStyle('M' . ($sheetRow - 1))->getFont()->setBold(true);
        $sheet->getStyle('M' . ($sheetRow - 1))->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('00DDDDDD');

        $sheet->getColumnDimension('B')->setWidth(40);
        $sheet->getColumnDimension('C')->setWidth(17);
        $sheet->getColumnDimension('D')->setWidth(17);
        $sheet->getColumnDimension('E')->setWidth(17);
        $sheet->getColumnDimension('F')->setWidth(25);
        $sheet->getColumnDimension('G')->setWidth(40);
        $sheet->getColumnDimension('H')->setWidth(40);
        $sheet->getColumnDimension('I')->setWidth(25);
        $sheet->getColumnDimension('J')->setWidth(25);
        $sheet->getColumnDimension('K')->setWidth(17);
        $sheet->getColumnDimension('L')->setWidth(25);
        $sheet->getColumnDimension('M')->setWidth(25);
        $sheet->getColumnDimension('N')->setWidth(25);

        $tempPath = Craft::$app->getPath()->getTempPath() . '/' . rand() . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        $data = file_get_contents($tempPath);
        unlink($tempPath);

        return $data;
    }

    public function getFilename(): string
    {
        /** @var ElementInterface $elementType */
        $elementType = $this->elementType;
        return $elementType::pluralLowerDisplayName() . '.xlsx';
    }
}
