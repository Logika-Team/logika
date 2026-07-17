//-----vars---------------------------------------
const windowEl = window;
const documentEl = document;
const htmlEl = document.documentElement;
const bodyEl = document.body;
const activeClass = 'active';
const activeClassMode = 'mode';
const header = document.querySelector('header');
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
const gallerySectionSlider = document.querySelectorAll('.gallery-section__slider');
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
  const fixBlocks = document?.querySelectorAll(".fixed-block");
  const pagePosition = window.scrollY;
  const paddingOffset = `${window.innerWidth - bodyEl.offsetWidth}px`;

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
  const fixBlocks = document?.querySelectorAll(".fixed-block");
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

const elementHeight = (el, variableName) => {
  if(el) {
    function initListener(){
      const elementHeight = el.offsetHeight;
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
  elementHeight(header, "header-height");
});

document.addEventListener("DOMContentLoaded", () => {
  const leadModal = document.querySelector('[data-logika-lead-modal]');
  if (!leadModal) return;

  const closeButtons = leadModal.querySelectorAll('[data-logika-lead-modal-close]');
  const firstInput = leadModal.querySelector('input[name="name"]');
  const courseInput = leadModal.querySelector('input[name="course_id"]');
  let trigger = null;

  const closeLeadModal = () => {
    leadModal.hidden = true;
    enableScroll();
    trigger?.focus();
  };

  const openLeadModal = (nextTrigger) => {
    trigger = nextTrigger;
    courseInput.value = nextTrigger.dataset.logikaCourseId || '';
    leadModal.hidden = false;
    disableScroll();
    window.setTimeout(() => firstInput?.focus(), 0);
  };

  document.addEventListener('click', (event) => {
    const link = event.target.closest('a[href]');
    if (!link || new URL(link.href, window.location.href).hash !== '#lead-form') return;

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

//----accordion----------------------------------
window.addEventListener("DOMContentLoaded", () => {
  accParrent &&
    accParrent.map(function (accordionParrent) {
      if (accordionParrent) {
        let multipleSetting = false;
        let breakpoinSetting = false;
        let defaultOpenSetting;

        if (
          accordionParrent.dataset.single &&
          accordionParrent.dataset.breakpoint
        ) {
          multipleSetting = accordionParrent.dataset.single; // true - включает сингл аккордион
          breakpoinSetting = accordionParrent.dataset.breakpoint; // брейкпоинт сингл режима (если он true)
        }

        const getAccordions = function (dataName = "[data-id]") {
          return accordionParrent.querySelectorAll(dataName);
        };

        const accordions = getAccordions();
        let openedAccordion = null;

        const closeAccordion = function (accordion, className = "active") {
          accordion.style.maxHeight = 0;
          removeCustomClass(accordion, className);
        };

        const openAccordion = function (accordion, className = "active") {
          accordion.style.maxHeight = accordion.scrollHeight + "px";
          addCustomClass(accordion, className);
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
          let curentDataNumber = this.dataset.id;

          toggleAccordionButton(this);
          const accordionContent = accordionParrent.querySelector(
            `[data-content="${curentDataNumber}"]`
          );
          const isAccordionOpen = checkIsAccordionOpen(accordionContent);

          if (isAccordionOpen) {
            closeAccordion(accordionContent);
            openedAccordion = null;
          } else {
            if (openedAccordion != null) {
              const mobileSettings = () => {
                let containerWidth = document.documentElement.clientWidth;
                if (
                  containerWidth <= breakpoinSetting &&
                  multipleSetting === "true"
                ) {
                  closeAccordion(openedAccordion);
                  toggleAccordionButton(
                    accordionParrent.querySelector(
                      `[data-id="${openedAccordion.dataset.content}"]`
                    )
                  );
                }
              };

              window.addEventListener("resize", () => {
                mobileSettings();
              });
              mobileSettings();
            }

            openAccordion(accordionContent);
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
      item.addEventListener("click", function () {
          hideMenuHandler(mobileMenu, burger);
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
          slidesPerView: 3,
          spaceBetween: 10,
          speed: 1800,
          // loop: true,
          observer: true,
          observeParents: true,
          watchSlidesProgress: true,
          navigation: {
            nextEl: nextBtn, // Тепер кнопки успішно зв'яжуться зі слайдером
            prevEl: prevBtn,
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

  gallerySectionSlider.forEach(function (slider) {
    const container = slider.querySelector('.swiper-container');
    let instance = null;
    const toggle = () => {
      if (window.innerWidth <= 1024 && !instance) instance = new Swiper(container, { speed: 1800, loop: true, observer: true, observeParents: true, watchSlidesProgress: true, spaceBetween: 10, breakpoints: { 320: { slidesPerView: 1.2, spaceBetween: 10 }, 576: { slidesPerView: 2, spaceBetween: 15 } } });
      if (window.innerWidth > 1024 && instance) { instance.destroy(true, true); instance = null; }
    };
    if (container) { toggle(); window.addEventListener('resize', toggle); }
  });


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
