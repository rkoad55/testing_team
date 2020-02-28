<?php

namespace App\Http\Controllers\Admin;

use App\Package;
use App\User;
use App\Branding;
use App\Cfaccount;
use App\Spaccount;

use Silber\Bouncer\Database\Ability;
use Silber\Bouncer\Database\Role;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;

class PackageController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {



        // $json='{"data":[{"id":1,"name":"Testy","description":"123","price":null,"base":"sp","owner":1,"created_at":"2018-09-12 14:49:48","updated_at":"2018-09-12 14:49:48"}],"status":200}';

        // $js=json_decode($json);
        // $packages=[];
        // foreach ($js->data as $pkg) {
        //     # code...

        //     $packages[]=[$pkg->id => $pkg->name];
            
        // }

        // dd($packages);
        // die();
        if (! Gate::allows('users_manage')) {
            return abort(401);
        }
        $packageRecord = Package::all();

        return view('admin.packages.index',compact('packageRecord'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        if (! Gate::allows('users_manage')) {
            return abort(401);
        }
        $roles = Role::get()->pluck('name', 'name');

        $abilities = Role::where("name","org")->first()->getAbilities();

        return view('admin.packages.create', compact('roles','abilities'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        // dd($request->input('abilities'));
         $input = $request->except('abilities');
        $package=Package::create($input);
        

        $package->owner=auth()->user()->id;

       $package->save();
        foreach ($request->input('abilities') as $ability) {
             // dd($ability);
             $package->allow($ability);
        }
        
        //$package->assign('organization');
        
        // $input->assign('organization');
        return redirect()->route('admin.packages.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Package  $package
     * @return \Illuminate\Http\Response
     */
    public function show(Package $package)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Package  $package
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
       if (! Gate::allows('users_manage')) {
            return abort(401);
        }

        $packageFind = Package::findOrFail($id);
        // dd($packageFind->abilities);
        return view('admin.packages.edit', compact('packageFind'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Package  $package
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,$id)
    {
        if (! Gate::allows('users_manage')) {
            return abort(401);
        }
        $result = package::find($id);
        $result->name= $request->get('name');
        $result->description = $request->get('description');
        $result->price = $request->get('price');
        $result->save();
        return redirect()->route('admin.packages.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Package  $package
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (! Gate::allows('users_manage')) {
            return abort(401);
        }
        $packageId = package::findOrFail($id);
        
            $packageId->delete();    
    

        return redirect()->route('admin.packages.index');
    }
}
