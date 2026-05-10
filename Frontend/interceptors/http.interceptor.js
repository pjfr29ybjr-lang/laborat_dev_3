export const HttpInterceptor = {
    async request(url, options = {}) {
        // Adiciona o Token automaticamente em todas as chamadas para o Backend
        const token = localStorage.getItem('user_token');
        
        const headers = {
            'Content-Type': 'application/json',
            ...options.headers
        };

        if (token) {
            headers['Authorization'] = `Bearer ${token}`;
        }

        try {
            const response = await fetch(url, { ...options, headers });

            // Se o servidor responder 401 (Não autorizado), desloga na hora
            if (response.status === 401) {
                localStorage.clear();
                window.location.href = "../pages/login.html";
                return null;
            }

            return response;
        } catch (error) {
            console.error("Erro na comunicação com o servidor:", error);
            throw error;
        }
    }
};