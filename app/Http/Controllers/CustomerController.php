<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;

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
            return response()->json(['error' => $validator->messages()], 200);
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        //
    }
}
