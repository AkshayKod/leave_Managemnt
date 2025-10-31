admin_export_emplloyees_pdf.php

<?php
$lifetime=86400; session_set_cookie_params($lifetime);
session_start();
require_once '../config/connection.php';
// NOTE: For FPDF to work, you must ensure 'fpdf.php' is available in this directory.
// We are including a minimal FPDF implementation here for self-containment.
include('fpdf.php'); 

if (!isset($_SESSION['user_id']) || $_SESSION['user_role']!=='admin') { 
    die("Access denied."); 
}

date_default_timezone_set('Asia/Kolkata');

// 1. Fetch Data
$sql = "SELECT id, name, email, gender, address, contact, created_at FROM users WHERE role='employee' ORDER BY name ASC";
$res = mysqli_query($conn, $sql);

if (!$res) {
    die("Database query failed: " . mysqli_error($conn));
}

$data = [];
while ($row = mysqli_fetch_assoc($res)) {
    $data[] = $row;
}

// 2. Define PDF Class (Simplified with basic table logic)
class PDF extends FPDF
{
    // Page header
    function Header()
    {
        // Title
        $this->SetFont('Arial','B',15);
        $this->Cell(0,10,'Employee Details Report',0,1,'C');
        $this->SetFont('Arial','',10);
        $this->Cell(0,5,'Generated on: ' . date('Y-m-d H:i:s'),0,1,'C');
        $this->Ln(5);
    }

    // Page footer
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
    }

    // Table renderer
    function EmployeeTable($header, $data)
    {
        $this->SetFillColor(46, 134, 171); // Dark blue header color
        $this->SetTextColor(255);
        $this->SetDrawColor(128,0,0);
        $this->SetLineWidth(.3);
        $this->SetFont('Arial','B',8);
        
        // Column widths
        $w = array(10, 30, 45, 15, 45, 25, 20); // Total width 190 (A4 landscape is 277, portrait is 190 approx)

        // Header
        for($i=0;$i<count($header);$i++)
            $this->Cell($w[$i],7,$header[$i],1,0,'C',true);
        $this->Ln();

        // Data
        $this->SetFillColor(224, 235, 255);
        $this->SetTextColor(0);
        $this->SetFont('Arial','',8);
        $fill = false;
        
        foreach($data as $row)
        {
            $this->Cell($w[0],6,$row['id'],'LR',0,'C',$fill);
            $this->Cell($w[1],6,$row['name'],'LR',0,'L',$fill);
            $this->Cell($w[2],6,$row['email'],'LR',0,'L',$fill);
            $this->Cell($w[3],6,$row['gender'],'LR',0,'C',$fill);
            // MultiCell for Address
            $x = $this->GetX();
            $y = $this->GetY();
            $this->MultiCell($w[4],6,$row['address'],'LR','L',$fill);
            $h = $this->GetY() - $y; // Height of MultiCell
            $this->SetXY($x + $w[4], $y); 
            $this->Cell($w[5],6,$row['contact'],'LR',0,'L',$fill);
            $this->Cell($w[6],6,substr($row['created_at'], 0, 10),'LR',0,'C',$fill);
            $this->SetY($y + $h); // Move back to the next row starting point
            $this->Cell(array_sum($w), 0, '', 'T'); // Draw bottom border for the row
            $fill = !$fill;
        }
    }
}

// 3. Instantiate and Render
$pdf = new PDF('P','mm','A4');
$pdf->AliasNbPages();
$pdf->AddPage();

// Set table headers
$header = array('ID', 'Name', 'Email', 'Gender', 'Address', 'Contact', 'Created At');

$pdf->EmployeeTable($header, $data);

// 4. Output
$pdf->Output('I', 'Employee_Details_'.date('Ymd').'.pdf');
exit;

// FPDF Library Content (Included here for self-containment, ensure this is a full FPDF implementation on your server)

// The content of fpdf.php would normally be included here.
// For this environment, we assume the host environment (Canvas) provides FPDF 
// if the user has installed it, but in a real-world scenario, you MUST download
// the full fpdf.php and include it correctly. Since I cannot provide the full
// library file here, I'll add a placeholder comment. You must manually place 
// the official fpdf.php file in your admin folder. 
//
// If the FPDF class is not found, you need to check your include path or ensure fpdf.php is present.
// The include('fpdf.php') line must point to your FPDF installation.
//

?>