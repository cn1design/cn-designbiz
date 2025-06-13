document.addEventListener('DOMContentLoaded', function () {
  const modalButtons = document.querySelectorAll('.modal-open-button');
  const closeButtons = document.querySelectorAll('.custom-modal-close');

  modalButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      const modal = document.querySelector(btn.dataset.modalTarget);
      if (modal) {
        modal.style.display = 'block'; // 表示状態にする
        setTimeout(() => {
          modal.classList.add('show'); // アニメーション用クラス追加
        }, 10); // レイアウト確定後に発火
      }
    });
  });

  closeButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      const modal = btn.closest('.custom-modal');
      if (modal) {
        modal.classList.remove('show');
        setTimeout(() => {
          modal.style.display = 'none'; // 非表示
        }, 300); // アニメーション完了後
      }
    });
  });

  // オーバーレイクリックでも閉じる
  document.addEventListener('click', e => {
    if (e.target.matches('.custom-modal-overlay')) {
      const modal = e.target.closest('.custom-modal');
      if (modal) {
        modal.classList.remove('show');
        setTimeout(() => {
          modal.style.display = 'none';
        }, 300);
      }
    }
  });

  // モーダル内の空の <p> 削除（アニメーション後）
  document.addEventListener('click', function (e) {
    if (e.target.matches('.modal-open-button')) {
      setTimeout(() => {
        const modalInner = document.querySelector('.custom-modal-content .custom-modal-inner');
        if (modalInner) {
          modalInner.querySelectorAll('p:empty').forEach(p => p.remove());
        }
      }, 500); // アニメーション完了後にも安心
    }
  });

  // サイドバー状態監視（classで切り替え）
  const body = document.body;
  const observer = new MutationObserver(() => {
    const sidebar = document.querySelector('.ld-focus-sidebar');
    if (sidebar && sidebar.offsetWidth === 0) {
      body.classList.add('sidebar-closed');
    } else {
      body.classList.remove('sidebar-closed');
    }
  });

  const target = document.querySelector('.ld-focus-sidebar') || document.body;
  observer.observe(target, { attributes: true, childList: true, subtree: true });
});
