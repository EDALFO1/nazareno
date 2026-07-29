<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\Common\Entity\Sheet;
use OpenSpout\Writer\XLSX\Writer;

/**
 * Genera el archivo Excel (.xlsx) del Reporte financiero: mismos datos que se
 * ven en pantalla (App\Filament\Pages\ReporteFinanciero::reporte()), en tres
 * hojas — Resumen, Ingresos, Egresos.
 */
class ReporteFinancieroExportService
{
    private Style $estiloTitulo;

    private Style $estiloSeccion;

    private Style $estiloEncabezadoTabla;

    private Style $estiloMoneda;

    public function __construct()
    {
        $this->estiloTitulo = (new Style())->setFontBold()->setFontSize(14);
        $this->estiloSeccion = (new Style())->setFontBold()->setFontSize(11);
        $this->estiloEncabezadoTabla = (new Style())->setFontBold()->setBackgroundColor('EEEEEE');
        $this->estiloMoneda = (new Style())->setFormat('#,##0');
    }

    /**
     * @param  array{
     *     saldoActual: float, totalIngresos: float, totalEgresos: float,
     *     ingresosPorCategoria: Collection, egresosPorCategoria: Collection,
     *     movimientosIngreso: Collection, movimientosEgreso: Collection,
     *     totalPorCobrar: float, totalPorPagar: float,
     * }  $reporte
     * @return string Ruta del archivo temporal generado.
     */
    public function generar(array $reporte, ?string $desde, ?string $hasta): string
    {
        $writer = new Writer();
        $rutaTemporal = tempnam(sys_get_temp_dir(), 'reporte_financiero').'.xlsx';
        $writer->openToFile($rutaTemporal);

        $writer->getCurrentSheet()->setName('Resumen');
        $writer->getCurrentSheet()->setColumnWidth(34, 1);
        $writer->getCurrentSheet()->setColumnWidth(16, 2);
        $writer->getCurrentSheet()->setColumnWidth(14, 3);
        $this->escribirResumen($writer, $reporte, $desde, $hasta);

        $hojaIngresos = $writer->addNewSheetAndMakeItCurrent();
        $hojaIngresos->setName('Ingresos');
        $this->anchoMovimientos($hojaIngresos, conPersona: true);
        $this->escribirMovimientos($writer, $reporte['movimientosIngreso'], conPersona: true);

        $hojaEgresos = $writer->addNewSheetAndMakeItCurrent();
        $hojaEgresos->setName('Egresos');
        $this->anchoMovimientos($hojaEgresos, conPersona: false);
        $this->escribirMovimientos($writer, $reporte['movimientosEgreso'], conPersona: false);

        $writer->close();

        return $rutaTemporal;
    }

    private function anchoMovimientos(Sheet $hoja, bool $conPersona): void
    {
        $hoja->setColumnWidth(12, 1); // Fecha
        $hoja->setColumnWidth(22, 2); // Categoría
        $hoja->setColumnWidth($conPersona ? 28 : 32, 3); // Persona / Concepto
        $hoja->setColumnWidth(14, 4); // Método
        $hoja->setColumnWidth(14, 5); // Monto
    }

    private function escribirResumen(Writer $writer, array $reporte, ?string $desde, ?string $hasta): void
    {
        $formatearFecha = fn (?string $fecha) => $fecha ? Carbon::parse($fecha)->format('d/m/Y') : 'sin definir';

        $writer->addRow(Row::fromValues(['Reporte financiero'], $this->estiloTitulo));
        $writer->addRow(Row::fromValues(['Periodo', $formatearFecha($desde).' a '.$formatearFecha($hasta)]));
        $writer->addRow(Row::fromValues([]));

        $writer->addRow(Row::fromValues(['Saldo actual (caja/bancos)', $reporte['saldoActual']], $this->estiloMoneda));
        $writer->addRow(Row::fromValues(['Por cobrar', $reporte['totalPorCobrar']], $this->estiloMoneda));
        $writer->addRow(Row::fromValues(['Por pagar', $reporte['totalPorPagar']], $this->estiloMoneda));
        $writer->addRow(Row::fromValues(['Ingresos del periodo', $reporte['totalIngresos']], $this->estiloMoneda));
        $writer->addRow(Row::fromValues(['Egresos del periodo', $reporte['totalEgresos']], $this->estiloMoneda));
        $writer->addRow(Row::fromValues(['Balance del periodo', $reporte['totalIngresos'] - $reporte['totalEgresos']], $this->estiloMoneda));
        $writer->addRow(Row::fromValues([]));

        $writer->addRow(Row::fromValues(['Ingresos por categoría'], $this->estiloSeccion));
        $writer->addRow(Row::fromValues(['Categoría', 'Movimientos', 'Total'], $this->estiloEncabezadoTabla));
        foreach ($reporte['ingresosPorCategoria'] as $fila) {
            $writer->addRow(Row::fromValues([$fila['categoria'], $fila['cantidad'], $fila['total']], $this->estiloMoneda));
        }
        $writer->addRow(Row::fromValues([]));

        $writer->addRow(Row::fromValues(['Egresos por categoría'], $this->estiloSeccion));
        $writer->addRow(Row::fromValues(['Categoría', 'Movimientos', 'Total'], $this->estiloEncabezadoTabla));
        foreach ($reporte['egresosPorCategoria'] as $fila) {
            $writer->addRow(Row::fromValues([$fila['categoria'], $fila['cantidad'], $fila['total']], $this->estiloMoneda));
        }
    }

    private function escribirMovimientos(Writer $writer, Collection $movimientos, bool $conPersona): void
    {
        $writer->addRow(Row::fromValues(
            $conPersona
                ? ['Fecha', 'Categoría', 'Persona', 'Método', 'Monto']
                : ['Fecha', 'Categoría', 'Concepto', 'Método', 'Monto'],
            $this->estiloEncabezadoTabla,
        ));

        foreach ($movimientos as $movimiento) {
            $writer->addRow(Row::fromValues([
                $movimiento->fecha->format('d/m/Y'),
                $movimiento->categoriaContable?->nombre ?? '—',
                $conPersona
                    ? ($movimiento->persona?->nombre_completo ?? '—')
                    : ($movimiento->descripcion ?? '—'),
                ucfirst($movimiento->metodo_pago),
                (float) $movimiento->monto,
            ], $this->estiloMoneda));
        }
    }
}
