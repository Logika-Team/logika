<!DOCTYPE html>
<html lang="uk">
@include('partials/head.html')

<body>
  @include('partials/header.html')

  <main>
    <section class="article-section">
      <div class="container">
        <div class="article-section__wrapp">
          <div class="breadcrumbs">
            <a href="/">Головна</a> / Освітня ліцензія
          </div>

          <div class="article-section__box">
            <div class="article-section__content" style="grid-template-columns: minmax(0, 1fr);">
              <div class="article-section__editor">
                <h2 style="text-align:center;">ОСВІТНЯ ЛІЦЕНЗІЯ</h2>
                <div class="license-documents" style="display:grid;gap:16px;width:min(100%,800px);margin:16px auto 0;">
                  <img src="img/litsenziia/1.jpg.webp" alt="Освітня ліцензія, сторінка 1" style="width:100%;height:auto;display:block;">
                  <img src="img/litsenziia/4.webp" alt="Освітня ліцензія, сторінка 2" style="width:100%;height:auto;display:block;">
                  <img src="img/litsenziia/2.webp" alt="Освітня ліцензія, сторінка 2" style="width:100%;height:auto;display:block;">
                  <img src="img/litsenziia/3.webp" alt="Освітня ліцензія, сторінка 3" style="width:100%;height:auto;display:block;">
                  <img src="img/litsenziia/5.webp" alt="Освітня ліцензія, сторінка 5" style="width:100%;height:auto;display:block;">
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>

  @include('partials/footer.html')
</body>
</html>
