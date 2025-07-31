<?php
require('fpdf.php');

// Connect to DB
$conn = new mysqli('localhost', 'root', '', 'electricity_billing_system');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$bill_id = $_GET['bill_id'];
$sql = "SELECT * FROM bill_receipt WHERE bill_id = '$bill_id' LIMIT 1";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $bill = $result->fetch_assoc();

    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);

    $pdf->Cell(0,10,'Electricity Bill',0,1,'C');
    $pdf->Ln(10);
    $pdf->SetFont('Arial','',12);
    $pdf->Cell(0,10,"Bill ID: " . $bill['bill_id'],0,1);
    $pdf->Cell(0,10,"Consumer Name: " . $bill['name'],0,1);
    $pdf->Cell(0,10,"Address: " . $bill['address'],0,1);
    $pdf->Cell(0,10,"Usage (Units): " . $bill['usage_unit'],0,1);
    $pdf->Cell(0,10,"Amount: ₹" . number_format($bill['amount'],2),0,1);
    $pdf->Cell(0,10,"Status: " . $bill['payment_status'],0,1);

    $pdf->Output('D', "Bill_" . $bill['bill_id'] . ".pdf");
} else {
    echo "No bill found!";
}

$conn->close();
?>
