<?php
declare(strict_types=1);

/** RF-02: Gestión de usuarios y roles (solo ADMIN). Las cuentas se desactivan, no se borran. */
final class UsuarioController extends Controller
{
    public function index(): void
    {
        Auth::requireRole('ADMIN');
        $q = trim((string) ($_GET['q'] ?? ''));
        $this->render('usuarios/index', ['title' => 'Usuarios', 'items' => Usuario::all($q), 'q' => $q]);
    }

    public function form(): void
    {
        Auth::requireRole('ADMIN');
        $id = (int) ($_GET['id'] ?? 0);
        $item = ['nombre' => '', 'username' => '', 'rol' => 'MECANICO', 'activo' => 1];
        if ($id > 0 && !($item = Usuario::find($id))) {
            $this->notFound();
        }
        $this->renderForm($item, [], $id);
    }

    public function save(): void
    {
        Auth::requireRole('ADMIN');
        $this->requirePost();
        $id = (int) ($_POST['id'] ?? 0);
        $self = $id > 0 && $id === Auth::user()['id'];
        $actual = $id > 0 ? Usuario::find($id) : null;
        if ($id > 0 && !$actual) {
            $this->notFound();
        }
        $d = [
            'nombre' => preg_replace('/\s+/u', ' ', $this->post('nombre')),
            'username' => $this->post('username'),
            'password' => (string) ($_POST['password'] ?? ''),
            'rol' => $this->post('rol'),
            'activo' => isset($_POST['activo']) ? 1 : 0,
        ];
        if ($self) { // el administrador no puede quitarse a sí mismo el rol ni el acceso
            $d['rol'] = $actual['rol'];
            $d['activo'] = 1;
        }
        $errors = $this->validate($d, $id);
        if (!$errors) {
            try {
                $id ? Usuario::update($id, $d) : Usuario::create($d);
                $this->flash('success', $id ? 'Usuario actualizado.' : 'Usuario creado.');
                $this->redirect('usuarios');
            } catch (PDOException $ex) {
                if ($ex->getCode() !== '23000') {
                    throw $ex;
                }
                $errors['username'] = 'Ya existe un usuario con ese username.';
            }
        }
        unset($d['password']);
        $this->renderForm($d, $errors, $id);
    }

    /** Activa/desactiva la cuenta (baja lógica). */
    public function toggle(): void
    {
        Auth::requireRole('ADMIN');
        $this->requirePost();
        $id = (int) ($_POST['id'] ?? 0);
        $u = Usuario::find($id);
        if (!$u) {
            $this->notFound();
        }
        if ($id === Auth::user()['id']) {
            $this->flash('danger', 'No puede desactivar su propia cuenta.');
        } else {
            Usuario::setActivo($id, (int) $u['activo'] === 1 ? 0 : 1);
            $this->flash('success', (int) $u['activo'] === 1 ? 'Usuario desactivado.' : 'Usuario activado.');
        }
        $this->redirect('usuarios');
    }

    private function renderForm(array $item, array $errors, int $id): void
    {
        $this->render('usuarios/form', ['title' => $id ? 'Editar usuario' : 'Nuevo usuario',
            'item' => $item, 'errors' => $errors, 'id' => $id, 'esPropio' => $id > 0 && $id === Auth::user()['id']]);
    }

    private function validate(array $d, int $id): array
    {
        $e = [];
        $len = mb_strlen($d['nombre']);
        if ($len < 3 || $len > 100) {
            $e['nombre'] = 'El nombre debe tener entre 3 y 100 caracteres.';
        }
        if (!preg_match('/^[A-Za-z0-9._-]{3,50}$/', $d['username'])) {
            $e['username'] = 'Username inválido (3 a 50 caracteres: letras, números, . _ -).';
        } elseif (Usuario::usernameExists($d['username'], $id)) {
            $e['username'] = 'Ya existe un usuario con ese username.';
        }
        if (!in_array($d['rol'], ['ADMIN', 'MECANICO'], true)) {
            $e['rol'] = 'Seleccione un rol válido.';
        }
        $pl = strlen($d['password']);
        if (($id === 0 && $pl < 8) || ($pl > 0 && $pl < 8) || $pl > 72) {
            $e['password'] = $id === 0 ? 'La contraseña es obligatoria (8 a 72 caracteres).'
                                       : 'Deje vacío para conservarla o ingrese 8 a 72 caracteres.';
        }
        return $e;
    }
}
