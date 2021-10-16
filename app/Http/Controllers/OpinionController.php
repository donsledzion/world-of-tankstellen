<?php

namespace App\Http\Controllers;

use App\Http\Requests\OpinionRequest;
use App\Models\Opinion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OpinionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  OpinionRequest  $request
     * @return JsonResponse
     */
    public function store(OpinionRequest $request)
    {

        try {
            //error_log("Request: ".$request->validated());
            Opinion::create([
                'user_id' => $request->user_id,
                'station_id' => $request->station_id,
                'rate' => $request->rate,
                'comment' => $request->comment,
            ]);
            return response()->json([
                'status' => 'success',
                'message' => 'Dodano opinię',
            ])->setStatusCode(200);
        } catch (\Exception $e) {
            error_log("An exception caught! ".$e->getMessage());
            return response()->json([
                'status' => 'fail',
                'message' => 'Błąd!'.$e->getMessage(),
            ])->setStatusCode(500);
        }
    }


    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Opinion  $opinion
     * @return JsonResponse
     */
    public function show(Opinion $opinion):JsonResponse
    {
        try {
            return response()->json([
                'status' => 'success',
                'message' => 'Znaleziono opinię',
                'opinion' => $opinion,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Nie znaleziono opinii o tej stacji',
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return JsonResponse
     */
    public function showByStation(int $id):JsonResponse
    {
        $opinions = Opinion::where('station_id',$id)->with('user')->get();
        if($opinions->count()>0){
            return response()->json([
                'status' => 'success',
                'message' => 'Znaleziono '.$opinions->count().' opinii:',
                'opinions' => $opinions,
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Nie znaleziono opinii o tej stacji',
                'opinions' => $opinions,
            ]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Opinion  $opinion
     * @return \Illuminate\Http\Response
     */
    public function edit(Opinion $opinion)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Opinion  $opinion
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Opinion $opinion)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Opinion  $opinion
     * @return \Illuminate\Http\Response
     */
    public function destroy(Opinion $opinion)
    {
        //
    }
}
