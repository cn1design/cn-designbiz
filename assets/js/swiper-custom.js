document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.swiper-container').forEach(container => {
      new Swiper(container, {
        loop: true,
        speed: 400,
        spaceBetween: 20,
        lazy: true,
        autoplay: {
          delay: 3000,
          disableOnInteraction: false
        },
        navigation: {
          nextEl: container.querySelector('.swiper-button-next'),
          prevEl: container.querySelector('.swiper-button-prev')
        },
        pagination: {
          el: container.querySelector('.swiper-pagination'),
          clickable: true
        },
        breakpoints: {
          0: { slidesPerView: 1.2 },
          768: { slidesPerView: 2 },
          1024: { slidesPerView: 3 },
          1440: { slidesPerView: 4 }
        }
      });
    });
  });
  