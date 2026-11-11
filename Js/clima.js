
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
            
            try {
                response = await fetch(url, {
                    cache: 'no-store',
                    signal: controller.signal
                });
            } finally {
                clearTimeout(timeout);
            }

            if (!response.ok) {
                throw Error('Não foi possível consultar a fonte meteorológica.');
            }

            const data = await response.json();

            if (!data.current || !Array.isArray(data.hourly?.time)) {
                throw Error('Dados meteorológicos incompletos.');
            }

            const start = data.hourly.time.findIndex(t =>
                t >= data.current.time.slice(0, 13) + ':00'
            );

            if (start < 0) {
                throw Error('Previsão horária indisponível.');
            }

            
            const next6 = data.hourly.precipitation_probability.slice(start, start + 6);
            const rainfall = data.hourly.precipitation.slice(start, start + 6);

            const chance = next6.length && next6.every(Number.isFinite)
                ? Math.max(...next6)
                : null;

            const sum = rainfall.length && rainfall.every(Number.isFinite)
                ? rainfall.reduce((a, b) => a + b, 0)
                : null;

            for (const root of sections) {
                text(root, 'condition', conditions[data.current.weather_code] || 'Condição indisponível');

                text(root, 'temperature', label(data.current.temperature_2m, '°C', 1));
                text(root, 'rain', label(data.current.precipitation, 'mm', 1));
                text(root, 'wind', label(data.current.wind_speed_10m, 'km/h', 0));
                text(root, 'probability', label(chance, '%', 0));
                text(root, 'precipitation', label(sum, 'mm', 1));

                text(
                    root,
                    'update',
                    `Fonte: Open-Meteo · Referência: ${data.current.time.slice(8, 10)}/${data.current.time.slice(5, 7)} às ${data.current.time.slice(11, 16)} (Joinville)`
                );

                const tbody = root.querySelector('[data-weather-hourly]');

                if (tbody) {
                    tbody.replaceChildren();
                    
                    for (let i = start; i < Math.min(start + 24, data.hourly.time.length); i += 3) {
                        const tr = document.createElement('tr');
                        const horario = data.hourly.time[i];

                        const valores = [
                            `${horario.slice(8, 10)}/${horario.slice(5, 7)} ${horario.slice(11, 16)}`,
                            label(data.hourly.precipitation_probability[i], '%', 0),
                            label(data.hourly.precipitation[i], 'mm', 1)
                        ];

                        for (const value of valores) {
                            const td = document.createElement('td');

                            td.textContent = value;
                            tr.appendChild(td);
                        }

                        tbody.appendChild(tr);
                    }
                }
            }

        } catch (error) {
            for (const root of sections) {
                for (const key of [
                    'temperature',
                    'rain',
                    'wind',
                    'probability',
                    'precipitation'
                ]) {
                    text(root, key, 'Indisponível');
                }
                
                text(root, 'condition', 'Não foi possível consultar o clima.');

                text(
                    root,
                    'update',
                    'Fonte: Open-Meteo · Dados indisponíveis. Verifique a conexão com a internet.'
                );

                const tbody = root.querySelector('[data-weather-hourly]');

                if (tbody) {
                    tbody.replaceChildren();

                    const tr = document.createElement('tr');
                    const td = document.createElement('td');

                    td.colSpan = 3;
                    td.textContent = 'Previsão indisponível.';

                    tr.appendChild(td);
                    tbody.appendChild(tr);
                }
            }
        }
    }

    atualizar();

    setInterval(atualizar, 600000);
})();