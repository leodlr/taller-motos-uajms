<?php
declare(strict_types=1);

final class ClienteController extends Controller
{
    public function index(): void
    {
        Auth::requireRole('ADMIN', 'MECANICO'); // MECANICO: solo lectura (RF-03)
        $q = trim((string) ($_GET['q'] ?? ''));
        $this->render('clientes/index', ['title' => 'Clientes', 'items' => Cliente::all($q), 'q' => $q]);
    }

    public function form(): void
    {
        Auth::requireRole('ADMIN');
        $id = (int) ($_GET['id'] ?? 0);
        $item = ['ci' => '', 'nombre_completo' => '', 'telefono' => ''];
        if ($id > 0 && !($item = Cliente::find($id))) {
            $this->notFound();
        }
        $this->render('clientes/form', ['title' => $id ? 'Editar cliente' : 'Nuevo cliente',
            'item' => $item, 'errors' => [], 'id' => $id]);
    }

    public function save(): void
    {
        Auth::requireRole('ADMIN');
        $this->requirePost();
        $id = (int) ($_POST['id'] ?? 0);
        $d = [
            'ci' => $this->post('ci'),
            'nombre_completo' => preg_replace('/\s+/u', ' ', $this->post('nombre_completo')),
            'telefono' => $this->post('telefono'),
        ];
        $errors = $this->validate($d, $id);

        if (!$errors) {
            try {
                $id ? Cliente::update($id, $d) : Cliente::create($d);
                $this->flash('success', $id ? 'Cliente actualizado.' : 'Cliente registrado.');
                $this->redirect('clientes');
            } catch (PDOException $ex) {
                if ($ex->getCode() !== '23000') {
                    throw $ex;
                }
                $errors['ci'] = 'Ya existe un cliente con ese CI.';
            }
        }
        $this->render('clientes/form', ['title' => $id ? 'Editar cliente' : 'Nuevo cliente',
            'item' => $d, 'errors' => $errors, 'id' => $id]);
    }

    public function delete(): void
    {
        Auth::requireRole('ADMIN');
        $this->requirePost();
        try {
            Cliente::delete((int) ($_POST['id'] ?? 0));
            $this->flash('success', 'Cliente eliminado.');
        } catch (PDOException $ex) {
            if ($ex->getCode() !== '23000') {
                throw $ex;
            }
            $this->flash('danger', 'No se puede eliminar: el cliente tiene motos registradas.');
        }
        $this->redirect('clientes');
    }

    private function validate(array $d, int $id): array
    {
        $e = [];
        if (!preg_match('/^[A-Za-z0-9-]{5,20}$/', $d['ci'])) {
            $e['ci'] = 'CI inválido (5 a 20 caracteres alfanuméricos).';
        } elseif (Cliente::ciExists($d['ci'], $id)) {
            $e['ci'] = 'Ya existe un cliente con ese CI.';
        }
        $len = mb_strlen($d['nombre_completo']);
        if ($len < 3 || $len > 120) {
            $e['nombre_completo'] = 'El nombre debe tener entre 3 y 120 caracteres.';
        }
        if (!preg_match('/^[0-9+\-\s]{7,25}$/', $d['telefono'])) {
            $e['telefono'] = 'Teléfono inválido (7 a 25 dígitos, puede incluir + y -).';
        }
        return $e;
    }
}
