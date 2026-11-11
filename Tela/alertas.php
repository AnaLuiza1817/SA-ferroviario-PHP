<?php
require_once 'auth.php';
requireLogin();
$alertas = $conexao->query("SELECT a.id, a.nivel, a.mensagem, a.status, a.criado_em, s.codigo AS sensor, t.codigo AS trem, tr.codigo AS trecho FROM alertas a LEFT JOIN sensores s ON s.id = a.sensor_id LEFT JOIN trens t ON t.id = a.trem_id LEFT JOIN trechos tr ON tr.id = a.trecho_id ORDER BY (a.status = 'Ativo') DESC, a.criado_em DESC LIMIT 100")->fetch_all(MYSQLI_ASSOC);
$tituloPagina = 'Alertas';
require __DIR__ . '/cabecalho.php';
?>
<main class="container py-4 pb-5">
    <div class="titulo-tela"><div><h1>Alertas da operação</h1><p>Ocorrências registradas pelos sensores e pela operação ferroviária.</p></div></div>
    <section class="painel-liso">
        <div class="painel-topo"><h2>Últimos alertas</h2><span>Registros do banco de dados</span></div>
        <div class="table-responsive"><table class="table table-hover align-middle">
            <thead><tr><th>Data e hora</th><th>Nível</th><th>Mensagem</th><th>Referência</th><th>Situação</th></tr></thead>
            <tbody>
            <?php foreach ($alertas as $alerta): ?>
                <?php $nivel = strtolower($alerta['nivel']); ?>
                <tr><td><?= e(date('d/m/Y H:i', strtotime($alerta['criado_em']))) ?></td>
                    <td><span class="rotulo-alerta <?= in_array($nivel, ['crítico', 'critico', 'alto'], true) ? 'critico' : 'atencao' ?>"><i class="fas fa-triangle-exclamation" aria-hidden="true"></i> <?= e($alerta['nivel']) ?></span></td>
                    <td><?= e($alerta['mensagem']) ?></td>
                    <td><?= e(implode(' · ', array_filter([$alerta['sensor'], $alerta['trem'], $alerta['trecho']]))) ?: '—' ?></td>
                    <td><?= e($alerta['status']) ?></td></tr>
            <?php endforeach; ?>
            <?php if (!$alertas): ?><tr><td colspan="5" class="text-center py-4">Nenhum alerta cadastrado.</td></tr><?php endif; ?>
            </tbody>
        </table></div>
    </section>
</main>
<?php require __DIR__ . '/rodape.php'; ?>
