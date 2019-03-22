<?php

namespace App\Services;

use Rap2hpoutre\FastExcel\FastExcel;
use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Common\Type;

class ExcelService extends Service {

    public function __construct() {}

    public function toArray($file) {
        $reader = ReaderFactory::create(Type::XLSX);
        $reader->open($file);
        $data = [];
        foreach($reader->getSheetIterator() as $key=>$sheet) {
            $data[] = (object)['name' => $sheet->getName(), 'rows' => (new FastExcel)->sheet($key)->import($file)->toArray()];
        }
        $reader->close();

        return $data;
    }
}