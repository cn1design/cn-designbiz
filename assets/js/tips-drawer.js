(function () {
    'use strict';
  
    document.addEventListener('DOMContentLoaded', function () {
      const tipsPage = document.querySelector('.tips_page');
      if (!tipsPage) return;
  
      const drawer = tipsPage.querySelector('.fixed-tips-index');
      const toggleBtn = document.getElementById('drawerToggle');
  
      if (!drawer || !toggleBtn) {
        console.warn('ドロワーまたはトグルボタンが見つかりません');
        return;
      }
  
      // オーバーレイを生成
      const overlay = document.createElement('div');
      overlay.className = 'drawer-overlay';
      document.body.appendChild(overlay);
  
      // 閉じるボタンを生成（均等な×）
      const closeButton = document.createElement('button');
      closeButton.innerHTML = '&times;';
  
      function openDrawer() {
        drawer.classList.add('open');
        toggleBtn.classList.add('open');
        overlay.classList.add('open');
        document.body.classList.add('drawer-open');
      }
  
      function closeDrawer() {
        drawer.classList.remove('open');
        toggleBtn.classList.remove('open');
        overlay.classList.remove('open');
        document.body.classList.remove('drawer-open');
      }
  
      toggleBtn.addEventListener('click', () => {
        drawer.classList.contains('open') ? closeDrawer() : openDrawer();
      });
  
      overlay.addEventListener('click', closeDrawer);
      closeButton.addEventListener('click', closeDrawer);
      document.addEventListener('keydown', e => {
        if (e.key === 'Escape') closeDrawer();
      });
  
      // ページ内リンククリックでスムーススクロール＋閉じる
      drawer.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
          const target = document.querySelector(this.getAttribute('href'));
          if (target) {
            e.preventDefault();
            closeDrawer();
            target.scrollIntoView({ behavior: 'smooth' });
          }
        });
      });
  
      // ハンバーガー初期非表示 → スクロール位置に応じて表示/非表示
      toggleBtn.style.opacity = '0';
      toggleBtn.style.transition = 'opacity 0.3s ease';
      
      function updateToggleVisibility() {
        if (window.scrollY > 100) {
          toggleBtn.style.opacity = '1';
          toggleBtn.classList.add('visible');
        } else {
          toggleBtn.style.opacity = '0';
          toggleBtn.classList.remove('visible');
        }
      }
      
      // 初期状態を設定
      updateToggleVisibility();
      
      // スクロールイベントで表示/非表示を制御
      window.addEventListener('scroll', updateToggleVisibility);
    });
  })();
  