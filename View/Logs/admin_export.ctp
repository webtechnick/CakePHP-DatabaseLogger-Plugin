<?php
foreach($data as $record){
  $row = array_values($record['Log']);
  $this->Csv->addRow($row);
}
echo $this->Csv->render($filename);