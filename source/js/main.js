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

const mainSliders = document.querySelectorAll('.banner-section__slider');
const categoriesSliders = document.querySelectorAll('.categories-slider');
// const productsSliders = document.querySelectorAll('.products-slider');
const productGallery = document.querySelectorAll('.product__gallery');

const categoriesPageSLider = document.querySelectorAll('.top-section__slider');

const miniCartSlider = document.querySelectorAll('.mini-cart__slider');

const counters = document.querySelectorAll('[data-counter]');
const addReviewBtnTrigger = document.querySelector('.reviews-section__feedback-trigger');
const openCartBtn = document.querySelectorAll('.open-cart');
const starsRating = document.querySelectorAll('.star-ratings');
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
  if(mainSliders){
    mainSliders.forEach(function (slider) {
      const container = slider.querySelector(".swiper-container");
      const nextBtn = slider.querySelector(".swiper-button-next");
      const prevBtn = slider.querySelector(".swiper-button-prev");
  
      const mainSwiper = new Swiper(container, {
        spaceBetween: 20,
        slidesPerView: 1,
        speed: 1800,
        watchOverflow: true,
        loop:true,
        observer: true,
        observeParents: true,
        navigation: {
          nextEl: nextBtn,
          prevEl: prevBtn,
        },
        breakpoints: {
          320: {
            spaceBetween: 10,
          },
          1024: {
            spaceBetween: 20,
          },
        },
      });
    });

    let swipers = [];

    function initSliders() {
      if(categoriesSliders) {
        categoriesSliders.forEach(function (slider) {
          const container = slider.querySelector(".swiper-container");
          const pagination = slider.querySelector(".swiper-pagination");
      
          if (window.innerWidth < 1025) {
            if (!container.swiper) {
              const swiper = new Swiper(container, {
                // watchOverflow: true,
                loop: true,
                observer: true,
                observeParents: true,
                pagination: {
                  el: pagination,
                  clickable: true,
                },
                breakpoints: {
                  320: {
                    slidesPerView: 1.15,
                    spaceBetween: 20,
                  },
                  576: {
                    slidesPerView: 2,
                    spaceBetween: 20,
                  },
                  768: {
                    slidesPerView: 3,
                    spaceBetween: 20,
                  },
                },
              });
              swipers.push(swiper);
            }
          } else {
            if (container.swiper) {
              container.swiper.destroy(true, true);
            }
          }
        });
      }
    }
    
    window.addEventListener('load', initSliders);
    window.addEventListener('resize', initSliders); 
  }

  if (productGallery.length) {
    productGallery.forEach(function (parent) {
      const mainSwiper = parent.querySelector(".product__gallery-images");
      const subSwiper = parent.querySelector(".product__gallery-thumbs");
      const nextBtn = parent.querySelector(".swiper-button-next");
      const prevBtn = parent.querySelector(".swiper-button-prev");
      const pagination = parent.querySelector(".swiper-pagination");
  
      if (!mainSwiper || !subSwiper) return;

      let subSlider;

  
      if (subSwiper) {
        subSlider = new Swiper(subSwiper, {
          spaceBetween: 15,
          slidesPerView: "auto",
          freeMode: true,
          watchOverflow: true,
          watchSlidesProgress: true,
        });
      }

      const mainSlider = new Swiper(mainSwiper, {
        loop: true,
        spaceBetween: 10,
        navigation: {
          nextEl: nextBtn,
          prevEl: prevBtn,
        },
        pagination: {
          el: pagination,
          clickable: true,
        },
        thumbs: {
          swiper: subSlider,
        },
      });
    });
  }

  if(categoriesPageSLider) {
    categoriesPageSLider.forEach(function (slider) {
      const container = slider.querySelector(".swiper-container");
      const nextBtn = slider.querySelector(".swiper-button-next");
      const prevBtn = slider.querySelector(".swiper-button-prev");
  
      const categoriesPageSwiper = new Swiper(container, {
        speed: 1800,
        watchOverflow: true,
        observer: true,
        observeParents: true,
        navigation: {
          nextEl: nextBtn,
          prevEl: prevBtn,
        },
        breakpoints: {
          320: {
            spaceBetween: 10,
            slidesPerView: 1.5,
          },
          576: {
            spaceBetween: 10,
            slidesPerView: 3.5,
          },
          768: {
            spaceBetween: 15,
            slidesPerView: 4.5,
          },
          1024: {
            spaceBetween: 20,
            slidesPerView: 5.5,
          },
        },
      });
    });
  }

  if(miniCartSlider) {
    miniCartSlider.forEach(function (slider) {
      const container = slider.querySelector(".swiper-container");
      const nextBtn = slider.querySelector(".swiper-button-next");
      const prevBtn = slider.querySelector(".swiper-button-prev");
  
      const miniCartSwiper = new Swiper(container, {
        spaceBetween: 16,
        slidesPerView: 1.3,
        loop: true,
        speed: 1800,
        watchOverflow: true,
        observer: true,
        observeParents: true,
        navigation: {
          nextEl: nextBtn,
          prevEl: prevBtn,
        },
      });
    });
  }
});


// document.addEventListener('DOMContentLoaded', function(){
//     let swipers = [];

//     function initProductsSliders() {
//       if(productsSliders) {
//         productsSliders.forEach(function (slider) {
//           const container = slider.querySelector(".swiper-container");
      
//           if (window.innerWidth < 1025) {
//             if (!container.swiper) {
//               const swiper = new Swiper(container, {
//                 slidesPerView: 1.15,
//                 spaceBetween: 16,
//                 loop: true,
//                 observer: true,
//                 observeParents: true,
//                 breakpoints: {
//                   320: {
//                     slidesPerView: 1.3,
//                     spaceBetween: 16,
//                   },
//                   576: {
//                     slidesPerView: 2,
//                     spaceBetween: 20,
//                   },
//                   768: {
//                     slidesPerView: 3,
//                     spaceBetween: 20,
//                   },
//                 },
//               });
//               swipers.push(swiper);
//             }
//           } else {
//             if (container.swiper) {
//               container.swiper.destroy(true, true);
//             }
//           }
//         });
//       }
//     }
    
//     window.addEventListener('load', initProductsSliders);
//     window.addEventListener('resize', initProductsSliders); 
// })


//---- Product Couner ----------------------------------
if (counters) {
  counters.forEach(counter => {
    const iconID = counter.getAttribute('data-counter');

    counter.addEventListener('click', e => {
      const target = e.target.closest('[data-btn-plus], [data-btn-minus]');
      if (!target) return;

      const input = counter.querySelector('[data-input-value]');
      let value = parseInt(input?.value || 0, 10);

      const minusBtn = counter.querySelector('[data-btn-minus]');
      const svgUse = minusBtn?.querySelector('svg use');

      if (target.hasAttribute('data-btn-plus')) {
        value++;
        input.value = value;

        // Если стало больше 1, вернуть иконку "минус"
        if (value > 1 && svgUse) {
          svgUse.setAttribute('href', 'img/sprite/sprite.svg#minus');
        }

      } else if (target.hasAttribute('data-btn-minus')) {
        // Исправленное условие: если trash — до 0, иначе до 1
        if (value > (iconID === 'trash' ? 0 : 1)) {
          value--;
        }

        input.value = value;

        if (svgUse && iconID === 'trash') {
          if (value === 0) {
            toggleAlert();
            svgUse.setAttribute('href', 'img/sprite/sprite.svg#trash');
          } else {
            svgUse.setAttribute('href', 'img/sprite/sprite.svg#minus');
          }
        }
      }
    });
  });

  function toggleAlert() {
    document.querySelector('.mini-cart__alert').classList.toggle('active');
    document.querySelector('.mini-cart__box').classList.toggle('alert');
  }

  document.querySelector('.mini-cart__box .btn-remove').addEventListener('click', () => {
    toggleAlert();
  });

  document.querySelector('.mini-cart__box .btn-cancel').addEventListener('click', () => {
    toggleAlert();
  });
}

//---- Review block ----------------------------------
if(addReviewBtnTrigger) {
  addReviewBtnTrigger.addEventListener('click', ()=>{
    const reviewFormBlock = document.querySelector('.reviews-section__summary-add');

    reviewFormBlock.classList.add('active');
  })
}

//---- Star rating ----------------------------------
if(starsRating) {
  starsRating.forEach(rating => {
    const stars = rating.querySelectorAll('svg');
    let initialStars = parseFloat(rating.dataset.stars) || 0;
  
    for (let i = 0; i < Math.min(Math.ceil(initialStars), stars.length); i++) {
      stars[i].classList.add('active');
    }
  
    const summarySpan = rating.parentElement.querySelector('span[data-stars-summary]');
    if (summarySpan) {
      summarySpan.textContent = initialStars.toFixed(1);
    }
  
    stars.forEach((star, index) => {
      star.addEventListener('click', () => {
        let value = index + 1;
  
        stars.forEach(s => s.classList.remove('active'));
  
        for (let i = 0; i < value; i++) {
          stars[i].classList.add('active');
        }
  
        rating.setAttribute('data-stars', value);
  
        if (summarySpan) {
          summarySpan.textContent = value.toFixed(1);
        }
  
        console.log(`Вы выбрали рейтинг: ${value.toFixed(1)}`);
      });
    });
  })
}

//---- Add to cart ----------------------------------
if (openCartBtn) {
  openCartBtn.forEach(btn => {
    btn.addEventListener('click', () => {
      const miniCart = document.querySelector('.mini-cart');
      const closeBtns = document.querySelectorAll('.close');
      const mobileMenus = document.querySelectorAll('.mobile');
      const burgers = document.querySelectorAll('.burger');

      // Закрыть активные мобильные меню
      mobileMenus.forEach(menu => {
        if (menu.classList.contains('active')) {
          menu.classList.remove('active');
        }
      });

      // Сброс состояния бургеров
      burgers.forEach(burger => {
        if (burger.classList.contains('active')) {
          burger.classList.remove('active');
        }
      });

      if (miniCart) {
        miniCart.classList.toggle('active');

        // Блокировка скролла на мобильных
        if (window.innerWidth <= 1025) {
          disableScroll();
        }

        // Кнопки закрытия мини-корзины
        closeBtns.forEach(closeBtn => {
          closeBtn.addEventListener('click', () => {
            miniCart.classList.remove('active');
            enableScroll();
          });
        });

        // Закрытие при клике вне корзины
        function handleOutsideClick(e) {
          if (!miniCart.contains(e.target) && !btn.contains(e.target)) {
            miniCart.classList.remove('active');

            if (window.innerWidth >= 768) {
              enableScroll();
            }

            document.removeEventListener('click', handleOutsideClick);
          }
        }

        // Отложенный слушатель для внешнего клика
        setTimeout(() => {
          document.addEventListener('click', handleOutsideClick);
        }, 0);
      }
    });
  });
}

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

//---- Range ----------------------------------
const rangeContainer = document.querySelector('.range-container');

if(rangeContainer){
  new DoubleRangeSlider('.range-container');
}



// DinamicHeight
stickyHeader(header, 300, 100, 'linear', 0, 80);


document.addEventListener("DOMContentLoaded", function () {
  elementHeight(header, "header-height");
});

