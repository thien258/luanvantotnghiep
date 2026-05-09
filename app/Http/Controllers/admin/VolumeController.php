<?php

namespace App\Http\Controllers\admin;
use App\Models\Volume;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class VolumeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
      public function __construct()
    {
      $this->middleware('auth');
        $volumes= Volume::orderBy( 'id','desc')->get();
        view()->share('volumes',$volumes);
    }

    public function index()
    {
        //
        $volumes=Volume::all();
        return view('admin.volume.volume-list',compact('volumes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return view('admin.volume.add');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $volume = Volume::create([
            'name' => $request->name,
            'status' => $request->status,
        ]);
        if ($volume) 
             return redirect()->route('admin.volume.index');
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
        $volume = Volume::find($id);
        return view('admin.volume.edit', compact('volume'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $volume = Volume::find($id);
        $volume->update([
            'name' => $request->name,
            'status' => $request->status,
        ]);
        if ($volume)
            return redirect()->route('admin.volume.index');
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
        $volume = Volume::find($id);
        $volume->delete();
       if ($volume)
            return redirect()->route('admin.volume.index');
        else {
            return back();
        }
    }
}
