<?php

namespace App\Http\Controllers;

use Exception;
use Maatwebsite\Excel\Excel;
use Illuminate\Http\Request;

class FileController extends Controller
{
	protected $excel;

	/**
	 * Create a new controller instance.
	 *
	 * @param Excel $excel
	 */
	public function __construct(Excel $excel)
	{
		$this->excel = $excel;
	}

	public function export()
	{
		return $this->excel->export(new Export);
	}
}