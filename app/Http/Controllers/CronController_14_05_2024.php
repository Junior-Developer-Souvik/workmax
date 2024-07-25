<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\PurchaseOrder;
use App\Models\Ledger;
use App\Models\UserCity;
use App\User;

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

}
