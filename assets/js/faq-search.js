document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('faq-search-form');
    if (!form) {
        return;
    }

    const input = document.getElementById('faq-search-input');
    const contentContainer = document.getElementById('faq-content-original');
    const allPanes = Array.from(contentContainer.querySelectorAll('.kt-accordion-pane'));
    const allWraps = Array.from(contentContainer.querySelectorAll('.kt-accordion-wrap, .wp-block-group'));
    const allHeadings = Array.from(contentContainer.querySelectorAll('h1, h2, h3, h4, h5, h6'));

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        performSearch();
    });
    
    // リアルタイム検索（入力中の検索）
    let searchTimeout;
    input.addEventListener('keyup', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(performSearch, 300); // 300ミリ秒待ってから検索実行
    });

    function performSearch() {
        const searchQuery = input.value.toLowerCase().trim();

        // 動的に追加された要素を削除
        const existingTitle = contentContainer.querySelector('.faq-search-title');
        if (existingTitle) existingTitle.remove();
        
        const existingBackButton = contentContainer.querySelector('.faq-back-link-container');
        if (existingBackButton) existingBackButton.remove();

        // 全要素を表示状態に戻す
        allPanes.forEach(el => el.classList.remove('hidden'));
        allWraps.forEach(el => el.classList.remove('hidden'));
        allHeadings.forEach(el => el.classList.remove('hidden'));

        if (!searchQuery) {
            return;
        }

        // 検索タイトルをコンテンツの先頭に追加
        const titleEl = document.createElement('h2');
        titleEl.className = 'faq-search-title';
        titleEl.innerHTML = '検索結果: "' + escapeHTML(input.value) + '"';
        contentContainer.insertBefore(titleEl, contentContainer.firstChild);

        allWraps.forEach(el => el.classList.add('hidden'));
        allPanes.forEach(el => el.classList.add('hidden'));
        allHeadings.forEach(el => el.classList.add('hidden'));

        const matchingPanes = allPanes.filter(pane => {
            return pane.innerHTML.toLowerCase().includes(searchQuery);
        });
        
        if (matchingPanes.length > 0) {
            matchingPanes.forEach(pane => {
                pane.classList.remove('hidden');
                pane.querySelectorAll('h1, h2, h3, h4, h5, h6').forEach(h => {
                    if (h.textContent.toLowerCase().includes(searchQuery)) {
                        h.classList.remove('hidden');
                    }
                });
                let parent = pane.parentElement;
                while (parent && parent !== document.body) {
                    if (parent.matches('.kt-accordion-wrap, .kt-accordion-pane, .wp-block-group')) {
                        parent.classList.remove('hidden');
                    }
                    const prevSibling = parent.previousElementSibling;
                    if (prevSibling && prevSibling.matches('h1, h2, h3, h4, h5, h6')) {
                        prevSibling.classList.remove('hidden');
                    }
                    parent = parent.parentElement;
                }
            });
        } else {
             const noResultEl = document.createElement('p');
             noResultEl.textContent = '検索結果が見つかりませんでした。';
             titleEl.appendChild(noResultEl);
        }

        // 「戻る」ボタンをコンテンツの最後に追加
        const backButtonContainer = document.createElement('div');
        backButtonContainer.className = 'faq-back-link-container';
        const backLink = document.createElement('a');
        backLink.href = window.location.pathname;
        backLink.className = 'faq-back-link';
        backLink.innerHTML = '← すべての質問に戻る';
        backButtonContainer.appendChild(backLink);
        contentContainer.appendChild(backButtonContainer);
    }
    
    function escapeHTML(str) {
        return str.replace(/[&<>"']/g, function(match) {
            return {
                '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
            }[match];
        });
    }
}); 