<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim((string) ($_POST['nome'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $senha = (string) ($_POST['senha'] ?? '');
    if ($nome === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($senha) < 6) {
        $error = 'Informe nome, email válido e senha com pelo menos 6 caracteres.';
    } else {
        try {
            $statement = database()->prepare('INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)');
            $statement->execute([$nome, $email, password_hash($senha, PASSWORD_DEFAULT)]);
            header('Location: /login.php');
            exit;
        } catch (PDOException $exception) {
            $error = 'Este email já está cadastrado.';
        }
    }
}
$pageTitle = 'Cadastro | NaBrasa';
require __DIR__ . '/../includes/header.php';
?>
<div class="auth-wrap col-md-7 col-lg-5 mx-auto">
    <div class="card auth-card p-4 p-md-5">
        <div class="auth-badge mb-3"><i class="bi bi-egg-fried" aria-hidden="true"></i></div>
        <h1 class="h3 mb-1">Criar conta</h1>
        <p class="text-muted mb-4">Leva menos de um minuto para começar a organizar.</p>
        <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <form method="post">
            <label class="form-label">Nome</label>
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-person" aria-hidden="true"></i></span>
                <input class="form-control" name="nome" placeholder="Seu nome" required>
            </div>
            <label class="form-label">Email</label>
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-envelope" aria-hidden="true"></i></span>
                <input class="form-control" type="email" name="email" placeholder="seu@email.com" required>
            </div>
            <label class="form-label">Senha</label>
            <div class="input-group mb-1">
                <span class="input-group-text"><i class="bi bi-lock" aria-hidden="true"></i></span>
                <input class="form-control" type="password" name="senha" id="senha" placeholder="••••••••" minlength="6" required>
                <button class="btn toggle-password" type="button" data-target="senha" aria-label="Mostrar senha"><i class="bi bi-eye" aria-hidden="true"></i></button>
            </div>
            <small class="text-muted d-block mb-4">Mínimo de 6 caracteres.</small>
            <button class="btn btn-danger w-100 py-2">Cadastrar</button>
        </form>
        <p class="mt-4 mb-0 text-center">Já é cadastrado? <a href="/login.php" class="fw-semibold">Clique aqui para fazer login</a></p>
    </div>
</div>
<script>
document.querySelectorAll('.toggle-password').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var input = document.getElementById(btn.dataset.target);
        var icon = btn.querySelector('i');
        var showing = input.type === 'text';
        input.type = showing ? 'password' : 'text';
        icon.classList.toggle('bi-eye', showing);
        icon.classList.toggle('bi-eye-slash', !showing);
        btn.setAttribute('aria-label', showing ? 'Mostrar senha' : 'Ocultar senha');
    });
});
</script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
