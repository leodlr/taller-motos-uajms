<?php
declare(strict_types=1);

final class OrdenController extends Controller
{
    public function index(): void
    {
        Auth::requireRole('ADMIN', 'MECANICO');
        $user = Auth::user();
        $estado = (string) ($_GET['estado'] ?? '');
        if (!in_array($estado, Orden::ESTADOS, true)) {
            $estado = '';
        }
        $q = trim((string) ($_GET['q'] ?? ''));
        $items = Orden::all($estado, $user['rol'] === 'MECANICO' ? $user['id'] : null, $q);
        $this->render('ordenes/index', [
            'title' => $user['rol'] === 'MECANICO' ? 'Mis órdenes' : 'Órdenes de trabajo',
            'items' => $items, 'estado' => $estado, 'q' => $q,
        ]);
    }

    public function form(): void
    {
        Auth::requireRole('ADMIN');
        $id = (int) ($_GET['id'] ?? 0);
        $item = ['id_moto' => (int) ($_GET['id_moto'] ?? 0), 'id_mecanico' => 0,
                 'falla_reportada' => '', 'costo_mano_obra' => '0.00'];
        if ($id > 0 && !($item = Orden::find($id))) {
            $this->notFound();
        }
        $this->renderForm($item, [], $id);
    }

    public function save(): void
    {
        Auth::requireRole('ADMIN');
        $this->requirePost();
        $id = (int) ($_POST['id'] ?? 0);
        $d = [
            'id_moto' => (int) ($_POST['id_moto'] ?? 0),
            'id_mecanico' => (int) ($_POST['id_mecanico'] ?? 0),
            'falla_reportada' => $this->post('falla_reportada'),
            'costo_mano_obra' => str_replace(',', '.', $this->post('costo_mano_obra')),
        ];
        $errors = [];
        if ($d['id_moto'] <= 0 || !Moto::find($d['id_moto'])) {
            $errors['id_moto'] = 'Seleccione una moto válida.';
        }
        if (!Usuario::mecanicoExists($d['id_mecanico'])) {
            $errors['id_mecanico'] = 'Seleccione un mecánico activo.';
        }
        $len = mb_strlen($d['falla_reportada']);
        if ($len < 5 || $len > 1000) {
            $errors['falla_reportada'] = 'Describa la falla (5 a 1000 caracteres).';
        }
        if (!preg_match('/^\d{1,8}(\.\d{1,2})?$/', $d['costo_mano_obra'])) {
            $errors['costo_mano_obra'] = 'Costo inválido (número >= 0, máx. 2 decimales).';
        }
        if ($id > 0 && !Orden::find($id)) {
            $this->notFound();
        }
        if ($errors) {
            $this->renderForm($d, $errors, $id);
            return;
        }
        $d['costo_mano_obra'] = number_format((float) $d['costo_mano_obra'], 2, '.', '');
        if ($id > 0) {
            Orden::updateHeader($id, $d);
            $this->flash('success', 'Orden actualizada.');
            $this->redirect('ordenes.ver&id=' . $id);
        }
        $codigo = Orden::create($d);
        $this->flash('success', "Orden $codigo creada.");
        $this->redirect('ordenes');
    }

    public function ver(): void
    {
        Auth::requireRole('ADMIN', 'MECANICO');
        $orden = $this->load((int) ($_GET['id'] ?? 0));
        $this->render('ordenes/ver', [
            'title' => 'Orden ' . $orden['codigo_correlativo'],
            'orden' => $orden,
            'detalle' => Orden::detalle((int) $orden['id_orden']),
            'repuestos' => array_filter(Repuesto::all(), fn ($r) => (int) $r['stock_actual'] > 0),
        ]);
    }

    /** ADMIN y MECANICO (asignado): estado y diagnóstico. */
    public function update(): void
    {
        Auth::requireRole('ADMIN', 'MECANICO');
        $this->requirePost();
        $orden = $this->load((int) ($_POST['id'] ?? 0));
        try {
            Orden::updateEstado((int) $orden['id_orden'], $this->post('estado'), $this->post('diagnostico'));
            $this->flash('success', 'Estado y diagnóstico actualizados.');
        } catch (DomainException $e) {
            $this->flash('danger', $e->getMessage());
        }
        $this->redirect('ordenes.ver&id=' . (int) $orden['id_orden']);
    }

    /** ADMIN y MECANICO (asignado): agregar repuesto (transacción con descuento de stock). */
    public function addrep(): void
    {
        Auth::requireRole('ADMIN', 'MECANICO');
        $this->requirePost();
        $orden = $this->load((int) ($_POST['id'] ?? 0));
        $cant = $this->post('cantidad');
        try {
            if (!ctype_digit($cant) || strlen($cant) > 9) {
                throw new DomainException('La cantidad debe ser un entero mayor a 0.');
            }
            Orden::addRepuesto((int) $orden['id_orden'], (int) ($_POST['id_repuesto'] ?? 0), (int) $cant);
            $this->flash('success', 'Repuesto agregado y stock descontado.');
        } catch (DomainException $e) {
            $this->flash('danger', $e->getMessage());
        }
        $this->redirect('ordenes.ver&id=' . (int) $orden['id_orden']);
    }

    public function delete(): void
    {
        Auth::requireRole('ADMIN');
        $this->requirePost();
        try {
            Orden::delete((int) ($_POST['id'] ?? 0));
            $this->flash('success', 'Orden eliminada.');
        } catch (DomainException $e) {
            $this->flash('danger', $e->getMessage());
        }
        $this->redirect('ordenes');
    }

    /** Carga la orden; un MECANICO solo accede a las asignadas a él. */
    private function load(int $id): array
    {
        $orden = $id > 0 ? Orden::find($id) : null;
        if (!$orden) {
            $this->notFound();
        }
        $user = Auth::user();
        if ($user['rol'] === 'MECANICO' && (int) $orden['id_mecanico'] !== $user['id']) {
            http_response_code(403);
            exit('403 - Esta orden no está asignada a usted.');
        }
        return $orden;
    }

    private function renderForm(array $item, array $errors, int $id): void
    {
        $this->render('ordenes/form', [
            'title' => $id ? 'Editar orden' : 'Nueva orden', 'item' => $item, 'errors' => $errors, 'id' => $id,
            'motos' => Moto::all(), 'mecanicos' => Usuario::mecanicos(),
        ]);
    }
}
