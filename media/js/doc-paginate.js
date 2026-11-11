// A4 paginator of the printable documents (moves the flowing blocks of #sp-od-flow into pages).
(function () {
  var pages = document.getElementById('sp-od-pages');
  var flow = document.getElementById('sp-od-flow');
  var tpl = document.getElementById('sp-od-page-template');
  var body = null;
  var SAFETY = 6; // px kept free at the bottom of every page for print rendering differences

  function newPage() {
    var page = tpl.content.firstElementChild.cloneNode(true);
    pages.appendChild(page);
    page.querySelector('.sp-od-num').textContent = pages.children.length;
    body = page.querySelector('.sp-od-body');
  }
  // scrollHeight never drops below clientHeight, so measure where the content actually ends.
  function overflows() {
    var last = body.lastElementChild;
    if (!last) return false;
    var bottom = last.getBoundingClientRect().bottom + (parseFloat(getComputedStyle(last).marginBottom) || 0);
    return bottom - body.getBoundingClientRect().top > body.clientHeight - SAFETY;
  }
  function splittable(el) {
    if (el.matches('ul,ol')) return el.children.length > 1;
    if (el.matches('table')) return el.tBodies.length && el.tBodies[0].rows.length > 1;
    return false;
  }
  // Moves the trailing children of `el` into a new element until `el` fits; returns the remainder.
  function split(el) {
    var rest = el.cloneNode(false);
    var from = el, to = rest;
    if (el.matches('table')) {
      if (el.tHead) rest.appendChild(el.tHead.cloneNode(true));
      to = document.createElement('tbody');
      rest.appendChild(to);
      from = el.tBodies[0];
    }
    while (overflows() && from.children.length > 1) to.insertBefore(from.lastElementChild, to.firstChild);
    if (overflows()) { // not even one row/item fits: put everything back and move the whole block
      while (to.firstElementChild) from.appendChild(to.firstElementChild);
      return null;
    }
    if (el.matches('ol') && !el.hasAttribute('start')) rest.setAttribute('start', String(from.children.length + 1));
    else if (el.matches('ol')) rest.setAttribute('start', String(parseInt(el.getAttribute('start'), 10) + from.children.length));
    return rest;
  }
  function place(el) {
    // Sections marked "new page" start on a fresh page (unless the current one is still empty).
    if (el.classList.contains('sp-o-break') && body.children.length) newPage();
    body.appendChild(el);
    if (!overflows()) return;
    if (splittable(el)) {
      var rest = split(el);
      if (rest) { newPage(); place(rest); return; }
    }
    // Move the block (and a heading that would be left alone) to a new page.
    var carry = [el];
    var prev = el.previousElementSibling;
    while (prev && prev.classList.contains('sp-o-keep')) { carry.unshift(prev); prev = prev.previousElementSibling; }
    if (carry.length === body.children.length) return; // already first on its page: let it be
    newPage();
    carry.forEach(function (node) { body.appendChild(node); });
  }
  function paginate() {
    var blocks = Array.prototype.slice.call(flow.children);
    newPage();
    blocks.forEach(place);
    flow.remove();
    if (body && !body.children.length) pages.removeChild(pages.lastElementChild);
  }
  var images = Array.prototype.slice.call(document.querySelectorAll('.sp-od img')).map(function (img) {
    return img.complete ? Promise.resolve() : new Promise(function (r) { img.addEventListener('load', r); img.addEventListener('error', r); });
  });
  var fonts = document.fonts && document.fonts.ready ? document.fonts.ready : Promise.resolve();
  window.spOfferReady = Promise.all(images.concat([fonts])).then(paginate);
})();
