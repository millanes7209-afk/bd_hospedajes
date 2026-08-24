<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tenant;
use Illuminate\Support\Facades\File;

class ConfiguracionController extends Controller
{
    /**
     * Muestra la vista de configuración del perfil de la empresa (logo, nombre, colores, eslogan)
     */
    public function index()
    {
        $tenant = app()->bound('tenant') ? app('tenant') : null;

        if (!$tenant) {
            $tenant = (object) [
                'subdominio' => 'ricopollo',
                'nombre' => env('APP_NAME', 'RICO POLLO'),
                'eslogan' => 'Sabor que cruje, pasión que deleita',
                'primary_color' => '#FFE66D',
                'accent_color' => '#E23E1A',
                'logo' => 'assets/ricopollo.svg',
            ];
        }

        return view('admin.configuracion.index', compact('tenant'));
    }

    /**
     * Procesa los cambios de configuración y la subida de imagen de logo
     */
    public function update(Request $request)
    {
        $tenant = app()->bound('tenant') ? app('tenant') : null;

        $request->validate([
            'nombre' => 'required|string|max:150',
            'eslogan' => 'nullable|string|max:255',
            'primary_color' => 'nullable|string|max:20',
            'accent_color' => 'nullable|string|max:20',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,svg,webp|max:2048',
        ]);

        $subdominio = $tenant ? $tenant->subdominio : 'ricopollo';

        $updateData = [
            'nombre' => $request->nombre,
            'eslogan' => $request->eslogan,
            'primary_color' => $request->primary_color ?? '#FFE66D',
            'accent_color' => $request->accent_color ?? '#E23E1A',
            'updated_at' => now(),
        ];

        // Subida y procesamiento de la imagen del Logo
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $extension = $file->getClientOriginalExtension();
            $filename = 'logo_' . $subdominio . '_' . time() . '.' . $extension;

            $destinationPath = public_path('uploads/tenants');
            if (!File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, 0755, true);
            }

            // Mover archivo subido
            $file->move($destinationPath, $filename);
            $updateData['logo'] = 'uploads/tenants/' . $filename;
        }

        try {
            if ($tenant && class_exists('\App\Models\Tenant')) {
                Tenant::where('subdominio', $subdominio)->update($updateData);
            }
        } catch (\Throwable $e) {
            // Ignorar silenciosamente si no se usa la BD saas_control en la instancia independiente
        }

        return back()->with('success', 'CONFIGURACIÓN DE EMPRESA Y LOGO ACTUALIZADOS CORRECTAMENTE');
    }
}
