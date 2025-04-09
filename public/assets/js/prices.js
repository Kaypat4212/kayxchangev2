document.addEventListener('DOMContentLoaded', () => {
    // Initialize AOS
    AOS.init({
      offset: 100,
      duration: 800,
      easing: 'ease-in-out',
      once: true,
      mirror: true,
      anchorPlacement: 'center-bottom'
    });
  
    // Fetch coins data
    const fetchCoinsData = async () => {
      try {
        const response = await fetch('https://api.coingecko.com/api/v3/coins/markets?vs_currency=usd&ids=bitcoin,ethereum,tether,litecoin,binancecoin&order=market_cap_desc&per_page=5&page=1&sparkline=false');
        const data = await response.json();
        displayCoinsData(data);
      } catch (error) {
        console.error('Error fetching coins data:', error);
      }
    };
  
  // Display coins data
const displayCoinsData = (coinsData) => {
  const pricesContainer = document.getElementById('prices-container');
  coinsData.forEach((coin, index) => {
    const priceDiv = document.createElement('div');
    priceDiv.classList.add(`price${index + 1}`);
    priceDiv.classList.add('coin-price'); // Add a CSS class for styling
  
    const coinImage = document.createElement('img');
    coinImage.setAttribute('src', coin.image);
    coinImage.setAttribute('alt', `${coin.name} logo`);
    coinImage.setAttribute('width', '23');
    coinImage.setAttribute('height', '23');
    coinImage.setAttribute('data-aos', 'flip-left');
    // coinImage.setAttribute('style', 'background-color: black; border-radius:20px; padding:1px;');
    priceDiv.appendChild(coinImage);
    priceDiv.innerHTML += '<br/>';

    const coinName = document.createElement('small');
    coinName.setAttribute('data-aos', 'zoom-in-up');
    coinName.textContent = coin.name.toUpperCase();
    coinName.classList.add('coin-name'); // Add a CSS class for styling
    priceDiv.appendChild(coinName);
    priceDiv.innerHTML += '<br/>';

    const coinPrice = document.createElement('small');
    coinPrice.setAttribute('data-aos', 'zoom-in-up');
    coinPrice.textContent = `$${coin.current_price}`;
    coinPrice.classList.add('coin-price'); // Add a CSS class for styling
    priceDiv.appendChild(coinPrice);
    priceDiv.innerHTML += '<br/>';

    pricesContainer.appendChild(priceDiv);
  });
};

// Fetch coins data
fetchCoinsData()
  .then(coinsData => {
    displayCoinsData(coinsData);
  })
  .catch(error => {
    console.error(error);
  })})
