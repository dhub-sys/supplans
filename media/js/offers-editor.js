/**
 * Support Plans – visual text editor for offer texts.
 *
 * Turns <textarea data-sp-editor> into a small WYSIWYG editor with a toolbar.
 * The textarea stays the form field and keeps the light markup that the PDF
 * renderer understands (see OfferHelper::render), so stored texts, the PDF and
 * its pagination are unchanged:
 *   **bold**  *italic*  __underline__  ^1  [text](https://…)  ## / ### headings
 *   - bullet  1. numbered  blank line = new paragraph  {variables}
 */
(function () {
  'use strict';

  var VARIABLES = [
    ['{customer}', 'Πελάτης'],
    ['{project}', 'Έργο / εταιρεία πελάτη'],
    ['{company}', 'Η επωνυμία σας'],
    ['{number}', 'Αριθμός προσφοράς'],
    ['{date}', 'Ημερομηνία προσφοράς']
  ];

  /* ---------- markup -> HTML (mirror of OfferHelper::render, without placeholders) ---------- */

  function esc(s) {
    return s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }

  function inline(text) {
    var out = esc(text);
    out = out.replace(/\*\*\*(.+?)\*\*\*/g, '<strong><em>$1</em></strong>');
    out = out.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
    out = out.replace(/(^|[^*])\*(?!\s)(.+?)(?<!\s)\*(?!\*)/g, '$1<em>$2</em>');
    out = out.replace(/__(.+?)__/g, '<u>$1</u>');
    out = out.replace(/\^(\d{1,3})/g, '<sup>$1</sup>');
    out = out.replace(/\[([^\]]+)\]\((https?:\/\/[^\s)]+)\)/g, '<a href="$2">$1</a>');
    return out;
  }

  function toHtml(markup) {
    var lines = String(markup || '').replace(/\r\n?/g, '\n').split('\n');
    var html = [], para = [], list = null;
    function flushPara() { if (para.length) { html.push('<p>' + para.join('<br>') + '</p>'); para = []; } }
    function flushList() {
      if (list) { html.push('<' + list.type + '><li>' + list.items.join('</li><li>') + '</li></' + list.type + '>'); list = null; }
    }
    lines.forEach(function (line) {
      var t = line.trim(), m;
      if (t === '') { flushPara(); flushList(); return; }
      if ((m = t.match(/^(#{2,3})\s+(.+)$/))) {
        flushPara(); flushList();
        var tag = m[1].length === 2 ? 'h3' : 'h4';
        html.push('<' + tag + '>' + inline(m[2]) + '</' + tag + '>');
        return;
      }
      var type = null, content = '';
      if ((m = t.match(/^[-*•▪]\s+(.+)$/))) { type = 'ul'; content = m[1]; }
      else if ((m = t.match(/^\d+[.)]\s+(.+)$/))) { type = 'ol'; content = m[1]; }
      if (type) {
        flushPara();
        if (list && list.type !== type) flushList();
        list = list || { type: type, items: [] };
        list.items.push(inline(content));
        return;
      }
      flushList();
      para.push(inline(t));
    });
    flushPara(); flushList();
    return html.join('') || '<p><br></p>';
  }

  /* ---------- HTML -> markup ---------- */

  var BLOCKS = /^(P|DIV|H1|H2|H3|H4|H5|H6|UL|OL|LI|BLOCKQUOTE|PRE|TABLE|SECTION|ARTICLE)$/;

  // Wraps inline content in a marker, keeping surrounding spaces outside it.
  function wrap(text, mark) {
    if (!text.trim()) return text;
    var m = text.match(/^(\s*)([\s\S]*?)(\s*)$/);
    return m[1] + mark + m[2] + mark + m[3];
  }

  function inlineMd(node) {
    var out = '';
    node.childNodes.forEach(function (n) {
      if (n.nodeType === 3) { out += n.nodeValue.replace(/\u00a0/g, ' ').replace(/\u200b/g, '').replace(/\n/g, ' '); return; }
      if (n.nodeType !== 1) return;
      var tag = n.tagName, inner;
      if (tag === 'BR') { out += '\n'; return; }
      if (tag === 'UL' || tag === 'OL') return; // nested lists are flattened by the block walker
      inner = inlineMd(n);
      var style = n.getAttribute('style') || '';
      if (tag === 'B' || tag === 'STRONG' || /font-weight:\s*(bold|[6-9]00)/i.test(style)) inner = wrap(inner, '**');
      if (tag === 'I' || tag === 'EM' || /font-style:\s*italic/i.test(style)) inner = wrap(inner, '*');
      if (tag === 'U' || /text-decoration[^;]*underline/i.test(style)) inner = wrap(inner, '__');
      if (tag === 'SUP') {
        // Footnote marks are numbers; anything typed after them inside the <sup> stays normal text.
        var fm = inner.match(/^\s*(\d{1,3})([\s\S]*)$/);
        inner = fm ? '^' + fm[1] + fm[2] : inner;
      }
      if (tag === 'A') {
        var href = n.getAttribute('href') || '';
        if (/^https?:\/\//i.test(href) && inner.trim()) inner = '[' + inner.trim() + '](' + href + ')';
      }
      out += inner;
    });
    return out;
  }

  function cleanLines(text) {
    return text.split('\n').map(function (l) { return l.replace(/\s+/g, ' ').trim(); }).filter(function (l, i, a) { return l !== '' || (i > 0 && a[i - 1] !== ''); }).join('\n').trim();
  }

  function listMd(list, out) {
    var n = 0;
    Array.prototype.forEach.call(list.children, function (li) {
      if (li.tagName !== 'LI') return;
      var text = cleanLines(inlineMd(li)).replace(/\n/g, ' ');
      if (text) out.push((list.tagName === 'OL' ? (++n) + '. ' : '- ') + text);
      li.querySelectorAll(':scope > ul, :scope > ol').forEach(function (sub) { listMd(sub, out); });
    });
  }

  function toMarkup(root) {
    var blocks = [], pending = '';
    function flush() { var t = cleanLines(pending); if (t) blocks.push(t); pending = ''; }
    function walk(parent) {
      parent.childNodes.forEach(function (n) {
        if (n.nodeType === 1 && BLOCKS.test(n.tagName)) {
          flush();
          var tag = n.tagName;
          if (tag === 'UL' || tag === 'OL') { var items = []; listMd(n, items); if (items.length) blocks.push(items.join('\n')); }
          else if (/^H[1-3]$/.test(tag)) { var h = cleanLines(inlineMd(n)).replace(/\n/g, ' '); if (h) blocks.push('## ' + h); }
          else if (/^H[4-6]$/.test(tag)) { var h4 = cleanLines(inlineMd(n)).replace(/\n/g, ' '); if (h4) blocks.push('### ' + h4); }
          else if (Array.prototype.some.call(n.children, function (c) { return BLOCKS.test(c.tagName); })) walk(n);
          else { pending = inlineMd(n); flush(); }
        } else if (n.nodeType === 1 || n.nodeType === 3) {
          var tmp = document.createElement('span');
          tmp.appendChild(n.cloneNode(true));
          pending += inlineMd(tmp);
        }
      });
      flush();
    }
    walk(root);
    // Lists and headings are separated by a blank line, like paragraphs.
    return blocks.join('\n\n');
  }

  /* ---------- editor UI ---------- */

  var ICONS = {
    bold: '<b>B</b>', italic: '<i>I</i>', underline: '<u>U</u>',
    ul: '<svg viewBox="0 0 20 20"><circle cx="4" cy="5" r="1.4"/><circle cx="4" cy="10" r="1.4"/><circle cx="4" cy="15" r="1.4"/><path d="M8 5h9M8 10h9M8 15h9"/></svg>',
    ol: '<svg viewBox="0 0 20 20"><path d="M8 5h9M8 10h9M8 15h9"/><text x="1.5" y="7" font-size="6">1</text><text x="1.5" y="12" font-size="6">2</text><text x="1.5" y="17" font-size="6">3</text></svg>',
    sup: 'x<sup>1</sup>',
    link: '<svg viewBox="0 0 20 20"><path d="M8.5 11.5a3.5 3.5 0 0 0 5 0l2.5-2.5a3.5 3.5 0 0 0-5-5L10 5"/><path d="M11.5 8.5a3.5 3.5 0 0 0-5 0L4 11a3.5 3.5 0 0 0 5 5l1-1"/></svg>',
    unlink: '<svg viewBox="0 0 20 20"><path d="M8.5 11.5a3.5 3.5 0 0 0 5 0l2.5-2.5a3.5 3.5 0 0 0-5-5L10 5"/><path d="M11.5 8.5a3.5 3.5 0 0 0-5 0L4 11a3.5 3.5 0 0 0 5 5l1-1"/><path d="M3 3l14 14"/></svg>',
    clear: '<svg viewBox="0 0 20 20"><path d="M5 4h10M10 4l-3 12M4 17h6M13 12l4 4M17 12l-4 4"/></svg>',
    undo: '<svg viewBox="0 0 20 20"><path d="M7 5L3 9l4 4"/><path d="M3 9h9a5 5 0 0 1 0 10h-2"/></svg>',
    redo: '<svg viewBox="0 0 20 20"><path d="M13 5l4 4-4 4"/><path d="M17 9H8a5 5 0 0 0 0 10h2"/></svg>',
    code: '<svg viewBox="0 0 20 20"><path d="M7 6l-4 4 4 4M13 6l4 4-4 4"/></svg>'
  };

  function button(cmd, title, html) {
    return '<button type="button" class="sp-ed-btn" data-cmd="' + cmd + '" title="' + title + '" aria-label="' + title + '">' + html + '</button>';
  }

  function attach(textarea) {
    if (textarea.dataset.spEditorReady) return;
    textarea.dataset.spEditorReady = '1';

    var wrapEl = document.createElement('div');
    wrapEl.className = 'sp-ed';
    // Built-in variables plus the custom ones defined on the page (Μεταβλητές card).
    function variableOptions() {
      var list = VARIABLES.slice();
      if (typeof window.spCustomVariables === 'function') list = list.concat(window.spCustomVariables());
      return '<option value="">Μεταβλητή…</option>' + list.map(function (v) { return '<option value="' + esc(v[0]) + '">' + esc(v[0] + ' – ' + v[1]) + '</option>'; }).join('');
    }
    wrapEl.innerHTML =
      '<div class="sp-ed-toolbar" role="toolbar">' +
        '<select class="sp-ed-block" title="Στυλ κειμένου"><option value="p">Κείμενο</option><option value="h3">Υπότιτλος (υπογράμμιση)</option><option value="h4">Υπότιτλος (έντονος)</option></select>' +
        '<span class="sp-ed-sep"></span>' +
        button('bold', 'Έντονα (Ctrl+B)', ICONS.bold) + button('italic', 'Πλάγια (Ctrl+I)', ICONS.italic) + button('underline', 'Υπογράμμιση (Ctrl+U)', ICONS.underline) +
        '<span class="sp-ed-sep"></span>' +
        button('insertUnorderedList', 'Λίστα με κουκκίδες', ICONS.ul) + button('insertOrderedList', 'Αριθμημένη λίστα', ICONS.ol) +
        '<span class="sp-ed-sep"></span>' +
        button('superscript', 'Παραπομπή / εκθέτης (π.χ. ¹) – Ctrl+.', ICONS.sup) + button('link', 'Σύνδεσμος', ICONS.link) + button('unlink', 'Αφαίρεση συνδέσμου', ICONS.unlink) +
        '<span class="sp-ed-sep"></span>' +
        '<select class="sp-ed-var" title="Εισαγωγή μεταβλητής">' + variableOptions() + '</select>' +
        '<span class="sp-ed-sep"></span>' +
        button('removeFormat', 'Καθαρισμός μορφοποίησης', ICONS.clear) + button('undo', 'Αναίρεση (Ctrl+Z)', ICONS.undo) + button('redo', 'Επανάληψη', ICONS.redo) +
        '<span class="sp-ed-spacer"></span>' +
        button('source', 'Προβολή κώδικα μορφοποίησης', ICONS.code) +
      '</div>' +
      '<div class="sp-ed-area" contenteditable="true" spellcheck="true"></div>';

    textarea.parentNode.insertBefore(wrapEl, textarea);
    wrapEl.appendChild(textarea);
    textarea.classList.add('sp-ed-source');
    textarea.hidden = true;

    var area = wrapEl.querySelector('.sp-ed-area');
    var blockSel = wrapEl.querySelector('.sp-ed-block');
    var varSel = wrapEl.querySelector('.sp-ed-var');
    var rows = parseInt(textarea.getAttribute('rows'), 10) || 6;
    area.style.minHeight = Math.max(90, rows * 22) + 'px';
    area.innerHTML = toHtml(textarea.value);
    var sourceMode = false;
    var savedRange = null;

    function sync() {
      var md = toMarkup(area);
      if (textarea.value !== md) {
        textarea.value = md;
        textarea.dispatchEvent(new Event('input', { bubbles: true }));
      }
    }
    function exec(cmd, value) {
      var sel = window.getSelection();
      var inside = sel.rangeCount && area.contains(sel.getRangeAt(0).commonAncestorContainer);
      area.focus();
      // Restore the last caret position only when focus was taken by a toolbar control.
      if (!inside && savedRange) { sel.removeAllRanges(); sel.addRange(savedRange); }
      try { document.execCommand('styleWithCSS', false, false); } catch (e) {}
      document.execCommand(cmd, false, value);
      sync();
      updateState();
    }
    function updateState() {
      wrapEl.querySelectorAll('.sp-ed-btn[data-cmd]').forEach(function (b) {
        var c = b.dataset.cmd, on = false;
        if (/^(bold|italic|underline|superscript|insertUnorderedList|insertOrderedList)$/.test(c)) {
          try { on = document.queryCommandState(c); } catch (e) {}
        }
        b.classList.toggle('is-on', !!on && document.activeElement === area);
      });
      var block = '';
      try { block = String(document.queryCommandValue('formatBlock') || '').toLowerCase(); } catch (e) {}
      blockSel.value = block === 'h1' || block === 'h2' || block === 'h3' ? 'h3' : (/^h[4-6]$/.test(block) ? 'h4' : 'p');
    }
    function rememberSelection() {
      var sel = window.getSelection();
      if (sel.rangeCount && area.contains(sel.getRangeAt(0).commonAncestorContainer)) savedRange = sel.getRangeAt(0).cloneRange();
    }

    wrapEl.querySelector('.sp-ed-toolbar').addEventListener('mousedown', function (e) {
      if (e.target.closest('.sp-ed-btn')) e.preventDefault(); // keep the text selection
    });
    wrapEl.querySelector('.sp-ed-toolbar').addEventListener('click', function (e) {
      var b = e.target.closest('.sp-ed-btn');
      if (!b) return;
      var cmd = b.dataset.cmd;
      if (cmd === 'source') {
        sourceMode = !sourceMode;
        if (sourceMode) { sync(); textarea.hidden = false; area.hidden = true; textarea.focus(); }
        else { area.innerHTML = toHtml(textarea.value); textarea.hidden = true; area.hidden = false; }
        b.classList.toggle('is-on', sourceMode);
        wrapEl.classList.toggle('is-source', sourceMode);
        return;
      }
      if (sourceMode) return;
      if (cmd === 'superscript') {
        var cur = window.getSelection();
        var collapsed = !cur.rangeCount || cur.getRangeAt(0).collapsed || !area.contains(cur.getRangeAt(0).commonAncestorContainer);
        if (collapsed) {
          // Insert a numbered footnote mark; the caret continues in normal text after it.
          rememberSelection();
          var max = 0;
          area.querySelectorAll('sup').forEach(function (el) { var v = parseInt(el.textContent, 10); if (v > max) max = v; });
          var num = window.prompt('Αριθμός παραπομπής', String(max + 1));
          if (!num || !/^\d{1,3}$/.test(num.trim())) return;
          exec('insertHTML', '<sup>' + num.trim() + '</sup>\u200b');
          return;
        }
      }
      if (cmd === 'link') {
        rememberSelection();
        var url = window.prompt('Διεύθυνση συνδέσμου (https://…)', 'https://');
        if (!url || !/^https?:\/\/\S+$/i.test(url.trim())) return;
        if (savedRange && savedRange.collapsed) {
          exec('insertHTML', '<a href="' + esc(url.trim()) + '">' + esc(url.trim()) + '</a>');
        } else {
          exec('createLink', url.trim());
        }
        return;
      }
      exec(cmd);
    });
    blockSel.addEventListener('mousedown', rememberSelection);
    blockSel.addEventListener('change', function () { if (!sourceMode) exec('formatBlock', '<' + blockSel.value + '>'); });
    function refreshVariables() { rememberSelection(); var html = variableOptions(); if (varSel.dataset.opts !== html) { varSel.innerHTML = html; varSel.dataset.opts = html; } }
    varSel.addEventListener('mousedown', refreshVariables);
    varSel.addEventListener('focus', refreshVariables);
    varSel.addEventListener('change', function () {
      var v = varSel.value;
      varSel.value = '';
      if (!v) return;
      if (sourceMode) {
        var s = textarea.selectionStart, en = textarea.selectionEnd;
        textarea.value = textarea.value.slice(0, s) + v + textarea.value.slice(en);
        textarea.selectionStart = textarea.selectionEnd = s + v.length;
        textarea.focus();
        return;
      }
      exec('insertText', v);
    });

    area.addEventListener('input', function () { rememberSelection(); sync(); });
    document.addEventListener('selectionchange', rememberSelection);
    area.addEventListener('keyup', function () { rememberSelection(); updateState(); });
    area.addEventListener('mouseup', function () { rememberSelection(); updateState(); });
    area.addEventListener('blur', function () { rememberSelection(); sync(); });
    area.addEventListener('focus', function () {
      try { document.execCommand('defaultParagraphSeparator', false, 'p'); } catch (e) {}
      updateState();
    });
    // Paste as plain text so formatting from Word / web pages does not leak in;
    // lines that look like bullets keep their meaning through the markup.
    area.addEventListener('paste', function (e) {
      var text = (e.clipboardData || window.clipboardData).getData('text/plain');
      if (text == null) return;
      e.preventDefault();
      text = text.replace(/\r\n?/g, '\n').replace(/^[\t ]*[•▪◦●■–]\s*/gm, '- ');
      if (/\n/.test(text)) {
        // Multi-line paste: convert through the markup so lists and paragraphs become real blocks.
        document.execCommand('insertHTML', false, toHtml(text));
      } else {
        document.execCommand('insertText', false, text);
      }
      sync();
    });
    // Keyboard shortcut for superscript footnotes.
    area.addEventListener('keydown', function (e) {
      if ((e.ctrlKey || e.metaKey) && e.key === '.') { e.preventDefault(); wrapEl.querySelector('[data-cmd=superscript]').click(); }
    });
    var form = textarea.form;
    if (form) form.addEventListener('submit', function () { if (sourceMode) return; sync(); });
  }

  function attachAll(root) {
    (root || document).querySelectorAll('textarea[data-sp-editor]').forEach(attach);
  }

  window.SpEditor = { attach: attach, attachAll: attachAll, toHtml: toHtml, toMarkup: toMarkup };
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', function () { attachAll(); });
  else attachAll();
})();
