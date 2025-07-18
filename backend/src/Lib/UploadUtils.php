<?php

namespace App\Lib;

class UploadUtils
{
    public static function isExcelFile($file)
    {
        $typeFormat = explode("/", $file->getClientMediaType());
        $type = $typeFormat[0];
        $format = $typeFormat[1];
        $acceptedFormats = [
            'vnd.ms-excel',
            'vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'octet-stream',
        ];
        if ($type != 'application' || (!in_array($format, $acceptedFormats))) {
            return false;
        }
        return true;
    }

    public static function getNumRowsInSpreadsheet($worksheetArrayOfRows)
    {
        $numRows = 0;
        foreach ($worksheetArrayOfRows as $row) {
            if (empty(array_filter($row))) {
                return $numRows;
            }
            $numRows++;
        }
    }

    public static function getValidRowsFromSheet($worksheetArrayOfRows)
    {
        // Filter out empty rows from the sheet
        return array_filter($worksheetArrayOfRows, function($row) {
            // Check if the row has any non-empty cell
            return count(array_filter($row, function($cell) {
                return !empty($cell);
            })) > 0;
        });
    }
}