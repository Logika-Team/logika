document.addEventListener('DOMContentLoaded', () => {
  const root = document.querySelector('main');
  const isBlog = root?.querySelector('[data-media-blog]');
  const featured = root?.querySelector('[data-media-featured]');
  const list = root?.querySelector('[data-media-list]');
  const articles = root?.querySelector('[data-media-articles]');
  const sort = root?.querySelector('[data-media-sort]');
  const year = root?.querySelector('[data-media-year]');
  const yearOptions = root?.querySelector('[data-media-year-options]');
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
  let cards = [];

  if (!root || !list || (!isBlog && !featured) || !context || !config.endpoint) return;

  const link = (className, card) => {
    const element = document.createElement('a');
    element.className = className;
    element.href = card.url;
    element.textContent = card.title;
    return element;
  };
  const thumbnail = (card, width, height) => {
    const element = document.createElement('a');
    const picture = document.createElement('picture');
    const image = document.createElement('img');
    element.className = 'news-card__thumbnail';
    element.href = card.url;
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
  const renderArticles = (cards) => {
    if (!articles) return;
    articles.replaceChildren();
    cards.slice(0, 3).forEach((card) => {
      const item = document.createElement('li');
      const article = document.createElement('article');
      const info = document.createElement('div');
      const image = thumbnail(card, 465, 235);
      const excerpt = document.createElement('p');
      const date = document.createElement('p');
      item.className = 'articles-section__item';
      article.className = 'article-card';
      info.className = 'article-card__info';
      image.className = 'article-card__thumbnail';
      excerpt.className = 'article-card__excerpt';
      date.className = 'article-card__date';
      excerpt.textContent = card.excerpt;
      date.textContent = card.date;
      info.append(link('article-card__title', card), excerpt, date);
      article.append(image, info);
      item.append(article);
      articles.append(item);
    });
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
    renderArticles(cards);
    if (isBlog) return renderBlog(cards);
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
  const renderBlog = (items) => {
    cards = items;
    const years = [...new Set(cards.map((card) => card.date.slice(-4)))].sort().reverse();
    const currentYear = year?.value || '';
    if (yearOptions) {
      yearOptions.replaceChildren();
      [['', 'Усі роки'], ...years.map((value) => [value, value])].forEach(([value, label]) => {
        const item = document.createElement('li');
        const option = document.createElement('button');
        option.className = 'main-form__age-option';
        option.type = 'button';
        option.setAttribute('role', 'option');
        option.setAttribute('aria-selected', String(value === currentYear));
        option.dataset.mediaFilterOption = value;
        option.textContent = label;
        item.append(option);
        yearOptions.append(item);
      });
    }
    if (year) year.value = currentYear;
    const visible = cards
      .filter((card) => !year?.value || card.date.endsWith(year.value))
      .sort((a, b) => (sort?.value === 'old' ? 1 : -1) * a.date.split('.').reverse().join('').localeCompare(b.date.split('.').reverse().join('')));
    list.replaceChildren();
    if (!visible.length) {
      const empty = document.createElement('p');
      empty.textContent = 'Статей за обраними фільтрами не знайдено.';
      list.append(empty);
      return;
    }
    visible.forEach((card) => {
      const item = document.createElement('li');
      const article = document.createElement('article');
      const info = document.createElement('div');
      article.className = 'article-card';
      info.className = 'article-card__info';
      const image = thumbnail(card, 465, 235);
      image.className = 'article-card__thumbnail';
      info.append(link('article-card__title', card));
      const excerpt = document.createElement('p');
      excerpt.className = 'article-card__excerpt';
      excerpt.textContent = card.excerpt;
      const date = document.createElement('p');
      date.className = 'article-card__date';
      date.textContent = card.date;
      info.append(excerpt, date);
      item.append(article);
      article.append(image, info);
      list.append(item);
    });
  };
  const refresh = (city, search = searchInput?.value.trim() || '') => {
    const id = ++requestId;
    const url = new URL(config.endpoint, window.location.origin);
    if (city) url.searchParams.set('city', city.id);
    if (search) url.searchParams.set('search', search);
    if (isBlog) url.searchParams.set('all', '1');
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
  const closeFilters = () => root.querySelectorAll('[data-media-filter]').forEach((filter) => {
    filter.classList.remove('is-open');
    filter.querySelector('[data-media-filter-trigger]')?.setAttribute('aria-expanded', 'false');
    const dropdown = filter.querySelector('[role="listbox"]');
    if (dropdown) dropdown.hidden = true;
  });
  root.addEventListener('click', (event) => {
    const trigger = event.target.closest('[data-media-filter-trigger]');
    if (trigger) {
      const filter = trigger.closest('[data-media-filter]');
      const open = !filter.classList.contains('is-open');
      closeFilters();
      filter.classList.toggle('is-open', open);
      trigger.setAttribute('aria-expanded', String(open));
      filter.querySelector('[role="listbox"]').hidden = !open;
      return;
    }
    const option = event.target.closest('[data-media-filter-option]');
    if (!option) return;
    const filter = option.closest('[data-media-filter]');
    const input = filter.querySelector('input');
    input.value = option.dataset.mediaFilterOption;
    filter.querySelector('.main-form__age-label').textContent = option.textContent;
    filter.querySelectorAll('[data-media-filter-option]').forEach((item) => item.setAttribute('aria-selected', String(item === option)));
    closeFilters();
    renderBlog(cards);
  });
  document.addEventListener('click', (event) => { if (!event.target.closest('[data-media-filter]')) closeFilters(); });
  document.addEventListener('keydown', (event) => { if ('Escape' === event.key) closeFilters(); });
  context.load().then(() => refresh(context.get()));
  window.addEventListener('logika:city-change', ({ detail }) => { hideSuggestions(); refresh(detail.city); });
});
