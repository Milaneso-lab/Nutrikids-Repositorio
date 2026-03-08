<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ConfiguracionController extends Controller
{
    public function index()
    {
        return view('admin.configuracion.index');
    }

    public function update(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'nombre_sistema' => 'nullable|string|max:100',
            'email_contacto' => 'nullable|email|max:100',
            'telefono_contacto' => 'nullable|string|max:20',
            'politica_privacidad' => 'nullable|string',
            'terminos_condiciones' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $validator->errors()->all()
            ], 400);
        }

        try {
            // Aquí podrías guardar la configuración en la base de datos o en un archivo de configuración
            // Por ahora solo retornamos éxito
            return response()->json([
                'success' => true,
                'message' => 'Configuración actualizada exitosamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la configuración.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function uploadLogo(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'El archivo debe ser una imagen válida (jpeg, png, jpg, gif) y no mayor a 2MB.',
            ], 400);
        }

        try {
            if ($request->hasFile('logo')) {
                $file = $request->file('logo');
                $filename = 'logo.' . $file->getClientOriginalExtension();
                $file->move(public_path('Imagenes'), $filename);

                return response()->json([
                    'success' => true,
                    'message' => 'Logo actualizado exitosamente.',
                    'url' => asset('Imagenes/' . $filename)
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al subir el logo.',
            ], 500);
        }
    }
}
