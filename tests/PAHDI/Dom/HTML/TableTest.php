<?php
class HTMLTableTest extends PAHDITest
{
	static $doc;
	
	static function setUpBeforeClass ()
	{
		$html = "
		<table id='a'>
			<caption>test-test</caption>
			<thead id='head'>
				<tr>
					<td>1</td>
					<td>2</td>
					<td>3</td>
				</tr>
				<tr>
					<td>1</td>
					<td>2</td>
					<td>3</td>
				</tr>
			</thead>
			<tbody id='b'>
				<tr id='c'>
					<td>1</td>
					<td id='d'>2</td>
					<td>3</td>
				</tr>
				<tr id='e'>
					<td>1</td>
					<td>2</td>
					<td><table><tbody><tr><td>12312312</td></tr></tbody></table></td>
				</tr>
			</tbody>
			<tbody>
				<tr>
					<td>1</td>
					<td>2</td>
					<td>3</td>
				</tr>
				<tr>
					<td>1</td>
					<td>2</td>
					<td>3</td>
				</tr>
			</tbody>
			<tfoot id='foot'>
				<tr>
					<td>1</td>
					<td>2</td>
					<td>3</td>
				</tr>
				<tr>
					<td>1</td>
					<td>2</td>
					<td>3</td>
				</tr>
			</tfoot>
		</table>";
		self::$doc = PAHDITest::parseHTML($html);
	}
	
	function testTablePointers ()
	{
		$table = self::$doc->getElementById("a");
		$this->assertEquals($table->tBodies->length, 2);
		$this->assertEquals($table->tHead->id, "head");
		$this->assertEquals($table->tFoot->id, "foot");
		$this->assertEquals($table->caption->textContent, "test-test");
	}
	
	function testCellIndex ()
	{
		$cell = self::$doc->getElementById("d");
		$this->assertEquals($cell->cellIndex, 1);
	}
	
	function testRowPointers ()
	{
		$row = self::$doc->getElementById("e");
		$this->assertEquals($row->cells->length, 3);
	}
	
	function testTableAndSectionRowsPointer ()
	{
		$table = self::$doc->getElementById("a");
		$this->assertEquals($table->rows->length, 8);
		$body = self::$doc->getElementById("b");
		$this->assertEquals($body->rows->length, 2);
	}
	
	function testRowIndexes ()
	{
		$row = self::$doc->getElementById("e");
		$this->assertEquals($row->sectionRowIndex, 1);
		$this->assertEquals($row->rowIndex, 3);
	}
	
	function testSectionInsertDeleteRow ()
	{
		$foot = self::$doc->getElementById("foot");
		$newrow = $foot->insertRow(1);
		$this->assertTrue($foot->rows[1]->isSameNode($newrow));
		$length = $foot->childNodes->length;
		$foot->deleteRow(1);
		$this->assertEquals($foot->childNodes->length, $length - 1);
	}
	
	function testTableInsertDeleteRow ()
	{
		$table = self::$doc->getElementById("a");
		$newrow = $table->insertRow(1);
		$rows = $table->rows;
		$this->assertTrue($rows[1]->isSameNode($newrow));
		$length = $rows->length;
		$table->deleteRow(1);
		$this->assertEquals($table->rows->length, $length - 1);
	}
	
	function testInsertDeleteCell ()
	{
		$row = self::$doc->getElementById("e");
		$newcell = $row->insertCell(1);
		$this->assertTrue($row->cells[1]->isSameNode($newcell));
		$length = $row->childNodes->length;
		$row->deleteCell(1);
		$this->assertEquals($row->childNodes->length, $length - 1);
	}
	
	function testCreateDeleteCaption ()
	{
		$table = self::$doc->getElementById("a");
		$this->assertEquals($table->createCaption()->textContent, "test-test");
		$table->deleteCaption();
		$this->assertEquals($table->caption, null);
	}
	
	function testCreateDeleteThead ()
	{
		$table = self::$doc->getElementById("a");
		$this->assertEquals($table->createTHead()->id, "head");
		$table->deleteTHead();
		$this->assertEquals($table->tHead, null);
	}
	
	function testCreateDeleteTfoot ()
	{
		$table = self::$doc->getElementById("a");
		$this->assertEquals($table->createTFoot()->id, "foot");
		$table->deleteTFoot();
		$this->assertEquals($table->tFoot, null);
	}
}