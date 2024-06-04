<?php



namespace App\Http\Controllers;



use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;

use App\Models\PurchaseOrder;

use App\Models\Ledger;
use App\Models\Invoice;
use App\Models\WhatsAppInvoice;
use App\Models\PackingslipNew1;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\UserCity;
use Illuminate\Support\Str;

use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;



class CronController extends Controller

{

    //



    public function test()

    {

        echo 'Hello';

    }



    public function generateTask()

    {

        // echo "Hello! It's cron job";



        /*

            Task generate CRON:-  0 0 * * * curl -s "{base_url}/cron/generateTask"

        */



        DB::table('test_cron')->insert(['unique_id'=>uniqid().' created at '.date('Y-m-d') , 'description' => 'generateTask'  ]);

        

        $start_date = date("Y-m-d", strtotime("last sunday"));

        $end_date = date("Y-m-d", strtotime("next saturday"));



        $staff = DB::table('users')->select('id','name','monthly_salary','daily_salary')->where('type', 2)->where('status', 1)->get();

        if(!empty($staff)){

            foreach($staff as $user){

                /* Salary Generation */

                

                $checkExistSalaryDayLedger = DB::table('ledger')->where('staff_id',$user->id)->where('purpose','salary')->where('entry_date', date('Y-m-d'))->first();

                if(empty($checkExistSalaryDayLedger)){

                    $transaction_id = "SAL".$user->id."".date('Ymd').time();

                    $user->salary_id = $transaction_id;

                    Ledger::insert([

                        'user_type' => 'staff',

                        'staff_id' => $user->id,

                        'transaction_id' => $transaction_id,

                        'transaction_amount' => $user->daily_salary,

                        'is_credit' => 1,

                        'entry_date' => date('Y-m-d'),

                        'purpose' => 'salary',

                        'purpose_description' => "Staff Daily Salary"

                    ]);

                }              

                /* Task Generation */

                /*$checkTask = DB::table('tasks')->where('user_id',$user->id)->orderBy('id','desc')->first();

                

                if(!empty($checkTask)){

                    $user->existTask = 1;

                    $user->checkTask = $checkTask;



                    if($checkTask->start_date != $start_date && $checkTask->end_date != $end_date){

                        $id = DB::table('tasks')->insertGetId([

                            'user_id' => $user->id,

                            'start_date' => $start_date,

                            'end_date' => $end_date,

                            'created_at' => date('Y-m-d H:i:s'),

                            'updated_at' => date('Y-m-d H:i:s')

                        ]);

                        $taskDetails = DB::table('task_details')->where('task_id',$checkTask->id)->get();

                        if(!empty($taskDetails)){

                            foreach($taskDetails as $td){

                                DB::table('task_details')->insert([

                                    'task_id'=>$id,

                                    'store_id'=>$td->store_id,

                                    'no_of_visit'=>$td->no_of_visit,

                                    'created_at' => date('Y-m-d H:i:s'),

                                    'updated_at' => date('Y-m-d H:i:s')

                                ]);

                            }

                        }

                    }



                }else{

                    $user->existTask = 0;

                    $user->checkTask = (object) [];

                }*/

            }

        }

        

    }



    public function generate_commission()

    {

        // $salesmans = DB::table('users')->select('id','name','designation','targeted_collection_amount_commission')->where('id', 13)->get()->toArray();

        $salesmans = DB::table('users')->select('id','name','designation','targeted_collection_amount_commission')->where('designation', 1)->where('status', 1)->get()->toArray();



        foreach($salesmans as $user){

            echo 'User Id:- '.$user->id;

            echo '<br/>';

            $user_cities = UserCity::where('user_id',$user->id)->pluck('city_id')->toArray();

            

            // dd($user_cities);



            $staff_collection_commission_eligibility = DB::table('staff_collection_commission_eligibility')->selectRaw("SUM(invoice_paid_amount) AS covered_amount, month_val,year_val,GROUP_CONCAT(city_id) AS cities")->whereIn('city_id',$user_cities)->groupBy('month_val')->groupBy('year_val')->get();



            $percent = $user->targeted_collection_amount_commission;

            if(!empty($staff_collection_commission_eligibility)){

                foreach($staff_collection_commission_eligibility as $comm){

                    $covered_amount = $comm->covered_amount;

                    $commission_val =  getPercentageVal($percent,$covered_amount);

                    $comm->commission_val = $commission_val;



                    $checkExistStaffComm = DB::table('collection_staff_commissions')->where('user_id',$user->id)->where('month_val',$comm->month_val)->where('year_val',$comm->year_val)->first();



                    if(!empty($checkExistStaffComm)){

                        ## Update table

                        

                        DB::table('collection_staff_commissions')->where('id',$checkExistStaffComm->id)->update([

                            'targeted_collection_amount_commission' => $user->targeted_collection_amount_commission,

                            'commission_on_amount' => $comm->covered_amount,

                            'final_commission_amount' => $commission_val,

                            'collection_cities' => $comm->cities,

                        ]);



                        $checkExistLedger = Ledger::where('collection_staff_commission_id', $checkExistStaffComm->id)->first();



                        if(!empty($checkExistLedger)){

                            Ledger::where('id',$checkExistLedger->id)->update([

                                'user_type' => 'staff',

                                'staff_id' => $user->id,

                                'entry_date' => date('Y-m-01', strtotime($comm->year_val.'-'.$comm->month_val)),

                                'collection_staff_commission_id' => $checkExistStaffComm->id,

                                'transaction_id' => $checkExistStaffComm->unique_id,

                                'transaction_amount' => $commission_val,

                                'is_credit' => 1,

                                'updated_at' => date('Y-m-01 00:00:00', strtotime($comm->year_val.'-'.$comm->month_val))

                            ]);

                        } else {



                            $ledgerArr = array(

                                'user_type' => 'staff',

                                'staff_id' => $user->id,

                                'collection_staff_commission_id' => $checkExistStaffComm->id,

                                'transaction_id' => $checkExistStaffComm->unique_id,

                                'transaction_amount' => $commission_val,

                                'is_credit' => 1,

                                'entry_date' => date('Y-m-01', strtotime($comm->year_val.'-'.$comm->month_val)),

                                'purpose' => 'payment_collection_commission',

                                'purpose_description' => 'Monthly Payment Collection Commission',

                                'created_at' => date('Y-m-01 00:00:00', strtotime($comm->year_val.'-'.$comm->month_val)),

                                'updated_at' => date('Y-m-01 00:00:00', strtotime($comm->year_val.'-'.$comm->month_val))

                            );

                

                            Ledger::insert($ledgerArr);



                        }

                    } else {

                        ## Insert table

                        $unique_id = "COMM".$comm->year_val."".$comm->month_val."".str_pad($user->id,4,"0",STR_PAD_LEFT);

                        $collection_staff_commission_id = DB::table('collection_staff_commissions')->insertGetId([

                            'user_id' => $user->id,

                            'unique_id' => $unique_id,

                            'year_val' => $comm->year_val,

                            'month_val' => $comm->month_val,

                            'commission_on_amount' => $comm->covered_amount,

                            'targeted_collection_amount_commission' => $user->targeted_collection_amount_commission,

                            'final_commission_amount' => $commission_val,

                            'collection_cities' => $comm->cities

                        ]);



                        $checkExistLedger = Ledger::where('collection_staff_commission_id', $collection_staff_commission_id)->first();





                        if(!empty($checkExistLedger)){

                            Ledger::where('id',$checkExistLedger->id)->update([

                                'user_type' => 'staff',

                                'staff_id' => $user->id,

                                'entry_date' => date('Y-m-01', strtotime($comm->year_val.'-'.$comm->month_val)),

                                'collection_staff_commission_id' => $collection_staff_commission_id,

                                'transaction_id' => $unique_id,

                                'transaction_amount' => $commission_val,

                                'is_credit' => 1,

                                'updated_at' => date('Y-m-01 00:00:00', strtotime($comm->year_val.'-'.$comm->month_val))

                            ]);

                        } else {



                            $ledgerArr = array(

                                'user_type' => 'staff',

                                'staff_id' => $user->id,

                                'collection_staff_commission_id' => $collection_staff_commission_id,

                                'transaction_id' => $unique_id,

                                'transaction_amount' => $commission_val,

                                'is_credit' => 1,

                                'entry_date' => date('Y-m-01', strtotime($comm->year_val.'-'.$comm->month_val)),

                                'purpose' => 'payment_collection_commission',

                                'purpose_description' => 'Monthly Payment Collection Commission',

                                'created_at' => date('Y-m-01 00:00:00', strtotime($comm->year_val.'-'.$comm->month_val)),

                                'updated_at' => date('Y-m-01 00:00:00', strtotime($comm->year_val.'-'.$comm->month_val))

                            );

                

                            Ledger::insert($ledgerArr);



                        }

                    }

                }

            }

            



            // dd($staff_collection_commission_eligibility);



            $user->staff_collection_commission_eligibility = $staff_collection_commission_eligibility;

        }

        echo '<pre>'; print_r($salesmans);

    }

    public function daily_ledger_send(){
        $now = Carbon::now();
        $startTime = $now->copy()->subMinutes(75)->toDateTimeString();
        $endTime = $now->copy()->subMinutes(60)->toDateTimeString();
        $data = DB::table('ledger')
            ->select('ledger.store_id', 'ledger.is_credit', 'ledger.last_whatsapp', 'stores.bussiness_name', 'stores.whatsapp', 'ledger.id', 'ledger.whatsapp_status', 'ledger.created_at AS last_date')
            ->selectSub(function ($query) {
                $query->select('created_at')
                      ->from('ledger as l2')
                      ->whereColumn('l2.store_id', 'ledger.store_id')
                      ->orderBy('l2.id')
                      ->limit(1);
            }, 'first_date')
            ->join('stores', 'stores.id', '=', 'ledger.store_id')
            ->whereNotNull('ledger.store_id')
            ->whereIn('ledger.id', function($query) {
                $query->select(DB::raw('MAX(id)'))
                      ->from('ledger')
                      ->groupBy('store_id');
            })->where('ledger.whatsapp_status', 0)
            ->where('ledger.is_credit', 1)
            ->whereBetween('ledger.created_at', [$startTime, $endTime])
            ->orderBy('ledger.id', 'desc')
            ->get();
            if(count($data)>0){
                foreach($data as $key =>$item){
                    $mobile = $item->whatsapp;
                    // $mobile = 8016638037;
                    $token = env('WHATSAPP_TOKEN_VARIABLE');
                    if (isValidMobileNumber($mobile)) {
                        $user_type = "store";
                        $store_id = $item->store_id;
                        $staff_id = 0;
                        $admin_id = 0;
                        $supplier_id = 0;
                        $select_user_name = $item->bussiness_name;
                        // $year = date('Y');
                        // $april_1 = $year . '-04-01';
                        // $from_date = $april_1;
                        $from_date = $item->first_date;
                        $to_date = date('Y-m-d');
                        $sort_by = 'asc';
                        $bank_cash = '';
                        $data = $outstanding = array();
                        $day_opening_amount = $is_opening_bal =  0;
                        $is_opening_bal_showable = 1;
                        $opening_bal_date = "";
                        if(!empty($user_type)){

                            DB::enableQueryLog();
            
                            $data = DB::table('ledger AS l')->select('l.*','p.voucher_no','p.payment_in','p.amount AS payment_amount','p.payment_mode','p.chq_utr_no','p.narration');
            
                            
                            $opening_bal = DB::table('ledger');
            
                            if($user_type == 'store' && !empty($store_id)){
                                $data = $data->where('l.user_type', 'store')->where('l.store_id',$store_id);
                                $opening_bal = $opening_bal->where('user_type',$user_type)->where('store_id',$store_id);
            
                            }

                            $check_ob_exist_store = DB::table('ledger')->where('purpose','opening_balance')->where('user_type', 'store')->where('store_id',$store_id)->orderBy('id','asc')->first();
            
                            if(!empty($check_ob_exist_store)){
            
                                $from_date = ($from_date < $check_ob_exist_store->entry_date) ? $check_ob_exist_store->entry_date : $from_date;
            
                                $is_opening_bal = 1;
            
                                $opening_bal_date = $check_ob_exist_store->entry_date;
            
            
                                if($from_date == $check_ob_exist_store->entry_date){                    
            
                                    $is_opening_bal_showable = 0;                    
            
                                } else {
            
                                    $opening_bal = $opening_bal->whereRaw(" entry_date BETWEEN '".$check_ob_exist_store->entry_date."' AND '".date('Y-m-d', strtotime('-1 day', strtotime($from_date)))."'  ");
            
                                }                
            
            
                            } else {
            
                                $opening_bal = $opening_bal->whereRaw(" entry_date <= '".date('Y-m-d', strtotime('-1 day', strtotime($from_date)))."'  ");
                              
            
                            } 
                            /* +++++++++++++++++++ */
                            $check_ob_exist_partner = DB::table('ledger')->where('purpose','opening_balance')->where('user_type', 'partner')->where('admin_id',$admin_id)->orderBy('id','asc')->first();
            
            
                            if(!empty($check_ob_exist_partner)){
            
                                $from_date = ($from_date < $check_ob_exist_partner->entry_date) ? $check_ob_exist_partner->entry_date : $from_date;
            
                                $is_opening_bal = 1;
            
                                $opening_bal_date = $check_ob_exist_partner->entry_date;
            
            
                                if($from_date == $check_ob_exist_partner->entry_date){                    
            
                                    $is_opening_bal_showable = 0;                    
            
                                } else {
            
                                    $opening_bal = $opening_bal->whereRaw(" entry_date BETWEEN '".$check_ob_exist_partner->entry_date."' AND '".date('Y-m-d', strtotime('-1 day', strtotime($from_date)))."'  ");
            
                                }                
            
                                
            
                            } else {
                                $opening_bal = $opening_bal->whereRaw(" entry_date <= '".date('Y-m-d', strtotime('-1 day', strtotime($from_date)))."'  ");
            
                            } 
            
            
            
                            
                           
            
                            if(!empty($from_date) && !empty($to_date)){
            
                                $data = $data->whereRaw("l.entry_date BETWEEN '".$from_date."' AND '".$to_date."' ");
            
                            }
                            
                            // if(Auth::user()->type == 2){
            
                            //     $opening_bal = $opening_bal->where('is_gst', 1);
            
                            // }
            
                            $opening_bal = $opening_bal->orderBy('entry_date',$sort_by);  
            
                            $opening_bal = $opening_bal->orderBy('updated_at',$sort_by);  
            
                            $opening_bal = $opening_bal->get();
            
                           
                            foreach($opening_bal as $ob){
            
                                if(!empty($ob->is_credit)){
            
                                    $credit_amount = $ob->transaction_amount;
            
                                    $day_opening_amount += $ob->transaction_amount;
            
                                }
            
                                if(!empty($ob->is_debit)){
            
                                    $debit_amount = $ob->transaction_amount;
            
                                    $day_opening_amount -= $ob->transaction_amount;
            
                                }
            
                            }
            
            
            
                            if(!empty($bank_cash)){
            
                                $data = $data->where('l.bank_cash', $bank_cash);
            
                            }
            
            
            
                            // dd($day_opening_amount);
            
                            $data = $data->leftJoin('payment AS p','p.id','l.payment_id');
            
                            $data = $data->orderBy('l.entry_date',$sort_by);  
            
                            $data = $data->orderBy('l.updated_at',$sort_by);  
            
                            $data = $data->get()->toarray(); 
                            // dd($data);
            
                        }
                        $ledgerpdfname = ucwords($user_type)."-".date('Y-m-d-H-i-s-A')."";
                        $pdf = Pdf::loadView('admin.report.ledger-pdf', compact('data','user_type','store_id','staff_id','admin_id','supplier_id','select_user_name','from_date','to_date','day_opening_amount','is_opening_bal','is_opening_bal_showable','opening_bal_date','bank_cash'));
                        $upload_path = public_path('uploads/whatsapp/ledger/');

                        $select_user_name = Str::slug($select_user_name, '-');
                        // Generate a unique filename using the invoice number
                        $ledger_pdf_filename = 'Ledger-'.$select_user_name. '.pdf';

                        // Check if the directory exists, if not, create it
                        if (!file_exists($upload_path)) {
                            mkdir($upload_path, 0777, true);
                        }

                        // Save the PDF content to the specified path
                        if ($pdf->save($upload_path . $ledger_pdf_filename)) {
                            // Construct the full path of the saved PDF file
                            $ledger_pdf_file_path = asset('uploads/whatsapp/ledger/' . $ledger_pdf_filename);
                        } else {
                            // If saving fails, set $final_invoice_pdf_path to null
                            $ledger_pdf_file_path = null;
                        }
                        if($ledger_pdf_file_path!=null){
                            try {
                                $mobile = '91'.$mobile;
                                $ch = curl_init();
                                $link = $ledger_pdf_file_path;
                                // Set the URL
                                $url = "https://transapi.bluwaves.in/api/sendFiles?token=$token&phone=$mobile&link=$link";
                                    // Initialize cURL session
                                $ch = curl_init($url);
                                // Set cURL options
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                $response = curl_exec($ch);
                                // Check for errors
                                if ($response === false) {
                                    // Handle error
                                    $error = curl_error($ch);
                                    $whatsapp_message_logs = DB::table('whatsapp_message_logs')->insert([
                                        'response' => json_encode($response),
                                        'mobile'=>$mobile,
                                        'sending_file'=>"Ledger",
                                        'created_at' => now(),
                                        'updated_at' => now(),
                                    ]);
                                    curl_close($ch);
                                } else {
                                    $whatsapp_message_logs = DB::table('whatsapp_message_logs')->insert([
                                        'response' => json_encode($response),
                                        'mobile'=>$mobile,
                                        'sending_file'=>"Ledger",
                                        'created_at' => now(),
                                        'updated_at' => now(),
                                    ]);
                                    $ledger = DB::table('ledger')
                                    ->where('id', $item->id)
                                    ->update(['last_whatsapp' => now(), 'whatsapp_status'=>1]);
                                    curl_close($ch);
                                }
                            } catch (\Exception $e) {
                                // Handle exception
                                $errorMessage = $e->getMessage();
                                $whatsapp_message_logs = DB::table('whatsapp_message_logs')->insert([
                                    'response' => json_encode($errorMessage),
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);
                            }
                        }
                    }
                }
                return "Message sent successfully";
            }else{
                $whatsapp_message_logs = DB::table('whatsapp_message_logs')->insert([
                    'response' => "success",
                    'mobile'=>null,
                    'sending_file'=>"Ledger",
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

    }
    public function daily_invoices_send(){
        $now = Carbon::now();
        $startTime = $now->copy()->subMinutes(75)->toDateTimeString();
        $endTime = $now->copy()->subMinutes(60)->toDateTimeString();
    
        $WhatsAppInvoice = WhatsAppInvoice::with('store')->where('status', 1)
            ->whereBetween('updated_at', [$startTime, $endTime])
            ->get();
        $data= [];
        if(count($WhatsAppInvoice)>0){
            foreach($WhatsAppInvoice as $key=>$item){
                // if($item->tb_required==1 && !empty($item->tally_bill_file) && $item->lr_required==1 && !empty($item->transport_lr_file)){
                //   $data[]= $item;
                // }
                // if($item->tb_required==0  && empty($item->tally_bill_file) && $item->lr_required==0 && empty($item->transport_lr_file)){
                //     $data[]= $item;
                // }
                if ($item->tally_bill_file && $item->transport_lr_file) {
                    $data[]= $item;
                } 
                if(
                    ($item->tb_required == 0 && $item->lr_required == 0) || 
                    ($item->tb_required == 0 && isset($item->transport_lr_file)) || 
                    ($item->lr_required == 0 && isset($item->tally_bill_file))
                ) {
                    $data[]= $item;
                }
            }
        }
        
        // dd($data);
        if(count($data)>0){
            foreach($data as $k=>$value){
                $send_message = 0; // Default to not send message
                // Check conditions for sending message
                // if (($value->tb_required == 0 && $value->lr_required == 0) || 
                //     ($value->tb_required == 1 && $value->lr_required == 1 && 
                //      $value->tally_bill_file && $value->transport_lr_file)) {
                //     $send_message = 1; // Set to send message
                // }
                if ($value->tally_bill_file && $value->transport_lr_file) {
                    $send_message = 1;
                } elseif (
                    ($value->tb_required == 0 && $value->lr_required == 0) || 
                    ($value->tb_required == 0 && isset($value->transport_lr_file)) || 
                    ($value->lr_required == 0 && isset($value->tally_bill_file))
                ) {
                    $send_message = 1;
                } else {
                    $send_message = 0;
                }
                $id = $value->id;
                $mobile = $value->store->whatsapp;
                // $mobile = 8016638037;
                $WhatsAppInvoice = WhatsAppInvoice::where('id', $id)->where('status', 1)->first();
                if($send_message==1){
                    if (isValidMobileNumber($mobile)) {
                        $invoice = Invoice::with('order', 'store', 'user', 'products')->where('invoice_no', $value->invoice_no)->first();

                        if (!$invoice) {
                            $final_invoice_pdf_path = null;
                        }else{
                            // Generate PDF for the invoice
                            if (!empty($invoice->is_gst)) {
                                $pdf = PDF::loadView('admin.packingslip.invoice', compact('invoice'));
                            } else {
                                $pdf = PDF::loadView('admin.packingslip.cashslip', compact('invoice'));
                            }

                            $upload_path = public_path('uploads/whatsapp/invoice/');
                            $bussiness_name = $invoice->store?$invoice->store->bussiness_name:date('d-m-Y');
                            $bussiness_name = Str::slug($bussiness_name, '-');
                            // Generate a unique filename using the invoice number
                            $invoice_pdf_filename = 'Estimate-slip-'.$bussiness_name . '.pdf';

                            // Check if the directory exists, if not, create it
                            if (!file_exists($upload_path)) {
                                mkdir($upload_path, 0777, true);
                            }

                            // Save the PDF content to the specified path
                            if ($pdf->save($upload_path . $invoice_pdf_filename)) {
                                // Construct the full path of the saved PDF file
                                $final_invoice_pdf_path = asset('uploads/whatsapp/invoice/' . $invoice_pdf_filename);
                            } else {
                                // If saving fails, set $final_invoice_pdf_path to null
                                $final_invoice_pdf_path = null;
                            }
                        }

                        // Fetch the packingslip
                            $packingslip = PackingslipNew1::where('id', $value->packingslip_id)->first();
                            $data = DB::table('packing_slip AS ps')->select('ps.*', 'p.name AS pro_name', 'o.order_no', 'o.created_at AS ordered_at', 's.store_name', 's.whatsapp AS store_whatsapp', 's.bussiness_name')->leftJoin('orders AS o', 'o.id', 'ps.order_id')->leftJoin('products AS p', 'p.id', 'ps.product_id')->leftJoin('stores AS s', 's.id', 'o.store_id')->where('ps.slip_no', $packingslip->slipno)->get();

                            // Generate PDF for the packing slip
                            $packingslip_pdf = PDF::loadView('admin.packingslip.pdf', compact('data', 'packingslip'))->output();

                            // Define the upload directory
                            $upload_path = public_path('uploads/whatsapp/packingslip/');

                            // Generate a unique filename using the packing slip ID
                            $packingslip_pdf_filename = 'Packing-slip-' . $bussiness_name . '.pdf';

                            // Check if the directory exists, if not, create it
                            if (!file_exists($upload_path)) {
                                mkdir($upload_path, 0777, true);
                            }
                            // Save the PDF content to the specified path
                            file_put_contents($upload_path . $packingslip_pdf_filename, $packingslip_pdf);

                            // Construct the full path of the saved PDF file
                            $final_packingslip_pdf_path = asset('uploads/whatsapp/packingslip/' . $packingslip_pdf_filename);

                            // Check if the PDF was saved successfully
                            if (file_exists($upload_path . $packingslip_pdf_filename)) {
                            $packingslip_pdf_filename = $packingslip_pdf_filename;
                            } else {
                                $packingslip_pdf_filename = null;
                            }

                        // File links
                        $fileLinks = [
                            [
                                'name'=>"Invoice",
                                'url' => $final_invoice_pdf_path,
                            ],
                            [
                                'name'=>"Packingslip",
                                'url' => $final_packingslip_pdf_path,
                            ],
                            [
                                    'name'=>"transport-LR",
                                    'url' => $value->transport_lr_file ? asset($value->transport_lr_file) : null,
                                ],
                            [
                                    'name'=>"tally-bill",
                                    'url' => $value->tally_bill_file ? asset($value->tally_bill_file) : null,
                            ],
                        ];
                            $mobile ='91'.$mobile;
                        // Get the token from the environment configuration
                        $token = env('WHATSAPP_TOKEN_VARIABLE');
                            foreach ($fileLinks as $fileLink) {
                                if($fileLink['url']!=null){
                                    try {
                                        // Initialize cURL session
                                        $ch = curl_init();
                                        $link = $fileLink['url'];
                                        // Set the URL
                                        $url = "https://transapi.bluwaves.in/api/sendFiles?token=$token&phone=$mobile&link=$link";
                                            // Initialize cURL session
                                        $ch = curl_init($url);
                                        // Set cURL options
                                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                        $response = curl_exec($ch);
                                        // Check for errors
                                        if ($response === false) {
                                            // Handle error
                                            $error = curl_error($ch);
                                            $whatsapp_message_logs = DB::table('whatsapp_message_logs')->insert([
                                                'response' => json_encode($response),
                                                'mobile'=>$mobile,
                                                'sending_file'=>$fileLink['name'],
                                                'created_at' => now(),
                                                'updated_at' => now(),
                                            ]);
                                            curl_close($ch);
                                        } else {
                                            $whatsapp_message_logs = DB::table('whatsapp_message_logs')->insert([
                                                'response' => json_encode($response),
                                                'mobile'=>$mobile,
                                                'sending_file'=>$fileLink['name'],
                                                'created_at' => now(),
                                                'updated_at' => now(),
                                            ]);
                                            curl_close($ch);
                                        }
                                    } catch (\Exception $e) {
                                        // Handle exception
                                        $errorMessage = $e->getMessage();
                                        $whatsapp_message_logs = DB::table('whatsapp_message_logs')->insert([
                                            'response' => json_encode($errorMessage),
                                            'mobile'=>$mobile,
                                            'created_at' => now(),
                                            'updated_at' => now(),
                                        ]);
                                    }
                                }
                        }
                        $whatsapp_invoices = DB::table('whatsapp_invoices')
                        ->where('id', $id)
                        ->update(['last_whatsapp' => now(), 'status'=>2]);
                    }
                }
            }
            return "Message sent successfully!";
        }else{
            $whatsapp_message_logs = DB::table('whatsapp_message_logs')->insert([
                'response' => "success",
                'mobile'=>null,
                'sending_file'=>"Invoice",
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            return "No Data found!";
        }
    }
    public function weekly_whatsapp_message_logs_delete(){
        $now = Carbon::now();
        // Calculate the date and time for 7 days ago
        $sevenDaysAgo = $now->subDays(7);
        // Perform the delete operation
        DB::table('whatsapp_message_logs')
            ->where('created_at', '<', $sevenDaysAgo)
            ->delete();
            return "deleted data!";
    }
}

