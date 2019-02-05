<?php

require_once 'vendor/autoload.php';

$inputFileName = './../primepages/sub_categories.xlsx';
$outputFileName = './../primepages/sub_categories.sql';
$mapper = Closure::fromCallable('makeUpdateSqlForHeads');

generateSql($inputFileName, $outputFileName, $mapper);

function generateSql($inputFileName, $outputFileName, Closure $mapper) {

	$outputFileHandle = fopen($outputFileName, 'w+');

	readExcel($inputFileName, function ($row) use ($outputFileHandle, $mapper) {
		$sql = $mapper($row);
		fwrite($outputFileHandle, $sql);
	});

	fclose($outputFileHandle);
}

function readExcel($filename, $consumer) {

	$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
	$reader->setReadDataOnly(true);

	$spreadsheet = $reader->load($filename);

	$worksheet = $spreadsheet->getActiveSheet();

	$rowIterator = $worksheet->getRowIterator();

	$skipHeader = true;

	foreach ($rowIterator as $row) {

		if ($skipHeader) {
			$skipHeader = false;
			continue;
		}

		$cellIterator = $row->getCellIterator();

		$rowArray = [];

		foreach ($cellIterator as $cell) {
			$rowArray[] = $cell->getValue();
		}

		$consumer($rowArray);
	}
}

function makeUpdateSqlForHeads($row) {
	$table = 'sub_categories';
	$id = $row[0];
	$slug = validSlug($row[5]);
	return makeUpdateSql($table, 'sub_cat_slug ', $slug, 'sub_cat_id', $id);
}

function makeUpdateSqlForSubHeads($row) {
	$table = 'sub_sub_catgories';
	$id = $row[0];
	$slug = validSlug($row[5]);
	return makeUpdateSql($table, 'sub_sub_cat_slug', $slug, 'sub_sub_cat_id', $id);
}

function makeUpdateSql($table, $colunm, $value, $whereColunm, $whereValue) {
	return "UPDATE $table SET $colunm = '$value' WHERE $whereColunm = $whereValue;\n";
}

function validSlug($slug) {
	return str_replace('/', '', $slug);
}
