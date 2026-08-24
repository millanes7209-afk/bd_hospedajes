<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ConfiguracionController extends Controller
{
    /**
     * Muestra la vista de configuración del perfil de la empresa (logo, nombre, colores, eslogan)
     */
    public function index()
    {
        $tenant = null;
        try {
            if (app()->bound('tenant')) {
                $tenant = app('tenant');
            }
        } catch (\Throwable $e) {
            $tenant = null;
        }

        if (!$tenant) {
            $tenant = (object) [
                'id' => 1,
                'subdominio' => 'ricopollo',
                'nombre' => env('APP_NAME', 'RICO POLLO'),
                'eslogan' => 'Sabor que cruje, pasión que deleita',
                'primary_color' => '#FFE66D',
                'accent_color' => '#E23E1A',
                'logo' => 'assets/ricopollo.svg',
            ];

            try {
                if (Schema::hasTable('tenants')) {
                    $dbTenant = DB::table('tenants')->where('subdominio', 'ricopollo')->first();
                    if ($dbTenant) {
                        $tenant = $dbTenant;
                    }
                }
            } catch (\Throwable $e) {
            }
        }

        return view('admin.configuracion.index', compact('tenant'));
    }

    /**
     * Procesa los cambios de configuración y la subida de imagen de logo
     */
    public function update(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:150',
            'eslogan' => 'nullable|string|max:255',
            'primary_color' => 'nullable|string|max:20',
            'accent_color' => 'nullable|string|max:20',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,svg,webp|max:2048',
        ]);

        $tenant = null;
        try {
            if (app()->bound('tenant')) {
                $tenant = app('tenant');
            }
        } catch (\Throwable $e) {
        }

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

            $file->move($destinationPath, $filename);
            $updateData['logo'] = 'uploads/tenants/' . $filename;
        }

        try {
            if (Schema::hasTable('tenants')) {
                DB::table('tenants')->where('subdominio', $subdominio)->update($updateData);
            }
        } catch (\Throwable $e) {
        }

        return back()->with('success', 'CONFIGURACIÓN DE EMPRESA Y LOGO ACTUALIZADOS CORRECTAMENTE');
    }
}
