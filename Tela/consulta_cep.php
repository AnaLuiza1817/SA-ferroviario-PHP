<?php
require_once 'auth.php';
requireLogin();
$tituloPagina = 'Consulta de CEP';
require __DIR__ . '/cabecalho.php';
?>
<main class="container">
    <div class="titulo-tela"><div><h1>Consulta de CEP</h1><p>Preenchimento de endereço com dados públicos dos Correios via ViaCEP.</p></div></div>
    <section class="painel-liso formulario-cep" aria-labelledby="consulta-endereco">
        <h2 id="consulta-endereco" class="mb-3">Consultar endereço</h2>
        <form data-cep-form autocomplete="off">
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label" for="cep">CEP</label><div class="input-group"><input class="form-control" id="cep" name="cep" inputmode="numeric" maxlength="9" placeholder="00000-000" required aria-describedby="status-cep"><button class="btn btn-primary" type="submit">Consultar</button></div></div>
                <div class="col-md-8"><label class="form-label" for="logradouro">Logradouro</label><input class="form-control" id="logradouro" name="logradouro" readonly></div>
                <div class="col-md-4"><label class="form-label" for="bairro">Bairro</label><input class="form-control" id="bairro" name="bairro" readonly></div>
                <div class="col-md-5"><label class="form-label" for="cidade">Cidade</label><input class="form-control" id="cidade" name="cidade" readonly></div>
                <div class="col-md-3"><label class="form-label" for="uf">Estado (UF)</label><input class="form-control" id="uf" name="uf" readonly></div>
            </div>
            <p class="mt-3 mb-0 text-muted" id="status-cep" role="status" aria-live="polite">Digite um CEP brasileiro de 8 números para consultar.</p>
        </form>
    </section>
</main>
<?php require __DIR__ . '/rodape.php'; ?>
