<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();

$db = database();
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $nome = trim((string) ($_POST['nome'] ?? ''));
    $adultos = (int) ($_POST['adultos'] ?? 0);
    $criancas = (int) ($_POST['criancas'] ?? 0);
    $data = (string) ($_POST['data_churrasco'] ?? '');
    $duracao = (string) ($_POST['duracao'] ?? '');
    $tipo = (string) ($_POST['tipo'] ?? '');
    if ($nome === '' || $adultos < 0 || $criancas < 0 || $adultos + $criancas === 0 || $data === '') {
        $error = 'Preencha o nome, data e informe pelo menos um participante.';
    } else {
        $statement = $db->prepare(
            'INSERT INTO churrascos (nome, data_churrasco, quantidade_adultos, quantidade_criancas, duracao, tipo, usuario_id) VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $statement->execute([$nome, $data, $adultos, $criancas, $duracao, $tipo, currentUserId()]);
        header('Location: /');
        exit;
    }
}
$pageTitle = 'Novo churrasco | NaBrasa';
require __DIR__ . '/../../includes/header.php';
?>
<div class="card p-4 col-lg-8 mx-auto">
    <h1 class="h3">Criar churrasco</h1>
    <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="post" class="row g-3">
        <?= csrfField() ?>
        <div class="col-md-8"><label class="form-label">Nome</label><input class="form-control" name="nome" required></div>
        <div class="col-md-4"><label class="form-label">Data</label><input class="form-control" type="date" name="data_churrasco" required></div>
        <div class="col-md-4"><label class="form-label">Adultos</label><input class="form-control" type="number" min="0" name="adultos" value="1"></div>
        <div class="col-md-4"><label class="form-label">Crianças</label><input class="form-control" type="number" min="0" name="criancas" value="0"></div>
        <div class="col-md-4"><label class="form-label">Duração</label><select class="form-select" name="duracao"><option>2 horas</option><option selected>4 horas</option><option>6 horas ou mais</option></select></div>
        <div class="col-md-6"><label class="form-label">Tipo</label><select class="form-select" name="tipo"><option>Econômico</option><option selected>Tradicional</option><option>Na Brasa</option></select></div>
        <div class="col-12"><button class="btn btn-danger">Salvar churrasco</button><a class="btn btn-outline-secondary ms-2" href="/">Cancelar</a></div>
    </form>
</div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
