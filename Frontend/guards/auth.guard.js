export const AuthGuard = {
    check() {
        const token = localStorage.getItem('user_token');
        const isAuthPage = window.location.pathname.includes('login.html') || 
                           window.location.pathname.includes('register.html');

        // Se NÃO tem token e NÃO está na página de login/registro -> Bloqueia
        if (!token && !isAuthPage) {
            alert("Acesso negado! Por favor, faça login primeiro.");
            window.location.href = "login.html";
            return false;
        }

        // Se JÁ tem token e tenta ir para o login -> Manda para a home
        if (token && isAuthPage) {
            window.location.href = "home.html";
            return false;
        }

        return true;
    }
};