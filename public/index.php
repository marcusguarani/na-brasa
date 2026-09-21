<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$db = database();
$userId = currentUserId();
$message = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'deletar') {
    verifyCsrf();
    $churrascoId = (int) ($_POST['churrasco_id'] ?? 0);
    $delete = $db->prepare('DELETE FROM churrascos WHERE id = ? AND usuario_id = ?');
    $delete->execute([$churrascoId, $userId]);
    $message = $delete->rowCount() > 0 ? 'Churrasco excluído.' : null;
}
$statement = $db->prepare('SELECT nome FROM usuarios WHERE id = ?');
$statement->execute([$userId]);
$user = $statement->fetch();
$statement = $db->prepare(
    'SELECT c.*, COUNT(cp.participante_id) AS participantes
     FROM churrascos c LEFT JOIN churrasco_participante cp ON cp.churrasco_id = c.id
     WHERE c.usuario_id = ? GROUP BY c.id ORDER BY c.data_churrasco'
);
$statement->execute([$userId]);
$churrascos = $statement->fetchAll();
$totalEstimado = 0.0;
$totalParticipantes = 0;
foreach ($churrascos as $churrasco) {
    $totalParticipantes += (int) $churrasco['participantes'];
}
$pageTitle = 'NaBrasa | Dashboard';
require __DIR__ . '/../includes/header.php';
?>
<section class="hero rounded-4 text-white p-4 p-md-5 mb-4">
    <div class="eyebrow mb-2">Seu painel de organização</div>
    <h1 class="display-6 fw-bold">Olá, <?= htmlspecialchars($user['nome'] ?? 'churrasqueiro') ?>! <i class="bi bi-fire" aria-hidden="true"></i></h1>
    <p class="lead mb-0">Planeje o próximo encontro e deixe o churrasco no ponto.</p>
</section>
<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="card stat-card stat-fire p-4"><i class="stat-icon bi bi-fire" aria-hidden="true"></i><div class="stat-label">Churrascos cadastrados</div><div class="stat-value"><?= count($churrascos) ?></div></div></div>
    <div class="col-md-4"><div class="card stat-card stat-users p-4"><i class="stat-icon bi bi-people-fill" aria-hidden="true"></i><div class="stat-label">Participantes confirmados</div><div class="stat-value"><?= $totalParticipantes ?></div></div></div>
    <div class="col-md-4"><div class="card stat-card stat-calendar p-4"><i class="stat-icon bi bi-calendar-event-fill" aria-hidden="true"></i><div class="stat-label">Próximo churrasco</div><div class="stat-value h4"><?= !empty($churrascos) ? date('d/m/Y', strtotime($churrascos[0]['data_churrasco'])) : 'Nenhum' ?></div></div></div>
</div>
<?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
<div class="card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3"><div><div class="section-title">Meus churrascos</div><small class="text-muted">Acompanhe seus próximos encontros</small></div><a class="btn btn-primary" href="/pages/churrasco_form.php"><i class="bi bi-plus-lg me-1"></i> Novo churrasco</a></div>
    <?php if ($churrascos): ?><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Nome</th><th>Data</th><th>Participantes</th><th>Tipo</th><th></th></tr></thead><tbody>
        <?php foreach ($churrascos as $churrasco): ?><tr><td class="fw-semibold"><?= htmlspecialchars($churrasco['nome']) ?></td><td><?= date('d/m/Y', strtotime($churrasco['data_churrasco'])) ?></td><td><?= (int) $churrasco['participantes'] ?></td><td><span class="badge text-bg-warning"><?= htmlspecialchars($churrasco['tipo']) ?></span></td><td class="text-nowrap"><a class="btn btn-sm btn-outline-danger" href="/pages/calculadora.php?id=<?= (int) $churrasco['id'] ?>">Calculadora</a> <a class="btn btn-sm btn-outline-secondary" href="/pages/participantes.php?id=<?= (int) $churrasco['id'] ?>">Pessoas</a> <a class="btn btn-sm btn-warning" target="_blank" href="/participar.php?id=<?= (int) $churrasco['id'] ?>">Convite</a> <button type="button" class="btn btn-sm btn-outline-secondary btn-excluir" data-bs-toggle="modal" data-bs-target="#modalExcluir" data-id="<?= (int) $churrasco['id'] ?>" data-nome="<?= htmlspecialchars($churrasco['nome']) ?>" aria-label="Excluir <?= htmlspecialchars($churrasco['nome']) ?>"><i class="bi bi-trash" aria-hidden="true"></i></button></td></tr><?php endforeach; ?>
    </tbody></table></div><?php else: ?><div class="empty-state"><div class="empty-state-icon fs-1 mb-2"><i class="bi bi-fire" aria-hidden="true"></i></div><strong>Seu primeiro churrasco começa aqui</strong><p class="mb-3">Crie um evento e organize tudo em um só lugar.</p><a class="btn btn-primary" href="/pages/churrasco_form.php">Criar churrasco</a></div><?php endif; ?>
</div>

<div class="modal fade" id="modalExcluir" tabindex="-1" aria-labelledby="modalExcluirLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title h5" id="modalExcluirLabel"><i class="bi bi-exclamation-triangle text-danger me-2" aria-hidden="true"></i>Excluir churrasco</h2>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
        <p class="mb-0">Tem certeza que deseja excluir <strong id="modalExcluirNome"></strong>? Essa ação também apaga a lista de compras, os participantes e os pedidos ligados a ele, e não pode ser desfeita.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
        <form method="post">
            <input type="hidden" name="action" value="deletar">
            <input type="hidden" name="churrasco_id" id="modalExcluirId" value="">
            <?= csrfField() ?>
            <button type="submit" class="btn btn-danger">Sim, excluir</button>
        </form>
      </div>
    </div>
  </div>
</div>
<script>
document.getElementById('modalExcluir').addEventListener('show.bs.modal', function (event) {
    var btn = event.relatedTarget;
    document.getElementById('modalExcluirId').value = btn.getAttribute('data-id');
    document.getElementById('modalExcluirNome').textContent = btn.getAttribute('data-nome');
});
</script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
