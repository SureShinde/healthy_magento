<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);
class Table{
	private $rows;
	function row(){
		$row = new Row;
		$this->rows[] = $row;
		return $row;
	}
	function render(){
		foreach($this->rows as $row){
			var_dump($row->getData());
		}
	}
}
class Row{
	private $data;
	function addData($string){
		$this->data[] = $string;
	}
	function getData(){
		return $this->data;
	}
}

$table = new Table;

$row = $table->row();
$row->addData('Row One Data');

$row2 = $table->row();
$row2->addData('Row Two Data');

$table->render();
?>