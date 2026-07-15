document.addEventListener('DOMContentLoaded', () => {
  const root = document.querySelector('main');
  const featured = root?.querySelector('[data-media-featured]');
  const list = root?.querySelector('[data-media-list]');
  const searchForm = root?.querySelector('[data-media-search-form]');
  const searchInput = root?.querySelector('[data-media-search-input]');
  const suggestions = root?.querySelector('[data-media-search-suggestions]');
  const newsTitle = root?.querySelector('.news-section__title');
  const defaultNewsTitle = newsTitle?.textContent || 'Новини';
  const context = window.logikaCityContext;
  const config = window.logikaMediaCenter || {};
  let requestId = 0;
  let suggestionRequestId = 0;
  let suggestionTimer = 0;

  if (!root || !featured || !list || !context || !config.endpoint) return;

  const link = (className, card) => {
    const element = document.createElement('a');
    element.className = className;
    element.href = card.url;
    element.textContent = card.title;
    return element;
  };
  const thumbnail = (card, width, height) => {
    const element = document.createElement('div');
    const picture = document.createElement('picture');
    const image = document.createElement('img');
    element.className = 'news-card__thumbnail';
    image.src = card.image || '';
    image.alt = card.title;
    image.width = width;
    image.height = height;
    picture.append(image);
    element.append(picture);
    return element;
  };
  const tags = () => {
    const element = document.createElement('ul');
    const item = document.createElement('li');
    element.className = 'news-card__tags';
    item.className = 'news-card__tag';
    item.textContent = 'Logika Блог';
    element.append(item);
    return element;
  };
  const details = (card) => {
    const element = document.createElement('ul');
    const item = document.createElement('li');
    const date = document.createElement('p');
    element.className = 'news-card__details';
    date.textContent = card.date;
    item.append(date);
    element.append(item);
    return element;
  };
  const hideSuggestions = () => {
    if (!suggestions) return;
    suggestions.hidden = true;
    suggestions.replaceChildren();
  };
  const renderSuggestions = (cards, search) => {
    if (!suggestions || !search) return hideSuggestions();
    suggestions.replaceChildren();
    if (!cards.length) {
      const empty = document.createElement('p');
      empty.className = 'search-form__suggestions-empty';
      empty.textContent = 'За вашим запитом статей не знайдено.';
      suggestions.append(empty);
    } else {
      cards.slice(0, 5).forEach((card) => {
        const item = document.createElement('a');
        const title = document.createElement('span');
        const date = document.createElement('span');
        item.className = 'search-form__suggestion';
        item.href = card.url;
        item.setAttribute('role', 'option');
        title.className = 'search-form__suggestion-title';
        date.className = 'search-form__suggestion-date';
        title.textContent = card.title;
        date.textContent = card.date;
        item.append(title, date);
        suggestions.append(item);
      });
    }
    suggestions.hidden = false;
  };
  const loadSuggestions = () => {
    const search = searchInput?.value.trim() || '';
    window.clearTimeout(suggestionTimer);
    if (!search) return hideSuggestions();
    suggestionTimer = window.setTimeout(() => {
      const id = ++suggestionRequestId;
      const url = new URL(config.endpoint, window.location.origin);
      const city = context.get();
      if (city) url.searchParams.set('city', city.id);
      url.searchParams.set('search', search);
      fetch(url)
        .then((response) => response.ok ? response.json() : [])
        .then((cards) => { if (id === suggestionRequestId) renderSuggestions(Array.isArray(cards) ? cards : [], search); })
        .catch(() => { if (id === suggestionRequestId) hideSuggestions(); });
    }, 250);
  };
  const render = (cards, search) => {
    featured.replaceChildren();
    list.replaceChildren();
    if (newsTitle) newsTitle.textContent = search ? 'Результати пошуку' : defaultNewsTitle;
    if (!cards.length) {
      const empty = document.createElement('p');
      empty.textContent = search ? 'За вашим запитом статей не знайдено.' : 'Для цього міста поки немає матеріалів.';
      featured.append(empty);
      return;
    }

    const card = cards[0];
    const article = document.createElement('div');
    const info = document.createElement('div');
    const top = document.createElement('div');
    const description = document.createElement('p');
    article.className = 'news-card';
    info.className = 'news-card__info';
    top.className = 'news-card__top';
    description.className = 'news-card__descr';
    description.textContent = card.excerpt;
    top.append(tags(), details(card));
    info.append(top, link('news-card__title', card), description);
    article.append(thumbnail(card, 832, 430), info);
    featured.append(article);

    cards.slice(1).forEach((item) => {
      const row = document.createElement('li');
      const news = document.createElement('div');
      const info = document.createElement('div');
      row.className = 'news-section__item';
      news.className = 'news-card';
      info.className = 'news-card__info';
      info.append(tags(), link('news-card__title', item), details(item));
      news.append(thumbnail(item, 265, 202), info);
      row.append(news);
      list.append(row);
    });
  };
  const refresh = (city, search = searchInput?.value.trim() || '') => {
    const id = ++requestId;
    const url = new URL(config.endpoint, window.location.origin);
    if (city) url.searchParams.set('city', city.id);
    if (search) url.searchParams.set('search', search);
    root.setAttribute('aria-busy', 'true');
    fetch(url)
      .then((response) => response.ok ? response.json() : [])
      .then((cards) => { if (id === requestId) render(Array.isArray(cards) ? cards : [], search); })
      .catch(() => { if (id === requestId) render([], search); })
      .finally(() => { if (id === requestId) root.removeAttribute('aria-busy'); });
  };

  searchForm?.addEventListener('submit', (event) => {
    event.preventDefault();
    hideSuggestions();
    refresh(context.get());
  });
  searchInput?.addEventListener('input', loadSuggestions);
  searchInput?.addEventListener('keydown', (event) => { if ('Escape' === event.key) hideSuggestions(); });
  searchForm?.addEventListener('focusout', () => window.setTimeout(hideSuggestions, 150));
  context.load().then(() => refresh(context.get()));
  window.addEventListener('logika:city-change', ({ detail }) => { hideSuggestions(); refresh(detail.city); });
});
