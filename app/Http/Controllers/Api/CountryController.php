<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class CountryController extends Controller
{
    public function index()
    {
        return response()->json(Country::orderBy('name')->get());
    }

    public function showWithRegionsAndCities($id)
    {
        $country = Country::with([
            'regions.cities'
        ])->findOrFail($id);

        return response()->json($country);
    }
}
