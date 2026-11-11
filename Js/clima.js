
(() => {
    const sections = document.querySelectorAll('[data-weather]');

    if (!sections.length) return;

    const url = 'https://api.open-meteo.com/v1/forecast?latitude=-26.3014&longitude=-48.8439&current=temperature_2m,precipitation,wind_speed_10m,weather_code&hourly=precipitation_probability,precipitation&forecast_days=2&timezone=America%2FSao_Paulo';

    const conditions = {
        0: 'Céu limpo',
        1: 'Predominantemente limpo',
        2: 'Parcialmente nublado',
        3: 'Encoberto',
        45: 'Neblina',
        48: 'Neblina',
        51: 'Garoa',
        53: 'Garoa',
        55: 'Garoa intensa',
        56: 'Garoa gelada',
        57: 'Garoa gelada',
        61: 'Chuva fraca',
        63: 'Chuva moderada',
        65: 'Chuva forte',
        66: 'Chuva gelada',
        67: 'Chuva gelada',
        71: 'Neve',
        73: 'Neve',
        75: 'Neve',
        77: 'Neve',
        80: 'Pancadas de chuva',
        81: 'Pancadas de chuva',
        82: 'Pancadas de chuva fortes',
        85: 'Neve',
        86: 'Neve',
        95: 'Trovoadas',
        96: 'Trovoadas',
        99: 'Trovoadas'
    };
    
    const label = (value, unit, decimals = 0) => {
        return Number.isFinite(value)
            ? `${value.toLocaleString('pt-BR', {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals
            })} ${unit}`
            : 'Indisponível';
    };

    const text = (root, key, value) => {
        const el = root.querySelector(`[data-weather-${key}]`);

        if (el) {
            el.textContent = value;
        }
    };

    async function atualizar() {
        try {
            const controller = new AbortController();
            const timeout = setTimeout(() => controller.abort(), 10000);

            let response;