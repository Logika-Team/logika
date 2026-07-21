(() => {
  const lightbox = document.querySelector('[data-vacancies-lightbox]');
  const image = lightbox?.querySelector('[data-vacancies-lightbox-image]');

  if (!lightbox || !image) return;

  document.addEventListener('click', (event) => {
    const trigger = event.target.closest('[data-vacancies-gallery-src]');

    if (trigger) {
      image.src = trigger.dataset.vacanciesGallerySrc;
      image.alt = trigger.querySelector('img')?.alt || '';
      lightbox.showModal();
      return;
    }

    if (event.target === lightbox || event.target.closest('[data-vacancies-lightbox-close]')) {
      lightbox.close();
    }
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && lightbox.open) {
      lightbox.close();
    }
  });
})();
