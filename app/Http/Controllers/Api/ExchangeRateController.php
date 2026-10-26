<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ExchangeRateResource;
use App\Models\ExchangeRate;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ExchangeRateController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return ExchangeRateResource::collection(ExchangeRate::all());
    }
}
