<?php

namespace App\Http\Controllers;

use App\Models\User_picture;
use Illuminate\Http\Request;

class UserPictureController extends Controller
{
    public function updateUserPicture(Request $request, $idUser)
    {

        $userPicture = User_picture::where('idUser', $idUser)->first();

        if (!$userPicture) {
            User_picture::create([
                'idUser' => $idUser,
                'user_picture' => $request->user_picture,
            ]);
            return response()->json([
                'mesage' => 'User photo successfully created',
                'idUser' => $idUser,
                'userPicture' => $userPicture
            ]);
        }

        $userPicture->update(['user_picture' => $request->user_picture]);

        return response()->json([
            'mesage' => 'User photo successfully updated',
            'idUser' => $idUser,
            'userPicture' => $userPicture
        ]);
    }
}
