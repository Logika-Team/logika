<!DOCTYPE html>
<html lang="en">
@include('partials/head.html')

<body>
  @include('partials/header.html')

  <main>
    <section class="faq-banner-section">
      <div class="container">
        <div class="faq-banner-section__wrapp">
          <div class="breadcrumbs">
            <a href="/">Головна</a> / Питання-відповідь
          </div>

          <div class="faq-banner-section__blocks">
            <div class="faq-banner-section__left">
              <span class="faq-banner-section__label h5">FAQ</span>

              <div class="faq-banner-section__info">
                <h1 class="faq-banner-section__title">Часті запитання про навчання в Logika</h1>
                <p class="faq-banner-section__text">Зібрали відповіді на найпоширеніші запитання батьків і дітей про курси, формати навчання, розклад, вартість, викладачів, проєкти, табори та можливості приєднатися до команди Logika.</p>
              </div>

              <div class="faq-banner-section__btns">
                <a class="faq-banner-section__btn btn btn--bordered-violet" href="#faq">
                  Найпоширеніші питання
                  <svg width='20' height='20'>
                    <use href='img/sprite/sprite.svg#icon-more'></use>
                  </svg>
                </a>
                <a class="faq-banner-section__btn btn btn--violet" href="#lead-form">
                  Безкоштовний пробний урок
                  <svg width='20' height='20'>
                    <use href='img/sprite/sprite.svg#arrow-right'></use>
                  </svg>
                </a>
              </div>
            </div>

            <div class="faq-banner-section__right">
              <div class="faq-banner-section__media">
                <img width='483' height='479' src='img/faq/faq-image.svg' alt='boy-character'>
              </div>
              <div class="faq-banner-section__character-logika">
                <img width='110' height='136' src='img/faq/faq-icon.svg' alt='boy-character'>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <?php
      get_template_part(
        'template-parts/sections/faq',
        null,
        array(
          'section_id'           => 'faq',
          'section_title'        => 'Найпоширеніші питання',
          'with_backgrounds'     => true,
          'accordion_class'      => 'accordion accordion--mode',
          'accordion_breakpoint' => '1920',
          'fallback_faq_items'   => array(
            array(
              'question' => 'З якого віку дитина може навчатися в Logika?',
              'answer'   => 'Вартість залежить від обраного формату навчання, пакета занять, міста, можливих пільг і поточних знижок. Залиште заявку, і менеджер допоможе підібрати оптимальний варіант та розрахує фінальну вартість.',
            ),
            array(
              'question' => 'З якого віку можна навчатися в Logika?',
              'answer'   => 'Вартість залежить від обраного формату навчання, пакета занять, міста, можливих пільг і поточних знижок. Залиште заявку, і менеджер допоможе підібрати оптимальний варіант та розрахує фінальну вартість.',
            ),
            array(
              'question' => 'Чи потрібен досвід у програмуванні або англійській?',
              'answer'   => 'Вартість залежить від обраного формату навчання, пакета занять, міста, можливих пільг і поточних знижок. Залиште заявку, і менеджер допоможе підібрати оптимальний варіант та розрахує фінальну вартість.',
            ),
            array(
              'question' => 'Як проходять заняття?',
              'answer'   => 'Вартість залежить від обраного формату навчання, пакета занять, міста, можливих пільг і поточних знижок. Залиште заявку, і менеджер допоможе підібрати оптимальний варіант та розрахує фінальну вартість.',
            ),
          ),
        )
      );
    ?>

    <section class="testimonials-section">
      <div class="container">
        <div class="testimonials-section__wrapp">
          <h2>Довіра, підтверджена результатами</h2>

          <div class="testimonials-section__box">
            <ul class="testimonials-section__items">
              <li class="testimonials-section__item">
                <div class="testimonials-card is-image">
                  <div class="testimonials-card__decor">
                    <picture>
                      <source type='image/webp' srcset='img/testimonials/testimonial.webp'>
                      <img width='220' height='220' src='img/testimonials/testimonial.png' alt=''>
                    </picture>

                  </div>

                  <div class="testimonials-card__watch">
                    <svg width='34' height='34'>
                      <use href='img/sprite/sprite.svg#watch'></use>
                    </svg>
                  </div>
                </div>
              </li>

              <li class="testimonials-section__item">
                <div class="testimonials-card">
                  <div class="testimonials-card__box">
                    <div class="testimonials-card__top">
                      <div class="testimonials-card__avatar">
                        <picture>
                          <source type='image/webp' srcset='img/testimonials/ANNA.webp'>
                          <img width='56' height='56' src='img/testimonials/ANNA.png' alt=''>
                        </picture>
                      </div>

                      <div class="testimonials-card__info">
                        <div class="testimonials-card__name">Анна Н.</div>
                        <div class="testimonials-card__rating">
                          <svg width='100' height='18'>
                            <use href='img/sprite/sprite.svg#stars-rating'></use>
                          </svg>
                        </div>
                      </div>
                    </div>

                    <span class="testimonials-card__tag">Python Mastery</span>

                    <p class="testimonials-card__excerpt">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec facilisis vestibulum ullamcorper. Curabitur arcu magna, lobortis vel sapien...</p>
                  </div>
                </div>
              </li>

              <li class="testimonials-section__item">
                <div class="testimonials-card">
                  <div class="testimonials-card__box">
                    <div class="testimonials-card__top">
                      <div class="testimonials-card__avatar">
                        <picture>
                          <source type='image/webp' srcset='img/testimonials/ANNA.webp'>
                          <img width='56' height='56' src='img/testimonials/ANNA.png' alt=''>
                        </picture>
                      </div>

                      <div class="testimonials-card__info">
                        <div class="testimonials-card__name">Анна Н.</div>
                        <div class="testimonials-card__rating">
                          <svg width='100' height='18'>
                            <use href='img/sprite/sprite.svg#stars-rating'></use>
                          </svg>
                        </div>
                      </div>
                    </div>

                    <span class="testimonials-card__tag">Python Mastery</span>

                    <p class="testimonials-card__excerpt">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec facilisis vestibulum ullamcorper. Curabitur arcu magna, lobortis vel sapien...</p>
                  </div>
                </div>
              </li>

              <li class="testimonials-section__item">
                <div class="testimonials-card is-image">
                  <div class="testimonials-card__decor">
                    <picture>
                      <source type='image/webp' srcset='img/testimonials/testimonial.webp'>
                      <img width='220' height='220' src='img/testimonials/testimonial.png' alt=''>
                    </picture>

                  </div>
                </div>
              </li>

              <li class="testimonials-section__item">
                <div class="testimonials-card">
                  <div class="testimonials-card__box">
                    <div class="testimonials-card__top">
                      <div class="testimonials-card__avatar">
                        <picture>
                          <source type='image/webp' srcset='img/testimonials/ANNA.webp'>
                          <img width='56' height='56' src='img/testimonials/ANNA.png' alt=''>
                        </picture>
                      </div>

                      <div class="testimonials-card__info">
                        <div class="testimonials-card__name">Анна Н.</div>
                        <div class="testimonials-card__rating">
                          <svg width='100' height='18'>
                            <use href='img/sprite/sprite.svg#stars-rating'></use>
                          </svg>
                        </div>
                      </div>
                    </div>

                    <span class="testimonials-card__tag">Python Mastery</span>

                    <p class="testimonials-card__excerpt">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec facilisis vestibulum ullamcorper. Curabitur arcu magna, lobortis vel sapien...</p>
                  </div>
                </div>
              </li>

              <li class="testimonials-section__item">
                <div class="testimonials-card">
                  <div class="testimonials-card__box">
                    <div class="testimonials-card__top">
                      <div class="testimonials-card__avatar">
                        <picture>
                          <source type='image/webp' srcset='img/testimonials/ANNA.webp'>
                          <img width='56' height='56' src='img/testimonials/ANNA.png' alt=''>
                        </picture>
                      </div>

                      <div class="testimonials-card__info">
                        <div class="testimonials-card__name">Анна Н.</div>
                        <div class="testimonials-card__rating">
                          <svg width='100' height='18'>
                            <use href='img/sprite/sprite.svg#stars-rating'></use>
                          </svg>
                        </div>
                      </div>
                    </div>

                    <span class="testimonials-card__tag">Python Mastery</span>

                    <p class="testimonials-card__excerpt">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec facilisis vestibulum ullamcorper. Curabitur arcu magna, lobortis vel sapien...</p>
                  </div>
                </div>
              </li>

              <li class="testimonials-section__item">
                <div class="testimonials-card">
                  <div class="testimonials-card__box">
                    <div class="testimonials-card__top">
                      <div class="testimonials-card__avatar">
                        <picture>
                          <source type='image/webp' srcset='img/testimonials/ANNA.webp'>
                          <img width='56' height='56' src='img/testimonials/ANNA.png' alt=''>
                        </picture>
                      </div>

                      <div class="testimonials-card__info">
                        <div class="testimonials-card__name">Анна Н.</div>
                        <div class="testimonials-card__rating">
                          <svg width='100' height='18'>
                            <use href='img/sprite/sprite.svg#stars-rating'></use>
                          </svg>
                        </div>
                      </div>
                    </div>

                    <span class="testimonials-card__tag">Python Mastery</span>

                    <p class="testimonials-card__excerpt">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec facilisis vestibulum ullamcorper. Curabitur arcu magna, lobortis vel sapien...</p>
                  </div>
                </div>
              </li>

              <li class="testimonials-section__item">
                <div class="testimonials-card">
                  <div class="testimonials-card__box">
                    <div class="testimonials-card__top">
                      <div class="testimonials-card__avatar">
                        <picture>
                          <source type='image/webp' srcset='img/testimonials/ANNA.webp'>
                          <img width='56' height='56' src='img/testimonials/ANNA.png' alt=''>
                        </picture>
                      </div>

                      <div class="testimonials-card__info">
                        <div class="testimonials-card__name">Анна Н.</div>
                        <div class="testimonials-card__rating">
                          <svg width='100' height='18'>
                            <use href='img/sprite/sprite.svg#stars-rating'></use>
                          </svg>
                        </div>
                      </div>
                    </div>

                    <span class="testimonials-card__tag">Python Mastery</span>

                    <p class="testimonials-card__excerpt">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec facilisis vestibulum ullamcorper. Curabitur arcu magna, lobortis vel sapien...</p>
                  </div>
                </div>
              </li>

              <li class="testimonials-section__item">
                <div class="testimonials-card is-image">
                  <div class="testimonials-card__decor">
                    <picture>
                      <source type='image/webp' srcset='img/testimonials/testimonial.webp'>
                      <img width='220' height='220' src='img/testimonials/testimonial.png' alt=''>
                    </picture>

                  </div>
                </div>
              </li>

              <li class="testimonials-section__item">
                <div class="testimonials-card">
                  <div class="testimonials-card__box">
                    <div class="testimonials-card__top">
                      <div class="testimonials-card__avatar">
                        <picture>
                          <source type='image/webp' srcset='img/testimonials/ANNA.webp'>
                          <img width='56' height='56' src='img/testimonials/ANNA.png' alt=''>
                        </picture>
                      </div>

                      <div class="testimonials-card__info">
                        <div class="testimonials-card__name">Анна Н.</div>
                        <div class="testimonials-card__rating">
                          <svg width='100' height='18'>
                            <use href='img/sprite/sprite.svg#stars-rating'></use>
                          </svg>
                        </div>
                      </div>
                    </div>

                    <span class="testimonials-card__tag">Python Mastery</span>

                    <p class="testimonials-card__excerpt">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec facilisis vestibulum ullamcorper. Curabitur arcu magna, lobortis vel sapien...</p>
                  </div>
                </div>
              </li>

              <li class="testimonials-section__item">
                <div class="testimonials-card is-image">
                  <div class="testimonials-card__decor">
                    <picture>
                      <source type='image/webp' srcset='img/testimonials/testimonial.webp'>
                      <img width='220' height='220' src='img/testimonials/testimonial.png' alt=''>
                    </picture>

                  </div>
                </div>
              </li>

              <li class="testimonials-section__item">
                <div class="testimonials-card">
                  <div class="testimonials-card__box">
                    <div class="testimonials-card__top">
                      <div class="testimonials-card__avatar">
                        <picture>
                          <source type='image/webp' srcset='img/testimonials/ANNA.webp'>
                          <img width='56' height='56' src='img/testimonials/ANNA.png' alt=''>
                        </picture>
                      </div>

                      <div class="testimonials-card__info">
                        <div class="testimonials-card__name">Анна Н.</div>
                        <div class="testimonials-card__rating">
                          <svg width='100' height='18'>
                            <use href='img/sprite/sprite.svg#stars-rating'></use>
                          </svg>
                        </div>
                      </div>
                    </div>

                    <span class="testimonials-card__tag">Python Mastery</span>

                    <p class="testimonials-card__excerpt">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec facilisis vestibulum ullamcorper. Curabitur arcu magna, lobortis vel sapien...</p>
                  </div>
                </div>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    <div data-map-online-form hidden>
      <form class="banner-section__form main-form">
        <div class="main-form__title h5">
          <span>Перший урок — безкоштовно.</span>
          Залиште заявку за 30 секунд — ми зателефонуємо і підберемо зручний час
        </div>
        <div class="main-form__inputs">
          <input class="main-form__input" type="text" name="name" placeholder="Ім’я">
          <input class="main-form__input" type="tel" name="tel" placeholder="Номер телефону">
          <input class="main-form__input" type="text" name="age" placeholder="Вік дитини (від 7 до 17)">
        </div>
        <button class="main-form__btn btn btn--yellow">
          Спробувати безкоштовно
          <svg width='20' height='20'>
            <use href='img/sprite/sprite.svg#arrow-right'></use>
          </svg>
        </button>
        <p class="main-form__text">Натискаючи, ви погоджуєтесь із <a href="#">Політикою конфіденційності</a></p>
      </form>
    </div>

    <section class="school-map" data-school-map aria-labelledby="school-map-title">
      <div class="container">
        <div class="school-map__heading">
          <h2 id="school-map-title">Знайдіть свою школу або<br>навчайтесь онлайн</h2>
          <p>Наші школи у 130 містах України - знайдіть зручний варіант поруч із вами або навчайтесь онлайн.</p>
        </div>
        <div class="school-map__mode" role="group" aria-label="Формат навчання">
          <button class="is-active" type="button" data-map-mode="offline">Навчатися у нашому місті</button>
          <button type="button" data-map-mode="online">Онлайн навчання</button>
        </div>

        <div class="school-map__layout">
          <div class="school-map__visual">
            <div class="school-map__canvas" data-map-canvas aria-live="polite">
              <p>Завантажуємо карту областей...</p>
            </div>
          </div>

          <div class="school-map__selector">
            <h3>Оберіть місто навчання</h3>
            <p>Ми підкажемо зручний варіант у вибраній області.</p>
            <h4 data-map-region>Дніпропетровська область</h4>
            <div class="school-map__cities" data-map-cities></div>
          </div>
        </div>

        <div class="school-map__details" data-map-details>
          <h3 data-map-city-title>ДНІПРО</h3>
          <div class="school-map__details-content">
            <div class="school-map__locations">
              <p class="school-map__locations-count" data-map-locations-count>Усі локації (8)</p>
              <ul class="school-map__schools" data-map-schools></ul>
            </div>
            <iframe class="school-map__frame" data-map-frame title="Карта шкіл у Дніпрі" loading="lazy"
              src="https://www.google.com/maps?q=Dnipro,+Ukraine&output=embed"></iframe>
          </div>
        </div>
      </div>
    </section>

    <section class="cta-section">
      <div class="container">
        <div class="cta-section__wrapp">
          <div class="cta-section__left">
            <form class="cta-form">
              <div class="cta-form__top">
                <h2 class="cta-form__title h3">Підберемо курс саме для вашої дитини!</h2>
                <p class="cta-form__subtitle h4">Ми зателефонуємо в зручний час</p>
              </div>

                <div class="cta-form__inputs">
                  <input class="main-form__input" type="text" name="name" placeholder="Ім’я">
                  <input class="main-form__input" type="tel" name="tel" placeholder="Номер телефону">
                  <input class="main-form__input" type="text" name="town" placeholder="Оберіть місто">
                  <input class="main-form__input" type="text" name="age" placeholder="Вік дитини (від 7 до 17)">
                </div>

                <div class="cta-form__bottom">
                  <button class="cta-form__btn btn btn--yellow">
                    Отримати консультацію
                    <svg width='20' height='20'>
                      <use href='img/sprite/sprite.svg#arrow-right'></use>
                    </svg>
                  </button>

                  <p class="cta-form__text">Натискаючи, ви погоджуєтесь із <a href="#">Політикою конфіденційності</a></p>
                </div>
            </form>
          </div>

          <div class="cta-section__right">
            <div class="cta-section__image">
              <picture>
                <source type='image/webp' srcset='img/cta/cta.webp'>
                <img width='487' height='712' src='img/cta/cta.png' alt='cta girl'>
              </picture>
            </div>

            <div class="cta-section__character-logika">
              <img width='97' height='146' src='img/cta/cta-icon.svg' alt='cta-icon'>
            </div>
          </div>

          <div class="cta-section__top-bg">
            <img src="img/cta/cta-top-bg.svg" alt="">
          </div>

          <div class="cta-section__bottom-bg">
            <img src="img/cta/cta-bottom-bg.svg" alt="">
          </div>
        </div>
      </div>
    </section>
  </main>

  @include('partials/footer.html')

  <script src="js/swiper.js"></script>
  <script defer src="js/main.js"></script>
  <script defer src="js/camp-map.js"></script>
</body>


</html>
