(() => {
  const dialog = document.querySelector('[data-vacancies-details-dialog]');
  const title = dialog?.querySelector('[data-vacancies-details-title]');
  const content = dialog?.querySelector('[data-vacancies-details-content]');

  if (!dialog || !title || !content) return;

  document.addEventListener('click', (event) => {
    const trigger = event.target.closest('[data-vacancies-details-open]');

    if (trigger) {
      const template = document.querySelector(`[data-vacancies-details-template="${trigger.dataset.vacanciesDetailsOpen}"]`);
      if (!template) return;
      title.textContent = trigger.dataset.vacanciesDetailsTitle || '';
      content.replaceChildren(template.content.cloneNode(true));
      dialog.showModal();
      return;
    }

    if (event.target === dialog || event.target.closest('[data-vacancies-details-close]')) dialog.close();
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && dialog.open) dialog.close();
  });
})();
