<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ConfiguracionController extends Controller
{
    /**
     * Obteine la configuración actual del tenant combinando valores por defecto, archivo de persistencia local y BD.
     */
    private function getTenantConfig()
    {
        $defaultConfig = [
            'id' => 1,
            'subdominio' => 'monaka',
            'nombre' => env('APP_NAME', 'Salteñería Monaka'),
            'eslogan' => 'Las salteñas más deliciosas de la ciudad',
            'primary_color' => '#FFE66D',
            'accent_color' => '#E23E1A',
            'logo' => 'assets/logo.svg',
        ];

        // 1. Cargar archivo local de persistencia si existe
        $jsonPath = storage_path('app/tenant_config.json');
        if (File::exists($jsonPath)) {
            $fileConfig = json_decode(File::get($jsonPath), true);
            if (is_array($fileConfig)) {
                foreach ($fileConfig as $k => $v) {
                    if ($v !== null && $v !== '') {
                        $defaultConfig[$k] = $v;
                    }
                }
            }
        }

        // 2. Intentar consultar base de datos si la tabla 'tenants' existe
        try {
            if (Schema::hasTable('tenants')) {
                $dbTenant = DB::table('tenants')->where('subdominio', 'monaka')->first();
                if ($dbTenant) {
                    foreach ((array) $dbTenant as $k => $v) {
                        if ($v !== null && $v !== '') {
                            $defaultConfig[$k] = $v;
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            // Ignorar error de conexión a BD y usar fallback
        }

        return (object) $defaultConfig;
    }

    /**
     * Muestra la vista de configuración del perfil de la empresa (logo, nombre, colores, eslogan)
     */
    public function index()
    {
        $tenant = $this->getTenantConfig();
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
            $filename = 'logo_monaka_' . time() . '.' . $extension;

            $destinationPath = public_path('uploads/tenants');
            if (!File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, 0755, true);
            }

            $file->move($destinationPath, $filename);
            $newLogoPath = 'uploads/tenants/' . $filename;
            $updateData['logo'] = $newLogoPath;

            // También guardar una copia en un nombre fijo para respaldo estático
            @copy(public_path($newLogoPath), public_path('uploads/tenants/logo_monaka_current.' . $extension));
        }

        // 1. Guardar siempre en archivo local JSON para persistencia garantizada
        try {
            $jsonPath = storage_path('app/tenant_config.json');
            $existingConfig = [];
            if (File::exists($jsonPath)) {
                $existingConfig = json_decode(File::get($jsonPath), true) ?: [];
            }
            $merged = array_merge($existingConfig, array_filter($updateData, function ($val) {
                return $val !== null;
            }));
            File::put($jsonPath, json_encode($merged, JSON_PRETTY_PRINT));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Error guardando tenant_config.json: ' . $e->getMessage());
        }

        // 2. Guardar en BD usando updateOrInsert para que si la fila no existe se cree automáticamente
        try {
            if (Schema::hasTable('tenants')) {
                DB::table('tenants')->updateOrInsert(['subdominio' => 'monaka'], $updateData);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('No se pudo actualizar la tabla tenants: ' . $e->getMessage());
        }

        return back()->with('success', 'CONFIGURACIÓN DE EMPRESA Y LOGO ACTUALIZADOS CORRECTAMENTE');
    }
}
