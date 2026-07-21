(() => {
  const storageKey = 'logika-city';
  const legacyStorageKey = 'logika-city-id';
  const cookieName = 'logika_city';
  const config = window.logikaCityContextConfig || {};
  let cities = [];
  let loading = null;

  const cachedCity = () => {
    try {
      const city = JSON.parse(localStorage.getItem(storageKey) || 'null');
      return city?.id ? city : null;
    } catch (error) {
      return null;
    }
  };

  const storedId = () => cachedCity()?.id || (() => {
    try {
      return localStorage.getItem(legacyStorageKey);
    } catch (error) {
      return null;
    }
  })();

  const remember = (city) => {
	  document.cookie = `${cookieName}=${encodeURIComponent(String(city.id))}; Path=/; Max-Age=31536000; SameSite=Lax`;
    try {
      localStorage.setItem(storageKey, JSON.stringify(city));
      localStorage.setItem(legacyStorageKey, String(city.id));
    } catch (error) {
      // The current page still works when browser storage is unavailable.
    }
  };

  const forget = () => {
	  document.cookie = `${cookieName}=; Path=/; Max-Age=0; SameSite=Lax`;
    try {
      localStorage.removeItem(storageKey);
      localStorage.removeItem(legacyStorageKey);
    } catch (error) {
      // The current page still works when browser storage is unavailable.
    }
  };

  const cityFromPath = () => {
    const match = window.location.pathname.match(/^\/cities\/([^/]+)(?:\/|$)/);
    const cached = cachedCity();
    if (!match) return null;
    const city = cities.find((item) => new URL(item.url, window.location.origin).pathname === `/cities/${match[1]}/`);
    if (city || cities.length) return city || null;
    return cached?.url && new URL(cached.url, window.location.origin).pathname === `/cities/${match[1]}/` ? cached : null;
  };

  const get = () => cityFromPath() || cities.find((city) => String(city.id) === String(storedId())) || cachedCity();
  const isHomepage = () => /^\/(?:cities\/[^/]+\/?)?$/.test(window.location.pathname);
  const applyHero = (city) => {
    if (!isHomepage() || !city?.hero) return;
    const title = document.querySelector('.banner-section__title');
    const text = document.querySelector('.banner-section__subtitle');
    if (title && city.hero.title) title.textContent = city.hero.title;
    if (text && city.hero.text) text.textContent = city.hero.text;
  };
  const applyTopCity = (city) => {
    const topCity = document.querySelector('[data-logika-city-top]');
    if (!topCity) return;
    topCity.hidden = !city?.label;
    topCity.querySelector('span').textContent = city?.label ? `місто ${city.label}` : '';
  };

  const syncHomeLinks = (city) => {
    if (!city?.url) return;
    document.querySelectorAll('a[href]').forEach((anchor) => {
      const href = anchor.dataset.logikaOriginalHref || anchor.getAttribute('href');
      if (!href) return;
      const url = new URL(href, window.location.origin);
      if (url.origin !== window.location.origin || url.pathname !== '/' || url.search || url.hash) return;
      anchor.dataset.logikaOriginalHref = href;
      anchor.href = city.url;
    });
  };

  const load = () => {
    if (loading) return loading;
    if (!config.endpoint) return Promise.resolve(cities);
    loading = fetch(config.endpoint)
      .then((response) => response.ok ? response.json() : [])
      .then((items) => {
        cities = Array.isArray(items) ? items : [];
        const city = cityFromPath() || cities.find((item) => String(item.id) === String(storedId()));
        if (city) {
          remember(city);
          syncHomeLinks(city);
          applyHero(city);
          applyTopCity(city);
        } else if (cities.length) {
          forget();
          if (/^\/cities\/[^/]+(?:\/|$)/.test(window.location.pathname)) window.location.replace('/');
        }
        return cities;
      })
      .catch(() => []);

    return loading;
  };

  const set = (city, openCityPage = false) => {
    if (!city || !city.id) return;
    remember(city);
    syncHomeLinks(city);
    applyHero(city);
    applyTopCity(city);
    window.dispatchEvent(new CustomEvent('logika:city-change', { detail: { city } }));
    if (openCityPage && city.url) {
      if (isHomepage() && window.history?.replaceState) window.history.replaceState({ cityId: city.id }, '', city.url);
      else window.location.assign( city.url );
    }
  };

  const initial = get();
  if (initial) {
    syncHomeLinks(initial);
    applyHero(initial);
    applyTopCity(initial);
  }

  window.addEventListener('popstate', () => {
    const city = get();
    if (city) {
      syncHomeLinks(city);
      applyHero(city);
      applyTopCity(city);
      window.dispatchEvent(new CustomEvent('logika:city-change', { detail: { city } }));
    }
  });

  window.logikaCityContext = { get, load, set };
})();
