/**
 * Support Plans – structured lists on offer services ("＋ Λίστα").
 * Each list has a title, 1 or 2 columns and one item per line; the PDF shows it as a card.
 */
(function () {
  'use strict';
  var counter = 0;

  function add(container, data) {
    var tpl = document.getElementById('sp-list-block-template');
    if (!tpl || !container) return null;
    var prefix = container.getAttribute('data-prefix');
    var key = 'n' + Date.now().toString(36) + (counter++);
    var wrap = document.createElement('div');
    wrap.innerHTML = tpl.innerHTML.replace(/__P__/g, prefix).replace(/__k__/g, key).trim();
    var block = wrap.firstElementChild;
    if (data) {
      block.querySelector('.sp-list-title').value = data.title || '';
      block.querySelector('textarea').value = (data.items || []).join('\n');
      var radio = block.querySelector('input[type=radio][value="' + (data.cols === 2 ? 2 : 1) + '"]');
      if (radio) radio.checked = true;
    }
    container.appendChild(block);
    container.dispatchEvent(new Event('input', { bubbles: true }));
    return block;
  }

  document.addEventListener('click', function (e) {
    var addBtn = e.target.closest && e.target.closest('[data-list-add]');
    if (addBtn) {
      var block = add(addBtn.parentElement.querySelector('[data-lists]'));
      if (block) block.querySelector('.sp-list-title').focus();
      return;
    }
    var rm = e.target.closest && e.target.closest('[data-list-remove]');
    if (rm) {
      var b = rm.closest('[data-list-block]');
      var hasItems = b.querySelector('textarea').value.trim() !== '';
      if (hasItems && !confirm('Να αφαιρεθεί η λίστα;')) return;
      var c = b.parentElement;
      b.remove();
      c.dispatchEvent(new Event('input', { bubbles: true }));
    }
  });

  window.SpLists = { add: add };
})();
