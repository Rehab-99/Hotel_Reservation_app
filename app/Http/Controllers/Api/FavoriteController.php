<?php

namespace App\Http\Controllers\Api;
use App\Models\Room;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function addToFavorites($id)
    {
        $room = Room::findOrFail($id);
        $room->is_favorite = true;
        $room->save();

        return response()->json([
            'status'=>200,
            'message' => 'Room added to favorites successfully',
            'data'=>['Room_data'=>$room],
        ]);
    }

    public function removeFromFavorites($id)
    {
        $room = Room::findOrFail($id);
        $room->is_favorite = false;
        $room->save();

        return response()->json([
            'status'=>200,
            'message' => 'Room removed from favorites successfully',
            'data'=>['Room_data'=>$room],
        ]);
    }

    public function favoriteRooms(Request $request)
    {
        $favoriteRooms = Room::where('is_favorite', true)->get();

        return response()->json([
            'status'=>200,
            'message' => 'This is All favorite rooms',
            'data'=>['Room_data'=>$favoriteRooms],
        ]);
    }


}
