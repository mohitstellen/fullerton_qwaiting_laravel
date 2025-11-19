<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\Location;
use App\Models\Category;
use App\Models\Counter;
use App\Models\User;
use Illuminate\Support\Facades\Session;
use App\Models\Domain;

class EnsureLocationExists
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
          // Check if the user is logged in and has any locations

    //  if(Auth::check()){

    //       if (Location::where('team_id',tenant('id'))->where('status',1)->count() == 0) {
    //         // return redirect()->route('tenant.locations')->with('error', 'Please create a location first.');
    //         if (!$request->is('locations', 'locations/*')) {
    //             return redirect()->route('tenant.locations')->with('error', 'Please create a location first.');
    //         }

    //         if(empty(User::getDefaultLocation())){
    //             return redirect()->route('tenant.locations')->with('error', 'Please create a location first.');
    //         }
    //     }

        //    $location = Session::get('selectedLocation');

        //   if (!Category::whereJsonContains('category_locations', (string) $location)->exists()) {

        //     // return redirect()->route('tenant.locations')->with('error', 'Please create a location first.');
        //     if (!$request->is('category/1/create', 'category/1/create')) {
        //         return redirect()->route('tenant.category.create',['level'=>1])->with('error', 'Please create a category first.');
        //     }

        //     if(empty(User::getDefaultLocation())){
        //         return redirect()->route('tenant.category.create',['level'=>1])->with('error', 'Please create a category first.');
        //     }
        // }
        //   if (!Counter::whereJsonContains('counter_locations', (string) $location)->exists()) {

        //     // return redirect()->route('tenant.locations')->with('error', 'Please create a location first.');
        //     if (!$request->is('add-counter', 'add-counter')) {
        //         return redirect()->route('tenant.add-counter')->with('error', 'Please create a counter first.');
        //     }

        //     if(empty(User::getDefaultLocation())){
        //         return redirect()->route('tenant.add-counter')->with('error', 'Please create a counter first.');
        //     }
        // }

        // $users = User::whereNotNull('locations')
        //     ->where('locations', '!=', '')
        //     ->where('id', '!=', Auth::id())
        //     ->whereRaw("JSON_VALID(locations)")
        //     ->where(function ($query) use($location){
        //         $query->whereJsonContains('locations', (string) $location)
        //               ->orWhereJsonContains('locations', (int) $location);
        //     })->exists();


        //      if (!$users) {

        //     // return redirect()->route('tenant.locations')->with('error', 'Please create a location first.');
        //     if (!$request->is('staff/create', 'staff/create')) {
        //         return redirect()->route('tenant.staff.create')->with('error', 'Please create a Staff first.');
        //     }

        //     if(empty(User::getDefaultLocation())){
        //         return redirect()->route('tenant.staff.create')->with('error', 'Please create a staff first.');
        //     }
        // }

    // }

    //     return $next($request);

     if (Auth::check()) {
            $teamId = tenant('id') ?? Auth::user()->team_id;

            // Get current domain settings
            $domain = Domain::where('team_id',$teamId)->first();

            // Count active locations for this team
            $activeLocationsCount = Location::where('team_id', $teamId)
                ->where('status', 1)
                ->count();

            // Case 1: No active locations
            if ($activeLocationsCount == 0) {
                if (!$request->is('locations', 'locations/*')) {
                    return redirect()->route('tenant.locations')
                        ->with('error', 'Please create a location first.');
                }

                if (empty(User::getDefaultLocation())) {
                    return redirect()->route('tenant.locations')
                        ->with('error', 'Please create a location first.');
                }
            }

            // Case 2: Has active locations
            if ($activeLocationsCount > 0) {
                // ✅ If location page is enabled, force user to select location
                if ($domain && $domain->enable_location_page == 1) {
                    if (!Session::has('selectedLocation') && !$request->is('select-location', 'select-location/*')) {
                        return redirect()->route('tenant.select-location')
                            ->with('error', 'Please select a location first.');
                    }
                }
            }
        }

        return $next($request);
    }
}
