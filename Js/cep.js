
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

