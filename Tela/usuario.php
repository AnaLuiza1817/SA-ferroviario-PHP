
<?php
require_once 'auth.php';
requireAdmin();

$busca = trim($_GET['busca'] ?? '');
$sql = "
    SELECT u.id, u.nome, u.email, u.telefone, u.tipo, u.status, u.cep, u.logradouro, u.bairro, u.cidade, u.uf,
           (SELECT COUNT(*) FROM trens t WHERE t.usuario_responsavel_id = u.id) AS total_trens
    FROM usuarios u
    WHERE u.deleted_at IS NULL
";

if ($busca !== '') {
    $termo = '%' . $busca . '%';
    $porId = ctype_digit($busca);
    $stmt = $conexao->prepare($sql . ' AND (u.nome LIKE ? OR u.email LIKE ?' . ($porId ? ' OR u.id = ?' : '') . ') ORDER BY u.id ASC');
    if ($porId) {
        $idBusca = (int)$busca;
        $stmt->bind_param('ssi', $termo, $termo, $idBusca);
    } else {
        $stmt->bind_param('ss', $termo, $termo);
    }
    $stmt->execute();
    $usuarios = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $usuarios = $conexao->query($sql . ' ORDER BY u.id ASC')->fetch_all(MYSQLI_ASSOC);
}

$flash = consumirFlash();
if (isset($_GET['sucesso']) && $_GET['sucesso'] === 'cadastro') $flash = ['tipo'=>'success','texto'=>'Usuário cadastrado com sucesso.'];
if (isset($_GET['sucesso']) && $_GET['sucesso'] === 'edicao') $flash = ['tipo'=>'success','texto'=>'Usuário atualizado com sucesso.'];
?>
<?php $tituloPagina = 'Usuários'; require __DIR__ . '/cabecalho.php'; ?>

<main class="container py-5">
    <div class="titulo-tela flex-wrap">
        <div>
            <h1 class="fw-bold">Usuários do Sistema</h1>
            <p class="text-muted mb-0"><?= count($usuarios) ?> usuário(s) <?= $busca !== '' ? 'encontrado(s)' : 'cadastrado(s)' ?>.</p>
        </div>
        <div class="d-flex gap-2">
            <form method="get" class="d-flex gap-2">
                <input type="text" name="busca" class="form-control" placeholder="Nome ou ID" aria-label="Pesquisar por nome, e-mail ou ID" value="<?= e($busca) ?>">
                <button type="submit" class="btn btn-outline-primary"><i class="fas fa-search"></i></button>
            </form>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalNovoUsuario">Novo Usuário</button>
        </div>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash['tipo']) ?>"><?= e($flash['texto']) ?></div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>Nome</th>
                            <th>E-mail</th>
                            <th>Tipo</th>
                            <th>Trens Vinculados</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td class="ps-4 fw-semibold"><?= (int)$usuario['id'] ?></td>
                                <td class="fw-bold"><?= e($usuario['nome']) ?></td>
                                <td><?= e($usuario['email']) ?></td>
                                <td><?= e($usuario['tipo']) ?></td>
                                <td>
                                    <span class="badge <?= (int)$usuario['total_trens'] > 0 ? 'bg-primary' : 'bg-light text-dark border' ?> rounded-pill">
                                        <?= (int)$usuario['total_trens'] ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $usuario['status'] === 'Ativo' ? 'success' : 'secondary' ?>">
                                        <?= e($usuario['status']) ?>
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalEditarUsuario" data-usuario-id="<?= (int)$usuario['id'] ?>" data-usuario-nome="<?= e($usuario['nome']) ?>" data-usuario-email="<?= e($usuario['email']) ?>" data-usuario-telefone="<?= e($usuario['telefone']) ?>" data-usuario-tipo="<?= e($usuario['tipo']) ?>" data-usuario-status="<?= e($usuario['status']) ?>" data-usuario-cep="<?= e($usuario['cep']) ?>" data-usuario-logradouro="<?= e($usuario['logradouro']) ?>" data-usuario-bairro="<?= e($usuario['bairro']) ?>" data-usuario-cidade="<?= e($usuario['cidade']) ?>" data-usuario-uf="<?= e($usuario['uf']) ?>" aria-label="Editar usuário <?= e($usuario['nome']) ?>">Editar</button>
                                    <?php if ((int)$usuario['total_trens'] > 0 || (int)$usuario['id'] === (int)$_SESSION['usuario_id']): ?>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" disabled aria-disabled="true" title="<?= (int)$usuario['total_trens'] > 0 ? 'Usuário possui trens vinculados.' : 'Não é possível excluir a própria conta.' ?>">Excluir</button>
                                    <?php else: ?>
                                        <form id="excluirUsuario<?= (int)$usuario['id'] ?>" method="post" action="excluir.php?id=<?= (int)$usuario['id'] ?>" class="d-inline"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="confirmacao" value="sim"><button type="button" class="btn btn-sm btn-outline-danger" aria-label="Excluir usuário <?= e($usuario['nome']) ?>" data-bs-toggle="modal" data-bs-target="#modalConfirmacao" data-confirm-form="excluirUsuario<?= (int)$usuario['id'] ?>" data-confirm-titulo="Excluir usuário" data-confirm-mensagem="Excluir o usuário <?= e($usuario['nome']) ?>? Esta ação desativa sua conta." data-confirm-botao="Sim, excluir">Excluir</button></form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$usuarios): ?><tr><td colspan="7" class="text-center text-muted py-4">Nenhum usuário encontrado.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<div class="modal fade" id="modalNovoUsuario" tabindex="-1" aria-labelledby="tituloNovoUsuario" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content">
        <div class="modal-header"><h2 class="modal-title" id="tituloNovoUsuario">Novo usuário</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button></div>
        <form method="post" action="Cadastro.php" data-cep-lookup data-modal-ajax data-recarregar="usuario.php">
            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="modal" value="1">
            <div class="modal-body"><div class="alert alert-danger d-none" role="alert" data-modal-erro></div>
                <div class="row g-3">
                    <div class="col-md-6"><label for="novo-nome" class="form-label">Nome</label><input id="novo-nome" class="form-control" name="nome" maxlength="150" required></div>
                    <div class="col-md-6"><label for="novo-email" class="form-label">E-mail</label><input id="novo-email" class="form-control" type="email" name="email" maxlength="150" required></div>
                    <div class="col-md-6"><label for="novo-senha" class="form-label">Senha</label><input id="novo-senha" class="form-control" type="password" name="senha" minlength="8" autocomplete="new-password" required></div>
                    <div class="col-md-6"><label for="novo-telefone" class="form-label">Telefone</label><input id="novo-telefone" class="form-control" name="telefone" maxlength="30"></div>
                    <div class="col-md-6"><label for="novo-tipo" class="form-label">Tipo</label><select id="novo-tipo" class="form-select" name="tipo"><option>Usuário</option><option>Supervisor</option><option>Administrador</option></select></div>
                    <div class="col-md-6"><label for="novo-status" class="form-label">Status</label><select id="novo-status" class="form-select" name="status"><option>Ativo</option><option>Inativo</option></select></div>
                    <?php $prefixoEndereco = 'novo'; $enderecoValores = []; require __DIR__ . '/campos-endereco.php'; ?>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Cadastrar</button></div>
        </form>
    </div></div>
</div>
<div class="modal fade" id="modalEditarUsuario" tabindex="-1" aria-labelledby="tituloEditarUsuario" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content">
        <div class="modal-header"><h2 class="modal-title" id="tituloEditarUsuario">Editar usuário</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button></div>
        <form method="post" action="editar.php" data-cep-lookup data-modal-ajax data-recarregar="usuario.php">
            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="modal" value="1">
            <div class="modal-body"><div class="alert alert-danger d-none" role="alert" data-modal-erro></div>
                <div class="row g-3">
                    <div class="col-md-6"><label for="editar-nome" class="form-label">Nome</label><input id="editar-nome" class="form-control" name="nome" maxlength="150" required></div>
                    <div class="col-md-6"><label for="editar-email" class="form-label">E-mail</label><input id="editar-email" type="email" class="form-control" name="email" maxlength="150" required></div>
                    <div class="col-md-6"><label for="editar-telefone" class="form-label">Telefone</label><input id="editar-telefone" class="form-control" name="telefone" maxlength="30"></div>
                    <div class="col-md-3"><label for="editar-tipo" class="form-label">Tipo</label><select id="editar-tipo" class="form-select" name="tipo"><option>Usuário</option><option>Supervisor</option><option>Administrador</option></select></div>
                    <div class="col-md-3"><label for="editar-status" class="form-label">Status</label><select id="editar-status" class="form-select" name="status"><option>Ativo</option><option>Inativo</option></select></div>
                    <?php $prefixoEndereco = 'editar'; $enderecoValores = []; require __DIR__ . '/campos-endereco.php'; ?>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Salvar</button></div>
        </form>
    </div></div>
</div>
<?php require __DIR__ . '/rodape.php'; ?>
