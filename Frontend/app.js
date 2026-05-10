document.addEventListener('DOMContentLoaded', () => {
    const searchBtn = document.getElementById('search-btn');
    const cityInput = document.getElementById('city-input');

    searchBtn.addEventListener('click', () => {
        const city = cityInput.value;
        if(city) {
            alert('Buscando clima para: ' + city + '. Em breve conectaremos ao PHP!');
        } else {
            alert('Por favor, digite uma cidade.');
        }
    });
});