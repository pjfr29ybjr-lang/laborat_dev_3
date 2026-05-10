import { initNavbar } from './components/navbar.js';
import { WeatherService } from './services/weather.service.js';

document.addEventListener('DOMContentLoaded', () => {
    // Inicializa a barra de navegação
    initNavbar();

    const searchBtn = document.getElementById('search-btn');
    const cityInput = document.getElementById('city-input');
    const resultArea = document.getElementById('weather-result');

    if (searchBtn) {
        searchBtn.addEventListener('click', async () => {
            const city = cityInput.value;
            
            if (city) {
                resultArea.innerHTML = "<p>Buscando...</p>"; // Feedback visual
                
                // Busca os dados no "serviço"
                const data = await WeatherService.getWeatherData(city);
                
                // Exibe o card de resultado
                resultArea.innerHTML = `
                    <div class="neumorphism" style="margin-top: 20px; text-align: center; animation: fadeIn 0.5s;">
                        <h2 style="margin-bottom: 10px;">${data.city}</h2>
                        <p style="font-size: 3rem; font-weight: bold; color: var(--accent);">${data.temp}°C</p>
                        <p style="text-transform: uppercase; letter-spacing: 1px;">${data.condition}</p>
                        <hr style="margin: 15px 0; border: none; height: 1px; background: var(--shadow-dark);">
                        <p>Umidade: ${data.humidity}</p>
                    </div>
                `;
            } else {
                alert("Por favor, digite o nome de uma cidade.");
            }
        });
    }
});