(() => {
  const storageKey = 'logika-city-id';
  const config = window.logikaCityContextConfig || {};
  let cities = [];
  let loading = null;

  const storedId = () => {
    try {
      return localStorage.getItem(storageKey);
    } catch (error) {
      return null;
    }
  };

  const remember = (id) => {
    try {
      localStorage.setItem(storageKey, String(id));
    } catch (error) {
      // The current page still works when browser storage is unavailable.
    }
  };

  const get = () => cities.find((city) => String(city.id) === storedId()) || null;

  const load = () => {
    if (loading) return loading;
    if (!config.endpoint) return Promise.resolve(cities);
    loading = fetch(config.endpoint)
      .then((response) => response.ok ? response.json() : [])
      .then((items) => {
        cities = Array.isArray(items) ? items : [];
        return cities;
      })
      .catch(() => []);

    return loading;
  };

  const set = (city) => {
    if (!city || !city.id) return;
    remember(city.id);
    window.dispatchEvent(new CustomEvent('logika:city-change', { detail: { city } }));
  };

  window.logikaCityContext = { get, load, set };
})();
