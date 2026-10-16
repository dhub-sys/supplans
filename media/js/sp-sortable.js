/**
 * Support Plans – drag & drop ordering.
 *
 * <div data-sortable>               container (or <tbody data-sortable>)
 *   <div data-sort-item>            each sortable row
 *     <button data-sort-handle>     grip handle (⋮⋮)
 *
 * Works with mouse, touch and pen (pointer events). The handle also accepts
 * ArrowUp / ArrowDown from the keyboard. After a move the container fires a
 * bubbling "sp-sorted" event and an "input" event so forms notice the change.
 */
(function () {
  'use strict';

  function items(container) {
    return Array.prototype.filter.call(container.children, function (el) { return el.hasAttribute('data-sort-item'); });
  }

  function changed(container) {
    container.dispatchEvent(new CustomEvent('sp-sorted', { bubbles: true }));
    container.dispatchEvent(new Event('input', { bubbles: true }));
  }

  function start(e) {
    var handle = e.target.closest('[data-sort-handle]');
    if (!handle || (e.button !== undefined && e.button !== 0)) return;
    var item = handle.closest('[data-sort-item]');
    var container = item && item.parentElement;
    if (!container || !container.hasAttribute('data-sortable')) return;
    e.preventDefault();

    var startY = e.clientY;
    var moved = false;
    var rect = item.getBoundingClientRect();
    container.classList.add('is-sorting');
    item.classList.add('is-dragging');

    var lastY = e.clientY;
    var timer = null;

    function reorder(y) {
      item.style.transform = ''; // measure the real layout positions
      // Swap with neighbours while the pointer is past their middle (fast moves skip several rows).
      for (var guard = 0; guard < 100; guard++) {
        var list = items(container);
        var idx = list.indexOf(item);
        var prev = list[idx - 1], next = list[idx + 1];
        if (next && y > next.getBoundingClientRect().top + next.getBoundingClientRect().height / 2) { container.insertBefore(next, item); continue; }
        if (prev && y < prev.getBoundingClientRect().top + prev.getBoundingClientRect().height / 2) { container.insertBefore(prev, item.nextSibling); continue; }
        break;
      }
      // Keep the row under the pointer while it moves.
      var r = item.getBoundingClientRect();
      var shift = y - (r.top + grabOffset);
      item.style.transform = 'translateY(' + shift + 'px)';
    }
    var grabOffset = e.clientY - rect.top;

    function move(ev) {
      lastY = ev.clientY;
      if (!moved && Math.abs(ev.clientY - startY) < 3) return;
      moved = true;
      reorder(lastY);
      if (!timer) timer = setInterval(scrollTick, 16);
    }
    // While the pointer rests near the top/bottom edge, keep scrolling and reordering.
    function scrollTick() {
      var edge = 70, speed = 0;
      if (lastY < edge) speed = -Math.ceil((edge - lastY) / 4);
      else if (lastY > window.innerHeight - edge) speed = Math.ceil((lastY - (window.innerHeight - edge)) / 4);
      if (!speed) return;
      window.scrollBy(0, speed);
      reorder(lastY);
    }
    function end() {
      clearInterval(timer);
      document.removeEventListener('pointermove', move);
      document.removeEventListener('pointerup', end);
      document.removeEventListener('pointercancel', end);
      item.style.transform = '';
      item.classList.remove('is-dragging');
      container.classList.remove('is-sorting');
      if (moved) changed(container);
    }
    // Listen on the document: the dragged row is never detached, but neighbours move around it.
    document.addEventListener('pointermove', move);
    document.addEventListener('pointerup', end);
    document.addEventListener('pointercancel', end);
  }


  document.addEventListener('pointerdown', start);
  document.addEventListener('keydown', function (e) {
    var handle = e.target.closest && e.target.closest('[data-sort-handle]');
    if (!handle || (e.key !== 'ArrowUp' && e.key !== 'ArrowDown')) return;
    var item = handle.closest('[data-sort-item]');
    var container = item.parentElement;
    var list = items(container), idx = list.indexOf(item);
    if (e.key === 'ArrowUp' && idx > 0) container.insertBefore(item, list[idx - 1]);
    else if (e.key === 'ArrowDown' && idx < list.length - 1) container.insertBefore(list[idx + 1], item);
    else return;
    e.preventDefault();
    handle.focus();
    changed(container);
  });
  // A click on the handle must not submit forms or toggle anything.
  document.addEventListener('click', function (e) { if (e.target.closest && e.target.closest('[data-sort-handle]')) e.preventDefault(); });
})();

/* Info popovers: tap/click toggles (touch devices), Esc or a click outside closes. */
(function () {
  'use strict';
  document.addEventListener('click', function (e) {
    var btn = e.target.closest && e.target.closest('.sp-info-btn');
    document.querySelectorAll('.sp-info.is-open').forEach(function (el) {
      if (!btn || !el.contains(btn)) { el.classList.remove('is-open'); el.querySelector('.sp-info-btn').setAttribute('aria-expanded', 'false'); }
    });
    if (btn) {
      var box = btn.closest('.sp-info');
      var open = !box.classList.contains('is-open');
      box.classList.toggle('is-open', open);
      btn.setAttribute('aria-expanded', open ? 'true' : 'false');
      if (!open) btn.blur();
    }
  });
  document.addEventListener('keydown', function (e) {
    if (e.key !== 'Escape') return;
    document.querySelectorAll('.sp-info.is-open, .sp-info:focus-within').forEach(function (el) {
      el.classList.remove('is-open');
      var b = el.querySelector('.sp-info-btn'); b.setAttribute('aria-expanded', 'false'); b.blur();
    });
  });
})();
