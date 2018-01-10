<?php
require_once "../../config.php";
require_once "../util/PHPExcel.php";
require_once "../dao/QW_DAO.php";
require_once "../util/Utils.php";

use \Tsugi\Core\LTIX;
use \QW\DAO\QW_DAO;

// Retrieve the launch data if present
$LAUNCH = LTIX::requireData();

$p = $CFG->dbprefix;

$QW_DAO = new QW_DAO($PDOX, $p);

if ( $USER->instructor ) {

    $SetID = $_SESSION["SetID"];
   
    $questions = $QW_DAO->getQuestions($SetID);

 $rowCounter = 1;
    $Total = count($questions);

    $exportFile = new PHPExcel();

    
    $exportFile->setActiveSheetIndex(0)->setCellValue('A1', 'Student');
	$exportFile->setActiveSheetIndex(0)->setCellValue('B1', 'Username');
	$exportFile->setActiveSheetIndex(0)->setCellValue('C1', 'Date of Submission');
	
		
	$exportFile->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
	$exportFile->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);
	$exportFile->getActiveSheet()->getStyle('C1')->getFont()->setBold(true);
	
	$exportFile->getActiveSheet()->getColumnDimension('A')->setWidth(20);
	$exportFile->getActiveSheet()->getColumnDimension('B')->setWidth(10);
	$exportFile->getActiveSheet()->getColumnDimension('C')->setWidth(25);
	
	
	
	$letters = range('C','Z');
		for($x = 1; $x<=$Total; $x++){	
			$col1 = $x+2;
			$exportFile->getActiveSheet()->setCellValueByColumnAndRow($col1, $rowCounter, "Question ".$x);
			
			$cell_name = $letters[$x]."1";    
   			$exportFile->getActiveSheet()->getStyle($cell_name)->getFont()->setBold(true);
			
			
		}
	
	
$StudentList = $QW_DAO->Report($SetID);	
   

        $columnIterator = $exportFile->getActiveSheet()->getColumnIterator();
        $columnIterator->next();


     
  foreach ( $StudentList as $row ) {
 		   	$rowCounter++;	  
	  		$UserID = 	$row["UserID"];
	  
	  		$Email = $QW_DAO->findEmail($UserID);
	  		$UserName = explode("@",$Email);
	  
	  		$Modified1 = $QW_DAO->findDate($UserID, $SetID);
	        $Modified  =  new DateTime($Modified1);
		   	$exportFile->getActiveSheet()->setCellValue('A'.$rowCounter, $row["LastName"].', '.$row["FirstName"]);
	  
	  		$exportFile->getActiveSheet()->setCellValue('B'.$rowCounter, $UserName[0]);		
	  		$exportFile->getActiveSheet()->setCellValue('C'.$rowCounter, $Modified->format('m/d/y - H:i A '));	
	  
			$questions = $QW_DAO->getQuestions($SetID);	
	  		$QTotal = count($questions);
		    $col = 3; 
		   foreach ( $questions as $row1 ) {			   			
						$QID = $row1["QID"];
			   			$A="";	
						
						$Data = $QW_DAO->Review($QID, $UserID);	
						foreach ( $Data as $row2 ) {
								$A= $row2["Answer"];
								//$Date1 = $row2["Modified"];
						}

			$exportFile->getActiveSheet()->setCellValueByColumnAndRow($col, $rowCounter, $A);
        	$col++;
			
    }          
	
}
	 $columnIterator->next();
	
	$exportFile->getActiveSheet()->setTitle('Quick Write');

	
	
	
foreach($exportFile->getActiveSheet()->getColumnDimension() as $col) {
    $col->setAutoSize(true);
}
$exportFile->getActiveSheet()->calculateColumnWidths();
	
	
	
	
	
	
	
        // Redirect output to a client’s web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename=Quick Write.xls');
        header('Cache-Control: max-age=0');
        // If you're serving to IE over SSL, then the following may be needed
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0
        $objWriter = PHPExcel_IOFactory::createWriter($exportFile, 'Excel5');
        $objWriter->save('php://output');
}

