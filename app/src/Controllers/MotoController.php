<?php
declare(strict_types=1);

final class MotoController extends Controller
{
    public function index(): void
    {
        Auth::requireRole('ADMIN', 'MECANICO'); // MECANICO: solo lectura (RF-04)
        $q = trim((string) ($_GET['q'] ?? ''));
        $idCliente = (int) ($_GET['id_cliente'] ?? 0);
        $cliente = $idCliente > 0 ? Cliente::find($idCliente) : null;
        $this->render('motos/index', ['title' => 'Motos', 'items' => Moto::all($q, $cliente ? $idCliente : 0),
            'q' => $q, 'clienteFiltro' => $cliente]);
    }

    public function form(): void
    {
        Auth::requireRole('ADMIN');
        $id = (int) ($_GET['id'] ?? 0);
        $item = ['id_cliente' => (int) ($_GET['id_cliente'] ?? 0), 'placa' => '', 'marca' => '',
                 'modelo' => '', 'anio' => '', 'color' => ''];
        if ($id > 0 && !($item = Moto::find($id))) {
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
            'id_cliente' => (int) ($_POST['id_cliente'] ?? 0),
            'placa'  => strtoupper($this->post('placa')),
            'marca'  => $this->post('marca'),
            'modelo' => $this->post('modelo'),
            'anio'   => $this->post('anio'),
            'color'  => $this->post('color'),
        ];
        $errors = $this->validate($d, $id);

        if (!$errors) {
            $d['anio'] = (int) $d['anio'];
            try {
                $id ? Moto::update($id, $d) : Moto::create($d);
                $this->flash('success', $id ? 'Moto actualizada.' : 'Moto registrada.');
                $this->redirect('motos');
            } catch (PDOException $ex) {
                if ($ex->getCode() !== '23000') {
                    throw $ex;
                }
                $errors['placa'] = 'Ya existe una moto con esa placa.';
            }
        }
        $this->renderForm($d, $errors, $id);
    }

    public function delete(): void
    {
        Auth::requireRole('ADMIN');
        $this->requirePost();
        try {
            Moto::delete((int) ($_POST['id'] ?? 0));
            $this->flash('success', 'Moto eliminada.');
        } catch (PDOException $ex) {
            if ($ex->getCode() !== '23000') {
                throw $ex;
            }
            $this->flash('danger', 'No se puede eliminar: la moto tiene órdenes de trabajo.');
        }
        $this->redirect('motos');
    }

    private function renderForm(array $item, array $errors, int $id): void
    {
        $this->render('motos/form', ['title' => $id ? 'Editar moto' : 'Nueva moto', 'item' => $item,
            'errors' => $errors, 'id' => $id, 'clientes' => Cliente::all()]);
    }

    private function validate(array $d, int $id): array
    {
        $e = [];
        if ($d['id_cliente'] <= 0 || !Cliente::find($d['id_cliente'])) {
            $e['id_cliente'] = 'Seleccione un cliente válido.';
        }
        if (!preg_match('/^[A-Z0-9-]{5,15}$/', $d['placa'])) {
            $e['placa'] = 'Placa inválida (5 a 15 caracteres: letras, números o guion).';
        } elseif (Moto::placaExists($d['placa'], $id)) {
            $e['placa'] = 'Ya existe una moto con esa placa.';
        }
        foreach (['marca' => 50, 'modelo' => 60, 'color' => 30] as $f => $max) {
            $len = mb_strlen($d[$f]);
            if ($len < 2 || $len > $max) {
                $e[$f] = ucfirst($f) . " debe tener entre 2 y $max caracteres.";
            }
        }
        $max = (int) date('Y') + 1;
        if (!ctype_digit($d['anio']) || (int) $d['anio'] < 1950 || (int) $d['anio'] > $max) {
            $e['anio'] = "Año inválido (1950 a $max).";
        }
        return $e;
    }
}
