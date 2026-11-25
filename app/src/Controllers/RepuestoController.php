<?php
declare(strict_types=1);

final class RepuestoController extends Controller
{
    /** ADMIN y MECANICO pueden consultar stock; solo ADMIN modifica. */
    public function index(): void
    {
        Auth::requireRole('ADMIN', 'MECANICO');
        $q = trim((string) ($_GET['q'] ?? ''));
        $this->render('repuestos/index', ['title' => 'Repuestos', 'items' => Repuesto::all($q), 'q' => $q]);
    }

    public function form(): void
    {
        Auth::requireRole('ADMIN');
        $id = (int) ($_GET['id'] ?? 0);
        $item = ['codigo' => '', 'nombre' => '', 'precio_unitario' => '', 'stock_actual' => '0', 'stock_minimo' => '5'];
        if ($id > 0 && !($item = Repuesto::find($id))) {
            $this->notFound();
        }
        $this->render('repuestos/form', ['title' => $id ? 'Editar repuesto' : 'Nuevo repuesto',
            'item' => $item, 'errors' => [], 'id' => $id]);
    }

    public function save(): void
    {
        Auth::requireRole('ADMIN');
        $this->requirePost();
        $id = (int) ($_POST['id'] ?? 0);
        $d = [
            'codigo' => strtoupper($this->post('codigo')),
            'nombre' => $this->post('nombre'),
            'precio_unitario' => str_replace(',', '.', $this->post('precio_unitario')),
            'stock_actual' => $this->post('stock_actual'),
            'stock_minimo' => $this->post('stock_minimo'),
        ];
        $errors = $this->validate($d, $id);

        if (!$errors) {
            $d['precio_unitario'] = number_format((float) $d['precio_unitario'], 2, '.', '');
            $d['stock_actual'] = (int) $d['stock_actual'];
            $d['stock_minimo'] = (int) $d['stock_minimo'];
            try {
                $id ? Repuesto::update($id, $d) : Repuesto::create($d);
                $this->flash('success', $id ? 'Repuesto actualizado.' : 'Repuesto registrado.');
                $this->redirect('repuestos');
            } catch (PDOException $ex) {
                if ($ex->getCode() !== '23000') {
                    throw $ex;
                }
                $errors['codigo'] = 'Ya existe un repuesto con ese código.';
            }
        }
        $this->render('repuestos/form', ['title' => $id ? 'Editar repuesto' : 'Nuevo repuesto',
            'item' => $d, 'errors' => $errors, 'id' => $id]);
    }

    public function delete(): void
    {
        Auth::requireRole('ADMIN');
        $this->requirePost();
        try {
            Repuesto::delete((int) ($_POST['id'] ?? 0));
            $this->flash('success', 'Repuesto eliminado.');
        } catch (PDOException $ex) {
            if ($ex->getCode() !== '23000') {
                throw $ex;
            }
            $this->flash('danger', 'No se puede eliminar: el repuesto fue usado en órdenes de trabajo.');
        }
        $this->redirect('repuestos');
    }

    private function validate(array $d, int $id): array
    {
        $e = [];
        if (!preg_match('/^[A-Z0-9_-]{2,30}$/', $d['codigo'])) {
            $e['codigo'] = 'Código inválido (2 a 30 caracteres: letras, números, guion).';
        } elseif (Repuesto::codigoExists($d['codigo'], $id)) {
            $e['codigo'] = 'Ya existe un repuesto con ese código.';
        }
        $len = mb_strlen($d['nombre']);
        if ($len < 2 || $len > 120) {
            $e['nombre'] = 'El nombre debe tener entre 2 y 120 caracteres.';
        }
        if (!preg_match('/^\d{1,8}(\.\d{1,2})?$/', $d['precio_unitario'])) {
            $e['precio_unitario'] = 'Precio inválido (número positivo, máx. 2 decimales).';
        }
        foreach (['stock_actual' => 'Stock actual', 'stock_minimo' => 'Stock mínimo'] as $f => $label) {
            if (!ctype_digit($d[$f]) || strlen($d[$f]) > 9) {
                $e[$f] = "$label debe ser un entero mayor o igual a 0.";
            }
        }
        return $e;
    }
}
