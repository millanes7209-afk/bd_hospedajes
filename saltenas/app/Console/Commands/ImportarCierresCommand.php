<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Carrito;
use App\Models\VarianteSaltena;
use App\Services\CierreValidationService;

class ImportarCierresCommand extends Command
{
    protected $signature = 'cierres:importar-dulces-suenos';
    protected $description = 'Importa cierres diarios masivos para el carrito Dulces Sueños';

    public function handle(CierreValidationService $cierreService)
    {
        $this->info('Iniciando importación de cierres para Dulces Sueños...');

        // Buscar carrito Dulces Sueños (o el primero activo si no existe por nombre exacto)
        $carrito = Carrito::where('nombre', 'LIKE', '%Dulces%')
            ->orWhere('nombre', 'LIKE', '%Sueños%')
            ->first() ?? Carrito::first();

        if (!$carrito) {
            $this->error('No existe ningún Carrito registrado en la base de datos.');
            return 1;
        }

        $this->info("Carrito seleccionado: ID {$carrito->id} - {$carrito->nombre}");

        // Buscar primera variante activa
        $variante = VarianteSaltena::where('activo', true)->first();
        if (!$variante) {
            $this->error('No existe ninguna Variante de Salteña registrada.');
            return 1;
        }

        $this->info("Variante seleccionada: ID {$variante->id} - {$variante->nombre} (Bs. {$variante->precio_venta})");

        // Datos del cuadro proporcionado por el usuario
        $registros = [
            ['fecha' => '2026-09-15', 'ventas' => 50, 'min' => 7, 'max' => 24],
            ['fecha' => '2026-09-16', 'ventas' => 40, 'min' => 7, 'max' => 22],
            ['fecha' => '2026-09-17', 'ventas' => 50, 'min' => 12, 'max' => 27],
            ['fecha' => '2026-09-18', 'ventas' => 58, 'min' => 11, 'max' => 27],
            ['fecha' => '2026-09-19', 'ventas' => 78, 'min' => 14, 'max' => 29],
            ['fecha' => '2026-09-20', 'ventas' => 47, 'min' => 14, 'max' => 32],
            ['fecha' => '2026-09-22', 'ventas' => 59, 'min' => 6, 'max' => 26],
        ];

        $insertados = 0;
        foreach ($registros as $reg) {
            $ventas = $reg['ventas'];
            $montoReal = $ventas * $variante->precio_venta;

            $data = [
                'carrito_id' => $carrito->id,
                'fecha' => $reg['fecha'],
                'temp_min' => $reg['min'],
                'temp_max' => $reg['max'],
                'monto_real' => $montoReal,
                'detalles' => [
                    [
                        'variante_id' => $variante->id,
                        'cantidad_entregada' => $ventas,
                        'cantidad_vendida_normal' => $ventas,
                        'cantidad_sobrante' => 0,
                    ]
                ],
                'observaciones' => 'Importación masiva automática',
            ];

            $cierreService->guardarCierre($data);
            $insertados++;
            $this->info("✓ Registrado fecha {$reg['fecha']}: {$ventas} unidades (Bs. {$montoReal}), Temp: {$reg['min']}°C - {$reg['max']}°C");
        }

        $this->info("¡Importación completada con éxito! {$insertados} registros procesados.");
        return 0;
    }
}
