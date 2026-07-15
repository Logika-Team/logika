<!DOCTYPE html>
<html lang="uk">
@include('partials/head.html')

<body>
  @include('partials/header.html')

  <main>
    <section class="banner-section about-hero">
      <div class="container">
        <div class="banner-section__wrapp">
          <div class="banner-section__blocks">
            <div class="banner-section__left">
              <div class="banner-section__info">
                <h1>Найбільша в Україні школа програмування для дітей 7-17 років</h1>
                <h4>Перші результати вже через 4 тижні</h4>
              </div>
              <div class="banner-section__character-boy">
                <img width="432" height="232" src="img/about/hero-characters.png" alt="Персонажі Logika Ctrl та W">
              </div>
            </div>
            <div class="banner-section__right">
              <form id="form" class="banner-section__form main-form">
                <div class="main-form__title h5"><span>Перший урок — безкоштовно.</span>Залиште заявку за 30 секунд — ми зателефонуємо і підберемо зручний час</div>
                <div class="main-form__inputs">
                  <input class="main-form__input" type="text" name="name" placeholder="Ім’я">
                  <input class="main-form__input" type="tel" name="tel" placeholder="Номер телефону">
                  <input class="main-form__input" type="text" name="age" placeholder="Вік дитини (від 7 до 17)">
                </div>
                <button class="main-form__btn btn btn--yellow" type="submit">Спробувати безкоштовно<svg width="20" height="20"><use href="img/sprite/sprite.svg#arrow-right"></use></svg></button>
                <p class="main-form__text">Натискаючи, ви погоджуєтесь із <a href="#">Політикою конфіденційності</a></p>
              </form>
              <div class="banner-section__character-logika"><img width="97" height="146" src="img/logika-character.svg" alt="Персонаж Logika"></div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="about-stats" aria-labelledby="about-stats-title">
      <div class="container">
        <h1 id="about-stats-title">Навчайтеся в найбільшій школі в Україні</h1>
        <ul class="about-stats__list">
          <li><img src="img/banner-bar/icon-calendar-check.svg" alt="">З 2018 року на ринку</li>
          <li><img src="img/banner-bar/icon-document-certificate.svg" alt="">Освітня ліцензія</li>
          <li><img src="img/banner-bar/icon-rating-star.svg" alt="">4.9 рейтинг від клієнтів</li>
          <li><img src="img/banner-bar/icon-outline_school.svg" alt="">178 шкіл в Україні</li>
          <li><img src="img/banner-bar/icon-map-location.svg" alt="">130 міст по Україні</li>
          <li><img src="img/banner-bar/icon-tabler-school.svg" alt="">100тис+ успішних випускників</li>
        </ul>
      </div>
    </section>

    <section class="about-directions" aria-label="Напрями навчання">
      <div class="container">
        <ul>
          <li><a href="/it-courses.html" aria-label="Переглянути курси програмування"><img src="img/about/direction-programming.png?v=3" alt="Курси програмування"></a></li>
          <li><a href="/en-courses.html" aria-label="Переглянути курси англійської"><img src="img/about/direction-english.png?v=3" alt="Курси англійської мови"></a></li>
          <li><a href="/camps.html" aria-label="Переглянути табори"><img src="img/about/direction-camps.png?v=3" alt="Табори Logika"></a></li>
        </ul>
      </div>
    </section>

    <section class="about-outcomes" aria-labelledby="about-outcomes-title">
      <div class="container">
        <h2 id="about-outcomes-title">Що отримає ваша дитина,<br>навчаючись у школі “Logika”</h2>
        <ul>
          <li><img src="img/about/outcome-thinking.png?v=3" alt="Дитина полюбить точні науки та побачить, як шкільні предмети працюють у житті"></li>
          <li><img src="img/about/outcome-technology.png?v=3" alt="Дитина навчиться сучасним IT-технологіям, які використовують фахівці в реальних компаніях"></li>
          <li><img src="img/about/outcome-projects.png?v=3" alt="Дитина створюватиме реальні проєкти для перших професійних досягнень"></li>
        </ul>
      </div>
    </section>

    <section class="about-history" aria-labelledby="about-history-title">
      <div class="container">
        <div class="about-history__layout">
          <div class="about-history__content">
            <h2 id="about-history-title">Logika – успішний освітній<br>проєкт з 2018 року</h2>
            <div class="about-history__copy">
              <p>Наша школа є успішним освітнім проєктом, який діє з 2018 року та має понад 50 000 випускників. Навчаємо дітей 7–17 років, заняття проводимо онлайн та офлайн. Маємо два напрямки: програмування та англійська мова.</p>
              <p>Крім основного навчання проводимо сезонні інтелектуальні квести з призами, їздимо в IT-табори до Карпат та заохочуємо до навчання різноманітними способами.</p>
              <strong>Наша основна мета – навчити цікаво, легко та ефективно!</strong>
            </div>
            <a class="about-history__cta" href="#form">Безкоштовний пробний урок&nbsp; →</a>
          </div>
          <div class="about-history__image">
            <img src="img/about/anniversary-full.png" alt="Logika святкує чотири роки">
            <span class="about-history__skill about-history__skill--team">командна робота</span>
            <span class="about-history__skill about-history__skill--communication">комунікація</span>
            <span class="about-history__skill about-history__skill--thinking">критичне мислення</span>
            <span class="about-history__skill about-history__skill--creativity">креативність</span>
          </div>
        </div>
      </div>
    </section>

    <section class="gallery-section">
      <div class="container">
        <div class="gallery-section__wrapp">
          <h2>Gallery</h2>
        </div>
      </div>
    </section>

    <section class="media-section">
      <div class="container">
        <div class="media-section__wrapp">
          <h2 class="media-section__title h2">Чому тисячі батьків обирають Logika</h2>
          <ul class="media-section__cards">
            <li class="media-section__card media-section__card--yellow media-section__card--platform">
              <img class="media-section__card-img" width="270" height="255" src="img/media-center/why-logika/platform.png" alt="Єдина платформа — єдина якість">
              <div class="media-section__card-content"><h3>Єдина платформа — єдина якість</h3><p>Інтерактивний підручник, практичні завдання й трекер результатів забезпечують високу якість навчання.</p></div>
            </li>
            <li class="media-section__card media-section__card--violet media-section__card--projects">
              <img class="media-section__card-img" width="230" height="215" src="img/media-center/why-logika/projects.png" alt="Мінімум 5 реальних проєктів за курс">
              <div class="media-section__card-content"><h3>Мінімум 5 реальних проєктів за курс</h3><p>Ігри, мультфільми, боти, сайти чи додатки — від ідеї до готового результату.</p></div>
            </li>
            <li class="media-section__card media-section__card--green media-section__card--open-lessons">
              <img class="media-section__card-img" width="257" height="235" src="img/media-center/why-logika/open-lessons.png" alt="Відкриті уроки для батьків">
              <div class="media-section__card-content"><h3>Відкриті уроки для батьків</h3><p>Наприкінці модуля дитина презентує свій проєкт, а ви бачите реальний прогрес.</p></div>
            </li>
            <li class="media-section__card media-section__card--violet media-section__card--online-city">
              <img class="media-section__card-img" width="300" height="230" src="img/media-center/why-logika/online-city.png" alt="Онлайн або у вашому місті">
              <div class="media-section__card-content"><h3>Онлайн або у вашому місті</h3><p>Займайтеся у школі Logika або онлайн у невеликих групах.</p></div>
            </li>
            <li class="media-section__card media-section__card--green media-section__card--teachers">
              <img class="media-section__card-img" width="290" height="235" src="img/media-center/why-logika/teachers.png" alt="Викладачі з профільною освітою">
              <div class="media-section__card-content"><h3>Викладачі з профільною освітою</h3><p>Багаторівневий відбір, навчання за методологією Logika та досвід роботи з дітьми.</p></div>
            </li>
            <li class="media-section__card media-section__card--yellow media-section__card--game-method">
              <img class="media-section__card-img" width="300" height="230" src="img/media-center/why-logika/game-method.png" alt="Власна ігрова методика">
              <div class="media-section__card-content"><h3>Власна ігрова методика</h3><p>Навчаємо через сюжети, практику та залучення, а не через суху теорію.</p></div>
            </li>
          </ul>
        </div>
      </div>
    </section>

    <section class="onboarding-section">
      <div class="container">
        <div class="onboarding-section__wrapp">
          <h2>Як розпочати навчання</h2>

          <ul class="onboarding-section__items">
            <li class="onboarding-section__item">
              <div class="onboarding-section__item-media">
                <div class="onboarding-section__item-image">
                  <img width='368' height='280' src='img/onbording/onbording1.svg' alt=''>
                </div>
              </div>
              
              <div class="onboarding-section__item-content">
                <div class="onboarding-section__item-title h3">Залишiть заявку</div>

                <p class="onboarding-section__item-excerpt">Заповнiть форму або зателефонуйте. Менеджер зв'яжеться за 30 хвилин</p>

                <a href="#" class="onboarding-section__item-lesson btn btn--yellow">
                  Залишити заявку
                  <svg width='20' height='20'>
                    <use href='img/sprite/sprite.svg#arrow-right'></use>
                  </svg>
                </a>
              </div>
            </li>

            <li class="onboarding-section__item">
              <div class="onboarding-section__item-media">
                <div class="onboarding-section__item-image">
                  <img width='368' height='280' src='img/onbording/onbording2.svg' alt=''>
                </div>
              </div>
              
              <div class="onboarding-section__item-content">
                <div class="onboarding-section__item-title h3">Відвідайте безкоштовний пробний урок</div>

                <p class="onboarding-section__item-excerpt">Дитина спробує, ви подивитесь. Жодних зобов'язань</p>

                <a href="#" class="onboarding-section__item-lesson btn btn--yellow">
                  Залишити заявку
                  <svg width='20' height='20'>
                    <use href='img/sprite/sprite.svg#arrow-right'></use>
                  </svg>
                </a>
              </div>
            </li>

            <li class="onboarding-section__item">
              <div class="onboarding-section__item-media">
                <div class="onboarding-section__item-image">
                  <img width='368' height='280' src='img/onbording/onbording3.svg' alt=''>
                </div>
              </div>
              
              <div class="onboarding-section__item-content">
                <div class="onboarding-section__item-title h3">Розпочніть навчання та отримайте результат</div>

                <p class="onboarding-section__item-excerpt">Регулярнi заняття, власнi проекти, вiдкритi уроки для батькiв</p>

                <a href="#" class="onboarding-section__item-lesson btn btn--yellow">
                  Залишити заявку
                  <svg width='20' height='20'>
                    <use href='img/sprite/sprite.svg#arrow-right'></use>
                  </svg>
                </a>
              </div>
            </li>
          </div>
        </div>
      </div>
    </section>

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

    <section class="faq-section">
      <div class="faq-section__left-bg">
        <img src="img/faq/faq-left-bg.svg" alt="">
      </div>

      <div class="faq-section__right-bg">
        <img src="img/faq/faq-right-bg.svg" alt="">
      </div>

      <div class="container">
        <div class="faq-section__wrapp">
          <h2>Питання та відповіді</h2>

          <ul class='accordion' data-default="1" data-single='true' data-breakpoint='576' data-accordion-init>
            <li class='accordion__item'>
              <button class='accordion__btn h5' data-id='1'>
                Скільки коштує навчання в Logika?
              </button>
              <div class='accordion__content' data-content='1'>
                <div class="editor">
                  <p>Вартість залежить від обраного формату навчання, пакета занять, міста, можливих пільг і поточних знижок. Залиште заявку, і менеджер допоможе підібрати оптимальний варіант та розрахує фінальну вартість.</p>
                </div>
              </div>
            </li>
            <li class='accordion__item'>
              <button class='accordion__btn h5' data-id='2'>
                З якого віку можна навчатися в Logika?
              </button>
              <div class='accordion__content' data-content='2'>
                <div class="editor">
                  <p>Вартість залежить від обраного формату навчання, пакета занять, міста, можливих пільг і поточних знижок. Залиште заявку, і менеджер допоможе підібрати оптимальний варіант та розрахує фінальну вартість.</p>
                </div>
              </div>
            </li>
            <li class='accordion__item'>
              <button class='accordion__btn h5' data-id='3'>
                Чи потрібен досвід у програмуванні або англійській?
              </button>
              <div class='accordion__content' data-content='3'>
                <div class="editor">
                  <p>Вартість залежить від обраного формату навчання, пакета занять, міста, можливих пільг і поточних знижок. Залиште заявку, і менеджер допоможе підібрати оптимальний варіант та розрахує фінальну вартість.</p>
                </div>
              </div>
            </li>
            <li class='accordion__item'>
              <button class='accordion__btn h5' data-id='4'>
                Як проходять заняття?
              </button>
              <div class='accordion__content' data-content='4'>
                <div class="editor">
                  <p>Вартість залежить від обраного формату навчання, пакета занять, міста, можливих пільг і поточних знижок. Залиште заявку, і менеджер допоможе підібрати оптимальний варіант та розрахує фінальну вартість.</p>
                </div>
              </div>
            </li>
            <li class='accordion__item'>
              <button class='accordion__btn h5' data-id='5'>
                Що отримає дитина під час навчання?
              </button>
              <div class='accordion__content' data-content='5'>
                <div class="editor">
                  <p>Вартість залежить від обраного формату навчання, пакета занять, міста, можливих пільг і поточних знижок. Залиште заявку, і менеджер допоможе підібрати оптимальний варіант та розрахує фінальну вартість.</p>
                </div>
              </div>
            </li>
            <li class='accordion__item'>
              <button class='accordion__btn h5' data-id='6'>
                Як записатися на безкоштовний пробний урок?
              </button>
              <div class='accordion__content' data-content='6'>
                <div class="editor">
                  <p>Вартість залежить від обраного формату навчання, пакета занять, міста, можливих пільг і поточних знижок. Залиште заявку, і менеджер допоможе підібрати оптимальний варіант та розрахує фінальну вартість.</p>
                </div>
              </div>
            </li>
            <li class='accordion__item'>
              <button class='accordion__btn h5' data-id='7'>
                Чим Logika відрізняється від інших шкіл?
              </button>
              <div class='accordion__content' data-content='7'>
                <div class="editor">
                  <p>Вартість залежить від обраного формату навчання, пакета занять, міста, можливих пільг і поточних знижок. Залиште заявку, і менеджер допоможе підібрати оптимальний варіант та розрахує фінальну вартість.</p>
                </div>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </section>

    <section class="certificates-section">
        <div class="certificates-section__wrapp">
            <div class="container">
              <div class="certificates-section__box">
                  <div class="certificates-section__info">
                    <div class="certificates-section__text">
                      <h2>Ура, подарункові сертифікати</h2>
                      <h5>Вартість сертифікату на 2 місяці навчання = вартість 1-го місяця + вартість 2-го місяця зі знижкою 10%</h5>
                      <p>Залиште заявку, і ми зв'яжемось з вами: відповімо на будь-які запитання і допоможемо обрати курс </p>
                    </div>

                    <a class="btn btn--violet" href="#">
                      Залишити заявку
                      <svg width='20' height='20'>
                        <use href='img/sprite/sprite.svg#arrow-right'></use>
                      </svg>
                    </a>
                  </div>

                  <div class="certificates-section__preview">
                    <picture>
                      <source type='image/webp' srcset='img/certificates/certificate.webp'>
                      <img width='701' height='501' src='img/certificates/certificate.png' alt=''>
                    </picture>
                  </div>
              </div>
            </div>
        </div>
    </section>

    <section class="partners-section">
      <div class="container">
        <div class="partners-section__wrapp">
          <h2>Наші партнери</h2>

          <ul class="partners-section__gallery">
            <li>
              <picture>
                <source type='image/webp' srcset='img/Partners/think.webp'>
                <img width='305' height='180' src='img/Partners/think.png' alt=''>
              </picture>
            </li>
            <li>
              <picture>
                <source type='image/webp' srcset='img/Partners/1+1.webp'>
                <img width='305' height='180' src='img/Partners/1+1.png' alt=''>
              </picture>
            </li>
            <li>
              <picture>
                <source type='image/webp' srcset='img/Partners/Free.webp'>
                <img width='305' height='180' src='img/Partners/Free.png' alt=''>
              </picture>
            </li>
            <li>
              <picture>
                <source type='image/webp' srcset='img/Partners/club.webp'>
                <img width='305' height='180' src='img/Partners/club.png' alt=''>
              </picture>
            </li>
            <li>
              <picture>
                <source type='image/webp' srcset='img/Partners/ed.webp'>
                <img width='305' height='180' src='img/Partners/ed.png' alt=''>
              </picture>
            </li>
            <li>
              <picture>
                <source type='image/webp' srcset='img/Partners/basis.webp'>
                <img width='305' height='180' src='img/Partners/basis.png' alt=''>
              </picture>
            </li>
            <li>
              <picture>
                <source type='image/webp' srcset='img/Partners/fond.webp'>
                <img width='305' height='180' src='img/Partners/fond.png' alt=''>
              </picture>
            </li>
            <li>
              <picture>
                <source type='image/webp' srcset='img/Partners/mriya.webp'>
                <img width='305' height='180' src='img/Partners/mriya.png' alt=''>
              </picture>
            </li>
          </ul>
          
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
