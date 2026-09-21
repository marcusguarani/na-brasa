<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = database();
    $statement = $db->prepare('SELECT id, nome, senha FROM usuarios WHERE email = ?');
    $statement->execute([trim((string) ($_POST['email'] ?? ''))]);
    $user = $statement->fetch();
    if ($user && password_verify((string) ($_POST['senha'] ?? ''), $user['senha'])) {
        $_SESSION['usuario_id'] = (int) $user['id'];
        header('Location: /');
        exit;
    }
    $error = 'Email ou senha inválidos.';
}
$pageTitle = 'Entrar | NaBrasa';
require __DIR__ . '/../includes/header.php';
?>
<div class="auth-wrap col-md-7 col-lg-5 mx-auto">
    <div class="card auth-card p-4 p-md-5">
        <div class="auth-badge mb-3"><i class="bi bi-fire" aria-hidden="true"></i></div>
        <h1 class="h3 mb-1">Bem-vindo de volta</h1>
        <p class="text-muted mb-4">Entre para organizar seu próximo churrasco.</p>
        <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <form method="post">
            <label class="form-label">Email</label>
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-envelope" aria-hidden="true"></i></span>
                <input class="form-control" type="email" name="email" placeholder="seu@email.com" required>
            </div>
            <label class="form-label">Senha</label>
            <div class="input-group mb-4">
                <span class="input-group-text"><i class="bi bi-lock" aria-hidden="true"></i></span>
                <input class="form-control" type="password" name="senha" id="senha" placeholder="••••••••" required>
                <button class="btn toggle-password" type="button" data-target="senha" aria-label="Mostrar senha"><i class="bi bi-eye" aria-hidden="true"></i></button>
            </div>
            <button class="btn btn-danger w-100 py-2">Entrar</button>
        </form>
        <p class="mt-4 mb-0 text-center">Ainda não tem conta? <a href="/cadastro.php" class="fw-semibold">Cadastre-se</a></p>
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
