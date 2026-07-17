<header class="header fixed-block">
  <div class="header__top">
    <div class="container">
      <div class="header__top-box">
        <div class="header__contact">
          <div class="header__contact-heading">
            <svg width='16' height='16'>
              <use href='img/sprite/sprite.svg#icon-phone'></use>
            </svg>
            <span>Телефон:</span>
          </div>
          <a class="header__contact-link" href="tel:+380931707440" target="_blank">+ 38 (093) 170-74-40</a>
        </div>

        <div class="header__contact">
          <div class="header__contact-heading">
            <svg width='16' height='16'>
              <use href='img/sprite/sprite.svg#icon-email'></use>
            </svg>
            <span>Email:</span>
          </div>
          <a class="header__contact-link" href="mailto:kiev@logikaschool.com" target="_blank">kiev@logikaschool.com</a>
        </div>

        <div class="header__socials">
          <div class="header__socials-heading">Соц. мережі:</div>

          <ul class="header__socials-links">
            <li>
              <a href="https://www.instagram.com/logika_it_school/" target="_blank">
                <svg width='24' height='24'>
                  <use href='img/sprite/sprite.svg#instagram-color'></use>
                </svg>
              </a>
            </li>
            <li>
              <a href="https://www.facebook.com/logika.it.school" target="_blank">
                <svg width='24' height='24'>
                  <use href='img/sprite/sprite.svg#facebook-color'></use>
                </svg>
              </a>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <div class="container">
    <div class="header__box">
      <div class="header__left">
        <a class="header__logo logo" href="/">
          <img width='323' height='53' src='img/main-logo.svg?v=20260717' alt='Logika'>
        </a>

      </div>

      <div class="mobile">
        <div class="mobile__box">
          <div class="header__location">
            <div class="header__location-trigger">
              <div class="header__location-active">
                <svg width='16' height='16'>
                  <use href='img/sprite/sprite.svg#icon-location'></use>
                </svg>
                <p>Оберіть місто</p>
              </div>

              <svg width='18' height='18'>
                <use href='img/sprite/sprite.svg#icon-caret-down'></use>
              </svg>
            </div>
          </div>

          <nav class="header__nav" data-single='true' data-breakpoint='576' data-accordion-init>
            <ul class="menu">
              <li class="menu-item">
                <a class="menu-link" href="/about/">Про Logika</a>
              </li>
              <li class="menu-item menu-has-child">
                <a class="menu-link" href="/it-courses/">Курси</a>

                <button class="menu-button" data-id='courses'>
                  <svg width='18' height='18'>
                    <use href='img/sprite/sprite.svg#icon-caret-down'></use>
                  </svg>
                </button>

                <ul class="sub-menu" data-content='courses'>
                  <li class="menu-item">
                    <a class="menu-link" href="/it-courses/">IT Курси</a>
                  </li>
                  <li class="menu-item">
                    <a class="menu-link" href="/english-courses/">Курси англійської</a>
                  </li>
                </ul>
              </li>
              <li class="menu-item">
                <a class="menu-link" href="/camps/">IT-табори</a>
              </li>
              <li class="menu-item">
                <a class="menu-link" href="/media-center/">Медіа-центр</a>
              </li>
              <li class="menu-item">
                <a class="menu-link" href="/faq/">FAQ</a>
              </li>
              <li class="menu-item">
                <a class="menu-link" href="#">Контакти</a>
              </li>
            </ul>
          </nav>

          <div class="header__controls">
            <a class="header__login btn" href="https://student.logikaschool.com.ua/login">
              <svg width='16' height='16'>
                <use href='img/sprite/sprite.svg#icon-person'></use>
              </svg>
              <span>Увійти</span>
            </a>

            <a class="header__lesson btn btn--yellow" href="#">
              Пробний урок
              <svg width='20' height='20'>
                <use href='img/sprite/sprite.svg#arrow-right'></use>
              </svg>
            </a>


          </div>
        </div>
      </div>

      <button class="burger">
        <span class="burger__line"></span>
      </button>
    </div>
  </div>
</header>
