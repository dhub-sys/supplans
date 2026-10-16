/**
 * Support Plans – extra blocks on offer services: "＋ Λίστα" (card list with title,
 * description, 1/2 columns, one item per line) and "＋ Κείμενο" (formatted text).
 */
(function () {
  'use strict';
  var counter = 0;

  function add(container, data) {
    var type = data && data.type === 'text' ? 'text' : 'list';
    var tpl = document.getElementById(type === 'text' ? 'sp-text-block-template' : 'sp-list-block-template');
    if (!tpl || !container) return null;
    var prefix = container.getAttribute('data-prefix');
    var key = 'n' + Date.now().toString(36) + (counter++);
    var wrap = document.createElement('div');
    wrap.innerHTML = tpl.innerHTML.replace(/__P__/g, prefix).replace(/__k__/g, key).trim();
    var block = wrap.firstElementChild;
    if (data) {
      if (type === 'text') {
        block.querySelector('textarea').value = data.body || '';
      } else {
        block.querySelector('.sp-list-title').value = data.title || '';
        block.querySelector('textarea[name$="[desc]"]').value = data.desc || '';
        block.querySelector('textarea[name$="[items]"]').value = (data.items || []).join('\n');
        var radio = block.querySelector('input[type=radio][value="' + (data.cols === 2 ? 2 : 1) + '"]');
        if (radio) radio.checked = true;
      }
    }
    container.appendChild(block);
    if (window.SpEditor) window.SpEditor.attachAll(block);
    container.dispatchEvent(new Event('input', { bubbles: true }));
    return block;
  }

  document.addEventListener('click', function (e) {
    var addList = e.target.closest && e.target.closest('[data-list-add]');
    var addText = e.target.closest && e.target.closest('[data-text-add]');
    if (addList || addText) {
      var btn = addList || addText;
      var container = btn.closest('.sp-lists-wrap').querySelector('[data-lists]');
      var block = add(container, addText ? { type: 'text' } : null);
      if (block) {
        var focus = block.querySelector('.sp-list-title') || block.querySelector('.sp-ed-area');
        if (focus) focus.focus();
      }
      return;
    }
    var rm = e.target.closest && e.target.closest('[data-list-remove]');
    if (rm) {
      var b = rm.closest('[data-list-block]');
      var filled = Array.prototype.some.call(b.querySelectorAll('textarea'), function (t) { return t.value.trim() !== ''; });
      if (filled && !confirm(b.classList.contains('is-text') ? 'Να αφαιρεθεί το κείμενο;' : 'Να αφαιρεθεί η λίστα;')) return;
      var c = b.parentElement;
      b.remove();
      c.dispatchEvent(new Event('input', { bubbles: true }));
    }
  });

  window.SpLists = { add: add };
})();
