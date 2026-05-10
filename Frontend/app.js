import { initNavbar } from './components/navbar.js';
import { ThemeManager } from './styles/theme.js';

document.addEventListener('DOMContentLoaded', () => {
    // 1. Inicializa o Tema
    ThemeManager.init();

    // 2. Inicializa a Navbar (que contém o botão de tema)
    initNavbar();

    // 3. Configura o botão de troca de tema (está dentro da navbar)
    const themeBtn = document.getElementById('theme-toggle');
    if (themeBtn) {
        themeBtn.addEventListener('click', () => ThemeManager.toggle());
    }

    // 4. Lógica específica da página de Login (se existir o form)
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', (e) => {
            e.preventDefault();
            alert("Em breve: Conexão com AuthController.php!");
        });
    }
    
    // ... manter a lógica de busca da cidade que fizemos antes ...
});