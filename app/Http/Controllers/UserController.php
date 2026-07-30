<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $titulo = 'Usuarios';
        $usuarios = User::with(['rol', 'persona'])->orderBy('name')->paginate(20);

        return view('modules.usuarios.index', compact('titulo', 'usuarios'));
    }

    public function create()
    {
        $titulo = 'Crear usuario';
        $roles = Rol::orderBy('nombre')->get();

        return view('modules.usuarios.create', compact('titulo', 'roles'));
    }

    public function store(Request $request)
    {
        $request->validate(User::rules(), [
            'email.unique' => 'Este correo ya está registrado.',
        ]);

        User::create([
            'rol_id' => $request->rol_id,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('usuarios.index')->with('success', 'Usuario creado correctamente');
    }

    public function edit(User $usuario)
    {
        $titulo = 'Editar usuario';
        $roles = Rol::orderBy('nombre')->get();

        return view('modules.usuarios.edit', compact('titulo', 'usuario', 'roles'));
    }

    public function update(Request $request, User $usuario)
    {
        $request->validate(User::rules($usuario->id), [
            'email.unique' => 'Este correo ya está registrado.',
        ]);

        $datos = [
            'rol_id' => $request->rol_id,
            'name' => $request->name,
            'email' => $request->email,
        ];

        if ($request->filled('password')) {
            $datos['password'] = Hash::make($request->password);
        }

        $usuario->update($datos);

        return redirect()->route('usuarios.index')->with('success', 'Usuario actualizado correctamente');
    }

    public function destroy(User $usuario)
    {
        if ($usuario->id === Auth::id()) {
            return back()->with('error', 'No puedes eliminar tu propio usuario.');
        }

        $usuario->delete();

        return redirect()->route('usuarios.index')->with('success', 'Usuario eliminado correctamente');
    }
}
