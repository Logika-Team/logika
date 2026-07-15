(() => {
  const storageKey = 'logika-city';
  const legacyStorageKey = 'logika-city-id';
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
    try {
      localStorage.setItem(storageKey, JSON.stringify(city));
      localStorage.setItem(legacyStorageKey, String(city.id));
    } catch (error) {
      // The current page still works when browser storage is unavailable.
    }
  };

  const cityFromPath = () => {
    const match = window.location.pathname.match(/^\/cities\/([^/]+)(?:\/|$)/);
    const cached = cachedCity();
    return match ? cities.find((city) => new URL(city.url, window.location.origin).pathname === `/cities/${match[1]}/`)
      || (cached?.url && new URL(cached.url, window.location.origin).pathname === `/cities/${match[1]}/` ? cached : null) : null;
  };

  const get = () => cityFromPath() || cities.find((city) => String(city.id) === String(storedId())) || cachedCity();

  const cityUrl = (city, destination = window.location.href) => {
    if (!city?.url) return '';
    const url = new URL(city.url, window.location.origin);
    const current = new URL(destination, window.location.origin);
    const path = current.pathname.replace(/^\/cities\/[^/]+(?:\/|$)/, '/').replace(/^\/+/, '');
    url.pathname = `${url.pathname.replace(/\/?$/, '/')}${path}`;
    url.search = current.search;
    url.hash = current.hash;
    return url.href;
  };

  const syncLinks = (city) => {
    if (!city) return;
    document.querySelectorAll('a[href]').forEach((anchor) => {
      const href = anchor.dataset.logikaOriginalHref || anchor.getAttribute('href');
      if (!href || '#' === href) return;
      const url = new URL(href, window.location.origin);
      if (url.origin !== window.location.origin || /^\/(?:wp-admin|wp-login|wp-json)/.test(url.pathname)) return;
      anchor.dataset.logikaOriginalHref = href;
      anchor.href = cityUrl(city, url.href);
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
          syncLinks(city);
        }
        return cities;
      })
      .catch(() => []);

    return loading;
  };

  const set = (city, updateUrl = false) => {
    if (!city || !city.id) return;
    remember(city);
    const url = updateUrl && cityUrl(city);
    if (url && window.history?.pushState) window.history.pushState({ cityId: city.id }, '', url);
    syncLinks(city);
    window.dispatchEvent(new CustomEvent('logika:city-change', { detail: { city } }));
  };

  const initial = get();
  if (initial) syncLinks(initial);

  window.addEventListener('popstate', () => {
    const city = get();
    if (city) {
      syncLinks(city);
      window.dispatchEvent(new CustomEvent('logika:city-change', { detail: { city } }));
    }
  });

  window.logikaCityContext = { get, load, set, url: cityUrl };
})();
