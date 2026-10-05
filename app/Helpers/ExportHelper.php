<?php

namespace App\Helpers;

use ZipArchive;

class ExportHelper
{
    public static function download($filename, $title, $headings, $rows) {
        $file = tempnam(sys_get_temp_dir(), 'slw_xlsx_');
        $zip = new ZipArchive;

        if ($zip->open($file, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Excel file create failed.');
        }

        /* Content Types */
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8"?>
        <Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
        <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
        <Default Extension="xml" ContentType="application/xml"/>
        <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
        <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
        <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
        </Types>');

        /* Root Relationship */
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8"?>
        <Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
        <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
        </Relationships>');

        /* Workbook */
        $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8"?>
        <workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"
        xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
        <sheets>
        <sheet name="Agent Report" sheetId="1" r:id="rId1"/>
        </sheets>
        </workbook>');

        /* Workbook Relations */
        $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8"?>
        <Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
        <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
        <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
        </Relationships>');

        /* Styles */
        $zip->addFromString('xl/styles.xml', '<?xml version="1.0" encoding="UTF-8"?>
        <styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">

        <fonts count="4">

        <font>
        <sz val="11"/>
        <name val="Calibri"/>
        </font>

        <!-- Title -->
        <font>
        <b/>
        <sz val="18"/>
        <color rgb="FF000000"/>
        <name val="Calibri"/>
        </font>

        <!-- Header -->
        <font>
        <b/>
        <sz val="11"/>
        <color rgb="FFFFFFFF"/>
        <name val="Calibri"/>
        </font>

        <!-- Generated -->
        <font>
        <b/>
        <sz val="11"/>
        <color rgb="FF000000"/>
        <name val="Calibri"/>
        </font>

        </fonts>

        <fills count="3">

        <fill>
        <patternFill patternType="none"/>
        </fill>

        <fill>
        <patternFill patternType="solid">
        <fgColor rgb="FFFFFFFF"/>
        </patternFill>
        </fill>

        <fill>
        <patternFill patternType="solid">
        <fgColor rgb="FFFF6347"/>
        </patternFill>
        </fill>

        </fills>

        <borders count="2">

        <border>
        <left/>
        <right/>
        <top/>
        <bottom/>
        <diagonal/>
        </border>

        <border>
        <left style="thin"><color rgb="FFD1D5DB"/></left>
        <right style="thin"><color rgb="FFD1D5DB"/></right>
        <top style="thin"><color rgb="FFD1D5DB"/></top>
        <bottom style="thin"><color rgb="FFD1D5DB"/></bottom>
        <diagonal/>
        </border>

        </borders>

        <cellXfs count="4">

        <!-- Normal -->
        <xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>

        <!-- Title -->
        <xf numFmtId="0" fontId="1" fillId="1" borderId="1" applyAlignment="1">
        <alignment horizontal="center" vertical="center"/>
        </xf>

        <!-- Header -->
        <xf numFmtId="0" fontId="2" fillId="2" borderId="1" applyAlignment="1">
        <alignment horizontal="center" vertical="center" wrapText="1"/>
        </xf>

        <!-- Generated -->
        <xf numFmtId="0" fontId="3" fillId="0" borderId="1" applyAlignment="1">
        <alignment horizontal="center" vertical="center"/>
        </xf>

        </cellXfs>

        </styleSheet>');

        $count = count($headings);
        $lastCol = self::columnName($count);

        /* Worksheet */
        $sheet = '<?xml version="1.0" encoding="UTF-8"?>
        <worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">

        <sheetViews>
        <sheetView workbookViewId="0">
        <pane ySplit="4" topLeftCell="A5" activePane="bottomLeft" state="frozen"/>
        </sheetView>
        </sheetViews>

        <sheetFormatPr defaultRowHeight="18"/>

        <cols>';

        /* Column Width */
        for ($i = 1; $i <= $count; $i++) {

            $width = 22;

            if ($i == 1) $width = 28;
            if ($i == 2) $width = 16;
            if ($i == 3) $width = 25;
            if ($i == 4) $width = 32;
            if ($i == 5) $width = 18;
            if ($i == 6) $width = 20;
            if ($i == 7) $width = 18;
            if ($i == 8) $width = 15;
            if ($i == 9) $width = 20;

            $sheet .= '<col min="' . $i . '" max="' . $i . '" width="' . $width . '" customWidth="1"/>';
        }

        $sheet .= '</cols><sheetData>';

        /* Title */
        $sheet .= '<row r="1" ht="30">'
            . self::cell('A1', $title, 1)
            . '</row>';

        /* Generated On - India Time */
        $generated = 'Generated On: ' .
            now()->timezone('Asia/Kolkata')->format('d M Y, h:i A');

        $sheet .= '<row r="2" ht="22">'
            . self::cell('A2', $generated, 3)
            . '</row>';

        $sheet .= '<row r="3"></row>';

        /* Header */
        $sheet .= '<row r="4" ht="25">';

        foreach ($headings as $i => $heading) {
            $sheet .= self::cell(
                self::columnName($i + 1) . '4',
                $heading,
                2
            );
        }

        $sheet .= '</row>';

        /* Agent Data - Normal Font */
        $rowNo = 5;

        foreach ($rows as $row) {

            $sheet .= '<row r="' . $rowNo . '">';

            foreach ($row as $i => $value) {
                $sheet .= self::cell(
                    self::columnName($i + 1) . $rowNo,
                    $value,
                    0
                );
            }

            $sheet .= '</row>';
            $rowNo++;
        }

        $lastRow = max(4, $rowNo - 1);

        $sheet .= '</sheetData>';

        /* Excel Filter */
        $sheet .= '<autoFilter ref="A4:' . $lastCol . $lastRow . '"/>';

        /* Merge */
        $sheet .= '<mergeCells count="2">
        <mergeCell ref="A1:' . $lastCol . '1"/>
        <mergeCell ref="A2:' . $lastCol . '2"/>
        </mergeCells>';

        $sheet .= '</worksheet>';

        $zip->addFromString(
            'xl/worksheets/sheet1.xml',
            $sheet
        );

        $zip->close();

        return response()->download(
            $file,
            $filename,
            [
                'Content-Type' =>
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',

                'Content-Disposition' =>
                    'attachment; filename="' . $filename . '"',
            ]
        )->deleteFileAfterSend(true);
    }

    private static function cell($ref, $value, $style = 0)
    {
        $value = (string) $value;

        $clean = @iconv('UTF-8', 'UTF-8//IGNORE', $value);

        if ($clean !== false) {
            $value = $clean;
        }

        $value = preg_replace(
            '/[^\x09\x0A\x0D\x20-\x{D7FF}\x{E000}-\x{FFFD}]/u',
            '',
            $value
        ) ?? '';

        $value = htmlspecialchars(
            $value,
            ENT_XML1 | ENT_QUOTES,
            'UTF-8'
        );

        return '<c r="' . $ref . '" s="' . $style . '" t="inlineStr">
        <is><t xml:space="preserve">' . $value . '</t></is>
        </c>';
    }

    private static function columnName($number)
    {
        $name = '';

        while ($number > 0) {
            $number--;
            $name = chr(65 + ($number % 26)) . $name;
            $number = intdiv($number, 26);
        }

        return $name;
    }
}