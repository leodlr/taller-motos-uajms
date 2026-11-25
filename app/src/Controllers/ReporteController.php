<?php
declare(strict_types=1);

final class ReporteController extends Controller
{
    /** Reporte 1: historial de reparaciones e ingresos. */
    public function historial(): void
    {
        Auth::requireRole('ADMIN');
        $errors = [];
        $desde = $this->validDate((string) ($_GET['desde'] ?? ''), 'desde', $errors);
        $hasta = $this->validDate((string) ($_GET['hasta'] ?? ''), 'hasta', $errors);
        if ($desde && $hasta && $desde > $hasta) {
            $errors['hasta'] = 'La fecha final no puede ser anterior a la inicial.';
        }
        $placa = strtoupper(trim((string) ($_GET['placa'] ?? '')));
        $cliente = trim((string) ($_GET['cliente'] ?? ''));

        $items = $errors ? [] : Reporte::historial($desde, $hasta, $placa, $cliente);
        $ingresos = 0.0;
        $facturado = 0.0;
        foreach ($items as $it) {
            $facturado += (float) $it['total'];
            if ($it['estado'] === 'Entregada') {
                $ingresos += (float) $it['total'];
            }
        }
        $this->render('reportes/historial', [
            'title' => 'Historial e ingresos', 'items' => $items, 'errors' => $errors,
            'desde' => $desde ?? '', 'hasta' => $hasta ?? '', 'placa' => $placa, 'cliente' => $cliente,
            'ingresos' => $ingresos, 'facturado' => $facturado,
        ]);
    }

    /** Reporte 2: repuestos con stock crítico. */
    public function stock(): void
    {
        Auth::requireRole('ADMIN');
        $this->render('reportes/stock', ['title' => 'Stock crítico', 'items' => Reporte::stockCritico()]);
    }

    private function validDate(string $v, string $key, array &$errors): ?string
    {
        if ($v === '') {
            return null;
        }
        $d = DateTime::createFromFormat('Y-m-d', $v);
        if (!$d || $d->format('Y-m-d') !== $v) {
            $errors[$key] = 'Fecha inválida.';
            return null;
        }
        return $v;
    }
}
