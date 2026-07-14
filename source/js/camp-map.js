document.addEventListener('DOMContentLoaded', () => {
  const map = document.querySelector('[data-school-map]');

  if (!map) return;

  const regionNames = {
    cherkasy: 'Черкаська область', chernihiv: 'Чернігівська область', chernivtsi: 'Чернівецька область',
    dnipropetrovsk: 'Дніпропетровська область', donetsk: 'Донецька область',
    'ivano-frankivsk': 'Івано-Франківська область', kharkiv: 'Харківська область', kherson: 'Херсонська область',
    khmelnytskyi: 'Хмельницька область', kirovohrad: 'Кіровоградська область', kyiv: 'Київська область',
    'kyiv-city': 'місто Київ', luhansk: 'Луганська область', lviv: 'Львівська область', mykolaiv: 'Миколаївська область',
    odessa: 'Одеська область', poltava: 'Полтавська область', rivne: 'Рівненська область', sumy: 'Сумська область',
    ternopil: 'Тернопільська область', vinnytsia: 'Вінницька область', volyn: 'Волинська область',
    zakarpattia: 'Закарпатська область', zaporizhia: 'Запорізька область', zhytomyr: 'Житомирська область'
  };
  const dnipro = {
    city: 'Дніпро',
    cities: ['Дніпро', 'Кривий Ріг', 'Жовті Води', 'Кам’янське', 'Павлоград', 'Новомосковськ', 'Перещепине', 'П’ятихатки', 'Підгородне', 'Покров', 'Синельникове', 'Солоне', 'Тернівка'],
    schools: [
      ['Підстанція', 'вул. Запорізька, 34 (ТЦ «Дафі»), Дніпро'],
      ['Перемога', 'просп. Героїв, 11 (ТРЦ «Терра»), Дніпро'],
      ['Червоний камінь', 'вул. Коробова, 5 (Центр англійської), Дніпро'],
      ['Лівий Берег', 'вул. Миру, 29 (БФ «Школа 1»), Дніпро'],
      ['Центр', 'вул. Глінки, 17 (3-й поверх), Дніпро'],
      ['12 квартал', 'вул. Василя Сліпака, 35 (Дніпропрес), Дніпро'],
      ['смт. Слобожанське', 'вул. Теплична, 27С (3-й поверх), Дніпро'],
      ['Фрунзе', 'вул. Донецьке Шосе, 9 (школа Big Ben), Дніпро']
    ]
  };
  const canvas = map.querySelector('[data-map-canvas]');
  const regionTitle = map.querySelector('[data-map-region]');
  const cities = map.querySelector('[data-map-cities]');
  const cityTitle = map.querySelector('[data-map-city-title]');
  const schools = map.querySelector('[data-map-schools]');
  const locationsCount = map.querySelector('[data-map-locations-count]');
  const frame = map.querySelector('[data-map-frame]');

  const renderDetails = (regionId, city = null) => {
    const isDnipro = regionId === 'dnipropetrovsk';
    const regionName = regionNames[regionId] || 'Область України';
    const selectedCity = city || (isDnipro ? dnipro.city : regionName);
    regionTitle.textContent = regionName;
    cityTitle.textContent = selectedCity.toUpperCase();
    locationsCount.textContent = isDnipro ? `Усі локації (${dnipro.schools.length})` : 'Локації області';
    cities.replaceChildren(...(isDnipro ? dnipro.cities : [regionName]).map((name) => {
      const button = document.createElement('button');
      button.type = 'button';
      button.textContent = name;
      button.classList.toggle('is-active', name === selectedCity);
      button.addEventListener('click', () => renderDetails(regionId, name));
      return button;
    }));
    schools.replaceChildren();
    if (isDnipro) {
      dnipro.schools.forEach(([name, address]) => {
        const item = document.createElement('li');
        item.textContent = name;
        const addressNode = document.createElement('span');
        addressNode.textContent = address;
        item.append(addressNode);
        schools.append(item);
      });
      frame.src = `https://www.google.com/maps?q=${encodeURIComponent(selectedCity + ', Ukraine')}&output=embed`;
      frame.title = `Карта шкіл у місті ${selectedCity}`;
    } else {
      const item = document.createElement('li');
      item.textContent = 'Оберіть місто для перегляду шкіл';
      const note = document.createElement('span');
      note.textContent = 'Каталог шкіл для цієї області ще не підключено.';
      item.append(note);
      schools.append(item);
      frame.src = `https://www.google.com/maps?q=${encodeURIComponent(regionName + ', Ukraine')}&output=embed`;
      frame.title = `Карта ${regionName}`;
    }
  };

  const selectRegion = (regionId) => {
    canvas.querySelectorAll('path[data-region]').forEach((path) => {
      const active = path.dataset.region === regionId;
      path.classList.toggle('is-active', active);
      path.setAttribute('aria-pressed', String(active));
    });
    renderDetails(regionId);
  };

  fetch('img/maps/ukraine-regions.svg')
    .then((response) => {
      if (!response.ok) throw new Error('Map asset could not be loaded');
      return response.text();
    })
    .then((svg) => {
      canvas.innerHTML = svg;
      canvas.querySelectorAll('path[id]').forEach((path) => {
        const regionId = path.id;
        if (!regionNames[regionId]) return;
        path.dataset.region = regionId;
        path.setAttribute('role', 'button');
        path.setAttribute('tabindex', '0');
        path.setAttribute('aria-label', regionNames[regionId]);
        path.addEventListener('click', () => selectRegion(regionId));
        path.addEventListener('keydown', (event) => {
          if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            selectRegion(regionId);
          }
        });
      });
      selectRegion('dnipropetrovsk');
    })
    .catch(() => {
      canvas.textContent = 'Не вдалося завантажити карту областей.';
    });

  map.querySelectorAll('[data-map-mode]').forEach((button) => {
    button.addEventListener('click', () => {
      map.querySelectorAll('[data-map-mode]').forEach((item) => item.classList.toggle('is-active', item === button));
    });
  });
});
