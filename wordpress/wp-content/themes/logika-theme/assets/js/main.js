//-----vars---------------------------------------
const windowEl = window;
const documentEl = document;
const htmlEl = document.documentElement;
const bodyEl = document.body;
const activeClass = 'active';
const activeClassMode = 'mode';
const header = document.querySelector('header');
const headerTop = document.querySelector('.header__top');
const footer = document.querySelector('footer');

const burger = document.querySelectorAll('.burger');
const mobileMenu = document.querySelector('.mobile');
const mobileMenuCloseBtn = document.querySelectorAll('.mobile__close');
const dropdownToggles = document.querySelectorAll(".toggle-dropdown");
const accParrent = [...document.querySelectorAll("[data-accordion-init]")];

const marqueeSectionSlider = document.querySelectorAll('.marquee-section__slider');
const englishSectionSlider = document.querySelectorAll('.english-section__slider');
const categoriesCoursesSlider = document.querySelectorAll('.categories-section__slider');
const tripsSectionSlider = document.querySelectorAll('.trips-section__slider');
const campsHighlightsSlider = document.querySelectorAll('.camp-highlights__slider');
const testimonialsSlider = document.querySelectorAll('.testimonials-section__slider');
const campsHistorySlider = document.querySelectorAll('.camp-history__slider');
const campGalleries = document.querySelectorAll('[data-camp-gallery]');

//------------------------------------------------

//----customFunction------------------------------
const fadeIn = (el, timeout, display) => {
	el.style.opacity = 0;
	el.style.display = display || 'flex';
	el.style.transition = `all ${timeout}ms`;
	setTimeout(() => {
		el.style.opacity = 1;
	}, 10);
};

const fadeOut = (el, timeout) => {
	el.style.opacity = 1;
	el.style.transition = `all ${timeout}ms ease`;
	el.style.opacity = 0;

	setTimeout(() => {
		el.style.display = 'none';
	}, timeout);
};

const toggleCustomClass = (item, customClass = "active") => {
  item.classList.toggle(customClass);
};

const toggleClassInArray = (arr, customClass = "active") => {
  arr.forEach((item) => {
    item.classList.toggle(customClass);
  });
};

const removeClassInArray = (arr, customClass = "active") => {
  arr.forEach((item) => {
    item.classList.remove(customClass);
  });
};

const addCustomClass = (item, customClass = "active") => {
  item.classList.add(customClass);
};

const addClassInArray = (arr, customClass) => {
  arr.forEach((item) => {
    item.classList.add(customClass);
  });
}

const removeCustomClass = (item, customClass = "active") => {
  item.classList.remove(customClass);
};

const disableScroll = () => {
  const fixBlocks = document?.querySelectorAll(".fixed-block:not(.header)");
  const pagePosition = window.scrollY;
  const paddingOffset = `${window.innerWidth - htmlEl.clientWidth}px`;

  htmlEl.style.scrollBehavior = "none";
  fixBlocks.forEach((el) => {
    el.style.paddingRight = paddingOffset;
  });
  bodyEl.style.paddingRight = paddingOffset;
  bodyEl.classList.add("dis-scroll");
  bodyEl.dataset.position = pagePosition;
  bodyEl.style.top = `-${pagePosition}px`;
};

const enableScroll = () => {
  const fixBlocks = document?.querySelectorAll(".fixed-block:not(.header)");
  const body = document.body;
  const pagePosition = parseInt(bodyEl.dataset.position, 10);
  fixBlocks.forEach((el) => {
    el.style.paddingRight = "0px";
  });
  bodyEl.style.paddingRight = "0px";

  bodyEl.style.top = "auto";
  bodyEl.classList.remove("dis-scroll");
  window.scroll({
    top: pagePosition,
    left: 0,
  });
};

const elementHeight = (el, variableName, extraEl = null) => {
  if(el) {
    function initListener(){
      const elementHeight = el.offsetHeight + (extraEl?.offsetHeight || 0);
      document.querySelector(':root').style.setProperty(`--${variableName}`, `${elementHeight}px`);
    }
    window.addEventListener('DOMContentLoaded', initListener)
    window.addEventListener('resize', initListener)
  }
}

const elementWidth = (el, variableName) => {
	if (el) {
		function initListener() {
			const elementWidth = el.offsetWidth;
			document.querySelector(':root').style.setProperty(`--${variableName}`, `${elementWidth}px`);
		}

		window.addEventListener('DOMContentLoaded', initListener);
		window.addEventListener('resize', initListener);
	}
};

const stickyHeader = (block, duration, delay, type, offset = 0, scrollThreshold = 40) => {
	let lastScrollTop = 0;
	let accumulatedScroll = 0;

	block.style.transition = `all ${duration}ms ${type}`;

	const updateHeaderPosition = () => {
		const currentScroll = window.pageYOffset;
		if (currentScroll > block.offsetHeight + offset) {
			if (currentScroll > lastScrollTop) {
				block.style.top = `-${block.offsetHeight}px`;
        block.classList.add('sticky');
				block.style.transitionDelay = '0ms';
				accumulatedScroll = 0;
			} else {
				accumulatedScroll += lastScrollTop - currentScroll;

				if (accumulatedScroll >= scrollThreshold) {
					block.style.top = '0';
					block.style.transitionDelay = `${delay}ms`;
					accumulatedScroll = 0;
          block.classList.remove('sticky');
				}
			}
		} else {
			block.style.top = '0';
      block.classList.remove('sticky');
			block.style.transitionDelay = '0ms';
		}

		lastScrollTop = currentScroll <= 0 ? 0 : currentScroll;
	};

	const debounce = (func, wait) => {
		let timeout;

		return function executedFunction(...args) {
			const later = () => {
				clearTimeout(timeout);
				func(...args);
			};

			clearTimeout(timeout);
			timeout = setTimeout(later, wait);
		};
	};

	const debouncedUpdateHeader = debounce(updateHeaderPosition, 10);

	window.addEventListener('scroll', debouncedUpdateHeader);
};

// DinamicHeight
stickyHeader(header, 300, 100, 'linear', 0, 80);


document.addEventListener("DOMContentLoaded", function () {
  elementHeight(header, "header-height", headerTop);
});

document.addEventListener("DOMContentLoaded", () => {
  const leadModal = document.querySelector('[data-logika-modal]');
  if (!leadModal) return;
  const modalContainer = leadModal.querySelector('[data-target="lesson"]');
  if (!modalContainer) return;
  const closeButtons = leadModal.querySelectorAll('.modal-close');
  const firstInput = leadModal.querySelector('input[name="name"]');
  const courseInput = leadModal.querySelector('input[name="course_id"]');
	const campInput = leadModal.querySelector('input[name="camp_id"]');
	const formIdInput = leadModal.querySelector('input[name="form_id"]');
	const ageField = leadModal.querySelector('[data-logika-age-field]');
	const ageInput = ageField?.querySelector('select[name="child_age"]');
	const messageField = leadModal.querySelector('[data-logika-message-field]');
	const messageInput = messageField?.querySelector('[data-logika-message-input]');
	const modalTitle = leadModal.querySelector('[data-logika-modal-title]');
  let trigger = null;
  const closeLeadModal = () => {
    modalContainer.classList.remove('modal-open');
    leadModal.classList.remove('is-open');
    leadModal.classList.remove('is-director-message');
    leadModal.hidden = true;
    enableScroll();
    trigger?.focus();
  };
  const openLeadModal = (nextTrigger) => {
    trigger = nextTrigger;
    if (courseInput) courseInput.value = nextTrigger.dataset.logikaCourseId || '';
		if (campInput) campInput.value = nextTrigger.dataset.logikaCampId || '';
		const formId = nextTrigger.dataset.logikaFormId || 'trial_lesson';
		const isGiftCertificate = formId === 'gift_certificate';
		const isDirectorMessage = formId === 'director_message';
		if (formIdInput) formIdInput.value = formId;
		if (modalTitle) modalTitle.textContent = isDirectorMessage ? 'Відправити лист директору' : campInput?.value ? 'Запис на табір' : 'Перший урок — безкоштовно.';
		leadModal.classList.toggle('is-director-message', isDirectorMessage);
		if (ageField) ageField.hidden = isGiftCertificate || isDirectorMessage;
		if (ageInput) {
			ageInput.disabled = isGiftCertificate || isDirectorMessage;
			ageInput.required = !isGiftCertificate && !isDirectorMessage;
		}
		if (messageField) messageField.hidden = !isDirectorMessage;
		if (messageInput) messageInput.required = isDirectorMessage;
    modalContainer.classList.add('modal-open');
    leadModal.hidden = false;
    window.requestAnimationFrame(() => window.requestAnimationFrame(() => leadModal.classList.add('is-open')));
    disableScroll();
    window.setTimeout(() => firstInput?.focus({ preventScroll: true }), 100);
  };
  document.addEventListener('click', (event) => {
    const link = event.target.closest('a[href], button[data-path="lesson"]');
    if (!link) return;
    if (link.matches('a[href]')) {
      const url = new URL(link.href, window.location.href);
      if (url.origin !== window.location.origin || url.hash !== '#lead-form') return;
    }
    event.preventDefault();
    openLeadModal(link);
  });
  closeButtons.forEach((button) => button.addEventListener('click', closeLeadModal));
  leadModal.addEventListener('click', (event) => {
    if (event.target === leadModal) closeLeadModal();
  });
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && !leadModal.hidden) closeLeadModal();
  });
});

document.addEventListener("DOMContentLoaded", () => {
  const campModal = document.querySelector('[data-logika-camp-modal]');
  if (!campModal) return;
  const modalContainer = campModal.querySelector('[data-target="camps"]');
  if (!modalContainer) return;
  const closeButtons = campModal.querySelectorAll('.modal-close');
  let trigger = null;
  const closeCampModal = () => {
    modalContainer.classList.remove('modal-open');
    campModal.classList.remove('is-open');
    campModal.hidden = true;
    enableScroll();
    trigger?.focus();
  };
  const openCampModal = (nextTrigger) => {
    trigger = nextTrigger;
    modalContainer.classList.add('modal-open');
    campModal.hidden = false;
    window.requestAnimationFrame(() => window.requestAnimationFrame(() => campModal.classList.add('is-open')));
    disableScroll();
  };
  document.addEventListener('click', (event) => {
    const link = event.target.closest('a[data-path="camps"], button[data-path="camps"]');
    if (!link) return;
    event.preventDefault();
    openCampModal(link);
  });
  closeButtons.forEach((button) => button.addEventListener('click', closeCampModal));
  campModal.addEventListener('click', (event) => {
    if (event.target === campModal) closeCampModal();
  });
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && !campModal.hidden) closeCampModal();
  });
});

//----accordion----------------------------------
window.addEventListener("DOMContentLoaded", () => {
  accParrent &&
    accParrent.map(function (accordionParrent) {
      if (accordionParrent) {
        let multipleSetting = false;
        let breakpoinSetting = false;
        let defaultOpenSetting;

        multipleSetting = accordionParrent.dataset.single || false;
        breakpoinSetting = accordionParrent.dataset.breakpoint || false;

        const getAccordions = function (dataName = "[data-id]") {
          return accordionParrent.querySelectorAll(dataName);
        };

        const accordions = getAccordions();
        let openedAccordion = accordionParrent.querySelector(".accordion__content.active");

        const closeAccordion = function (accordion, className = "active") {
          accordion.style.maxHeight = 0;
          removeCustomClass(accordion, className);

          const itemParent = accordion.closest('.accordion__item, .menu-has-child');
          if (itemParent) {
            removeCustomClass(itemParent, className);
          }
        };

        const openAccordion = function (accordion, className = "active") {
          accordion.style.maxHeight = accordion.scrollHeight + "px";
          addCustomClass(accordion, className);

          const itemParent = accordion.closest('.accordion__item, .menu-has-child');
          if (itemParent) {
            addCustomClass(itemParent, className);
          }
        };

        const toggleAccordionButton = function (button, className = "active") {
          const childParrent = button.closest('.menu-has-child');
          toggleCustomClass(button, className);

          if(childParrent) {
            toggleCustomClass(childParrent, className);
          }
        };

        const checkIsAccordionOpen = function (accordion) {
          return accordion.classList.contains("active");
        };

        const accordionClickHandler = function (e) {
          e.preventDefault();
          const accordionContent = this.closest(".accordion__item, .menu-has-child")?.querySelector("[data-content]");
          if (!accordionContent) return;
          const isAccordionOpen = checkIsAccordionOpen(accordionContent);

          if (isAccordionOpen) {
            closeAccordion(accordionContent);
            toggleAccordionButton(this);
            openedAccordion = null;
          } else {
            if (
              openedAccordion != null &&
              multipleSetting === "true" &&
              (!breakpoinSetting || document.documentElement.clientWidth <= breakpoinSetting)
            ) {
              closeAccordion(openedAccordion);
              const previousButton = openedAccordion.closest(".accordion__item, .menu-has-child")?.querySelector("[data-id]");
              if (previousButton) toggleAccordionButton(previousButton);
              openedAccordion = null;
            }

            openAccordion(accordionContent);
            toggleAccordionButton(this);
            openedAccordion = accordionContent;
          }
        };

        const activateAccordion = function (accordions, handler) {
          for (const accordion of accordions) {
            accordion.addEventListener("click", handler);
          }
        };
        const accordionDefaultOpen = (currentId) => {
          const defaultOpenContent = accordionParrent.querySelector(
            `[data-content="${currentId}"]`
          );
          const defaultOpenButton = accordionParrent.querySelector(
            `[data-id="${currentId}"]`
          );

          if (!defaultOpenContent || !defaultOpenButton) {
            return;
          }

          openedAccordion = defaultOpenContent;

          toggleAccordionButton(defaultOpenButton);
          openAccordion(defaultOpenContent);
        };

        if (accordionParrent.dataset.default) {
          defaultOpenSetting = accordionParrent.dataset.default; // получает id аккордиона который будет открыт по умолчанию
          accordionDefaultOpen(defaultOpenSetting);
        }

        activateAccordion(accordions, accordionClickHandler);
      }
    });
});

//----burger------------------------------------
const mobileMenuHandler = function (mobileMenu, burger) {
  burger.forEach((btn) => {
    btn.addEventListener("click", function (e) {
      e.preventDefault();

      toggleCustomClass(mobileMenu, activeClass);
      toggleClassInArray(burger, activeClass);

      if (mobileMenu.classList.contains(activeClass)) {
        disableScroll();
        addCustomClass(header, "open-menu");
      } else {
        enableScroll();
        removeCustomClass(header, "open-menu");
      }
    });
  });
};

const hideMenuHandler = function ( mobileMenu, burger) {
  enableScroll();
  removeCustomClass(mobileMenu, activeClass);
  removeClassInArray(burger, activeClass);

  if (mobileMenu.classList.contains(activeClass)) {
    disableScroll();
    addCustomClass(header, "open-menu");
  } else {
    enableScroll();
    removeCustomClass(header, "open-menu");
  }
};

document.addEventListener("DOMContentLoaded", function () {
  mobileMenuHandler(mobileMenu, burger);

  if (mobileMenu) {
    mobileMenu.querySelectorAll("a").forEach(function (item) {
      if (item.parentElement && item.parentElement.classList.contains("menu-has-child")) {
        return;
      }
      item.addEventListener("click", function () {
          hideMenuHandler(mobileMenu, burger);
      });
    });

    mobileMenu.querySelectorAll(".menu-has-child > .menu-link").forEach(function (link) {
      link.addEventListener("click", function (e) {
        if (document.documentElement.clientWidth > 1024) return;
        e.preventDefault();
        const button = this.parentElement.querySelector(".menu-button");
        if (button) button.click();
      });
    });
  }

  if (mobileMenuCloseBtn) {
    mobileMenuCloseBtn.forEach((btn) => {
      btn.addEventListener("click", function (e) {
        hideMenuHandler(mobileMenu, burger);
      });
    });
  }

  if(dropdownToggles){
    dropdownToggles.forEach((toggle) => {
      toggle.addEventListener("click", function () {
        const parentItem = this.closest(".header__nav-item");
  
        document.querySelectorAll(".header__nav-item.active").forEach((item) => {
          if (item !== parentItem) {
            item.classList.remove("active");
          }
        });
  
        parentItem.classList.toggle("active");
      });
    });
  }
});

//----Sliders----------------------------------
document.addEventListener("DOMContentLoaded", function () {
  if (campGalleries.length > 0) {
    campGalleries.forEach(function (gallery) {
      const mainImage = gallery.querySelector("[data-gallery-main]");
      const thumbs = Array.from(gallery.querySelectorAll("[data-gallery-thumb]"));
      const prevBtn = gallery.querySelector("[data-gallery-prev]");
      const nextBtn = gallery.querySelector("[data-gallery-next]");

      if (!mainImage || thumbs.length === 0) {
        return;
      }

      let currentIndex = thumbs.findIndex(function (thumb) {
        return thumb.classList.contains("is-active");
      });

      if (currentIndex < 0) {
        currentIndex = 0;
      }

      const setActiveSlide = function (index) {
        const nextIndex = (index + thumbs.length) % thumbs.length;
        const activeThumb = thumbs[nextIndex];
        const nextSrc = activeThumb.dataset.galleryThumb;

        if (!nextSrc) {
          return;
        }

        mainImage.src = nextSrc;
        thumbs.forEach(function (thumb, thumbIndex) {
          const isActive = thumbIndex === nextIndex;
          thumb.classList.toggle("is-active", isActive);
          thumb.setAttribute("aria-pressed", isActive ? "true" : "false");
        });
        currentIndex = nextIndex;
      };

      thumbs.forEach(function (thumb, index) {
        thumb.addEventListener("click", function () {
          setActiveSlide(index);
        });
      });

      if (prevBtn) {
        prevBtn.addEventListener("click", function () {
          setActiveSlide(currentIndex - 1);
        });
      }

      if (nextBtn) {
        nextBtn.addEventListener("click", function () {
          setActiveSlide(currentIndex + 1);
        });
      }
    });
  }

  if (marqueeSectionSlider) {
    marqueeSectionSlider.forEach(function (slider) {
      const container = slider.querySelector(".swiper-container");

      const mainSwiper = new Swiper(container, {
        spaceBetween: 36,
        slidesPerView: "auto",
        speed: 4000,
        loop: true,
        allowTouchMove: false, 
        
        autoplay: {
          delay: 0, 
          disableOnInteraction: false,
        },
        
        observer: true,
        observeParents: true,
      });
    }
  )};

  if (englishSectionSlider) {
    englishSectionSlider.forEach(function (slider) {
      const container = slider.querySelector(".swiper-container");
      const nextBtn = slider.querySelector(".swiper-button-next");
      const prevBtn = slider.querySelector(".swiper-button-prev");

      const mainSwiper = new Swiper(container, {
        slidesPerView: "auto",
        spaceBetween: 10,
        speed: 1800,
        loop: true,
        observer: true,
        observeParents: true,
        navigation: {
          nextEl: nextBtn,
          prevEl: prevBtn,
        },
      });
    }
  )};

  if (categoriesCoursesSlider.length > 0) {
    categoriesCoursesSlider.forEach(function (slider) {
      const container = slider.querySelector(".swiper-container");
      
      // Піднімаємося до спільного батька (сесії), щоб знайти кнопки, які лежать вище в HTML
      const parentSection = slider.closest('.categories-section');
      const nextBtn = parentSection ? parentSection.querySelector(".swiper-button-next") : null;
      const prevBtn = parentSection ? parentSection.querySelector(".swiper-button-prev") : null;

      if (container) {
        const mainSwiper = new Swiper(container, {
          speed: 1800,
          // loop: true,
          observer: true,
          observeParents: true,
          watchSlidesProgress: true,
          navigation: {
            nextEl: nextBtn, // Тепер кнопки успішно зв'яжуться зі слайдером
            prevEl: prevBtn,
          },
          breakpoints: {
            360: { slidesPerView: 1.15, spaceBetween: 10 },
            576: { slidesPerView: 1.75, spaceBetween: 10 },
            768: { slidesPerView: 2, spaceBetween: 10 },
            991: { slidesPerView: 3, spaceBetween: 10 },
          },
        });
      }
    });
  }

  tripsSectionSlider.forEach(function (slider) {
    const container = slider.querySelector('.swiper-container');
    const section = slider.closest('.trips-section');

    if (container && section) {
      new Swiper(container, { speed: 1800, loop: true, observer: true, observeParents: true, watchSlidesProgress: true, navigation: { nextEl: section.querySelector('.swiper-button-next'), prevEl: section.querySelector('.swiper-button-prev') }, breakpoints: { 320: { slidesPerView: 1.2, centeredSlides: true, spaceBetween: 10 }, 577: { slidesPerView: 'auto', centeredSlides: false, spaceBetween: 20 } } });
    }
  });

  if (campsHighlightsSlider.length > 0) {
    campsHighlightsSlider.forEach(function (slider) {
      const container = slider.querySelector(".swiper-container");
      if (!container) return;

      let mainSwiper = null;

      const initOrDestroySlider = () => {
        const windowWidth = window.innerWidth;

        if (windowWidth <= 1024) {
          if (!mainSwiper) {
            mainSwiper = new Swiper(container, {
              speed: 1800,
              loop: true,
              observer: true,
              observeParents: true,
              watchSlidesProgress: true,
              spaceBetween: 10,
              slidesPerView: "auto",
            });
          }
        } else if (mainSwiper) {
          mainSwiper.destroy(true, true);
          mainSwiper = null;
        }
      };

      initOrDestroySlider();

      let resizeTimeout;
      window.addEventListener("resize", () => {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(initOrDestroySlider, 150);
      });
    });
  }

  testimonialsSlider.forEach(function (slider) {
    const container = slider.querySelector('.swiper-container');
    if (!container) return;

    let instance = null;
    const toggle = () => {
      if (window.innerWidth <= 1024 && !instance) instance = new Swiper(container, { speed: 1800, loop: true, observer: true, observeParents: true, watchSlidesProgress: true, spaceBetween: 20, slidesPerView: 'auto' });
      if (window.innerWidth > 1024 && instance) { instance.destroy(true, true); instance = null; }
    };
    toggle();
    window.addEventListener('resize', toggle);
  });

  if (campsHistorySlider.length > 0) {
    campsHistorySlider.forEach(function (slider) {
      const container = slider.querySelector(".swiper-container");

      const parentSection = slider.closest('.camp-history');
      const nextBtn = parentSection ? parentSection.querySelector(".swiper-button-next") : null;
      const prevBtn = parentSection ? parentSection.querySelector(".swiper-button-prev") : null;

      const storedCity = (() => {
        try {
          return localStorage.getItem("logika-city-id");
        } catch (error) {
          return null;
        }
      })();
      const slides = Array.from(slider.querySelectorAll(".nizhyn-school__video"));
      const initialSlide = Math.max(slides.findIndex((slide) => storedCity && slide.dataset.cityId === storedCity), 0);

      if (container) {
        new Swiper(container, {
          speed: 1800,
          initialSlide: initialSlide,
          observer: true,
          observeParents: true,
          loop: true,
          watchSlidesProgress: true,
          navigation: {
            nextEl: nextBtn,
            prevEl: prevBtn,
          },
          breakpoints: {
            360: {
              slidesPerView: 1,
              spaceBetween: 10,
            },
            1024: {
              slidesPerView: 2,
              spaceBetween: 20,
            },
          },
        });
      }
    });
  }

});


//----camp-history video---------------------------
document.addEventListener("click", (event) => {
  const button = event.target.closest(".camp-history .nizhyn-school__play");
  const card = button && button.closest(".nizhyn-school__video");

  if (!card || !card.dataset.videoId || card.querySelector("iframe")) return;

  const iframe = document.createElement("iframe");
  iframe.src = `https://www.youtube-nocookie.com/embed/${card.dataset.videoId}?autoplay=1&rel=0`;
  iframe.title = card.querySelector(".nizhyn-school__video-caption").textContent;
  iframe.allow = "autoplay; encrypted-media; picture-in-picture";
  iframe.allowFullscreen = true;
  card.append(iframe);
});

//---- Select ----------------------------------
const closeSelect = function (selectBody, select , className = "active") {
  selectBody.style.height = 0;
  removeCustomClass(select, className);
};

const openSelect = function (selectBody, select , className = "active") {
  selectBody.style.height = "fit-content";
  addCustomClass(select, className);
};

const checkIsSelectOpen = function (select) {
  return select.classList.contains('active');
}

const select = document.querySelectorAll("[data-select]");

if (select.length) {
  select.forEach((item) => {
    const selectCurrent = item.querySelector(".select__current");
    const selectInput = item.querySelector(".select__input");
    const selectOptions = [...item.querySelectorAll("svg")];
    const selectBody = item.querySelector(".select__body");

    selectOptions.map((option) => {
      option ? option.style.pointerEvents = "none" : '';
    });

    if (selectInput) {
      const currentId = selectCurrent.getAttribute("data-id");
      selectInput.setAttribute("value", currentId);
    }

    item.addEventListener("click", (e) => {
      if (e.target.tagName.toLowerCase() !== 'a') {
        e.preventDefault();
      }

      const isSelectOpen = checkIsSelectOpen(item);
      const el = e.target.dataset.type;
      const innerSelect = e.target.innerHTML;
      let items = item.querySelectorAll(`.select__list [data-id]`);
      let currentItem = item.querySelector(`.select__list [data-id='${selectInput.getAttribute("value")}']`)

      if (el === "option") {
        selectCurrent.innerHTML = innerSelect;
        selectInput.setAttribute("value", e.target.getAttribute("data-id"));
        selectCurrent.setAttribute("data-id", e.target.getAttribute("data-id"));
      }

      items.forEach(function (item) {item.style.display = "flex"});
      currentItem.style.display = "none";

      if (isSelectOpen) {
        closeSelect(selectBody, item);
      } else {
        openSelect(selectBody, item)
      }
    });


    document.addEventListener("click", function (event) {
      if (!item.contains(event.target) && checkIsSelectOpen(item)) {
        closeSelect(selectBody, item);
      }
    });
  });
}

document.querySelectorAll('.portfolio-section__viewport').forEach((viewport) => {
  viewport.querySelectorAll('img').forEach((image) => { image.draggable = false; });
  const featuredCard = viewport.querySelector('.portfolio-section__card--featured');
  const centerFeaturedCard = () => {
    if (!featuredCard || !window.matchMedia('(max-width: 1024px)').matches) return;
    viewport.scrollLeft = featuredCard.offsetLeft - (viewport.clientWidth - featuredCard.offsetWidth) / 2;
  };
  centerFeaturedCard();
  window.addEventListener('resize', centerFeaturedCard);
  let startX = 0;
  let startScrollLeft = 0;
  let dragged = false;

  viewport.addEventListener('pointerdown', (event) => {
    if (event.pointerType === 'mouse' && event.button !== 0) return;
    if (event.target.closest('a, button, input, select, textarea')) return;

    startX = event.clientX;
    startScrollLeft = viewport.scrollLeft;
    dragged = false;
    viewport.classList.add('is-dragging');
    viewport.setPointerCapture(event.pointerId);
  });

  viewport.addEventListener('pointermove', (event) => {
    if (!viewport.hasPointerCapture(event.pointerId)) return;

    const deltaX = event.clientX - startX;
    dragged ||= Math.abs(deltaX) > 4;
    if (dragged) event.preventDefault();
    viewport.scrollLeft = startScrollLeft - deltaX;
  });

  const stopDragging = (event) => {
    viewport.classList.remove('is-dragging');
    if (viewport.hasPointerCapture(event.pointerId)) viewport.releasePointerCapture(event.pointerId);
  };

  viewport.addEventListener('pointerup', stopDragging);
  viewport.addEventListener('pointercancel', stopDragging);
  viewport.addEventListener('click', (event) => {
    if (!dragged) return;

    event.preventDefault();
    event.stopPropagation();
    dragged = false;
  }, true);
});
