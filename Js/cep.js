
(() => {
    document.querySelectorAll('form[data-cep-form], form[data-cep-lookup]').forEach(form => {
        const cep = form.elements.cep;

        if (!cep) return;

        const campos = ['logradouro', 'bairro', 'cidade', 'uf'];
        const status = form.querySelector('[data-cep-status]') || document.getElementById('status-cep');
        const botao = form.querySelector('[data-consultar-cep]');
        
        const limpar = () => {
            campos.forEach(campo => {
                form.elements[campo].value = '';
            });
        };

        cep.addEventListener('input', () => {
            const numeros = cep.value.replace(/\D/g, '').slice(0, 8);

            cep.value = numeros.length > 5
                ? `${numeros.slice(0, 5)}-${numeros.slice(5)}`
                : numeros;

limpar();

            status.textContent = 'Informe um CEP brasileiro com 8 números.';
        });

        const consultar = async () => {
            const numeros = cep.value.replace(/\D/g, '');

            limpar();

            if (!/^\d{8}$/.test(numeros)) {
                status.textContent = 'CEP inválido. Informe 8 números.';
                return;
            }

            status.textContent = 'Consultando CEP...';

            if (botao) botao.disabled = true;

            const controller = new AbortController();
            const timeout = setTimeout(() => controller.abort(), 8000);

            try {
                const resposta = await fetch(
                    `https://viacep.com.br/ws/${numeros}/json/`,
                    {
                        signal: controller.signal,
                        cache: 'no-store'
                    }
                );

                if (!resposta.ok) {
                    throw new Error('Erro HTTP');
                }

                const dados = await resposta.json();

                if (dados.erro) {
                    status.textContent = 'CEP não encontrado. Confira o número informado.';
                    return;
                }

                if (!dados.cep || !dados.localidade || !dados.uf) {
                    throw new Error('Dados incompletos');
                }

                for (const [campo, chave] of [
                    ['logradouro', 'logradouro'],
                    ['bairro', 'bairro'],
                    ['cidade', 'localidade'],
                    ['uf', 'uf']
                ]) {
                    form.elements[campo].value = String(dados[chave] || '');
                }

                cep.value = String(dados.cep);

                status.textContent = 'Endereço encontrado e preenchido pela ViaCEP.';

  } catch {
                status.textContent = 'Não foi possível consultar o CEP. Verifique sua conexão e tente novamente.';

            } finally {
                clearTimeout(timeout);

                if (botao) botao.disabled = false;
            }
        };

        botao?.addEventListener('click', consultar);

        if (form.hasAttribute('data-cep-form')) {
            form.addEventListener('submit', event => {
                event.preventDefault();
                consultar();
            });
        }
    });
})();