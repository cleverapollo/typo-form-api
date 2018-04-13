<?php

namespace App\Exports;

use App\Models\Submission;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Contracts\Support\Responsable;

class CSVExport implements FromCollection, Responsable
{
	use Exportable;

	/**
	 * It's required to define the fileName within
	 * the export class when making use of Responsable.
	 */
	private $fileName = 'submissions.csv';

	public function collection()
	{
		return Submission::all();
	}
}
