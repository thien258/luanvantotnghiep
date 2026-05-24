<?php

namespace App\Http\Controllers\admin;
use App\Models\Festival;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class FestivalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __construct()
    {
      $this->middleware('auth');
      $fetivals= Festival::orderBy( 'id','desc')->get();
      view::share('festivals',$fetivals);
    }
    public function index()
    {
        //
        $festivals= Festival::all();
        return view('admin.festival.festival-list',compact('festivals'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        
        return view('admin.festival.add');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $festival = Festival::create([
            'name' => $request->name,
            'discount' => $request->discount,
            'status' => $request->status,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);
        if ($festival)          
               return redirect()->route('admin.festival.index');
        else {
            return back();        
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
        $festival = Festival::find($id);
        return view('admin.festival.edit', compact('festival'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $festival = Festival::find($id);
        $festival->update([
            'name' => $request->name,
            'discount' => $request->discount,
            'status' => $request->status,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);
        if ($festival)
            return redirect()->route('admin.festival.index');
        else {
            return back();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //  
        $festival = Festival::find($id);
        $festival->delete();
       if ($festival)            return redirect()->route('admin.festival.index');
        else {
            return back();        
        }
    }
}
