<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RoomType;

class RoomTypeController extends Controller
{
    //add room type
    public function addType(Request $request){
        $validated=$request->validate([
            'name' => 'required|string',
            'image' => 'required|image',
            'price' => 'required|string',
            'size' => 'required|string',
        ]);

        $image=$request->file('image');
        $imageName = time() . '.' . $image->getClientOriginalExtension();
        $image->move('RoomType/', $imageName);
        $base64Image = base64_encode(file_get_contents('RoomType/'.$imageName));

        $type=RoomType::create([
            'name'=>$request->name,
            'price'=>$request->price,
            'size'=>$request->size,
            'image'=>$base64Image,
        ]);


        return response()->json([
            'status'=>201,
            'message'=>'Room Type added Successfully',
            'data'=>['Room_Type_data'=>$type],
        ]);
    }

    // show all types of rooms
    public function allTypes(){
        $all_types=RoomType::all();
        return response()->json([
            'status'=>201,
            'message'=>' All Room Type',
            'data'=>['All Room Type'=>$all_types],
        ]);
    }


}

