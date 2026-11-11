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