document.addEventListener('DOMContentLoaded', () => {
  const count = document.querySelector('[data-article-view-count]');
  const endpoint = window.logikaArticleViews?.endpoint;
  if (!count || !endpoint) return;

  fetch(endpoint, {method: 'POST', credentials: 'same-origin', headers: {'Content-Type': 'application/json'}})
    .then((response) => response.ok ? response.json() : null)
    .then((data) => { if (data?.views !== undefined) count.textContent = data.views; });
});
