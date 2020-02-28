<?php

namespace App\Http\Controllers\Admin;

use App\User;
use App\Branding;
use App\Cfaccount;
use App\Spaccount;

use Silber\Bouncer\Database\Ability;
use Silber\Bouncer\Database\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUsersRequest;
use App\Http\Requests\Admin\UpdateUsersRequest;


class UsersController extends Controller
{
    /**
     * Display a listing of User.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! Gate::allows('users_manage')) {
            return abort(401);
        }

        
        
        $users = User::whereIs('organization')->where('owner',auth()->user()->id)->with('roles')->get();
        
       
        
        return view('admin.users.index', compact('users'));
    }

    public function listResellers()
    {
        if (! Gate::allows('users_manage')) {
            return abort(401);
        }

        

        $users = User::whereIs('reseller')->with('roles')->get();
        
     
        
        return view('admin.users.resellers', compact('users'));
    }

    /**
     * Show the form for creating new User.
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

        return view('admin.users.create', compact('roles','abilities'));
    }

     public function createReseller()
    {
        if (! Gate::allows('users_manage')) {
            return abort(401);
        }
        $roles = Role::get()->pluck('name', 'name');

        $cfAccounts = User::findOrFail(auth()->user()->id)->cfaccount;

        $spAccounts = User::findOrFail(auth()->user()->id)->spaccount;

        $abilities = Role::where("name","res")->first()->getAbilities();



        return view('admin.users.createReseller', compact('roles','abilities','spAccounts','cfAccounts'));
    }

    /**
     * Store a newly created User in storage.
     *
     * @param  \App\Http\Requests\StoreUsersRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreUsersRequest $request)
    {
        if (! Gate::allows('users_manage')) {
            return abort(401);
        }
        $user = User::create($request->all());

        
       $user->owner=auth()->user()->id;

       $user->save();


        foreach ($request->input('abilities') as $ability) {
             
             $user->allow($ability);
        }
        

        // foreach ($request->input('roles') as $role) {
             $user->assign('organization');
        // }

        return redirect()->route('admin.users.index');
    }
 
 public function storeReseller(StoreUsersRequest $request)
    {
        if (! Gate::allows('resellers_manage')) {
            return abort(401);
        }


         
       
        $user = User::create($request->except('cfaccount','spaccount','cf','sp'));

        



        foreach ($request->input('abilities') as $ability) {
             
             $user->allow($ability);
        }


        $cfaccount=cfaccount::find($request->input('cfaccount'));
        $spaccount=spaccount::find($request->input('spaccount'));

         $user->allow('access',$cfaccount);
         $user->allow('access',$spaccount);


        // foreach ($request->input('roles') as $role) {
             $user->assign('reseller');
        // }

             $url = strtolower(str_random(5))."_".str_slug($user->name).".panel.blockdos.net";
             Branding::create([
                'name' => $user->name,
                'url'  => $url,
                'email' => $user->email,
                'temp_url' => '',
                'logo' => '',
                'user_id' => $user->id,
                'cf' => $request->input('cfAllowed'),
                'sp' => $request->input('spAllowed'),

             ]);

        return redirect()->route('admin.listResellers');
    }

    /**
     * Show the form for editing User.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (! Gate::allows('users_manage')) {
            return abort(401);
        }

        $user = User::findOrFail($id);

        if ( $user->owner!= auth()->user()->id) {
            return abort(401);
        }

        $roles = Role::get()->pluck('name', 'name');

        //dd();
        // $abilities = Ability::get()->pluck('name', 'name');
        // 
         $abilities = Role::where("name","org")->first()->getAbilities();

        // foreach ($abilities as $ability) {
        //     # code...
        //     # 
        //     echo($ability->name);
        // }
        // die();


       

        return view('admin.users.edit', compact('user', 'roles','abilities'));
    }

    /**
     * Update User in storage.
     *
     * @param  \App\Http\Requests\UpdateUsersRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateUsersRequest $request, $id)
    {
        if (! Gate::allows('users_manage')) {
            return abort(401);
        }
        $user = User::findOrFail($id);
        if ( $user->owner!= auth()->user()->id) {
            return abort(401);
        }
        
        $user->update($request->all());

        if(isset($request->password))
        {
             //dd($request->password);
            $user->password_updated = 1; 
             $user->save();   
        }
        
       
        // dd($user->can("reseller_access"));
       foreach ($user->getAbilities() as $ability) {
            $user->forbid($ability->name);
        }

        //dd($request->input('abilities'));
        //

        if($request->input('abilities')!=null)
        {
            foreach ($request->input('abilities') as $ability) {
                 $user->unforbid($ability);
                 $user->allow($ability);
            }
             \Bouncer::refreshFor($user);
        }

        


        return redirect()->route('admin.users.index');
    }

    /**
     * Remove User from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (! Gate::allows('users_manage')) {
            return abort(401);
        }


        $user = User::findOrFail($id);
         if ( $user->owner!= auth()->user()->id) {
            return abort(401);
        }

        if($user->zone->count()>0)
        {
            dd("user has domains");
        }
        else
        {
            // dd("user does not have domains");
            $user->delete();    
        }
        

        return redirect()->route('admin.users.index');
    }

    /**
     * Delete all selected User at once.
     *
     * @param Request $request
     */
    public function massDestroy(Request $request)
    {
        if (! Gate::allows('users_manage')) {
            return abort(401);
        }

        if ($request->input('ids')) {
            $entries = User::whereIn('id', $request->input('ids'))->get();

            foreach ($entries as $entry) {

                 if ( $entry->owner== auth()->user()->id) {
                     $entry->delete();
                }

               
            }
        }
    }


    public function listZones(User $user)
    {
        if ( $user->owner!= auth()->user()->id) {
            return abort(401);
        }
        
        $zones=$user->zone;
        return view('admin.users.zones', compact('zones','user'));

        
    }



}
