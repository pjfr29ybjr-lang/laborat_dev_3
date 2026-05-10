export const WeatherService = {
    async getWeatherData(city) {
        // Por enquanto, simulamos uma resposta. 
        // No futuro, aqui teremos o fetch('backend/api.php...')
        return {
            temp: 30,
            condition: "Ensolarado",
            city: city
        };
    }
};