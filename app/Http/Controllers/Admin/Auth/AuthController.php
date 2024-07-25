<?php



namespace App\Http\Controllers\Admin\Auth;



use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

use App\User;



class AuthController extends Controller

{

    public function index()

    {

        return view('admin.auth.login');

    }

    public function login(Request $request)

    {

        // die('Hi');

        $request->validate([

            'mobile' => 'required',

            'password' => 'required|string'

        ]);



        $adminCreds = $request->only('mobile', 'password');

        $checkAdmin = \App\User::where('mobile',$request->mobile)->first();



        if(!empty($checkAdmin)){

            if(!empty($checkAdmin->status)){    

                if(!in_array($checkAdmin->designation,[1])){

                    if ( Auth::guard('web')->attempt($adminCreds) ) {

                        //  dd(Auth::guard('web')->attempt($adminCreds));

                        return redirect()->route('admin.home');
                            
                    } else {

                        return redirect()->route('admin.login')->withInputs($request->all())->with('failure', 'Invalid credentials. Try again');

                    }

                } else {

                    return redirect()->route('admin.login')->withInputs($request->all())->with('failure', 'You have no access to login here');

                }

                

            }else{

                return redirect()->route('admin.login')->withInputs($request->all())->with('failure', 'Inactive user');

            }

        }else{

            return redirect()->route('admin.login')->withInputs($request->all())->with('failure', 'No user found');

        }



    }



    public function adminLogout()

    {

        Auth::logout();

        // Auth::guard('admin')->logout();

        return redirect(route('admin.login'));

    }

}

