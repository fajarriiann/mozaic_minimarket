<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AcctSupplierBalance;
use App\Models\CoreSupplier;
use Elibyy\TCPDF\Facades\TCPDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class AcctMutationPayableReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        if(!$month = Session::get('month')){
            $month = date('m');
        }else{
            $month = Session::get('month');
        }
        if(!$year = Session::get('year')){
            $year = date('Y');
        }else{
            $year = Session::get('year');
        }
        $monthlist = array(
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maret',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Agustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember'
        );
        $year_now 	=	date('Y');
        for($i=($year_now-2); $i<($year_now+2); $i++){
            $yearlist[$i] = $i;
        } 

        $data_supplier = CoreSupplier::select('supplier_name', 'supplier_id')
        ->where('data_state',0)
        ->where('company_id', Auth::user()->company_id)
        ->get();

        return view('content.AcctMutationPayableReport.ListAcctMutationPayableReport',compact('monthlist','yearlist','month','year','data_supplier'));
    }

    public function filterMutationPayableReport(Request $request)
    {
        $month = $request->month;
        $year = $request->year;

        Session::put('month', $month);
        Session::put('year', $year);

        return redirect('/mutation-payable-report');
    }

    public function resetFilterMutationPayableReport()
    {
        Session::forget('month');
        Session::forget('year');

        return redirect('/mutation-payable-report');
    }

    public function getOpeningBalance($supplier_id)
    {
        if(!$month = Session::get('month')){
            $month = date('m');
        }else{
            $month = Session::get('month');
        }
        if(!$year = Session::get('year')){
            $year = date('Y');
        }else{
            $year = Session::get('year');
        }

        $data = AcctSupplierBalance::select('last_balance')
        ->where('data_state',0)
        ->where('supplier_id', $supplier_id)
        ->where('company_id', Auth::user()->company_id)
        ->whereMonth('supplier_balance_date', $month-1)
        ->whereYear('supplier_balance_date', $year)
        ->orderBy('supplier_balance_id', 'DESC')
        ->first();

        if (!empty($data)) {
            return $data['last_balance'];
        } else {
            return 0;
        }
    }
    
    public function getPayableAmount($supplier_id)
    {
        if(!$month = Session::get('month')){
            $month = date('m');
        }else{
            $month = Session::get('month');
        }
        if(!$year = Session::get('year')){
            $year = date('Y');
        }else{
            $year = Session::get('year');
        }

        $data = AcctSupplierBalance::select('payable_amount')
        ->where('data_state',0)
        ->where('supplier_id', $supplier_id)
        ->where('company_id', Auth::user()->company_id)
        ->whereMonth('supplier_balance_date', $month)
        ->whereYear('supplier_balance_date', $year)
        ->get();

        $payable_amount = 0; 
        foreach ($data as $key => $val) {
            $payable_amount += $val['payable_amount'];
        }

        return $payable_amount;
    }

    public function getPaymentAmount($supplier_id)
    {
        if(!$month = Session::get('month')){
            $month = date('m');
        }else{
            $month = Session::get('month');
        }
        if(!$year = Session::get('year')){
            $year = date('Y');
        }else{
            $year = Session::get('year');
        }

        $data = AcctSupplierBalance::select('payment_amount')
        ->where('data_state',0)
        ->where('supplier_id', $supplier_id)
        ->where('company_id', Auth::user()->company_id)
        ->whereMonth('supplier_balance_date', $month)
        ->whereYear('supplier_balance_date', $year)
        ->get();

        $payment_amount = 0; 
        foreach ($data as $key => $val) {
            $payment_amount += $val['payment_amount'];
        }

        return $payment_amount;
    }

    public function getLastBalance($supplier_id)
    {
        if(!$month = Session::get('month')){
            $month = date('m');
        }else{
            $month = Session::get('month');
        }
        if(!$year = Session::get('year')){
            $year = date('Y');
        }else{
            $year = Session::get('year');
        }

        $data = AcctSupplierBalance::select('last_balance')
        ->where('data_state',0)
        ->where('supplier_id', $supplier_id)
        ->where('company_id', Auth::user()->company_id)
        ->whereMonth('supplier_balance_date', $month)
        ->whereYear('supplier_balance_date', $year)
        ->orderBy('supplier_balance_id', 'DESC')
        ->first();

        return $data['last_balance'];
    }
    
    public function printMutationPayableReport()
    {
        if(!$month = Session::get('month')){
            $month = date('m');
        }else{
            $month = Session::get('month');
        }
        if(!$year = Session::get('year')){
            $year = date('Y');
        }else{
            $year = Session::get('year');
        }

        $data_supplier = CoreSupplier::select('supplier_name', 'supplier_id')
        ->where('data_state',0)
        ->where('company_id', Auth::user()->company_id)
        ->get();

        $pdf = new TCPDF('P', PDF_UNIT, 'F4', true, 'UTF-8', false);

        $pdf::SetPrintHeader(false);
        $pdf::SetPrintFooter(false);

        $pdf::SetMargins(20, 10, 20, 10); // put space of 10 on top

        $pdf::setImageScale(PDF_IMAGE_SCALE_RATIO);

        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf::setLanguageArray($l);
        }

        $pdf::SetFont('helvetica', 'B', 20);

        $pdf::AddPage();

        $pdf::SetFont('helvetica', '', 8);

        $tbl = "
        <table cellspacing=\"0\" cellpadding=\"2\" border=\"0\">
            <tr>
                <td><div style=\"text-align: center; font-size:14px; font-weight: bold\">LAPORAN RETUR PEMBELIAN</div></td>
            </tr>
            <tr>
                <td><div style=\"text-align: center; font-size:12px\">PERIODE : ".date('d M Y', strtotime($start_date))." s.d. ".date('d M Y', strtotime($end_date))."</div></td>
            </tr>
        </table>
        ";
        $pdf::writeHTML($tbl, true, false, false, false, '');
        
        $no = 1;
        $tblStock1 = "
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"1\" width=\"100%\">
            <tr>
                <td width=\"5%\" ><div style=\"text-align: center; font-weight: bold\">No</div></td>
                <td width=\"19%\" ><div style=\"text-align: center; font-weight: bold\">Nama Pemasok</div></td>
                <td width=\"19%\" ><div style=\"text-align: center; font-weight: bold\">Saldo Awal</div></td>
                <td width=\"19%\" ><div style=\"text-align: center; font-weight: bold\">Hutang Baru</div></td>
                <td width=\"19%\" ><div style=\"text-align: center; font-weight: bold\">Pembayaran</div></td>
                <td width=\"19%\" ><div style=\"text-align: center; font-weight: bold\">Saldo Akhir</div></td>
            </tr>
        
             ";

        $no = 1;
        $tblStock2 =" ";
        foreach ($data_supplier as $key => $val) {
            $tblStock2 .="
                <tr>			
                    <td style=\"text-align:center\">$no.</td>
                    <td style=\"text-align:left\">".$val['supplier_name']."</td>
                    <td style=\"text-align:left\">".number_format($this->getOpeningBalance($val['warehouse_id']),2,'.',',')."</td>
                    <td style=\"text-align:left\">".number_format($this->getPayableAmount($val['warehouse_id']),2,'.',',')."</td>
                    <td style=\"text-align:left\">".number_format($this->getPaymentAmount($val['warehouse_id']),2,'.',',')."</td>
                    <td style=\"text-align:right\">".number_format($this->getLastBalance($val['warehouse_id']),2,'.',',')."</td>
                </tr>
                
            ";
            $no++;
        }
        $tblStock3 = " 

        </table>
        <table cellspacing=\"0\" cellpadding=\"2\" border=\"0\">
            <tr>
                <td style=\"text-align:right\">".Auth::user()->name.", ".date('d-m-Y H:i')."</td>
            </tr>
        </table>";

        $pdf::writeHTML($tblStock1.$tblStock2.$tblStock3, true, false, false, false, '');

        $filename = 'Laporan_Pembelian_'.$start_date.'s.d.'.$end_date.'.pdf';
        $pdf::Output($filename, 'I');
    }

    public function exportMutationPayableReport()
    {
        if(!$month = Session::get('month')){
            $month = date('m');
        }else{
            $month = Session::get('month');
        }
        if(!$year = Session::get('year')){
            $year = date('Y');
        }else{
            $year = Session::get('year');
        }

        $data_supplier = CoreSupplier::select('supplier_name', 'supplier_id')
        ->where('data_state',0)
        ->where('company_id', Auth::user()->company_id)
        ->get();

        $spreadsheet = new Spreadsheet();

        if(count($data_supplier)>=0){
            $spreadsheet->getProperties()->setCreator("IBS CJDW")
                                        ->setLastModifiedBy("IBS CJDW")
                                        ->setTitle("Purchase Return Report")
                                        ->setSubject("")
                                        ->setDescription("Purchase Return Report")
                                        ->setKeywords("Purchase, Return, Report")
                                        ->setCategory("Purchase Return Report");
                                 
            $sheet = $spreadsheet->getActiveSheet(0);
            $spreadsheet->getActiveSheet()->getPageSetup()->setFitToWidth(1);
            $spreadsheet->getActiveSheet()->getPageSetup()->setFitToWidth(1);
            $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(5);
            $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(23);
            $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(20);
    
            $spreadsheet->getActiveSheet()->mergeCells("B1:F1");
            $spreadsheet->getActiveSheet()->getStyle('B1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $spreadsheet->getActiveSheet()->getStyle('B1')->getFont()->setBold(true)->setSize(16);
            $spreadsheet->getActiveSheet()->getStyle('B3:F3')->getFont()->setBold(true);

            $spreadsheet->getActiveSheet()->getStyle('B3:F3')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $spreadsheet->getActiveSheet()->getStyle('B3:F3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            $sheet->setCellValue('B1',"Laporan Retur Pembelian Dari Periode ".date('d M Y', strtotime($start_date))." s.d. ".date('d M Y', strtotime($end_date)));	
            $sheet->setCellValue('B3',"No");
            $sheet->setCellValue('C3',"Nama Pemasok");
            $sheet->setCellValue('D3',"Nama Gudang");
            $sheet->setCellValue('E3',"Tanggal Retur Pembelian");
            $sheet->setCellValue('F3',"Jumlah Total");
            
            $j=4;
            $no=0;
            
            foreach($data_supplier as $key=>$val){

                if(is_numeric($key)){
                    
                    $sheet = $spreadsheet->getActiveSheet(0);
                    $spreadsheet->getActiveSheet()->setTitle("Jurnal Umum");
                    $spreadsheet->getActiveSheet()->getStyle('B'.$j.':F'.$j)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                    $spreadsheet->getActiveSheet()->getStyle('H'.$j.':F'.$j)->getNumberFormat()->setFormatCode('0.00');
            
                    $spreadsheet->getActiveSheet()->getStyle('B'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    $spreadsheet->getActiveSheet()->getStyle('C'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                    $spreadsheet->getActiveSheet()->getStyle('D'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                    $spreadsheet->getActiveSheet()->getStyle('E'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                    $spreadsheet->getActiveSheet()->getStyle('F'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);




                        $no++;
                        $sheet->setCellValue('B'.$j, $no);
                        $sheet->setCellValue('C'.$j, $this->getSupplierName($val['supplier_id']));
                        $sheet->setCellValue('D'.$j, $this->getWarehouseName($val['warehouse_id']));
                        $sheet->setCellValue('E'.$j, date('d-m-Y', strtotime($val['purchase_return_date'])));
                        $sheet->setCellValue('F'.$j, number_format($val['purchase_return_subtotal'],2,'.',','));
                }
                $j++;
        
            }
            $spreadsheet->getActiveSheet()->mergeCells('B'.$j.':F'.$j);
            $spreadsheet->getActiveSheet()->getStyle('B'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $sheet->setCellValue('B'.$j, Auth::user()->name.", ".date('d-m-Y H:i'));
            
            $filename='Laporan_Retur_Pembelian_'.$start_date.'_s.d._'.$end_date.'.xls';
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="'.$filename.'"');
            header('Cache-Control: max-age=0');

            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xls');
            $writer->save('php://output');
        }else{
            echo "Maaf data yang di eksport tidak ada !";
        }
    }
}
