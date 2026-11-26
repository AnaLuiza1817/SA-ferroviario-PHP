<div class="col-12 campos-endereco">
    <h3 class="h6 mb-2">Endereço</h3>

    <div class="row g-3">

        <div class="col-md-5">
            <label class="form-label" for="<?= e($prefixoEndereco) ?>-cep">CEP</label>

            <div class="input-group">
                <input class="form-control" id="<?= e($prefixoEndereco) ?>-cep" name="cep" inputmode="numeric" maxlength="9" placeholder="00000-000" value="<?= e($enderecoValores['cep'] ?? '') ?>">

                <button class="btn btn-outline-primary" type="button" data-consultar-cep>
                    Consultar
                </button>
            </div>
        </div>
        
        <div class="col-md-7">
            <label class="form-label" for="<?= e($prefixoEndereco) ?>-logradouro">Logradouro</label>
            <input class="form-control" id="<?= e($prefixoEndereco) ?>-logradouro" name="logradouro" maxlength="150" value="<?= e($enderecoValores['logradouro'] ?? '') ?>">
        </div>

        <div class="col-md-4">
            <label class="form-label" for="<?= e($prefixoEndereco) ?>-bairro">Bairro</label>
            <input class="form-control" id="<?= e($prefixoEndereco) ?>-bairro" name="bairro" maxlength="100" value="<?= e($enderecoValores['bairro'] ?? '') ?>">
        </div>

        <div class="col-md-6">
            <label class="form-label" for="<?= e($prefixoEndereco) ?>-cidade">Cidade</label>
            <input class="form-control" id="<?= e($prefixoEndereco) ?>-cidade" name="cidade" maxlength="100" value="<?= e($enderecoValores['cidade'] ?? '') ?>">
        </div>

        <div class="col-md-2">
            <label class="form-label" for="<?= e($prefixoEndereco) ?>-uf">UF</label>
            <input class="form-control" id="<?= e($prefixoEndereco) ?>-uf" name="uf" maxlength="2" value="<?= e($enderecoValores['uf'] ?? '') ?>">
        </div>

    </div>

    <p class="small text-muted mt-2 mb-0" role="status" aria-live="polite" data-cep-status>
        Informe o CEP e clique em Consultar para preencher os dados.
    </p>
</div>