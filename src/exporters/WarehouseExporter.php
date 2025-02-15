<?php

namespace homm\hommwarehouse\exporters;

use Craft;
use craft\base\ElementExporter;
use craft\elements\db\ElementQuery;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\Db;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
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

        $globalAddress = \craft\elements\GlobalSet::find()->handle('globalAddress')->one();

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
            'Ausgetrunken',
            'Flaschengroesse',
        ];
        $sheet->fromArray($cols, null, 'A' . $sheetRow++);

        foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K'] as $col) {
            $sheet->getStyle($col . $sheetRow - 1)->getFont()->setBold(true);
        }

        $sheet->getStyle('F' . $sheetRow - 1)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('I' . $sheetRow - 1)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);


        $eagerLoadableFieldHandles = ['weinKategorie', 'weinRegion', 'weinJahrgange', 'weinProduzent', 'weinRebsorten', 'weinFlaschengrossen'];
        $eagerLoadableSuperTableFieldHandles = ['weinFlaschengrossen.grosse', 'weinRebsorten.rebsorte'];
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
        foreach (Db::each($query) as $element) {
            $elementArr = $element->toArray(['title', 'weinLagerbestand', 'weinAusgetrunken']);
            foreach ($eagerLoadableFieldHandles as $handle) {
                $values = [];

                if ($handle === 'weinRebsorten') {
                    foreach ($element->getFieldValue($handle)->toArray() as $value) {
                        $values[] = $value->rebsorte[0]->title;
                    }
                    $elementArr[$handle] = join(' | ', array_filter($values));
                } elseif ($handle === 'weinFlaschengrossen') {
                    foreach ($element->getFieldValue($handle)->toArray() as $value) {
                        $values[] = $value->grosse[0]->title;
                    }
                    $elementArr[$handle] = join(' | ', array_filter($values));
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
                'Nummerierung' => $sheetCol++,
                'Weinname' => $elementArr['title'],
                'Kategorie' => $elementArr['weinKategorie'],
                'Lnad' => $elementArr['Land'],
                'Region' => $elementArr['weinRegion'],
                'Jahrgang' => $elementArr['weinJahrgange'],
                'Rebsorte' => $elementArr['weinRebsorten'],
                'Produzent' => $elementArr['weinProduzent'],
                'Lagerbestand' => $elementArr['weinLagerbestand']['stock'] ?? '0',
                'Ausgetrunken' => $elementArr['weinAusgetrunken'] ? strtoupper(Craft::t('hommwarehouse', 'finished')) : '',
                'Flaschengrösse' => $elementArr['weinFlaschengrossen'],
            ], null, 'A' . $sheetRow++);

            $sheet->getStyle('F' . $sheetRow - 1)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('I' . $sheetRow - 1)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            if (($sheetRow - 1) % 2 === 0) {
                foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K'] as $col) {
                    $sheet->getStyle($col . $sheetRow - 1)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('00EDEDED');
                }
            }
        }

        $sheet->getColumnDimension('B')->setWidth(40);
        $sheet->getColumnDimension('C')->setWidth(17);
        $sheet->getColumnDimension('D')->setWidth(17);
        $sheet->getColumnDimension('E')->setWidth(17);
        $sheet->getColumnDimension('F')->setWidth(25);
        $sheet->getColumnDimension('G')->setWidth(40);
        $sheet->getColumnDimension('H')->setWidth(40);
        $sheet->getColumnDimension('I')->setWidth(25);
        $sheet->getColumnDimension('J')->setWidth(25);
        $sheet->getColumnDimension('K')->setWidth(25);

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
