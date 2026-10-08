document.querySelectorAll('[data-mascara="digitos"]').forEach((campo) => {
    const max = Number(campo.dataset.max || 0);
    const ate = campo.dataset.ate ? Number(campo.dataset.ate) : null;
    campo.addEventListener('input', () => {
        let digitos = campo.value.replace(/\D/g, '');
        if (max > 0) {
            digitos = digitos.slice(0, max);
        }
        if (ate !== null && max > 0 && digitos.length === max && Number(digitos) > ate) {
            digitos = digitos.slice(0, -1);
        }
        campo.value = digitos;
    });
});

document.querySelectorAll('[data-mascara="reais"]').forEach((campo) => {
    const aplicar = () => {
        const digitos = campo.value.replace(/\D/g, '').slice(0, 10);
        const centavos = digitos.padStart(3, '0');
        const inteiro = centavos.slice(0, -2).replace(/^0+(?=\d)/, '');
        const milhar = inteiro.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        campo.value = milhar + ',' + centavos.slice(-2);
    };
    campo.addEventListener('input', aplicar);
    if (campo.value) {
        aplicar();
    }
});
