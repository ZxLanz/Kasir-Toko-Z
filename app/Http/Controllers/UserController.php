<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\UserController;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        $users = User::orderBy('id')
            ->when($search, function ($q, $search) {
                return $q->where('nama','like', "%{$search}%")
                        ->orWhere('username','like', "{$search}%");
            })
            ->paginate();
            
            if($search) {
             $users->appends(['search'=> $search]);
        }

            return view('user.index', [
                'users' => $users // Pastikan variabel ini dikirim
            ]);
        }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('user.create');
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama'=>['required','max:100'],
            'username'=>['required','max:100','unique:users'],
            'role'=>['required','in:admin,petugas'],
            'password'=>['required','max:100','confirmed']
        ]);
        $request->merge([
            'password'=>bcrypt($request->password)
        ]);
        
        User::create($request->all());

        return redirect()->route('user.index')->with('store','success');
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        abort(404);
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        return view('user.edit',[
            'user'=>$user
        ]);
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'nama'=>['required','max:100'],
            'username'=>['required','max:100','unique:users,username,'.$user->id],
            'role'=>['required','in:admin,petugas'],
            'password_baru'=>['nullable','max:100','confirmed']
        ]);

        if($request->password_baru){
            $request->merge([
                'password'=>bcrypt($request->password_baru)
            ]);
            $user->update($request->all());
        } else {
            $user->update($request->only('nama','username','role'));
        }
        return redirect()->route('user.index')->with('update','success');
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('user.index')->with('success','User deleted successfully.');
        
        return back()->with('destroy','success');
        //
    }
}
