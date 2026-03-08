<?php

namespace App\Http\Controllers;

use App\Models\Contacto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ContactoController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|min:2|max:50|regex:/^[A-Za-zÁáÉéÍíÓóÚúÑñ\s]+$/',
            'apellido' => 'required|string|min:2|max:50|regex:/^[A-Za-zÁáÉéÍíÓóÚúÑñ\s]+$/',
            'email' => 'required|email|max:100',
            'mensaje' => 'required|string|min:10|max:500',
        ], [
            'nombre.regex' => 'El nombre debe contener solo letras y espacios',
            'apellido.regex' => 'El apellido debe contener solo letras y espacios',
            'mensaje.min' => 'El mensaje debe tener al menos 10 caracteres',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $validator->errors()->all()
            ], 400)->header('Content-Type', 'application/json');
        }

        try {
            $contacto = Contacto::create([
                'nombre' => $request->nombre,
                'apellido' => $request->apellido,
                'email' => $request->email,
                'mensaje' => $request->mensaje,
                'fecha_creacion' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => '¡Mensaje enviado correctamente! Gracias por contactarnos.'
            ])->header('Content-Type', 'application/json');
        } catch (\Exception $e) {
            \Log::error('Error al guardar contacto: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar el mensaje. Por favor, inténtalo de nuevo.'
            ], 500)->header('Content-Type', 'application/json');
        }
    }
}
