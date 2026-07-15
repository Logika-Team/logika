document.addEventListener('DOMContentLoaded', () => {
  const map = document.querySelector('[data-school-map]');

  if (!map) return;
  const cityContext = window.logikaCityContext || {
    get: () => null,
    load: () => Promise.resolve([]),
    set: () => {}
  };

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
  const canvas = map.querySelector('[data-map-canvas]');
  const layout = map.querySelector('.school-map__layout');
  const details = map.querySelector('[data-map-details]');
  const regionTitle = map.querySelector('[data-map-region]');
  const cities = map.querySelector('[data-map-cities]');
  const cityTitle = map.querySelector('[data-map-city-title]');
  const schools = map.querySelector('[data-map-schools]');
  const locationsCount = map.querySelector('[data-map-locations-count]');
  const frame = map.querySelector('[data-map-frame]');
  const config = { mapUrl: 'img/maps/ukraine-regions.svg', ...(window.logikaThemeAssets || {}) };
  const onlinePanel = document.createElement('div');
  const heroForm = document.querySelector('.banner-section__form[data-logika-lead-form], .cta-form[data-logika-lead-form]');
  const formOrigin = heroForm?.parentNode;
  const formNextSibling = heroForm?.nextSibling;
  let selectedCity = null;

  onlinePanel.className = 'school-map__online';
  onlinePanel.hidden = true;
  layout?.before(onlinePanel);
  details.hidden = true;

  const requestJson = (url) => fetch(url).then((response) => {
    if (!response.ok) throw new Error('Map data could not be loaded');
    return response.json();
  });

  const fetchMap = () => fetch(config.mapUrl).then((response) => {
    if (!response.ok) throw new Error('Map asset could not be loaded');
    return response.text();
  });

  const setFrame = (city, branches) => {
    const branch = branches.find(({ lat, lng }) => lat || lng);
    const lat = branch?.lat || city.lat;
    const lng = branch?.lng || city.lng;
    const mapUrl = branch?.map_url;

    if (lat || lng) {
      frame.src = `https://www.google.com/maps?q=${encodeURIComponent(`${lat},${lng}`)}&output=embed`;
      frame.title = `Карта шкіл у місті ${city.label}`;
    } else if (mapUrl) {
      frame.src = mapUrl;
      frame.title = `Карта шкіл у місті ${city.label}`;
    } else {
      frame.removeAttribute('src');
      frame.title = `Карта шкіл у місті ${city.label}`;
    }
  };

  const renderBranches = (city, branches) => {
    schools.replaceChildren();
    locationsCount.textContent = branches.length ? `Усі локації (${branches.length})` : 'Локації відсутні';

    if (!branches.length) {
      const item = document.createElement('li');
      item.textContent = 'У цьому місті поки немає доступних філій.';
      schools.append(item);
    }

    branches.forEach((branch) => {
      const item = document.createElement('li');
      item.textContent = branch.label;
      if (branch.address) {
        const address = document.createElement('span');
        address.textContent = branch.address;
        item.append(address);
      }
      schools.append(item);
    });
    setFrame(city, branches);
  };

  const selectCity = (city, persist = true) => {
    selectedCity = city;
    if (persist) cityContext.set(city);
    cityTitle.textContent = city.label.toUpperCase();
    details.hidden = false;
    locationsCount.textContent = 'Завантажуємо локації...';
    schools.replaceChildren();
    cities.querySelectorAll('button').forEach((button) => button.classList.toggle('is-active', button.dataset.cityId === String(city.id)));

    const branches = config.branchesEndpoint ? requestJson(`${config.branchesEndpoint}${city.id}/branches`) : Promise.resolve([]);

    branches
      .then((branches) => {
        if (selectedCity === city) renderBranches(city, branches);
      })
      .catch(() => {
        if (selectedCity !== city) return;
        locationsCount.textContent = 'Локації недоступні';
        schools.replaceChildren();
        const item = document.createElement('li');
        item.textContent = 'Не вдалося завантажити локації міста.';
        schools.append(item);
      });
  };

  const renderRegion = (regionId, citiesByRegion) => {
    const regionCities = citiesByRegion.get(regionNames[regionId]) || [];
    regionTitle.textContent = regionNames[regionId];
    cityTitle.textContent = 'ОБЕРІТЬ МІСТО';
    details.hidden = true;
    selectedCity = null;
    cities.replaceChildren(...regionCities.map((city) => {
      const button = document.createElement('button');
      button.type = 'button';
      button.textContent = city.label;
      button.dataset.cityId = city.id;
      button.addEventListener('click', () => selectCity(city));
      return button;
    }));
  };

  const selectRegion = (regionId, citiesByRegion) => {
    canvas.querySelectorAll('path[data-region]').forEach((path) => {
      const active = path.dataset.region === regionId;
      path.classList.toggle('is-active', active);
      path.setAttribute('aria-pressed', String(active));
    });
    renderRegion(regionId, citiesByRegion);
  };

  const moveHeroForm = () => {
    if (!heroForm) return;
    onlinePanel.append(heroForm);
    onlinePanel.hidden = false;
    layout.hidden = true;
    details.hidden = true;
  };

  const restoreHeroForm = () => {
    if (!heroForm || !formOrigin) return;
    formOrigin.insertBefore(heroForm, formNextSibling);
    onlinePanel.hidden = true;
    layout.hidden = false;
    details.hidden = !selectedCity;
  };

  const setMode = (mode) => {
    map.querySelectorAll('[data-map-mode]').forEach((button) => button.classList.toggle('is-active', button.dataset.mapMode === mode));
    if ('online' === mode) moveHeroForm();
    else restoreHeroForm();
  };

  map.querySelectorAll('[data-map-mode]').forEach((button) => {
    button.addEventListener('click', () => setMode(button.dataset.mapMode));
  });

  if (!config.mapUrl) return;

  Promise.all([fetchMap(), cityContext.load()])
    .then(([svg, cityList]) => {
      const citiesByRegion = cityList.reduce((groups, city) => {
        const label = city.region?.label;
        if (!label) return groups;
        groups.set(label, [...(groups.get(label) || []), city]);
        return groups;
      }, new Map());
      const selectContextCity = (city) => {
        const regionId = city?.region?.slug;
        if (!city || !regionId || !regionNames[regionId]) return;
        selectRegion(regionId, citiesByRegion);
        selectCity(city, false);
      };

      canvas.innerHTML = svg;
      canvas.querySelectorAll('path[id]').forEach((path) => {
        const regionId = path.id;
        if (!regionNames[regionId] || !citiesByRegion.has(regionNames[regionId])) return;
        path.dataset.region = regionId;
        path.setAttribute('role', 'button');
        path.setAttribute('tabindex', '0');
        path.setAttribute('aria-label', regionNames[regionId]);
        path.setAttribute('aria-pressed', 'false');
        path.addEventListener('click', () => selectRegion(regionId, citiesByRegion));
        path.addEventListener('keydown', (event) => {
          if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            selectRegion(regionId, citiesByRegion);
          }
        });
      });
      regionTitle.textContent = 'Оберіть область';
      cities.replaceChildren();
      selectContextCity(cityContext.get());
      window.addEventListener('logika:city-change', ({ detail }) => selectContextCity(detail.city));
    })
    .catch(() => {
      canvas.textContent = 'Не вдалося завантажити карту областей.';
    });
});
