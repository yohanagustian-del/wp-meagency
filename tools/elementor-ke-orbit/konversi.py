#!/usr/bin/env python3
"""Konversi halaman Elementor ke field template Orbit (layanan / studi kasus hub).

Pakai:
  python3 -I tools/elementor-ke-orbit/konversi.py <template> <keluar-dir> <post.json>...

<post.json> = hasil konektor `wp_get_post_full` (context=edit, include meta), memuat
`post` dan `meta._elementor_data`. <template> = `layanan` atau `studi-kasus-hub`.

Isi halaman dipindah apa adanya, tanpa menulis ulang teks:
- H1 pertama -> mea_h1; paragraf pertama setelah H1 (sebelum judul lain) -> mea_lede.
- Gambar widget Image sebelum H1 -> mea_foto_hero_id (alt dari Media Library).
- Accordion Elementor dan JSON-LD FAQPage di widget HTML -> mea_faq; salinan FAQ yang
  terlihat di isi dibuang supaya tidak dobel (FAQ tampil di section FAQ template).
- Sisanya -> mea_isi: HTML bersih (judul, paragraf, daftar, link, tabel, gambar) tanpa
  style, script, kelas, dan kontainer. Gambar Media Library ditulis `[mea-gambar ID]`.
Semua yang dibuang atau diubah dicatat di laporan (`catatan` di seed).
"""
import html
import json
import re
import sys
from html.parser import HTMLParser

DROP = {'style', 'script', 'head', 'title', 'meta', 'link', 'noscript', 'svg', 'form', 'iframe', 'template', 'button', 'input', 'select', 'textarea', 'canvas', 'video', 'audio', 'picture-source'}
BLOCK = {'p', 'li', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'td', 'th', 'blockquote', 'figcaption', 'dt', 'dd', 'summary'}
KEEP = {'p', 'ul', 'ol', 'li', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'strong', 'em', 'a', 'br', 'blockquote', 'table', 'thead', 'tbody', 'tr', 'th', 'td', 'img', 'hr'}
MAP = {'b': 'strong', 'i': 'em', 'summary': 'h3', 'dt': 'h3', 'dd': 'p', 'figcaption': 'p'}
CONTAINER = {'div', 'section', 'article', 'header', 'footer', 'main', 'aside', 'figure', 'nav', 'details', 'dl', 'body', 'html', 'picture'}
INLINE_OK = {'strong', 'em', 'a', 'br', 'img'}


def norm(text):
    """Teks polos: tag blok dan <br> jadi spasi, tag inline dihapus tanpa spasi."""
    text = re.sub(r'</?(p|li|h[1-6]|br|td|th|ul|ol|div|section|tr|blockquote)\b[^>]*>', ' ', text or '')
    return re.sub(r'\s+', ' ', html.unescape(re.sub(r'<[^>]+>', '', text))).strip()


class Clean(HTMLParser):
    """HTML bebas -> daftar blok HTML bersih (string per blok tingkat atas)."""

    def __init__(self):
        super().__init__(convert_charrefs=True)
        self.out = []          # token output
        self.stack = []        # tag tersimpan yang terbuka
        self.drop = 0          # kedalaman elemen yang dibuang
        self.implicit = False  # <p> yang dibuka otomatis
        self.notes = []
        self.ldjson = []
        self.in_ld = False
        self.fixed = 0

    def _in_block(self):
        return any(t in BLOCK or t in ('ul', 'ol', 'table') for t in self.stack) or self.implicit

    def _close_implicit(self):
        if self.implicit:
            self.out.append('</p>')
            self.implicit = False

    def handle_starttag(self, tag, attrs):
        a = dict(attrs)
        if self.drop:
            if tag not in ('br', 'img', 'meta', 'link', 'input', 'hr', 'source'):
                self.drop += 1
            return
        if tag == 'script' and 'ld+json' in (a.get('type') or ''):
            self.in_ld = True
            self.drop = 1
            return
        if tag in DROP:
            if tag in ('iframe', 'video', 'form'):
                self.notes.append('dibuang: <%s> %s' % (tag, a.get('src') or a.get('action') or ''))
            if tag not in ('meta', 'link', 'input', 'source'):
                self.drop = 1
            return
        style = (a.get('style') or '').replace(' ', '')
        if tag == 'a' and 'position:fixed' in style:
            # Tombol WA melayang: link-nya dipertahankan sebagai paragraf biasa.
            self._close_implicit()
            self.out.append('<p>')
            self.stack.append('p')
            self.fixed += 1
            self.notes.append('tombol melayang jadi link biasa: %s' % a.get('href', ''))
        tag = MAP.get(tag, tag)
        if tag == 'span' and self.out and self.out[-1] == '\0span':
            # Span berdampingan (mis. deretan logo media) dipisah spasi.
            self.out[-1] = ' '
        if tag in CONTAINER or tag == 'span' or tag not in KEEP:
            if tag in CONTAINER:
                self._close_implicit()
            return
        if tag in BLOCK or tag in ('ul', 'ol', 'table', 'hr'):
            self._close_implicit()
        elif tag in INLINE_OK and not self._in_block():
            self.out.append('<p>')
            self.implicit = True
        attr = ''
        if tag == 'a':
            href = (a.get('href') or '').strip()
            if href and not href.startswith('javascript:'):
                attr = ' href="%s"' % html.escape(href)
                if a.get('target') == '_blank':
                    attr += ' target="_blank" rel="noopener"'
        elif tag == 'img':
            src = a.get('src') or a.get('data-src') or ''
            if not src:
                return
            attr = ' src="%s" alt="%s"' % (html.escape(src), html.escape(a.get('alt') or ''))
            for k in ('width', 'height'):
                if (a.get(k) or '').isdigit():
                    attr += ' %s="%s"' % (k, a[k])
            attr += ' loading="lazy"'
        if tag in ('br', 'img', 'hr'):
            self.out.append('<%s%s>' % (tag, attr))
            return
        self.out.append('<%s%s>' % (tag, attr))
        self.stack.append(tag)

    def handle_endtag(self, tag):
        if self.drop:
            self.drop -= 1
            if not self.drop:
                self.in_ld = False
            return
        tag = MAP.get(tag, tag)
        if tag in CONTAINER:
            self._close_implicit()
            return
        if tag == 'span':
            self.out.append('\0span')
            return
        if tag not in self.stack:
            return
        while self.stack:
            t = self.stack.pop()
            self.out.append('</%s>' % t)
            if t == tag:
                break
        if tag == 'a' and self.fixed and 'a' not in self.stack and self.stack and self.stack[-1] == 'p':
            self.fixed -= 1
            self.out.append('</%s>' % self.stack.pop())

    def handle_data(self, data):
        if self.drop:
            if self.in_ld:
                self.ldjson.append(data)
            return
        if not data.strip():
            if self.out and self._in_block():
                self.out.append(' ')
            return
        if not self._in_block():
            self.out.append('<p>')
            self.implicit = True
        # "<b>21X</b>ROAS" (angka dan label yang dulu bertumpuk lewat CSS) -> "21X ROAS".
        if self.out and self.out[-1] == '</strong>' and re.match(r'[A-Z0-9]', data) and re.search(r'[0-9A-Za-z%+]$', self.out[-2] if len(self.out) > 1 else ''):
            self.out.append(' ')
        self.out.append(html.escape(re.sub(r'\s+', ' ', data), quote=False))

    def result(self):
        self._close_implicit()
        while self.stack:
            self.out.append('</%s>' % self.stack.pop())
        text = ''.join(self.out).replace('\0span', '')
        text = re.sub(r'<(p|li|h[1-6]|strong|em|a)( [^>]*)?>\s*</\1>', '', text)
        text = re.sub(r'<(p|li|h[1-6]|strong|em|a)( [^>]*)?>\s*</\1>', '', text)
        text = re.sub(r'\s*(<br>\s*)+</p>', '</p>', text)
        text = re.sub(r'<p>\s*(<br>\s*)+', '<p>', text)
        text = re.sub(r' +', ' ', text)
        text = re.sub(r'<(p|li|h[1-6]|td|th)( [^>]*)?>\s+', r'<\1\2>', text)
        text = re.sub(r'\s+</(p|li|h[1-6]|td|th)>', r'</\1>', text)
        return text


def split_blocks(text):
    """Pecah HTML bersih menjadi blok tingkat atas."""
    blocks, depth, start = [], 0, 0
    for m in re.finditer(r'<(/?)(p|ul|ol|h[1-6]|blockquote|table|hr)\b[^>]*>', text):
        if m.group(1):
            depth -= 1
            if depth == 0:
                blocks.append(text[start:m.end()].strip())
                start = m.end()
        else:
            if depth == 0:
                if text[start:m.start()].strip():
                    blocks.append('<p>%s</p>' % text[start:m.start()].strip())
                start = m.start()
            if m.group(2) == 'hr':
                blocks.append('<hr>')
                start = m.end()
                continue
            depth += 1
    if text[start:].strip():
        blocks.append(text[start:].strip())
    return [b for b in blocks if b]


def clean(fragment):
    p = Clean()
    p.feed(fragment)
    p.close()
    return split_blocks(p.result()), p.notes, ''.join(p.ldjson)


class Doc:
    def __init__(self):
        self.blocks = []      # (jenis, html) jenis: blok | gambar
        self.faq = []
        self.notes = []
        self.hero_img = 0
        self.seen_h1 = False
        self.faq_at = []      # indeks blok tempat FAQ (accordion) dulu berada
        self.wid = 0          # nomor widget yang sedang dibaca
        self.wids = []        # nomor widget per blok (sejajar dengan blocks)

    def add(self, blocks, notes=()):
        for b in blocks:
            if b.startswith('<h1'):
                if not self.seen_h1:
                    self.seen_h1 = True
                    self.blocks.append(('h1', norm(b), self.wid))
                    self.h1_wid = self.wid
                    continue
                self.notes.append('H1 kedua jadi H2: %s' % norm(b))
                b = '<h2>' + b[b.index('>') + 1:-5] + '</h2>'
            self.blocks.append(('blok', b, self.wid))
        self.notes.extend(notes)


def widget(doc, el):
    doc.wid += 1
    t = el.get('widgetType')
    s = el.get('settings', {})
    if t == 'heading':
        size = s.get('header_size') or 'h2'
        size = size if re.fullmatch(r'h[1-6]', size) else 'h2'
        title = norm(s.get('title', ''))
        if not title:
            return
        inner = html.escape(title, quote=False)
        url = (s.get('link') or {}).get('url')
        if url:
            inner = '<a href="%s">%s</a>' % (html.escape(url), inner)
        doc.add(['<%s>%s</%s>' % (size, inner, size)])
    elif t == 'text-editor':
        blocks, notes, _ = clean(s.get('editor', ''))
        doc.add(blocks, notes)
    elif t == 'image':
        img = s.get('image') or {}
        mid = int(img.get('id') or 0)
        if mid and not doc.seen_h1 and not doc.hero_img:
            doc.hero_img = mid
            doc.notes.append('gambar sebelum H1 jadi foto hero: %d' % mid)
        elif mid:
            doc.blocks.append(('gambar', '<p>[mea-gambar %d]</p>' % mid, doc.wid))
        elif img.get('url'):
            doc.add(['<p><img src="%s" alt="" loading="lazy"></p>' % html.escape(img['url'])])
    elif t == 'image-carousel':
        for img in s.get('carousel') or []:
            if img.get('id'):
                doc.blocks.append(('gambar', '<p>[mea-gambar %d]</p>' % int(img['id']), doc.wid))
    elif t == 'button':
        text = norm(s.get('text', ''))
        url = (s.get('link') or {}).get('url') or ''
        if text:
            doc.add(['<p><a href="%s">%s</a></p>' % (html.escape(url), html.escape(text, quote=False)) if url else '<p>%s</p>' % html.escape(text, quote=False)])
    elif t == 'progress':
        pct = (s.get('percent') or {}).get('size')
        title = norm(s.get('title', ''))
        doc.add(['<p>%s%s</p>' % (html.escape(title, quote=False), ': %s%%' % pct if pct not in (None, '') else '')])
    elif t == 'icon-list':
        items = []
        for it in s.get('icon_list') or []:
            text = html.escape(norm(it.get('text', '')), quote=False)
            url = (it.get('link') or {}).get('url')
            if text:
                items.append('<li>%s</li>' % ('<a href="%s">%s</a>' % (html.escape(url), text) if url else text))
        if items:
            doc.add(['<ul>%s</ul>' % ''.join(items)])
    elif t == 'slides':
        for sl in s.get('slides') or []:
            parts = []
            if norm(sl.get('heading')):
                parts.append('<h3>%s</h3>' % html.escape(norm(sl['heading']), quote=False))
            if norm(sl.get('description')):
                parts.append('<p>%s</p>' % html.escape(norm(sl['description']), quote=False))
            url = (sl.get('link') or {}).get('url')
            if norm(sl.get('button_text')):
                bt = html.escape(norm(sl['button_text']), quote=False)
                parts.append('<p>%s</p>' % ('<a href="%s">%s</a>' % (html.escape(url), bt) if url else bt))
            doc.add(parts)
    elif t == 'html':
        blocks, notes, ld = clean(s.get('html', ''))
        for chunk in re.findall(r'\{.*\}', ld, re.S) if ld else []:
            try:
                data = json.loads(chunk)
            except ValueError:
                doc.notes.append('JSON-LD tidak terbaca')
                continue
            for node in data if isinstance(data, list) else [data]:
                if node.get('@type') == 'FAQPage':
                    for q in node.get('mainEntity', []):
                        doc.faq.append({'tanya': norm(q.get('name')), 'jawab': norm((q.get('acceptedAnswer') or {}).get('text'))})
                    doc.notes.append('JSON-LD FAQPage (%d tanya) jadi field FAQ' % len(node.get('mainEntity', [])))
                else:
                    doc.notes.append('JSON-LD dibuang: %s' % node.get('@type'))
        doc.add(blocks, notes)
    elif t == 'nested-accordion':
        items = s.get('items') or []
        kids = el.get('elements', [])
        for i, it in enumerate(items):
            sub = Doc()
            sub.seen_h1 = True
            for k in kids[i:i + 1]:
                walk(sub, k.get('elements', []))
            answer = ' '.join(norm(b) for kind, b, _ in sub.blocks if kind == 'blok')
            doc.faq.append({'tanya': norm(it.get('item_title')), 'jawab': answer})
        doc.notes.append('accordion (%d tanya) jadi field FAQ' % len(items))
        doc.faq_at.append(len(doc.blocks))
        return 'stop'
    elif t in ('spacer', 'divider', 'icon'):
        return
    else:
        doc.notes.append('widget tidak dikenal dilewati: %s' % t)


def walk(doc, els):
    for el in els:
        if el.get('elType') == 'widget':
            if widget(doc, el) == 'stop':
                continue
        walk(doc, el.get('elements', []))


def drop_faq_copies(doc):
    """Buang salinan FAQ yang terlihat di isi (pertanyaan + paragraf jawabannya)."""
    qs = {norm(f['tanya']).lower(): norm(f['jawab']).lower() for f in doc.faq}
    if not qs:
        return
    out, i, blocks, removed = [], 0, doc.blocks, 0
    while i < len(blocks):
        kind, b, wid = blocks[i]
        key = norm(b).lower()
        if kind == 'blok' and key in qs:
            if not removed:
                doc.faq_at.append(len(out))
            ans = qs[key]
            i += 1
            removed += 1
            while i < len(blocks) and blocks[i][0] == 'blok' and norm(blocks[i][1]).lower() and norm(blocks[i][1]).lower() in ans and norm(blocks[i][1]).lower() not in qs:
                i += 1
            continue
        out.append((kind, b, wid))
        i += 1
    if removed:
        doc.notes.append('salinan FAQ di isi dibuang: %d tanya' % removed)
    doc.blocks = out


def drop_faq_heading(doc, marks):
    """Judul section FAQ lama (judul tepat sebelum FAQ yang dipindah, tanpa isi lain
    sesudahnya sebelum judul berikut) dibuang: section FAQ template punya judul sendiri."""
    out = []
    for idx, (kind, b, wid) in enumerate(doc.blocks):
        if kind == 'blok' and re.match(r'<h[1-6]', b) and idx in marks:
            doc.notes.append('judul section FAQ lama dibuang: %s' % norm(b))
            continue
        out.append((kind, b, wid))
    doc.blocks = out


# Label breadcrumb dan nama schema Service, sama dengan label navigasi (mea_orbit_pilar()).
JUDUL_PENDEK = {
    '/services/': 'Layanan',
    '/ecommerce-service/': 'E-commerce Service',
    '/social-media-management/': 'Social Media Management',
    '/seo/': 'SEO',
    '/affiliate/': 'Affiliate',
    '/jasa-konten/': 'Jasa Konten',
    '/jasa-konten-tiktok/': 'Konten TikTok',
    '/jasa-live-streaming/': 'Live Streaming',
    '/jasa-live-streaming-tiktok/': 'Live Streaming TikTok',
    '/jasa-tiktok-ads/': 'TikTok Ads',
    '/jasa-kelola-tiktok-shop/': 'Kelola TikTok Shop',
    '/jasa-management-marketplace/': 'Kelola Toko Shopee',
    '/jasa-upgrade-shopee-mall/': 'Upgrade Shopee Mall',
    '/jasa-iklan-marketplace/': 'Iklan Marketplace',
    '/jasa-optimasi-shopee/': 'Optimasi Shopee',
    '/jasa-website/': 'Jasa Website',
}


def convert(post, template):
    data = post['meta']['_elementor_data']
    data = json.loads(data) if isinstance(data, str) else data
    doc = Doc()
    walk(doc, data)
    drop_faq_copies(doc)
    marks = set()
    for at in doc.faq_at:
        # Judul (dan label pendek) tepat sebelum FAQ.
        j = at - 1
        while j >= 0 and doc.blocks[j][0] == 'blok' and re.match(r'<h[1-6]', doc.blocks[j][1]):
            marks.add(j)
            j -= 1
    drop_faq_heading(doc, marks)
    h1, lede, label, isi = '', '', '', []
    pre = []
    after_h1 = False
    for kind, b, wid in doc.blocks:
        if kind == 'h1':
            h1 = b
            after_h1 = True
            # Label pendek tepat sebelum H1 di widget HTML yang sama (eyebrow hero).
            if pre and pre[-1][1] == wid and pre[-1][0].startswith('<p>') and len(norm(pre[-1][0])) <= 60 and '<a ' not in pre[-1][0]:
                label = norm(pre.pop()[0])
                doc.notes.append('label hero: %s' % label)
            continue
        if not after_h1:
            pre.append((b, wid))
            continue
        # Lede hanya bila H1 memang di puncak halaman (tidak ada blok isi sebelumnya).
        if not lede and not isi and not pre and kind == 'blok' and b.startswith('<p>') and '<a ' not in b:
            lede = norm(b)
            continue
        isi.append(b)
    if pre:
        doc.notes.append('blok sebelum H1 ditaruh di awal isi: %d' % len(pre))
    meta = {'mea_label_hero': label, 'mea_h1': h1, 'mea_lede': lede, 'mea_isi': '\n'.join([b for b, _ in pre] + isi), 'mea_faq': doc.faq}
    if template == 'layanan':
        meta['mea_foto_hero_id'] = doc.hero_img
        meta['mea_judul_pendek'] = JUDUL_PENDEK.get('/%s/' % post['post']['slug'], '')
        if post['post']['slug'] == 'services':
            meta['mea_hub_layanan'] = True
        tpl = 'page-templates/template-layanan.php'
    else:
        meta['mea_hub_kasus'] = True
        tpl = 'page-templates/template-studi-kasus.php'
        meta.pop('mea_faq')
        if doc.hero_img:
            doc.notes.append('foto hero %d tidak dipakai (template studi kasus)' % doc.hero_img)
    if not h1:
        doc.notes.append('PERHATIAN: tidak ada H1')
    gambar = sorted(set(int(x) for x in re.findall(r'\[mea-gambar (\d+)\]', meta['mea_isi'])))
    return {
        'page_id': post['post']['id'],
        'url': '/%s/' % post['post']['slug'],
        'template': tpl,
        'sebelum': {
            'template': post['meta'].get('_wp_page_template') or 'default',
            '_elementor_edit_mode': post['meta'].get('_elementor_edit_mode', ''),
        },
        'meta': meta,
        'gambar': gambar,
        'catatan': doc.notes,
    }


def main():
    template, outdir, files = sys.argv[1], sys.argv[2], sys.argv[3:]
    for f in files:
        post = json.load(open(f))
        seed = convert(post, template)
        name = '%s/page-%d-%s.json' % (outdir, seed['page_id'], post['post']['slug'])
        with open(name, 'w') as fh:
            json.dump(seed, fh, ensure_ascii=False, indent=2)
            fh.write('\n')
        print(name, 'h1=%r' % seed['meta']['mea_h1'][:60], 'isi=%d' % len(seed['meta']['mea_isi']), 'faq=%d' % len(seed['meta'].get('mea_faq', [])), 'catatan=%d' % len(seed['catatan']))


if __name__ == '__main__':
    main()
