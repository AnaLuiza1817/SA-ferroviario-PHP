<footer class="bg-white border-top py-3 mt-auto">
    <div class="container text-center text-muted small">
        &copy; <?= date('Y') ?> Hyper Sense - Sistema Integrado de Gestão Ferroviária.
    </div>
</footer>

<?php if (empty($semScripts)): ?>

<div class="modal" id="modalConfirmacao" tabindex="-1" aria-labelledby="tituloConfirmacao" aria-describedby="mensagemConfirmacao" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tituloConfirmacao">Confirmar ação</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>

            <div class="modal-body" id="mensagemConfirmacao">
                Deseja continuar?
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    Cancelar
                </button>

                <button type="submit" id="confirmarAcao" class="btn btn-danger">
                    Confirmar
                </button>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($carregarGrafico)): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
<script src="../Js/main.js?v=<?= filemtime(__DIR__ . '/../Js/main.js') ?>"></script>
<script src="../Js/cep.js?v=<?= filemtime(__DIR__ . '/../Js/cep.js') ?>"></script>
<script src="../Js/clima.js?v=<?= filemtime(__DIR__ . '/../Js/clima.js') ?>"></script>

<?php endif; ?>

</body>
</html>