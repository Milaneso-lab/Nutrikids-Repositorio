<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\RespuestasCrud;
use App\Http\Controllers\Controller;
use App\Models\Configuracion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ConfiguracionController extends Controller
{
    use RespuestasCrud;

    public function index()
    {
        $configuracion = Configuracion::todas();

        return view('admin.configuracion.index', compact('configuracion'));
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre_sistema' => 'nullable|string|max:100',
            'email_contacto' => 'nullable|email|max:100',
            'telefono_contacto' => 'nullable|string|max:20',
            'politica_privacidad' => 'nullable|string',
            'terminos_condiciones' => 'nullable|string',
        ], [
            'email_contacto.email' => 'El email de contacto no tiene un formato válido.',
            'telefono_contacto.max' => 'El teléfono no puede superar los 20 caracteres.',
        ]);

        if ($validator->fails()) {
            return $this->respuestaValidacion($request, $validator);
        }

        try {
            Configuracion::guardarVarias($validator->validated());

            return $this->respuestaExito(
                $request,
                'Configuración guardada correctamente.',
                'admin.configuracion.index',
                ['configuracion' => Configuracion::todas()]
            );
        } catch (\Throwable $e) {
            return $this->respuestaExcepcion($request, $e, 'guardar configuración', 'admin.configuracion.index');
        }
    }

    public function uploadLogo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'logo.required' => 'Selecciona una imagen antes de subirla.',
            'logo.image' => 'El archivo debe ser una imagen.',
            'logo.mimes' => 'Formatos permitidos: JPG, PNG, GIF.',
            'logo.max' => 'La imagen no puede superar los 2 MB.',
        ]);

        if ($validator->fails()) {
            return $this->respuestaValidacion($request, $validator);
        }

        try {
            $file = $request->file('logo');
            $filename = 'logo.'.$file->getClientOriginalExtension();
            $file->move(public_path('Imagenes'), $filename);

            return $this->respuestaExito(
                $request,
                'Logo actualizado correctamente.',
                'admin.configuracion.index',
                ['url' => asset('Imagenes/'.$filename)]
            );
        } catch (\Throwable $e) {
            return $this->respuestaExcepcion($request, $e, 'subir logo', 'admin.configuracion.index');
        }
    }
}
