<?php

namespace App\Http\Controllers;

use App\Models\NotificationsHosts;
use App\Models\Properties;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;


class NotificationsHostController extends Controller
{
    public function createdNotification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'startDate' => 'date_format:Y-m-d',
            'endDate' => 'date_format:Y-m-d',
            'nameProperty' => 'required|string',
            'nameUser' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }


        $newNotification = new NotificationsHosts([
            'startDate' => $request->startDate,
            'endDate' => $request->endDate,
            'nameProperty' => $request->nameProperty,
            'nameUser' => $request->nameUser,
        ]);

        $newNotification->idProperty = $request->idProperty;
        $newNotification->host_id = $request->idUser;

        $newNotification->save();

        return response()->json([
            'message' => 'User successfully notification host',
            'notificationHost' => $newNotification,
        ], 201);
    }


       public function userByIdHost($idUser)
    {
        $user = DB::table('notifications_hosts')->where('host_id', '=', $idUser)
            ->select(
                'notifications_hosts.idNotificationsHosts',
                'notifications_hosts.startDate',
                'notifications_hosts.endDate',
                'notifications_hosts.nameProperty',
                'notifications_hosts.nameUser',
                'notifications_hosts.idProperty',
                'notifications_hosts.host_id'
            )
            ->get();
        foreach ($user as $notification) {
            $nameUserParts = explode(', ', $notification->nameUser);

            $notification->nameUser = $nameUserParts[0];
            $notification->idUser = intval($nameUserParts[1]);
            $host = User::select('user_pictures.user_picture')
                ->leftJoin('user_pictures', 'users.idUser', '=', 'user_pictures.idUser')
                ->where('users.idUser', intval($nameUserParts[1]))
                ->first();
            $notification->user_picture = $host->user_picture;
        }
        return $user;
    }
}
