<?php

namespace App\Http\Controllers;

use App\Models\Holidays;
use App\Models\Images;
use App\Models\Properties;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class FilterController extends Controller
{
    public function getAllPropertiesWithCity(Request $request)
    {
        DB::statement("SET SQL_MODE=''");

        $city = $request->input('city');
        $hosts = $request->input('hosts');
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');

        $currentDate = "2021-05-08";

        $query = Properties::select(
            'idProperty',
            'propertyName',
            'propertyAmount',
            'propertyAbility',
            'images.imageLink',
            'images.property_id',
            'propertyDescription',
            'propertyCity',
            'propertyStatus'
        )->join(DB::raw('(SELECT * FROM images GROUP BY property_id) as images'), function ($join) {
            $join->on('properties.idProperty', '=', 'images.property_id');
        })->where('propertyStatus', 'Publicado');

        if ($city !== null) {
            $query->where('propertyCity', 'LIKE', '%' . $city . '%');
        }

        if ($hosts !== null) {
            $query->where('propertyAbility', $hosts);
        }

        if ($startDate !== null && $endDate !== null) {
            $query->where(function ($subquery) use ($startDate, $endDate) {
                $subquery->whereNotIn('properties.idProperty', function ($reservationQuery) use ($startDate, $endDate) {
                    $reservationQuery->select('idProperty')
                        ->from('reservations')
                        ->where('startDate', '<=', $endDate)
                        ->where('endDate', '>=', $startDate);
                });
            });
        } else {
            $query->leftJoin('status_properties', 'status_properties.property_id', '=', 'properties.idProperty')
                ->where(function ($query) use ($currentDate) {
                    $query->whereNull('status_properties.startDate')
                        ->orWhere('status_properties.startDate', '>', $currentDate);
                })
                ->orWhere(function ($query) use ($currentDate) {
                    $query->whereNull('status_properties.endDate')
                        ->orWhere('status_properties.endDate', '<', $currentDate);
                })
                ->orWhere(function ($query) use ($currentDate) {
                    $query->where('status_properties.status', '!=', 'Pausado')
                        ->orWhereNull('status_properties.status');
                });
        }
        $properties = $query->get();

        return response()->json($properties);
    }
}
