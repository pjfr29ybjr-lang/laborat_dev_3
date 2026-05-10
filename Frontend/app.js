import { initNavbar } from './components/navbar.js';
import { ThemeManager } from './styles/theme.js';
import { WeatherService } from './services/weather.service.js';
import { AuthGuard } from './guards/auth.guard.js';

document.addEventListener('DOMContentLoaded', async () => {
    // 1. Setup Visual e Navbar
    ThemeManager.init();
    initNavbar();

    // 2. Bloqueio de Segurança Inteligente
    const path = window.location.pathname;
    // Verifica se a página atual é login ou registro para NÃO bloquear
    const isAuthPage = path.includes('login.html') || path.includes('register.html');
    
    if (!isAuthPage) {
        if (!AuthGuard.check()) {
            console.log("Acesso negado: Redirecionando para login.");
            return; 
        }
    }

    // --- 3. LÓGICA DE REGISTO ---
    const registerForm = document.getElementById('register-form');
    if (registerForm) {
        console.log("Formulário de Registro detetado.");
        registerForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const name = registerForm.querySelectorAll('input')[0].value;
            const email = registerForm.querySelectorAll('input[1]').value; // Melhor usar querySelectorAll se não tiver ID nos inputs
            const password = registerForm.querySelectorAll('input[2]').value;

            try {
                const response = await fetch('http://localhost/weather-app/Backend/public/index.php?action=register', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ name, email, password })
                });
                const result = await response.json();
                if (response.ok) {
                    alert("Sucesso! " + result.message);
                    window.location.href = "login.html";
                } else {
                    alert("Erro: " + result.message);
                }
            } catch (error) {
                console.error("Erro no fetch:", error);
            }
        });
    }

    // --- 4. LÓGICA DE LOGIN ---
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        console.log("Formulário de Login detetado.");
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const email = loginForm.querySelector('input[type="email"]').value;
            const password = loginForm.querySelector('input[type="password"]').value;

            console.log("Tentando entrar com:", email);

            try {
                const response = await fetch('http://localhost/weather-app/Backend/public/index.php?action=login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email, password })
                });

                const result = await response.json();

                if (response.ok) {
                    localStorage.setItem('user', JSON.stringify(result.user));
                    localStorage.setItem('token', result.token);
                    alert("Bem-vindo!");
                    window.location.href = "../index.html"; 
                } else {
                    alert("Falha: " + result.message);
                }
            } catch (error) {
                console.error("Erro no login:", error);
                alert("Erro ao conectar ao servidor.");
            }
        });
    }
});