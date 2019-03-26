<?php

namespace App\Services;

use Rap2hpoutre\FastExcel\FastExcel;
use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Common\Type;

class ExcelService extends Service {

    public function __construct() {}

    public function toArray($file, $chunk_size = false) {
        $reader = ReaderFactory::create(Type::XLSX);
        $reader->open($file);
        $data = [];
        foreach($reader->getSheetIterator() as $key=>$sheet) {
            $rows = (new FastExcel)->sheet($key)->import($file)->toArray();
            $chunks = $chunk_size ? array_chunk($rows, $chunk_size) : $rows;

            foreach($chunks as $chunk) {
                $data[] = (object)['name' => $sheet->getName(), 'rows' => $chunk];
            }
        }
        $reader->close();

        return $data;
    }
}