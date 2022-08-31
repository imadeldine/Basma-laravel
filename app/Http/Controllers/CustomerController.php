<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;



class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    // public function index()
    // {
    //     return view('customer.index', [
    //         'customers' => DB::table('customers')->paginate(15)
    //     ]);
    // }

    public function index(Request $request)
    {
        $num=$request->num;
        $customers = Customer::select('id', 'firstname', 'email')
        ->paginate($num);
        return response()->json([
            'success' => true,
            'message' => 'Customers retrieved successfully',
            'data' => $customers
        ], Response::HTTP_OK);
    }
 
    public function average_registrations_per_day()
    {
        $customers = Customer::selectRaw('count(*) as count, date(created_at) as date')
        ->groupBy('date')
        ->get();
        return response()->json([
            'success' => true,
            'message' => 'Customers retrieved successfully',
            'data' => $customers
        ], Response::HTTP_OK);
    }

    public function average_registrations_this_week()
    {
      
        $customers = Customer::selectRaw('count(*) as count, date(created_at) as week')
        ->whereRaw('date(created_at) >= curdate() - INTERVAL DAYOFWEEK(curdate())+1 DAY')
        ->groupBy('week')
        ->get();
        return response()->json([
            'success' => true,
            'message' => 'Customers retrieved successfully',
            'data' => $customers
        ], Response::HTTP_OK);
    }

    public function average_registrations_last_month()
    {
        $customers = Customer::selectRaw('count(*) as count, date(created_at) as month')
        ->whereRaw('date(created_at) >= curdate() - INTERVAL DAYOFMONTH(curdate())+1 DAY')
        ->groupBy('month')
        ->get();
        return response()->json([
            'success' => true,
            'message' => 'Customers retrieved successfully',
            'data' => $customers
        ], Response::HTTP_OK);
    }

    public function average_registrations_last_year()
    {
        $customers = Customer::selectRaw('count(*) as count, date(created_at) as year')
        ->whereRaw('date(created_at) >= curdate() - INTERVAL DAYOFYEAR(curdate())+1 DAY')
        ->groupBy('year')
        ->get();
        return response()->json([
            'success' => true,
            'message' => 'Customers retrieved successfully',
            'data' => $customers
        ], Response::HTTP_OK);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        //Validate data
        $data = $request->only('firstname','lastname', 'email', 'phone','password');
        $validator = Validator::make($data, [
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'email' => 'required|email|unique:customers',
            'phone' => 'required|string|unique:customers|min:8',
            'password' => 'required|string|min:6|max:50'
        ]);
    
        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 500);
        }
    
        //Request is valid, create new user
        $customer = Customer::create([
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => bcrypt($request->password)
        ]);
    
        //Customer created, return success response
        return response()->json([
            'success' => true,
            'message' => 'Customer created successfully',
            'data' => $customer
        ], Response::HTTP_OK);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request){
       
        $response = Http::asForm()->post(env("GOOGLE_RECAPTCHA_URL"), [
            'secret' => env("GOOGLE_RECAPTCHA_SECRET"),
            'response' => $request->recaptcha_token,
            'remoteip' => $request->ip(),
        ]);
        Log::info($response);
        Log::info($response['success']);
        Log::info($request->all());
        // dd();
        if($response)
        {

            if($response['success'])
            {
                $customer = new Customer;
                $customer->fill($request->all());
            
                if ($customer->save())
                {
                    return response()->json([

                        'success' => true,
                        'message' => 'Operation succeeded',
                        'data'    => $customer,
                    ],200);
                }
                else
                {
                    return response()->json([

                        'success' => false,
                        'message' => 'Operation failed',
                    ],500);
                }
            }else
            {
                return response()->json([

                    'success' => false,
                    'message' => 'Operation failed',
                    'errors'=>['recaptcha_token'=>'reCAPTCHA verification failed.']
                ],422);
            }
        }
        else
        {
            return response()->json([

                'success' => false,
                'message' => 'Operation failed',
                'errors'=>['recaptcha_token'=>'reCAPTCHA is required.']
            ],404);
        }
    }
 
    public function sendDailyData()
    {
        $customers = Customer::where('created_at', '>', Carbon::now()->subDays(1))->count();
        
        $mailData= array(
            'message' => "$customers",
        );
        
        if($customers)
        {
            
            Mail::send('mail',["data"=>$mailData], function($message)
            {
    
                $message->from(config('mail.from_email'));
                $message->to(config('mail.to_email'), 'Basma')->subject('Basma New Registered Customers');
                
            }); 
        }
    }


    public function Average($range){

        $customers = Customer::count();
        $customers_data = Customer::where('created_at', '>', Carbon::now()->subDays($range))->get('created_at'); 
        $customer = $customers_data->count();
        
        if($range == 1)
        {
            $average = $customer / 24;
            $timeFrame = 'Per Hour';
        }
        elseif($range == 7)
        {
            $average = $customer / 7;
            $timeFrame = 'Per Day';

        }
        elseif($range == 30)
        {
            $average = $customer / 4;
            $timeFrame = 'Per Week';

        }
        elseif($range == 90)
        {
            $average = $customer / 3;
            $timeFrame = 'Per Month';
        }
        elseif($range == 365)
        {
            $average = $customer / 12;
            $timeFrame = 'Per Month';
        }

        if ($customers_data)
        {
            return response()->json([

                'success'    => true,
                'message'    => 'Operation succeeded',
                'data'       => $customers_data,
                'total'      => $customer,
                'average'    => $average,
                'time_frame' => $timeFrame,
                'customers' => $customers,
            ],200);
        }
        else
        {
            return response()->json([

                'success' => false,
                'message' => 'error 500',
            ],500);
        }
    }

}
