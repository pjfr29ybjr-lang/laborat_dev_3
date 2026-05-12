import { WeatherService } from './services/weather.service.js';

const i18n = {
    pt: { welcome: "Bem-vindo", search: "Buscar", fav: "Favoritos", hist: "Recentes", weak: "Senha Fraca!" },
    en: { welcome: "Welcome", search: "Search", fav: "Favorites", hist: "Recent", weak: "Weak Password!" }
};

document.addEventListener('DOMContentLoaded', () => {
    console.log("🚀 Chley Weather Online");

    // --- LÓGICA DE NAVEGAÇÃO E SESSÃO ---
    const user = JSON.parse(localStorage.getItem('user'));
    
    // Se estiver no Dashboard ou Home e não houver user, volta ao Login
    if (!user && (window.location.pathname.includes('dashboard') || window.location.pathname.includes('home'))) {
        window.location.href = 'index.html'; // Ajusta conforme a tua pasta
    }

    // Preencher dados do Perfil se existirem
    if (user && document.getElementById('user-display-name')) {
        document.getElementById('user-display-name').innerText = user.name;
        document.getElementById('user-email').innerText = user.email;
    }

    // --- 1. VALIDAÇÃO DE REGISTRO ---
    const regForm = document.getElementById('register-form');
    if (regForm) {
        regForm.onsubmit = (e) => {
            e.preventDefault(); // Trava o envio automático
            const name = regForm.querySelector('input[type="text"]').value;
            const email = regForm.querySelector('input[type="email"]').value;
            const pass = regForm.querySelector('input[type="password"]').value;
            
            const forte = /^(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#$%^&*]).{8,}$/;
            
            if (!forte.test(pass)) {
                alert("ERRO: A senha deve ter 8+ caracteres, 1 maiúscula, 1 número e 1 símbolo.");
                return;
            }

            // Salva e simula sucesso
            localStorage.setItem('user', JSON.stringify({ name, email }));
            alert("Conta criada com sucesso!");
            window.location.href = 'login.html';
        };
    }

    // --- 2. LÓGICA DE LOGIN ---
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.onsubmit = (e) => {
            e.preventDefault();
            const email = loginForm.querySelector('input[type="email"]').value;
            // Aqui podes adicionar lógica para validar contra o que guardaste no Register
            localStorage.setItem('user', JSON.stringify({ name: "A. Rutherford", email: email }));
            window.location.href = 'home.html';
        };
    }

    // --- 3. BUSCA DE METEOROLOGIA ---
    const searchBtn = document.getElementById('search-btn');
    const cityInput = document.getElementById('city-input');
    if (searchBtn) {
        searchBtn.onclick = async () => {
            const city = cityInput.value.trim();
            if (!city) return;
            try {
                const data = await WeatherService.getWeatherData(city);
                if (data) {
                    renderWeather(data);
                    CRUD.adicionarHistorico(city);
                }
            } catch (err) { alert("Erro ao buscar clima."); }
        };
    }

    // --- 4. LOGOUT ---
    const logoutBtn = document.getElementById('logout-btn');
    if (logoutBtn) {
        logoutBtn.onclick = () => {
            localStorage.removeItem('user');
            window.location.href = '../index.html';
        };
    }
});

// CRUD Global
window.CRUD = {
    adicionarHistorico(cidade) {
        let h = JSON.parse(localStorage.getItem('historico')) || [];
        if (!h.includes(cidade)) {
            h.unshift(cidade);
            localStorage.setItem('historico', JSON.stringify(h.slice(0, 5)));
            this.render();
        }
    },
    render() {
        const histList = document.getElementById('history-list');
        if (!histList) return;
        const hist = JSON.parse(localStorage.getItem('historico')) || [];
        histList.innerHTML = hist.map(c => `<li class="neumorphism-item">${c}</li>`).join('');
    }
};

function renderWeather(data) {
    const target = document.getElementById('weather-result');
    if (!target) return;
    target.innerHTML = `
        <div class="neumorphism weather-card" style="text-align:center; padding:20px; margin-top:20px;">
            <h3>${data.name}</h3>
            <p style="font-size: 2.5rem; font-weight: bold;">${Math.round(data.main.temp)}°C</p>
            <p>${data.weather[0].description}</p>
        </div>
    `;
}
// --- LÓGICA GLOBAL DE DARK MODE ---
const themeToggle = document.getElementById('theme-toggle');
const body = document.body;

// 1. Verificar se já existe uma preferência salva ao carregar a página
if (localStorage.getItem('theme') === 'dark') {
    body.classList.add('dark');
}

// 2. Evento de clique no botão (se o botão existir na página atual)
if (themeToggle) {
    themeToggle.addEventListener('click', () => {
        body.classList.toggle('dark');
        
        // Salvar a escolha do utilizador
        if (body.classList.contains('dark')) {
            localStorage.setItem('theme', 'dark');
        } else {
            localStorage.setItem('theme', 'light');
        }
    });
}