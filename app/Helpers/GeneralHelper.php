<?php

namespace App\Helpers;

use App\Models\Country;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;

class GeneralHelper
{
    public static function checkFileExtension($url)
    {
        $ext = File::extension($url);
        if (in_array($ext, ['jpeg', 'jpg', 'png', 'svg'])) {
            return 'image';
        }
        return 'file';
    }

    public static function applyUserFilter($query,$model=null)
    {
        if (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();

            $query->where(function($q) use($user){
                $q->where('organization_id', $user->organization_id);
            });
        } elseif (Auth::guard('web2')->check()) {
            $user = Auth::guard('web2')->user();
            $teamIds = self::getTeam($user);

            if($model == 'ErpCustomer'){
                $query->where(function($q) use($teamIds){
                    $q->whereIn('sales_person_id',$teamIds);
                });
                
            }else{
                $query->where(function($q) use($teamIds,$user){
                    $q->whereHas('customer',function($query) use($teamIds){
                            $query->whereIn('sales_person_id',$teamIds);
                        });
                });
            }

        }

        return $query;
    }

    public static function applyDiaryFilter($query,$model=null)
    {
        if (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();

            $query->where(function($q) use($user){
                $q->where('organization_id', $user->organization_id);
            });
        } elseif (Auth::guard('web2')->check()) {
            $user = Auth::guard('web2')->user();
            $teamIds = self::getTeam($user);
            
            $query->where(function($q) use($teamIds,$user){
                $q->where('created_by',$user->id)
                    ->orWhereHas('customer',function($query) use($teamIds){
                        $query->whereIn('sales_person_id',$teamIds);
                    });
            });

        }

        return $query;
    }

    public static function applyDateFilter($query, $request, $dateColumn){
        if ($request->date_filter == 'today') {
            $query->whereDate($dateColumn,date('Y-m-d'));
        }
        
        elseif ($request->date_filter == 'week') {
            $startOfWeek = Carbon::now()->startOfWeek()->toDateString();
            $endOfWeek = Carbon::now()->endOfWeek()->toDateString();
            $query->whereDate($dateColumn, '>=', $startOfWeek)
            ->whereDate($dateColumn, '<=', $endOfWeek);
        }

        elseif ($request->date_filter == 'month') {
            $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
            $endOfMonth = Carbon::now()->endOfMonth()->toDateString();
            $query->whereDate($dateColumn, '>=', $startOfMonth)
            ->whereDate($dateColumn, '<=', $endOfMonth);
        }
        elseif ($request->date_range) {
            $duration = explode(' to ', $request->date_range);
            $from_date = Carbon::parse($duration[0]);
            $to_date = isset($duration[1]) ? Carbon::parse($duration[1]) : Carbon::parse($duration[0]);

            $query->whereDate($dateColumn, '<=', $to_date)
            ->whereDate($dateColumn, '>=', $from_date);
        }
        else {
        // elseif ($request->date_filter == 'ytd') {
            // $startOfYear = Carbon::now()->startOfYear()->toDateString();
            $currentYear = Carbon::now()->year;
            $startOfFinancialYear = Carbon::create($currentYear, 4, 1);
            if (Carbon::now()->month < 4) {
                $startOfFinancialYear->subYear();
            }

            $startOfYear = $startOfFinancialYear->toDateString();
            $today = Carbon::now()->toDateString();
            $query->whereDate($dateColumn, '>=', $startOfYear)
            ->whereDate($dateColumn, '<=', $today);
        }

        return $query;
    }

    public static function loginUserType()
    {
        if (Auth::guard('web')->check()) {
            $type = 'user';
        } elseif (Auth::guard('web2')->check()) {
            $type = 'employee';
        } else {
            $type = request() -> user() ?-> authenticable_type;
        }

        return $type;
    }

    public static function dateFormat($date)
    {
        $date = $date ? date('d/m/Y', strtotime($date)) : '';
        return $date;
    }

    public static function timeFormat($date)
    {
        $date = $date ? date('h:i A', strtotime($date)) : '';
        return $date;
    }

    public static function getTeam($user){
        $teamIds = Employee::where(function($q) use($user){
            $q->where('manager_id', $user->id)
            ->orWhere('id',$user->id);
        })
        ->pluck('id')
        ->toArray();

        return $teamIds;
    }

    public static function dateFormat2($date)
    {
        if ($date) {
            // Convert the date to a timestamp
            $timestamp = strtotime($date);
            
            // Get the day, month, and year
            $day = date('j', $timestamp);  // Day of the month without leading zeros
            $month = date('F', $timestamp); // Full month name (e.g., January, February, etc.)
            $year = date('Y', $timestamp);  // Year (e.g., 2025)
            
            // Determine the suffix (st, nd, rd, th) for the day
            if (in_array($day, [11, 12, 13])) {
                $suffix = 'th'; // Special case for 11th, 12th, and 13th
            } else {
                switch ($day % 10) {
                    case 1:
                        $suffix = 'st';
                        break;
                    case 2:
                        $suffix = 'nd';
                        break;
                    case 3:
                        $suffix = 'rd';
                        break;
                    default:
                        $suffix = 'th';
                        break;
                }
            }

            // Format the date as 1st-April-2025
            $formattedDate = $day . $suffix . '-' . $month . '-' . $year;
        } else {
            $formattedDate = ''; // Return an empty string if no date provided
        }

        return $formattedDate;
    }

    public static function dateFormat3($date)
    {
        $date = $date ? date('d-m-Y', strtotime($date)) : '';
        return $date;
    }


}