(() => {
  const section = document.querySelector('[data-city-home-seo]');
  const context = window.logikaCityContext;
  const endpoint = window.logikaHomepageCitySeo?.endpoint;

  if (!section || !context || !endpoint) return;

  const title = section.querySelector('[data-city-home-seo-title]');
  const description = section.querySelector('[data-city-home-seo-description]');
  const cta = section.querySelector('[data-city-home-seo-cta-label]');
  const illustration = section.querySelector('[data-city-home-seo-illustration]');
  const poster = section.querySelector('[data-city-home-seo-video-poster]');
  const caption = section.querySelector('[data-city-home-seo-video-caption]');
  const play = section.querySelector('[data-city-home-seo-video-play]');
  const frame = section.querySelector('[data-city-home-seo-video-frame]');
  let request = 0;
  let embedUrl = '';

  const hide = () => { section.hidden = true; };
  const youtubeEmbed = (value) => {
    try {
      const url = new URL(value);
      const host = url.hostname.replace(/^www\./, '');
      const id = host === 'youtu.be' ? url.pathname.slice(1) : (host === 'youtube.com' || host === 'm.youtube.com') ? url.searchParams.get('v') || url.pathname.split('/')[2] : '';
      return /^[\w-]{11}$/.test(id || '') ? `https://www.youtube-nocookie.com/embed/${id}?autoplay=1&rel=0` : '';
    } catch (error) {
      return '';
    }
  };
  const image = (element, value) => {
    element.src = value.url;
    element.alt = value.alt || '';
  };
  const paragraphs = (value) => value.split(/\n{2,}/).filter(Boolean).map((text) => {
    const paragraph = document.createElement('p');
    paragraph.textContent = text;
    return paragraph;
  });
  const render = (data) => {
    if (!data) return hide();
    embedUrl = youtubeEmbed(data.video.url);
    if (!embedUrl) return hide();
    title.textContent = data.title;
    description.replaceChildren(...paragraphs(data.description));
    cta.textContent = data.cta_label;
    image(illustration, data.illustration);
    image(poster, data.video.poster);
    caption.textContent = data.video.caption;
    frame.removeAttribute('src');
    frame.hidden = true;
    frame.title = `Відео: ${data.video.caption}`;
    play.hidden = false;
    play.setAttribute('aria-label', `Відтворити відео: ${data.title}`);
    section.hidden = false;
  };
  const load = (city) => {
    const current = ++request;
    hide();
    if (!city?.id) return;
    fetch(`${endpoint}${encodeURIComponent(city.id)}/homepage-seo`)
      .then((response) => response.ok ? response.json() : null)
      .then((data) => { if (current === request) render(data); })
      .catch(() => { if (current === request) hide(); });
  };

  window.addEventListener('logika:city-change', ({ detail }) => load(detail?.city));
  play.addEventListener('click', () => {
    if (!embedUrl) return;
    frame.src = embedUrl;
    frame.hidden = false;
    play.hidden = true;
  });
  context.load().then(() => load(context.get())).catch(hide);
})();
