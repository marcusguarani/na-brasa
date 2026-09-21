<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../functions/calculadora.php';
require_once __DIR__ . '/../../functions/produtos.php';
requireLogin();

$db = database();
$churrascoId = (int) ($_GET['id'] ?? 0);
$statement = $db->prepare('SELECT * FROM churrascos WHERE id = ? AND usuario_id = ?');
$statement->execute([$churrascoId, currentUserId()]);
$churrasco = $statement->fetch();
if (!$churrasco) {
    header('Location: /');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'salvar_lista') {
    verifyCsrf();
    $db->beginTransaction();
    $db->prepare('DELETE FROM churrasco_produto WHERE churrasco_id = ?')->execute([$churrascoId]);
    $insert = $db->prepare('INSERT INTO churrasco_produto (churrasco_id, produto_id, quantidade) VALUES (?, ?, ?)');
    foreach ((array) ($_POST['quantidades'] ?? []) as $produtoId => $quantidade) {
        if ((float) $quantidade >= 0) {
            $insert->execute([$churrascoId, (int) $produtoId, (float) $quantidade]);
        }
    }
    $db->commit();
    header('Location: /pages/lista_compras.php?id=' . $churrascoId);
    exit;
}
$produtos = $db->query('SELECT * FROM produtos WHERE ativo = 1 ORDER BY categoria, nome')->fetchAll();
$busca = trim((string) ($_GET['busca'] ?? ''));
$lista = calcularLista($produtos, (int) $churrasco['quantidade_adultos'], (int) $churrasco['quantidade_criancas'], $churrasco['tipo'], $churrasco['duracao']);
$listaExibida = $busca !== '' ? filtrarProdutos($lista, $busca) : $lista;
$idsExibidos = array_column($listaExibida, 'id');
$listaOculta = array_filter($lista, static fn (array $item): bool => !in_array($item['id'], $idsExibidos, true));
$total = totalLista($lista);
$pessoas = (int) $churrasco['quantidade_adultos'] + (int) $churrasco['quantidade_criancas'];
$pageTitle = 'Calculadora | NaBrasa';
require __DIR__ . '/../../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4"><div><a href="/" class="text-danger text-decoration-none">← Dashboard</a><h1 class="h3 mb-0"><?= htmlspecialchars($churrasco['nome']) ?></h1></div><span class="badge text-bg-warning"><?= htmlspecialchars($churrasco['tipo']) ?></span></div>
<div class="row g-3 mb-4"><div class="col-md-4"><div class="card p-3"><small class="text-muted">Pessoas equivalentes</small><h2><?= number_format((float) $churrasco['quantidade_adultos'] + ((int) $churrasco['quantidade_criancas'] * .5), 1, ',', '.') ?></h2></div></div><div class="col-md-4"><div class="card p-3"><small class="text-muted">Total estimado</small><h2><?= moeda($total) ?></h2></div></div><div class="col-md-4"><div class="card p-3"><small class="text-muted">Valor por participante</small><h2><?= $pessoas ? moeda($total / $pessoas) : 'R$ 0,00' ?></h2></div></div></div>
<div class="card p-4"><div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3"><h2 class="h4 mb-0">Lista de compras sugerida</h2><form method="get" class="d-flex gap-2"><input type="hidden" name="id" value="<?= $churrascoId ?>"><input class="form-control form-control-sm" type="search" name="busca" placeholder="Buscar produto..." value="<?= htmlspecialchars($busca) ?>"><button class="btn btn-sm btn-outline-secondary">Buscar</button><?php if ($busca !== ''): ?><a class="btn btn-sm btn-outline-secondary" href="/pages/calculadora.php?id=<?= $churrascoId ?>">Limpar</a><?php endif; ?></form></div>
<?php if ($busca !== '' && !$listaExibida): ?><p class="text-muted">Nenhum produto encontrado para "<?= htmlspecialchars($busca) ?>".</p><?php endif; ?>
<form method="post"><input type="hidden" name="action" value="salvar_lista"><?= csrfField() ?><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Produto</th><th>Categoria</th><th>Quantidade</th><th>Preço unitário</th><th>Subtotal</th></tr></thead><tbody>
<?php foreach ($listaExibida as $item): ?><tr><td><?= htmlspecialchars($item['nome']) ?></td><td><?= htmlspecialchars($item['categoria']) ?></td><td><input class="form-control form-control-sm" style="max-width:120px" type="number" min="0" step="0.1" name="quantidades[<?= (int) $item['id'] ?>]" value="<?= htmlspecialchars((string) $item['quantidade']) ?>"> <?= htmlspecialchars($item['unidade_medida']) ?></td><td><?= moeda((float) $item['preco']) ?></td><td><?= moeda((float) $item['subtotal']) ?></td></tr><?php endforeach; ?>
<?php foreach ($listaOculta as $item): ?><input type="hidden" name="quantidades[<?= (int) $item['id'] ?>]" value="<?= htmlspecialchars((string) $item['quantidade']) ?>"><?php endforeach; ?>
</tbody><tfoot><tr class="fw-bold"><td colspan="4" class="text-end">Total sugerido</td><td><?= moeda($total) ?></td></tr></tfoot></table></div><button class="btn btn-danger">Salvar lista de compras</button><a class="btn btn-outline-secondary ms-2" href="/pages/lista_compras.php?id=<?= $churrascoId ?>">Ver lista salva</a></form></div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
