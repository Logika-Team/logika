const cityRoot = document.querySelector('.header__location');
const cityTrigger = cityRoot ? cityRoot.querySelector('.header__location-trigger') : null;

if (cityRoot && cityTrigger && window.logikaCityContext) {
  const cityLabel = cityTrigger.querySelector('p');
  const dropdown = document.createElement('div');
  const search = document.createElement('input');
  const list = document.createElement('ul');
  const empty = document.createElement('p');
  let cities = [];

  dropdown.className = 'header__city-dropdown';
  dropdown.setAttribute('aria-hidden', 'true');
  search.className = 'header__city-search';
  search.type = 'search';
  search.placeholder = 'Пошук міста';
  search.setAttribute('aria-label', 'Пошук міста');
  list.className = 'header__city-list';
  list.setAttribute('role', 'listbox');
  empty.className = 'header__city-empty';
  empty.textContent = 'Місто не знайдено';
  empty.hidden = true;
  dropdown.append(search, list, empty);
  cityRoot.append(dropdown);
  cityTrigger.tabIndex = 0;
  cityTrigger.setAttribute('role', 'button');
  cityTrigger.setAttribute('aria-expanded', 'false');

  const initial = window.logikaCityContext.get();
  if (initial && cityLabel) cityLabel.textContent = initial.label;

  const close = () => {
    cityRoot.classList.remove('header__location--open');
    cityTrigger.setAttribute('aria-expanded', 'false');
    dropdown.setAttribute('aria-hidden', 'true');
  };
  const open = () => {
    cityRoot.classList.add('header__location--open');
    cityTrigger.setAttribute('aria-expanded', 'true');
    dropdown.setAttribute('aria-hidden', 'false');
    search.focus();
  };
  const setRegionState = (button, isOpen) => {
    const item = button.closest('.header__city-region');
    item.classList.toggle('is-open', isOpen);
    button.setAttribute('aria-expanded', String(isOpen));
  };
  const regionLabel = (label) => String(label || 'Інші міста').replace(' область', ' обл.');
  const cityOptionLabel = (label) => label === 'Онлайн' || /^м\.\s/.test(label) ? label : `м. ${label}`;
  const renderCities = (current) => {
    list.replaceChildren();
    const groups = cities.reduce((regions, city) => {
      const region = city.region || {};
      const key = region.slug || 'other';
      if (!regions[key]) regions[key] = { region, cities: [] };
      regions[key].cities.push(city);
      return regions;
    }, {});

    Object.values(groups).sort((a, b) => Number(a.region.label === 'Інші міста') - Number(b.region.label === 'Інші міста')).forEach((group) => {
      const item = document.createElement('li');
      const button = document.createElement('button');
      const citiesList = document.createElement('ul');
      const isCurrentRegion = current && group.cities.some((city) => current.id === city.id);

      item.className = 'header__city-region';
      button.className = 'header__city-region-toggle';
      button.type = 'button';
      button.textContent = regionLabel(group.region.label);
      button.setAttribute('aria-expanded', 'false');
      citiesList.className = 'header__city-region-cities';
      citiesList.hidden = true;
      button.addEventListener('click', () => {
        const nextState = !item.classList.contains('is-open');
        list.querySelectorAll('.header__city-region-toggle').forEach((region) => setRegionState(region, false));
        list.querySelectorAll('.header__city-region-cities').forEach((regionCities) => { regionCities.hidden = true; });
        setRegionState(button, nextState);
        citiesList.hidden = !nextState;
      });

      group.cities.forEach((city) => {
        const cityItem = document.createElement('li');
        const option = document.createElement('button');
        option.className = 'header__city-option';
        option.type = 'button';
        option.dataset.id = city.id;
        option.textContent = cityOptionLabel(city.label);
        option.setAttribute('role', 'option');

        if (current && current.id === city.id) {
          option.classList.add('is-active');
          option.setAttribute('aria-selected', 'true');
        }

        option.addEventListener('click', () => {
          window.logikaCityContext.set(city, true);
          close();
        });
        cityItem.append(option);
        citiesList.append(cityItem);
      });

      item.append(button, citiesList);
      list.append(item);
      if (isCurrentRegion) {
        setRegionState(button, true);
        citiesList.hidden = false;
      }
    });
  };

  window.logikaCityContext.load().then((items) => {
    cities = items;
    const current = window.logikaCityContext.get();
    renderCities(current);
    if (current && cityLabel) cityLabel.textContent = current.label;
  });

  window.addEventListener('logika:city-change', ({ detail }) => {
    const city = detail.city;
    renderCities(city);
    if (cityLabel) cityLabel.textContent = city.label;
  });
  search.addEventListener('input', () => {
    const query = search.value.trim().toLowerCase();
    let visible = 0;
    list.querySelectorAll('.header__city-region').forEach((region) => {
      const regionName = region.querySelector('.header__city-region-toggle').textContent.toLowerCase();
      const options = [...region.querySelectorAll('.header__city-option')];
      const regionMatch = regionName.includes(query);
      const matches = options.filter((option) => regionMatch || option.textContent.toLowerCase().includes(query));
      options.forEach((option) => { option.parentElement.hidden = !matches.includes(option); });
      region.hidden = query ? matches.length === 0 : false;
      if (query) {
        region.classList.toggle('is-open', matches.length > 0);
        region.querySelector('.header__city-region-toggle').setAttribute('aria-expanded', String(matches.length > 0));
        region.querySelector('.header__city-region-cities').hidden = matches.length === 0;
      }
      visible += matches.length;
    });
    empty.hidden = visible > 0;
  });
  cityTrigger.addEventListener('click', () => cityRoot.classList.contains('header__location--open') ? close() : open());
  cityTrigger.addEventListener('keydown', (event) => {
    if (event.key === 'Enter' || event.key === ' ') {
      event.preventDefault();
      cityRoot.classList.contains('header__location--open') ? close() : open();
    }
  });
  document.addEventListener('click', (event) => { if (!cityRoot.contains(event.target)) close(); });
  document.addEventListener('keydown', (event) => { if (event.key === 'Escape') close(); });
}
