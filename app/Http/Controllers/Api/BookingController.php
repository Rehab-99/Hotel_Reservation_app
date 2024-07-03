<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Room;
use App\Notifications\PaymentCancelled;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Illuminate\Support\Facades\Notification;
use App\Notifications\PaymentSuccessful;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{

    protected $provider;

    public function __construct()
    {
        $this->provider = new PayPalClient();
    }

    public function booking_step1(Request $request){
        $validated=$request->validate([
            'room_id'=>'required|exists:rooms,id',
            'check_in'=>'required|date',
            'check_out'=>'required|date|after:check_in',
            'room_type'=>'required|string',
            'num_of_rooms'=>'required',
            'guest'=>'required',
            'num_of_nights'=>'required|integer',
        ]);
            $check_in=$request->check_in;
            $check_out=$request->check_out;
            $num_of_nights=$request->num_of_nights;
            $room=Room::find($request->room_id);

        $isBooked=Booking::where('room_id',$request->room_id)
        ->where('check_in','<=',$check_out)
        ->where('check_out','>=',$check_in)->exists();
        // check room is available or not
        if($isBooked){
            return response()->json([
                'status'=>200,
                'message'=>'room is already booked ',
                'data'=>['booking_data'=> []],
            ]);
        }
        $subtotal=$room->price * $num_of_nights;
        $discount=$room->discount * $num_of_nights;
        $total=$subtotal-$discount;

        $booking=Booking::create([
            'room_id'=>$request->room_id,
            'check_in'=>$request->check_in,
            'check_out'=>$request->check_out,
            'room_type'=>$request->room_type,
            'num_of_rooms'=>$request->num_of_rooms,
            'guest'=>$request->guest,
            'num_of_nights'=>$request->num_of_nights,
            'subtotal'=>$subtotal,
            'discount'=>$discount,
            'total'=>$total,
        ]);
        return response()->json([
            'status'=>200,
            'message'=>'continue to complete booking',
            'data'=>['booking_id' => $booking->id],
        ]);
    }

    public function booking_step2(Request $request){
        $validated=$request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'first_name'=>'required|string',
            'surname'=>'required|string',
            'email'=>'required|string',
            'phone'=>'required|string',
            'payment_method'=>'required'
        ]);

        $booking = Booking::find($request->booking_id);
        $user = auth()->user();

        $booking->update([
            'first_name' => $request->first_name,
            'surname' => $request->surname,
            'email' => $request->email,
            'phone' => $request->phone,
            'payment_method' => $request->payment_method,
            'user_id' => $user->id,
        ]);

        $payment_method=$request->payment_method;
        if($payment_method == 'cash'){
            $booking->update(['status' => 'confirmed']);
                    return response()->json([
                        'status'=>200,
                        'message'=>'booking success',
                        'data'=>['booking_data'=>$booking],
                    ]);
        }else{
                // Initialize PayPal Client
            $provider = new PayPalClient();
            $provider->setApiCredentials(config('paypal'));
            $paypalToken=$provider->getAccessToken();
            $response = $provider->createOrder([
                "intent" => "CAPTURE",
                "application_context" => [
                    "return_url" => route('paypal.success',['booking_id' => $booking->id]),
                    "cancel_url" => route('paypal.cancel',['booking_id' => $booking->id]),
                ],
                "purchase_units" => [
                    0 => [
                        "amount" => [
                            "currency_code" => "USD",
                            "value" => number_format($booking->total, 2, '.', '')
                        ],
                        "description" => "Booking Payment for " . $booking->id,
                    ]
                ]
            ]);
            if (isset($response['id']) && $response['id'] != null) {
                // Return the approval URL to redirect to
                foreach ($response['links'] as $links) {
                    if ($links['rel'] == 'approve') {
                        return response()->json([
                            'status' => 302,
                            'message' => 'Redirecting to PayPal',
                            'data' => ['redirect_url' => $links['href']],
                        ]);
                    }
                }
            }

            return response()->json([
                'status' => 400,
                'message' => 'Something went wrong.',
                'data'=>['error' => $response],
            ]);
        }
    }

    public function paypalSuccess(Request $request){
        $booking = Booking::findOrFail($request->booking_id);
        $booking->update(['status' => 'confirmed']);
        $user = User::findOrFail($booking->user_id);

        Notification::send($user,new PaymentSuccessful($booking->id));
        return response()->json([
                    'status' => 200,
                    'message' => 'Payment successful',
                    'data'=>['booking_data'=>$booking],
        ]);
    }

    public function paypalCancel(Request $request){
        $booking = Booking::findOrFail($request->booking_id);
        $user = User::findOrFail($booking->user_id);
        Notification::send($user,new PaymentCancelled($booking->id));
        $booking->delete();

            return response()->json([
                'status' => 400,
                'message' => 'Payment cancelled',
                'data'=>[],
            ]);
        }

}

