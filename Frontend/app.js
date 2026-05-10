import { initNavbar } from './components/navbar.js';
import { ThemeManager } from './styles/theme.js';
import { WeatherService } from './services/weather.service.js';
import { AuthGuard } from './guards/auth.guard.js';

document.addEventListener('DOMContentLoaded', async () => {
    // 1. Bloqueio de Segurança
    if (!AuthGuard.check()) return;

    // 2. Setup Visual
    ThemeManager.init();
    initNavbar();

    // 3. Eventos Globais
    const themeBtn = document.getElementById('theme-toggle');
    if (themeBtn) themeBtn.addEventListener('click', () => ThemeManager.toggle());

    const logoutBtn = document.getElementById('logout-btn');
    if (logoutBtn) {
        logoutBtn.onclick = () => {
            localStorage.clear();
            window.location.href = "login.html";
        };
    }

    // 4. Lógica de Busca de Clima
    const searchBtn = document.getElementById('search-btn');
    if (searchBtn) {
        searchBtn.addEventListener('click', async () => {
            const city = document.getElementById('city-input').value;
            const resultArea = document.getElementById('weather-result');
            
            if (city) {
                resultArea.innerHTML = "<div class='loader'>Consultando API...</div>";
                const data = await WeatherService.getWeatherData(city);
                
                if (data) {
                    resultArea.innerHTML = `
                        <div class="neumorphism" style="margin-top: 20px; text-align: center;">
                            <img src="https://openweathermap.org/img/wn/${data.icon}@2x.png">
                            <h2>${data.city}</h2>
                            <p style="font-size: 3rem; font-weight: bold; color: var(--accent);">${data.temp}°C</p>
                            <p>${data.condition}</p>
                            <button class="neumorphism-btn" id="fav-btn" style="margin-top:10px;">⭐ Favoritar</button>
                        </div>
                    `;
                } else {
                    resultArea.innerHTML = "<p>Erro ao buscar. Verifique a chave da API.</p>";
                }
            }
        });
    }
});