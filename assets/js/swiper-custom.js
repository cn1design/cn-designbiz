document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.swiper-container').forEach(container => {
      new Swiper(container, {
        loop: true,
        speed: 400,
        spaceBetween: 20,
        lazy: true,
        centeredSlides: true,
        // autoplay: {
        //   delay: 6000,
        //   disableOnInteraction: false
        // },
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
          768: { slidesPerView: 1.5 },
          1024: { slidesPerView: 1.8 },
          1440: { slidesPerView: 2.8 }
        }
      });
    });
  });
  