<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Room;

class RoomController extends Controller
{
    //add room
    public function addRoom(Request $request){
        $validated=$request->validate([
            'title' => 'required|string',
            'roomType_id' => 'required|string',
            'price' => 'required|string',
            'discount' => 'required|string',
            'status' => 'required|string',
            'image' => 'required|image',
            'is_favorite'=>'required'
        ]);

        $image=$request->file('image');
        $imageName = time() . '.' . $image->getClientOriginalExtension();
        $image->move('Room/', $imageName);
        $base64Image = base64_encode(file_get_contents('Room/'.$imageName));

        $add_room=Room::create([
            'title'=>$request->title,
            'roomType_id'=>$request->roomType_id,
            'price'=>$request->price,
            'discount'=>$request->discount,
            'status'=>$request->status,
            'image'=>$base64Image,
            'is_favorite'=>$request->is_favorite,
        ]);

        return response()->json([
            'status'=>201,
            'message'=>'Room added Successfully',
            'data'=>['Room_data'=>$add_room],
        ]);
    }

    //show all rooms of the same type
    public function allRooms($id){
        $all_rooms=Room::where('roomType_id',$id)->get();

        return response()->json([
            'status'=>201,
            'message'=>' All Rooms of this type',
            'data'=>['All Rooms'=>$all_rooms],
        ]);
    }

    public function room_details($id)
    {
        $room = Room::findOrFail($id);

        return response()->json([
            'status'=>201,
            'message'=>'room details',
            'data'=>['Room_data'=>$room],
        ]);
    }

    // make room favorite or not
/*
    public function updateFavorite(Request $request,$favorite){
        $request->validate([
            'favorite'=>'required|boolean',
        ]);

        $room=Room::findOrFail($favorite);
        $room->update(['favorite' => $request->favorite]);

        return response()->json([
            'status'=>201,
            'message'=>' Favorites updated successfully',
            'data'=>['Favorites'=>$room],
        ]);
    }  */





}
