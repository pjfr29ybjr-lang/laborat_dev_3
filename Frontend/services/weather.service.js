export const WeatherService = {
    // Substitua pela sua chave real quando a tiver
    apiKey: 'e6332f743042227acae8c1265ed9ee14', 

    async getWeatherData(city) {
        try {
            // URL real da API (usando unidades métricas para Celsius)
            const url = `https://api.openweathermap.org/data/2.5/weather?q=${city}&units=metric&lang=pt_br&appid=${this.apiKey}`;
            
            const response = await fetch(url);
            if (!response.ok) throw new Error("Cidade não encontrada");
            
            const data = await response.json();
            
            return {
                city: data.name,
                temp: Math.round(data.main.temp),
                condition: data.weather[0].description,
                humidity: data.main.humidity + "%",
                icon: data.weather[0].icon
            };
        } catch (error) {
            console.error("Erro na busca:", error);
            return null;
        }
    }
};