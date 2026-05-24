<?php

namespace App\Http\Controllers\admin;
use App\Models\Concentration;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class ConcentrationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
       public function __construct()
    {
      $this->middleware('auth');
        $concentrations= Concentration::orderBy( 'id','desc')->get();
        View::share('concentrations',$concentrations);
    }
    public function index()
    {
        //
        $concentrations=Concentration::all();
        return view('admin.concentration.concentration-list',compact('concentrations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return view('admin.concentration.add');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $concentration = Concentration::create([
            'concentration' => $request->concentration,
            'status' => $request->status,
        ]);
        if ($concentration)            
             return redirect()->route('admin.concentration.index');
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
        $concentration = Concentration::find($id);
        return view('admin.concentration.edit', compact('concentration'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $concentration = Concentration::find($id);
        $concentration->update([
            'concentration' => $request->concentration,
            'status' => $request->status,
        ]);
        if ($concentration)
            return redirect()->route('admin.concentration.index');
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
        $concentration = Concentration::find($id);
        $concentration->delete();
       if ($concentration)
            return redirect()->route('admin.concentration.index');
        else {
            return back();
        }
    }
}
