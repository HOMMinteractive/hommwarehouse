<?php

namespace homm\hommwarehouse\exporters;

use Craft;
use craft\base\EagerLoadingFieldInterface;
use craft\base\ElementExporter;
use craft\elements\db\ElementQuery;
use craft\elements\db\ElementQueryInterface;
use craft\elements\ElementCollection;
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

        $sheet->fromArray(['Inventar - ' . $globalAddress->globalAddressContactCompany . ' - ' . date('d.m.Y')], null, 'A' . $sheetRow++);
        $sheet->fromArray([], null, 'A' . $sheetRow++);

        $cols = [
            'Weinname',
            'Kategorie',
            'Land',
            'Region',
            'Jahrgang',
            'Produzent',
            'Lagerbestand',
            'Ausgetrunken',
            'Rebsorte',
            'Flaschengroesse',
        ];
        $sheet->fromArray($cols, null, 'A' . $sheetRow++);


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
                'Weinname' => $elementArr['title'],
                'Kategorie' => $elementArr['weinKategorie'],
                'Lnad' => $elementArr['Land'],
                'Region' => $elementArr['weinRegion'],
                'Jahrgang' => $elementArr['weinJahrgange'],
                'Produzent' => $elementArr['weinProduzent'],
                'Lagerbestand' => $elementArr['weinLagerbestand']['stock'] ?? '0',
                'Ausgetrunken' => $elementArr['weinAusgetrunken'] ? Craft::t('hommwarehouse', 'Yes') : Craft::t('hommwarehouse', 'No'),
                'Rebsorte' => $elementArr['weinRebsorten'],
                'Flaschengroesse' => $elementArr['weinFlaschengrossen'],
            ], null, 'A' . $sheetRow++);
        }

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
