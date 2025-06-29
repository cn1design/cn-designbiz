document.addEventListener('DOMContentLoaded', function () {
  const buttons = document.querySelectorAll('.fixed-tips-index .wp-block-button');
  const sections = [
    document.getElementById('material-site'),
    document.getElementById('banner-trace-sheet'),
    document.getElementById('question-session'),
    document.getElementById('seminar-video')
  ];

  // Intersection Observerの設定
  const observerOptions = {
    root: null,
    rootMargin: '0px 0px -400px 0px', // 下端から400px手前で交差判定
    threshold: 0
  };

  let activeIndex = -1;
  let lastScrollY = window.scrollY;
  let scrollTimeout;
  let isFooterVisible = false;

  const observer = new IntersectionObserver((entries) => {
    // 画面内に入っているセクションを配列で取得
    const visibleSections = entries
      .filter(entry => entry.isIntersecting)
      .map(entry => sections.indexOf(entry.target));

    // 一番下に近い（＝最後に交差した）セクションをアクティブに
    if (visibleSections.length > 0) {
      const newIndex = Math.max(...visibleSections);
      if (activeIndex !== newIndex) {
        buttons.forEach(btn => btn.classList.remove('is-active'));
        if (buttons[newIndex]) {
          buttons[newIndex].classList.add('is-active');
        }
        activeIndex = newIndex;
      }
    }
  }, observerOptions);

  sections.forEach(section => {
    if (section) observer.observe(section);
  });

  const tipsIndex = document.querySelector('.fixed-tips-index');
  // 目次ボタンの初期位置を監視するためのダミー要素
  let sentinel = document.createElement('div');
  sentinel.style.position = 'absolute';
  sentinel.style.top = '0';
  sentinel.style.width = '100%';
  sentinel.style.height = tipsIndex.offsetHeight + 'px'; // 目次ボタンと同じ高さに
  tipsIndex.parentNode.insertBefore(sentinel, tipsIndex);

  // フッター付近の.my-page__contentが見えたら非表示
  const myPageContent = document.querySelector('.my-page__content');
  if (myPageContent) {
    const footerObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        isFooterVisible = entry.isIntersecting;
        handleScroll(); // 状態が変わったら即時反映
      });
    }, {
      root: null,
      threshold: 0.1 // 10%以上見えたら非表示
    });
    footerObserver.observe(myPageContent);
  }

  // スクロール位置の監視とアニメーション制御
  const handleScroll = () => {
    if (scrollTimeout) {
      window.cancelAnimationFrame(scrollTimeout);
    }

    scrollTimeout = window.requestAnimationFrame(() => {
      const currentScrollY = window.scrollY;
      const isMobile = window.innerWidth <= 799;

      if (isMobile) {
        // モバイル時のスクロール制御
        if (currentScrollY > 700 && !isFooterVisible) {
          if (!tipsIndex.classList.contains('is-fixed')) {
            tipsIndex.classList.add('is-fixed');
          }
        } else {
          tipsIndex.classList.remove('is-fixed');
        }
      }
      
      lastScrollY = currentScrollY;
    });
  };

  // スクロールイベントリスナー
  window.addEventListener('scroll', handleScroll);

  // リサイズイベントリスナー
  window.addEventListener('resize', () => {
    if (window.innerWidth > 799) {
      tipsIndex.classList.remove('is-fixed');
    } else {
      handleScroll(); // リサイズ時にスクロール位置を再チェック
    }
  });

  // 初期表示時のチェック
  handleScroll();

  // Intersection Observerで目次ボタンが消えたら.is-fixedを付与
  const fixedObserver = new window.IntersectionObserver(
    (entries) => {
      if (window.innerWidth > 799) {
        tipsIndex.classList.remove('is-fixed');
        return;
      }
      if (!entries[0].isIntersecting) {
        tipsIndex.classList.add('is-fixed');
      } else {
        tipsIndex.classList.remove('is-fixed');
      }
    },
    {
      root: null,
      threshold: 0,
    }
  );
  fixedObserver.observe(sentinel);
}); 
